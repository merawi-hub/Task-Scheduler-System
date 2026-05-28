<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    use Queueable;

    public function __construct(
        private User $user
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'welcome',
            'title' => 'Welcome to Task Scheduler!',
            'message' => "Welcome {$this->user->name}! You can now submit jobs and monitor their progress in real-time.",
            'icon' => 'check-circle',
            'color' => 'green',
        ];
    }
}
