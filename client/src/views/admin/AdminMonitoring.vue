<template>
  <div class="flex-1 overflow-auto">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
      <div class="px-8 py-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Monitoring</h1>
            <p class="text-sm text-gray-500 mt-1">System metrics and real-time performance</p>
          </div>
          <button
            @click="refreshData"
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
      <!-- KPI Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <p class="text-sm font-medium text-gray-500">Total Jobs</p>
          <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ kpis.totalJobs }}</h3>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <p class="text-sm font-medium text-gray-500">Active Workers</p>
          <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ kpis.activeWorkers }}</h3>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <p class="text-sm font-medium text-gray-500">Failed Tasks</p>
          <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ kpis.failedTasks }}</h3>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <p class="text-sm font-medium text-gray-500">Throughput/sec</p>
          <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ kpis.throughput }}</h3>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <p class="text-sm font-medium text-gray-500">Queue Size</p>
          <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ kpis.queueSize }}</h3>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <p class="text-sm font-medium text-gray-500">Success Rate</p>
          <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ kpis.successRate }}%</h3>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <h3 class="text-lg font-semibold text-gray-900 mb-6">Throughput</h3>
          <LineChart :data="throughputChartData" />
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <h3 class="text-lg font-semibold text-gray-900 mb-6">Active Workers</h3>
          <LineChart :data="workerChartData" />
        </div>
      </div>

      <!-- Bottom Row -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <h3 class="text-lg font-semibold text-gray-900 mb-6">Task Status</h3>
          <DonutChart :data="taskStatusChartData" />
        </div>

        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Activity Feed</h3>
            <span class="text-xs text-gray-400">Latest events</span>
          </div>
          <div v-if="activityFeed.length === 0" class="text-sm text-gray-500">
            No activity recorded yet.
          </div>
          <div v-else class="space-y-4">
            <div v-for="item in activityFeed" :key="item.id" class="flex items-start gap-3">
              <div
                :class="[
                  'w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0',
                  item.level === 'error'
                    ? 'bg-red-100'
                    : item.level === 'warning'
                      ? 'bg-yellow-100'
                      : 'bg-green-100'
                ]"
              >
                <svg
                  class="w-4 h-4"
                  :class="[
                    item.level === 'error'
                      ? 'text-red-600'
                      : item.level === 'warning'
                        ? 'text-yellow-600'
                        : 'text-green-600'
                  ]"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ item.message }}</p>
                <p class="text-xs text-gray-500">
                  {{ item.job_name || 'Job #' + item.job_id }} · {{ item.worker_key || 'Unknown Worker' }}
                </p>
                <p class="text-xs text-gray-400 mt-1">{{ formatTime(item.timestamp) }}</p>
              </div>
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

const kpis = computed(() => {
  const metrics = adminStore.systemMetrics || {}
  // Support both flat and nested (realtime) structures
  const jobs    = metrics.jobs    || metrics.realtime?.jobs    || {}
  const tasks   = metrics.tasks   || metrics.realtime?.tasks   || {}
  const workers = metrics.workers || metrics.realtime?.workers || {}
  const perf    = metrics.performance || metrics.realtime?.performance || {}

  return {
    totalJobs:    jobs.total    || 0,
    activeWorkers: (workers.active) || (workers.idle || 0) + (workers.busy || 0),
    failedTasks:  tasks.failed  || 0,
    throughput:   perf.throughput_per_second || 0,
    queueSize:    tasks.queue_size || tasks.pending || 0,
    successRate:  jobs.success_rate || metrics.system?.success_rate || 0
  }
})

const throughputChartData = computed(() => {
  const history = adminStore.metricsHistory?.history
  if (!history?.throughput) {
    return { labels: [], datasets: [{ label: 'Throughput', data: [], borderColor: '#6366f1', backgroundColor: 'rgba(99, 102, 241, 0.1)', fill: true }] }
  }

  return {
    labels: history.throughput.map(item => formatIntervalLabel(item.timestamp)),
    datasets: [
      {
        label: 'Throughput (tasks/sec)',
        data: history.throughput.map(item => item.throughput || 0),
        borderColor: '#6366f1',
        backgroundColor: 'rgba(99, 102, 241, 0.1)',
        tension: 0.4,
        fill: true
      }
    ]
  }
})

const workerChartData = computed(() => {
  const history = adminStore.metricsHistory?.history
  if (!history?.worker_count) {
    return { labels: [], datasets: [{ label: 'Workers', data: [], borderColor: '#10b981', backgroundColor: 'rgba(16, 185, 129, 0.1)', fill: true }] }
  }

  return {
    labels: history.worker_count.map(item => formatIntervalLabel(item.timestamp)),
    datasets: [
      {
        label: 'Active Workers',
        data: history.worker_count.map(item => item.count || 0),
        borderColor: '#10b981',
        backgroundColor: 'rgba(16, 185, 129, 0.1)',
        tension: 0.4,
        fill: true
      }
    ]
  }
})

const taskStatusChartData = computed(() => {
  const metrics = adminStore.systemMetrics
  const tasks = metrics?.tasks || metrics?.realtime?.tasks || {}
  return {
    labels: ['Completed', 'Running', 'Failed', 'Pending'],
    datasets: [{
      data: [tasks.done || tasks.completed || 0, tasks.running || 0, tasks.failed || 0, tasks.pending || 0],
      backgroundColor: ['#10b981', '#3b82f6', '#ef4444', '#6b7280'],
      borderWidth: 0
    }]
  }
})

const activityFeed = computed(() => adminStore.activityFeed.slice(0, 8))

function formatIntervalLabel(timestamp) {
  const date = new Date(timestamp)
  return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
}

function formatTime(timestamp) {
  const date = new Date(timestamp)
  return date.toLocaleString()
}

async function refreshData() {
  loading.value = true
  try {
    await Promise.all([
      adminStore.fetchSystemMetrics(),
      adminStore.fetchMetricsHistory('day'),
      adminStore.fetchActivityFeed(20)
    ])
  } catch (error) {
    console.error('Failed to refresh monitoring data:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  refreshData()
  refreshInterval.value = setInterval(refreshData, 30000)
})

onUnmounted(() => {
  if (refreshInterval.value) {
    clearInterval(refreshInterval.value)
  }
})
</script>
