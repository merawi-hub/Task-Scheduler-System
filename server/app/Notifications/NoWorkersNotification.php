<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NoWorkersNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'no_workers',
            'title' => 'No Workers Available',
            'message' => 'The system has no active workers. Jobs cannot be processed until workers are started.',
            'icon' => 'alert-circle',
            'color' => 'orange',
            'severity' => 'critical',
            'action' => 'start_workers',
            'action_label' => 'Start Workers',
        ];
    }
}
