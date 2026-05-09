<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Worker Dead Threshold
    |--------------------------------------------------------------------------
    |
    | The number of seconds without a heartbeat before a worker is considered
    | dead. Dead workers will have their tasks reassigned automatically.
    |
    */
    'worker_dead_threshold' => env('WORKER_DEAD_THRESHOLD', 45),

    /*
    |--------------------------------------------------------------------------
    | Task Default Timeout
    |--------------------------------------------------------------------------
    |
    | The default timeout in seconds for task execution. Tasks that exceed
    | this timeout will be automatically retried or failed.
    |
    */
    'task_default_timeout' => env('TASK_DEFAULT_TIMEOUT', 300),

    /*
    |--------------------------------------------------------------------------
    | Maximum Task Retries
    |--------------------------------------------------------------------------
    |
    | The maximum number of times a failed task will be retried before being
    | marked as permanently failed.
    |
    */
    'max_task_retries' => env('MAX_TASK_RETRIES', 3),

    /*
    |--------------------------------------------------------------------------
    | Heartbeat Interval
    |--------------------------------------------------------------------------
    |
    | The recommended interval in seconds for workers to send heartbeats.
    | Workers should send heartbeats more frequently than the dead threshold.
    |
    */
    'heartbeat_interval' => env('HEARTBEAT_INTERVAL', 30),

    /*
    |--------------------------------------------------------------------------
    | Metrics Cache Duration
    |--------------------------------------------------------------------------
    |
    | The number of seconds to cache system metrics. Lower values provide
    | more real-time data but increase database load.
    |
    */
    'metrics_cache_duration' => env('METRICS_CACHE_DURATION', 10),

    /*
    |--------------------------------------------------------------------------
    | Task Claim Timeout
    |--------------------------------------------------------------------------
    |
    | The maximum time in seconds to wait for a database lock when claiming
    | a task. This prevents workers from waiting indefinitely.
    |
    */
    'task_claim_timeout' => env('TASK_CLAIM_TIMEOUT', 5),

    /*
    |--------------------------------------------------------------------------
    | Job Types
    |--------------------------------------------------------------------------
    |
    | Supported job types and their configurations.
    |
    */
    'job_types' => [
        'image_processing' => [
            'default_timeout' => 600, // 10 minutes
            'max_retries' => 3,
            'operations' => ['resize', 'compress', 'thumbnail', 'watermark'],
        ],
        'video_conversion' => [
            'default_timeout' => 1800, // 30 minutes
            'max_retries' => 2,
        ],
        'data_processing' => [
            'default_timeout' => 300, // 5 minutes
            'max_retries' => 3,
        ],
        'report_generation' => [
            'default_timeout' => 600, // 10 minutes
            'max_retries' => 2,
        ],
        'email_batch' => [
            'default_timeout' => 180, // 3 minutes
            'max_retries' => 5,
        ],
        'result_processing' => [
            'default_timeout' => 300, // 5 minutes per task
            'max_retries' => 3,
            'operations' => ['calculate_grades', 'generate_report', 'validate_data'],
            'records_per_task' => 100, // 100 student records per task
        ],
        'ml_training' => [
            'default_timeout' => 3600, // 60 minutes
            'max_retries' => 1,
        ],
        'batch_processing' => [
            'default_timeout' => 300,
            'max_retries' => 3,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Priority Levels
    |--------------------------------------------------------------------------
    |
    | Job priority levels (1-10, where 10 is highest priority).
    |
    */
    'priority_levels' => [
        'low' => 3,
        'normal' => 5,
        'high' => 7,
        'urgent' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for file storage (images, videos, etc.).
    |
    */
    'storage' => [
        'disk' => env('SCHEDULER_STORAGE_DISK', 'public'),
        'base_path' => 'jobs',
        'max_file_size' => 10240, // KB (10MB)
        'allowed_image_types' => ['jpeg', 'png', 'jpg', 'gif', 'webp'],
        'allowed_video_types' => ['mp4', 'avi', 'mov', 'wmv'],
    ],
];
