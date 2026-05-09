<?php

namespace App\Services;

use App\Models\SchedulerJob;
use App\Models\Task;

/**
 * TaskPartitionerService
 *
 * Splits a job into N tasks. Each task gets a payload that tells a worker
 * exactly which slice of work to process.
 *
 * For result_processing with 1,000 records and 10 tasks:
 *
 *   Task 1  → records  1 –  100  (start_index=0,  end_index=99)
 *   Task 2  → records 101 – 200  (start_index=100, end_index=199)
 *   ...
 *   Task 10 → records 901 – 1000 (start_index=900, end_index=999)
 *
 * The payload uses 0-based indices internally; the human-readable
 * "record_from" / "record_to" fields are 1-based for display.
 */
class TaskPartitionerService
{
    // Default records per task for result_processing
    private const DEFAULT_RECORDS_PER_TASK = 100;

    /**
     * Main entry point — dispatch to the right partitioner.
     *
     * @param  SchedulerJob  $job
     * @param  int           $taskCount
     * @param  array|null    $dataset   Optional real dataset to chunk
     * @return Task[]
     */
    public function partition(SchedulerJob $job, int $taskCount, ?array $dataset = null): array
    {
        $taskConfig = $this->resolveTaskConfig($job);

        if ($job->type === 'image_processing' && $job->input_files) {
            return $this->partitionImageProcessingJob($job, $taskCount, $taskConfig);
        }

        if ($job->type === 'result_processing') {
            return $this->partitionResultProcessingJob($job, $taskCount, $taskConfig, $dataset);
        }

        if ($dataset) {
            return $this->partitionDataset($job, $taskCount, $taskConfig, $dataset);
        }

        return $this->partitionGeneric($job, $taskCount, $taskConfig);
    }

    // =========================================================================
    // result_processing
    // =========================================================================

    /**
     * Partition a result_processing job into exactly $taskCount tasks.
     *
     * Each task payload:
     * {
     *   "type":          "result_processing",
     *   "task_number":   1,          // 1-based, for display
     *   "total_tasks":   10,
     *   "operations":    ["calculate_grades", "generate_report", "validate_data"],
     *   "start_index":   0,          // 0-based, inclusive
     *   "end_index":     99,         // 0-based, inclusive
     *   "record_from":   1,          // 1-based, for display
     *   "record_to":     100,        // 1-based, for display
     *   "records_count": 100,
     *   "total_records": 1000,
     *   "job_name":      "Student Result Processing",
     *   "records":       [ {...}, ... ]   // actual student records
     * }
     */
    private function partitionResultProcessingJob(
        SchedulerJob $job,
        int $taskCount,
        array $taskConfig,
        ?array $dataset = null
    ): array {
        $jobConfig  = config('scheduler.job_types.result_processing', []);
        $operations = $jobConfig['operations'] ?? ['calculate_grades', 'generate_report', 'validate_data'];
        $recordsPerTask = $jobConfig['records_per_task'] ?? self::DEFAULT_RECORDS_PER_TASK;

        // Total records = taskCount × recordsPerTask (e.g. 10 × 100 = 1,000)
        $totalRecords = $taskCount * $recordsPerTask;

        // Use provided dataset or generate a simulated one
        $records = $dataset ?? $this->generateStudentRecords($totalRecords);

        // Recalculate in case dataset has a different length
        $actualTotal  = count($records);
        $chunkSize    = (int) ceil($actualTotal / $taskCount);
        $chunks       = array_chunk($records, $chunkSize);
        $tasks        = [];

        foreach ($chunks as $index => $chunk) {
            // 0-based indices
            $startIndex = $index * $chunkSize;
            $endIndex   = min($startIndex + count($chunk) - 1, $actualTotal - 1);

            // 1-based display values
            $recordFrom = $startIndex + 1;
            $recordTo   = $endIndex + 1;

            $tasks[] = Task::create([
                'job_id'          => $job->id,
                'task_index'      => $index,
                'status'          => 'pending',
                'retry_count'     => 0,
                'max_retries'     => $taskConfig['max_retries'],
                'timeout_seconds' => $taskConfig['timeout_seconds'],
                'payload'         => [
                    'type'          => 'result_processing',
                    'task_number'   => $index + 1,
                    'total_tasks'   => $taskCount,
                    'operations'    => $operations,
                    // 0-based (used by workers)
                    'start_index'   => $startIndex,
                    'end_index'     => $endIndex,
                    // 1-based (used for display)
                    'record_from'   => $recordFrom,
                    'record_to'     => $recordTo,
                    'records_count' => count($chunk),
                    'total_records' => $actualTotal,
                    'job_name'      => $job->name,
                    'records'       => $chunk,
                ],
            ]);
        }

        return $tasks;
    }

    /**
     * Generate a realistic simulated dataset of student records.
     *
     * Each record:
     * {
     *   "id":      1,
     *   "name":    "Student 1",
     *   "subject": "Mathematics",
     *   "scores":  [85, 90, 78, 92, 88]
     * }
     */
    private function generateStudentRecords(int $count): array
    {
        $subjects = [
            'Mathematics', 'Physics', 'Chemistry', 'Biology',
            'English', 'History', 'Geography', 'Computer Science',
        ];
        $records = [];

        for ($i = 1; $i <= $count; $i++) {
            $records[] = [
                'id'      => $i,
                'name'    => "Student {$i}",
                'subject' => $subjects[($i - 1) % count($subjects)],
                'scores'  => [
                    rand(50, 100),
                    rand(50, 100),
                    rand(50, 100),
                    rand(50, 100),
                    rand(50, 100),
                ],
            ];
        }

        return $records;
    }

    // =========================================================================
    // image_processing
    // =========================================================================

    private function partitionImageProcessingJob(
        SchedulerJob $job,
        int $taskCount,
        array $taskConfig
    ): array {
        $tasks         = [];
        $images        = $job->input_files;
        $totalImages   = count($images);
        $imagesPerTask = (int) ceil($totalImages / $taskCount);
        $imageChunks   = array_chunk($images, $imagesPerTask);

        foreach ($imageChunks as $index => $imageChunk) {
            $startIndex = $index * $imagesPerTask;
            $endIndex   = min($startIndex + count($imageChunk) - 1, $totalImages - 1);

            $tasks[] = Task::create([
                'job_id'           => $job->id,
                'task_index'       => $index,
                'payload'          => [
                    'type'         => 'image_processing',
                    'task_number'  => $index + 1,
                    'total_tasks'  => $taskCount,
                    'operations'   => $job->operations ?? $taskConfig['operations'],
                    'start_index'  => $startIndex,
                    'end_index'    => $endIndex,
                    'record_from'  => $startIndex + 1,
                    'record_to'    => $endIndex + 1,
                    'images_count' => count($imageChunk),
                ],
                'input_images'     => $imageChunk,
                'output_images'    => [],
                'images_processed' => 0,
                'status'           => 'pending',
                'retry_count'      => 0,
                'max_retries'      => $taskConfig['max_retries'],
                'timeout_seconds'  => $taskConfig['timeout_seconds'],
            ]);
        }

        return $tasks;
    }

    // =========================================================================
    // Generic — real dataset provided
    // =========================================================================

    private function partitionDataset(
        SchedulerJob $job,
        int $taskCount,
        array $taskConfig,
        array $dataset
    ): array {
        $tasks     = [];
        $total     = count($dataset);
        $chunkSize = (int) ceil($total / $taskCount);
        $chunks    = array_chunk($dataset, $chunkSize);

        foreach ($chunks as $index => $chunk) {
            $startIndex = $index * $chunkSize;
            $endIndex   = min($startIndex + count($chunk) - 1, $total - 1);

            $tasks[] = $this->createTask($job, $index, [
                'type'          => $job->type,
                'task_number'   => $index + 1,
                'total_tasks'   => $taskCount,
                'data'          => $chunk,
                'start_index'   => $startIndex,
                'end_index'     => $endIndex,
                'record_from'   => $startIndex + 1,
                'record_to'     => $endIndex + 1,
                'records_count' => count($chunk),
            ], $taskConfig);
        }

        return $tasks;
    }

    // =========================================================================
    // Generic — no dataset (simulated range-based tasks)
    // =========================================================================

    private function partitionGeneric(
        SchedulerJob $job,
        int $taskCount,
        array $taskConfig
    ): array {
        $tasks        = [];
        $totalItems   = 1000;
        $itemsPerTask = (int) ceil($totalItems / $taskCount);

        for ($i = 0; $i < $taskCount; $i++) {
            $startIndex = $i * $itemsPerTask;
            $endIndex   = min(($i + 1) * $itemsPerTask - 1, $totalItems - 1);

            $tasks[] = $this->createTask($job, $i, [
                'type'          => $job->type,
                'task_number'   => $i + 1,
                'total_tasks'   => $taskCount,
                'start_index'   => $startIndex,
                'end_index'     => $endIndex,
                'record_from'   => $startIndex + 1,
                'record_to'     => $endIndex + 1,
                'items_count'   => $endIndex - $startIndex + 1,
                'records_count' => $endIndex - $startIndex + 1,
                'job_name'      => $job->name,
            ], $taskConfig);
        }

        return $tasks;
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private function createTask(
        SchedulerJob $job,
        int $index,
        array $payload,
        array $taskConfig
    ): Task {
        return Task::create([
            'job_id'          => $job->id,
            'task_index'      => $index,
            'payload'         => $payload,
            'status'          => 'pending',
            'retry_count'     => 0,
            'max_retries'     => $taskConfig['max_retries'],
            'timeout_seconds' => $taskConfig['timeout_seconds'],
        ]);
    }

    private function resolveTaskConfig(SchedulerJob $job): array
    {
        $jobConfig      = config('scheduler.job_types.' . $job->type, []);
        $defaultTimeout = config('scheduler.task_default_timeout', 300);
        $defaultRetries = config('scheduler.max_task_retries', 3);

        return [
            'timeout_seconds' => $jobConfig['default_timeout'] ?? $defaultTimeout,
            'max_retries'     => $jobConfig['max_retries']     ?? $defaultRetries,
            'operations'      => $jobConfig['operations']      ?? [],
        ];
    }
}
