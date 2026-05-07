<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Worker extends Model
{
    protected $fillable = [
        'worker_key',
        'api_token',
        'hostname',
        'ip_address',
        'status',
        'current_task_id',
        'last_heartbeat_at',
        'tasks_completed',
        'tasks_failed',
        'registered_at',
    ];

    protected $casts = [
        'last_heartbeat_at' => 'datetime',
        'registered_at' => 'datetime',
        'tasks_completed' => 'integer',
        'tasks_failed' => 'integer',
    ];

    protected $hidden = [
        'api_token',
    ];

    /**
     * Generate a unique API token for the worker
     */
    public static function generateApiToken(): string
    {
        return Str::random(64);
    }

    /**
     * Get all tasks assigned to this worker
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get the current task being executed
     */
    public function currentTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'current_task_id');
    }

    /**
     * Get task logs for this worker
     */
    public function taskLogs(): HasMany
    {
        return $this->hasMany(TaskLog::class);
    }

    /**
     * Check if worker is considered dead
     */
    public function isDead(): bool
    {
        if (!$this->last_heartbeat_at) {
            return true;
        }

        $threshold = config('scheduler.worker_dead_threshold', 45);
        return $this->last_heartbeat_at->diffInSeconds(now()) > $threshold;
    }

    /**
     * Mark worker as dead
     */
    public function markAsDead(): void
    {
        $this->update([
            'status' => 'dead',
            'current_task_id' => null,
        ]);
    }

    /**
     * Update heartbeat timestamp
     */
    public function heartbeat(): void
    {
        $this->update([
            'last_heartbeat_at' => now(),
        ]);
    }
}
