<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Distributed Task Scheduler</h1>
            <p class="text-sm text-gray-500 mt-1">Real-time job and worker monitoring</p>
          </div>
          <div class="flex items-center space-x-3">
            <div class="flex items-center space-x-2 text-sm">
              <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
              <span class="text-gray-600">Live</span>
            </div>
            <button
              @click="refreshAll"
              class="btn btn-secondary flex items-center space-x-2"
              :disabled="refreshing"
            >
              <svg
                class="w-4 h-4"
                :class="{ 'animate-spin': refreshing }"
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
      <div class="space-y-8">
        <!-- Metrics Bar -->
        <section>
          <MetricsBar :metrics="metricsStore.metrics" />
        </section>

        <!-- Job Submission Form -->
        <section>
          <JobSubmitForm @job-submitted="handleJobSubmitted" />
        </section>

        <!-- Jobs Table -->
        <section>
          <JobsTable
            :jobs="jobsStore.jobs"
            :loading="jobsStore.loading"
            @cancel-job="handleCancelJob"
          />
        </section>

        <!-- Workers Grid -->
        <section>
          <WorkersGrid
            :workers="workersStore.workers"
            :loading="workersStore.loading"
          />
        </section>
      </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center justify-between text-sm text-gray-500">
          <p>© 2024 Distributed Task Scheduler. Built with Vue 3 + Laravel 11.</p>
          <p>Last updated: {{ lastUpdated }}</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useJobsStore } from '@/stores/jobsStore'
import { useWorkersStore } from '@/stores/workersStore'
import { useMetricsStore } from '@/stores/metricsStore'
import MetricsBar from '@/components/MetricsBar.vue'
import JobSubmitForm from '@/components/JobSubmitForm.vue'
import JobsTable from '@/components/JobsTable.vue'
import WorkersGrid from '@/components/WorkersGrid.vue'

const jobsStore = useJobsStore()
const workersStore = useWorkersStore()
const metricsStore = useMetricsStore()

const refreshing = ref(false)
const lastUpdated = ref('')
let pollingInterval = null

onMounted(async () => {
  // Initial data fetch
  await loadAllData()
  
  // Start polling every 5 seconds
  pollingInterval = setInterval(async () => {
    await loadAllData(true)
  }, 5000)
})

onUnmounted(() => {
  // Clear polling interval when component is destroyed
  if (pollingInterval) {
    clearInterval(pollingInterval)
  }
})

async function loadAllData(silent = false) {
  if (!silent) {
    refreshing.value = true
  }
  
  try {
    // Fetch all data in parallel
    await Promise.all([
      jobsStore.fetchJobs(),
      workersStore.fetchWorkers(),
      metricsStore.fetchMetrics()
    ])
    
    updateLastUpdated()
  } catch (error) {
    console.error('Error loading dashboard data:', error)
  } finally {
    if (!silent) {
      refreshing.value = false
    }
  }
}

async function refreshAll() {
  await loadAllData()
}

function handleJobSubmitted(job) {
  console.log('Job submitted:', job)
  // The job is already added to the store by the form component
  // We can optionally refresh to get the latest state
  setTimeout(() => {
    loadAllData(true)
  }, 1000)
}

async function handleCancelJob(jobId) {
  try {
    await jobsStore.cancelJob(jobId)
    // Refresh data after cancellation
    await loadAllData(true)
  } catch (error) {
    console.error('Error cancelling job:', error)
    alert('Failed to cancel job. Please try again.')
  }
}

function updateLastUpdated() {
  const now = new Date()
  lastUpdated.value = now.toLocaleTimeString()
}
</script>
