<?php

namespace App\Services;

use App\Models\User;
use App\Models\SchedulerJob;
use App\Models\Worker;
use App\Notifications\JobCompletedNotification;
use App\Notifications\JobFailedNotification;
use Illuminate\Support\Facades\Notification;

/**
 * NotificationService
 *
 * Centralized service for managing all system notifications
 */
class NotificationService
{
    /**
     * Notify user when their job completes successfully
     */
    public function notifyJobCompleted(SchedulerJob $job): void
    {
        if ($job->user) {
            $job->user->notify(new JobCompletedNotification($job));
        }
    }

    /**
     * Notify user when their job fails
     */
    public function notifyJobFailed(SchedulerJob $job, ?string $reason = null): void
    {
        if ($job->user) {
            $job->user->notify(new JobFailedNotification($job, $reason));
        }
    }

    /**
     * Notify user when job starts processing
     */
    public function notifyJobStarted(SchedulerJob $job): void
    {
        if ($job->user) {
            $job->user->notify(new \App\Notifications\JobStartedNotification($job));
        }
    }

    /**
     * Notify admins when a worker dies
     */
    public function notifyWorkerDied(Worker $worker): void
    {
        $admins = User::where('is_admin', true)->get();

        Notification::send($admins, new \App\Notifications\WorkerDiedNotification($worker));
    }

    /**
     * Notify admins when system has no workers
     */
    public function notifyNoWorkersAvailable(): void
    {
        $admins = User::where('is_admin', true)->get();

        Notification::send($admins, new \App\Notifications\NoWorkersNotification());
    }

    /**
     * Notify user when job is taking longer than expected
     */
    public function notifyJobDelayed(SchedulerJob $job): void
    {
        if ($job->user) {
            $job->user->notify(new \App\Notifications\JobDelayedNotification($job));
        }
    }

    /**
     * Notify admins about system health issues
     */
    public function notifySystemHealthIssue(string $issue, array $details = []): void
    {
        $admins = User::where('is_admin', true)->get();

        Notification::send($admins, new \App\Notifications\SystemHealthNotification($issue, $details));
    }

    /**
     * Notify user when job reaches milestone (25%, 50%, 75%)
     */
    public function notifyJobProgress(SchedulerJob $job, int $percentage): void
    {
        if ($job->user && in_array($percentage, [25, 50, 75])) {
            $job->user->notify(new \App\Notifications\JobProgressNotification($job, $percentage));
        }
    }

    /**
     * Notify admins when task retry limit is reached
     */
    public function notifyTaskRetryLimitReached(SchedulerJob $job, int $taskId): void
    {
        $admins = User::where('is_admin', true)->get();

        Notification::send($admins, new \App\Notifications\TaskRetryLimitNotification($job, $taskId));
    }

    /**
     * Send welcome notification to new users
     */
    public function notifyWelcome(User $user): void
    {
        $user->notify(new \App\Notifications\WelcomeNotification($user));
    }

    /**
     * Notify user about account activity
     */
    public function notifyAccountActivity(User $user, string $activity, array $details = []): void
    {
        $user->notify(new \App\Notifications\AccountActivityNotification($activity, $details));
    }
}
