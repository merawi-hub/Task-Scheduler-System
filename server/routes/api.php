<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\WorkerController;
use App\Http\Controllers\Api\MetricsController;
use App\Http\Controllers\Admin\AdminJobController;
use App\Http\Controllers\Admin\AdminWorkerController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminMetricsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Test route to verify connection
Route::get('/test', function () {
    return response()->json([
        'message' => 'API connection successful!',
        'status' => 'connected',
        'timestamp' => now()
    ]);
});

/*
|--------------------------------------------------------------------------
| Authentication Routes (Public)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    // Protected auth routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::put('/password', [AuthController::class, 'changePassword']);
    });
});

/*
|--------------------------------------------------------------------------
| Job Submitter Routes (Authenticated Users)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('jobs')->group(function () {
    Route::get('/', [JobController::class, 'index']);
    Route::post('/', [JobController::class, 'store']);
    Route::get('/{id}', [JobController::class, 'show']);
    Route::get('/{id}/status', [JobController::class, 'statusPoll']);
    Route::get('/{id}/retry-stats', [JobController::class, 'retryStats']);
    Route::get('/{id}/completion', [JobController::class, 'completionSummary']); // Final summary
    Route::get('/{id}/tasks', [JobController::class, 'tasks']);
    Route::get('/{id}/download', [JobController::class, 'download']);
    Route::delete('/{id}', [JobController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Worker Node Routes (API Token Authentication)
|--------------------------------------------------------------------------
*/
// Worker registration (public - generates token)
Route::post('/workers/register', [WorkerController::class, 'register']);

// Worker-authenticated routes (require X-Worker-Token header)
Route::middleware('worker.auth')->group(function () {
    Route::get('/tasks/next', [TaskController::class, 'next']);
    Route::post('/tasks/{id}/start', [TaskController::class, 'start']);
    Route::post('/tasks/{id}/complete', [TaskController::class, 'complete']);
    Route::post('/tasks/{id}/fail', [TaskController::class, 'fail']);
    Route::post('/tasks/{id}/update-images', [TaskController::class, 'updateImages']);
    Route::post('/workers/{key}/heartbeat', [WorkerController::class, 'heartbeat']);
    Route::get('/workers/{key}/me', [WorkerController::class, 'me']);
});

// Pull-based activity snapshot — authenticated users can see who is pulling what
Route::middleware('auth:sanctum')->get('/tasks/activity', [TaskController::class, 'activity']);

// Worker list — requires user auth (not public)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/workers', [WorkerController::class, 'index']);
    Route::get('/workers/{key}', [WorkerController::class, 'show']);
});

/*
|--------------------------------------------------------------------------
| System Admin Routes (Admin Users Only)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Job Management
    Route::get('/jobs', [AdminJobController::class, 'index']); // All jobs from all users
    Route::get('/jobs/statistics', [AdminJobController::class, 'statistics']);
    Route::get('/jobs/{id}', [AdminJobController::class, 'show']);
    Route::post('/jobs/{id}/cancel', [AdminJobController::class, 'forceCancel']);
    Route::post('/jobs/{id}/retry', [AdminJobController::class, 'retryJob']);
    Route::delete('/jobs/{id}', [AdminJobController::class, 'destroy']);
    
    // Worker Management
    Route::get('/workers', [AdminWorkerController::class, 'index']); // All workers
    Route::get('/workers/statistics', [AdminWorkerController::class, 'statistics']);
    Route::get('/workers/fault-tolerance', [AdminWorkerController::class, 'faultTolerance']); // Heartbeat + recovery data
    Route::get('/workers/{key}', [AdminWorkerController::class, 'show']);
    Route::post('/workers/{key}/mark-dead', [AdminWorkerController::class, 'markDead']);
    Route::delete('/workers/{key}', [AdminWorkerController::class, 'destroy']);
    
    // User Management
    Route::get('/users', [AdminUserController::class, 'index']); // All users
    Route::get('/users/statistics', [AdminUserController::class, 'statistics']);
    Route::get('/users/{id}', [AdminUserController::class, 'show']);
    Route::put('/users/{id}', [AdminUserController::class, 'update']);
    Route::delete('/users/{id}', [AdminUserController::class, 'destroy']);
    
    // System Metrics
    Route::get('/metrics', [AdminMetricsController::class, 'index']);
    Route::get('/metrics/history', [AdminMetricsController::class, 'history']);
    Route::get('/metrics/health', [AdminMetricsController::class, 'health']);
    Route::get('/metrics/activity', [AdminMetricsController::class, 'activity']);
    
    // Task Logs
    Route::get('/logs', [AdminMetricsController::class, 'activity']); // Alias for activity feed
});

/*
|--------------------------------------------------------------------------
| Public Metrics Routes (For Dashboard - Consider adding auth)
|--------------------------------------------------------------------------
*/
Route::prefix('metrics')->group(function () {
    Route::get('/', [MetricsController::class, 'index']);
    Route::get('/history', [MetricsController::class, 'history']);
});

// Legacy route for backward compatibility
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

