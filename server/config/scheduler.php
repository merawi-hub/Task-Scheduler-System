<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Worker Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for worker nodes and health monitoring
    |
    */

    'worker_dead_threshold' => env('WORKER_DEAD_THRESHOLD', 45),
    'worker_heartbeat_interval' => env('WORKER_HEARTBEAT_INTERVAL', 15),

    /*
    |--------------------------------------------------------------------------
    | Task Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for task execution and timeouts
    |
    */

    'task_default_timeout' => env('TASK_DEFAULT_TIMEOUT', 300),
    'task_max_retries' => env('TASK_MAX_RETRIES', 3),

    /*
    |--------------------------------------------------------------------------
    | Job Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for job management
    |
    */

    'job_max_tasks' => env('JOB_MAX_TASKS', 10000),
    'job_default_priority' => env('JOB_DEFAULT_PRIORITY', 5),
];
