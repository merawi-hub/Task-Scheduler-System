# Quick Start Guide - Distributed Task Scheduler

## Prerequisites

- PHP 8.2+
- MySQL 8.0+
- Redis 7.x
- Composer

## Installation Steps

### 1. Install Dependencies

```bash
cd server
composer install
```

### 2. Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_scheduler
DB_USERNAME=root
DB_PASSWORD=your_password

REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### 3. Create Database

```bash
mysql -u root -p
```

```sql
CREATE DATABASE task_scheduler;
EXIT;
```

### 4. Run Migrations

```bash
php artisan migrate
```

You should see:
```
✓ 2026_05_06_112417_create_workers_table
✓ 2026_05_06_113019_create_tasks_table
✓ 2026_05_06_113030_create_task_logs_table
✓ 2026_05_06_113241_create_scheduler_jobs_table
```

### 5. Start Services

Open 5 separate terminal windows:

**Terminal 1 - Laravel Server:**
```bash
cd server
php artisan serve
```

**Terminal 2 - Task Scheduler:**
```bash
cd server
php artisan schedule:work
```

**Terminal 3 - Worker 1:**
```bash
cd server
php artisan worker:run --key=worker-001
```

**Terminal 4 - Worker 2:**
```bash
cd server
php artisan worker:run --key=worker-002
```

**Terminal 5 - Worker 3:**
```bash
cd server
php artisan worker:run --key=worker-003
```

## Test the System

### 1. Submit a Job

```bash
curl -X POST http://localhost:8000/api/jobs \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Batch Process",
    "type": "csv_aggregate",
    "task_count": 20,
    "priority": 5,
    "description": "Testing the distributed task scheduler"
  }'
```

Expected response:
```json
{
  "message": "Job created successfully",
  "job": {
    "id": 1,
    "name": "Test Batch Process",
    "status": "pending",
    "total_tasks": 20,
    ...
  }
}
```

### 2. Watch Workers Process Tasks

In the worker terminals, you should see:
```
📋 Claimed Task #1 (Job #1, Index 0)
   Type: csv_aggregate
   Processing items 0 to 49
   ⏳ Simulating 2s of work...
✅ Task #1 completed in 2150ms
```

### 3. Check Job Progress

```bash
curl http://localhost:8000/api/jobs/1
```

Response:
```json
{
  "job": {
    "id": 1,
    "status": "running",
    "total_tasks": 20,
    "completed_tasks": 15,
    "failed_tasks": 0,
    ...
  },
  "progress": 75.0
}
```

### 4. View All Workers

```bash
curl http://localhost:8000/api/workers
```

Response:
```json
{
  "workers": [
    {
      "id": 1,
      "worker_key": "worker-001",
      "status": "busy",
      "tasks_completed": 5,
      "tasks_failed": 0,
      ...
    }
  ],
  "summary": {
    "total": 3,
    "idle": 1,
    "busy": 2,
    "dead": 0
  }
}
```

### 5. View System Metrics

```bash
curl http://localhost:8000/api/metrics
```

Response:
```json
{
  "jobs": {
    "total": 1,
    "running": 1,
    "completed": 0
  },
  "tasks": {
    "total": 20,
    "done": 15,
    "running": 2,
    "pending": 3
  },
  "workers": {
    "total": 3,
    "active": 3,
    "utilization": 66.67
  },
  "performance": {
    "tasks_per_second": 0.42,
    "avg_task_duration_seconds": 2.5
  }
}
```

## Test Fault Tolerance

### Test 1: Dead Worker Detection

1. Start 3 workers and submit a job with 30 tasks
2. While tasks are running, kill one worker (Ctrl+C)
3. Wait 60 seconds
4. The dead worker's tasks will be reassigned to healthy workers

Watch the scheduler terminal:
```
🔍 Scanning for dead workers...
⚠️  Found 1 dead worker(s)
📋 Reassigned 2 task(s)
   - worker-002: 2 tasks reassigned
```

### Test 2: Task Retry on Failure

Tasks have a 5% random failure rate. When a task fails:

1. It's automatically retried with exponential backoff
2. Retry 1: 10 seconds delay
3. Retry 2: 20 seconds delay
4. Retry 3: 40 seconds delay
5. After 3 retries, marked as permanently failed

Watch worker logs:
```
❌ Task #5 failed: Simulated random failure
```

Check task logs:
```bash
curl http://localhost:8000/api/jobs/1/tasks
```

You'll see tasks with `retry_count > 0`.

## Common Commands

```bash
# List all jobs
curl http://localhost:8000/api/jobs

# Get specific job
curl http://localhost:8000/api/jobs/1

# Cancel a job
curl -X DELETE http://localhost:8000/api/jobs/1

# List workers
curl http://localhost:8000/api/workers

# Get worker details
curl http://localhost:8000/api/workers/worker-001

# Manually detect dead workers
php artisan workers:detect-dead

# Manually detect timed-out tasks
php artisan tasks:detect-timeout
```

## Troubleshooting

### "Connection refused" errors

Make sure MySQL and Redis are running:
```bash
# Check MySQL
mysql -u root -p -e "SELECT 1"

# Check Redis
redis-cli ping
```

### Workers not claiming tasks

1. Check workers are registered:
```bash
curl http://localhost:8000/api/workers
```

2. Check Laravel logs:
```bash
tail -f storage/logs/laravel.log
```

### Tasks stuck in "running"

Run the timeout detection manually:
```bash
php artisan tasks:detect-timeout
```

## Next Steps

1. ✅ System is working!
2. Submit more complex jobs
3. Test with more workers (5-10)
4. Monitor performance metrics
5. Build the Vue 3 dashboard for visual monitoring

## Architecture Overview

```
┌─────────────────┐
│  Vue Dashboard  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐      ┌──────────────┐
│ Laravel API     │◄────►│    MySQL     │
│  (Coordinator)  │      │   Database   │
└────────┬────────┘      └──────────────┘
         │
         ▼
    ┌────────┐
    │ Redis  │
    └───┬────┘
        │
    ┌───┴────┬────────┬────────┐
    ▼        ▼        ▼        ▼
┌────────┐ ┌────────┐ ┌────────┐
│Worker 1│ │Worker 2│ │Worker N│
└────────┘ └────────┘ └────────┘
```

## Key Features Implemented

✅ Atomic task claiming (no race conditions)  
✅ Pull-based task distribution  
✅ Worker heartbeat system  
✅ Dead worker detection & task reassignment  
✅ Task timeout detection  
✅ Automatic retry with exponential backoff  
✅ Job status propagation  
✅ Real-time metrics API  
✅ Complete REST API  
✅ Scheduled background jobs  

## Performance

With 3 workers:
- ~30-40 tasks/minute
- ~2000 tasks/hour
- Linear scaling with more workers

Enjoy your distributed task scheduler! 🚀
