================================================================================
  DISTRIBUTED TASK SCHEDULER - TASKFLOW
================================================================================

A distributed task scheduling system with three-actor architecture:
User, Admin, and Worker nodes for efficient task processing.

================================================================================
QUICK START
================================================================================

1. START BACKEND:
   - Double-click START_BACKEND.bat
   - Or run: cd server && php -S 127.0.0.1:8000 server.php
   - Backend will run on: http://127.0.0.1:8000

2. START FRONTEND:
   - Open new terminal
   - Run: cd client && npm run dev
   - Frontend will run on: http://localhost:5173

3. OPEN BROWSER:
   - Navigate to: http://localhost:5173
   - Login with credentials below

================================================================================
DEFAULT CREDENTIALS
================================================================================

Admin Account:
  Email: admin@taskscheduler.com
  Password: admin123

Test User Account:
  Email: user@taskscheduler.com
  Password: user123

================================================================================
PROJECT STRUCTURE
================================================================================

Distributed-Task-Scheduler-/
├── client/              # Vue.js frontend application
│   ├── src/
│   │   ├── views/       # Page components
│   │   ├── components/  # Reusable components
│   │   ├── stores/      # Pinia state management
│   │   ├── api/         # API client configuration
│   │   └── router/      # Vue Router configuration
│   └── .env             # Frontend environment variables
│
├── server/              # Laravel backend API
│   ├── app/             # Application logic
│   ├── routes/          # API routes
│   ├── database/        # Migrations and seeders
│   ├── config/          # Configuration files
│   ├── server.php       # PHP built-in server router
│   └── .env             # Backend environment variables
│
├── .git/                # Git repository
├── .gitignore           # Git ignore rules
├── PROJECT_PRESENTATION.txt  # Project presentation slides
├── START_BACKEND.bat    # Backend startup script
└── START_SYSTEM.bat     # Full system startup script

================================================================================
FEATURES
================================================================================

USER FEATURES:
- Submit computational jobs
- Monitor job progress in real-time
- View job history and statistics
- Download job results
- Manage profile and settings

ADMIN FEATURES:
- Manage all users and jobs
- Monitor system health and metrics
- Manage worker nodes
- View system logs and analytics
- Control job execution and priorities

WORKER FEATURES:
- Register as worker node
- Receive and execute tasks
- Report task progress
- Handle fault tolerance

================================================================================
TECHNOLOGY STACK
================================================================================

Frontend:
- Vue.js 3 (Composition API)
- Vite (Build tool)
- Pinia (State management)
- Vue Router (Routing)
- Axios (HTTP client)
- Tailwind CSS (Styling)

Backend:
- Laravel 11 (PHP Framework)
- MySQL (Database)
- Laravel Sanctum (Authentication)
- RESTful API architecture

================================================================================
API ENDPOINTS
================================================================================

Authentication:
- POST /api/auth/login
- POST /api/auth/register
- POST /api/auth/logout
- GET /api/auth/me

Jobs:
- GET /api/jobs (List user jobs)
- POST /api/jobs (Submit new job)
- GET /api/jobs/{id} (Job details)
- DELETE /api/jobs/{id} (Cancel job)

Admin:
- GET /api/admin/users
- GET /api/admin/jobs
- GET /api/admin/workers
- GET /api/admin/metrics

Workers:
- POST /api/workers/register
- POST /api/workers/{key}/heartbeat
- GET /api/tasks/next

And 60+ more endpoints...

================================================================================
DEVELOPMENT
================================================================================

Backend Setup:
1. cd server
2. composer install
3. cp .env.example .env
4. php artisan key:generate
5. php artisan migrate
6. php artisan db:seed --class=AdminUserSeeder

Frontend Setup:
1. cd client
2. npm install
3. cp .env.example .env
4. npm run dev

================================================================================
TROUBLESHOOTING
================================================================================

Login fails:
- Ensure backend is running (START_BACKEND.bat)
- Check client/.env has: VITE_API_BASE_URL=http://127.0.0.1:8000/api
- Verify port 8000 is not blocked by firewall

CORS errors:
- Backend is configured for localhost:5173
- Make sure you access frontend through correct URL

Database errors:
- Run: php artisan migrate
- Run: php artisan db:seed --class=AdminUserSeeder

================================================================================
PRESENTATION
================================================================================

For project presentation slides, see: PROJECT_PRESENTATION.txt

================================================================================
SUPPORT
================================================================================

For issues or questions:
1. Check this README
2. Review PROJECT_PRESENTATION.txt
3. Check browser console (F12) for errors
4. Check Laravel logs: server/storage/logs/laravel.log

================================================================================
