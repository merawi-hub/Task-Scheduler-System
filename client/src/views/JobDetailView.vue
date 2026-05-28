<template>
  <div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <UserSidebar />

    <!-- Main Content -->
    <div class="flex-1 ml-64 overflow-auto">
      <!-- Header -->
      <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="px-8 py-4">
          <div class="flex items-center gap-3">
            <router-link
              to="/my-jobs"
              class="flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-700 font-medium transition-colors"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
              </svg>
              Back to My Jobs
            </router-link>
            <span class="text-gray-300">/</span>
            <h1 class="text-lg font-semibold text-gray-900">Job Details</h1>
          </div>
        </div>
      </header>

      <!-- Main Content -->
      <main class="p-8">
        <!-- Loading State -->
        <div v-if="initialLoading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-16 w-16 border-b-2 border-indigo-600"></div>
          <p class="mt-4 text-gray-600">Loading job details...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="max-w-2xl mx-auto">
          <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8">
            <div class="text-center">
              <div class="mx-auto w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mb-4">
                <svg class="w-10 h-10 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <h3 class="text-2xl font-bold text-gray-900 mb-2">Error Loading Job</h3>
              <p class="text-gray-600 mb-6">{{ error }}</p>
              <div class="flex items-center justify-center gap-3">
                <button @click="goToMyJobs" class="px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-all font-semibold">
                  Go to My Jobs
                </button>
                <button @click="goBack" class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-all font-semibold">
                  Go Back
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Job Detail Component -->
        <div v-else-if="job">
          <!-- ── COMPLETED / FAILED — show completion banner first ─────────── -->
          <JobCompletionBanner
            v-if="job.status === 'completed' || job.status === 'failed'"
            :job-id="job.id"
            class="mb-6"
            @view-tasks="scrollToTasks"
            @submit-another="router.push('/submit-job')"
            @go-to-jobs="router.push('/my-jobs')"
          />

          <!-- Worker Activity Panel — shown when job is pending or running -->
          <WorkerActivityPanel
            v-if="job.status === 'pending' || job.status === 'running'"
            :job-id="job.id"
            class="mb-6"
          />

          <!-- Load Balance Panel — shown when job is running -->
          <LoadBalancePanel
            v-if="job.status === 'running'"
            class="mb-6"
          />

          <JobDetail
            ref="jobDetailRef"
            :job="job"
            :tasks="tasks"
            :tasks-loading="tasksLoading"
            :tasks-error="tasksError"
            :refreshing="loading"
            @cancel-job="handleCancelJob"
            @refresh="refresh"
          />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useJobsStore } from '@/stores/jobsStore'
import apiClient from '@/api/axios'
import JobDetail from '@/components/JobDetail.vue'
import UserSidebar from '@/components/UserSidebar.vue'
import WorkerActivityPanel from '@/components/WorkerActivityPanel.vue'
import LoadBalancePanel from '@/components/LoadBalancePanel.vue'
import JobCompletionBanner from '@/components/JobCompletionBanner.vue'

const route = useRoute()
const router = useRouter()
const jobsStore = useJobsStore()

const job = ref(null)
const tasks = ref([])
const taskStatusCounts = ref(null)
const initialLoading = ref(true)
const loading = ref(false)
const tasksLoading = ref(false)
const tasksError = ref(null)
const error = ref(null)
const jobDetailRef = ref(null)

function scrollToTasks() {
  if (jobDetailRef.value) {
    jobDetailRef.value.activeTab = 'tasks'
  }
}

let pollingInterval = null

const jobId = computed(() => {
  const id = route.params.id;
  // Validate ID is defined and numeric and positive
  if (!id || id === 'undefined' || id === 'null' || isNaN(Number(id)) || Number(id) <= 0) {
    return null;
  }
  return Number(id);
})

onMounted(async () => {
  await loadJobData()

  // Adaptive polling:
  // - 2s when running/pending (fast updates)
  // - Stop entirely when terminal (completed/failed/cancelled)
  function scheduleNext() {
    const status = job.value?.status
    if (!status || ['completed', 'failed', 'cancelled'].includes(status)) {
      // Job is done — no more polling needed
      return
    }
    const interval = (status === 'running' || status === 'pending') ? 2000 : 5000
    pollingInterval = setTimeout(async () => {
      await loadJobData(true)
      scheduleNext()
    }, interval)
  }
  scheduleNext()
})

onUnmounted(() => {
  if (pollingInterval) {
    clearTimeout(pollingInterval)
  }
})

async function loadJobData(silent = false) {
  // Validate job ID before making API calls
  if (!jobId.value) {
    error.value = 'Invalid job ID. Please select a valid job.';
    initialLoading.value = false;
    loading.value = false;
    job.value = null;
    tasks.value = [];
    return;
  }

  if (!silent) {
    loading.value = true
  }

  error.value = null

  try {
    // Fetch job details
    const jobData = await jobsStore.fetchJob(jobId.value)
    job.value = jobData
  } catch (err) {
    console.error('Error loading job data:', err)
    error.value = err.response?.data?.message || 'Failed to load job details'
    job.value = null
  } finally {
    initialLoading.value = false
    loading.value = false
  }

  if (!job.value) {
    tasks.value = []
    tasksError.value = null
    return
  }

  // Fetch tasks for this job
  tasksLoading.value = true
  tasksError.value = null
  try {
    const response = await apiClient.get(`/jobs/${jobId.value}/tasks`)
    tasks.value = response.data.tasks || []
    taskStatusCounts.value = response.data.status_counts || null
  } catch (err) {
    console.error('Error loading job tasks:', err)
    tasksError.value = err.response?.data?.message || 'Failed to load job tasks'
    tasks.value = []
  } finally {
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
  router.push('/dashboard')
}

function goToMyJobs() {
  router.push('/my-jobs')
}
</script>
