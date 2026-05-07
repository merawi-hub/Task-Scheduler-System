<?php

namespace App\Services;

use App\Models\Task;
use App\Models\Worker;
use App\Models\TaskLog;
use Illuminate\Support\Facades\DB;

class TaskClaimService
{
    /**
     * Atomically claim the next available task for a worker
     * Uses SELECT FOR UPDATE to prevent race conditions
     *
     * @param string $workerKey
     * @return Task|null
     */
    public function claimNext(string $workerKey): ?Task
    {
        $worker = Worker::where('worker_key', $workerKey)->first();

        if (!$worker) {
            return null;
        }

        return DB::transaction(function () use ($worker) {
            // Atomically select and lock the next available task
            $task = Task::where('status', 'pending')
                ->where(function ($query) {
                    $query->whereNull('available_after')
                        ->orWhere('available_after', '<=', now());
                })
                ->orderBy('created_at')
                ->lockForUpdate()
                ->first();

            if (!$task) {
                return null;
            }

            // Update task status to assigned
            $task->update([
                'status' => 'assigned',
                'worker_id' => $worker->id,
                'assigned_at' => now(),
            ]);

            // Update worker status
            $worker->update([
                'status' => 'busy',
                'current_task_id' => $task->id,
            ]);

            // Log the assignment
            TaskLog::info(
                $task->id,
                "Task assigned to worker {$worker->worker_key}",
                $worker->id,
                ['worker_key' => $worker->worker_key]
            );

            // Update job status to running if it's the first task
            $job = $task->job;
            if ($job->status === 'pending') {
                $job->update([
                    'status' => 'running',
                    'started_at' => now(),
                ]);
            }

            return $task->fresh(['job', 'worker']);
        });
    }

    /**
     * Mark task as started
     *
     * @param Task $task
     * @param Worker $worker
     * @return bool
     */
    public function markStarted(Task $task, Worker $worker): bool
    {
        if ($task->status !== 'assigned' || $task->worker_id !== $worker->id) {
            return false;
        }

        $task->update([
            'status' => 'running',
            'started_at' => now(),
        ]);

        TaskLog::info(
            $task->id,
            "Task execution started",
            $worker->id
        );

        return true;
    }

    /**
     * Mark task as completed
     *
     * @param Task $task
     * @param Worker $worker
     * @param array|null $result
     * @return bool
     */
    public function markCompleted(Task $task, Worker $worker, ?array $result = null): bool
    {
        if ($task->status !== 'running' || $task->worker_id !== $worker->id) {
            return false;
        }

        return DB::transaction(function () use ($task, $worker, $result) {
            $task->update([
                'status' => 'done',
                'completed_at' => now(),
            ]);

            // Update worker statistics
            $worker->increment('tasks_completed');
            $worker->update([
                'status' => 'idle',
                'current_task_id' => null,
            ]);

            // Log completion
            TaskLog::info(
                $task->id,
                "Task completed successfully",
                $worker->id,
                $result
            );

            // Update job progress
            app(JobStatusService::class)->recalculate($task->job);

            return true;
        });
    }

    /**
     * Mark task as failed
     *
     * @param Task $task
     * @param Worker $worker
     * @param string $reason
     * @return bool
     */
    public function markFailed(Task $task, Worker $worker, string $reason): bool
    {
        if ($task->worker_id !== $worker->id) {
            return false;
        }

        return DB::transaction(function () use ($task, $worker, $reason) {
            $task->update([
                'failure_reason' => $reason,
            ]);

            // Check if we can retry
            if ($task->canRetry()) {
                // Re-queue with exponential backoff
                $backoffSeconds = $task->calculateBackoffDelay();
                $task->update([
                    'status' => 'pending',
                    'worker_id' => null,
                    'retry_count' => $task->retry_count + 1,
                    'available_after' => now()->addSeconds($backoffSeconds),
                    'assigned_at' => null,
                    'started_at' => null,
                ]);

                TaskLog::warning(
                    $task->id,
                    "Task failed, will retry (attempt {$task->retry_count}/{$task->max_retries})",
                    $worker->id,
                    ['reason' => $reason, 'backoff_seconds' => $backoffSeconds]
                );
            } else {
                // Permanent failure
                $task->update([
                    'status' => 'failed',
                    'completed_at' => now(),
                ]);

                TaskLog::error(
                    $task->id,
                    "Task permanently failed after {$task->max_retries} retries",
                    $worker->id,
                    ['reason' => $reason]
                );
            }

            // Update worker statistics
            $worker->increment('tasks_failed');
            $worker->update([
                'status' => 'idle',
                'current_task_id' => null,
            ]);

            // Update job status
            app(JobStatusService::class)->recalculate($task->job);

            return true;
        });
    }
}
