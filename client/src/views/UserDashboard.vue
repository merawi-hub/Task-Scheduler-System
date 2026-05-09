<template>
  <div class="flex h-screen bg-gray-50">
    <UserSidebar />

    <div class="flex-1 ml-64 overflow-auto">
      <!-- Header -->
      <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="px-8 py-4 flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-sm text-gray-500 mt-0.5">Welcome Back, {{ user?.name?.split(" ")[0] }} 👋</p>
          </div>
          <div class="flex items-center gap-4">
            <!-- Search -->
            <div class="relative">
              <input type="text" placeholder="Search anything..." class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
              <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <!-- Notifications Bell -->
            <button @click="$router.push('/notifications')" class="relative p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
              <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>
            <!-- User Avatar -->
            <div class="flex items-center gap-3 cursor-pointer" @click="$router.push('/profile')">
              <div class="w-9 h-9 rounded-full bg-indigo-600 flex items-center justify-center">
                <span class="text-white font-semibold text-sm">{{ getUserInitials() }}</span>
              </div>
              <div class="text-sm">
                <p class="font-medium text-gray-900">{{ user?.name }}</p>
                <p class="text-gray-500 text-xs">{{ user?.email }}</p>
              </div>
            </div>
          </div>
        </div>
      </header>

      <main class="p-8">

        <!-- Stats Cards -->
        <div class="grid grid-cols-4 gap-6 mb-8">
          <!-- Total Jobs -->
          <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm text-gray-500 mb-1">Total Jobs</p>
                <h3 class="text-3xl font-bold text-gray-900">{{ stats.totalJobs }}</h3>
                <p class="text-xs text-gray-400 mt-2">All submitted jobs</p>
              </div>
              <div class="p-3 bg-blue-50 rounded-lg">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Running Jobs -->
          <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm text-gray-500 mb-1">Running Jobs</p>
                <h3 class="text-3xl font-bold text-blue-600">{{ stats.running }}</h3>
                <div v-if="stats.running > 0" class="flex items-center gap-1 mt-2">
                  <span class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-pulse"></span>
                  <p class="text-xs text-blue-600">Active now</p>
                </div>
                <p v-else class="text-xs text-gray-400 mt-2">None active</p>
              </div>
              <div class="p-3 bg-blue-50 rounded-lg">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Completed Jobs -->
          <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm text-gray-500 mb-1">Completed Jobs</p>
                <h3 class="text-3xl font-bold text-green-600">{{ stats.completed }}</h3>
                <p class="text-xs text-gray-400 mt-2">Successfully finished</p>
              </div>
              <div class="p-3 bg-green-50 rounded-lg">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Failed Jobs -->
          <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm text-gray-500 mb-1">Failed Jobs</p>
                <h3 class="text-3xl font-bold text-red-600">{{ stats.failed }}</h3>
                <p class="text-xs text-gray-400 mt-2">Need attention</p>
              </div>
              <div class="p-3 bg-red-50 rounded-lg">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Jobs Table + Activity Timeline -->
        <div class="grid grid-cols-3 gap-6 mb-8">

          <!-- Recent Jobs Table (2 cols) -->
          <div class="col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
              <div class="flex items-center gap-3">
                <h3 class="text-lg font-semibold text-gray-900">Recent Jobs</h3>
                <div v-if="stats.running > 0" class="flex items-center gap-1.5 px-2 py-0.5 bg-blue-50 border border-blue-200 rounded-full">
                  <span class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-pulse"></span>
                  <span class="text-xs font-medium text-blue-700">{{ stats.running }} running</span>
                </div>
              </div>
              <button @click="$router.push('/my-jobs')" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">View All Jobs →</button>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="flex items-center justify-center py-16">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            </div>

            <!-- Empty -->
            <div v-else-if="recentJobs.length === 0" class="text-center py-16">
              <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
              </svg>
              <p class="mt-3 text-sm text-gray-500">No jobs yet. Create your first job!</p>
              <button @click="showCreateModal = true" class="mt-4 px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">+ Create Job</button>
            </div>

            <!-- Table -->
            <div v-else class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr v-for="job in recentJobs" :key="job.id" class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center gap-3">
                        <div :class="['w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0', getJobIconBg(job.status)]">
                          <svg class="w-4 h-4" :class="getJobIconColor(job.status)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                          </svg>
                        </div>
                        <div>
                          <p class="text-sm font-medium text-gray-900">{{ job.name }}</p>
                          <p class="text-xs text-gray-400">#{{ job.id }}</p>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ job.type || 'General' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="['px-2.5 py-1 inline-flex text-xs font-semibold rounded-full', getStatusClass(job.status)]">
                        {{ job.status.charAt(0).toUpperCase() + job.status.slice(1) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center gap-2">
                        <div class="w-20 bg-gray-200 rounded-full h-1.5">
                          <div :class="getProgressColor(job.status)" class="h-1.5 rounded-full transition-all" :style="{ width: getProgress(job) + '%' }"></div>
                        </div>
                        <span class="text-xs text-gray-600">{{ getProgress(job) }}%</span>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatTimeAgo(job.created_at) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center gap-2">
                        <button @click="$router.push('/jobs/' + job.id)" class="text-xs font-medium text-indigo-600 hover:text-indigo-800 px-2 py-1 rounded hover:bg-indigo-50">View</button>
                        <button @click="$router.push('/jobs/' + job.id + '/monitoring')" class="text-xs font-medium text-blue-600 hover:text-blue-800 px-2 py-1 rounded hover:bg-blue-50">Monitor</button>
                        <button v-if="job.status === 'failed'" @click="retryJob(job)" class="text-xs font-medium text-orange-600 hover:text-orange-800 px-2 py-1 rounded hover:bg-orange-50">Retry</button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Activity Timeline (1 col) -->
          <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between mb-5">
              <h3 class="text-lg font-semibold text-gray-900">Activity Timeline</h3>
              <button @click="$router.push('/notifications')" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">View All</button>
            </div>
            <div v-if="activityTimeline.length === 0" class="text-center py-8">
              <p class="text-sm text-gray-400">No recent activity</p>
            </div>
            <div v-else class="relative">
              <!-- Timeline line -->
              <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-100"></div>
              <div class="space-y-5">
                <div v-for="event in activityTimeline" :key="event.id" class="flex gap-4 relative">
                  <!-- Dot -->
                  <div :class="['w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 z-10 border-2 border-white', getTimelineDotBg(event.type)]">
                    <svg class="w-3.5 h-3.5" :class="getTimelineDotIcon(event.type)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path v-if="event.type === 'completed'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                      <path v-else-if="event.type === 'failed'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                      <path v-else-if="event.type === 'retry'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                      <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                  </div>
                  <!-- Content -->
                  <div class="flex-1 min-w-0 pb-1">
                    <p class="text-sm font-medium text-gray-900 leading-snug">{{ event.title }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ event.description }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ event.time }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-5">Quick Actions</h3>
          <div class="grid grid-cols-2 gap-4">
            <button @click="showCreateModal = true" class="flex items-center gap-4 p-4 border-2 border-dashed border-indigo-200 rounded-xl hover:border-indigo-400 hover:bg-indigo-50 transition-all group">
              <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center group-hover:bg-indigo-200 transition-colors">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
              </div>
              <div class="text-left">
                <p class="text-sm font-semibold text-gray-900">+ Create Job</p>
                <p class="text-xs text-gray-500">Submit a new distributed task</p>
              </div>
            </button>

            <button @click="$router.push('/my-jobs')" class="flex items-center gap-4 p-4 border border-gray-200 rounded-xl hover:border-gray-300 hover:bg-gray-50 transition-all group">
              <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center group-hover:bg-orange-200 transition-colors">
                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </div>
              <div class="text-left">
                <p class="text-sm font-semibold text-gray-900">View All Jobs</p>
                <p class="text-xs text-gray-500">Manage all your submitted jobs</p>
              </div>
            </button>
          </div>
        </div>

      </main>
    </div>

    <!-- Create Job Modal -->
    <CreateJobModal v-if="showCreateModal" @close="showCreateModal = false" @created="handleJobCreated" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import UserSidebar from '@/components/UserSidebar.vue'
import CreateJobModal from '@/components/modals/CreateJobModal.vue'
import api from '@/api'

const authStore = useAuthStore()
const router = useRouter()

const user = computed(() => authStore.user)
const loading = ref(false)
const showCreateModal = ref(false)
let refreshTimer = null

const stats = ref({ totalJobs: 0, running: 0, completed: 0, failed: 0, pending: 0 })
const recentJobs = ref([])
const activityTimeline = ref([])

function getUserInitials() {
  if (!user.value?.name) return 'U'
  const names = user.value.name.split(' ')
  if (names.length >= 2) return names[0][0].toUpperCase() + names[1][0].toUpperCase()
  return names[0][0].toUpperCase()
}

async function loadData() {
  loading.value = true
  try {
    const res = await api.get('/jobs', { params: { sort_by: 'created_at', sort_order: 'desc', per_page: 100 } })
    const allJobs = res.data.data || []

    stats.value = {
      totalJobs: allJobs.length,
      running: allJobs.filter(j => j.status === 'running').length,
      completed: allJobs.filter(j => j.status === 'completed').length,
      failed: allJobs.filter(j => j.status === 'failed').length,
      pending: allJobs.filter(j => j.status === 'pending').length,
    }

    recentJobs.value = allJobs.slice(0, 8)

    // Build activity timeline from recent jobs
    activityTimeline.value = allJobs.slice(0, 6).map(job => ({
      id: job.id,
      type: job.status === 'completed' ? 'completed' : job.status === 'failed' ? 'failed' : 'running',
      title: job.status === 'completed'
        ? `${job.name} completed`
        : job.status === 'failed'
        ? `${job.name} failed`
        : `${job.name} is running`,
      description: `${job.completed_tasks || 0} / ${job.total_tasks || 0} tasks`,
      time: formatTimeAgo(job.created_at),
    }))
  } catch (err) {
    console.error('Dashboard load error:', err)
  } finally {
    loading.value = false
  }
}

async function retryJob(job) {
  try {
    await api.post(`/jobs/${job.id}/retry`)
    await loadData()
  } catch (err) {
    console.error('Retry failed:', err)
  }
}

function handleJobCreated(newJob) {
  showCreateModal.value = false
  if (newJob?.id) router.push(`/jobs/${newJob.id}`)
  else loadData()
}

function getProgress(job) {
  if (!job.total_tasks || job.total_tasks === 0) return job.status === 'completed' ? 100 : 0
  return Math.round((job.completed_tasks / job.total_tasks) * 100)
}

function getStatusClass(status) {
  return {
    completed: 'bg-green-100 text-green-800',
    running: 'bg-blue-100 text-blue-800',
    failed: 'bg-red-100 text-red-800',
    pending: 'bg-yellow-100 text-yellow-800',
    cancelled: 'bg-gray-100 text-gray-800',
  }[status] || 'bg-gray-100 text-gray-800'
}

function getProgressColor(status) {
  return { completed: 'bg-green-500', running: 'bg-blue-500', failed: 'bg-red-500', pending: 'bg-yellow-400' }[status] || 'bg-gray-400'
}

function getJobIconBg(status) {
  return { completed: 'bg-green-100', running: 'bg-blue-100', failed: 'bg-red-100', pending: 'bg-yellow-100' }[status] || 'bg-gray-100'
}

function getJobIconColor(status) {
  return { completed: 'text-green-600', running: 'text-blue-600', failed: 'text-red-600', pending: 'text-yellow-600' }[status] || 'text-gray-600'
}

function getTimelineDotBg(type) {
  return { completed: 'bg-green-100', failed: 'bg-red-100', retry: 'bg-orange-100', running: 'bg-blue-100' }[type] || 'bg-gray-100'
}

function getTimelineDotIcon(type) {
  return { completed: 'text-green-600', failed: 'text-red-600', retry: 'text-orange-600', running: 'text-blue-600' }[type] || 'text-gray-600'
}

function formatTimeAgo(dateString) {
  if (!dateString) return 'N/A'
  const diff = Date.now() - new Date(dateString).getTime()
  const m = Math.floor(diff / 60000)
  const h = Math.floor(diff / 3600000)
  const d = Math.floor(diff / 86400000)
  if (m < 1) return 'Just now'
  if (m < 60) return `${m}m ago`
  if (h < 24) return `${h}h ago`
  if (d < 7) return `${d}d ago`
  return new Date(dateString).toLocaleDateString()
}

function scheduleRefresh() {
  const hasRunning = stats.value.running > 0
  refreshTimer = setTimeout(async () => {
    await loadData()
    scheduleRefresh()
  }, hasRunning ? 10000 : 60000)
}

onMounted(() => {
  loadData().then(() => scheduleRefresh())
})

onUnmounted(() => {
  if (refreshTimer) clearTimeout(refreshTimer)
})
</script>
