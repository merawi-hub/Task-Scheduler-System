<template>
  <div class="space-y-6">
    <!-- Job Header -->
    <div class="card">
      <div class="flex items-start justify-between">
        <div class="flex-1">
          <div class="flex items-center space-x-3 mb-2">
            <h1 class="text-3xl font-bold text-gray-900">{{ job.name }}</h1>
            <StatusBadge :status="job.status" />
          </div>
          
          <p v-if="job.description" class="text-gray-600 mb-4">
            {{ job.description }}
          </p>
          
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
              <p class="text-gray-500">Job ID</p>
              <p class="font-semibold text-gray-900">#{{ job.id }}</p>
            </div>
            <div>
              <p class="text-gray-500">Type</p>
              <p class="font-semibold text-gray-900">{{ formatJobType(job.type) }}</p>
            </div>
            <div>
              <p class="text-gray-500">Priority</p>
              <p class="font-semibold text-gray-900">{{ job.priority || 5 }}</p>
            </div>
            <div>
              <p class="text-gray-500">Submitted By</p>
              <p class="font-semibold text-gray-900">{{ job.submitted_by || 'System' }}</p>
            </div>
          </div>
        </div>
        
        <div class="ml-4">
          <button
            v-if="job.status === 'pending' || job.status === 'running'"
            @click="$emit('cancel-job', job.id)"
            class="btn btn-danger"
          >
            Cancel Job
          </button>
        </div>
      </div>
    </div>

    <!-- Progress Overview -->
    <div class="card">
      <h2 class="text-xl font-bold text-gray-900 mb-4">Progress Overview</h2>
      
      <!-- Progress Bar -->
      <div class="mb-6">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-gray-700">Overall Progress</span>
          <span class="text-sm font-semibold text-gray-900">{{ progressPercentage }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-4">
          <div
            class="h-4 rounded-full transition-all duration-300"
            :class="progressBarColor"
            :style="{ width: `${progressPercentage}%` }"
          ></div>
        </div>
      </div>

      <!-- Task Stats Grid -->
      <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="text-center p-4 bg-gray-50 rounded-lg">
          <p class="text-3xl font-bold text-gray-900">{{ job.total_tasks || 0 }}</p>
          <p class="text-sm text-gray-600 mt-1">Total Tasks</p>
        </div>
        <div class="text-center p-4 bg-green-50 rounded-lg">
          <p class="text-3xl font-bold text-green-600">{{ job.completed_tasks || 0 }}</p>
          <p class="text-sm text-gray-600 mt-1">Completed</p>
        </div>
        <div class="text-center p-4 bg-blue-50 rounded-lg">
          <p class="text-3xl font-bold text-blue-600">{{ runningTasks }}</p>
          <p class="text-sm text-gray-600 mt-1">Running</p>
        </div>
        <div class="text-center p-4 bg-yellow-50 rounded-lg">
          <p class="text-3xl font-bold text-yellow-600">{{ pendingTasks }}</p>
          <p class="text-sm text-gray-600 mt-1">Pending</p>
        </div>
        <div class="text-center p-4 bg-red-50 rounded-lg">
          <p class="text-3xl font-bold text-red-600">{{ job.failed_tasks || 0 }}</p>
          <p class="text-sm text-gray-600 mt-1">Failed</p>
        </div>
      </div>

      <!-- Timestamps -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6 pt-6 border-t border-gray-200">
        <div>
          <p class="text-sm text-gray-500">Created At</p>
          <p class="text-sm font-medium text-gray-900">{{ formatDateTime(job.created_at) }}</p>
        </div>
        <div>
          <p class="text-sm text-gray-500">Started At</p>
          <p class="text-sm font-medium text-gray-900">{{ formatDateTime(job.started_at) }}</p>
        </div>
        <div>
          <p class="text-sm text-gray-500">Completed At</p>
          <p class="text-sm font-medium text-gray-900">{{ formatDateTime(job.completed_at) }}</p>
        </div>
      </div>

      <!-- Duration -->
      <div v-if="duration" class="mt-4 p-3 bg-blue-50 rounded-lg">
        <p class="text-sm text-gray-600">
          <span class="font-medium">Duration:</span>
          {{ duration }}
        </p>
      </div>
    </div>

    <!-- Tasks Table -->
    <TasksTable
      :tasks="tasks"
      :loading="tasksLoading"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import StatusBadge from './StatusBadge.vue'
import TasksTable from './TasksTable.vue'

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
  }
})

defineEmits(['cancel-job'])

const progressPercentage = computed(() => {
  if (!props.job.total_tasks || props.job.total_tasks === 0) return 0
  return Math.round((props.job.completed_tasks / props.job.total_tasks) * 100)
})

const progressBarColor = computed(() => {
  switch (props.job.status) {
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
})

const runningTasks = computed(() => {
  return props.tasks.filter(t => t.status === 'running').length
})

const pendingTasks = computed(() => {
  return props.tasks.filter(t => t.status === 'pending' || t.status === 'assigned').length
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

function formatJobType(type) {
  if (!type) return 'Unknown'
  return type
    .split('_')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ')
}

function formatDateTime(dateString) {
  if (!dateString) return 'N/A'
  
  const date = new Date(dateString)
  return date.toLocaleString()
}
</script>
