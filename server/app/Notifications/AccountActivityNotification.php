<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AccountActivityNotification extends Notification
{
    use Queueable;

    public function __construct(
        private string $activity,
        private array $details = []
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'account_activity',
            'title' => 'Account Activity',
            'message' => $this->activity,
            'details' => $this->details,
            'icon' => 'user',
            'color' => 'blue',
        ];
    }
}
