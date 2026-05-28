<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;

class Notification extends DatabaseNotification
{
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * Get the notification's priority level
     */
    public function getPriorityAttribute(): string
    {
        return $this->data['priority'] ?? 'normal';
    }

    /**
     * Get the notification's category
     */
    public function getCategoryAttribute(): string
    {
        return $this->data['category'] ?? 'general';
    }

    /**
     * Check if notification is unread
     */
    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): void
    {
        if ($this->isUnread()) {
            $this->forceFill(['read_at' => $this->freshTimestamp()])->save();
        }
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(): void
    {
        if (!$this->isUnread()) {
            $this->forceFill(['read_at' => null])->save();
        }
    }
}
