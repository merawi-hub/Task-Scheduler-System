<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WorkerHealthService;
use App\Services\MetricsService;

class MonitorSystemHealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:monitor-health';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor system health, detect dead workers and timed-out tasks';

    /**
     * Execute the console command.
     */
    public function handle(WorkerHealthService $healthService, MetricsService $metricsService): int
    {
        $this->info('Starting system health monitoring...');

        // Detect and handle dead workers
        $this->info('Checking for dead workers...');
        $deadWorkerStats = $healthService->detectDeadWorkers();
        
        if ($deadWorkerStats['dead_workers_count'] > 0) {
            $this->warn("Found {$deadWorkerStats['dead_workers_count']} dead worker(s)");
            $this->warn("Reassigned {$deadWorkerStats['reassigned_tasks_count']} task(s)");
            
            foreach ($deadWorkerStats['workers'] as $worker) {
                $this->line("  - {$worker['worker_key']}: {$worker['reassigned_tasks']} tasks reassigned");
            }
        } else {
            $this->info('No dead workers detected');
        }

        // Detect and handle timed-out tasks
        $this->info('Checking for timed-out tasks...');
        $timeoutStats = $healthService->detectTimedOutTasks();
        
        if ($timeoutStats['timed_out_count'] > 0) {
            $this->warn("Found {$timeoutStats['timed_out_count']} timed-out task(s)");
            
            foreach ($timeoutStats['tasks'] as $task) {
                $this->line("  - Task #{$task['task_id']} (Job #{$task['job_id']}) on worker {$task['worker_key']}");
            }
        } else {
            $this->info('No timed-out tasks detected');
        }

        // Display system health
        $health = $metricsService->getSystemHealth();
        $this->newLine();
        $this->info("System Health: {$health['status']}");
        
        if (!empty($health['issues'])) {
            $this->error('Issues:');
            foreach ($health['issues'] as $issue) {
                $this->line("  - {$issue}");
            }
        }
        
        if (!empty($health['warnings'])) {
            $this->warn('Warnings:');
            foreach ($health['warnings'] as $warning) {
                $this->line("  - {$warning}");
            }
        }

        $this->newLine();
        $this->info('Health monitoring completed');

        return Command::SUCCESS;
    }
}
