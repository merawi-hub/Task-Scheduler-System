<?php

namespace App\Console\Commands;

use App\Services\WorkerHealthService;
use Illuminate\Console\Command;

class DetectDeadWorkers extends Command
{
    protected $signature = 'workers:detect-dead';

    protected $description = 'Detect dead workers and reassign their tasks';

    public function __construct(
        private WorkerHealthService $healthService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('🔍 Scanning for dead workers...');

        $stats = $this->healthService->detectDeadWorkers();

        if ($stats['dead_workers_count'] === 0) {
            $this->info('✅ All workers are healthy');
            return 0;
        }

        $this->warn("⚠️  Found {$stats['dead_workers_count']} dead worker(s)");
        $this->info("📋 Reassigned {$stats['reassigned_tasks_count']} task(s)");

        foreach ($stats['workers'] as $worker) {
            $this->line("   - {$worker['worker_key']}: {$worker['reassigned_tasks']} tasks reassigned");
        }

        return 0;
    }
}
