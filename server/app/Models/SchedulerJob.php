<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchedulerJob extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'type',
        'input_files',
        'output_files',
        'operations',
        'storage_path',
        'status',
        'total_tasks',
        'completed_tasks',
        'failed_tasks',
        'priority',
        'submitted_by',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'input_files' => 'array',
        'output_files' => 'array',
        'operations' => 'array',
        'total_tasks' => 'integer',
        'completed_tasks' => 'integer',
        'failed_tasks' => 'integer',
        'priority' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the user who submitted this job
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all tasks for this job
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'job_id');
    }

    /**
     * Calculate progress percentage
     */
    public function getProgressAttribute(): float
    {
        if ($this->total_tasks === 0) {
            return 0;
        }

        return round(($this->completed_tasks / $this->total_tasks) * 100, 2);
    }

    /**
     * Check if job is in terminal state
     */
    public function isTerminal(): bool
    {
        return in_array($this->status, ['completed', 'failed', 'cancelled']);
    }

    /**
     * Check if job can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'running']);
    }

    /**
     * Get pending tasks count
     */
    public function getPendingTasksCountAttribute(): int
    {
        return $this->total_tasks - $this->completed_tasks - $this->failed_tasks;
    }
}
