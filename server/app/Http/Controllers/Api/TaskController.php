<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Services\TaskClaimService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function __construct(
        private TaskClaimService $claimService
    ) {}

    /**
     * Claim the next available task (atomic pull operation)
     * Worker calls this to ask: "Give me a task"
     */
    public function next(Request $request): JsonResponse
    {
        $worker = $request->get('authenticated_worker');

        if (!$worker) {
            return response()->json(['message' => 'Worker authentication required'], 401);
        }

        $task = $this->claimService->claimNext($worker->worker_key);

        if (!$task) {
            return response()->json([
                'message'    => 'No tasks available',
                'worker_key' => $worker->worker_key,
                'status'     => 'idle',
            ], 204);
        }

        return response()->json([
            'message'    => 'Task claimed',
            'worker_key' => $worker->worker_key,
            'task'       => $task,
            // Tell the worker exactly what to do
            'next_step'  => "POST /api/tasks/{$task->id}/start",
        ]);
    }

    /**
     * Get current pull-based activity snapshot (public — for the dashboard)
     * Shows: which workers are pulling which tasks right now
     */
    public function activity(): JsonResponse
    {
        $activity = $this->claimService->getCurrentActivity();
        return response()->json($activity);
    }

    /**
     * Mark task as started
     */
    public function start(Request $request, int $id): JsonResponse
    {
        $task = Task::find($id);
        if (!$task) {
            return response()->json([
                'message' => 'Task not found',
            ], 404);
        }

        $worker = $request->get('authenticated_worker');
        if (!$worker) {
            return response()->json([
                'message' => 'Worker authentication required',
            ], 401);
        }

        $success = $this->claimService->markStarted($task, $worker);

        if (!$success) {
            return response()->json([
                'message' => 'Task cannot be started in its current state',
                'status' => $task->status,
            ], 409);
        }

        return response()->json([
            'message' => 'Task started',
            'task' => $task->fresh(),
        ]);
    }

    /**
     * Mark task as completed
     */
    public function complete(Request $request, int $id): JsonResponse
    {
        $task = Task::find($id);
        if (!$task) {
            return response()->json([
                'message' => 'Task not found',
            ], 404);
        }

        $worker = $request->get('authenticated_worker');
        if (!$worker) {
            return response()->json([
                'message' => 'Worker authentication required',
            ], 401);
        }

        $result = $request->get('result', []);
        if (!is_array($result)) {
            $result = [];
        }
        if ($request->has('duration_ms')) {
            $result['duration_ms'] = $request->duration_ms;
        }

        $success = $this->claimService->markCompleted($task, $worker, $result);

        if (!$success) {
            return response()->json([
                'message' => 'Task cannot be completed in its current state',
                'status' => $task->status,
            ], 409);
        }

        return response()->json([
            'message' => 'Task completed',
            'task' => $task->fresh(),
        ]);
    }

    /**
     * Mark task as failed
     */
    public function fail(Request $request, int $id): JsonResponse
    {
        $task = Task::find($id);
        if (!$task) {
            return response()->json([
                'message' => 'Task not found',
            ], 404);
        }

        $worker = $request->get('authenticated_worker');
        if (!$worker) {
            return response()->json([
                'message' => 'Worker authentication required',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $success = $this->claimService->markFailed($task, $worker, $request->reason);

        if (!$success) {
            return response()->json([
                'message' => 'Task cannot be marked as failed',
            ], 409);
        }

        return response()->json([
            'message' => 'Task marked as failed',
            'task' => $task->fresh(),
            'will_retry' => $task->status === 'pending',
        ]);
    }

    /**
     * Update task with processed images
     */
    public function updateImages(Request $request, int $id): JsonResponse
    {
        $task = Task::find($id);
        if (!$task) {
            return response()->json([
                'message' => 'Task not found',
            ], 404);
        }

        $worker = $request->get('authenticated_worker');
        if (!$worker) {
            return response()->json([
                'message' => 'Worker authentication required',
            ], 401);
        }

        if ($task->worker_id !== $worker->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'output_images' => 'required|array',
            'images_processed' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $task->update([
            'output_images' => $request->output_images,
            'images_processed' => $request->images_processed,
        ]);

        return response()->json([
            'message' => 'Task images updated',
            'task' => $task->fresh(),
        ]);
    }
}
