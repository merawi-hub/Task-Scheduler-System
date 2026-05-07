<template>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-4">
    <!-- Total Jobs -->
    <div class="card hover:shadow-lg transition-shadow">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600">Total Jobs</p>
          <p class="text-3xl font-bold text-gray-900 mt-1">
            {{ metrics.total_jobs || 0 }}
          </p>
        </div>
        <div class="p-3 bg-blue-100 rounded-full">
          <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
        </div>
      </div>
    </div>

    <!-- Total Tasks -->
    <div class="card hover:shadow-lg transition-shadow">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600">Total Tasks</p>
          <p class="text-3xl font-bold text-gray-900 mt-1">
            {{ metrics.total_tasks || 0 }}
          </p>
        </div>
        <div class="p-3 bg-purple-100 rounded-full">
          <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
          </svg>
        </div>
      </div>
    </div>

    <!-- Completed Tasks -->
    <div class="card hover:shadow-lg transition-shadow">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600">Completed</p>
          <p class="text-3xl font-bold text-green-600 mt-1">
            {{ metrics.completed_tasks || 0 }}
          </p>
          <p class="text-xs text-gray-500 mt-1">
            {{ completionRate }}% complete
          </p>
        </div>
        <div class="p-3 bg-green-100 rounded-full">
          <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
      </div>
    </div>

    <!-- Failed Tasks -->
    <div class="card hover:shadow-lg transition-shadow">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600">Failed</p>
          <p class="text-3xl font-bold text-red-600 mt-1">
            {{ metrics.failed_tasks || 0 }}
          </p>
          <p class="text-xs text-gray-500 mt-1">
            {{ failureRate }}% failure rate
          </p>
        </div>
        <div class="p-3 bg-red-100 rounded-full">
          <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
      </div>
    </div>

    <!-- Active Workers -->
    <div class="card hover:shadow-lg transition-shadow">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600">Active Workers</p>
          <p class="text-3xl font-bold text-blue-600 mt-1">
            {{ metrics.active_workers || 0 }}
          </p>
          <p class="text-xs text-gray-500 mt-1">
            of {{ metrics.total_workers || 0 }} total
          </p>
        </div>
        <div class="p-3 bg-indigo-100 rounded-full">
          <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
        </div>
      </div>
    </div>

    <!-- Throughput -->
    <div class="card hover:shadow-lg transition-shadow">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600">Throughput</p>
          <p class="text-3xl font-bold text-orange-600 mt-1">
            {{ formatThroughput(metrics.tasks_per_second) }}
          </p>
          <p class="text-xs text-gray-500 mt-1">
            tasks/sec
          </p>
        </div>
        <div class="p-3 bg-orange-100 rounded-full">
          <svg class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
        </div>
      </div>
    </div>
  </div>

  <!-- Additional Metrics Row -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
    <!-- Average Task Duration -->
    <div class="card">
      <div class="flex items-center space-x-3">
        <div class="p-2 bg-teal-100 rounded">
          <svg class="w-5 h-5 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div>
          <p class="text-sm font-medium text-gray-600">Avg Duration</p>
          <p class="text-xl font-bold text-gray-900">
            {{ formatDuration(metrics.average_task_duration) }}
          </p>
        </div>
      </div>
    </div>

    <!-- Worker Utilization -->
    <div class="card">
      <div class="flex items-center space-x-3">
        <div class="p-2 bg-cyan-100 rounded">
          <svg class="w-5 h-5 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
        </div>
        <div>
          <p class="text-sm font-medium text-gray-600">Worker Utilization</p>
          <p class="text-xl font-bold text-gray-900">
            {{ metrics.worker_utilization || 0 }}%
          </p>
        </div>
      </div>
    </div>

    <!-- Total Retries -->
    <div class="card">
      <div class="flex items-center space-x-3">
        <div class="p-2 bg-amber-100 rounded">
          <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
        </div>
        <div>
          <p class="text-sm font-medium text-gray-600">Total Retries</p>
          <p class="text-xl font-bold text-gray-900">
            {{ metrics.total_retries || 0 }}
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- System Health Indicator -->
  <div class="card mt-4">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm font-medium text-gray-600 mb-2">System Health</p>
        <div class="flex items-center space-x-2">
          <div
            class="w-3 h-3 rounded-full"
            :class="healthIndicatorClass"
          ></div>
          <span class="text-lg font-semibold" :class="healthTextClass">
            {{ healthStatus }}
          </span>
        </div>
      </div>
      <div class="text-right">
        <p class="text-sm text-gray-500">Running Tasks</p>
        <p class="text-2xl font-bold text-blue-600">
          {{ metrics.running_tasks || 0 }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  metrics: {
    type: Object,
    required: true
  }
})

const completionRate = computed(() => {
  const total = props.metrics.total_tasks || 0
  const completed = props.metrics.completed_tasks || 0
  if (total === 0) return 0
  return Math.round((completed / total) * 100)
})

const failureRate = computed(() => {
  const total = props.metrics.total_tasks || 0
  const failed = props.metrics.failed_tasks || 0
  if (total === 0) return 0
  return Math.round((failed / total) * 100)
})

const healthStatus = computed(() => {
  const rate = completionRate.value
  const failures = failureRate.value
  
  if (rate >= 90 && failures < 5) return 'Excellent'
  if (rate >= 75 && failures < 10) return 'Good'
  if (rate >= 50 && failures < 20) return 'Fair'
  return 'Poor'
})

const healthIndicatorClass = computed(() => {
  const status = healthStatus.value
  if (status === 'Excellent') return 'bg-green-500 animate-pulse'
  if (status === 'Good') return 'bg-blue-500'
  if (status === 'Fair') return 'bg-yellow-500'
  return 'bg-red-500'
})

const healthTextClass = computed(() => {
  const status = healthStatus.value
  if (status === 'Excellent') return 'text-green-600'
  if (status === 'Good') return 'text-blue-600'
  if (status === 'Fair') return 'text-yellow-600'
  return 'text-red-600'
})

function formatThroughput(value) {
  if (!value) return '0.0'
  return Number(value).toFixed(1)
}

function formatDuration(seconds) {
  if (!seconds || seconds === 0) return '0s'
  
  if (seconds < 60) return `${Math.round(seconds)}s`
  
  const minutes = Math.floor(seconds / 60)
  const remainingSeconds = Math.round(seconds % 60)
  
  if (minutes < 60) return `${minutes}m ${remainingSeconds}s`
  
  const hours = Math.floor(minutes / 60)
  const remainingMinutes = minutes % 60
  
  return `${hours}h ${remainingMinutes}m`
}
</script>
