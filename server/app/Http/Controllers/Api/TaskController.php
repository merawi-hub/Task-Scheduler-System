<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Worker;
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
     * Claim the next available task (atomic operation)
     */
    public function next(Request $request): JsonResponse
    {
        $workerKey = $request->header('X-Worker-Key');

        if (!$workerKey) {
            return response()->json([
                'message' => 'X-Worker-Key header is required',
            ], 401);
        }

        $task = $this->claimService->claimNext($workerKey);

        if (!$task) {
            return response()->json([
                'message' => 'No tasks available',
            ], 204);
        }

        return response()->json([
            'task' => $task,
        ]);
    }

    /**
     * Mark task as started
     */
    public function start(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'worker_key' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $task = Task::find($id);
        if (!$task) {
            return response()->json([
                'message' => 'Task not found',
            ], 404);
        }

        $worker = Worker::where('worker_key', $request->worker_key)->first();
        if (!$worker) {
            return response()->json([
                'message' => 'Worker not found',
            ], 404);
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
        $validator = Validator::make($request->all(), [
            'worker_key' => 'required|string',
            'result' => 'nullable|array',
            'duration_ms' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $task = Task::find($id);
        if (!$task) {
            return response()->json([
                'message' => 'Task not found',
            ], 404);
        }

        $worker = Worker::where('worker_key', $request->worker_key)->first();
        if (!$worker) {
            return response()->json([
                'message' => 'Worker not found',
            ], 404);
        }

        $result = $request->get('result');
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
        $validator = Validator::make($request->all(), [
            'worker_key' => 'required|string',
            'reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $task = Task::find($id);
        if (!$task) {
            return response()->json([
                'message' => 'Task not found',
            ], 404);
        }

        $worker = Worker::where('worker_key', $request->worker_key)->first();
        if (!$worker) {
            return response()->json([
                'message' => 'Worker not found',
            ], 404);
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
}
