<?php

namespace App\Services;

use App\Models\Worker;
use App\Models\Task;
use App\Models\TaskLog;
use Illuminate\Support\Facades\DB;

class WorkerHealthService
{
    /**
     * Detect and handle dead workers
     *
     * @return array Statistics about detected dead workers
     */
    public function detectDeadWorkers(): array
    {
        $threshold = config('scheduler.worker_dead_threshold', 45);
        $deadlineTime = now()->subSeconds($threshold);

        $deadWorkers = Worker::where('status', '!=', 'dead')
            ->where(function ($query) use ($deadlineTime) {
                $query->where('last_heartbeat_at', '<', $deadlineTime)
                    ->orWhereNull('last_heartbeat_at');
            })
            ->get();

        $stats = [
            'dead_workers_count' => 0,
            'reassigned_tasks_count' => 0,
            'workers' => [],
        ];

        foreach ($deadWorkers as $worker) {
            $reassignedCount = $this->handleDeadWorker($worker);
            
            $stats['dead_workers_count']++;
            $stats['reassigned_tasks_count'] += $reassignedCount;
            $stats['workers'][] = [
                'worker_key' => $worker->worker_key,
                'reassigned_tasks' => $reassignedCount,
            ];
        }

        return $stats;
    }

    /**
     * Handle a single dead worker
     *
     * @param Worker $worker
     * @return int Number of tasks reassigned
     */
    private function handleDeadWorker(Worker $worker): int
    {
        return DB::transaction(function () use ($worker) {
            // Mark worker as dead
            $worker->markAsDead();

            // Find all tasks assigned to or running on this worker
            $tasks = Task::where('worker_id', $worker->id)
                ->whereIn('status', ['assigned', 'running'])
                ->get();

            $reassignedCount = 0;

            foreach ($tasks as $task) {
                // Re-queue the task with retry logic
                if ($task->canRetry()) {
                    $backoffSeconds = $task->calculateBackoffDelay();
                    
                    $task->update([
                        'status' => 'pending',
                        'worker_id' => null,
                        'retry_count' => $task->retry_count + 1,
                        'available_after' => now()->addSeconds($backoffSeconds),
                        'failure_reason' => "Worker {$worker->worker_key} died",
                        'assigned_at' => null,
                        'started_at' => null,
                    ]);

                    TaskLog::warning(
                        $task->id,
                        "Task reassigned due to dead worker (attempt {$task->retry_count}/{$task->max_retries})",
                        null,
                        [
                            'dead_worker' => $worker->worker_key,
                            'backoff_seconds' => $backoffSeconds,
                        ]
                    );

                    $reassignedCount++;
                } else {
                    // Permanent failure
                    $task->update([
                        'status' => 'failed',
                        'worker_id' => null,
                        'completed_at' => now(),
                        'failure_reason' => "Worker {$worker->worker_key} died, max retries exceeded",
                    ]);

                    TaskLog::error(
                        $task->id,
                        "Task permanently failed due to dead worker after {$task->max_retries} retries",
                        null,
                        ['dead_worker' => $worker->worker_key]
                    );
                }

                // Update job status
                app(JobStatusService::class)->recalculate($task->job);
            }

            return $reassignedCount;
        });
    }

    /**
     * Detect and handle timed-out tasks
     *
     * @return array Statistics about timed-out tasks
     */
    public function detectTimedOutTasks(): array
    {
        $timedOutTasks = Task::where('status', 'running')
            ->whereNotNull('started_at')
            ->get()
            ->filter(function ($task) {
                return $task->hasTimedOut();
            });

        $stats = [
            'timed_out_count' => 0,
            'tasks' => [],
        ];

        foreach ($timedOutTasks as $task) {
            $this->handleTimedOutTask($task);
            
            $stats['timed_out_count']++;
            $stats['tasks'][] = [
                'task_id' => $task->id,
                'job_id' => $task->job_id,
                'worker_key' => $task->worker?->worker_key,
            ];
        }

        return $stats;
    }

    /**
     * Handle a single timed-out task
     *
     * @param Task $task
     * @return void
     */
    private function handleTimedOutTask(Task $task): void
    {
        DB::transaction(function () use ($task) {
            $worker = $task->worker;

            // Re-queue the task with retry logic
            if ($task->canRetry()) {
                $backoffSeconds = $task->calculateBackoffDelay();
                
                $task->update([
                    'status' => 'pending',
                    'worker_id' => null,
                    'retry_count' => $task->retry_count + 1,
                    'available_after' => now()->addSeconds($backoffSeconds),
                    'failure_reason' => 'Task timeout',
                    'assigned_at' => null,
                    'started_at' => null,
                ]);

                TaskLog::warning(
                    $task->id,
                    "Task timed out and reassigned (attempt {$task->retry_count}/{$task->max_retries})",
                    $worker?->id,
                    [
                        'timeout_seconds' => $task->timeout_seconds,
                        'backoff_seconds' => $backoffSeconds,
                    ]
                );
            } else {
                // Permanent failure
                $task->update([
                    'status' => 'failed',
                    'worker_id' => null,
                    'completed_at' => now(),
                    'failure_reason' => 'Task timeout, max retries exceeded',
                ]);

                TaskLog::error(
                    $task->id,
                    "Task permanently failed due to timeout after {$task->max_retries} retries",
                    $worker?->id,
                    ['timeout_seconds' => $task->timeout_seconds]
                );
            }

            // Update worker status if it exists
            if ($worker) {
                $worker->increment('tasks_failed');
                $worker->update([
                    'status' => 'idle',
                    'current_task_id' => null,
                ]);
            }

            // Update job status
            app(JobStatusService::class)->recalculate($task->job);
        });
    }
}
