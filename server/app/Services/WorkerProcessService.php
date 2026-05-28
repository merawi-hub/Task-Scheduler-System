<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * WorkerProcessService
 *
 * Manages worker processes programmatically from the UI.
 * Spawns, tracks, and terminates worker processes.
 */
class WorkerProcessService
{
    private const WORKERS_FILE = 'workers_processes.json';

    /**
     * Start a new worker process
     *
     * @param string $workerKey
     * @param array $options
     * @return array
     */
    public function startWorker(string $workerKey, array $options = []): array
    {
        $sleep = $options['sleep'] ?? 2;
        $heartbeat = $options['heartbeat'] ?? 15;
        $failRate = $options['fail_rate'] ?? 0;

        // Build the command
        $command = sprintf(
            'php %s worker:run --key=%s --sleep=%d --heartbeat=%d --fail-rate=%d',
            base_path('artisan'),
            escapeshellarg($workerKey),
            $sleep,
            $heartbeat,
            $failRate
        );

        try {
            // Start the process in the background
            if (PHP_OS_FAMILY === 'Windows') {
                // Windows: use START command to run in background
                $fullCommand = sprintf('start /B %s > NUL 2>&1', $command);
                pclose(popen($fullCommand, 'r'));
                $pid = null; // Windows doesn't easily give us PID
            } else {
                // Linux/Mac: use nohup and get PID
                $fullCommand = sprintf('nohup %s > /dev/null 2>&1 & echo $!', $command);
                $pid = (int) shell_exec($fullCommand);
            }

            // Store worker process info
            $this->saveWorkerProcess($workerKey, [
                'worker_key' => $workerKey,
                'pid' => $pid,
                'command' => $command,
                'started_at' => now()->toIso8601String(),
                'options' => $options,
                'status' => 'running',
            ]);

            Log::info("Worker process started", [
                'worker_key' => $workerKey,
                'pid' => $pid,
                'command' => $command,
            ]);

            return [
                'success' => true,
                'message' => "Worker {$workerKey} started successfully",
                'worker_key' => $workerKey,
                'pid' => $pid,
            ];

        } catch (\Exception $e) {
            Log::error("Failed to start worker process", [
                'worker_key' => $workerKey,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => "Failed to start worker: {$e->getMessage()}",
            ];
        }
    }

    /**
     * Start multiple workers at once
     *
     * @param int $count
     * @param array $options
     * @return array
     */
    public function startMultipleWorkers(int $count, array $options = []): array
    {
        $results = [];
        $prefix = $options['prefix'] ?? 'worker';

        for ($i = 1; $i <= $count; $i++) {
            $workerKey = "{$prefix}-{$i}";
            $results[] = $this->startWorker($workerKey, $options);

            // Small delay between spawns to avoid race conditions
            usleep(100000); // 100ms
        }

        $successful = collect($results)->where('success', true)->count();

        return [
            'success' => $successful > 0,
            'message' => "Started {$successful} out of {$count} workers",
            'results' => $results,
            'total' => $count,
            'successful' => $successful,
            'failed' => $count - $successful,
        ];
    }

    /**
     * Stop a worker process
     *
     * @param string $workerKey
     * @return array
     */
    public function stopWorker(string $workerKey): array
    {
        $processes = $this->getWorkerProcesses();

        if (!isset($processes[$workerKey])) {
            return [
                'success' => false,
                'message' => "Worker process {$workerKey} not found in tracking",
            ];
        }

        $process = $processes[$workerKey];
        $pid = $process['pid'] ?? null;

        try {
            if ($pid && $this->isProcessRunning($pid)) {
                // Kill the process
                if (PHP_OS_FAMILY === 'Windows') {
                    exec("taskkill /F /PID {$pid} 2>&1");
                } else {
                    exec("kill -9 {$pid} 2>&1");
                }
            }

            // Remove from tracking
            $this->removeWorkerProcess($workerKey);

            Log::info("Worker process stopped", [
                'worker_key' => $workerKey,
                'pid' => $pid,
            ]);

            return [
                'success' => true,
                'message' => "Worker {$workerKey} stopped successfully",
                'worker_key' => $workerKey,
            ];

        } catch (\Exception $e) {
            Log::error("Failed to stop worker process", [
                'worker_key' => $workerKey,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => "Failed to stop worker: {$e->getMessage()}",
            ];
        }
    }

    /**
     * Stop all worker processes
     *
     * @return array
     */
    public function stopAllWorkers(): array
    {
        $processes = $this->getWorkerProcesses();
        $results = [];

        foreach (array_keys($processes) as $workerKey) {
            $results[] = $this->stopWorker($workerKey);
        }

        $successful = collect($results)->where('success', true)->count();

        return [
            'success' => true,
            'message' => "Stopped {$successful} workers",
            'results' => $results,
            'total' => count($processes),
            'successful' => $successful,
        ];
    }

    /**
     * Get all tracked worker processes
     *
     * @return array
     */
    public function getWorkerProcesses(): array
    {
        if (!Storage::exists(self::WORKERS_FILE)) {
            return [];
        }

        $content = Storage::get(self::WORKERS_FILE);
        $processes = json_decode($content, true) ?? [];

        // Update status based on actual process state
        foreach ($processes as $key => &$process) {
            $pid = $process['pid'] ?? null;
            if ($pid) {
                $process['is_running'] = $this->isProcessRunning($pid);
                $process['status'] = $process['is_running'] ? 'running' : 'stopped';
            } else {
                $process['is_running'] = false;
                $process['status'] = 'unknown';
            }
        }

        return $processes;
    }

    /**
     * Check if a process is running
     *
     * @param int $pid
     * @return bool
     */
    private function isProcessRunning(int $pid): bool
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec("tasklist /FI \"PID eq {$pid}\" 2>&1");
            return $output && strpos($output, (string) $pid) !== false;
        } else {
            $output = shell_exec("ps -p {$pid} 2>&1");
            return $output && strpos($output, (string) $pid) !== false;
        }
    }

    /**
     * Save worker process info
     *
     * @param string $workerKey
     * @param array $data
     * @return void
     */
    private function saveWorkerProcess(string $workerKey, array $data): void
    {
        $processes = $this->getWorkerProcesses();
        $processes[$workerKey] = $data;
        Storage::put(self::WORKERS_FILE, json_encode($processes, JSON_PRETTY_PRINT));
    }

    /**
     * Remove worker process from tracking
     *
     * @param string $workerKey
     * @return void
     */
    private function removeWorkerProcess(string $workerKey): void
    {
        $processes = $this->getWorkerProcesses();
        unset($processes[$workerKey]);
        Storage::put(self::WORKERS_FILE, json_encode($processes, JSON_PRETTY_PRINT));
    }

    /**
     * Clean up stopped processes from tracking
     *
     * @return array
     */
    public function cleanupStoppedProcesses(): array
    {
        $processes = $this->getWorkerProcesses();
        $removed = 0;

        foreach ($processes as $key => $process) {
            if (!$process['is_running']) {
                $this->removeWorkerProcess($key);
                $removed++;
            }
        }

        return [
            'success' => true,
            'message' => "Cleaned up {$removed} stopped processes",
            'removed' => $removed,
        ];
    }
}
