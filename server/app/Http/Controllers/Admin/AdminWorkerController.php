<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminWorkerController extends Controller
{
    /**
     * List all workers with detailed information + heartbeat health
     */
    public function index(Request $request): JsonResponse
    {
        $query = Worker::with('currentTask');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $sortBy    = $request->get('sort_by', 'last_heartbeat_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $workers     = $query->get();
        $threshold   = config('scheduler.worker_dead_threshold', 45);
        $activeCount = $workers->whereIn('status', ['idle', 'busy'])->count();

        // Enrich each worker with heartbeat health data
        $workers->each(function ($worker) use ($threshold) {
            $secondsSince = $worker->last_heartbeat_at
                ? (int) $worker->last_heartbeat_at->diffInSeconds(now())
                : null;

            $worker->seconds_since_heartbeat = $secondsSince;
            $worker->is_dead                 = $worker->isDead();

            // Heartbeat health: healthy / warning / critical / dead
            $worker->heartbeat_health = match (true) {
                $secondsSince === null                    => 'unknown',
                $secondsSince <= 20                       => 'healthy',
                $secondsSince <= 35                       => 'warning',
                $secondsSince <= $threshold               => 'critical',
                default                                   => 'dead',
            };

            // Seconds until declared dead (countdown)
            $worker->seconds_until_dead = $secondsSince !== null && $secondsSince < $threshold
                ? $threshold - $secondsSince
                : 0;
        });

        return response()->json([
            'workers'  => $workers,
            'total'    => $workers->count(),
            'active'   => $activeCount,
            'idle'     => $workers->where('status', 'idle')->count(),
            'dead'     => $workers->where('status', 'dead')->count(),
            'threshold_seconds' => $threshold,
        ]);
    }

    /**
     * Fault tolerance snapshot — shows heartbeat status, dead workers,
     * and recently recovered tasks. Used by the FaultTolerancePanel.
     */
    public function faultTolerance(): JsonResponse
    {
        $threshold   = config('scheduler.worker_dead_threshold', 45);
        $deadlineTime = now()->subSeconds($threshold);

        // All workers with heartbeat data
        $allWorkers = Worker::with('currentTask')->get();

        $allWorkers->each(function ($w) use ($threshold) {
            $s = $w->last_heartbeat_at ? (int) $w->last_heartbeat_at->diffInSeconds(now()) : null;
            $w->seconds_since_heartbeat = $s;
            $w->seconds_until_dead      = ($s !== null && $s < $threshold) ? $threshold - $s : 0;
            $w->heartbeat_health        = match (true) {
                $s === null          => 'unknown',
                $s <= 20             => 'healthy',
                $s <= 35             => 'warning',
                $s <= $threshold     => 'critical',
                default              => 'dead',
            };
        });

        // Tasks that were recovered (re-queued) due to dead workers — last 24h
        $recoveredTasks = Task::where('failure_reason', 'like', '%died%')
            ->orWhere('failure_reason', 'like', '%dead%')
            ->where('updated_at', '>=', now()->subDay())
            ->with('job:id,name')
            ->orderBy('updated_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn($t) => [
                'task_id'        => $t->id,
                'job_id'         => $t->job_id,
                'job_name'       => $t->job?->name,
                'status'         => $t->status,
                'failure_reason' => $t->failure_reason,
                'retry_count'    => $t->retry_count,
                'recovered_at'   => $t->updated_at->toIso8601String(),
            ]);

        // Dead workers in the last 24h (from task logs)
        $recentDeathEvents = \App\Models\TaskLog::where('message', 'like', '%dead worker%')
            ->orWhere('message', 'like', '%died%')
            ->where('logged_at', '>=', now()->subDay())
            ->orderBy('logged_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($log) => [
                'message'   => $log->message,
                'level'     => $log->level,
                'logged_at' => $log->logged_at->toIso8601String(),
                'context'   => $log->context,
            ]);

        return response()->json([
            'threshold_seconds'   => $threshold,
            'heartbeat_interval'  => config('scheduler.heartbeat_interval', 30),
            'workers'             => $allWorkers->map(fn($w) => [
                'worker_key'             => $w->worker_key,
                'hostname'               => $w->hostname,
                'status'                 => $w->status,
                'heartbeat_health'       => $w->heartbeat_health,
                'seconds_since_heartbeat'=> $w->seconds_since_heartbeat,
                'seconds_until_dead'     => $w->seconds_until_dead,
                'last_heartbeat_at'      => $w->last_heartbeat_at?->toIso8601String(),
                'tasks_completed'        => $w->tasks_completed,
                'tasks_failed'           => $w->tasks_failed,
                'current_task_id'        => $w->current_task_id,
            ])->values()->toArray(),
            'recovered_tasks'     => $recoveredTasks,
            'recent_death_events' => $recentDeathEvents,
            'snapshot_at'         => now()->toIso8601String(),
        ]);
    }

    /**
     * Get detailed information about a specific worker
     */
    public function show(string $key): JsonResponse
    {
        $worker = Worker::with(['currentTask', 'tasks' => function ($query) {
            $query->orderBy('updated_at', 'desc')->limit(50);
        }])->where('worker_key', $key)->first();

        if (!$worker) {
            return response()->json([
                'message' => 'Worker not found',
            ], 404);
        }

        return response()->json([
            'worker' => $worker,
            'recent_tasks' => $worker->tasks,
        ]);
    }

    /**
     * Mark a worker as dead (admin action)
     */
    public function markDead(string $key): JsonResponse
    {
        $worker = Worker::where('worker_key', $key)->first();

        if (!$worker) {
            return response()->json([
                'message' => 'Worker not found',
            ], 404);
        }

        $worker->markAsDead();

        // Release any tasks assigned to this worker
        Task::where('worker_id', $worker->id)
            ->where('status', 'running')
            ->update([
                'status' => 'pending',
                'worker_id' => null,
                'started_at' => null,
            ]);

        return response()->json([
            'message' => 'Worker marked as dead and tasks released',
            'worker' => $worker->fresh(),
        ]);
    }

    /**
     * Remove a worker from the system
     */
    public function destroy(string $key): JsonResponse
    {
        $worker = Worker::where('worker_key', $key)->first();

        if (!$worker) {
            return response()->json([
                'message' => 'Worker not found',
            ], 404);
        }

        // Release any running tasks
        Task::where('worker_id', $worker->id)
            ->where('status', 'running')
            ->update([
                'status' => 'pending',
                'worker_id' => null,
                'started_at' => null,
            ]);

        $worker->delete();

        return response()->json([
            'message' => 'Worker removed from system',
        ]);
    }

    /**
     * Get worker statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_workers' => Worker::count(),
            'by_status' => Worker::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status'),
            'total_tasks_completed' => Worker::sum('tasks_completed'),
            'total_tasks_failed' => Worker::sum('tasks_failed'),
            'average_tasks_per_worker' => Worker::avg('tasks_completed'),
        ];

        return response()->json($stats);
    }
}
