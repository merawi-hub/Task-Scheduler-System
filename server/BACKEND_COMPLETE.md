# Distributed Task Scheduler - Complete Backend Implementation

## Overview

This backend implements a **distributed task scheduling system** based on enterprise-grade distributed computing principles. It's not just a queue system - it's a complete distributed execution platform that handles:

- **Job Partitioning**: Breaking large jobs into manageable tasks
- **Atomic Task Claiming**: Race-condition-free task distribution using database transactions
- **Fault Tolerance**: Automatic detection and recovery from worker failures
- **Retry Logic**: Exponential backoff for failed tasks
- **Health Monitoring**: Real-time system health and performance metrics
- **Scalability**: Horizontal scaling through pull-based architecture

## Architecture

### Core Components

1. **Coordinator (Laravel Backend)**
   - Manages job submission and partitioning
   - Orchestrates task distribution
   - Monitors system health
   - Provides admin and user APIs

2. **Task Queue**
   - Database-backed task queue
   - Atomic task claiming with `lockForUpdate()`
   - Priority-based task selection

3. **Worker Nodes**
   - Pull tasks from coordinator
   - Execute tasks independently
   - Send heartbeats
   - Report results

4. **Health Monitor**
   - Detects dead workers
   - Handles task timeouts
   - Reassigns failed tasks
   - Runs every minute via scheduler

## Database Schema

### users
- User authentication and authorization
- Admin flag for system administrators

### scheduler_jobs
- Job metadata and configuration
- Progress tracking (total_tasks, completed_tasks, failed_tasks)
- Status management (pending, running, completed, failed, cancelled)
- User ownership (user_id)

### tasks
- Individual task units
- Worker assignment
- Retry logic (retry_count, max_retries, available_after)
- Timeout handling (timeout_seconds)
- Status tracking (pending, assigned, running, done, failed, cancelled)

### workers
- Worker registration and identification
- Heartbeat monitoring (last_heartbeat_at)
- Performance metrics (tasks_completed, tasks_failed)
- Status tracking (idle, busy, dead)

### task_logs
- Comprehensive logging system
- Severity levels (info, warning, error)
- Context data for debugging

## API Endpoints

### Authentication (Public)
```
POST /api/auth/register - Register new user
POST /api/auth/login - Login
POST /api/auth/logout - Logout (authenticated)
GET /api/auth/me - Get current user (authenticated)
```

### User Job Management (Authenticated)
```
GET /api/jobs - List user's jobs
POST /api/jobs - Submit new job
GET /api/jobs/{id} - View job details
GET /api/jobs/{id}/tasks - View job tasks
DELETE /api/jobs/{id} - Cancel job
GET /api/jobs/{id}/download - Download results (image processing)
```

### Worker Operations (Token Auth)
```
POST /api/workers/register - Register new worker
GET /api/tasks/next - Claim next task (atomic)
POST /api/tasks/{id}/start - Mark task started
POST /api/tasks/{id}/complete - Mark task completed
POST /api/tasks/{id}/fail - Mark task failed
POST /api/tasks/{id}/update-images - Update processed images
POST /api/workers/{key}/heartbeat - Send heartbeat
```

### Admin Operations (Admin Only)
```
# Job Management
GET /api/admin/jobs - All jobs from all users
GET /api/admin/jobs/statistics - Job statistics
GET /api/admin/jobs/{id} - View any job
POST /api/admin/jobs/{id}/cancel - Force cancel job
POST /api/admin/jobs/{id}/retry - Retry failed tasks
DELETE /api/admin/jobs/{id} - Delete job permanently

# Worker Management
GET /api/admin/workers - All workers
GET /api/admin/workers/statistics - Worker statistics
GET /api/admin/workers/{key} - View worker details
POST /api/admin/workers/{key}/mark-dead - Mark worker as dead
DELETE /api/admin/workers/{key} - Delete worker

# User Management
GET /api/admin/users - All users
GET /api/admin/users/statistics - User statistics
GET /api/admin/users/{id} - View user details
PUT /api/admin/users/{id} - Update user
DELETE /api/admin/users/{id} - Delete user

# System Metrics
GET /api/admin/metrics - Real-time system metrics
GET /api/admin/metrics/history - Historical metrics
GET /api/admin/metrics/health - System health status
GET /api/admin/metrics/activity - Activity feed
GET /api/admin/logs - Task logs (alias for activity)
```

## Core Services

### 1. TaskClaimService
**Purpose**: Atomic task claiming to prevent race conditions

**Key Methods**:
- `claimNext($workerKey)` - Atomically claim next available task using `lockForUpdate()`
- `markStarted($task, $worker)` - Mark task as running
- `markCompleted($task, $worker, $result)` - Complete task and update statistics
- `markFailed($task, $worker, $reason)` - Handle task failure with retry logic

**Why It's Important**: This is the heart of the distributed system. The `lockForUpdate()` transaction ensures that only ONE worker can claim a task, preventing duplicate processing.

### 2. TaskPartitionerService
**Purpose**: Split jobs into manageable tasks

**Key Methods**:
- `partition($job, $taskCount, $dataset)` - Partition job into tasks
- `partitionImageProcessingJob($job, $taskCount)` - Special handling for image processing

**Strategies**:
- Dataset chunking for data processing jobs
- Image batching for image processing jobs
- Simulated payloads for demo jobs

### 3. WorkerHealthService
**Purpose**: Fault tolerance and reliability

**Key Methods**:
- `detectDeadWorkers()` - Find workers that stopped sending heartbeats
- `handleDeadWorker($worker)` - Reassign tasks from dead workers
- `detectTimedOutTasks()` - Find tasks that exceeded timeout
- `handleTimedOutTask($task)` - Retry or fail timed-out tasks

**Thresholds**:
- Worker dead threshold: 45 seconds without heartbeat
- Task timeout: Configurable per task (default 300 seconds)

### 4. JobStatusService
**Purpose**: Job lifecycle management

**Key Methods**:
- `recalculate($job)` - Update job status based on task completion
- `cancel($job)` - Cancel job and all pending tasks
- `determineJobStatus()` - Calculate appropriate job status

**Status Flow**:
```
pending → running → completed/failed/cancelled
```

### 5. MetricsService
**Purpose**: System observability and monitoring

**Key Methods**:
- `getRealTimeMetrics()` - Current system state
- `getHistoricalMetrics($period)` - Time-series data for charts
- `getActivityFeed($limit)` - Recent system events
- `getSystemHealth()` - Health status with issues and warnings

**Metrics Provided**:
- Job metrics (total, by status, success rate)
- Task metrics (total, by status, queue size, success rate)
- Worker metrics (total, by status, utilization rate)
- Performance metrics (throughput, avg duration, retry rate)

## Key Features

### 1. Atomic Task Claiming
```php
$task = Task::where('status', 'pending')
    ->lockForUpdate()  // ← Critical for distributed systems
    ->first();
```

This prevents the "double processing" problem where two workers claim the same task.

### 2. Exponential Backoff
```php
$backoffSeconds = pow(2, $retryCount) * 5;
// Retry 1: 10 seconds
// Retry 2: 20 seconds
// Retry 3: 40 seconds
```

Failed tasks are retried with increasing delays to avoid overwhelming the system.

### 3. Heartbeat Monitoring
Workers send heartbeats every 30 seconds. If a worker misses heartbeats for 45 seconds, it's considered dead and its tasks are reassigned.

### 4. Task Timeout Detection
Tasks have configurable timeouts. If a task runs longer than its timeout, it's automatically retried or failed.

### 5. Real-Time Metrics
Metrics are cached for 10 seconds to reduce database load while providing near-real-time data.

## Background Jobs

### System Health Monitor
**Command**: `php artisan system:monitor-health`
**Schedule**: Every minute
**Actions**:
- Detect dead workers
- Reassign tasks from dead workers
- Detect timed-out tasks
- Retry or fail timed-out tasks
- Report system health

**Setup**:
```bash
# Start Laravel scheduler
php artisan schedule:work

# Or use cron (production)
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## Configuration

### Environment Variables
```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task-scheduler
DB_USERNAME=root
DB_PASSWORD=your_password

# Queue
QUEUE_CONNECTION=database

# Session
SESSION_DRIVER=database

# Sanctum (API Authentication)
SANCTUM_STATEFUL_DOMAINS=localhost:5173,localhost:5174
```

### System Configuration
Create `config/scheduler.php`:
```php
return [
    'worker_dead_threshold' => env('WORKER_DEAD_THRESHOLD', 45), // seconds
    'task_default_timeout' => env('TASK_DEFAULT_TIMEOUT', 300), // seconds
    'max_task_retries' => env('MAX_TASK_RETRIES', 3),
    'heartbeat_interval' => env('HEARTBEAT_INTERVAL', 30), // seconds
];
```

## Real-World Use Cases

### 1. Image Processing Service
**Scenario**: Process 10,000 images (resize, compress, thumbnail)

**Flow**:
1. User uploads 10,000 images
2. System creates job with 100 tasks (100 images per task)
3. 10 workers process tasks simultaneously
4. Each worker processes ~10 tasks
5. Result: 20 minutes vs 5 hours on single machine

### 2. Video Conversion Platform
**Scenario**: Convert 1,000 videos to multiple formats

**Flow**:
1. User submits 1,000 videos
2. System creates conversion tasks
3. Workers process videos independently
4. Failed conversions retry automatically
5. Dead workers don't block progress

### 3. Data Analytics Pipeline
**Scenario**: Process 1 million database records

**Flow**:
1. User submits analytics job
2. System splits into 1,000 tasks (1,000 records each)
3. Workers process in parallel
4. Results aggregated automatically
5. Fault-tolerant execution

### 4. Report Generation
**Scenario**: Generate 500 PDF reports

**Flow**:
1. User requests bulk report generation
2. System distributes across workers
3. Parallel PDF generation
4. Automatic retry on failures
5. Collect and deliver results

## Testing

### Manual Testing

#### 1. Submit a Job
```bash
curl -X POST http://localhost:8000/api/jobs \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Job",
    "type": "data_processing",
    "task_count": 10,
    "priority": 5
  }'
```

#### 2. Register a Worker
```bash
curl -X POST http://localhost:8000/api/workers/register \
  -H "Content-Type: application/json" \
  -d '{
    "hostname": "worker-001",
    "ip_address": "192.168.1.100"
  }'
```

#### 3. Claim a Task
```bash
curl -X GET http://localhost:8000/api/tasks/next \
  -H "X-Worker-Key: YOUR_WORKER_KEY"
```

#### 4. Complete a Task
```bash
curl -X POST http://localhost:8000/api/tasks/1/complete \
  -H "Content-Type: application/json" \
  -d '{
    "worker_key": "YOUR_WORKER_KEY",
    "result": {"status": "success"},
    "duration_ms": 1500
  }'
```

### Load Testing

Use Apache Bench or similar tools:
```bash
# Test job submission
ab -n 100 -c 10 -T 'application/json' \
  -H "Authorization: Bearer TOKEN" \
  -p job_payload.json \
  http://localhost:8000/api/jobs

# Test task claiming
ab -n 1000 -c 50 \
  -H "X-Worker-Key: WORKER_KEY" \
  http://localhost:8000/api/tasks/next
```

## Deployment

### Development
```bash
# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Start server
php artisan serve

# Start scheduler (separate terminal)
php artisan schedule:work
```

### Production

#### Using Supervisor (Recommended)
```ini
[program:scheduler]
command=php /path/to/project/artisan schedule:work
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/scheduler.log
```

#### Using Cron
```cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## Monitoring

### Key Metrics to Watch

1. **Throughput**: Tasks completed per second
2. **Queue Size**: Number of pending tasks
3. **Worker Utilization**: Percentage of busy workers
4. **Success Rate**: Percentage of successful tasks
5. **Retry Rate**: Percentage of tasks that needed retries
6. **Average Task Duration**: Time to complete tasks

### Health Indicators

**Healthy System**:
- Active workers > 0
- Success rate > 95%
- Queue size manageable
- Low retry rate

**Degraded System**:
- Success rate 80-95%
- Some dead workers
- Increasing queue size

**Critical System**:
- Success rate < 80%
- No active workers
- Large queue backlog
- High failure rate

## Troubleshooting

### Problem: Tasks Not Being Processed
**Causes**:
- No active workers
- All workers dead
- Database connection issues

**Solutions**:
- Check worker status: `GET /api/admin/workers`
- Restart workers
- Check database connectivity

### Problem: High Failure Rate
**Causes**:
- Worker crashes
- Task timeouts
- Resource exhaustion

**Solutions**:
- Check task logs: `GET /api/admin/logs`
- Increase task timeout
- Add more workers
- Optimize task processing

### Problem: Dead Workers Not Detected
**Causes**:
- Scheduler not running
- Health monitor disabled

**Solutions**:
- Start scheduler: `php artisan schedule:work`
- Check cron configuration
- Manually run: `php artisan system:monitor-health`

## Performance Optimization

### Database Indexes
Ensure these indexes exist:
```sql
-- Tasks table
CREATE INDEX idx_tasks_status_available ON tasks(status, available_after);
CREATE INDEX idx_tasks_worker_status ON tasks(worker_id, status);
CREATE INDEX idx_tasks_job_status ON tasks(job_id, status);

-- Workers table
CREATE INDEX idx_workers_status_heartbeat ON workers(status, last_heartbeat_at);

-- Jobs table
CREATE INDEX idx_jobs_user_status ON scheduler_jobs(user_id, status);
```

### Caching
Metrics are cached for 10 seconds. Adjust in MetricsService:
```php
Cache::remember('system_metrics', 10, function () {
    // ...
});
```

### Connection Pooling
Use persistent database connections in production:
```env
DB_PERSISTENT=true
```

## Security

### API Authentication
- Users: Laravel Sanctum tokens
- Workers: Custom API tokens (X-Worker-Token header)
- Admins: Sanctum + admin middleware

### Authorization
- Users can only access their own jobs
- Admins can access all resources
- Workers can only claim and update tasks

### Input Validation
All endpoints validate input using Laravel's validation rules.

### Rate Limiting
Consider adding rate limiting for public endpoints:
```php
Route::middleware('throttle:60,1')->group(function () {
    // Rate-limited routes
});
```

## Future Enhancements

1. **Priority Queues**: High-priority jobs processed first
2. **Worker Pools**: Specialized workers for specific job types
3. **Task Dependencies**: DAG-based task execution
4. **Distributed Tracing**: End-to-end request tracking
5. **Auto-scaling**: Dynamic worker provisioning
6. **Webhooks**: Job completion notifications
7. **Multi-tenancy**: Isolated environments per tenant
8. **Streaming Results**: Real-time result streaming

## Conclusion

This backend implements a production-ready distributed task scheduling system with:

✅ **Atomic task claiming** - No race conditions
✅ **Fault tolerance** - Automatic recovery from failures
✅ **Horizontal scalability** - Add more workers as needed
✅ **Comprehensive monitoring** - Real-time metrics and health checks
✅ **Retry logic** - Exponential backoff for failed tasks
✅ **Admin controls** - Full system management capabilities

It demonstrates enterprise-grade distributed computing concepts and is ready for real-world use cases like image processing, video conversion, data analytics, and report generation.
