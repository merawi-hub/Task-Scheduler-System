<template>
  <div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <UserSidebar />

    <!-- Main Content -->
    <div class="flex-1 ml-64 overflow-auto">
      <!-- Header -->
      <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="px-8 py-4">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
              <p class="text-sm text-gray-500 mt-1">Welcome back, {{ user?.name }}! Here's what's happening with your tasks.</p>
            </div>
            <div class="flex items-center gap-4">
              <!-- Search -->
              <div class="relative">
                <input
                  type="text"
                  placeholder="Search anything..."
                  class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <span class="absolute right-3 top-2.5 text-xs text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">⌘K</span>
              </div>

              <!-- Notifications -->
              <button class="relative p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
              </button>

              <!-- User Profile -->
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center">
                  <span class="text-white font-semibold text-sm">{{ getUserInitials() }}</span>
                </div>
                <div class="text-sm">
                  <p class="font-medium text-gray-900">{{ user?.name }}</p>
                  <p class="text-gray-500 text-xs">{{ user?.email }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Dashboard Content -->
      <main class="p-8">
        <!-- Stats Cards Row -->
        <div class="flex items-center justify-between mb-8">
          <div class="grid grid-cols-4 gap-6 flex-1">
            <!-- Total Jobs -->
            <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
              <div class="flex items-start justify-between">
                <div>
                  <p class="text-sm text-gray-500 mb-2">Total Jobs</p>
                  <h3 class="text-3xl font-bold text-gray-900">{{ stats.totalJobs }}</h3>
                  <p class="text-sm text-green-600 mt-2">
                    <span class="font-medium">+{{ stats.jobsChange }}%</span>
                    <span class="text-gray-500 ml-1">vs last 7 days</span>
                  </p>
                </div>
                <div class="p-3 bg-blue-50 rounded-lg">
                  <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                  </svg>
                </div>
              </div>
            </div>

            <!-- Completed -->
            <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
              <div class="flex items-start justify-between">
                <div>
                  <p class="text-sm text-gray-500 mb-2">Completed</p>
                  <h3 class="text-3xl font-bold text-gray-900">{{ stats.completed }}</h3>
                  <p class="text-sm text-green-600 mt-2">
                    <span class="font-medium">+{{ stats.completedChange }}%</span>
                    <span class="text-gray-500 ml-1">vs last 7 days</span>
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
            <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
              <div class="flex items-start justify-between">
                <div>
                  <p class="text-sm text-gray-500 mb-2">Running</p>
                  <h3 class="text-3xl font-bold text-gray-900">{{ stats.running }}</h3>
                  <p class="text-sm text-blue-600 mt-2">
                    <span class="font-medium">+{{ stats.runningChange }}</span>
                    <span class="text-gray-500 ml-1">vs last 7 days</span>
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
            <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
              <div class="flex items-start justify-between">
                <div>
                  <p class="text-sm text-gray-500 mb-2">Failed</p>
                  <h3 class="text-3xl font-bold text-gray-900">{{ stats.failed }}</h3>
                  <p class="text-sm text-red-600 mt-2">
                    <span class="font-medium">-{{ stats.failedChange }}%</span>
                    <span class="text-gray-500 ml-1">vs last 7 days</span>
                  </p>
                </div>
                <div class="p-3 bg-yellow-50 rounded-lg">
                  <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
              </div>
            </div>
          </div>

          <!-- Date Filter & Refresh -->
          <div class="ml-6 flex items-center gap-3">
            <button class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              Last 7 days
            </button>
            <button @click="refreshData" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
              <svg class="w-5 h-5" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Total Tasks Card -->
        <div class="mb-8">
          <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-indigo-100 mb-2">Total Tasks</p>
                <h3 class="text-4xl font-bold">{{ stats.totalTasks }}</h3>
                <p class="text-sm text-indigo-100 mt-2">+{{ stats.tasksChange }}%</p>
              </div>
              <div class="p-4 bg-white/20 rounded-lg backdrop-blur-sm">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-3 gap-6 mb-8">
          <!-- Job Overview Chart (2 columns) -->
          <div class="col-span-2 bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-lg font-semibold text-gray-900">Job Overview</h3>
              <select class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option>All Jobs</option>
                <option>Completed</option>
                <option>Running</option>
                <option>Failed</option>
              </select>
            </div>
            <UserLineChart :data="jobsChartData" />
          </div>

          <!-- Status Distribution (1 column) -->
          <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Status Distribution</h3>
            <UserDonutChart :data="statusChartData" />
          </div>
        </div>

        <!-- Bottom Section -->
        <div class="grid grid-cols-3 gap-6">
          <!-- Recent Jobs (2 columns) -->
          <div class="col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900">Recent Jobs</h3>
              <button @click="$router.push('/my-jobs')" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">View All Jobs</button>
            </div>
            <RecentJobsTable :jobs="recentJobs" />
          </div>

          <!-- Right Column -->
          <div class="space-y-6">
            <!-- Recent Activity -->
            <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
                <button class="text-sm font-medium text-indigo-600 hover:text-indigo-700">View All</button>
              </div>
              <div class="space-y-4">
                <div v-for="activity in recentActivity" :key="activity.id" class="flex items-start gap-3">
                  <div :class="[
                    'w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0',
                    activity.type === 'completed' ? 'bg-green-100' : activity.type === 'running' ? 'bg-blue-100' : 'bg-red-100'
                  ]">
                    <svg class="w-4 h-4" :class="[
                      activity.type === 'completed' ? 'text-green-600' : activity.type === 'running' ? 'text-blue-600' : 'text-red-600'
                    ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ activity.title }}</p>
                    <p class="text-xs text-gray-500">{{ activity.description }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ activity.time }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Top Workers -->
            <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Top Workers</h3>
                <button class="text-sm font-medium text-indigo-600 hover:text-indigo-700">View All</button>
              </div>
              <div class="space-y-3">
                <div v-for="worker in topWorkers" :key="worker.id" class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                      <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-900">{{ worker.name }}</p>
                      <p class="text-xs text-gray-500">{{ worker.tasks }} tasks</p>
                    </div>
                  </div>
                  <span class="text-sm font-semibold text-green-600">{{ worker.efficiency }}%</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Action Cards -->
        <div class="grid grid-cols-4 gap-6 mt-8">
          <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
            </div>
            <h4 class="text-sm font-semibold text-gray-900 mb-2">Create New Job</h4>
            <p class="text-xs text-gray-500 mb-4">Upload files or define tasks to create a new job.</p>
            <button class="w-full px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
              Create Job
            </button>
          </div>

          <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <h4 class="text-sm font-semibold text-gray-900 mb-2">My Schedules</h4>
            <p class="text-xs text-gray-500 mb-4">Manage your scheduled jobs and recurring tasks.</p>
            <button class="w-full px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
              View Schedules
            </button>
          </div>

          <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
              </svg>
            </div>
            <h4 class="text-sm font-semibold text-gray-900 mb-2">Active Tasks</h4>
            <p class="text-xs text-gray-500 mb-4">View and monitor all currently running tasks.</p>
            <button class="w-full px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
              View Active Tasks
            </button>
          </div>

          <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <h4 class="text-sm font-semibold text-gray-900 mb-2">Monitoring</h4>
            <p class="text-xs text-gray-500 mb-4">Track real-time stats about your jobs and system performance.</p>
            <button class="w-full px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
              View Monitoring
            </button>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useJobsStore } from '@/stores/jobsStore'
import UserSidebar from '@/components/UserSidebar.vue'
import UserLineChart from '@/components/charts/UserLineChart.vue'
import UserDonutChart from '@/components/charts/UserDonutChart.vue'
import RecentJobsTable from '@/components/RecentJobsTable.vue'

const authStore = useAuthStore()
const jobsStore = useJobsStore()

const loading = ref(false)
const refreshInterval = ref(null)

const user = computed(() => authStore.user)

const stats = ref({
  totalJobs: 24,
  jobsChange: 12.6,
  completed: 16,
  completedChange: 33.3,
  running: 5,
  runningChange: 2,
  failed: 3,
  failedChange: 33,
  totalTasks: 2450,
  tasksChange: 16.8
})

const jobsChartData = ref({
  labels: ['May 7', 'May 8', 'May 9', 'May 10', 'May 11', 'May 12', 'May 13'],
  datasets: [
    {
      label: 'Completed',
      data: [20, 25, 30, 35, 40, 45, 50],
      borderColor: '#10b981',
      backgroundColor: 'rgba(16, 185, 129, 0.1)',
      tension: 0.4,
      fill: true
    },
    {
      label: 'Running',
      data: [10, 15, 18, 20, 22, 25, 18],
      borderColor: '#3b82f6',
      backgroundColor: 'rgba(59, 130, 246, 0.1)',
      tension: 0.4,
      fill: true
    },
    {
      label: 'Failed',
      data: [2, 3, 4, 5, 4, 6, 5],
      borderColor: '#ef4444',
      backgroundColor: 'rgba(239, 68, 68, 0.1)',
      tension: 0.4,
      fill: true
    }
  ]
})

const statusChartData = ref({
  labels: ['Completed', 'Running', 'Failed', 'Pending'],
  datasets: [{
    data: [16, 5, 3, 0],
    backgroundColor: ['#10b981', '#3b82f6', '#ef4444', '#6b7280'],
    borderWidth: 0
  }]
})

const recentJobs = ref([
  { id: 1, name: 'Video Processing Job', status: 'completed', progress: 100, tasks: '120 / 120', created_at: '2024-05-13T10:30:00Z' },
  { id: 2, name: 'Image Resize Job', status: 'running', progress: 65, tasks: '65 / 100', created_at: '2024-05-13T10:15:00Z' },
  { id: 3, name: 'Data ETL Pipeline', status: 'completed', progress: 100, tasks: '80 / 80', created_at: '2024-05-13T09:45:00Z' },
  { id: 4, name: 'Report Generation', status: 'failed', progress: 20, tasks: '10 / 50', created_at: '2024-05-13T09:30:00Z' },
  { id: 5, name: 'ML Model Training', status: 'running', progress: 40, tasks: '20 / 50', created_at: '2024-05-13T09:15:00Z' }
])

const recentActivity = ref([
  { id: 1, type: 'completed', title: 'Video Processing Job', description: 'Job completed successfully', time: '5m ago' },
  { id: 2, type: 'running', title: 'Image Resize Job', description: 'Job is now running', time: '10m ago' },
  { id: 3, type: 'failed', title: 'Data ETL Pipeline', description: 'Job failed', time: '25m ago' },
  { id: 4, type: 'completed', title: 'Report Generation', description: 'Job completed successfully', time: '1h ago' },
  { id: 5, type: 'running', title: 'ML Model Training', description: 'Job is now running', time: '2h ago' }
])

const topWorkers = ref([
  { id: 1, name: 'worker-01', tasks: 162, efficiency: 95 },
  { id: 2, name: 'worker-02', tasks: 145, efficiency: 78 },
  { id: 3, name: 'worker-03', tasks: 128, efficiency: 71 },
  { id: 4, name: 'worker-04', tasks: 98, efficiency: 62 },
  { id: 5, name: 'worker-05', tasks: 85, efficiency: 52 }
])

function getUserInitials() {
  if (!user.value?.name) return 'U'
  const names = user.value.name.split(' ')
  if (names.length >= 2) {
    return names[0][0] + names[1][0]
  }
  return names[0][0]
}

async function refreshData() {
  loading.value = true
  try {
    await jobsStore.fetchJobs()
    // Update stats from real data
  } catch (error) {
    console.error('Failed to refresh data:', error)
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
