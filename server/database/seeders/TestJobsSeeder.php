<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SchedulerJob;
use App\Models\Task;
use Illuminate\Support\Facades\DB;

class TestJobsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get test users
        $regularUser = User::where('email', 'user@taskscheduler.com')->first();
        $adminUser = User::where('email', 'admin@taskscheduler.com')->first();

        if (!$regularUser || !$adminUser) {
            $this->command->error('Test users not found. Please run AdminUserSeeder first.');
            return;
        }

        $this->command->info('Creating test jobs...');

        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Clear existing data
        DB::table('task_logs')->truncate();
        DB::table('tasks')->truncate();
        DB::table('scheduler_jobs')->truncate();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create jobs for regular user
        $this->createJobsForUser($regularUser, 5);
        
        // Create jobs for admin user
        $this->createJobsForUser($adminUser, 3);

        $this->command->info('Test jobs created successfully!');
    }

    private function createJobsForUser(User $user, int $count): void
    {
        $jobTypes = [
            'video_processing',
            'image_processing',
            'data_processing',
            'ml_training',
            'report_generation',
            'batch_processing'
        ];

        $statuses = ['pending', 'running', 'completed', 'failed'];
        $priorities = [1, 3, 5, 7, 10];

        for ($i = 1; $i <= $count; $i++) {
            $status = $statuses[array_rand($statuses)];
            $totalTasks = rand(10, 100);
            $completedTasks = $status === 'completed' ? $totalTasks : rand(0, $totalTasks - 1);
            $failedTasks = $status === 'failed' ? rand(1, 5) : 0;

            $job = SchedulerJob::create([
                'user_id' => $user->id,
                'name' => $this->generateJobName($i),
                'description' => $this->generateDescription(),
                'type' => $jobTypes[array_rand($jobTypes)],
                'status' => $status,
                'priority' => $priorities[array_rand($priorities)],
                'submitted_by' => $user->name,
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'failed_tasks' => $failedTasks,
                'started_at' => $status !== 'pending' ? now()->subHours(rand(1, 24)) : null,
                'completed_at' => $status === 'completed' ? now()->subHours(rand(0, 12)) : null,
                'created_at' => now()->subDays(rand(0, 7)),
            ]);

            // Create tasks for the job
            $this->createTasksForJob($job, $totalTasks, $completedTasks, $failedTasks);

            $this->command->info("Created job: {$job->name} for {$user->name}");
        }
    }

    private function createTasksForJob(SchedulerJob $job, int $total, int $completed, int $failed): void
    {
        $taskStatuses = [];
        
        // Add completed tasks (use 'done' status)
        for ($i = 0; $i < $completed; $i++) {
            $taskStatuses[] = 'done';
        }
        
        // Add failed tasks
        for ($i = 0; $i < $failed; $i++) {
            $taskStatuses[] = 'failed';
        }
        
        // Fill remaining with pending/running
        $remaining = $total - $completed - $failed;
        for ($i = 0; $i < $remaining; $i++) {
            $taskStatuses[] = $job->status === 'running' && $i < 3 ? 'running' : 'pending';
        }

        // Shuffle to randomize
        shuffle($taskStatuses);

        // Create tasks
        foreach ($taskStatuses as $index => $status) {
            Task::create([
                'job_id' => $job->id,
                'task_index' => $index,
                'payload' => ['task_id' => $index, 'data' => 'Sample task data'],
                'status' => $status,
                'worker_id' => null, // No workers yet
                'assigned_at' => $status !== 'pending' ? now()->subHours(rand(1, 12)) : null,
                'started_at' => $status !== 'pending' ? now()->subHours(rand(1, 12)) : null,
                'completed_at' => $status === 'done' ? now()->subHours(rand(0, 6)) : null,
                'failure_reason' => $status === 'failed' ? 'Task execution failed: Timeout error' : null,
            ]);
        }
    }

    private function generateJobName(int $index): string
    {
        $names = [
            'Video Encoding Pipeline',
            'Image Thumbnail Generation',
            'Data Analytics Processing',
            'ML Model Training',
            'Monthly Report Generation',
            'Batch Email Processing',
            'Database Migration',
            'File Conversion Task',
            'API Data Sync',
            'Log Analysis Job'
        ];

        return $names[array_rand($names)] . " #{$index}";
    }

    private function generateDescription(): string
    {
        $descriptions = [
            'Process large dataset with distributed workers',
            'Convert and optimize media files for web delivery',
            'Generate comprehensive analytics reports',
            'Train machine learning model on production data',
            'Batch process user-submitted content',
            'Synchronize data across multiple systems',
            'Perform scheduled maintenance tasks',
            'Execute data transformation pipeline',
        ];

        return $descriptions[array_rand($descriptions)];
    }
}
