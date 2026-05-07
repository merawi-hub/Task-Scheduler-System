<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'task_id',
        'worker_id',
        'level',
        'message',
        'context',
        'logged_at',
    ];

    protected $casts = [
        'context' => 'array',
        'logged_at' => 'datetime',
    ];

    /**
     * Get the task this log belongs to
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the worker that created this log
     */
    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    /**
     * Create an info log
     */
    public static function info(int $taskId, string $message, ?int $workerId = null, ?array $context = null): self
    {
        return self::create([
            'task_id' => $taskId,
            'worker_id' => $workerId,
            'level' => 'info',
            'message' => $message,
            'context' => $context,
            'logged_at' => now(),
        ]);
    }

    /**
     * Create a warning log
     */
    public static function warning(int $taskId, string $message, ?int $workerId = null, ?array $context = null): self
    {
        return self::create([
            'task_id' => $taskId,
            'worker_id' => $workerId,
            'level' => 'warning',
            'message' => $message,
            'context' => $context,
            'logged_at' => now(),
        ]);
    }

    /**
     * Create an error log
     */
    public static function error(int $taskId, string $message, ?int $workerId = null, ?array $context = null): self
    {
        return self::create([
            'task_id' => $taskId,
            'worker_id' => $workerId,
            'level' => 'error',
            'message' => $message,
            'context' => $context,
            'logged_at' => now(),
        ]);
    }
}
