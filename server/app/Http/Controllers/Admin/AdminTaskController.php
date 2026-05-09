<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Services\JobStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminTaskController extends Controller
{
    public function __construct(
        private JobStatusService $jobStatusService
    ) {}

    /**
     * List all tasks with filtering and pagination
     */
    public function index(Request $request): JsonResponse
    {
        $query = Task::with([
            'job:id,name,type,status,priority',
            'worker:id,worker_key,hostname,status',
        ]);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('job_id')) {
            $query->where('job_id', $request->job_id);
        }

        if ($request->has('worker_id')) {
            $query->where('worker_id', $request->worker_id);
        }

        if ($request->has('worker_key')) {
            $query->whereHas('worker', function ($workerQuery) use ($request) {
                $workerQuery->where('worker_key', $request->worker_key);
            });
        }

        if ($request->has('type')) {
            $query->whereHas('job', function ($jobQuery) use ($request) {
                $jobQuery->where('type', $request->type);
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = min($request->get('per_page', 25), 200);
        $tasks = $query->paginate($perPage);

        return response()->json($tasks);
    }

    /**
     * Get a specific task with relations
     */
    public function show(int $id): JsonResponse
    {
        $task = Task::with(['job', 'worker', 'logs'])->find($id);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found',
            ], 404);
        }

        return response()->json([
            'task' => $task,
        ]);
    }

    /**
     * Retry a failed task (admin only)
     */
    public function retry(int $id): JsonResponse
    {
        $task = Task::with('job')->find($id);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found',
            ], 404);
        }

        if ($task->status !== 'failed') {
            return response()->json([
                'message' => 'Only failed tasks can be retried',
                'status' => $task->status,
            ], 409);
        }

        if (!$task->canRetry()) {
            return response()->json([
                'message' => 'Task has reached the maximum retry limit',
                'retry_count' => $task->retry_count,
                'max_retries' => $task->max_retries,
            ], 409);
        }

        $task->update([
            'status' => 'pending',
            'worker_id' => null,
            'retry_count' => $task->retry_count + 1,
            'available_after' => now(),
            'assigned_at' => null,
            'started_at' => null,
            'completed_at' => null,
            'failure_reason' => null,
        ]);

        $this->jobStatusService->recalculate($task->job);

        return response()->json([
            'message' => 'Task retried successfully',
            'task' => $task->fresh(),
        ]);
    }
}
