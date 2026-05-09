<?php

namespace App\Services;

use App\Models\SchedulerJob;
use App\Models\Task;
use App\Models\Worker;
use App\Models\TaskLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class MetricsService
{
    /**
     * Get real-time system metrics
     *
     * @return array
     */
    public function getRealTimeMetrics(): array
    {
        $cacheSeconds = config('scheduler.metrics_cache_duration', 10);

        return Cache::remember('system_metrics', $cacheSeconds, function () {
            return [
                'jobs' => $this->getJobMetrics(),
                'tasks' => $this->getTaskMetrics(),
                'workers' => $this->getWorkerMetrics(),
                'performance' => $this->getPerformanceMetrics(),
                'timestamp' => now()->toIso8601String(),
            ];
        });
    }

    /**
     * Get job-related metrics
     *
     * @return array
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
            'success_rate' => $this->calculateJobSuccessRate(),
        ];
    }

    /**
     * Get task-related metrics
     *
     * @return array
     */
    private function getTaskMetrics(): array
    {
        $taskStats = Task::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $totalTasks = Task::count();

        return [
            'total' => $totalTasks,
            'pending' => $taskStats['pending'] ?? 0,
            'assigned' => $taskStats['assigned'] ?? 0,
            'running' => $taskStats['running'] ?? 0,
            'done' => $taskStats['done'] ?? 0,
            'failed' => $taskStats['failed'] ?? 0,
            'cancelled' => $taskStats['cancelled'] ?? 0,
            'success_rate' => $totalTasks > 0
                ? round((($taskStats['done'] ?? 0) / $totalTasks) * 100, 2)
                : 0,
            'queue_size' => $taskStats['pending'] ?? 0,
        ];
    }

    /**
     * Get worker-related metrics
     *
     * @return array
     */
    private function getWorkerMetrics(): array
    {
        $workerStats = Worker::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $totalWorkers = Worker::count();
        $activeWorkers = Worker::where('status', '!=', 'dead')
            ->where(function ($query) {
                $query->whereNull('last_heartbeat_at')
                    ->orWhere('last_heartbeat_at', '>=', now()->subSeconds(45));
            })
            ->count();

        return [
            'total' => $totalWorkers,
            'idle' => $workerStats['idle'] ?? 0,
            'busy' => $workerStats['busy'] ?? 0,
            'dead' => $workerStats['dead'] ?? 0,
            'active' => $activeWorkers,
            'utilization_rate' => $activeWorkers > 0
                ? round((($workerStats['busy'] ?? 0) / $activeWorkers) * 100, 2)
                : 0,
        ];
    }

    /**
     * Get performance metrics
     *
     * @return array
     */
    private function getPerformanceMetrics(): array
    {
        // Calculate throughput (tasks completed in last minute)
        $tasksLastMinute = Task::where('status', 'done')
            ->where('completed_at', '>=', now()->subMinute())
            ->count();

        $throughput = round($tasksLastMinute / 60, 2); // tasks per second

        // Calculate average task duration
        $avgDuration = Task::where('status', 'done')
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', now()->subHour())
            ->get()
            ->avg(function ($task) {
                return $task->started_at->diffInMilliseconds($task->completed_at);
            });

        // Calculate retry rate
        $totalTasks = Task::count();
        $retriedTasks = Task::where('retry_count', '>', 0)->count();
        $retryRate = $totalTasks > 0 ? round(($retriedTasks / $totalTasks) * 100, 2) : 0;

        return [
            'throughput_per_second' => $throughput,
            'avg_task_duration_ms' => $avgDuration ? round($avgDuration) : 0,
            'retry_rate' => $retryRate,
            'tasks_last_minute' => $tasksLastMinute,
        ];
    }

    /**
     * Calculate job success rate
     *
     * @return float
     */
    private function calculateJobSuccessRate(): float
    {
        $totalCompleted = SchedulerJob::whereIn('status', ['completed', 'failed'])->count();

        if ($totalCompleted === 0) {
            return 0;
        }

        $successful = SchedulerJob::where('status', 'completed')->count();

        return round(($successful / $totalCompleted) * 100, 2);
    }

    /**
     * Get historical metrics for charts
     *
     * @param string $period (hour, day, week)
     * @return array
     */
    public function getHistoricalMetrics(string $period = 'hour'): array
    {
        $intervals = $this->getTimeIntervals($period);

        return [
            'period' => $period,
            'intervals' => $intervals,
            'tasks_completed' => $this->getTaskCompletionHistory($intervals),
            'tasks_failed' => $this->getTaskFailureHistory($intervals),
            'worker_count' => $this->getWorkerCountHistory($intervals),
            'throughput' => $this->getThroughputHistory($intervals),
        ];
    }

    /**
     * Get time intervals for historical data
     *
     * @param string $period
     * @return array
     */
    private function getTimeIntervals(string $period): array
    {
        $intervals = [];
        $now = now();

        switch ($period) {
            case 'hour':
                // Last 60 minutes, 1-minute intervals
                for ($i = 59; $i >= 0; $i--) {
                    $intervals[] = $now->copy()->subMinutes($i);
                }
                break;

            case 'day':
                // Last 24 hours, 1-hour intervals
                for ($i = 23; $i >= 0; $i--) {
                    $intervals[] = $now->copy()->subHours($i);
                }
                break;

            case 'week':
                // Last 7 days, 1-day intervals
                for ($i = 6; $i >= 0; $i--) {
                    $intervals[] = $now->copy()->subDays($i)->startOfDay();
                }
                break;
        }

        return $intervals;
    }

    /**
     * Get task completion history
     *
     * @param array $intervals
     * @return array
     */
    private function getTaskCompletionHistory(array $intervals): array
    {
        $data = [];

        foreach ($intervals as $i => $time) {
            $nextTime = $intervals[$i + 1] ?? now();

            $count = Task::where('status', 'done')
                ->where('completed_at', '>=', $time)
                ->where('completed_at', '<', $nextTime)
                ->count();

            $data[] = [
                'timestamp' => $time->toIso8601String(),
                'count' => $count,
            ];
        }

        return $data;
    }

    /**
     * Get task failure history
     *
     * @param array $intervals
     * @return array
     */
    private function getTaskFailureHistory(array $intervals): array
    {
        $data = [];

        foreach ($intervals as $i => $time) {
            $nextTime = $intervals[$i + 1] ?? now();

            $count = Task::where('status', 'failed')
                ->where('completed_at', '>=', $time)
                ->where('completed_at', '<', $nextTime)
                ->count();

            $data[] = [
                'timestamp' => $time->toIso8601String(),
                'count' => $count,
            ];
        }

        return $data;
    }

    /**
     * Get worker count history
     *
     * @param array $intervals
     * @return array
     */
    private function getWorkerCountHistory(array $intervals): array
    {
        $data = [];

        foreach ($intervals as $time) {
            $count = Worker::where('created_at', '<=', $time)
                ->where(function ($query) use ($time) {
                    $query->where('status', '!=', 'dead')
                        ->orWhere('updated_at', '>=', $time->copy()->subMinutes(5));
                })
                ->count();

            $data[] = [
                'timestamp' => $time->toIso8601String(),
                'count' => $count,
            ];
        }

        return $data;
    }

    /**
     * Get throughput history
     *
     * @param array $intervals
     * @return array
     */
    private function getThroughputHistory(array $intervals): array
    {
        $data = [];

        foreach ($intervals as $i => $time) {
            $nextTime = $intervals[$i + 1] ?? now();
            $seconds = $time->diffInSeconds($nextTime);

            $count = Task::where('status', 'done')
                ->where('completed_at', '>=', $time)
                ->where('completed_at', '<', $nextTime)
                ->count();

            $throughput = $seconds > 0 ? round($count / $seconds, 2) : 0;

            $data[] = [
                'timestamp' => $time->toIso8601String(),
                'throughput' => $throughput,
            ];
        }

        return $data;
    }

    /**
     * Get activity feed (recent events)
     *
     * @param int $limit
     * @return array
     */
    public function getActivityFeed(int $limit = 50): array
    {
        $logs = TaskLog::with(['task.job', 'worker'])
            ->orderBy('logged_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'timestamp' => $log->logged_at->toIso8601String(),
                    'level' => $log->level,
                    'message' => $log->message,
                    'task_id' => $log->task_id,
                    'job_id' => $log->task->job_id ?? null,
                    'job_name' => $log->task->job->name ?? null,
                    'worker_key' => $log->worker->worker_key ?? null,
                    'context' => $log->context,
                ];
            });

        return $logs->toArray();
    }

    /**
     * Get system health status
     *
     * @return array
     */
    public function getSystemHealth(): array
    {
        $metrics = $this->getRealTimeMetrics();

        $health = [
            'status' => 'healthy',
            'issues' => [],
            'warnings' => [],
        ];

        // Check for dead workers
        if ($metrics['workers']['dead'] > 0) {
            $health['warnings'][] = "{$metrics['workers']['dead']} dead worker(s) detected";
        }

        // Check for high failure rate
        if ($metrics['tasks']['success_rate'] < 90 && $metrics['tasks']['total'] > 100) {
            $health['issues'][] = "Low task success rate: {$metrics['tasks']['success_rate']}%";
            $health['status'] = 'degraded';
        }

        // Check for stalled queue
        if ($metrics['tasks']['queue_size'] > 1000 && $metrics['workers']['active'] === 0) {
            $health['issues'][] = "Large queue with no active workers";
            $health['status'] = 'critical';
        }

        // Check for low worker utilization
        if ($metrics['workers']['active'] > 0 && $metrics['workers']['utilization_rate'] < 20) {
            $health['warnings'][] = "Low worker utilization: {$metrics['workers']['utilization_rate']}%";
        }

        return $health;
    }
}
