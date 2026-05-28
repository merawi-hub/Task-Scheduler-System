<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemHealthNotification extends Notification
{
    use Queueable;

    public function __construct(
        private string $issue,
        private array $details = []
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'system_health',
            'title' => 'System Health Issue',
            'message' => $this->issue,
            'details' => $this->details,
            'icon' => 'activity',
            'color' => 'red',
            'severity' => 'high',
        ];
    }
}
