<?php

namespace App\Notifications;

use App\Models\SchedulerJob;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JobDelayedNotification extends Notification
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
        $elapsedMinutes = $this->job->started_at
            ? $this->job->started_at->diffInMinutes(now())
            : 0;

        return [
            'type' => 'job_delayed',
            'title' => 'Job Taking Longer Than Expected',
            'message' => "Your job \"{$this->job->name}\" is taking longer than expected. It has been running for {$elapsedMinutes} minutes.",
            'job_id' => $this->job->id,
            'job_name' => $this->job->name,
            'elapsed_minutes' => $elapsedMinutes,
            'completed_tasks' => $this->job->completed_tasks,
            'total_tasks' => $this->job->total_tasks,
            'progress_percent' => $this->job->total_tasks > 0
                ? round(($this->job->completed_tasks / $this->job->total_tasks) * 100)
                : 0,
            'icon' => 'clock',
            'color' => 'yellow',
        ];
    }
}
