<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchedulerJob;
use App\Services\TaskPartitionerService;
use App\Services\JobStatusService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class JobController extends Controller
{
    public function __construct(
        private TaskPartitionerService $partitioner,
        private JobStatusService $jobStatusService
    ) {}

    /**
     * List all jobs with filtering and pagination (user-scoped)
     */
    public function index(Request $request): JsonResponse
    {
        $query = SchedulerJob::query()->where('user_id', $request->user()->id);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
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
     * Create a new job
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|max:100',
            'task_count' => 'required|integer|min:1|max:10000',
            'priority' => 'nullable|integer|min:1|max:10',
            'submitted_by' => 'nullable|string|max:255',
            'dataset' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $job = DB::transaction(function () use ($request) {
                // Create the job with user_id
                $job = SchedulerJob::create([
                    'user_id' => $request->user()->id,
                    'name' => $request->name,
                    'description' => $request->description,
                    'type' => $request->type,
                    'status' => 'pending',
                    'priority' => $request->get('priority', 5),
                    'submitted_by' => $request->user()->name,
                    'total_tasks' => $request->task_count,
                    'completed_tasks' => 0,
                    'failed_tasks' => 0,
                ]);

                // Partition into tasks
                $tasks = $this->partitioner->partition(
                    $job,
                    $request->task_count,
                    $request->get('dataset')
                );

                return $job->fresh(['tasks']);
            });

            return response()->json([
                'message' => 'Job created successfully',
                'job' => $job,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create job',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific job with all its tasks (user-scoped)
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $job = SchedulerJob::with(['tasks' => function ($query) {
            $query->orderBy('task_index');
        }, 'tasks.worker'])
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$job) {
            return response()->json([
                'message' => 'Job not found or access denied',
            ], 404);
        }

        return response()->json([
            'job' => $job,
            'progress' => $job->progress,
            'pending_tasks' => $job->pending_tasks_count,
        ]);
    }

    /**
     * Get tasks for a specific job (user-scoped)
     */
    public function tasks(Request $request, int $id): JsonResponse
    {
        $job = SchedulerJob::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$job) {
            return response()->json([
                'message' => 'Job not found or access denied',
            ], 404);
        }

        $query = $job->tasks()->with('worker');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'task_index');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $tasks = $query->get();

        return response()->json([
            'job_id' => $job->id,
            'tasks' => $tasks,
        ]);
    }

    /**
     * Cancel a job (user-scoped)
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $job = SchedulerJob::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$job) {
            return response()->json([
                'message' => 'Job not found or access denied',
            ], 404);
        }

        if (!$job->canBeCancelled()) {
            return response()->json([
                'message' => 'Job cannot be cancelled in its current state',
                'status' => $job->status,
            ], 409);
        }

        $this->jobStatusService->cancel($job);

        return response()->json([
            'message' => 'Job cancelled successfully',
            'job' => $job->fresh(),
        ]);
    }
}
