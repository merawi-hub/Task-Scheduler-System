<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchedulerJob;
use App\Models\Task;
use App\Models\Worker;
use App\Models\User;
use App\Services\MetricsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminMetricsController extends Controller
{
    public function __construct(
        private MetricsService $metricsService
    ) {}

    /**
     * Get comprehensive system metrics
     */
    public function index(Request $request): JsonResponse
    {
        // Use the new MetricsService for real-time metrics
        $realTimeMetrics = $this->metricsService->getRealTimeMetrics();
        $health = $this->metricsService->getSystemHealth();
        $activityFeed = $this->metricsService->getActivityFeed(50);

        // Combine with additional admin-specific metrics
        $metrics = [
            'realtime' => $realTimeMetrics,
            'health' => $health,
            'activity_feed' => $activityFeed,
            'system' => $this->getSystemMetrics(),
            'jobs' => $this->getJobMetrics(),
            'tasks' => $this->getTaskMetrics(),
            'workers' => $this->getWorkerMetrics(),
            'users' => $this->getUserMetrics(),
        ];

        return response()->json($metrics);
    }

    /**
     * Get system-wide metrics
     */
    private function getSystemMetrics(): array
    {
        return [
            'uptime' => 'N/A', // Could be tracked separately
            'total_jobs_processed' => SchedulerJob::whereIn('status', ['completed', 'failed'])->count(),
            'total_tasks_processed' => Task::whereIn('status', ['done', 'failed'])->count(),
            'success_rate' => $this->calculateSuccessRate(),
        ];
    }

    /**
     * Get job-related metrics
     */
    private function getJobMetrics(): array
    {
        return [
            'total' => SchedulerJob::count(),
            'pending' => SchedulerJob::where('status', 'pending')->count(),
            'running' => SchedulerJob::where('status', 'running')->count(),
            'completed' => SchedulerJob::where('status', 'completed')->count(),
            'failed' => SchedulerJob::where('status', 'failed')->count(),
            'cancelled' => SchedulerJob::where('status', 'cancelled')->count(),
            'by_type' => SchedulerJob::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type'),
            'recent_completed' => SchedulerJob::where('status', 'completed')
                ->orderBy('completed_at', 'desc')
                ->limit(5)
                ->get(['id', 'name', 'completed_at', 'user_id']),
        ];
    }

    /**
     * Get task-related metrics
     */
    private function getTaskMetrics(): array
    {
        return [
            'total' => Task::count(),
            'pending' => Task::where('status', 'pending')->count(),
            'running' => Task::where('status', 'running')->count(),
            'completed' => Task::where('status', 'done')->count(),
            'failed' => Task::where('status', 'failed')->count(),
            'average_execution_time' => Task::where('status', 'done')
                ->whereNotNull('started_at')
                ->whereNotNull('completed_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, started_at, completed_at)) as avg_time')
                ->value('avg_time'),
        ];
    }

    /**
     * Get worker-related metrics
     */
    private function getWorkerMetrics(): array
    {
        return [
            'total' => Worker::count(),
            'active' => Worker::whereIn('status', ['idle', 'busy'])->count(),
            'idle' => Worker::where('status', 'idle')->count(),
            'dead' => Worker::where('status', 'dead')->count(),
            'total_tasks_completed' => Worker::sum('tasks_completed'),
            'total_tasks_failed' => Worker::sum('tasks_failed'),
            'top_performers' => Worker::orderBy('tasks_completed', 'desc')
                ->limit(5)
                ->get(['worker_key', 'hostname', 'tasks_completed', 'tasks_failed']),
        ];
    }

    /**
     * Get user-related metrics
     */
    private function getUserMetrics(): array
    {
        return [
            'total' => User::count(),
            'admins' => User::where('is_admin', true)->count(),
            'regular' => User::where('is_admin', false)->count(),
            'active_users' => User::has('jobs')->count(),
            'top_submitters' => User::withCount('jobs')
                ->orderBy('jobs_count', 'desc')
                ->limit(5)
                ->get(['id', 'name', 'email', 'jobs_count']),
        ];
    }

    /**
     * Calculate overall success rate
     */
    private function calculateSuccessRate(): float
    {
        $completed = Task::where('status', 'done')->count();
        $total = Task::whereIn('status', ['done', 'failed'])->count();

        if ($total === 0) {
            return 0;
        }

        return round(($completed / $total) * 100, 2);
    }

    /**
     * Get historical metrics
     */
    public function history(Request $request): JsonResponse
    {
        $period = $request->get('period', 'hour'); // hour, day, week

        if (!in_array($period, ['hour', 'day', 'week'])) {
            return response()->json([
                'message' => 'Invalid period. Must be: hour, day, or week',
            ], 400);
        }

        // Use MetricsService for historical data
        $history = $this->metricsService->getHistoricalMetrics($period);

        // Also include legacy format for backward compatibility
        $days = $request->get('days', 7);
        $legacyHistory = DB::table('scheduler_jobs')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as jobs_created')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'period' => $period,
            'history' => $history,
            'legacy_history' => [
                'period' => $days . ' days',
                'data' => $legacyHistory,
            ],
        ]);
    }

    /**
     * Get system health status
     */
    public function health(Request $request): JsonResponse
    {
        $health = $this->metricsService->getSystemHealth();

        return response()->json($health);
    }

    /**
     * Get activity feed
     */
    public function activity(Request $request): JsonResponse
    {
        $limit = min($request->get('limit', 50), 200);
        $activityFeed = $this->metricsService->getActivityFeed($limit);

        return response()->json([
            'activity' => $activityFeed,
            'count' => count($activityFeed),
        ]);
    }
}
