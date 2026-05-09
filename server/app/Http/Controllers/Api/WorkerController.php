<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class WorkerController extends Controller
{
    /**
     * Register a new worker or reactivate existing one
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'worker_key' => 'required|string|max:64',
            'hostname' => 'required|string|max:255',
            'ip_address' => 'nullable|string|max:45',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $worker = Worker::where('worker_key', $request->worker_key)->first();

        if ($worker) {
            // Reactivate existing worker
            $worker->update([
                'hostname' => $request->hostname,
                'ip_address' => $request->ip_address,
                'status' => 'idle',
                'last_heartbeat_at' => now(),
                'current_task_id' => null,
            ]);

            return response()->json([
                'message' => 'Worker reactivated',
                'worker' => $worker,
                'api_token' => $worker->api_token, // Return existing token
            ], 200);
        }

        // Create new worker with API token
        $worker = Worker::create([
            'worker_key' => $request->worker_key,
            'api_token' => Worker::generateApiToken(),
            'hostname' => $request->hostname,
            'ip_address' => $request->ip_address,
            'status' => 'idle',
            'last_heartbeat_at' => now(),
            'registered_at' => now(),
        ]);

        return response()->json([
            'message' => 'Worker registered successfully',
            'worker' => $worker,
            'api_token' => $worker->api_token, // Return token ONCE on registration
        ], 201);
    }

    /**
     * Update worker heartbeat
     */
    public function heartbeat(Request $request, string $key): JsonResponse
    {
        $worker = $request->get('authenticated_worker');

        if (!$worker) {
            return response()->json([
                'message' => 'Worker authentication required',
            ], 401);
        }

        if ($worker && $worker->worker_key !== $key) {
            return response()->json([
                'message' => 'Worker key does not match authenticated token',
            ], 403);
        }

        $worker->heartbeat();

        // Optionally update status if provided
        if ($request->has('status') && in_array($request->status, ['idle', 'busy'])) {
            $worker->update(['status' => $request->status]);
        }

        return response()->json([
            'message' => 'Heartbeat received',
            'worker' => $worker->fresh(),
        ]);
    }

    /**
     * Get authenticated worker details
     */
    public function me(Request $request): JsonResponse
    {
        $worker = $request->get('authenticated_worker');

        if (!$worker) {
            return response()->json([
                'message' => 'Worker authentication required',
            ], 401);
        }

        $worker->load('currentTask');
        $worker->is_dead = $worker->isDead();
        $worker->seconds_since_heartbeat = $worker->last_heartbeat_at
            ? $worker->last_heartbeat_at->diffInSeconds(now())
            : null;

        return response()->json([
            'worker' => $worker,
        ]);
    }

    /**
     * List all workers
     */
    public function index(Request $request): JsonResponse
    {
        $query = Worker::query();

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Include current task information
        $query->with('currentTask');

        // Sorting
        $sortBy = $request->get('sort_by', 'registered_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $workers = $query->get();

        // Add computed fields
        $workers->each(function ($worker) {
            $worker->is_dead = $worker->isDead();
            $worker->seconds_since_heartbeat = $worker->last_heartbeat_at
                ? $worker->last_heartbeat_at->diffInSeconds(now())
                : null;
        });

        return response()->json([
            'workers' => $workers,
            'summary' => [
                'total' => $workers->count(),
                'idle' => $workers->where('status', 'idle')->count(),
                'busy' => $workers->where('status', 'busy')->count(),
                'dead' => $workers->where('status', 'dead')->count(),
            ],
        ]);
    }

    /**
     * Get a specific worker
     */
    public function show(string $key): JsonResponse
    {
        $worker = Worker::where('worker_key', $key)
            ->with(['currentTask', 'tasks' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(10);
            }])
            ->first();

        if (!$worker) {
            return response()->json([
                'message' => 'Worker not found',
            ], 404);
        }

        $worker->is_dead = $worker->isDead();
        $worker->seconds_since_heartbeat = $worker->last_heartbeat_at
            ? $worker->last_heartbeat_at->diffInSeconds(now())
            : null;

        return response()->json([
            'worker' => $worker,
        ]);
    }
}
