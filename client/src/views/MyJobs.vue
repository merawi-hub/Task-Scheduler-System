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
              <h1 class="text-2xl font-bold text-gray-900">My Jobs</h1>
              <p class="text-sm text-gray-500 mt-1">Manage and monitor all your submitted jobs</p>
            </div>
            <div class="flex items-center gap-3">
              <!-- Filter Dropdown -->
              <select v-model="filterStatus" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="running">Running</option>
                <option value="completed">Completed</option>
                <option value="failed">Failed</option>
                <option value="cancelled">Cancelled</option>
              </select>

              <!-- Refresh Button -->
              <button @click="refreshJobs" class="flex items-center gap-2 px-4 py-2 text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span class="text-sm font-medium">Refresh</span>
              </button>

              <!-- Create Job Button -->
              <button @click="showCreateModal = true" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="text-sm font-medium">Create Job</span>
              </button>
            </div>
          </div>
        </div>
      </header>

      <!-- Main Content -->
      <main class="p-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
          <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-500 mb-1">Total Jobs</p>
                <h3 class="text-2xl font-bold text-gray-900">{{ jobStats.total }}</h3>
              </div>
              <div class="p-3 bg-blue-50 rounded-lg">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-500 mb-1">Completed</p>
                <h3 class="text-2xl font-bold text-green-600">{{ jobStats.completed }}</h3>
              </div>
              <div class="p-3 bg-green-50 rounded-lg">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-500 mb-1">Running</p>
                <h3 class="text-2xl font-bold text-blue-600">{{ jobStats.running }}</h3>
              </div>
              <div class="p-3 bg-blue-50 rounded-lg">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-500 mb-1">Pending</p>
                <h3 class="text-2xl font-bold text-yellow-600">{{ jobStats.pending }}</h3>
              </div>
              <div class="p-3 bg-yellow-50 rounded-lg">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-500 mb-1">Failed</p>
                <h3 class="text-2xl font-bold text-red-600">{{ jobStats.failed }}</h3>
              </div>
              <div class="p-3 bg-red-50 rounded-lg">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Jobs Table -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
          <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900">All Jobs</h3>
              <div class="flex items-center gap-3">
                <div class="relative">
                  <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search jobs..."
                    class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  />
                  <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </div>
              </div>
            </div>
          </div>

          <!-- Loading State -->
          <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            <p class="text-gray-600 mt-4">Loading jobs...</p>
          </div>

          <!-- Error State -->
          <div v-else-if="error" class="p-6">
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
              {{ error }}
            </div>
          </div>

          <!-- Empty State -->
          <div v-else-if="filteredJobs.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No jobs found</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by creating a new job.</p>
            <button @click="showCreateModal = true" class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
              Create Job
            </button>
          </div>

          <!-- Jobs Table -->
          <div v-else class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tasks</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="job in filteredJobs" :key="job.id" class="hover:bg-gray-50 cursor-pointer" @click="viewJob(job)">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-900">{{ job.name }}</p>
                        <p class="text-xs text-gray-500">#{{ job.id }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ job.type || 'General' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="[
                      'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full',
                      getStatusClass(job.status)
                    ]">
                      {{ job.status.toUpperCase() }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-2">
                      <div class="flex-1 bg-gray-200 rounded-full h-2 max-w-[100px]">
                        <div :class="getProgressColor(job.status)" class="h-2 rounded-full transition-all" :style="{ width: getProgress(job) + '%' }"></div>
                      </div>
                      <span class="text-xs font-medium text-gray-600">{{ getProgress(job) }}%</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ job.completed_tasks || 0 }} / {{ job.total_tasks || 0 }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(job.created_at) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <div class="flex items-center gap-2">
                      <button @click.stop="viewJob(job)" class="text-indigo-600 hover:text-indigo-900 font-medium">
                        View
                      </button>
                      <button v-if="job.status === 'running' || job.status === 'pending'" @click.stop="cancelJob(job)" class="text-red-600 hover:text-red-900 font-medium">
                        Cancel
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </main>
    </div>

    <!-- Create Job Modal -->
    <CreateJobModal v-if="showCreateModal" @close="showCreateModal = false" @created="handleJobCreated" />

    <!-- Job Detail Modal -->
    <JobDetailModal v-if="selectedJob" :job="selectedJob" @close="selectedJob = null" @updated="refreshJobs" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useJobsStore } from '@/stores/jobsStore'
import UserSidebar from '@/components/UserSidebar.vue'
import CreateJobModal from '@/components/modals/CreateJobModal.vue'
import JobDetailModal from '@/components/modals/JobDetailModal.vue'

const jobsStore = useJobsStore()

const jobs = ref([])
const loading = ref(false)
const error = ref(null)
const filterStatus = ref('')
const searchQuery = ref('')
const showCreateModal = ref(false)
const selectedJob = ref(null)

const jobStats = computed(() => {
  return {
    total: jobs.value.length,
    completed: jobs.value.filter(j => j.status === 'completed').length,
    running: jobs.value.filter(j => j.status === 'running').length,
    pending: jobs.value.filter(j => j.status === 'pending').length,
    failed: jobs.value.filter(j => j.status === 'failed').length
  }
})

const filteredJobs = computed(() => {
  let filtered = jobs.value

  if (filterStatus.value) {
    filtered = filtered.filter(j => j.status === filterStatus.value)
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(j => 
      j.name.toLowerCase().includes(query) ||
      j.id.toString().includes(query)
    )
  }

  return filtered
})

async function refreshJobs() {
  loading.value = true
  error.value = null
  
  try {
    await jobsStore.fetchJobs()
    jobs.value = jobsStore.jobs
  } catch (err) {
    error.value = 'Failed to load jobs'
  } finally {
    loading.value = false
  }
}

function handleJobCreated() {
  showCreateModal.value = false
  refreshJobs()
}

function viewJob(job) {
  selectedJob.value = job
}

async function cancelJob(job) {
  if (confirm(`Are you sure you want to cancel "${job.name}"?`)) {
    try {
      await jobsStore.cancelJob(job.id)
      refreshJobs()
    } catch (err) {
      alert('Failed to cancel job')
    }
  }
}

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

function getProgress(job) {
  if (!job.total_tasks || job.total_tasks === 0) return 0
  return Math.round((job.completed_tasks / job.total_tasks) * 100)
}

function formatDate(dateString) {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now - date
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)
  
  if (diffMins < 1) return 'Just now'
  if (diffMins < 60) return `${diffMins}m ago`
  if (diffHours < 24) return `${diffHours}h ago`
  if (diffDays < 7) return `${diffDays}d ago`
  
  return date.toLocaleDateString()
}

onMounted(() => {
  refreshJobs()
})
</script>
