# Admin Dashboard - Quick Start Guide

## 🚀 Getting Started

### Prerequisites
- Node.js 20.19.0 or higher
- npm or yarn
- Backend server running

### Installation

1. **Install Dependencies**
   ```bash
   cd client
   npm install
   ```

2. **Configure Environment**
   ```bash
   cp .env.example .env
   ```
   
   Edit `.env` and set your API URL:
   ```env
   VITE_API_BASE_URL=http://localhost:8000/api
   ```

3. **Start Development Server**
   ```bash
   npm run dev
   ```

4. **Access the Dashboard**
   - Open browser to `http://localhost:5173`
   - Login with admin credentials
   - Navigate to `/admin` route

## 📋 Features Overview

### Dashboard View
The main dashboard (`/admin`) displays:

✅ **4 Stat Cards**
- Total Jobs with trend indicator
- Completed jobs count
- Running jobs count  
- Failed jobs count

✅ **Jobs Overview Chart**
- 7-day trend line chart
- Three data series (Completed, Running, Failed)
- Interactive tooltips

✅ **Tasks by Status Chart**
- Donut chart with center total
- Color-coded segments
- Percentage breakdown

✅ **Recent Jobs Table**
- Last 10 jobs
- Status badges
- Progress bars
- Task counts
- Timestamps

✅ **System Health Panel**
- Component status indicators
- CPU/Memory/Disk usage meters
- Real-time health monitoring

### Navigation Sections

| Section | Status | Description |
|---------|--------|-------------|
| Dashboard | ✅ Complete | Main overview with charts |
| Jobs | ✅ Complete | Job management interface |
| Tasks | 🚧 Placeholder | Coming soon |
| Workers | ✅ Complete | Worker monitoring |
| Queues | 🚧 Placeholder | Coming soon |
| Schedulers | 🚧 Placeholder | Coming soon |
| Monitoring | 🚧 Placeholder | Coming soon |
| Logs | 🚧 Placeholder | Coming soon |
| Users | ✅ Complete | User management |
| Settings | 🚧 Placeholder | Coming soon |

## 🎨 Design Specifications

### Color Scheme
```css
Sidebar Background: #1a1f3a (Dark Navy)
Active Item: #6366f1 (Purple)
Success/Completed: #10b981 (Green)
Running/Info: #3b82f6 (Blue)
Failed/Error: #ef4444 (Red)
Pending/Warning: #f59e0b (Yellow)
```

### Layout
- **Sidebar Width**: 256px (16rem)
- **Main Content**: Full width minus sidebar
- **Card Padding**: 24px (1.5rem)
- **Border Radius**: 12px (rounded-xl)

## 🔧 Configuration

### Auto-Refresh
Dashboard auto-refreshes every 30 seconds. To change:

```javascript
// In AdminDashboard.vue
refreshInterval = setInterval(() => {
  loadDashboardData()
}, 30000) // Change to desired milliseconds
```

### Time Filter Options
Default options: 7, 14, 30 days. To add more:

```vue
<select v-model="timeFilter">
  <option value="7">Last 7 days</option>
  <option value="14">Last 14 days</option>
  <option value="30">Last 30 days</option>
  <option value="90">Last 90 days</option> <!-- Add this -->
</select>
```

### Chart Customization

**Line Chart Colors:**
```javascript
datasets: [
  {
    label: 'Completed',
    borderColor: '#3b82f6', // Change color here
    backgroundColor: 'rgba(59, 130, 246, 0.1)',
  }
]
```

**Donut Chart Colors:**
```javascript
backgroundColor: [
  '#10b981', // Completed - Green
  '#3b82f6', // Running - Blue
  '#ef4444', // Failed - Red
  '#f59e0b'  // Pending - Yellow
]
```

## 📡 API Requirements

### Required Endpoints

1. **GET /admin/metrics**
   ```json
   {
     "jobs": {
       "total": 128,
       "completed": 98,
       "running": 18,
       "failed": 12,
       "pending": 0,
       "cancelled": 0
     },
     "workers": {
       "total": 5,
       "active": 4
     },
     "tasks": {
       "total": 328
     }
   }
   ```

2. **GET /admin/jobs?limit=10&sort=-created_at**
   ```json
   {
     "data": [
       {
         "id": 1,
         "name": "Job Name",
         "status": "completed",
         "total_tasks": 100,
         "completed_tasks": 100,
         "created_at": "2024-01-15T10:30:00Z"
       }
     ]
   }
   ```

3. **GET /admin/workers**
4. **GET /admin/users**

## 🐛 Troubleshooting

### Issue: Charts not displaying
**Solution:**
```bash
# Reinstall chart dependencies
npm install chart.js vue-chartjs --save
```

### Issue: Sidebar not showing
**Solution:**
- Check if `AdminLayout` is properly imported
- Verify Tailwind CSS is loaded
- Check browser console for errors

### Issue: API errors
**Solution:**
- Verify backend is running
- Check `.env` file has correct API URL
- Confirm you're logged in as admin
- Check network tab for failed requests

### Issue: Build errors
**Solution:**
```bash
# Clear cache and rebuild
rm -rf node_modules dist
npm install
npm run build
```

## 🎯 Testing Checklist

- [ ] Dashboard loads without errors
- [ ] All 4 stat cards display correct data
- [ ] Line chart renders with 3 data series
- [ ] Donut chart shows task breakdown
- [ ] Recent jobs table populates
- [ ] System health indicators show status
- [ ] Sidebar navigation works
- [ ] Refresh button updates data
- [ ] Time filter changes chart data
- [ ] Auto-refresh works (wait 30s)
- [ ] Logout button works
- [ ] Responsive on mobile
- [ ] No console errors

## 📱 Responsive Breakpoints

```css
/* Mobile */
@media (max-width: 640px) {
  - Sidebar collapses
  - Cards stack vertically
  - Table scrolls horizontally
}

/* Tablet */
@media (min-width: 641px) and (max-width: 1024px) {
  - 2 cards per row
  - Charts stack
}

/* Desktop */
@media (min-width: 1025px) {
  - 4 cards per row
  - Charts side by side
  - Full layout
}
```

## 🚀 Performance Tips

1. **Optimize Images**: Use WebP format for icons
2. **Lazy Load**: Charts only render when visible
3. **Debounce**: Refresh button has built-in debouncing
4. **Memoization**: Chart data is computed and cached
5. **Cleanup**: Intervals cleared on unmount

## 📚 Component Reference

### AdminSidebar.vue
```vue
<AdminSidebar
  :active-item="'dashboard'"
  :user-name="'Admin User'"
  @navigate="handleNavigate"
  @logout="handleLogout"
/>
```

### AdminLayout.vue
```vue
<AdminLayout
  :active-section="activeSection"
  :user-name="userName"
  @navigate="handleNavigate"
  @logout="handleLogout"
>
  <!-- Your content here -->
</AdminLayout>
```

### LineChart.vue
```vue
<LineChart
  :data="chartData"
  :options="chartOptions"
/>
```

### DonutChart.vue
```vue
<DonutChart
  :data="chartData"
  :show-center-text="true"
  :center-value="328"
  center-label="Total Tasks"
/>
```

## 🔐 Security Notes

- Admin routes protected by authentication guard
- Role-based access control (admin only)
- API tokens stored securely
- CSRF protection enabled
- XSS prevention via Vue sanitization

## 📞 Support

For issues or questions:
1. Check this guide first
2. Review `ADMIN_DASHBOARD_README.md`
3. Check browser console for errors
4. Verify API responses in network tab
5. Contact development team

## 🎉 Success!

If you see the dashboard with:
- ✅ Sidebar with TaskFlow logo
- ✅ 4 stat cards with data
- ✅ Line chart showing trends
- ✅ Donut chart with task breakdown
- ✅ Recent jobs table
- ✅ System health panel

**Congratulations! Your admin dashboard is working perfectly!** 🎊

---

**Need Help?** Check the full documentation in `ADMIN_DASHBOARD_README.md`
