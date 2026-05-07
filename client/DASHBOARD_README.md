# Distributed Task Scheduler - Vue 3 Dashboard

## Overview

This is a complete, production-ready Vue 3 frontend dashboard for the Distributed Task Scheduler system. It provides real-time monitoring of jobs, tasks, and workers with automatic polling and live updates.

## Features Implemented

### ✅ Pinia Stores (State Management)
- **jobsStore** - Manages jobs state with CRUD operations
- **workersStore** - Manages workers state and statistics
- **metricsStore** - Manages system-wide metrics and health

### ✅ Vue Components

#### Core Components
- **StatusBadge.vue** - Reusable colored status badges (green=done, blue=running, yellow=pending, red=failed)
- **JobSubmitForm.vue** - Form to submit new jobs with client-side validation
- **JobsTable.vue** - Sortable, filterable job list with progress bars
- **TasksTable.vue** - Task list with color-coded statuses and statistics
- **WorkersGrid.vue** - Card grid showing worker status, heartbeat, and stats
- **MetricsBar.vue** - Top-level KPI dashboard with system health indicators
- **JobDetail.vue** - Detailed job view with all tasks

#### Views/Pages
- **Dashboard.vue** - Main dashboard with all components
- **JobDetailView.vue** - Detailed job view page with task drilldown

### ✅ Features

#### Real-time Updates
- Automatic polling every 5 seconds
- Live progress updates for jobs and tasks
- Worker heartbeat monitoring with visual indicators

#### Job Management
- Submit new jobs with validation
- View all jobs with filtering by status
- Sort jobs by ID, name, priority, or creation date
- Cancel running jobs
- Track job progress with visual progress bars
- Drilldown to view all tasks for a job

#### Worker Monitoring
- View all registered workers
- Real-time worker status (idle/busy/dead)
- Worker statistics (tasks completed/failed)
- Success rate calculation and visualization
- Heartbeat status with color indicators

#### System Metrics
- Total jobs, tasks, and workers
- Completion and failure rates
- System throughput (tasks/second)
- Average task duration
- Worker utilization percentage
- System health indicator

#### UI/UX Features
- Modern, professional Tailwind CSS styling
- Responsive design (mobile, tablet, desktop)
- Color-coded status badges
- Progress bars with animations
- Loading states and error handling
- Empty states with helpful messages
- Hover effects and transitions

### ✅ Styling
- Tailwind CSS for utility-first styling
- Custom color palette with primary blues
- Consistent spacing and typography
- Card-based layout
- Professional shadows and borders
- Responsive grid layouts

### ✅ Router Configuration
- `/` - Dashboard (main view)
- `/jobs/:id` - Job detail view
- Catch-all redirect to dashboard
- Dynamic page titles

## Project Structure

```
client/src/
├── api/
│   ├── axios.js           # Axios configuration with interceptors
│   └── index.js           # API service layer with all endpoints
├── assets/
│   └── main.css           # Tailwind CSS and custom styles
├── components/
│   ├── JobDetail.vue      # Job detail component
│   ├── JobSubmitForm.vue  # Job submission form
│   ├── JobsTable.vue      # Jobs table with sorting/filtering
│   ├── MetricsBar.vue     # System metrics dashboard
│   ├── StatusBadge.vue    # Reusable status badge
│   ├── TasksTable.vue     # Tasks table with statistics
│   └── WorkersGrid.vue    # Workers grid with cards
├── router/
│   └── index.js           # Vue Router configuration
├── stores/
│   ├── jobsStore.js       # Jobs state management
│   ├── metricsStore.js    # Metrics state management
│   └── workersStore.js    # Workers state management
├── views/
│   ├── Dashboard.vue      # Main dashboard view
│   └── JobDetailView.vue  # Job detail view
├── App.vue                # Root component
└── main.js                # Application entry point
```

## API Integration

The dashboard integrates with the Laravel backend API at `http://localhost:8000/api`:

### Jobs Endpoints
- `GET /jobs` - List all jobs
- `GET /jobs/:id` - Get job details
- `POST /jobs` - Submit new job
- `DELETE /jobs/:id` - Cancel job
- `GET /jobs/:id/tasks` - Get job tasks

### Workers Endpoints
- `GET /workers` - List all workers
- `GET /workers/:key` - Get worker details
- `POST /workers/register` - Register worker
- `POST /workers/:key/heartbeat` - Send heartbeat

### Metrics Endpoints
- `GET /metrics` - Get system metrics
- `GET /metrics/history` - Get metrics history

## Running the Dashboard

### Development Mode
```bash
cd client
npm install
npm run dev
```

The dashboard will be available at `http://localhost:5173`

### Production Build
```bash
npm run build
npm run preview
```

## Configuration

### Environment Variables
Create a `.env` file in the client directory:

```env
VITE_API_BASE_URL=http://localhost:8000/api
```

### Vite Configuration
The Vite config includes:
- Proxy to backend API at `/api`
- Path alias `@` pointing to `src/`
- Vue DevTools integration

## Key Features Explained

### Automatic Polling
All views automatically refresh data every 5 seconds to show live updates without requiring WebSockets.

### Status Color Coding
- **Green** - Completed/Done/Success
- **Blue** - Running/Busy/Active
- **Yellow** - Pending/Waiting
- **Red** - Failed/Dead/Error
- **Gray** - Cancelled/Idle
- **Purple** - Assigned

### Progress Tracking
Jobs show real-time progress with:
- Visual progress bars
- Percentage completion
- Task counts (completed/total)
- Failed task indicators

### Worker Health Monitoring
Workers display:
- Status badge (idle/busy/dead)
- Heartbeat indicator (pulsing green = healthy)
- Success rate with color-coded bar
- Task statistics

### Form Validation
Job submission form includes:
- Required field validation
- Min/max length checks
- Number range validation
- Real-time error messages
- Success feedback

## Best Practices Implemented

1. **Composition API** - Modern Vue 3 syntax
2. **Pinia Stores** - Centralized state management
3. **Component Reusability** - DRY principle
4. **Error Handling** - Try-catch with user feedback
5. **Loading States** - Visual feedback during API calls
6. **Responsive Design** - Mobile-first approach
7. **Accessibility** - Semantic HTML and ARIA labels
8. **Performance** - Computed properties and efficient rendering
9. **Code Organization** - Clear separation of concerns
10. **Documentation** - Inline comments and README

## Browser Support

- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Dependencies

- **Vue 3** - Progressive JavaScript framework
- **Vue Router** - Official router for Vue.js
- **Pinia** - State management library
- **Axios** - HTTP client
- **Tailwind CSS** - Utility-first CSS framework
- **Vite** - Next-generation frontend tooling

## Future Enhancements

Potential improvements:
- WebSocket integration for instant updates
- Charts and graphs for metrics history
- Task log viewer
- Advanced filtering and search
- Export data to CSV/JSON
- Dark mode toggle
- User authentication
- Notifications system
- Task retry manual trigger
- Worker management (start/stop)

## Troubleshooting

### API Connection Issues
- Ensure backend is running at `http://localhost:8000`
- Check CORS configuration in Laravel
- Verify `.env` file has correct API URL

### Styling Issues
- Run `npm install` to ensure Tailwind is installed
- Check that `main.css` is imported in `main.js`
- Verify `tailwind.config.js` content paths

### Data Not Updating
- Check browser console for API errors
- Verify polling interval is running
- Check network tab for failed requests

## License

This project is part of the Distributed Task Scheduler system.
