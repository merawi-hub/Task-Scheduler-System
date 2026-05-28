<?php

namespace App\Notifications;

use App\Models\SchedulerJob;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JobFailedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public SchedulerJob $job,
        public ?string $reason = null
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Job Failed',
            'message' => "Your job '{$this->job->name}' has failed." . ($this->reason ? " Reason: {$this->reason}" : ''),
            'job_id' => $this->job->id,
            'job_name' => $this->job->name,
            'failed_tasks' => $this->job->failed_tasks,
            'total_tasks' => $this->job->total_tasks,
            'reason' => $this->reason,
            'category' => 'job',
            'priority' => 'high',
            'action_url' => "/jobs/{$this->job->id}",
            'action_text' => 'View Details',
            'icon' => 'x-circle',
            'color' => 'red',
        ];
    }
}
