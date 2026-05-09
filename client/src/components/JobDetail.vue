<template>
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <!-- Page Header -->
    <div class="px-10 pt-7 pb-5 border-b border-gray-100">
      <div class="flex items-start justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Job Details</h1>
          <div class="flex items-center text-sm mt-2">
            <button @click="$router.push('/my-jobs')" class="text-gray-400 hover:text-[#5B5FED] transition-colors font-medium">
              Jobs
            </button>
            <svg class="w-4 h-4 mx-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-600 font-medium">{{ job.name }}</span>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <button
            type="button"
            class="p-2 rounded-lg border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 transition-colors"
            @click="$router.push('/my-jobs')"
            aria-label="Back to jobs"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
            </svg>
          </button>
          <button
            type="button"
            class="p-2 rounded-lg border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 transition-colors"
            :disabled="refreshing"
            @click="$emit('refresh')"
            aria-label="Refresh"
          >
            <svg class="w-4 h-4" :class="{ 'animate-spin': refreshing }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Job Header -->
    <div class="px-10 py-8">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-5">
          <!-- Job Icon -->
          <div class="w-16 h-16 rounded-2xl bg-[#5B5FED] flex items-center justify-center shadow-lg shadow-[#5B5FED]/20">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>

          <!-- Job Title & Status -->
          <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ job.name }}</h1>
            <div class="flex items-center gap-2">
              <StatusBadge :status="job.status" />
            </div>
          </div>
        </div>

        <!-- Actions Dropdown -->
        <div class="relative">
          <button class="px-5 py-2.5 bg-gray-50 hover:bg-gray-100 rounded-xl text-sm font-medium text-gray-600 flex items-center gap-2 transition-colors">
            Actions
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="px-10 border-b border-gray-100">
      <div class="flex gap-10">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="activeTab = tab.id"
          :class="[
            'py-4 text-base font-semibold border-b-3 transition-all relative',
            activeTab === tab.id
              ? 'text-[#5B5FED] border-[#5B5FED]'
              : 'text-gray-400 border-transparent hover:text-gray-600'
          ]"
        >
          {{ tab.label }}
          <span v-if="tab.count !== undefined" class="ml-2 text-sm font-normal">
            ({{ tab.count }})
          </span>
        </button>
      </div>
    </div>

    <!-- Tab Content -->
    <div class="p-10">
      <!-- Overview Tab -->
      <div v-if="activeTab === 'overview'" class="grid grid-cols-2 gap-8">

        <!-- ── Completion summary (shown when job is done) ─────────────────── -->
        <div v-if="isTerminal" class="col-span-2">
          <div :class="[
            'rounded-2xl p-6 border-2 flex items-center gap-6',
            job.status === 'completed'
              ? 'bg-green-50 border-green-200'
              : 'bg-red-50 border-red-200'
          ]">
            <!-- Big percentage -->
            <div class="flex-shrink-0 text-center">
              <div :class="[
                'text-6xl font-black',
                job.status === 'completed' ? 'text-green-600' : 'text-red-600'
              ]">
                {{ progressPercentage }}%
              </div>
              <p :class="['text-sm font-semibold mt-1',
                job.status === 'completed' ? 'text-green-700' : 'text-red-700']">
                {{ job.status === 'completed' ? 'COMPLETED' : 'FAILED' }}
              </p>
            </div>

            <!-- Divider -->
            <div :class="['w-px h-16 flex-shrink-0',
              job.status === 'completed' ? 'bg-green-200' : 'bg-red-200']"></div>

            <!-- Stats -->
            <div class="flex-1 grid grid-cols-3 gap-4">
              <div>
                <p class="text-xs text-gray-500">Tasks Done</p>
                <p class="text-xl font-bold text-gray-900">
                  {{ job.completed_tasks }} / {{ job.total_tasks }}
                </p>
              </div>
              <div>
                <p class="text-xs text-gray-500">Duration</p>
                <p class="text-xl font-bold text-gray-900">{{ duration || '—' }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-500">Completed At</p>
                <p class="text-sm font-semibold text-gray-700">{{ formatDateTime(job.completed_at) }}</p>
              </div>
            </div>

            <!-- Status icon -->
            <div :class="[
              'w-14 h-14 rounded-full flex items-center justify-center flex-shrink-0',
              job.status === 'completed' ? 'bg-green-500' : 'bg-red-500'
            ]">
              <svg v-if="job.status === 'completed'" class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
              </svg>
              <svg v-else class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </div>
          </div>
        </div>
        <!-- Job Information -->
        <div class="bg-gradient-to-br from-gray-50 to-white rounded-2xl p-8 border border-gray-100">
          <h3 class="text-xl font-bold text-gray-800 mb-8">Job Information</h3>

          <div class="space-y-6">
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-500 font-medium">Job ID</span>
              <span class="text-sm font-bold text-gray-800">#JOB-{{ String(job.id).padStart(3, '0') }}</span>
            </div>

            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-500 font-medium">Created At</span>
              <span class="text-sm font-semibold text-gray-700">{{ formatDateTime(job.created_at) }}</span>
            </div>

            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-500 font-medium">Created By</span>
              <span class="text-sm font-semibold text-gray-700">{{ job.user?.name || job.user?.email || 'admin@paulson.com' }}</span>
            </div>

            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-500 font-medium">Priority</span>
              <span :class="[
                'text-sm font-bold px-3 py-1 rounded-lg',
                job.priority >= 8 ? 'text-red-600 bg-red-50' : job.priority >= 5 ? 'text-orange-600 bg-orange-50' : 'text-green-600 bg-green-50'
              ]">
                {{ job.priority >= 8 ? 'High' : job.priority >= 5 ? 'Medium' : 'Low' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Progress -->
        <div class="bg-gradient-to-br from-gray-50 to-white rounded-2xl p-8 border border-gray-100">
          <h3 class="text-xl font-bold text-gray-800 mb-8">Progress</h3>

          <!-- Circular Progress -->
          <div class="flex items-center justify-center mb-8">
            <div class="relative w-48 h-48">
              <!-- Background Circle -->
              <svg class="w-full h-full transform -rotate-90">
                <circle
                  cx="96"
                  cy="96"
                  r="85"
                  stroke="#E8E9FE"
                  stroke-width="16"
                  fill="none"
                />
                <!-- Progress Circle -->
                <circle
                  cx="96"
                  cy="96"
                  r="85"
                  :stroke="progressColor"
                  stroke-width="16"
                  fill="none"
                  :stroke-dasharray="circumference"
                  :stroke-dashoffset="progressOffset"
                  stroke-linecap="round"
                  class="transition-all duration-700 ease-out"
                />
              </svg>
              <!-- Percentage Text -->
              <div class="absolute inset-0 flex items-center justify-center">
                <div class="text-center">
                  <div class="text-4xl font-bold text-gray-800">{{ progressPercentage }}%</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Progress Details -->
          <div class="text-center mb-6">
            <div class="text-3xl font-bold text-gray-800 mb-1">{{ job.completed_tasks || 0 }} / {{ job.total_tasks || 0 }}</div>
            <div class="text-sm text-gray-500 font-medium">tasks completed</div>
          </div>

          <div class="space-y-4">
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-500 font-medium">Estimated Completion</span>
              <span class="text-sm font-semibold text-gray-700">{{ estimatedCompletion }}</span>
            </div>

            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-500 font-medium">Duration</span>
              <span class="text-sm font-semibold text-gray-700">{{ duration || 'N/A' }}</span>
            </div>
          </div>
        </div>

        <!-- Task Status (Full Width) -->
        <div class="col-span-2 bg-gradient-to-br from-gray-50 to-white rounded-2xl p-8 border border-gray-100">
          <h3 class="text-xl font-bold text-gray-800 mb-8">Task Status</h3>

          <div class="grid grid-cols-4 gap-8">
            <!-- Completed -->
            <div>
              <div class="text-5xl font-bold text-[#22C55E] mb-4">{{ job.completed_tasks || 0 }}</div>
              <div class="w-full bg-gray-200 rounded-full h-3 mb-3 overflow-hidden">
                <div class="bg-gradient-to-r from-[#22C55E] to-[#16A34A] h-3 rounded-full transition-all duration-500" :style="{ width: completedPercentage + '%' }"></div>
              </div>
              <div class="text-sm text-gray-600 font-semibold">Completed</div>
            </div>

            <!-- Running -->
            <div>
              <div class="text-5xl font-bold text-[#F59E0B] mb-4">{{ runningTasks }}</div>
              <div class="w-full bg-gray-200 rounded-full h-3 mb-3 overflow-hidden">
                <div class="bg-gradient-to-r from-[#F59E0B] to-[#D97706] h-3 rounded-full transition-all duration-500" :style="{ width: runningPercentage + '%' }"></div>
              </div>
              <div class="text-sm text-gray-600 font-semibold">Running</div>
            </div>

            <!-- Failed -->
            <div>
              <div class="text-5xl font-bold text-[#EF4444] mb-4">{{ job.failed_tasks || 0 }}</div>
              <div class="w-full bg-gray-200 rounded-full h-3 mb-3 overflow-hidden">
                <div class="bg-gradient-to-r from-[#EF4444] to-[#DC2626] h-3 rounded-full transition-all duration-500" :style="{ width: failedPercentage + '%' }"></div>
              </div>
              <div class="text-sm text-gray-600 font-semibold">Failed</div>
            </div>

            <!-- Pending -->
            <div>
              <div class="text-5xl font-bold text-[#F97316] mb-4">{{ pendingTasks }}</div>
              <div class="w-full bg-gray-200 rounded-full h-3 mb-3 overflow-hidden">
                <div class="bg-gradient-to-r from-[#F97316] to-[#EA580C] h-3 rounded-full transition-all duration-500" :style="{ width: pendingPercentage + '%' }"></div>
              </div>
              <div class="text-sm text-gray-600 font-semibold">Pending</div>
            </div>
          </div>
        </div>

        <!-- Live Status Timeline (Full Width) -->
        <div class="col-span-2">
          <TaskStatusTimeline :job-id="job.id" :job-status="job.status" />
        </div>
      </div>

      <!-- Tasks Tab -->
      <div v-if="activeTab === 'tasks'">
        <!-- Error -->
        <div v-if="tasksError"
          class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          {{ tasksError }}
        </div>

        <!-- Live status timeline — always shown in tasks tab -->
        <TaskStatusTimeline :job-id="job.id" :job-status="job.status" class="mb-5" />

        <!-- "Waiting for workers" banner — shown when all tasks are pending -->
        <div v-if="!tasksLoading && allTasksPending && tasks.length > 0"
          class="mb-5 flex items-start gap-4 p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
          <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-yellow-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-semibold text-yellow-800">
              System is waiting for workers
            </p>
            <p class="text-xs text-yellow-700 mt-0.5">
              All {{ tasks.length }} tasks are <strong>PENDING</strong>.
              Workers will atomically claim tasks one by one when they become available.
            </p>
          </div>
          <div class="ml-auto flex-shrink-0">
            <span class="px-3 py-1.5 bg-yellow-200 text-yellow-800 text-xs font-bold rounded-full animate-pulse">
              PENDING × {{ tasks.length }}
            </span>
          </div>
        </div>

        <!-- Tasks table -->
        <TasksTable :tasks="tasks" :loading="tasksLoading" />
      </div>

      <!-- Retries Tab -->
      <div v-if="activeTab === 'retries'">
        <RetryPanel :job-id="job.id" />
      </div>

      <!-- Logs Tab -->
      <div v-if="activeTab === 'logs'" class="text-center py-16 text-gray-400">
        <svg class="w-20 h-20 mx-auto mb-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <p class="text-xl font-semibold text-gray-600 mb-2">No logs available</p>
        <p class="text-sm text-gray-400">Logs will appear here when tasks are executed</p>
      </div>

      <!-- Metrics Tab -->
      <div v-if="activeTab === 'metrics'" class="text-center py-16 text-gray-400">
        <svg class="w-20 h-20 mx-auto mb-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        <p class="text-xl font-semibold text-gray-600 mb-2">No metrics available</p>
        <p class="text-sm text-gray-400">Performance metrics will be displayed here</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import StatusBadge from './StatusBadge.vue'
import TasksTable from './TasksTable.vue'
import TaskStatusTimeline from './TaskStatusTimeline.vue'
import RetryPanel from './RetryPanel.vue'

const props = defineProps({
  job: {
    type: Object,
    required: true
  },
  tasks: {
    type: Array,
    default: () => []
  },
  tasksLoading: {
    type: Boolean,
    default: false
  },
  tasksError: {
    type: String,
    default: null
  },
  refreshing: {
    type: Boolean,
    default: false
  }
})

defineEmits(['cancel-job', 'refresh'])

const activeTab = ref('overview')

// Expose activeTab so parent can switch tabs (e.g. scroll to tasks)
defineExpose({ activeTab })

const tabs = computed(() => [
  { id: 'overview', label: 'Overview' },
  { id: 'tasks',    label: 'Tasks',   count: props.job.total_tasks || 0 },
  { id: 'retries',  label: 'Retries', count: retriedCount.value > 0 ? retriedCount.value : undefined },
  { id: 'logs',     label: 'Logs' },
  { id: 'metrics',  label: 'Metrics' },
])

// Count tasks that have been retried (retry_count > 0) or failed
const retriedCount = computed(() =>
  props.tasks.filter(t => t.retry_count > 0 || t.status === 'failed').length
)

// True when job is in a terminal state (completed / failed / cancelled)
const isTerminal = computed(() =>
  ['completed', 'failed', 'cancelled'].includes(props.job.status)
)

const progressPercentage = computed(() => {
  if (!props.job.total_tasks || props.job.total_tasks === 0) return 0
  return Math.round(((props.job.completed_tasks || 0) / props.job.total_tasks) * 100)
})

const progressColor = computed(() => {
  switch (props.job.status) {
    case 'completed':
      return '#22C55E' // green
    case 'running':
      return '#5B5FED' // blue/purple
    case 'failed':
      return '#EF4444' // red
    case 'cancelled':
      return '#9CA3AF' // gray
    default:
      return '#F59E0B' // orange/yellow
  }
})

// Circular progress calculations
const circumference = 2 * Math.PI * 85 // radius = 85
const progressOffset = computed(() => {
  const progress = progressPercentage.value
  return circumference - (progress / 100) * circumference
})

const runningTasks = computed(() => {
  return props.tasks.filter(t => t.status === 'running').length
})

const pendingTasks = computed(() => {
  return props.tasks.filter(t => t.status === 'pending' || t.status === 'assigned').length
})

// True when every task is still pending (no worker has claimed any yet)
const allTasksPending = computed(() => {
  if (!props.tasks.length) return false
  return props.tasks.every(t => t.status === 'pending')
})

const completedPercentage = computed(() => {
  if (!props.job.total_tasks || props.job.total_tasks === 0) return 0
  return Math.round(((props.job.completed_tasks || 0) / props.job.total_tasks) * 100)
})

const runningPercentage = computed(() => {
  if (!props.job.total_tasks || props.job.total_tasks === 0) return 0
  return Math.round((runningTasks.value / props.job.total_tasks) * 100)
})

const failedPercentage = computed(() => {
  if (!props.job.total_tasks || props.job.total_tasks === 0) return 0
  return Math.round(((props.job.failed_tasks || 0) / props.job.total_tasks) * 100)
})

const pendingPercentage = computed(() => {
  if (!props.job.total_tasks || props.job.total_tasks === 0) return 0
  return Math.round((pendingTasks.value / props.job.total_tasks) * 100)
})

const duration = computed(() => {
  if (!props.job.started_at) return null

  const start = new Date(props.job.started_at)
  const end = props.job.completed_at ? new Date(props.job.completed_at) : new Date()
  const durationMs = end - start

  if (durationMs < 0) return null

  const seconds = Math.floor(durationMs / 1000)
  const minutes = Math.floor(seconds / 60)
  const hours = Math.floor(minutes / 60)
  const days = Math.floor(hours / 24)

  if (days > 0) return `${days}d ${hours % 24}h ${minutes % 60}m`
  if (hours > 0) return `${hours}h ${minutes % 60}m ${seconds % 60}s`
  if (minutes > 0) return `${minutes}m ${seconds % 60}s`
  return `${seconds}s`
})

const estimatedCompletion = computed(() => {
  if (props.job.status === 'completed') {
    return formatDateTime(props.job.completed_at)
  }

  if (props.job.status === 'running' && props.job.started_at) {
    const start = new Date(props.job.started_at)
    const now = new Date()
    const elapsed = now - start
    const completed = props.job.completed_tasks || 0
    const total = props.job.total_tasks || 0

    if (completed > 0) {
      const avgTimePerTask = elapsed / completed
      const remainingTasks = total - completed
      const estimatedRemainingTime = avgTimePerTask * remainingTasks
      const estimatedEnd = new Date(now.getTime() + estimatedRemainingTime)

      return formatDateTime(estimatedEnd)
    }
  }

  return 'Calculating...'
})

function formatDateTime(dateString) {
  if (!dateString) return 'N/A'

  const date = new Date(dateString)
  const options = {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
    hour12: true
  }
  return date.toLocaleString('en-US', options)
}
</script>
