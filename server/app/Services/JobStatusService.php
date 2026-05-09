<?php

namespace App\Services;

use App\Models\SchedulerJob;
use Illuminate\Support\Facades\DB;

class JobStatusService
{
    /**
     * Recalculate and update job status based on its tasks
     *
     * @param SchedulerJob $job
     * @return void
     */
    public function recalculate(SchedulerJob $job): void
    {
        DB::transaction(function () use ($job) {
            // Get task counts by status
            $taskCounts = DB::table('tasks')
                ->select('status', DB::raw('count(*) as count'))
                ->where('job_id', $job->id)
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $completedCount = $taskCounts['done'] ?? 0;
            $failedCount = $taskCounts['failed'] ?? 0;
            $cancelledCount = $taskCounts['cancelled'] ?? 0;
            $totalTasks = $job->total_tasks;

            // Update counts
            $job->update([
                'completed_tasks' => $completedCount,
                'failed_tasks' => $failedCount,
            ]);

            // Determine job status
            $newStatus = $this->determineJobStatus(
                $job->status,
                $totalTasks,
                $completedCount,
                $failedCount,
                $cancelledCount
            );

            if ($newStatus !== $job->status) {
                $updates = ['status' => $newStatus];

                // Set completed_at timestamp for terminal states
                if (in_array($newStatus, ['completed', 'failed', 'cancelled'])) {
                    $updates['completed_at'] = now();
                }

                $job->update($updates);

                // Log the job completion to the activity feed
                if ($newStatus === 'completed') {
                    \App\Models\TaskLog::info(
                        // Use the last completed task's ID as anchor
                        $job->tasks()->where('status', 'done')->latest('completed_at')->value('id') ?? 0,
                        "Job \"{$job->name}\" completed — all {$job->total_tasks} tasks done",
                        null,
                        [
                            'job_id'          => $job->id,
                            'job_name'        => $job->name,
                            'total_tasks'     => $job->total_tasks,
                            'completed_tasks' => $completedCount,
                            'completed_at'    => now()->toIso8601String(),
                        ]
                    );
                } elseif ($newStatus === 'failed') {
                    \App\Models\TaskLog::error(
                        $job->tasks()->where('status', 'failed')->latest('completed_at')->value('id') ?? 0,
                        "Job \"{$job->name}\" failed — {$failedCount} task(s) permanently failed",
                        null,
                        [
                            'job_id'       => $job->id,
                            'job_name'     => $job->name,
                            'failed_tasks' => $failedCount,
                        ]
                    );
                }
            }
        });
    }

    /**
     * Determine the appropriate job status
     *
     * @param string $currentStatus
     * @param int $totalTasks
     * @param int $completedCount
     * @param int $failedCount
     * @param int $cancelledCount
     * @return string
     */
    private function determineJobStatus(
        string $currentStatus,
        int $totalTasks,
        int $completedCount,
        int $failedCount,
        int $cancelledCount
    ): string {
        // If job is already cancelled, keep it cancelled
        if ($currentStatus === 'cancelled') {
            return 'cancelled';
        }

        // All tasks are in terminal state
        $terminalCount = $completedCount + $failedCount + $cancelledCount;

        if ($terminalCount === $totalTasks) {
            // All tasks completed successfully
            if ($completedCount === $totalTasks) {
                return 'completed';
            }

            // Some tasks failed permanently
            if ($failedCount > 0) {
                return 'failed';
            }

            // All tasks were cancelled
            if ($cancelledCount === $totalTasks) {
                return 'cancelled';
            }

            // Mixed terminal states - consider it failed
            return 'failed';
        }

        // Job is still in progress
        if ($completedCount > 0 || $failedCount > 0) {
            return 'running';
        }

        // No tasks have been picked up yet
        return 'pending';
    }

    /**
     * Cancel a job and all its pending/assigned tasks
     *
     * @param SchedulerJob $job
     * @return bool
     */
    public function cancel(SchedulerJob $job): bool
    {
        if (!$job->canBeCancelled()) {
            return false;
        }

        return DB::transaction(function () use ($job) {
            // Cancel all pending and assigned tasks
            DB::table('tasks')
                ->where('job_id', $job->id)
                ->whereIn('status', ['pending', 'assigned'])
                ->update([
                    'status' => 'cancelled',
                    'completed_at' => now(),
                    'updated_at' => now(),
                ]);

            // Update job status
            $job->update([
                'status' => 'cancelled',
                'completed_at' => now(),
            ]);

            return true;
        });
    }
}
