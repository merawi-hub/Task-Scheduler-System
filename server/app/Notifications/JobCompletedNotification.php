<?php

namespace App\Notifications;

use App\Models\SchedulerJob;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JobCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public SchedulerJob $job
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => 'Job Completed Successfully',
            'message' => "Your job '{$this->job->name}' has completed successfully.",
            'job_id' => $this->job->id,
            'job_name' => $this->job->name,
            'completed_tasks' => $this->job->completed_tasks,
            'total_tasks' => $this->job->total_tasks,
            'duration' => $this->calculateDuration(),
            'category' => 'job',
            'priority' => 'normal',
            'action_url' => "/jobs/{$this->job->id}",
            'action_text' => 'View Job',
            'icon' => 'check-circle',
            'color' => 'green',
        ];
    }

    private function calculateDuration(): ?string
    {
        if (!$this->job->started_at || !$this->job->completed_at) {
            return null;
        }

        $seconds = $this->job->started_at->diffInSeconds($this->job->completed_at);

        if ($seconds < 60) return "{$seconds}s";
        if ($seconds < 3600) return floor($seconds / 60) . 'm';
        return floor($seconds / 3600) . 'h ' . floor(($seconds % 3600) / 60) . 'm';
    }
}
