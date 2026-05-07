<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <button
              @click="goBack"
              class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
            >
              <svg class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
            </button>
            <div>
              <h1 class="text-2xl font-bold text-gray-900">Job Details</h1>
              <p class="text-sm text-gray-500 mt-1">View job progress and task details</p>
            </div>
          </div>
          <div class="flex items-center space-x-3">
            <div class="flex items-center space-x-2 text-sm">
              <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
              <span class="text-gray-600">Auto-refresh: 5s</span>
            </div>
            <button
              @click="refresh"
              class="btn btn-secondary flex items-center space-x-2"
              :disabled="loading"
            >
              <svg
                class="w-4 h-4"
                :class="{ 'animate-spin': loading }"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              <span>Refresh</span>
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Loading State -->
      <div v-if="initialLoading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600"></div>
        <p class="mt-4 text-gray-600">Loading job details...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="card">
        <div class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Error Loading Job</h3>
          <p class="mt-1 text-sm text-red-600">{{ error }}</p>
          <div class="mt-6">
            <button @click="goBack" class="btn btn-primary">
              Go Back to Dashboard
            </button>
          </div>
        </div>
      </div>

      <!-- Job Detail Component -->
      <div v-else-if="job">
        <JobDetail
          :job="job"
          :tasks="tasks"
          :tasks-loading="tasksLoading"
          @cancel-job="handleCancelJob"
        />
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useJobsStore } from '@/stores/jobsStore'
import JobDetail from '@/components/JobDetail.vue'

const route = useRoute()
const router = useRouter()
const jobsStore = useJobsStore()

const job = ref(null)
const tasks = ref([])
const initialLoading = ref(true)
const loading = ref(false)
const tasksLoading = ref(false)
const error = ref(null)

let pollingInterval = null

const jobId = computed(() => route.params.id)

onMounted(async () => {
  await loadJobData()
  
  // Start polling every 5 seconds
  pollingInterval = setInterval(async () => {
    await loadJobData(true)
  }, 5000)
})

onUnmounted(() => {
  if (pollingInterval) {
    clearInterval(pollingInterval)
  }
})

async function loadJobData(silent = false) {
  if (!silent) {
    loading.value = true
  }
  
  error.value = null
  
  try {
    // Fetch job details
    const jobData = await jobsStore.fetchJob(jobId.value)
    job.value = jobData
    
    // Fetch tasks for this job
    tasksLoading.value = true
    const tasksData = await jobsStore.fetchJobTasks(jobId.value)
    tasks.value = Array.isArray(tasksData) ? tasksData : (tasksData.data || [])
    
  } catch (err) {
    console.error('Error loading job data:', err)
    error.value = err.response?.data?.message || 'Failed to load job details'
  } finally {
    initialLoading.value = false
    loading.value = false
    tasksLoading.value = false
  }
}

async function refresh() {
  await loadJobData()
}

async function handleCancelJob(jobId) {
  if (!confirm('Are you sure you want to cancel this job?')) {
    return
  }
  
  try {
    await jobsStore.cancelJob(jobId)
    await loadJobData(true)
  } catch (error) {
    console.error('Error cancelling job:', error)
    alert('Failed to cancel job. Please try again.')
  }
}

function goBack() {
  router.push('/')
}
</script>
