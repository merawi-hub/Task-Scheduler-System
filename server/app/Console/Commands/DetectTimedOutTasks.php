<?php

namespace App\Console\Commands;

use App\Services\WorkerHealthService;
use Illuminate\Console\Command;

class DetectTimedOutTasks extends Command
{
    protected $signature = 'tasks:detect-timeout';

    protected $description = 'Detect and reassign timed-out tasks';

    public function __construct(
        private WorkerHealthService $healthService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('🔍 Scanning for timed-out tasks...');

        $stats = $this->healthService->detectTimedOutTasks();

        if ($stats['timed_out_count'] === 0) {
            $this->info('✅ No timed-out tasks found');
            return 0;
        }

        $this->warn("⚠️  Found {$stats['timed_out_count']} timed-out task(s)");

        foreach ($stats['tasks'] as $task) {
            $workerKey = $task['worker_key'] ?? 'unknown';
            $this->line("   - Task #{$task['task_id']} (Job #{$task['job_id']}) from worker {$workerKey}");
        }

        return 0;
    }
}
