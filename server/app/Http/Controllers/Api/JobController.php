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
        // Check if this is an image processing job with file uploads
        if ($request->hasFile('images')) {
            return $this->storeImageProcessingJob($request);
        }

        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'type'        => 'required|string|max:100',
            'task_count'  => 'required|integer|min:1|max:10000',
            'priority'    => 'nullable|integer|min:1|max:10',
            'dataset'     => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $result = DB::transaction(function () use ($request) {
                // ── 1. Create the Job record ──────────────────────────────────
                $job = SchedulerJob::create([
                    'user_id'         => $request->user()->id,
                    'name'            => $request->name,
                    'description'     => $request->description,
                    'type'            => $request->type,
                    'status'          => 'pending',
                    'priority'        => $request->get('priority', 5),
                    'submitted_by'    => $request->user()->name,
                    'total_tasks'     => $request->task_count,
                    'completed_tasks' => 0,
                    'failed_tasks'    => 0,
                ]);

                // ── 2. Partition into tasks ───────────────────────────────────
                $tasks = $this->partitioner->partition(
                    $job,
                    $request->task_count,
                    $request->get('dataset')
                );

                return [
                    'job'   => $job->fresh(['tasks']),
                    'tasks' => $tasks,
                ];
            });

            $job        = $result['job'];
            $tasks      = $result['tasks'];
            $taskCount  = count($tasks);

            // Build a summary of what was created so the frontend can show it
            $summary = $this->buildCreationSummary($job, $tasks);

            return response()->json([
                'message'      => 'Job created successfully',
                'job'          => $job,
                'tasks_created' => $taskCount,
                'summary'      => $summary,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create job',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Build a human-readable creation summary for the frontend confirmation screen.
     *
     * This is what the frontend uses to render the "Tasks Table" after job creation,
     * matching exactly the scenario:
     *   Task 1 | Job 1 | records 1→100   | pending
     *   Task 2 | Job 1 | records 101→200 | pending
     *   ...
     */
    private function buildCreationSummary(SchedulerJob $job, array $tasks): array
    {
        $taskCount    = count($tasks);
        $firstPayload = !empty($tasks) ? ($tasks[0]->payload ?? []) : [];
        $totalRecords = $firstPayload['total_records'] ?? ($taskCount * 100);
        $recordsPerTask = $taskCount > 0 ? (int) ceil($totalRecords / $taskCount) : 0;

        return [
            'job_id'           => $job->id,
            'job_name'         => $job->name,
            'job_type'         => $job->type,
            'status'           => $job->status,
            'priority'         => $job->priority,
            'total_tasks'      => $taskCount,
            'total_records'    => $totalRecords,
            'records_per_task' => $recordsPerTask,
            'operations'       => $firstPayload['operations'] ?? [],
            // Each row = one row in the Tasks database table
            'task_breakdown'   => array_map(fn($t) => [
                'task_id'       => $t->id,
                'job_id'        => $job->id,
                'task_number'   => $t->task_index + 1,
                'task_index'    => $t->task_index,
                // 0-based internal indices
                'start_index'   => $t->payload['start_index']  ?? 0,
                'end_index'     => $t->payload['end_index']    ?? 0,
                // 1-based display values (e.g. "records 1 → 100")
                'record_from'   => $t->payload['record_from']  ?? ($t->payload['start_index'] + 1 ?? 1),
                'record_to'     => $t->payload['record_to']    ?? ($t->payload['end_index']   + 1 ?? 100),
                'records_count' => $t->payload['records_count'] ?? $t->payload['items_count'] ?? 0,
                'status'        => $t->status,
            ], $tasks),
        ];
    }

    /**
     * Store image processing job with file uploads
     */
    private function storeImageProcessingJob(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'images' => 'required|array|min:1',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB max
            'operations' => 'nullable|array',
            'operations.*' => 'in:resize,compress,thumbnail',
            'task_count' => 'nullable|integer|min:1|max:100',
            'priority' => 'nullable|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $job = DB::transaction(function () use ($request) {
                // Create unique storage path for this job
                $storagePath = 'jobs/' . uniqid('job_', true);
                
                // Store uploaded images
                $imagePaths = [];
                foreach ($request->file('images') as $index => $image) {
                    $filename = time() . '_' . $index . '_' . $image->getClientOriginalName();
                    $path = $image->storeAs($storagePath . '/original', $filename, 'public');
                    $imagePaths[] = $path;
                }

                // Determine operations
                $operations = $request->get('operations', ['resize', 'compress', 'thumbnail']);

                // Calculate task count (default: 10 images per task)
                $imagesPerTask = 10;
                $taskCount = $request->get('task_count', (int) ceil(count($imagePaths) / $imagesPerTask));

                // Create the job
                $job = SchedulerJob::create([
                    'user_id' => $request->user()->id,
                    'name' => $request->name,
                    'description' => $request->description ?? 'Image processing job',
                    'type' => 'image_processing',
                    'input_files' => $imagePaths,
                    'output_files' => [],
                    'operations' => $operations,
                    'storage_path' => $storagePath,
                    'status' => 'pending',
                    'priority' => $request->get('priority', 5),
                    'submitted_by' => $request->user()->name,
                    'total_tasks' => $taskCount,
                    'completed_tasks' => 0,
                    'failed_tasks' => 0,
                ]);

                // Partition into tasks
                $tasks = $this->partitioner->partition($job, $taskCount);

                return $job->fresh(['tasks']);
            });

            return response()->json([
                'message' => 'Image processing job created successfully',
                'job' => $job,
                'images_uploaded' => count($job->input_files),
                'tasks_created' => $job->total_tasks,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create image processing job',
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
     * Get completion summary for a finished job.
     * Shows total duration, task stats, throughput, and worker contributions.
     */
    public function completionSummary(Request $request, int $id): JsonResponse
    {
        $job = SchedulerJob::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$job) {
            return response()->json(['message' => 'Job not found or access denied'], 404);
        }

        // Total wall-clock duration
        $durationMs = null;
        $durationLabel = null;
        if ($job->started_at && $job->completed_at) {
            $durationMs    = $job->started_at->diffInMilliseconds($job->completed_at);
            $durationLabel = $this->formatDurationMs($durationMs);
        }

        // Task stats
        $taskCounts = \Illuminate\Support\Facades\DB::table('tasks')
            ->select('status', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->where('job_id', $job->id)
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Average task duration
        $avgTaskMs = \App\Models\Task::where('job_id', $job->id)
            ->where('status', 'done')
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->get()
            ->avg(fn($t) => $t->started_at->diffInMilliseconds($t->completed_at));

        // Worker contributions — how many tasks each worker completed
        $workerContributions = \App\Models\Task::where('job_id', $job->id)
            ->where('status', 'done')
            ->whereNotNull('worker_id')
            ->with('worker:id,worker_key,hostname')
            ->get()
            ->groupBy('worker_id')
            ->map(fn($tasks, $workerId) => [
                'worker_key'   => $tasks->first()->worker?->worker_key ?? "Worker #{$workerId}",
                'hostname'     => $tasks->first()->worker?->hostname,
                'tasks_done'   => $tasks->count(),
                'share_pct'    => $job->total_tasks > 0
                    ? round(($tasks->count() / $job->total_tasks) * 100)
                    : 0,
            ])
            ->sortByDesc('tasks_done')
            ->values()
            ->toArray();

        // Total records processed (for result_processing jobs)
        $totalRecords = \App\Models\Task::where('job_id', $job->id)
            ->where('status', 'done')
            ->get()
            ->sum(fn($t) => $t->payload['records_count'] ?? 0);

        // Throughput: tasks per second
        $throughput = ($durationMs && $durationMs > 0)
            ? round(($job->completed_tasks / ($durationMs / 1000)), 2)
            : 0;

        return response()->json([
            'job_id'          => $job->id,
            'job_name'        => $job->name,
            'job_type'        => $job->type,
            'final_status'    => $job->status,
            'is_success'      => $job->status === 'completed',

            // Timing
            'started_at'      => $job->started_at?->toIso8601String(),
            'completed_at'    => $job->completed_at?->toIso8601String(),
            'duration_ms'     => $durationMs,
            'duration_label'  => $durationLabel,

            // Task counts
            'total_tasks'     => $job->total_tasks,
            'completed_tasks' => $job->completed_tasks,
            'failed_tasks'    => $job->failed_tasks,
            'progress'        => $job->progress,

            // Performance
            'avg_task_ms'     => $avgTaskMs ? (int) round($avgTaskMs) : null,
            'throughput_per_sec' => $throughput,
            'total_records'   => $totalRecords ?: null,

            // Worker contributions
            'worker_contributions' => $workerContributions,
            'workers_used'    => count($workerContributions),
        ]);
    }

    /**
     * Format milliseconds into human-readable duration
     */
    private function formatDurationMs(int $ms): string
    {
        $s = (int) round($ms / 1000);
        if ($s < 60)  return "{$s}s";
        $m = (int) floor($s / 60);
        $s = $s % 60;
        if ($m < 60)  return $s > 0 ? "{$m}m {$s}s" : "{$m}m";
        $h = (int) floor($m / 60);
        $m = $m % 60;
        return $m > 0 ? "{$h}h {$m}m" : "{$h}h";
    }

    /**
     * Get retry statistics for a job — shows exponential backoff schedule
     * and retry history per task. Used by the RetryPanel component.
     */
    public function retryStats(Request $request, int $id): JsonResponse
    {
        $job = SchedulerJob::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$job) {
            return response()->json(['message' => 'Job not found or access denied'], 404);
        }

        // Tasks that have been retried or failed
        $retriedTasks = $job->tasks()
            ->where(function ($q) {
                $q->where('retry_count', '>', 0)
                  ->orWhere('status', 'failed');
            })
            ->orderBy('task_index')
            ->get();

        // Tasks currently waiting in backoff (pending with available_after in the future)
        $backoffTasks = $job->tasks()
            ->where('status', 'pending')
            ->whereNotNull('available_after')
            ->where('available_after', '>', now())
            ->orderBy('available_after')
            ->get();

        $maxRetries = config('scheduler.max_task_retries', 3);

        // Build the backoff schedule table: attempt → delay
        $backoffSchedule = [];
        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            $backoffSchedule[] = [
                'attempt'        => $attempt + 1,
                'delay_seconds'  => (int) (pow(2, $attempt) * 5),
                'delay_label'    => $this->formatBackoffDelay((int) (pow(2, $attempt) * 5)),
            ];
        }

        return response()->json([
            'job_id'           => $job->id,
            'max_retries'      => $maxRetries,
            'backoff_formula'  => '2^attempt × 5 seconds',
            'backoff_schedule' => $backoffSchedule,

            // Tasks currently in backoff cooldown
            'backoff_tasks' => $backoffTasks->map(fn($t) => [
                'task_id'         => $t->id,
                'task_number'     => $t->task_index + 1,
                'retry_count'     => $t->retry_count,
                'max_retries'     => $t->max_retries,
                'failure_reason'  => $t->failure_reason,
                'available_after' => $t->available_after?->toIso8601String(),
                'seconds_until_available' => $t->available_after
                    ? max(0, (int) now()->diffInSeconds($t->available_after, false))
                    : 0,
                'next_delay_seconds' => (int) (pow(2, $t->retry_count) * 5),
            ])->values()->toArray(),

            // Tasks that have been retried (history)
            'retried_tasks' => $retriedTasks->map(fn($t) => [
                'task_id'        => $t->id,
                'task_number'    => $t->task_index + 1,
                'status'         => $t->status,
                'retry_count'    => $t->retry_count,
                'max_retries'    => $t->max_retries,
                'failure_reason' => $t->failure_reason,
                'will_retry'     => $t->status === 'pending' && $t->retry_count < $t->max_retries,
                'is_permanent'   => $t->status === 'failed',
                'record_from'    => $t->payload['record_from'] ?? null,
                'record_to'      => $t->payload['record_to']   ?? null,
            ])->values()->toArray(),

            // Summary counts
            'summary' => [
                'total_retried'    => $retriedTasks->where('retry_count', '>', 0)->count(),
                'in_backoff'       => $backoffTasks->count(),
                'permanently_failed' => $retriedTasks->where('status', 'failed')->count(),
                'recovered'        => $retriedTasks->where('status', 'done')->where('retry_count', '>', 0)->count(),
            ],
        ]);
    }

    /**
     * Format backoff delay into human-readable string
     */
    private function formatBackoffDelay(int $seconds): string
    {
        if ($seconds < 60) return "{$seconds}s";
        $m = (int) floor($seconds / 60);
        $s = $seconds % 60;
        return $s > 0 ? "{$m}m {$s}s" : "{$m}m";
    }
    public function statusPoll(Request $request, int $id): JsonResponse
    {
        $job = SchedulerJob::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$job) {
            return response()->json(['message' => 'Job not found or access denied'], 404);
        }

        // Task status counts — fast GROUP BY query, no payload loading
        $taskCounts = \Illuminate\Support\Facades\DB::table('tasks')
            ->select('status', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->where('job_id', $job->id)
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Recent status transitions (last 20 task log entries for this job)
        $recentTransitions = \App\Models\TaskLog::whereHas('task', fn($q) => $q->where('job_id', $job->id))
            ->with(['task:id,task_index,status', 'worker:id,worker_key'])
            ->orderBy('logged_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn($log) => [
                'task_id'     => $log->task_id,
                'task_index'  => $log->task?->task_index,
                'message'     => $log->message,
                'level'       => $log->level,
                'worker_key'  => $log->worker?->worker_key,
                'logged_at'   => $log->logged_at->toIso8601String(),
            ]);

        return response()->json([
            'job_id'          => $job->id,
            'job_status'      => $job->status,
            'progress'        => $job->progress,
            'total_tasks'     => $job->total_tasks,
            'completed_tasks' => $job->completed_tasks,
            'failed_tasks'    => $job->failed_tasks,
            'started_at'      => $job->started_at?->toIso8601String(),
            'completed_at'    => $job->completed_at?->toIso8601String(),
            // Task status breakdown
            'task_counts' => [
                'pending'   => $taskCounts['pending']   ?? 0,
                'assigned'  => $taskCounts['assigned']  ?? 0,
                'running'   => $taskCounts['running']   ?? 0,
                'done'      => $taskCounts['done']      ?? 0,
                'failed'    => $taskCounts['failed']    ?? 0,
                'cancelled' => $taskCounts['cancelled'] ?? 0,
            ],
            // Recent activity log for the live feed
            'recent_transitions' => $recentTransitions,
            'polled_at'          => now()->toIso8601String(),
        ]);
    }

    /**
     * Get tasks for a specific job (user-scoped)
     * Returns the full task list with payload details for the Tasks tab.
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

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $sortBy    = $request->get('sort_by', 'task_index');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $tasks = $query->get();

        // Enrich each task with display-friendly fields from its payload
        $enriched = $tasks->map(function ($task) use ($job) {
            $payload = $task->payload ?? [];
            return [
                // Database columns
                'id'              => $task->id,
                'job_id'          => $task->job_id,
                'task_index'      => $task->task_index,
                'status'          => $task->status,
                'worker_id'       => $task->worker_id,
                'retry_count'     => $task->retry_count,
                'max_retries'     => $task->max_retries,
                'failure_reason'  => $task->failure_reason,
                'assigned_at'     => $task->assigned_at,
                'started_at'      => $task->started_at,
                'completed_at'    => $task->completed_at,
                'timeout_seconds' => $task->timeout_seconds,
                'created_at'      => $task->created_at,
                'updated_at'      => $task->updated_at,
                // Worker info
                'worker'          => $task->worker ? [
                    'worker_key' => $task->worker->worker_key,
                    'hostname'   => $task->worker->hostname,
                ] : null,
                // Payload-derived display fields
                'task_number'     => $task->task_index + 1,
                'record_from'     => $payload['record_from']   ?? ($payload['start_index'] + 1 ?? null),
                'record_to'       => $payload['record_to']     ?? ($payload['end_index']   + 1 ?? null),
                'records_count'   => $payload['records_count'] ?? $payload['items_count']  ?? null,
                'total_records'   => $payload['total_records'] ?? null,
                'operations'      => $payload['operations']    ?? [],
                'payload_type'    => $payload['type']          ?? $job->type,
                // Full payload (for detail view)
                'payload'         => $payload,
            ];
        });

        // Task status counts for the summary bar
        $statusCounts = [
            'total'    => $tasks->count(),
            'pending'  => $tasks->where('status', 'pending')->count(),
            'assigned' => $tasks->where('status', 'assigned')->count(),
            'running'  => $tasks->where('status', 'running')->count(),
            'done'     => $tasks->where('status', 'done')->count(),
            'failed'   => $tasks->where('status', 'failed')->count(),
            'cancelled'=> $tasks->where('status', 'cancelled')->count(),
        ];

        return response()->json([
            'job_id'        => $job->id,
            'job_name'      => $job->name,
            'job_type'      => $job->type,
            'tasks'         => $enriched,
            'status_counts' => $statusCounts,
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

    /**
     * Download processed images for a job
     */
    public function download(Request $request, int $id): JsonResponse
    {
        $job = SchedulerJob::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$job) {
            return response()->json([
                'message' => 'Job not found or access denied',
            ], 404);
        }

        if ($job->type !== 'image_processing') {
            return response()->json([
                'message' => 'This job is not an image processing job',
            ], 400);
        }

        if ($job->status !== 'completed') {
            return response()->json([
                'message' => 'Job is not completed yet',
                'status' => $job->status,
                'progress' => $job->progress,
            ], 400);
        }

        // Collect all processed images from tasks
        $processedImages = [];
        foreach ($job->tasks as $task) {
            if ($task->output_images) {
                $processedImages = array_merge($processedImages, $task->output_images);
            }
        }

        return response()->json([
            'job_id' => $job->id,
            'job_name' => $job->name,
            'processed_images' => $processedImages,
            'total_images' => count($processedImages),
            'download_base_url' => url('storage'),
        ]);
    }
}
