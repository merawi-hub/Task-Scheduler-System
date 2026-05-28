<?php

namespace App\Notifications;

use App\Models\SchedulerJob;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JobProgressNotification extends Notification
{
    use Queueable;

    public function __construct(
        private SchedulerJob $job,
        private int $percentage
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'job_progress',
            'title' => 'Job Progress Update',
            'message' => "Your job \"{$this->job->name}\" is {$this->percentage}% complete.",
            'job_id' => $this->job->id,
            'job_name' => $this->job->name,
            'percentage' => $this->percentage,
            'completed_tasks' => $this->job->completed_tasks,
            'total_tasks' => $this->job->total_tasks,
            'icon' => 'trending-up',
            'color' => 'blue',
        ];
    }
}
