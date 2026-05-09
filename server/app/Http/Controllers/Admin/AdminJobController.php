<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchedulerJob;
use App\Services\JobStatusService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminJobController extends Controller
{
    public function __construct(
        private JobStatusService $jobStatusService
    ) {}

    /**
     * List ALL jobs from ALL users (admin only)
     */
    public function index(Request $request): JsonResponse
    {
        $query = SchedulerJob::with('user:id,name,email');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = min($request->get('per_page', 20), 100);
        $jobs = $query->paginate($perPage);

        return response()->json($jobs);
    }

    /**
     * Get a specific job (admin can view any job)
     */
    public function show(int $id): JsonResponse
    {
        $job = SchedulerJob::with(['user:id,name,email', 'tasks' => function ($query) {
            $query->orderBy('task_index');
        }, 'tasks.worker'])->find($id);

        if (!$job) {
            return response()->json([
                'message' => 'Job not found',
            ], 404);
        }

        return response()->json([
            'job' => $job,
            'progress' => $job->progress,
            'pending_tasks' => $job->pending_tasks_count,
        ]);
    }

    /**
     * Force cancel any job (admin only)
     */
    public function forceCancel(int $id): JsonResponse
    {
        $job = SchedulerJob::find($id);

        if (!$job) {
            return response()->json([
                'message' => 'Job not found',
            ], 404);
        }

        if ($job->isTerminal()) {
            return response()->json([
                'message' => 'Job is already in terminal state',
                'status' => $job->status,
            ], 409);
        }

        $this->jobStatusService->cancel($job);

        return response()->json([
            'message' => 'Job force cancelled by admin',
            'job' => $job->fresh(),
        ]);
    }

    /**
     * Delete a job permanently (admin only)
     */
    public function destroy(int $id): JsonResponse
    {
        $job = SchedulerJob::find($id);

        if (!$job) {
            return response()->json([
                'message' => 'Job not found',
            ], 404);
        }

        // Delete all associated tasks first
        $job->tasks()->delete();
        $job->delete();

        return response()->json([
            'message' => 'Job deleted permanently',
        ]);
    }

    /**
     * Get job statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_jobs' => SchedulerJob::count(),
            'by_status' => SchedulerJob::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status'),
            'by_type' => SchedulerJob::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type'),
            'total_tasks' => SchedulerJob::sum('total_tasks'),
            'completed_tasks' => SchedulerJob::sum('completed_tasks'),
            'failed_tasks' => SchedulerJob::sum('failed_tasks'),
        ];

        return response()->json($stats);
    }

    /**
     * Retry failed tasks for a job (admin only)
     */
    public function retryJob(int $id): JsonResponse
    {
        $job = SchedulerJob::find($id);

        if (!$job) {
            return response()->json([
                'message' => 'Job not found',
            ], 404);
        }

        // Find all failed tasks that can be retried
        $failedTasks = $job->tasks()->where('status', 'failed')->get();

        if ($failedTasks->isEmpty()) {
            return response()->json([
                'message' => 'No failed tasks to retry',
            ], 400);
        }

        $retriedCount = 0;
        foreach ($failedTasks as $task) {
            if ($task->retry_count < $task->max_retries) {
                $task->update([
                    'status' => 'pending',
                    'worker_id' => null,
                    'retry_count' => $task->retry_count + 1,
                    'available_after' => now(),
                    'assigned_at' => null,
                    'started_at' => null,
                    'completed_at' => null,
                ]);
                $retriedCount++;
            }
        }

        // Update job status if it was failed
        if ($job->status === 'failed' && $retriedCount > 0) {
            $job->update(['status' => 'running']);
        }

        // Recalculate job status
        $this->jobStatusService->recalculate($job);

        return response()->json([
            'message' => "Retried {$retriedCount} failed task(s)",
            'retried_count' => $retriedCount,
            'job' => $job->fresh(),
        ]);
    }
}
