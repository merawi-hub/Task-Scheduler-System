<template>
  <div class="flex-1 overflow-auto">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
      <div class="px-8 py-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">All Jobs</h1>
            <p class="text-sm text-gray-500 mt-1">Manage all jobs from all users</p>
          </div>
          <button
            @click="refreshJobs"
            class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
          >
            <svg class="w-5 h-5" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span class="text-sm font-medium">Refresh</span>
          </button>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="p-8">
      <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div v-if="loading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
          <p class="text-gray-600 mt-4">Loading jobs...</p>
        </div>

        <div v-else-if="error" class="p-6">
          <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            {{ error }}
          </div>
        </div>

        <div v-else-if="jobs.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
          </svg>
          <p class="text-gray-600 mt-4">No jobs found.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-100">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="job in jobs" :key="job.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ job.id }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ job.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                  <div>{{ job.user?.name || 'N/A' }}</div>
                  <div class="text-xs text-gray-400">{{ job.user?.email }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ job.type }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <StatusBadge :status="job.status" />
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-2">
                    <div class="flex-1 bg-gray-200 rounded-full h-2 max-w-[100px]">
                      <div :class="getProgressColor(job.status)" class="h-2 rounded-full transition-all" :style="{ width: calculateProgress(job) + '%' }"></div>
                    </div>
                    <span class="text-xs font-medium text-gray-600">{{ calculateProgress(job) }}%</span>
                  </div>
                  <div class="text-xs text-gray-500 mt-1">{{ job.completed_tasks }} / {{ job.total_tasks }} tasks</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                  <button
                    v-if="job.status === 'running' || job.status === 'pending'"
                    @click="cancelJob(job.id)"
                    class="text-yellow-600 hover:text-yellow-900 font-medium"
                  >
                    Cancel
                  </button>
                  <button
                    @click="deleteJob(job.id)"
                    class="text-red-600 hover:text-red-900 font-medium"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAdminStore } from '@/stores/adminStore'
import StatusBadge from '@/components/StatusBadge.vue'

const adminStore = useAdminStore()

const jobs = ref([])
const loading = ref(false)
const error = ref(null)

function calculateProgress(job) {
  if (job.total_tasks === 0) return 0
  return Math.round((job.completed_tasks / job.total_tasks) * 100)
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

async function refreshJobs() {
  loading.value = true
  error.value = null
  
  const result = await adminStore.fetchAllJobs()
  if (result.success) {
    jobs.value = result.data.data || result.data
  } else {
    error.value = result.error
  }
  
  loading.value = false
}

async function cancelJob(jobId) {
  if (!confirm('Are you sure you want to cancel this job?')) return
  
  const result = await adminStore.forceCancelJob(jobId)
  if (result.success) {
    alert('Job cancelled successfully')
    refreshJobs()
  } else {
    alert('Failed to cancel job: ' + result.error)
  }
}

async function deleteJob(jobId) {
  if (!confirm('Are you sure you want to permanently delete this job? This cannot be undone.')) return
  
  const result = await adminStore.deleteJob(jobId)
  if (result.success) {
    alert('Job deleted successfully')
    refreshJobs()
  } else {
    alert('Failed to delete job: ' + result.error)
  }
}

onMounted(() => {
  refreshJobs()
})
</script>
