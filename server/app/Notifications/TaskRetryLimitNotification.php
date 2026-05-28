<?php

namespace App\Notifications;

use App\Models\SchedulerJob;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskRetryLimitNotification extends Notification
{
    use Queueable;

    public function __construct(
        private SchedulerJob $job,
        private int $taskId
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task_retry_limit',
            'title' => 'Task Retry Limit Reached',
            'message' => "A task in job \"{$this->job->name}\" has reached its retry limit and permanently failed.",
            'job_id' => $this->job->id,
            'job_name' => $this->job->name,
            'task_id' => $this->taskId,
            'icon' => 'x-circle',
            'color' => 'red',
            'severity' => 'high',
        ];
    }
}
