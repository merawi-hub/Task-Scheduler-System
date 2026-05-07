<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'job_id',
        'task_index',
        'payload',
        'status',
        'worker_id',
        'retry_count',
        'max_retries',
        'failure_reason',
        'assigned_at',
        'started_at',
        'completed_at',
        'available_after',
        'timeout_seconds',
    ];

    protected $casts = [
        'payload' => 'array',
        'task_index' => 'integer',
        'retry_count' => 'integer',
        'max_retries' => 'integer',
        'timeout_seconds' => 'integer',
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'available_after' => 'datetime',
    ];

    /**
     * Get the job this task belongs to
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(SchedulerJob::class, 'job_id');
    }

    /**
     * Get the worker assigned to this task
     */
    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    /**
     * Get logs for this task
     */
    public function logs(): HasMany
    {
        return $this->hasMany(TaskLog::class);
    }

    /**
     * Check if task can be retried
     */
    public function canRetry(): bool
    {
        return $this->retry_count < $this->max_retries;
    }

    /**
     * Check if task is in terminal state
     */
    public function isTerminal(): bool
    {
        return in_array($this->status, ['done', 'cancelled']) || 
               ($this->status === 'failed' && !$this->canRetry());
    }

    /**
     * Check if task has timed out
     */
    public function hasTimedOut(): bool
    {
        if ($this->status !== 'running' || !$this->started_at) {
            return false;
        }

        return $this->started_at->diffInSeconds(now()) > $this->timeout_seconds;
    }

    /**
     * Calculate exponential backoff delay
     */
    public function calculateBackoffDelay(): int
    {
        return (int) (pow(2, $this->retry_count) * 5);
    }

    /**
     * Get duration in milliseconds
     */
    public function getDurationAttribute(): ?int
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->started_at->diffInMilliseconds($this->completed_at);
    }
}
