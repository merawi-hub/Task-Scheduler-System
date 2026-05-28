<template>
  <div class="flex-1 overflow-auto">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
      <div class="px-8 py-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">Overview of your distributed task scheduler</p>
          </div>
          <div class="flex items-center gap-4">
              <!-- Notifications -->
              <button @click="$router.push('/admin/notifications')" class="relative p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
              </button>

              <!-- Settings -->
              <button @click="$router.push('/admin/settings')" class="relative p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </button>

              <!-- Refresh Button -->
              <button @click="refreshData" class="flex items-center gap-2 px-4 py-2 text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span class="text-sm font-medium">Refresh</span>
              </button>

              <!-- Time Filter -->
              <select class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option>Last 24 hours</option>
                <option>Last 7 days</option>
                <option>Last 30 days</option>
              </select>
            </div>
          </div>
        </div>
      </header>

      <!-- Dashboard Content -->
      <main class="p-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
          <!-- Total Jobs -->
          <div @click="navigateToJobs()" class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 cursor-pointer hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm font-medium text-gray-500">Total Jobs</p>
                <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ stats.totalJobs }}</h3>
                <p class="text-sm mt-2" :class="stats.jobsChange >= 0 ? 'text-green-600' : 'text-red-600'">
                  <span class="font-medium">{{ stats.jobsChange >= 0 ? '+' : '' }}{{ stats.jobsChange }}%</span>
                  <span class="text-gray-500 ml-1">vs yesterday</span>
                </p>
              </div>
              <div class="p-3 bg-blue-50 rounded-lg">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Pending -->
          <div @click="navigateToJobs('pending')" class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 cursor-pointer hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm font-medium text-gray-500">Pending</p>
                <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ stats.pending }}</h3>
                <p class="text-sm mt-2" :class="stats.pendingChange >= 0 ? 'text-green-600' : 'text-red-600'">
                  <span class="font-medium">{{ stats.pendingChange >= 0 ? '+' : '' }}{{ stats.pendingChange }}%</span>
                  <span class="text-gray-500 ml-1">vs yesterday</span>
                </p>
              </div>
              <div class="p-3 bg-yellow-50 rounded-lg">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Running -->
          <div @click="navigateToJobs('running')" class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 cursor-pointer hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm font-medium text-gray-500">Running</p>
                <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ stats.running }}</h3>
                <p class="text-sm mt-2" :class="stats.runningChange >= 0 ? 'text-green-600' : 'text-red-600'">
                  <span class="font-medium">{{ stats.runningChange >= 0 ? '+' : '' }}{{ stats.runningChange }}%</span>
                  <span class="text-gray-500 ml-1">vs yesterday</span>
                </p>
              </div>
              <div class="p-3 bg-blue-50 rounded-lg">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Completed -->
          <div @click="navigateToJobs('completed')" class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 cursor-pointer hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm font-medium text-gray-500">Completed</p>
                <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ stats.completed }}</h3>
                <p class="text-sm mt-2" :class="stats.completedChange >= 0 ? 'text-green-600' : 'text-red-600'">
                  <span class="font-medium">{{ stats.completedChange >= 0 ? '+' : '' }}{{ stats.completedChange }}%</span>
                  <span class="text-gray-500 ml-1">vs yesterday</span>
                </p>
              </div>
              <div class="p-3 bg-green-50 rounded-lg">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Failed -->
          <div @click="navigateToJobs('failed')" class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 cursor-pointer hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm font-medium text-gray-500">Failed</p>
                <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ stats.failed }}</h3>
                <p class="text-sm mt-2" :class="stats.failedChange <= 0 ? 'text-green-600' : 'text-red-600'">
                  <span class="font-medium">{{ stats.failedChange >= 0 ? '+' : '' }}{{ stats.failedChange }}%</span>
                  <span class="text-gray-500 ml-1">vs yesterday</span>
                </p>
              </div>
              <div class="p-3 bg-red-50 rounded-lg">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Worker Control Panel -->
        <div class="mb-8">
          <WorkerControlPanel />
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
          <!-- Jobs Overview Chart -->
          <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-lg font-semibold text-gray-900">Jobs Overview</h3>
              <a href="#" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">View All</a>
            </div>
            <LineChart :data="jobsChartData" />
          </div>

          <!-- Tasks by Status Chart -->
          <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Tasks by Status</h3>
            <DonutChart :data="tasksChartData" />
          </div>
        </div>

        <!-- Bottom Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Recent Jobs Table -->
          <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100">
              <h3 class="text-lg font-semibold text-gray-900">Recent Jobs</h3>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tasks</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr v-for="job in recentJobs" :key="job.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ job.name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="getStatusClass(job.status)" class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full">
                        {{ job.status.toUpperCase() }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center gap-2">
                        <div class="flex-1 bg-gray-200 rounded-full h-2 max-w-[100px]">
                          <div :class="getProgressColor(job.status)" class="h-2 rounded-full transition-all"
                            :style="{ width: computeJobProgress(job) + '%' }"></div>
                        </div>
                        <span class="text-xs font-medium text-gray-600">{{ computeJobProgress(job) }}%</span>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ job.completed_tasks }}/{{ job.total_tasks }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(job.created_at) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- System Health -->
          <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">System Health</h3>
            <div class="space-y-4">
              <div v-for="service in systemHealth" :key="service.name" class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div :class="service.status === 'Healthy' ? 'bg-green-100' : 'bg-red-100'" class="p-2 rounded-lg">
                    <svg class="w-5 h-5" :class="service.status === 'Healthy' ? 'text-green-600' : 'text-red-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-900">{{ service.name }}</p>
                    <p v-if="service.detail" class="text-xs text-gray-500">{{ service.detail }}</p>
                  </div>
                </div>
                <span :class="service.status === 'Healthy' ? 'text-green-600' : 'text-red-600'" class="text-sm font-medium">
                  {{ service.status }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAdminStore } from '@/stores/adminStore'
import LineChart from '@/components/charts/LineChart.vue'
import DonutChart from '@/components/charts/DonutChart.vue'
import WorkerControlPanel from '@/components/WorkerControlPanel.vue'

const router = useRouter()
const adminStore = useAdminStore()
const loading = ref(false)
const refreshInterval = ref(null)

// Computed stats from store
const stats = computed(() => {
  const metrics = adminStore.systemMetrics
  if (!metrics) {
    return {
      totalJobs: 0, jobsChange: 0,
      pending: 0, pendingChange: 0,
      completed: 0, completedChange: 0,
      running: 0, runningChange: 0,
      failed: 0, failedChange: 0
    }
  }

  // Admin metrics structure: { realtime: { jobs, tasks, workers }, jobs, tasks, workers }
  const jobs = metrics.jobs || metrics.realtime?.jobs || {}
  const tasks = metrics.tasks || metrics.realtime?.tasks || {}
  const history = adminStore.metricsHistory?.legacy_history?.data || []
  const taskHistory = adminStore.metricsHistory?.history?.tasks_completed || []
  const taskFailedHistory = adminStore.metricsHistory?.history?.tasks_failed || []

  return {
    totalJobs: jobs.total || 0,
    jobsChange: calcChangeFromSeries(history.map(item => item.jobs_created || 0)),
    pending: jobs.pending || tasks.pending || 0,
    pendingChange: 0,
    completed: jobs.completed || 0,
    completedChange: calcChangeFromSeries(taskHistory.map(item => item.count || 0)),
    running: jobs.running || 0,
    runningChange: 0,
    failed: jobs.failed || 0,
    failedChange: calcChangeFromSeries(taskFailedHistory.map(item => item.count || 0))
  }
})

// Navigate to jobs page with optional status filter
function navigateToJobs(status = null) {
  if (status) {
    router.push({ path: '/admin/jobs', query: { status } })
  } else {
    router.push('/admin/jobs')
  }
}

// Jobs chart data
const jobsChartData = computed(() => {
  const history = adminStore.metricsHistory?.history
  if (!history?.intervals) {
    return {
      labels: [],
      datasets: [
        {
          label: 'Completed',
          data: [],
          borderColor: '#6366f1',
          backgroundColor: 'rgba(99, 102, 241, 0.1)',
          tension: 0.4,
          fill: true
        },
        {
          label: 'Running',
          data: [],
          borderColor: '#10b981',
          backgroundColor: 'rgba(16, 185, 129, 0.1)',
          tension: 0.4,
          fill: true
        },
        {
          label: 'Failed',
          data: [],
          borderColor: '#ef4444',
          backgroundColor: 'rgba(239, 68, 68, 0.1)',
          tension: 0.4,
          fill: true
        }
      ]
    }
  }

  const labels = history.intervals.map((timestamp) => formatIntervalLabel(timestamp))
  const completedSeries = history.tasks_completed.map(item => item.count || 0)
  const failedSeries = history.tasks_failed.map(item => item.count || 0)
  const runningBaseline = new Array(labels.length).fill(adminStore.systemMetrics?.jobs?.running || 0)

  return {
    labels,
    datasets: [
      {
        label: 'Completed',
        data: completedSeries,
        borderColor: '#6366f1',
        backgroundColor: 'rgba(99, 102, 241, 0.1)',
        tension: 0.4,
        fill: true
      },
      {
        label: 'Running',
        data: runningBaseline,
        borderColor: '#10b981',
        backgroundColor: 'rgba(16, 185, 129, 0.1)',
        tension: 0.4,
        fill: true
      },
      {
        label: 'Failed',
        data: failedSeries,
        borderColor: '#ef4444',
        backgroundColor: 'rgba(239, 68, 68, 0.1)',
        tension: 0.4,
        fill: true
      }
    ]
  }
})

// Tasks chart data from real metrics
const tasksChartData = computed(() => {
  const metrics = adminStore.systemMetrics
  // Admin metrics: { tasks: {...} } or { realtime: { tasks: {...} } }
  const tasks = metrics?.tasks || metrics?.realtime?.tasks || {}
  return {
    labels: ['Completed', 'Running', 'Failed', 'Pending'],
    datasets: [{
      data: [
        tasks.done || tasks.completed || 0,
        tasks.running || 0,
        tasks.failed || 0,
        tasks.pending || 0
      ],
      backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#6b7280'],
      borderWidth: 0
    }]
  }
})

// Recent jobs from store — pass raw job objects so computeJobProgress can work on them
const recentJobs = computed(() => {
  return adminStore.allJobs.slice(0, 5)
})

// System health computed from metrics
const systemHealth = computed(() => {
  const metrics = adminStore.systemMetrics
  if (!metrics) {
    return [
      { name: 'Master Node', status: 'Unknown', detail: null },
      { name: 'Worker Nodes', status: 'Unknown', detail: '0/0 Online' },
      { name: 'Queues', status: 'Unknown', detail: '0 Active' },
      { name: 'Database', status: 'Unknown', detail: null },
      { name: 'Redis', status: 'Unknown', detail: null }
    ]
  }

  // Support both { workers: {...} } and { realtime: { workers: {...} } }
  const workers = metrics.workers || metrics.realtime?.workers || {}
  const jobs    = metrics.jobs    || metrics.realtime?.jobs    || {}
  const activeWorkers = workers.active || (workers.idle || 0) + (workers.busy || 0)
  const totalWorkers  = workers.total  || 0
  const runningJobs   = jobs.running   || 0

  return [
    { name: 'Master Node', status: 'Healthy', detail: null },
    {
      name: 'Worker Nodes',
      status: activeWorkers > 0 ? 'Healthy' : 'Warning',
      detail: `${activeWorkers}/${totalWorkers} Online`
    },
    {
      name: 'Queues',
      status: 'Healthy',
      detail: `${runningJobs} Active`
    },
    { name: 'Database', status: 'Healthy', detail: null },
    { name: 'Redis', status: 'Healthy', detail: null }
  ]
})

function getStatusClass(status) {
  const classes = {
    completed: 'bg-green-100 text-green-800',
    running: 'bg-blue-100 text-blue-800',
    failed: 'bg-red-100 text-red-800',
    pending: 'bg-yellow-100 text-yellow-800',
    cancelled: 'bg-gray-100 text-gray-800'
  }
  return classes[status] || classes.pending
}

function getProgressColor(status) {
  const colors = {
    completed: 'bg-green-500',
    running: 'bg-blue-500',
    failed: 'bg-red-500',
    pending: 'bg-yellow-500'
  }
  return colors[status] || colors.pending
}

// Compute progress from actual task counts (API returns completed_tasks/total_tasks)
function computeJobProgress(job) {
  const total = job.total_tasks || 0
  const completed = job.completed_tasks || 0
  if (total === 0) return 0
  return Math.round((completed / total) * 100)
}

function formatDate(dateString) {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) + ' ' +
         date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
}

function formatIntervalLabel(timestamp) {
  const date = new Date(timestamp)
  return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
}

function calcChangeFromSeries(series) {
  if (!series || series.length < 2) return 0
  const latest = series[series.length - 1] || 0
  const previous = series[series.length - 2] || 0
  if (previous === 0) return latest > 0 ? 100 : 0
  return Math.round(((latest - previous) / previous) * 100)
}

async function refreshData() {
  loading.value = true
  try {
    // Fetch dashboard data (metrics + recent jobs)
    await Promise.all([
      adminStore.fetchDashboardData({ limit: 10 }),
      adminStore.fetchMetricsHistory('day')
    ])
  } catch (error) {
    console.error('Failed to refresh dashboard data:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  refreshData()
  // Auto-refresh every 30 seconds
  refreshInterval.value = setInterval(refreshData, 30000)
})

onUnmounted(() => {
  if (refreshInterval.value) {
    clearInterval(refreshInterval.value)
  }
})
</script>
