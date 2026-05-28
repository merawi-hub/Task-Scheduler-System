<?php

namespace App\Services;

use App\Models\Task;
use App\Models\Worker;
use App\Models\TaskLog;
use Illuminate\Support\Facades\DB;

/**
 * TaskClaimService — Pull-Based Scheduling
 *
 * Workers PULL tasks. The coordinator never pushes.
 *
 * Pull flow per worker:
 *   1. Worker calls  GET /tasks/next          → coordinator finds next pending task
 *   2. Coordinator atomically assigns it      → status: pending → assigned
 *   3. Worker calls  POST /tasks/{id}/start   → status: assigned → running
 *   4. Worker calls  POST /tasks/{id}/complete → status: running → done
 *      OR            POST /tasks/{id}/fail    → retry or permanent failure
 *
 * Race condition prevention:
 *   SELECT ... FOR UPDATE inside a transaction ensures only ONE worker
 *   gets each task even if 100 workers ask simultaneously.
 */
class TaskClaimService
{
    // =========================================================================
    // PULL — Worker asks: "Give me a task"
    // =========================================================================

    /**
     * Atomically claim the next available task for a worker.
     *
     * Priority order:
     *   1. Higher job priority first  (scheduler_jobs.priority DESC)
     *   2. Older tasks first          (tasks.created_at ASC)
     *   3. Only tasks whose available_after has passed (retry backoff)
     *
     * @param  string  $workerKey
     * @return Task|null  null = no tasks available right now
     */
    public function claimNext(string $workerKey): ?Task
    {
        $worker = Worker::where('worker_key', $workerKey)->first();

        if (!$worker) {
            return null;
        }

        return DB::transaction(function () use ($worker) {

            // ── Atomic SELECT FOR UPDATE ──────────────────────────────────────
            // Locks the row so no other worker can claim the same task.
            $task = Task::query()
                ->select('tasks.*')
                ->join('scheduler_jobs', 'tasks.job_id', '=', 'scheduler_jobs.id')
                ->where('tasks.status', 'pending')
                ->whereIn('scheduler_jobs.status', ['pending', 'running'])
                ->where(function ($q) {
                    $q->whereNull('tasks.available_after')
                      ->orWhere('tasks.available_after', '<=', now());
                })
                ->orderByDesc('scheduler_jobs.priority')   // highest priority first
                ->orderBy('tasks.created_at')              // oldest task first
                ->lockForUpdate()
                ->first();

            if (!$task) {
                return null;
            }

            // ── pending → assigned ────────────────────────────────────────────
            $task->update([
                'status'      => 'assigned',
                'worker_id'   => $worker->id,
                'assigned_at' => now(),
            ]);

            // ── Worker becomes busy ───────────────────────────────────────────
            $worker->update([
                'status'          => 'busy',
                'current_task_id' => $task->id,
            ]);

            // ── Audit log ─────────────────────────────────────────────────────
            TaskLog::info(
                $task->id,
                "Task claimed by worker {$worker->worker_key} (pull-based)",
                $worker->id,
                [
                    'worker_key'  => $worker->worker_key,
                    'hostname'    => $worker->hostname,
                    'pull_time'   => now()->toIso8601String(),
                ]
            );

            // ── First task of this job → job becomes running ──────────────────
            $job = $task->job;
            if ($job->status === 'pending') {
                $job->update([
                    'status'     => 'running',
                    'started_at' => now(),
                ]);

                // Send job started notification
                app(\App\Services\NotificationService::class)->notifyJobStarted($job);
            }

            // ── Normalize payload before returning to worker ──────────────────
            $task = $this->normalizeTaskPayload($task);

            return $task->fresh(['job', 'worker']);
        });
    }

    // =========================================================================
    // assigned → running
    // =========================================================================

    /**
     * Worker signals it has started processing the task.
     *
     * @param  Task    $task
     * @param  Worker  $worker
     * @return bool
     */
    public function markStarted(Task $task, Worker $worker): bool
    {
        // Guard: only the assigned worker can start it
        if ($task->status !== 'assigned' || $task->worker_id !== $worker->id) {
            return false;
        }

        $task->update([
            'status'     => 'running',
            'started_at' => now(),
        ]);

        TaskLog::info(
            $task->id,
            "Worker {$worker->worker_key} started executing task",
            $worker->id,
            ['started_at' => now()->toIso8601String()]
        );

        return true;
    }

    // =========================================================================
    // running → done
    // =========================================================================

    /**
     * Worker signals successful completion.
     *
     * @param  Task        $task
     * @param  Worker      $worker
     * @param  array|null  $result   Optional result payload from the worker
     * @return bool
     */
    public function markCompleted(Task $task, Worker $worker, ?array $result = null): bool
    {
        if ($task->status !== 'running' || $task->worker_id !== $worker->id) {
            return false;
        }

        return DB::transaction(function () use ($task, $worker, $result) {

            $task->update([
                'status'       => 'done',
                'completed_at' => now(),
            ]);

            // ── Worker goes back to idle, ready to pull the next task ─────────
            $worker->increment('tasks_completed');
            $worker->update([
                'status'          => 'idle',
                'current_task_id' => null,
            ]);

            TaskLog::info(
                $task->id,
                "Task completed by worker {$worker->worker_key}",
                $worker->id,
                array_merge($result ?? [], [
                    'completed_at'   => now()->toIso8601String(),
                    'duration_ms'    => $task->started_at
                        ? $task->started_at->diffInMilliseconds(now())
                        : null,
                ])
            );

            // ── Recalculate job progress ──────────────────────────────────────
            app(JobStatusService::class)->recalculate($task->job);

            return true;
        });
    }

    // =========================================================================
    // running → pending (retry) OR running → failed (permanent)
    // =========================================================================

    /**
     * Worker signals failure.
     *
     * If retries remain → re-queue with exponential backoff (pending).
     * If no retries left → permanent failure.
     *
     * Backoff formula: 2^retry_count × 5 seconds
     *   Attempt 1 → 5s,  Attempt 2 → 10s,  Attempt 3 → 20s
     *
     * @param  Task    $task
     * @param  Worker  $worker
     * @param  string  $reason
     * @return bool
     */
    public function markFailed(Task $task, Worker $worker, string $reason): bool
    {
        if ($task->worker_id !== $worker->id) {
            return false;
        }

        return DB::transaction(function () use ($task, $worker, $reason) {

            $task->update(['failure_reason' => $reason]);

            if ($task->canRetry()) {
                // ── Exponential backoff re-queue ──────────────────────────────
                $backoffSeconds = $task->calculateBackoffDelay();

                $task->update([
                    'status'          => 'pending',
                    'worker_id'       => null,
                    'retry_count'     => $task->retry_count + 1,
                    'available_after' => now()->addSeconds($backoffSeconds),
                    'assigned_at'     => null,
                    'started_at'      => null,
                ]);

                TaskLog::warning(
                    $task->id,
                    "Task failed — will retry in {$backoffSeconds}s "
                        . "(attempt {$task->retry_count}/{$task->max_retries})",
                    $worker->id,
                    [
                        'reason'          => $reason,
                        'backoff_seconds' => $backoffSeconds,
                        'retry_count'     => $task->retry_count,
                    ]
                );
            } else {
                // ── Permanent failure ─────────────────────────────────────────
                $task->update([
                    'status'       => 'failed',
                    'completed_at' => now(),
                ]);

                TaskLog::error(
                    $task->id,
                    "Task permanently failed after {$task->max_retries} retries",
                    $worker->id,
                    ['reason' => $reason]
                );

                // Notify admins about task retry limit reached
                app(\App\Services\NotificationService::class)->notifyTaskRetryLimitReached(
                    $task->job,
                    $task->id
                );
            }

            // ── Worker goes back to idle ──────────────────────────────────────
            $worker->increment('tasks_failed');
            $worker->update([
                'status'          => 'idle',
                'current_task_id' => null,
            ]);

            // ── Recalculate job status ────────────────────────────────────────
            app(JobStatusService::class)->recalculate($task->job);

            return true;
        });
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Normalize task payload to ensure all required fields exist with safe defaults.
     * This prevents "Undefined array key" errors when workers access payload fields.
     *
     * @param Task $task
     * @return Task
     */
    private function normalizeTaskPayload(Task $task): Task
    {
        $payload = $task->payload ?? [];

        // Ensure payload is an array (handle cases where it might be a string)
        if (is_string($payload)) {
            $payload = json_decode($payload, true) ?? [];
        }
        if (!is_array($payload)) {
            $payload = [];
        }

        // Check if payload is missing required fields
        $requiredFields = ['start_index', 'end_index', 'records_count'];
        $missingFields = array_filter($requiredFields, fn($field) => !isset($payload[$field]));

        if (!empty($missingFields)) {
            // Log warning for debugging
            \Illuminate\Support\Facades\Log::warning(
                "Task {$task->id} has incomplete payload, normalizing missing fields: " . implode(', ', $missingFields),
                [
                    'task_id' => $task->id,
                    'job_id' => $task->job_id,
                    'missing_fields' => $missingFields,
                    'original_payload' => $payload,
                ]
            );
        }

        // Ensure all required fields exist with safe defaults
        $normalizedPayload = array_merge([
            'start_index'   => 0,
            'end_index'     => 0,
            'record_from'   => 1,
            'record_to'     => 1,
            'records_count' => 0,
            'total_records' => 0,
            'operations'    => [],
        ], $payload);

        // Calculate derived fields if they're missing
        if (!isset($payload['record_from']) && isset($normalizedPayload['start_index'])) {
            $normalizedPayload['record_from'] = $normalizedPayload['start_index'] + 1;
        }
        if (!isset($payload['record_to']) && isset($normalizedPayload['end_index'])) {
            $normalizedPayload['record_to'] = $normalizedPayload['end_index'] + 1;
        }

        // Update and save the normalized payload
        $task->payload = $normalizedPayload;
        $task->save();

        return $task;
    }

    /**
     * Get a snapshot of the current pull-based activity:
     * which workers are pulling which tasks right now,
     * plus parallel execution metrics and load balancing stats.
     *
     * Load balancing is AUTOMATIC — fast workers pull more tasks,
     * slow workers pull fewer. No manual assignment needed.
     *
     * @return array
     */
    public function getCurrentActivity(): array
    {
        $busyWorkers = Worker::where('status', 'busy')
            ->with(['currentTask.job'])
            ->get();

        $idleWorkers = Worker::where('status', 'idle')
            ->orderBy('last_heartbeat_at', 'desc')
            ->get();

        $allWorkers = Worker::where('status', '!=', 'dead')->get();

        // Dead workers (recently died — last 10 minutes)
        $deadWorkers = Worker::where('status', 'dead')
            ->where('updated_at', '>=', now()->subMinutes(10))
            ->get();

        $pendingTaskCount = Task::where('status', 'pending')
            ->whereHas('job', fn($q) => $q->whereIn('status', ['pending', 'running']))
            ->where(fn($q) => $q->whereNull('available_after')
                                ->orWhere('available_after', '<=', now()))
            ->count();

        // ── Parallel execution metrics ────────────────────────────────────────
        $recentDone = Task::where('status', 'done')
            ->where('completed_at', '>=', now()->subSeconds(60))
            ->count();

        $throughputPerSecond = round($recentDone / 60, 2);

        $avgDurationMs = Task::where('status', 'done')
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->orderBy('completed_at', 'desc')
            ->limit(100)
            ->get()
            ->avg(fn($t) => $t->started_at->diffInMilliseconds($t->completed_at));

        $activeWorkerCount = $allWorkers->count();
        $speedMultiplier   = max(1, $activeWorkerCount);

        $recordsInFlight = $busyWorkers->sum(function ($w) {
            if (!$w->currentTask) return 0;
            $payload = $w->currentTask->payload ?? [];
            return $payload['records_count'] ?? $payload['items_count'] ?? 0;
        });

        $totalRecordsDone = Task::where('status', 'done')
            ->whereHas('job', fn($q) => $q->where('type', 'result_processing'))
            ->get()
            ->sum(fn($t) => $t->payload['records_count'] ?? 0);

        // ── Load balancing stats ──────────────────────────────────────────────
        // For each worker, compute how many tasks they've done and their avg speed.
        // Fast workers naturally accumulate more tasks — that IS load balancing.
        $totalTasksDone = $allWorkers->sum('tasks_completed');

        $loadBalanceStats = $allWorkers->map(function ($w) use ($totalTasksDone, $avgDurationMs) {
            // Per-worker average task duration (last 20 tasks)
            $workerAvgMs = Task::where('worker_id', $w->id)
                ->where('status', 'done')
                ->whereNotNull('started_at')
                ->whereNotNull('completed_at')
                ->orderBy('completed_at', 'desc')
                ->limit(20)
                ->get()
                ->avg(fn($t) => $t->started_at->diffInMilliseconds($t->completed_at));

            $sharePercent = $totalTasksDone > 0
                ? round(($w->tasks_completed / $totalTasksDone) * 100)
                : 0;

            // Speed label relative to average
            $speedLabel = 'normal';
            if ($workerAvgMs && $avgDurationMs) {
                if ($workerAvgMs < $avgDurationMs * 0.8)  $speedLabel = 'fast';
                if ($workerAvgMs > $avgDurationMs * 1.2)  $speedLabel = 'slow';
            }

            return [
                'worker_key'       => $w->worker_key,
                'hostname'         => $w->hostname,
                'status'           => $w->status,
                'tasks_completed'  => $w->tasks_completed,
                'tasks_failed'     => $w->tasks_failed,
                'share_percent'    => $sharePercent,
                'avg_duration_ms'  => $workerAvgMs ? (int) round($workerAvgMs) : null,
                'speed_label'      => $speedLabel,
                'last_heartbeat'   => $w->last_heartbeat_at?->toIso8601String(),
            ];
        })
        ->sortByDesc('tasks_completed')
        ->values()
        ->toArray();

        return [
            'busy_workers' => $busyWorkers->map(fn($w) => [
                'worker_key'      => $w->worker_key,
                'hostname'        => $w->hostname,
                'status'          => $w->status,
                'current_task'    => $w->currentTask ? [
                    'id'           => $w->currentTask->id,
                    'task_index'   => $w->currentTask->task_index,
                    'task_number'  => $w->currentTask->task_index + 1,
                    'record_from'  => $w->currentTask->payload['record_from'] ?? null,
                    'record_to'    => $w->currentTask->payload['record_to']   ?? null,
                    'records_count'=> $w->currentTask->payload['records_count'] ?? null,
                    'status'       => $w->currentTask->status,
                    'job_id'       => $w->currentTask->job_id,
                    'job_name'     => $w->currentTask->job?->name,
                    'started_at'   => $w->currentTask->started_at?->toIso8601String(),
                    'elapsed_ms'   => $w->currentTask->started_at
                        ? $w->currentTask->started_at->diffInMilliseconds(now())
                        : 0,
                    'timeout_ms'   => ($w->currentTask->timeout_seconds ?? 300) * 1000,
                ] : null,
                'tasks_completed' => $w->tasks_completed,
                'tasks_failed'    => $w->tasks_failed,
                'last_heartbeat'  => $w->last_heartbeat_at?->toIso8601String(),
            ])->values()->toArray(),

            'idle_workers' => $idleWorkers->map(fn($w) => [
                'worker_key'      => $w->worker_key,
                'hostname'        => $w->hostname,
                'status'          => $w->status,
                'tasks_completed' => $w->tasks_completed,
                'tasks_failed'    => $w->tasks_failed,
                'last_heartbeat'  => $w->last_heartbeat_at?->toIso8601String(),
            ])->values()->toArray(),

            // Recently dead workers (last 10 min) — shown in fault tolerance UI
            'dead_workers' => $deadWorkers->map(fn($w) => [
                'worker_key'      => $w->worker_key,
                'hostname'        => $w->hostname,
                'status'          => 'dead',
                'tasks_completed' => $w->tasks_completed,
                'tasks_failed'    => $w->tasks_failed,
                'died_at'         => $w->updated_at?->toIso8601String(),
                'last_heartbeat'  => $w->last_heartbeat_at?->toIso8601String(),
            ])->values()->toArray(),

            'pending_tasks_available' => $pendingTaskCount,
            'total_workers'           => $allWorkers->count() + $deadWorkers->count(),

            // ── Parallel execution metrics ────────────────────────────────────
            'parallel' => [
                'active_workers'         => $activeWorkerCount,
                'busy_workers'           => $busyWorkers->count(),
                'speed_multiplier'       => $speedMultiplier,
                'throughput_per_sec'     => $throughputPerSecond,
                'avg_task_duration_ms'   => $avgDurationMs ? (int) round($avgDurationMs) : null,
                'records_in_flight'      => $recordsInFlight,
                'total_records_done'     => $totalRecordsDone,
                'sequential_estimate_ms' => $avgDurationMs && $pendingTaskCount > 0
                    ? (int) round($avgDurationMs * ($pendingTaskCount + $busyWorkers->count()))
                    : null,
                'parallel_estimate_ms'   => $avgDurationMs && $pendingTaskCount > 0 && $activeWorkerCount > 0
                    ? (int) round(($avgDurationMs * ($pendingTaskCount + $busyWorkers->count())) / $activeWorkerCount)
                    : null,
            ],

            // ── Load balancing stats ──────────────────────────────────────────
            // Fast workers naturally do more tasks — this IS automatic load balancing.
            'load_balance' => [
                'total_tasks_done'  => $totalTasksDone,
                'worker_stats'      => $loadBalanceStats,
                'explanation'       => 'Fast workers finish sooner and immediately pull the next task. '
                    . 'No manual assignment needed — the pull-based system balances automatically.',
            ],

            'snapshot_at' => now()->toIso8601String(),
        ];
    }
}
