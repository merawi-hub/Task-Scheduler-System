<template>
  <div class="card">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Workers</h2>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      <p class="mt-4 text-gray-600">Loading workers...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="workers.length === 0" class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No workers registered</h3>
      <p class="mt-1 text-sm text-gray-500">Start worker nodes to see them here.</p>
    </div>

    <!-- Workers Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="worker in workers"
        :key="worker.id"
        class="border rounded-lg p-4 transition-all duration-200 hover:shadow-lg"
        :class="getWorkerCardClass(worker.status)"
      >
        <!-- Worker Header -->
        <div class="flex items-start justify-between mb-3">
          <div class="flex-1">
            <h3 class="text-sm font-semibold text-gray-900 truncate">
              {{ worker.worker_key }}
            </h3>
            <p class="text-xs text-gray-500 mt-0.5">
              {{ worker.hostname || 'Unknown Host' }}
            </p>
          </div>
          <StatusBadge :status="worker.status" />
        </div>

        <!-- Worker Stats -->
        <div class="grid grid-cols-2 gap-3 mb-3">
          <div class="bg-gray-50 rounded p-2">
            <p class="text-xs text-gray-500">Completed</p>
            <p class="text-lg font-bold text-green-600">
              {{ worker.tasks_completed || 0 }}
            </p>
          </div>
          <div class="bg-gray-50 rounded p-2">
            <p class="text-xs text-gray-500">Failed</p>
            <p class="text-lg font-bold text-red-600">
              {{ worker.tasks_failed || 0 }}
            </p>
          </div>
        </div>

        <!-- Current Task -->
        <div v-if="worker.status === 'busy' && worker.current_task_id" class="mb-3 p-2 bg-blue-50 rounded">
          <p class="text-xs text-gray-600">
            <span class="font-medium">Current Task:</span>
            #{{ worker.current_task_id }}
          </p>
        </div>

        <!-- Heartbeat Info -->
        <div class="flex items-center justify-between text-xs">
          <div class="flex items-center space-x-1">
            <div
              class="w-2 h-2 rounded-full"
              :class="getHeartbeatIndicatorClass(worker)"
            ></div>
            <span class="text-gray-500">
              {{ formatHeartbeat(worker.last_heartbeat_at) }}
            </span>
          </div>
          <span v-if="worker.ip_address" class="text-gray-400">
            {{ worker.ip_address }}
          </span>
        </div>

        <!-- Success Rate -->
        <div class="mt-3 pt-3 border-t border-gray-200">
          <div class="flex items-center justify-between text-xs mb-1">
            <span class="text-gray-600">Success Rate</span>
            <span class="font-medium text-gray-900">{{ getSuccessRate(worker) }}%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-1.5">
            <div
              class="h-1.5 rounded-full transition-all duration-300"
              :class="getSuccessRateColor(getSuccessRate(worker))"
              :style="{ width: `${getSuccessRate(worker)}%` }"
            ></div>
          </div>
        </div>

        <!-- Registration Time -->
        <div class="mt-2 text-xs text-gray-400">
          Registered {{ formatDate(worker.registered_at || worker.created_at) }}
        </div>
      </div>
    </div>

    <!-- Summary Stats -->
    <div v-if="!loading && workers.length > 0" class="mt-6 pt-6 border-t border-gray-200">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
        <div>
          <p class="text-3xl font-bold text-gray-900">{{ workers.length }}</p>
          <p class="text-sm text-gray-500">Total Workers</p>
        </div>
        <div>
          <p class="text-3xl font-bold text-green-600">{{ activeCount }}</p>
          <p class="text-sm text-gray-500">Active</p>
        </div>
        <div>
          <p class="text-3xl font-bold text-blue-600">{{ busyCount }}</p>
          <p class="text-sm text-gray-500">Busy</p>
        </div>
        <div>
          <p class="text-3xl font-bold text-red-600">{{ deadCount }}</p>
          <p class="text-sm text-gray-500">Dead</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import StatusBadge from './StatusBadge.vue'

const props = defineProps({
  workers: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const activeCount = computed(() => 
  props.workers.filter(w => w.status === 'idle' || w.status === 'busy').length
)

const busyCount = computed(() => 
  props.workers.filter(w => w.status === 'busy').length
)

const deadCount = computed(() => 
  props.workers.filter(w => w.status === 'dead').length
)

function getWorkerCardClass(status) {
  switch (status) {
    case 'busy':
      return 'border-blue-300 bg-blue-50'
    case 'idle':
      return 'border-gray-300 bg-white'
    case 'dead':
      return 'border-red-300 bg-red-50'
    default:
      return 'border-gray-300 bg-white'
  }
}

function getHeartbeatIndicatorClass(worker) {
  if (worker.status === 'dead') return 'bg-red-500'
  
  const lastHeartbeat = new Date(worker.last_heartbeat_at)
  const now = new Date()
  const diffSeconds = (now - lastHeartbeat) / 1000
  
  if (diffSeconds < 20) return 'bg-green-500 animate-pulse'
  if (diffSeconds < 40) return 'bg-yellow-500'
  return 'bg-red-500'
}

function getSuccessRate(worker) {
  const total = (worker.tasks_completed || 0) + (worker.tasks_failed || 0)
  if (total === 0) return 100
  return Math.round((worker.tasks_completed / total) * 100)
}

function getSuccessRateColor(rate) {
  if (rate >= 90) return 'bg-green-500'
  if (rate >= 70) return 'bg-yellow-500'
  return 'bg-red-500'
}

function formatHeartbeat(dateString) {
  if (!dateString) return 'Never'
  
  const date = new Date(dateString)
  const now = new Date()
  const diffSeconds = Math.floor((now - date) / 1000)
  
  if (diffSeconds < 10) return 'Just now'
  if (diffSeconds < 60) return `${diffSeconds}s ago`
  
  const diffMinutes = Math.floor(diffSeconds / 60)
  if (diffMinutes < 60) return `${diffMinutes}m ago`
  
  const diffHours = Math.floor(diffMinutes / 60)
  return `${diffHours}h ago`
}

function formatDate(dateString) {
  if (!dateString) return 'Unknown'
  
  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now - date
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)
  
  if (diffMins < 60) return `${diffMins}m ago`
  if (diffHours < 24) return `${diffHours}h ago`
  if (diffDays < 7) return `${diffDays}d ago`
  
  return date.toLocaleDateString()
}
</script>
