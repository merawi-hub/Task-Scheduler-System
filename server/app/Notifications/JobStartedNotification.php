<?php

namespace App\Notifications;

use App\Models\SchedulerJob;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JobStartedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private SchedulerJob $job
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'job_started',
            'title' => 'Job Started',
            'message' => "Your job \"{$this->job->name}\" has started processing.",
            'job_id' => $this->job->id,
            'job_name' => $this->job->name,
            'total_tasks' => $this->job->total_tasks,
            'priority' => $this->job->priority,
            'started_at' => $this->job->started_at?->toIso8601String(),
            'icon' => 'play',
            'color' => 'blue',
        ];
    }
}
