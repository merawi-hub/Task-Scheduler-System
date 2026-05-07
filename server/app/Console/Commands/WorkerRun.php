<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Exception;

class WorkerRun extends Command
{
    protected $signature = 'worker:run 
                            {--key=worker-001 : Unique worker identifier}
                            {--sleep=3 : Seconds to sleep when no tasks available}
                            {--heartbeat=15 : Heartbeat interval in seconds}';

    protected $description = 'Run a distributed task scheduler worker node';

    private string $workerKey;
    private string $baseUrl;
    private int $sleepSeconds;
    private int $heartbeatInterval;
    private float $lastHeartbeat = 0;

    public function handle(): int
    {
        $this->workerKey = $this->option('key');
        $this->sleepSeconds = (int) $this->option('sleep');
        $this->heartbeatInterval = (int) $this->option('heartbeat');
        $this->baseUrl = config('app.url') . '/api';

        $this->info("🚀 Starting worker: {$this->workerKey}");
        $this->info("📡 API Base URL: {$this->baseUrl}");

        // Register worker
        if (!$this->register()) {
            $this->error('Failed to register worker. Exiting.');
            return 1;
        }

        $this->info("✅ Worker registered successfully");
        $this->info("🔄 Entering task polling loop...\n");

        // Main worker loop
        $this->pollLoop();

        return 0;
    }

    /**
     * Register worker with coordinator
     */
    private function register(): bool
    {
        try {
            $response = Http::post("{$this->baseUrl}/workers/register", [
                'worker_key' => $this->workerKey,
                'hostname' => gethostname(),
                'ip_address' => gethostbyname(gethostname()),
            ]);

            return $response->successful();
        } catch (Exception $e) {
            $this->error("Registration failed: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Main polling loop
     */
    private function pollLoop(): void
    {
        while (true) {
            try {
                // Send heartbeat if needed
                $this->sendHeartbeatIfNeeded();

                // Try to claim a task
                $task = $this->claimTask();

                if ($task) {
                    $this->executeTask($task);
                } else {
                    $this->comment("💤 No tasks available, sleeping for {$this->sleepSeconds}s");
                    sleep($this->sleepSeconds);
                }
            } catch (Exception $e) {
                $this->error("❌ Error in worker loop: {$e->getMessage()}");
                sleep($this->sleepSeconds);
            }
        }
    }

    /**
     * Claim next available task
     */
    private function claimTask(): ?array
    {
        try {
            $response = Http::withHeaders([
                'X-Worker-Key' => $this->workerKey,
            ])->get("{$this->baseUrl}/tasks/next");

            if ($response->status() === 204) {
                return null;
            }

            if ($response->successful()) {
                return $response->json('task');
            }

            return null;
        } catch (Exception $e) {
            $this->error("Failed to claim task: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Execute a task
     */
    private function executeTask(array $task): void
    {
        $taskId = $task['id'];
        $jobId = $task['job_id'];
        $taskIndex = $task['task_index'];

        $this->info("📋 Claimed Task #{$taskId} (Job #{$jobId}, Index {$taskIndex})");

        try {
            // Mark task as started
            $this->markTaskStarted($taskId);

            $startTime = microtime(true);

            // Execute the actual task logic
            $result = $this->processTask($task);

            $duration = (int) ((microtime(true) - $startTime) * 1000);

            // Mark task as completed
            $this->markTaskCompleted($taskId, $result, $duration);

            $this->info("✅ Task #{$taskId} completed in {$duration}ms\n");
        } catch (Exception $e) {
            $this->error("❌ Task #{$taskId} failed: {$e->getMessage()}");
            $this->markTaskFailed($taskId, $e->getMessage());
        }
    }

    /**
     * Process task payload (simulate work)
     */
    private function processTask(array $task): array
    {
        $payload = $task['payload'];
        $type = $payload['type'] ?? 'unknown';

        $this->line("   Type: {$type}");
        $this->line("   Processing items {$payload['start_index']} to {$payload['end_index']}");

        // Simulate work based on task type
        $workDuration = match ($type) {
            'csv_aggregate' => rand(1, 3),
            'image_process' => rand(2, 5),
            'data_transform' => rand(1, 4),
            default => rand(1, 3),
        };

        $this->line("   ⏳ Simulating {$workDuration}s of work...");
        sleep($workDuration);

        // Simulate occasional failures (5% chance)
        if (rand(1, 100) <= 5) {
            throw new Exception("Simulated random failure");
        }

        return [
            'processed_items' => $payload['items_count'] ?? ($payload['end_index'] - $payload['start_index'] + 1),
            'type' => $type,
            'worker' => $this->workerKey,
        ];
    }

    /**
     * Mark task as started
     */
    private function markTaskStarted(int $taskId): void
    {
        Http::post("{$this->baseUrl}/tasks/{$taskId}/start", [
            'worker_key' => $this->workerKey,
        ]);
    }

    /**
     * Mark task as completed
     */
    private function markTaskCompleted(int $taskId, array $result, int $durationMs): void
    {
        Http::post("{$this->baseUrl}/tasks/{$taskId}/complete", [
            'worker_key' => $this->workerKey,
            'result' => $result,
            'duration_ms' => $durationMs,
        ]);
    }

    /**
     * Mark task as failed
     */
    private function markTaskFailed(int $taskId, string $reason): void
    {
        Http::post("{$this->baseUrl}/tasks/{$taskId}/fail", [
            'worker_key' => $this->workerKey,
            'reason' => $reason,
        ]);
    }

    /**
     * Send heartbeat if interval has passed
     */
    private function sendHeartbeatIfNeeded(): void
    {
        $now = microtime(true);

        if ($now - $this->lastHeartbeat >= $this->heartbeatInterval) {
            try {
                Http::post("{$this->baseUrl}/workers/{$this->workerKey}/heartbeat", [
                    'status' => 'idle',
                ]);

                $this->lastHeartbeat = $now;
            } catch (Exception $e) {
                $this->warn("⚠️  Heartbeat failed: {$e->getMessage()}");
            }
        }
    }
}
