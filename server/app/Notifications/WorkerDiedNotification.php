<?php

namespace App\Notifications;

use App\Models\Worker;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WorkerDiedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Worker $worker
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'worker_died',
            'title' => 'Worker Died',
            'message' => "Worker {$this->worker->worker_key} on {$this->worker->hostname} has stopped responding.",
            'worker_id' => $this->worker->id,
            'worker_key' => $this->worker->worker_key,
            'hostname' => $this->worker->hostname,
            'tasks_completed' => $this->worker->tasks_completed,
            'tasks_failed' => $this->worker->tasks_failed,
            'last_heartbeat' => $this->worker->last_heartbeat_at?->toIso8601String(),
            'icon' => 'alert-triangle',
            'color' => 'red',
            'severity' => 'high',
        ];
    }
}
