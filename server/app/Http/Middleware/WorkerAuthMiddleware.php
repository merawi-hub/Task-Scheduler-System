<?php

namespace App\Http\Middleware;

use App\Models\Worker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WorkerAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-Worker-Token');

        if (!$token) {
            return response()->json([
                'message' => 'Worker authentication required. Provide X-Worker-Token header.',
                'error' => 'WORKER_TOKEN_MISSING'
            ], 401);
        }

        $worker = Worker::where('api_token', $token)->first();

        if (!$worker) {
            return response()->json([
                'message' => 'Invalid worker token.',
                'error' => 'INVALID_WORKER_TOKEN'
            ], 401);
        }

        if ($worker->status === 'dead') {
            return response()->json([
                'message' => 'Worker is marked as dead. Please re-register.',
                'error' => 'WORKER_DEAD'
            ], 403);
        }

        // Attach worker to request for use in controllers
        $request->merge(['authenticated_worker' => $worker]);

        return $next($request);
    }
}
