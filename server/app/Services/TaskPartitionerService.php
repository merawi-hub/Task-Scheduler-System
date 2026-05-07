<?php

namespace App\Services;

use App\Models\SchedulerJob;
use App\Models\Task;

class TaskPartitionerService
{
    /**
     * Partition a job into multiple tasks
     *
     * @param SchedulerJob $job
     * @param int $taskCount
     * @param array|null $dataset
     * @return array Array of created tasks
     */
    public function partition(SchedulerJob $job, int $taskCount, ?array $dataset = null): array
    {
        $tasks = [];

        if ($dataset) {
            // Partition actual dataset
            $chunkSize = (int) ceil(count($dataset) / $taskCount);
            $chunks = array_chunk($dataset, $chunkSize);

            foreach ($chunks as $index => $chunk) {
                $tasks[] = $this->createTask($job, $index, [
                    'type' => $job->type,
                    'data' => $chunk,
                    'start_index' => $index * $chunkSize,
                    'end_index' => min(($index + 1) * $chunkSize - 1, count($dataset) - 1),
                    'chunk_size' => count($chunk),
                ]);
            }
        } else {
            // Generate demo tasks with simulated payloads
            $itemsPerTask = (int) ceil(1000 / $taskCount);

            for ($i = 0; $i < $taskCount; $i++) {
                $startIndex = $i * $itemsPerTask;
                $endIndex = min(($i + 1) * $itemsPerTask - 1, 999);

                $tasks[] = $this->createTask($job, $i, [
                    'type' => $job->type,
                    'start_index' => $startIndex,
                    'end_index' => $endIndex,
                    'items_count' => $endIndex - $startIndex + 1,
                    'job_name' => $job->name,
                ]);
            }
        }

        return $tasks;
    }

    /**
     * Create a single task
     *
     * @param SchedulerJob $job
     * @param int $index
     * @param array $payload
     * @return Task
     */
    private function createTask(SchedulerJob $job, int $index, array $payload): Task
    {
        return Task::create([
            'job_id' => $job->id,
            'task_index' => $index,
            'payload' => $payload,
            'status' => 'pending',
            'retry_count' => 0,
            'max_retries' => 3,
            'timeout_seconds' => config('scheduler.task_default_timeout', 300),
        ]);
    }
}
