<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Exception;

/**
 * WorkerRun — Pull-Based Worker Node
 *
 * Usage:
 *   php artisan worker:run --key=worker-1
 *   php artisan worker:run --key=worker-2
 *   php artisan worker:run --key=worker-3
 *
 * Each worker independently polls the coordinator asking:
 *   "Do you have work for me?"
 *
 * The coordinator atomically assigns the next pending task.
 * No two workers ever get the same task (SELECT FOR UPDATE).
 */
class WorkerRun extends Command
{
    protected $signature = 'worker:run
                            {--key=worker-1        : Unique worker identifier (e.g. worker-1, worker-2)}
                            {--sleep=2             : Seconds to wait when no tasks are available}
                            {--heartbeat=15        : Heartbeat interval in seconds}
                            {--fail-rate=5         : Simulated failure rate percentage (0-100)}
                            {--max-tasks=0         : Stop after N tasks (0 = run forever)}';

    protected $description = 'Run a pull-based distributed task scheduler worker node';

    private string $workerKey;
    private string $workerToken = '';
    private string $baseUrl;
    private int    $sleepSeconds;
    private int    $heartbeatInterval;
    private int    $failRate;
    private int    $maxTasks;
    private float  $lastHeartbeat = 0;
    private int    $tasksProcessed = 0;

    public function handle(): int
    {
        $this->workerKey         = $this->option('key');
        $this->sleepSeconds      = (int) $this->option('sleep');
        $this->heartbeatInterval = (int) $this->option('heartbeat');
        $this->failRate          = (int) $this->option('fail-rate');
        $this->maxTasks          = (int) $this->option('max-tasks');
        $this->baseUrl           = rtrim(config('app.url'), '/') . '/api';

        $this->printBanner();

        if (!$this->register()) {
            $this->error('❌ Failed to register worker. Is the server running?');
            return 1;
        }

        $this->info("✅ Worker <fg=green>{$this->workerKey}</> registered and ready");
        $this->line('');
        $this->info('🔄 Pull-based polling started. Worker asks: "Give me a task"');
        $this->line(str_repeat('─', 60));

        $this->pollLoop();

        return 0;
    }

    // =========================================================================
    // Registration
    // =========================================================================

    private function register(): bool
    {
        try {
            $response = Http::timeout(10)->post("{$this->baseUrl}/workers/register", [
                'worker_key' => $this->workerKey,
                'hostname'   => gethostname(),
                'ip_address' => gethostbyname(gethostname()),
            ]);

            if (!$response->successful()) {
                $this->error($response->body());
                return false;
            }

            $this->workerToken = (string) $response->json('api_token');
            return $this->workerToken !== '';

        } catch (Exception $e) {
            $this->error("Registration error: {$e->getMessage()}");
            return false;
        }
    }

    // =========================================================================
    // Main pull loop
    // =========================================================================

    private function pollLoop(): void
    {
        while (true) {
            // Stop after maxTasks if set
            if ($this->maxTasks > 0 && $this->tasksProcessed >= $this->maxTasks) {
                $this->info("\n🏁 Reached max-tasks ({$this->maxTasks}). Worker stopping.");
                break;
            }

            try {
                $this->sendHeartbeatIfNeeded();

                // ── PULL: ask coordinator for a task ──────────────────────────
                $task = $this->pullTask();

                if ($task) {
                    $this->executeTask($task);
                    $this->tasksProcessed++;
                } else {
                    $this->comment(
                        "  [{$this->workerKey}] 💤 No tasks available — sleeping {$this->sleepSeconds}s"
                    );
                    sleep($this->sleepSeconds);
                }

            } catch (Exception $e) {
                $this->error("  [{$this->workerKey}] ❌ Loop error: {$e->getMessage()}");
                sleep($this->sleepSeconds);
            }
        }
    }

    // =========================================================================
    // Pull — GET /tasks/next
    // =========================================================================

    private function pullTask(): ?array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(['X-Worker-Token' => $this->workerToken])
                ->get("{$this->baseUrl}/tasks/next");

            if ($response->status() === 204) {
                return null; // No tasks available
            }

            if ($response->successful()) {
                $task = $response->json('task');
                if ($task) {
                    $this->logPull($task);
                }
                return $task;
            }

            return null;

        } catch (Exception $e) {
            $this->warn("  [{$this->workerKey}] Pull failed: {$e->getMessage()}");
            return null;
        }
    }

    private function logPull(array $task): void
    {
        $payload    = $task['payload'] ?? [];
        $taskNum    = $payload['task_number'] ?? ($task['task_index'] + 1);
        $recordFrom = $payload['record_from'] ?? null;
        $recordTo   = $payload['record_to']   ?? null;
        $type       = $payload['type']        ?? $task['type'] ?? 'unknown';

        $range = ($recordFrom && $recordTo)
            ? "records {$recordFrom}→{$recordTo}"
            : "items {$payload['start_index']}→{$payload['end_index']}";

        $this->line('');
        $this->line("  <fg=cyan>[{$this->workerKey}]</> 📥 PULLED Task #{$task['id']}");
        $this->line("  <fg=gray>  ├─ Job:    #{$task['job_id']}</>");
        $this->line("  <fg=gray>  ├─ Task:   #{$taskNum} of {$payload['total_tasks']}</>");
        $this->line("  <fg=gray>  ├─ Type:   {$type}</>");
        $this->line("  <fg=gray>  ├─ Range:  {$range}</>");
        $this->line("  <fg=gray>  └─ Status: pending → <fg=blue>assigned</></>");
    }

    // =========================================================================
    // Execute
    // =========================================================================

    private function executeTask(array $task): void
    {
        $taskId  = $task['id'];
        $payload = $task['payload'] ?? [];
        $type    = $payload['type'] ?? 'generic';

        // ── assigned → running ────────────────────────────────────────────────
        $this->markStarted($taskId);
        $this->line("  <fg=cyan>[{$this->workerKey}]</> ▶  Task #{$taskId} — status: <fg=blue>assigned</> → <fg=yellow>running</>");

        $startMs = (int) (microtime(true) * 1000);

        try {
            $result = match ($type) {
                'result_processing' => $this->processResultProcessingTask($task),
                'image_processing'  => $this->processImageTask($task),
                default             => $this->processGenericTask($task),
            };

            $durationMs = (int) (microtime(true) * 1000) - $startMs;

            // ── running → done ────────────────────────────────────────────────
            $this->markCompleted($taskId, $result, $durationMs);
            $this->line(
                "  <fg=cyan>[{$this->workerKey}]</> ✅ Task #{$taskId} — status: <fg=yellow>running</> → <fg=green>done</> ({$durationMs}ms)"
            );

        } catch (Exception $e) {
            $durationMs = (int) (microtime(true) * 1000) - $startMs;

            // ── running → failed/pending (retry) ──────────────────────────────
            $this->markFailed($taskId, $e->getMessage());
            $this->line(
                "  <fg=cyan>[{$this->workerKey}]</> ❌ Task #{$taskId} failed: {$e->getMessage()}"
            );
        }
    }

    // =========================================================================
    // Task processors
    // =========================================================================

    /**
     * Process a result_processing task.
     *
     * Simulates:
     *   1. calculate_grades  — compute average score for each student
     *   2. generate_report   — format the result
     *   3. validate_data     — check for anomalies
     */
    private function processResultProcessingTask(array $task): array
    {
        $payload    = $task['payload'];
        $records    = $payload['records']    ?? [];
        $operations = $payload['operations'] ?? ['calculate_grades', 'generate_report', 'validate_data'];
        $recordFrom = $payload['record_from'] ?? 1;
        $recordTo   = $payload['record_to']   ?? count($records);

        $this->line(
            "  <fg=cyan>[{$this->workerKey}]</>   📚 Processing {$payload['records_count']} students "
            . "(records {$recordFrom}→{$recordTo})"
        );

        $processed = [];
        $errors    = 0;

        foreach ($records as $student) {
            // Simulate occasional failure
            if ($this->failRate > 0 && rand(1, 100) <= $this->failRate) {
                throw new Exception("Simulated failure on student #{$student['id']}");
            }

            $scores  = $student['scores'] ?? [];
            $average = count($scores) > 0 ? round(array_sum($scores) / count($scores), 2) : 0;
            $grade   = $this->calculateGrade($average);

            $processed[] = [
                'student_id' => $student['id'],
                'name'       => $student['name'],
                'average'    => $average,
                'grade'      => $grade,
                'subject'    => $student['subject'] ?? 'Unknown',
            ];
        }

        // Simulate processing time (proportional to record count)
        $sleepMs = min(count($records) * 2, 3000); // max 3s
        usleep($sleepMs * 1000);

        foreach ($operations as $op) {
            $this->line("  <fg=cyan>[{$this->workerKey}]</>   ✓ {$op}");
        }

        return [
            'type'             => 'result_processing',
            'records_processed'=> count($processed),
            'errors'           => $errors,
            'worker'           => $this->workerKey,
            'record_from'      => $recordFrom,
            'record_to'        => $recordTo,
            'operations_done'  => $operations,
        ];
    }

    private function calculateGrade(float $average): string
    {
        return match (true) {
            $average >= 90 => 'A',
            $average >= 80 => 'B',
            $average >= 70 => 'C',
            $average >= 60 => 'D',
            default        => 'F',
        };
    }

    private function processImageTask(array $task): array
    {
        $inputImages = $task['input_images'] ?? [];
        $operations  = $task['payload']['operations'] ?? ['resize', 'compress'];

        $this->line(
            "  <fg=cyan>[{$this->workerKey}]</>   🖼  Processing " . count($inputImages) . " images"
        );

        $imageService = app(\App\Services\ImageProcessingService::class);
        $outputImages = [];
        $processed    = 0;

        foreach ($inputImages as $imagePath) {
            try {
                $result = $imageService->processImage($imagePath, $operations);
                $outputImages[] = $result;
                $processed++;
            } catch (Exception $e) {
                $this->warn("  [{$this->workerKey}]   ⚠ Failed: {$e->getMessage()}");
            }
        }

        $this->updateTaskImages($task['id'], $outputImages, $processed);

        return [
            'type'             => 'image_processing',
            'records_processed'=> $processed,
            'worker'           => $this->workerKey,
        ];
    }

    private function processGenericTask(array $task): array
    {
        $payload = $task['payload'];
        $items   = $payload['items_count'] ?? $payload['records_count'] ?? 100;

        $this->line(
            "  <fg=cyan>[{$this->workerKey}]</>   ⚙  Processing {$items} items"
        );

        // Simulate failure
        if ($this->failRate > 0 && rand(1, 100) <= $this->failRate) {
            throw new Exception("Simulated random failure");
        }

        sleep(rand(1, 3));

        return [
            'type'             => $payload['type'] ?? 'generic',
            'records_processed'=> $items,
            'worker'           => $this->workerKey,
        ];
    }

    // =========================================================================
    // HTTP helpers
    // =========================================================================

    private function markStarted(int $taskId): void
    {
        Http::withHeaders(['X-Worker-Token' => $this->workerToken])
            ->post("{$this->baseUrl}/tasks/{$taskId}/start");
    }

    private function markCompleted(int $taskId, array $result, int $durationMs): void
    {
        Http::withHeaders(['X-Worker-Token' => $this->workerToken])
            ->post("{$this->baseUrl}/tasks/{$taskId}/complete", [
                'result'      => $result,
                'duration_ms' => $durationMs,
            ]);
    }

    private function markFailed(int $taskId, string $reason): void
    {
        Http::withHeaders(['X-Worker-Token' => $this->workerToken])
            ->post("{$this->baseUrl}/tasks/{$taskId}/fail", [
                'reason' => $reason,
            ]);
    }

    private function updateTaskImages(int $taskId, array $outputImages, int $count): void
    {
        Http::withHeaders(['X-Worker-Token' => $this->workerToken])
            ->post("{$this->baseUrl}/tasks/{$taskId}/update-images", [
                'output_images'    => $outputImages,
                'images_processed' => $count,
            ]);
    }

    private function sendHeartbeatIfNeeded(): void
    {
        $now = microtime(true);
        if ($now - $this->lastHeartbeat < $this->heartbeatInterval) {
            return;
        }

        try {
            Http::withHeaders(['X-Worker-Token' => $this->workerToken])
                ->post("{$this->baseUrl}/workers/{$this->workerKey}/heartbeat");
            $this->lastHeartbeat = $now;
        } catch (Exception $e) {
            $this->warn("  [{$this->workerKey}] ⚠ Heartbeat failed: {$e->getMessage()}");
        }
    }

    // =========================================================================
    // UI
    // =========================================================================

    private function printBanner(): void
    {
        $this->line('');
        $this->line('  <fg=blue>╔══════════════════════════════════════════════╗</>');
        $this->line("  <fg=blue>║</>  🤖 Worker Node: <fg=green>{$this->workerKey}</>                    <fg=blue>║</>");
        $this->line('  <fg=blue>║</>  Pull-Based Distributed Task Scheduler      <fg=blue>║</>');
        $this->line('  <fg=blue>╚══════════════════════════════════════════════╝</>');
        $this->line('');
        $this->line("  API:       {$this->baseUrl}");
        $this->line("  Sleep:     {$this->sleepSeconds}s when idle");
        $this->line("  Heartbeat: every {$this->heartbeatInterval}s");
        $this->line("  Fail rate: {$this->failRate}% (simulation)");
        $this->line('');
    }
}
