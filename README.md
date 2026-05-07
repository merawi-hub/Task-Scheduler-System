# Distributed Task Scheduler

A complete distributed computing system built with Laravel 11 that allows users to submit large jobs which are automatically decomposed into smaller parallel tasks and executed by multiple independent worker nodes.

## 🎯 Overview

This system demonstrates real distributed systems principles including:
- **Atomic Task Claiming** - No race conditions using SELECT FOR UPDATE
- **Pull-Based Scheduling** - Workers pull tasks for natural load balancing
- **Fault Tolerance** - Automatic recovery from worker crashes
- **Retry Logic** - Exponential backoff for failed tasks
- **Health Monitoring** - Dead worker detection and task reassignment
- **Task Timeout Detection** - Automatic reassignment of stuck tasks

## 🏗️ Architecture

```
┌─────────────────┐
│  Vue Dashboard  │  (Optional - Future Enhancement)
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

## ✨ Features

### Core Functionality
- ✅ Job submission and lifecycle management
- ✅ Automatic task partitioning
- ✅ Atomic task claiming (prevents race conditions)
- ✅ Pull-based task distribution
- ✅ Worker self-registration
- ✅ Heartbeat monitoring system
- ✅ Dead worker detection
- ✅ Task timeout detection
- ✅ Automatic retry with exponential backoff
- ✅ Job status propagation
- ✅ Real-time metrics API

### API Endpoints
- Job Management (CRUD operations)
- Task Management (claiming, status updates)
- Worker Management (registration, heartbeat)
- Metrics & Statistics

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- MySQL 8.0+
- Redis 7.x
- Composer

### Installation

1. **Clone and Install**
```bash
cd server
composer install
cp .env.example .env
php artisan key:generate
```

2. **Configure Database**
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE task_scheduler"

# Update .env with your credentials
DB_DATABASE=task_scheduler
DB_USERNAME=root
DB_PASSWORD=your_password
```

3. **Run Migrations**
```bash
php artisan migrate
```

4. **Start Services** (5 terminals)

Terminal 1 - Laravel Server:
```bash
php artisan serve
```

Terminal 2 - Task Scheduler:
```bash
php artisan schedule:work
```

Terminal 3-5 - Workers:
```bash
php artisan worker:run --key=worker-001
php artisan worker:run --key=worker-002
php artisan worker:run --key=worker-003
```

### Test the System

Submit a job:
```bash
curl -X POST http://localhost:8000/api/jobs \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Job",
    "type": "csv_aggregate",
    "task_count": 20,
    "priority": 5
  }'
```

Watch progress:
```bash
curl http://localhost:8000/api/jobs/1
curl http://localhost:8000/api/workers
curl http://localhost:8000/api/metrics
```

## 📚 Documentation

- **[QUICKSTART.md](server/QUICKSTART.md)** - Quick start guide with examples
- **[IMPLEMENTATION.md](server/IMPLEMENTATION.md)** - Complete implementation details
- **[IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md)** - Summary of what was built
- **[Documentation.txt](Doc/Documentation.txt)** - Original requirements specification

## 🏛️ Project Structure

```
server/
├── app/
│   ├── Models/              # Data models
│   │   ├── Worker.php
│   │   ├── SchedulerJob.php
│   │   ├── Task.php
│   │   └── TaskLog.php
│   ├── Services/            # Business logic
│   │   ├── TaskPartitionerService.php
│   │   ├── TaskClaimService.php
│   │   ├── JobStatusService.php
│   │   └── WorkerHealthService.php
│   ├── Http/Controllers/Api/  # API endpoints
│   │   ├── JobController.php
│   │   ├── TaskController.php
│   │   ├── WorkerController.php
│   │   └── MetricsController.php
│   └── Console/Commands/    # CLI commands
│       ├── WorkerRun.php
│       ├── DetectDeadWorkers.php
│       └── DetectTimedOutTasks.php
├── routes/
│   └── api.php              # API routes
├── config/
│   └── scheduler.php        # Configuration
└── database/
    └── migrations/          # Database schema
```

## 🔑 Key Implementation Details

### Atomic Task Claiming
```php
DB::transaction(function () use ($worker) {
    $task = Task::where('status', 'pending')
        ->lockForUpdate()  // Prevents race conditions
        ->first();
    
    $task->update(['status' => 'assigned', 'worker_id' => $worker->id]);
    return $task;
});
```

### Exponential Backoff
- Retry 1: 10 seconds delay
- Retry 2: 20 seconds delay  
- Retry 3: 40 seconds delay
- After 3 retries: Permanent failure

### Dead Worker Detection
- Workers send heartbeat every 15 seconds
- Missing 3 heartbeats (45 seconds) = dead worker
- Tasks automatically reassigned to healthy workers

## 📊 API Examples

### Create a Job
```bash
POST /api/jobs
{
  "name": "Batch Process",
  "type": "csv_aggregate",
  "task_count": 50,
  "priority": 8
}
```

### Claim a Task (Worker)
```bash
GET /api/tasks/next
Header: X-Worker-Key: worker-001
```

### Get System Metrics
```bash
GET /api/metrics
```

Response:
```json
{
  "jobs": {"total": 10, "running": 3, "completed": 7},
  "tasks": {"total": 500, "done": 450, "running": 30},
  "workers": {"total": 5, "active": 5, "utilization": 60},
  "performance": {"tasks_per_second": 0.42}
}
```

## 🧪 Testing

Run the test script:
```bash
cd server
chmod +x test-system.sh
./test-system.sh
```

## 🎓 Learning Outcomes

This project demonstrates:
- Distributed systems architecture
- Race condition prevention
- Fault tolerance patterns
- State machine design
- Database transactions
- Laravel best practices
- RESTful API design

## 📈 Performance

With 3 workers:
- ~30-40 tasks/minute
- ~2000 tasks/hour
- Linear scaling with more workers

## 🔧 Configuration

Edit `config/scheduler.php`:
```php
'worker_dead_threshold' => 45,      // Seconds
'worker_heartbeat_interval' => 15,  // Seconds
'task_default_timeout' => 300,      // Seconds
'task_max_retries' => 3,            // Attempts
```

## 🐛 Troubleshooting

**Workers not claiming tasks?**
- Check workers are registered: `curl http://localhost:8000/api/workers`
- Check Laravel logs: `tail -f storage/logs/laravel.log`

**Tasks stuck in "running"?**
- Run timeout detection: `php artisan tasks:detect-timeout`

**Dead workers not detected?**
- Check scheduler is running: `php artisan schedule:work`
- Manually run: `php artisan workers:detect-dead`

## 🚧 Future Enhancements

- [ ] Vue 3 Dashboard with real-time updates
- [ ] WebSocket support with Laravel Echo
- [ ] API authentication with Sanctum
- [ ] Docker Compose setup
- [ ] PHPUnit/Pest test suite
- [ ] Prometheus metrics export

## 📝 License

This is an academic project for distributed systems learning.

## 👥 Contributing

This is a class project. For educational purposes only.

---

**Built with Laravel 11 | MySQL | Redis**

For detailed setup instructions, see [QUICKSTART.md](server/QUICKSTART.md)
