# Distributed Task Scheduler - Implementation Guide

## Overview

This is a complete implementation of a Distributed Task Scheduler system built with Laravel 11. The system allows users to submit large jobs that are automatically decomposed into smaller parallel tasks, which are then executed by multiple independent worker nodes.

## Architecture

### Components

1. **Models** - Data layer with relationships and business logic
   - `Worker` - Represents worker nodes
   - `SchedulerJob` - Represents jobs submitted by users
   - `Task` - Individual units of work
   - `TaskLog` - Audit trail for task execution

2. **Services** - Business logic layer
   - `TaskPartitionerService` - Splits jobs into tasks
   - `TaskClaimService` - Atomic task claiming with SELECT FOR UPDATE
   - `JobStatusService` - Recalculates job status based on tasks
   - `WorkerHealthService` - Detects dead workers and timed-out tasks

3. **Controllers** - API endpoints
   - `JobController` - Job CRUD operations
   - `TaskController` - Task claiming and status updates
   - `WorkerController` - Worker registration and heartbeat
   - `MetricsController` - Dashboard statistics

4. **Commands** - Background processes
   - `WorkerRun` - Worker process loop
   - `DetectDeadWorkers` - Scheduled command (every minute)
   - `DetectTimedOutTasks` - Scheduled command (every 2 minutes)

## Setup Instructions

### 1. Environment Configuration

Update your `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_scheduler
DB_USERNAME=root
DB_PASSWORD=your_password

REDIS_HOST=127.0.0.1
REDIS_PORT=6379
QUEUE_CONNECTION=redis

# Scheduler Configuration
WORKER_DEAD_THRESHOLD=45
WORKER_HEARTBEAT_INTERVAL=15
TASK_DEFAULT_TIMEOUT=300
TASK_MAX_RETRIES=3
```

### 2. Database Setup

```bash
# Run migrations
php artisan migrate

# Verify tables were created
php artisan db:show
```

### 3. Start the Laravel Server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000/api`

### 4. Start the Task Scheduler

In a separate terminal:

```bash
php artisan schedule:work
```

This will run the scheduled commands:
- Dead worker detection (every minute)
- Timed-out task detection (every 2 minutes)

### 5. Start Worker Nodes

Open multiple terminals and start workers:

```bash
# Terminal 1
php artisan worker:run --key=worker-001

# Terminal 2
php artisan worker:run --key=worker-002

# Terminal 3
php artisan worker:run --key=worker-003
```

## API Endpoints

### Job Management

```bash
# Create a job
POST /api/jobs
{
  "name": "Batch Process",
  "type": "csv_aggregate",
  "task_count": 50,
  "priority": 8,
  "description": "Process CSV data"
}

# List all jobs
GET /api/jobs?status=running&per_page=20

# Get job details
GET /api/jobs/{id}

# Get job tasks
GET /api/jobs/{id}/tasks

# Cancel a job
DELETE /api/jobs/{id}
```

### Task Management (Worker-Facing)

```bash
# Claim next task
GET /api/tasks/next
Header: X-Worker-Key: worker-001

# Start task execution
POST /api/tasks/{id}/start
{
  "worker_key": "worker-001"
}

# Complete task
POST /api/tasks/{id}/complete
{
  "worker_key": "worker-001",
  "result": {"processed": 100},
  "duration_ms": 1200
}

# Fail task
POST /api/tasks/{id}/fail
{
  "worker_key": "worker-001",
  "reason": "Connection timeout"
}
```

### Worker Management

```bash
# Register worker
POST /api/workers/register
{
  "worker_key": "worker-001",
  "hostname": "server1",
  "ip_address": "127.0.0.1"
}

# Send heartbeat
POST /api/workers/{key}/heartbeat

# List all workers
GET /api/workers

# Get worker details
GET /api/workers/{key}
```

### Metrics

```bash
# Get system metrics
GET /api/metrics

# Get time-series history
GET /api/metrics/history
```

## Testing the System

### 1. Submit a Test Job

```bash
curl -X POST http://localhost:8000/api/jobs \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Job",
    "type": "csv_aggregate",
    "task_count": 30,
    "priority": 5
  }'
```

### 2. Monitor Progress

```bash
# Watch job status
curl http://localhost:8000/api/jobs/1

# Watch workers
curl http://localhost:8000/api/workers

# Watch metrics
curl http://localhost:8000/api/metrics
```

### 3. Test Fault Tolerance

**Test Dead Worker Detection:**
1. Start 3 workers
2. Submit a job with 50 tasks
3. Kill one worker (Ctrl+C) while it's processing a task
4. Wait 60 seconds
5. Observe the task being reassigned to another worker

**Test Task Timeout:**
1. Modify a task to take longer than the timeout (300 seconds)
2. Observe it being reassigned after timeout

**Test Retry Logic:**
1. Tasks have a 5% chance of random failure (simulated)
2. Failed tasks are automatically retried with exponential backoff
3. After 3 retries, tasks are marked as permanently failed

## Key Features

### 1. Atomic Task Claiming

The system uses `SELECT FOR UPDATE` with database transactions to ensure no two workers can claim the same task:

```php
$task = Task::where('status', 'pending')
    ->lockForUpdate()
    ->first();
```

### 2. Exponential Backoff

Failed tasks are retried with increasing delays:
- Retry 1: 10 seconds
- Retry 2: 20 seconds
- Retry 3: 40 seconds

### 3. Dead Worker Detection

Workers must send heartbeats every 15 seconds. If a worker misses 3 heartbeats (45 seconds), it's marked as dead and its tasks are reassigned.

### 4. Task Timeout Detection

Tasks running longer than their timeout (default 300 seconds) are automatically reassigned.

### 5. Job Status Propagation

Job status is automatically updated based on task completion:
- All tasks done → Job completed
- Any task permanently failed → Job failed
- Tasks in progress → Job running

## Configuration

All configuration is in `config/scheduler.php`:

```php
'worker_dead_threshold' => 45,      // Seconds before worker is dead
'worker_heartbeat_interval' => 15,  // Heartbeat interval
'task_default_timeout' => 300,      // Task timeout in seconds
'task_max_retries' => 3,            // Maximum retry attempts
```

## Monitoring

### Worker Status

Workers can be in three states:
- `idle` - Ready to accept tasks
- `busy` - Currently executing a task
- `dead` - No heartbeat received

### Task Status

Tasks follow this state machine:
- `pending` → `assigned` → `running` → `done`
- `running` → `failed` (retriable) → `pending` (retry)
- `failed` (max retries) → `failed` (permanent)

### Job Status

Jobs can be:
- `pending` - No tasks started yet
- `running` - Some tasks in progress
- `completed` - All tasks done
- `failed` - Some tasks permanently failed
- `cancelled` - Job was cancelled

## Troubleshooting

### Workers not claiming tasks

1. Check worker is registered: `GET /api/workers`
2. Check tasks are available: `GET /api/jobs/{id}/tasks`
3. Check worker heartbeat is recent
4. Check Laravel logs: `tail -f storage/logs/laravel.log`

### Tasks stuck in "running" state

1. Check if worker is dead: `GET /api/workers`
2. Wait for timeout detection (runs every 2 minutes)
3. Manually run: `php artisan tasks:detect-timeout`

### Dead workers not detected

1. Check scheduler is running: `php artisan schedule:work`
2. Manually run: `php artisan workers:detect-dead`
3. Check worker heartbeat timestamps

## Performance

With 5 workers, the system can process:
- ~50 tasks/minute
- ~3000 tasks/hour
- Linear scaling with more workers

## Next Steps

1. **Add Vue 3 Dashboard** - Real-time UI for monitoring
2. **Add WebSockets** - Live updates using Laravel Echo
3. **Add Authentication** - Secure API endpoints
4. **Add Docker** - Multi-container deployment
5. **Add Tests** - PHPUnit tests for critical paths

## License

This is an academic project for distributed systems learning.
