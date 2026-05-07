# Quick Start Guide - Vue 3 Dashboard

## Prerequisites

- Node.js 20.19.0 or higher
- npm (comes with Node.js)
- Laravel backend running at http://localhost:8000

## Installation

```bash
cd client
npm install
```

## Running the Dashboard

### Development Mode (Recommended)
```bash
npm run dev
```

The dashboard will be available at: **http://localhost:5173**

### Production Build
```bash
npm run build
npm run preview
```

## First Time Setup

1. **Start the Laravel Backend**
   ```bash
   cd ../server
   php artisan serve
   ```
   Backend should be running at http://localhost:8000

2. **Start Workers** (in separate terminals)
   ```bash
   php artisan worker:run --key=worker-001
   php artisan worker:run --key=worker-002
   php artisan worker:run --key=worker-003
   ```

3. **Start the Vue Dashboard**
   ```bash
   cd ../client
   npm run dev
   ```

4. **Open Browser**
   Navigate to http://localhost:5173

## Using the Dashboard

### Submit a Job
1. Fill out the "Submit New Job" form
2. Enter job name (e.g., "Process Images")
3. Select job type (e.g., "Image Processing")
4. Set number of tasks (e.g., 50)
5. Adjust priority (1-10)
6. Click "Submit Job"

### Monitor Jobs
- View all jobs in the Jobs table
- Filter by status (pending, running, completed, failed)
- Sort by ID, name, priority, or date
- Click on a job to view details

### View Job Details
- Click any job row to see detailed view
- See all tasks for that job
- Monitor real-time progress
- View task statuses and worker assignments

### Monitor Workers
- Scroll to Workers section
- See all registered workers
- Check worker status (idle/busy/dead)
- View worker statistics
- Monitor heartbeat indicators

### System Metrics
- View top metrics bar
- See total jobs, tasks, workers
- Check completion and failure rates
- Monitor throughput
- View system health

## Features

### Auto-Refresh
The dashboard automatically refreshes every 5 seconds to show live updates.

### Manual Refresh
Click the "Refresh" button in the header to manually update all data.

### Status Colors
- **Green** - Completed/Done
- **Blue** - Running/Busy
- **Yellow** - Pending
- **Red** - Failed/Dead
- **Gray** - Cancelled/Idle
- **Purple** - Assigned

### Progress Tracking
- Jobs show progress bars
- Percentage completion displayed
- Task counts (completed/total/failed)
- Real-time updates

## Troubleshooting

### Dashboard Not Loading
- Check if backend is running: http://localhost:8000/api/jobs
- Check browser console for errors
- Verify .env file has correct API URL

### No Data Showing
- Ensure backend database is migrated
- Check if workers are registered
- Submit a test job to see data

### API Errors
- Check CORS configuration in Laravel
- Verify API routes are working
- Check network tab in browser DevTools

### Styling Issues
- Run `npm install` to ensure all dependencies are installed
- Clear browser cache
- Check if Tailwind CSS is properly configured

## Environment Configuration

Create a `.env` file in the client directory:

```env
VITE_API_BASE_URL=http://localhost:8000/api
```

## Development Tips

### Hot Module Replacement (HMR)
Vite provides instant updates when you edit files. No need to refresh the browser.

### Vue DevTools
Press `Alt+Shift+D` in the app to toggle Vue DevTools, or open http://localhost:5173/__devtools__/

### Browser Console
Open browser DevTools (F12) to see:
- API requests in Network tab
- Console logs for debugging
- Vue component tree in Vue tab

## Common Tasks

### Add a New Component
1. Create file in `src/components/`
2. Import in parent component
3. Use in template

### Add a New Route
1. Create view in `src/views/`
2. Add route in `src/router/index.js`
3. Add navigation link

### Add a New Store
1. Create file in `src/stores/`
2. Define state, computed, actions
3. Import and use in components

### Modify API Endpoints
1. Edit `src/api/index.js`
2. Add new methods to appropriate API object
3. Use in stores or components

## Production Deployment

### Build for Production
```bash
npm run build
```

This creates optimized files in the `dist/` directory.

### Preview Production Build
```bash
npm run preview
```

### Deploy to Server
1. Build the project
2. Copy `dist/` folder to web server
3. Configure web server to serve `index.html`
4. Set environment variables for production API URL

## Support

For issues or questions:
1. Check the DASHBOARD_README.md for detailed documentation
2. Check the IMPLEMENTATION_COMPLETE.md for implementation details
3. Review the code comments in components
4. Check the Laravel backend documentation

## Next Steps

1. ✅ Submit your first job
2. ✅ Monitor job progress
3. ✅ View worker statistics
4. ✅ Explore system metrics
5. ✅ Try filtering and sorting
6. ✅ View job details
7. ✅ Cancel a running job

Enjoy using the Distributed Task Scheduler Dashboard! 🚀
