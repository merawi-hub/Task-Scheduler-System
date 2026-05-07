<template>
  <div class="card">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-xl font-bold text-gray-900">Tasks</h2>
      
      <!-- Filter by status -->
      <div class="flex items-center space-x-3">
        <label class="text-sm font-medium text-gray-700">Filter:</label>
        <select
          v-model="filterStatus"
          class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
        >
          <option value="">All Statuses</option>
          <option value="pending">Pending</option>
          <option value="assigned">Assigned</option>
          <option value="running">Running</option>
          <option value="done">Done</option>
          <option value="failed">Failed</option>
        </select>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      <p class="mt-2 text-gray-600">Loading tasks...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="filteredTasks.length === 0" class="text-center py-8">
      <p class="text-gray-500">No tasks found</p>
    </div>

    <!-- Tasks Table -->
    <div v-else class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Task #
            </th>
            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Status
            </th>
            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Worker
            </th>
            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Retries
            </th>
            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Duration
            </th>
            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Assigned At
            </th>
            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Completed At
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr
            v-for="task in filteredTasks"
            :key="task.id"
            class="hover:bg-gray-50 transition-colors"
            :class="getRowClass(task.status)"
          >
            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
              #{{ task.task_index !== undefined ? task.task_index : task.id }}
            </td>
            <td class="px-4 py-3 whitespace-nowrap">
              <StatusBadge :status="task.status" />
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
              <span v-if="task.worker">
                {{ task.worker.worker_key || `Worker #${task.worker_id}` }}
              </span>
              <span v-else-if="task.worker_id">
                Worker #{{ task.worker_id }}
              </span>
              <span v-else class="text-gray-400">
                Unassigned
              </span>
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-sm">
              <span
                v-if="task.retry_count > 0"
                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800"
              >
                {{ task.retry_count }} / {{ task.max_retries || 3 }}
              </span>
              <span v-else class="text-gray-400">
                0
              </span>
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
              {{ formatDuration(task) }}
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
              {{ formatDateTime(task.assigned_at) }}
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
              {{ formatDateTime(task.completed_at) }}
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Task Details Modal/Expandable (optional) -->
      <div v-if="selectedTask" class="mt-4 p-4 bg-gray-50 rounded-lg">
        <h3 class="text-sm font-semibold text-gray-900 mb-2">Task Details</h3>
        <div class="text-sm text-gray-600 space-y-1">
          <p><strong>Task ID:</strong> {{ selectedTask.id }}</p>
          <p v-if="selectedTask.failure_reason">
            <strong>Failure Reason:</strong>
            <span class="text-red-600">{{ selectedTask.failure_reason }}</span>
          </p>
          <p v-if="selectedTask.payload">
            <strong>Payload:</strong>
            <pre class="mt-1 p-2 bg-white rounded text-xs overflow-x-auto">{{ JSON.stringify(selectedTask.payload, null, 2) }}</pre>
          </p>
        </div>
      </div>
    </div>

    <!-- Summary Stats -->
    <div v-if="!loading && filteredTasks.length > 0" class="mt-4 pt-4 border-t border-gray-200">
      <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-center">
        <div>
          <p class="text-2xl font-bold text-gray-900">{{ taskStats.total }}</p>
          <p class="text-xs text-gray-500">Total</p>
        </div>
        <div>
          <p class="text-2xl font-bold text-green-600">{{ taskStats.done }}</p>
          <p class="text-xs text-gray-500">Done</p>
        </div>
        <div>
          <p class="text-2xl font-bold text-blue-600">{{ taskStats.running }}</p>
          <p class="text-xs text-gray-500">Running</p>
        </div>
        <div>
          <p class="text-2xl font-bold text-yellow-600">{{ taskStats.pending }}</p>
          <p class="text-xs text-gray-500">Pending</p>
        </div>
        <div>
          <p class="text-2xl font-bold text-red-600">{{ taskStats.failed }}</p>
          <p class="text-xs text-gray-500">Failed</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import StatusBadge from './StatusBadge.vue'

const props = defineProps({
  tasks: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const filterStatus = ref('')
const selectedTask = ref(null)

const filteredTasks = computed(() => {
  if (!filterStatus.value) return props.tasks
  return props.tasks.filter(task => task.status === filterStatus.value)
})

const taskStats = computed(() => {
  return {
    total: filteredTasks.value.length,
    done: filteredTasks.value.filter(t => t.status === 'done').length,
    running: filteredTasks.value.filter(t => t.status === 'running').length,
    pending: filteredTasks.value.filter(t => t.status === 'pending').length,
    assigned: filteredTasks.value.filter(t => t.status === 'assigned').length,
    failed: filteredTasks.value.filter(t => t.status === 'failed').length
  }
})

function getRowClass(status) {
  switch (status) {
    case 'failed':
      return 'bg-red-50'
    case 'done':
      return 'bg-green-50'
    default:
      return ''
  }
}

function formatDuration(task) {
  if (!task.started_at) return 'N/A'
  
  const start = new Date(task.started_at)
  const end = task.completed_at ? new Date(task.completed_at) : new Date()
  const durationMs = end - start
  
  if (durationMs < 0) return 'N/A'
  
  const seconds = Math.floor(durationMs / 1000)
  const minutes = Math.floor(seconds / 60)
  const hours = Math.floor(minutes / 60)
  
  if (hours > 0) return `${hours}h ${minutes % 60}m`
  if (minutes > 0) return `${minutes}m ${seconds % 60}s`
  return `${seconds}s`
}

function formatDateTime(dateString) {
  if (!dateString) return 'N/A'
  
  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now - date
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  
  if (diffMins < 1) return 'Just now'
  if (diffMins < 60) return `${diffMins}m ago`
  if (diffHours < 24) return `${diffHours}h ago`
  
  return date.toLocaleString()
}
</script>
