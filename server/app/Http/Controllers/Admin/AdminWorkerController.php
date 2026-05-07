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
     * List all workers with detailed information
     */
    public function index(Request $request): JsonResponse
    {
        $query = Worker::with('currentTask');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'last_heartbeat_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $workers = $query->get();

        return response()->json([
            'workers' => $workers,
            'total' => $workers->count(),
            'active' => $workers->where('status', 'active')->count(),
            'idle' => $workers->where('status', 'idle')->count(),
            'dead' => $workers->where('status', 'dead')->count(),
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
