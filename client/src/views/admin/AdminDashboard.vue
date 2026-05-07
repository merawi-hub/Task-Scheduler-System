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
              <button class="relative p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
              </button>

              <!-- Settings -->
              <button class="relative p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
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
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <!-- Total Jobs -->
          <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
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

          <!-- Completed -->
          <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
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

          <!-- Running -->
          <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
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

          <!-- Failed -->
          <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
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
                          <div :class="getProgressColor(job.status)" class="h-2 rounded-full transition-all" :style="{ width: job.progress + '%' }"></div>
                        </div>
                        <span class="text-xs font-medium text-gray-600">{{ job.progress }}%</span>
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
import { useAdminStore } from '@/stores/adminStore'
import LineChart from '@/components/charts/LineChart.vue'
import DonutChart from '@/components/charts/DonutChart.vue'

const adminStore = useAdminStore()
const loading = ref(false)
const refreshInterval = ref(null)

// Computed stats from store
const stats = computed(() => {
  const metrics = adminStore.systemMetrics
  if (!metrics) {
    return {
      totalJobs: 0,
      jobsChange: 0,
      completed: 0,
      completedChange: 0,
      running: 0,
      runningChange: 0,
      failed: 0,
      failedChange: 0
    }
  }

  return {
    totalJobs: metrics.jobs?.total || 0,
    jobsChange: 12.5, // TODO: Calculate from historical data
    completed: metrics.jobs?.completed || 0,
    completedChange: 8.2, // TODO: Calculate from historical data
    running: metrics.jobs?.running || 0,
    runningChange: 5.1, // TODO: Calculate from historical data
    failed: metrics.jobs?.failed || 0,
    failedChange: -2.4 // TODO: Calculate from historical data
  }
})

// Jobs chart data
const jobsChartData = computed(() => {
  // TODO: Fetch historical data from API
  // For now, using mock data
  return {
    labels: ['May 7', 'May 8', 'May 9', 'May 10', 'May 11', 'May 12', 'May 13'],
    datasets: [
      {
        label: 'Completed',
        data: [50, 55, 75, 85, 70, 80, stats.value.completed],
        borderColor: '#6366f1',
        backgroundColor: 'rgba(99, 102, 241, 0.1)',
        tension: 0.4,
        fill: true
      },
      {
        label: 'Running',
        data: [20, 25, 30, 35, 30, 40, stats.value.running],
        borderColor: '#10b981',
        backgroundColor: 'rgba(16, 185, 129, 0.1)',
        tension: 0.4,
        fill: true
      },
      {
        label: 'Failed',
        data: [5, 8, 10, 12, 10, 15, stats.value.failed],
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
  if (!metrics?.tasks) {
    return {
      labels: ['Completed', 'Running', 'Failed', 'Pending'],
      datasets: [{
        data: [0, 0, 0, 0],
        backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#6b7280'],
        borderWidth: 0
      }]
    }
  }

  return {
    labels: ['Completed', 'Running', 'Failed', 'Pending'],
    datasets: [{
      data: [
        metrics.tasks.completed || 0,
        metrics.tasks.running || 0,
        metrics.tasks.failed || 0,
        metrics.tasks.pending || 0
      ],
      backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#6b7280'],
      borderWidth: 0
    }]
  }
})

// Recent jobs from store
const recentJobs = computed(() => {
  return adminStore.allJobs.slice(0, 5).map(job => ({
    id: job.id,
    name: job.name,
    status: job.status,
    progress: job.progress || 0,
    completed_tasks: job.completed_tasks || 0,
    total_tasks: job.total_tasks || 0,
    created_at: job.created_at
  }))
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

  const activeWorkers = metrics.workers?.active || 0
  const totalWorkers = metrics.workers?.total || 0
  const runningJobs = metrics.jobs?.running || 0

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

function formatDate(dateString) {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) + ' ' +
         date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
}

async function refreshData() {
  loading.value = true
  try {
    // Fetch dashboard data (metrics + recent jobs)
    await adminStore.fetchDashboardData({ limit: 10 })
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
