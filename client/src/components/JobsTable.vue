<template>
  <div class="card">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold text-gray-900">Jobs</h2>
      
      <!-- Filter by status -->
      <div class="flex items-center space-x-3">
        <label class="text-sm font-medium text-gray-700">Filter:</label>
        <select
          v-model="filterStatus"
          class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
        >
          <option value="">All Statuses</option>
          <option value="pending">Pending</option>
          <option value="running">Running</option>
          <option value="completed">Completed</option>
          <option value="failed">Failed</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      <p class="mt-4 text-gray-600">Loading jobs...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="filteredJobs.length === 0" class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No jobs found</h3>
      <p class="mt-1 text-sm text-gray-500">
        {{ filterStatus ? 'No jobs match the selected filter.' : 'Get started by submitting a new job.' }}
      </p>
    </div>

    <!-- Jobs Table -->
    <div v-else class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" @click="sortBy('id')">
              ID
              <span v-if="sortField === 'id'">{{ sortDirection === 'asc' ? '↑' : '↓' }}</span>
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" @click="sortBy('name')">
              Name
              <span v-if="sortField === 'name'">{{ sortDirection === 'asc' ? '↑' : '↓' }}</span>
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Type
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Status
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Progress
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" @click="sortBy('priority')">
              Priority
              <span v-if="sortField === 'priority'">{{ sortDirection === 'asc' ? '↑' : '↓' }}</span>
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" @click="sortBy('created_at')">
              Created
              <span v-if="sortField === 'created_at'">{{ sortDirection === 'asc' ? '↑' : '↓' }}</span>
            </th>
            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr
            v-for="job in sortedJobs"
            :key="job.id"
            class="hover:bg-gray-50 cursor-pointer transition-colors"
            @click="viewJob(job.id)"
          >
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
              #{{ job.id }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">{{ job.name }}</div>
              <div v-if="job.description" class="text-sm text-gray-500 truncate max-w-xs">
                {{ job.description }}
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ formatJobType(job.type) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <StatusBadge :status="job.status" />
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center space-x-3">
                <div class="flex-1 bg-gray-200 rounded-full h-2 max-w-[120px]">
                  <div
                    class="h-2 rounded-full transition-all duration-300"
                    :class="getProgressBarColor(job.status)"
                    :style="{ width: `${getProgress(job)}%` }"
                  ></div>
                </div>
                <span class="text-sm font-medium text-gray-700 min-w-[60px]">
                  {{ getProgress(job) }}%
                </span>
              </div>
              <div class="text-xs text-gray-500 mt-1">
                {{ job.completed_tasks || 0 }} / {{ job.total_tasks || 0 }} tasks
                <span v-if="job.failed_tasks > 0" class="text-red-600">
                  ({{ job.failed_tasks }} failed)
                </span>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                {{ job.priority || 5 }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ formatDate(job.created_at) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <button
                v-if="job.status === 'pending' || job.status === 'running'"
                @click.stop="cancelJob(job.id)"
                class="text-red-600 hover:text-red-900 transition-colors"
              >
                Cancel
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import StatusBadge from './StatusBadge.vue'

const props = defineProps({
  jobs: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['cancel-job'])

const router = useRouter()

const filterStatus = ref('')
const sortField = ref('created_at')
const sortDirection = ref('desc')

const filteredJobs = computed(() => {
  if (!filterStatus.value) return props.jobs
  return props.jobs.filter(job => job.status === filterStatus.value)
})

const sortedJobs = computed(() => {
  const jobs = [...filteredJobs.value]
  
  jobs.sort((a, b) => {
    let aVal = a[sortField.value]
    let bVal = b[sortField.value]
    
    // Handle null/undefined
    if (aVal == null) return 1
    if (bVal == null) return -1
    
    // Handle dates
    if (sortField.value.includes('_at')) {
      aVal = new Date(aVal).getTime()
      bVal = new Date(bVal).getTime()
    }
    
    // Handle strings
    if (typeof aVal === 'string') {
      aVal = aVal.toLowerCase()
      bVal = bVal.toLowerCase()
    }
    
    if (sortDirection.value === 'asc') {
      return aVal > bVal ? 1 : -1
    } else {
      return aVal < bVal ? 1 : -1
    }
  })
  
  return jobs
})

function sortBy(field) {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortField.value = field
    sortDirection.value = 'desc'
  }
}

function getProgress(job) {
  if (!job.total_tasks || job.total_tasks === 0) return 0
  return Math.round((job.completed_tasks / job.total_tasks) * 100)
}

function getProgressBarColor(status) {
  switch (status) {
    case 'completed':
      return 'bg-green-500'
    case 'running':
      return 'bg-blue-500'
    case 'failed':
      return 'bg-red-500'
    case 'cancelled':
      return 'bg-gray-400'
    default:
      return 'bg-yellow-500'
  }
}

function formatJobType(type) {
  return type
    .split('_')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ')
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

function viewJob(jobId) {
  router.push(`/jobs/${jobId}`)
}

function cancelJob(jobId) {
  if (confirm('Are you sure you want to cancel this job?')) {
    emit('cancel-job', jobId)
  }
}
</script>
