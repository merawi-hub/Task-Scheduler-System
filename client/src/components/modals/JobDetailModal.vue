<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <!-- Glassmorphism Background overlay with blur -->
        <div class="fixed inset-0 backdrop-blur-md bg-black/30 transition-opacity" @click="$emit('close')"></div>

        <!-- Modal panel with glassmorphism -->
        <div class="relative bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl max-w-4xl w-full z-10 max-h-[90vh] overflow-hidden flex flex-col border border-white/20">
          <!-- Header with gradient -->
          <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 px-6 py-5 rounded-t-3xl">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center shadow-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <div>
                <h3 class="text-xl font-bold text-white">📋 {{ job.name }}</h3>
                <p class="text-sm text-indigo-100">Job #{{ job.id }} • {{ job.type || 'General' }}</p>
              </div>
            </div>
            <button @click="$emit('close')" class="text-white hover:text-gray-200 transition-all hover:rotate-90 duration-300">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-6 overflow-y-auto flex-1">
          <!-- Status and Progress -->
          <div class="grid grid-cols-2 gap-4">
            <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
              <p class="text-sm text-gray-500 mb-2">📊 Status</p>
              <span :class="[
                'px-3 py-1 inline-flex text-sm font-semibold rounded-full shadow-sm',
                getStatusClass(job.status)
              ]">
                {{ job.status.toUpperCase() }}
              </span>
            </div>

            <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
              <p class="text-sm text-gray-500 mb-2">⚡ Progress</p>
              <div class="flex items-center gap-3">
                <div class="flex-1 bg-gray-200 rounded-full h-3 shadow-inner">
                  <div :class="getProgressColor(job.status)" class="h-3 rounded-full transition-all shadow-sm" :style="{ width: getProgress() + '%' }"></div>
                </div>
                <span class="text-sm font-bold text-gray-900">{{ getProgress() }}%</span>
              </div>
            </div>
          </div>

          <!-- Stats Cards -->
          <div class="grid grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200 shadow-sm hover:shadow-md transition-all hover:scale-105">
              <p class="text-xs text-blue-600 font-medium mb-1">📦 Total Tasks</p>
              <p class="text-2xl font-bold text-blue-900">{{ job.total_tasks || 0 }}</p>
            </div>

            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4 border border-green-200 shadow-sm hover:shadow-md transition-all hover:scale-105">
              <p class="text-xs text-green-600 font-medium mb-1">✅ Completed</p>
              <p class="text-2xl font-bold text-green-900">{{ job.completed_tasks || 0 }}</p>
            </div>

            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-4 border border-yellow-200 shadow-sm hover:shadow-md transition-all hover:scale-105">
              <p class="text-xs text-yellow-600 font-medium mb-1">⏳ Pending</p>
              <p class="text-2xl font-bold text-yellow-900">{{ getPendingTasks() }}</p>
            </div>

            <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-4 border border-red-200 shadow-sm hover:shadow-md transition-all hover:scale-105">
              <p class="text-xs text-red-600 font-medium mb-1">❌ Failed</p>
              <p class="text-2xl font-bold text-red-900">{{ job.failed_tasks || 0 }}</p>
            </div>
          </div>

          <!-- Job Details -->
          <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 space-y-4 shadow-sm">
            <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
              <span>📝</span> Job Details
            </h4>
            
            <div class="grid grid-cols-2 gap-4">
              <div class="bg-white/50 rounded-lg p-3">
                <p class="text-sm text-gray-500 mb-1">⚡ Priority</p>
                <p class="text-sm font-medium text-gray-900">{{ job.priority || 5 }}</p>
              </div>

              <div class="bg-white/50 rounded-lg p-3">
                <p class="text-sm text-gray-500 mb-1">🕐 Created At</p>
                <p class="text-sm font-medium text-gray-900">{{ formatDate(job.created_at) }}</p>
              </div>

              <div class="bg-white/50 rounded-lg p-3">
                <p class="text-sm text-gray-500 mb-1">▶️ Started At</p>
                <p class="text-sm font-medium text-gray-900">{{ job.started_at ? formatDate(job.started_at) : 'Not started' }}</p>
              </div>

              <div class="bg-white/50 rounded-lg p-3">
                <p class="text-sm text-gray-500 mb-1">✅ Completed At</p>
                <p class="text-sm font-medium text-gray-900">{{ job.completed_at ? formatDate(job.completed_at) : 'Not completed' }}</p>
              </div>
            </div>

            <div v-if="job.description" class="bg-white/50 rounded-lg p-3">
              <p class="text-sm text-gray-500 mb-1">💬 Description</p>
              <p class="text-sm text-gray-900">{{ job.description }}</p>
            </div>
          </div>

          <!-- Task Data -->
          <div v-if="job.task_data" class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 shadow-sm">
            <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
              <span>📦</span> Task Data
            </h4>
            <pre class="bg-gray-900 text-green-400 p-4 rounded-xl overflow-x-auto text-xs font-mono shadow-inner">{{ formatJSON(job.task_data) }}</pre>
          </div>

          <!-- Tasks List -->
          <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
              <h4 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <span>📋</span> Tasks
              </h4>
              <button @click="loadTasks" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium flex items-center gap-1 hover:scale-105 transition-transform">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Refresh
              </button>
            </div>

            <div v-if="loadingTasks" class="text-center py-8">
              <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div>
              <p class="text-sm text-gray-600 mt-2">Loading tasks...</p>
            </div>

            <div v-else-if="tasks.length === 0" class="text-center py-8">
              <p class="text-sm text-gray-500">📭 No tasks found</p>
            </div>

            <div v-else class="space-y-2 max-h-64 overflow-y-auto">
              <div v-for="task in tasks" :key="task.id" class="bg-white/70 backdrop-blur-sm rounded-xl p-4 border border-gray-200 hover:border-indigo-300 transition-all hover:shadow-md">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div :class="[
                      'w-8 h-8 rounded-lg flex items-center justify-center shadow-sm',
                      task.status === 'completed' ? 'bg-green-100' : task.status === 'running' ? 'bg-blue-100' : task.status === 'failed' ? 'bg-red-100' : 'bg-gray-100'
                    ]">
                      <svg class="w-4 h-4" :class="[
                        task.status === 'completed' ? 'text-green-600' : task.status === 'running' ? 'text-blue-600' : task.status === 'failed' ? 'text-red-600' : 'text-gray-600'
                      ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-900">Task #{{ task.task_index }}</p>
                      <p class="text-xs text-gray-500">{{ task.worker_key || 'Unassigned' }}</p>
                    </div>
                  </div>
                  <span :class="[
                    'px-2 py-1 text-xs font-semibold rounded-full shadow-sm',
                    task.status === 'completed' ? 'bg-green-100 text-green-800' : task.status === 'running' ? 'bg-blue-100 text-blue-800' : task.status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'
                  ]">
                    {{ task.status }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 flex items-center justify-between border-t border-gray-200 rounded-b-3xl">
          <div class="flex items-center gap-2">
            <button
              v-if="job.status === 'running' || job.status === 'pending'"
              @click="handleCancel"
              :disabled="cancelling"
              class="px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all font-medium disabled:opacity-50 disabled:cursor-not-allowed shadow-md hover:shadow-lg hover:scale-105"
            >
              {{ cancelling ? 'Cancelling...' : '🚫 Cancel Job' }}
            </button>
          </div>
          <button
            @click="$emit('close')"
            class="px-6 py-2 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-xl hover:from-gray-700 hover:to-gray-800 transition-all font-medium shadow-md hover:shadow-lg hover:scale-105"
          >
            Close
          </button>
        </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useJobsStore } from '@/stores/jobsStore'
import api from '@/api/axios'

const props = defineProps({
  job: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'updated'])
const jobsStore = useJobsStore()

const tasks = ref([])
const loadingTasks = ref(false)
const cancelling = ref(false)

function getStatusClass(status) {
  const classes = {
    completed: 'bg-green-100 text-green-800',
    running: 'bg-blue-100 text-blue-800',
    failed: 'bg-red-100 text-red-800',
    pending: 'bg-yellow-100 text-yellow-800',
    cancelled: 'bg-gray-100 text-gray-800'
  }
  return classes[status] || classes.pending
}

function getProgressColor(status) {
  const colors = {
    completed: 'bg-green-500',
    running: 'bg-blue-500',
    failed: 'bg-red-500',
    pending: 'bg-yellow-500'
  }
  return colors[status] || colors.pending
}

function getProgress() {
  if (!props.job.total_tasks || props.job.total_tasks === 0) return 0
  return Math.round((props.job.completed_tasks / props.job.total_tasks) * 100)
}

function getPendingTasks() {
  return (props.job.total_tasks || 0) - (props.job.completed_tasks || 0) - (props.job.failed_tasks || 0)
}

function formatDate(dateString) {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function formatJSON(data) {
  try {
    if (typeof data === 'string') {
      return JSON.stringify(JSON.parse(data), null, 2)
    }
    return JSON.stringify(data, null, 2)
  } catch (e) {
    return data
  }
}

async function loadTasks() {
  loadingTasks.value = true
  try {
    const response = await api.get(`/jobs/${props.job.id}/tasks`)
    tasks.value = response.data.tasks || response.data || []
  } catch (error) {
    console.error('Failed to load tasks:', error)
  } finally {
    loadingTasks.value = false
  }
}

async function handleCancel() {
  if (!confirm(`Are you sure you want to cancel "${props.job.name}"?`)) return

  cancelling.value = true
  try {
    await jobsStore.cancelJob(props.job.id)
    emit('updated')
    emit('close')
  } catch (error) {
    alert('Failed to cancel job')
  } finally {
    cancelling.value = false
  }
}

onMounted(() => {
  loadTasks()
})
</script>
