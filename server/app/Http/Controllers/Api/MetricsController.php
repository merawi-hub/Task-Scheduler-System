<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchedulerJob;
use App\Models\Task;
use App\Models\Worker;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class MetricsController extends Controller
{
    /**
     * Get system-wide metrics
     */
    public function index(): JsonResponse
    {
        $metrics = [
            'jobs' => $this->getJobMetrics(),
            'tasks' => $this->getTaskMetrics(),
            'workers' => $this->getWorkerMetrics(),
            'performance' => $this->getPerformanceMetrics(),
        ];

        return response()->json($metrics);
    }

    /**
     * Get job-related metrics
     */
    private function getJobMetrics(): array
    {
        $jobStats = SchedulerJob::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'total' => SchedulerJob::count(),
            'pending' => $jobStats['pending'] ?? 0,
            'running' => $jobStats['running'] ?? 0,
            'completed' => $jobStats['completed'] ?? 0,
            'failed' => $jobStats['failed'] ?? 0,
            'cancelled' => $jobStats['cancelled'] ?? 0,
        ];
    }

    /**
     * Get task-related metrics
     */
    private function getTaskMetrics(): array
    {
        $taskStats = Task::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $totalTasks = Task::count();
        $completedTasks = $taskStats['done'] ?? 0;
        $failedTasks = $taskStats['failed'] ?? 0;

        return [
            'total' => $totalTasks,
            'pending' => $taskStats['pending'] ?? 0,
            'assigned' => $taskStats['assigned'] ?? 0,
            'running' => $taskStats['running'] ?? 0,
            'done' => $completedTasks,
            'failed' => $failedTasks,
            'cancelled' => $taskStats['cancelled'] ?? 0,
            'success_rate' => $totalTasks > 0 
                ? round(($completedTasks / $totalTasks) * 100, 2)
                : 0,
        ];
    }

    /**
     * Get worker-related metrics
     */
    private function getWorkerMetrics(): array
    {
        $workerStats = Worker::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $totalWorkers = Worker::count();
        $activeWorkers = ($workerStats['idle'] ?? 0) + ($workerStats['busy'] ?? 0);

        return [
            'total' => $totalWorkers,
            'idle' => $workerStats['idle'] ?? 0,
            'busy' => $workerStats['busy'] ?? 0,
            'dead' => $workerStats['dead'] ?? 0,
            'active' => $activeWorkers,
            'utilization' => $activeWorkers > 0 
                ? round((($workerStats['busy'] ?? 0) / $activeWorkers) * 100, 2)
                : 0,
            'total_tasks_completed' => Worker::sum('tasks_completed'),
            'total_tasks_failed' => Worker::sum('tasks_failed'),
        ];
    }

    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics(): array
    {
        // Calculate tasks per second (last hour)
        $oneHourAgo = now()->subHour();
        $recentCompletedTasks = Task::where('status', 'done')
            ->where('completed_at', '>=', $oneHourAgo)
            ->count();

        $tasksPerSecond = round($recentCompletedTasks / 3600, 2);

        // Calculate average task duration
        $avgDuration = Task::where('status', 'done')
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, started_at, completed_at)) as avg_duration')
            ->value('avg_duration');

        // Get retry statistics
        $retryStats = Task::where('retry_count', '>', 0)
            ->selectRaw('
                COUNT(*) as retried_tasks,
                AVG(retry_count) as avg_retries,
                MAX(retry_count) as max_retries
            ')
            ->first();

        return [
            'tasks_per_second' => $tasksPerSecond,
            'avg_task_duration_seconds' => $avgDuration ? round($avgDuration, 2) : 0,
            'retried_tasks' => $retryStats->retried_tasks ?? 0,
            'avg_retries' => $retryStats->avg_retries ? round($retryStats->avg_retries, 2) : 0,
            'max_retries' => $retryStats->max_retries ?? 0,
        ];
    }

    /**
     * Get time-series metrics for charts
     */
    public function history(): JsonResponse
    {
        // Get task completion history (last 24 hours, grouped by hour)
        $history = Task::where('completed_at', '>=', now()->subDay())
            ->where('status', 'done')
            ->selectRaw('
                DATE_FORMAT(completed_at, "%Y-%m-%d %H:00:00") as hour,
                COUNT(*) as completed_count
            ')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        return response()->json([
            'history' => $history,
        ]);
    }
}
