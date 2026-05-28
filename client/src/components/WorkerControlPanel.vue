<template>
  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-purple-50">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
          </div>
          <div>
            <h3 class="text-lg font-bold text-gray-900">Worker Control Panel</h3>
            <p class="text-xs text-gray-600">Manage worker processes from the UI</p>
          </div>
        </div>
        <button
          @click="refresh"
          :disabled="loading"
          class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-white transition-colors"
        >
          <svg :class="['w-5 h-5', loading ? 'animate-spin' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Stats Bar -->
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-100">
      <div class="grid grid-cols-4 gap-4">
        <div class="text-center">
          <div class="text-2xl font-bold text-indigo-600">{{ stats.total }}</div>
          <div class="text-xs text-gray-600">Total Processes</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-bold text-green-600">{{ stats.running }}</div>
          <div class="text-xs text-gray-600">Running</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-bold text-blue-600">{{ stats.registered }}</div>
          <div class="text-xs text-gray-600">Registered Workers</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-bold text-purple-600">{{ stats.active }}</div>
          <div class="text-xs text-gray-600">Active Workers</div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="px-6 py-4 border-b border-gray-100">
      <div class="flex items-center gap-3">
        <button
          @click="openWorkerModal"
          class="flex-1 px-4 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all shadow-md hover:shadow-lg"
        >
          <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Start Workers
        </button>
        <button
          @click="stopAllWorkers"
          :disabled="loading || stats.running === 0"
          class="px-4 py-3 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
        >
          <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
          </svg>
          Stop All
        </button>
        <button
          @click="cleanupProcesses"
          :disabled="loading"
          class="px-4 py-3 bg-gray-600 text-white font-semibold rounded-lg hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
        >
          <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
          </svg>
          Cleanup
        </button>
      </div>
    </div>

    <!-- Running Processes -->
    <div class="p-6">
      <h4 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
        </svg>
        Running Processes ({{ processes.length }})
      </h4>

      <!-- Empty State -->
      <div v-if="processes.length === 0" class="text-center py-8">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No worker processes running</h3>
        <p class="mt-1 text-sm text-gray-500">Click "Start Workers" to begin processing tasks</p>
      </div>

      <!-- Process List -->
      <div v-else class="space-y-3 max-h-96 overflow-y-auto">
        <div
          v-for="process in processes"
          :key="process.worker_key"
          class="p-4 bg-gradient-to-r from-gray-50 to-white border rounded-xl hover:shadow-md transition-all"
          :class="process.is_running ? 'border-green-200' : 'border-gray-200'"
        >
          <div class="flex items-center justify-between">
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-2">
                <!-- Status Indicator -->
                <div
                  class="w-3 h-3 rounded-full"
                  :class="process.is_running ? 'bg-green-500 animate-pulse' : 'bg-gray-400'"
                ></div>

                <!-- Worker Key -->
                <h5 class="text-sm font-bold text-gray-900">{{ process.worker_key }}</h5>

                <!-- Status Badge -->
                <span
                  class="px-2 py-0.5 text-xs font-medium rounded-full"
                  :class="process.is_running
                    ? 'bg-green-100 text-green-800'
                    : 'bg-gray-100 text-gray-800'"
                >
                  {{ process.status }}
                </span>

                <!-- PID -->
                <span v-if="process.pid" class="text-xs text-gray-500 font-mono">
                  PID: {{ process.pid }}
                </span>
              </div>

              <!-- Process Details -->
              <div class="flex items-center gap-4 text-xs text-gray-600">
                <div class="flex items-center gap-1">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  Started: {{ formatRelativeTime(process.started_at) }}
                </div>
                <div v-if="process.options" class="flex items-center gap-3">
                  <span class="flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    Sleep: {{ process.options.sleep }}s
                  </span>
                  <span class="flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    Heartbeat: {{ process.options.heartbeat }}s
                  </span>
                </div>
              </div>

              <!-- Worker Registration Status -->
              <div v-if="getWorkerStatus(process.worker_key)" class="mt-2 flex items-center gap-2">
                <span class="text-xs text-gray-500">Worker Status:</span>
                <span
                  class="px-2 py-0.5 text-xs font-medium rounded-full"
                  :class="getWorkerStatusClass(getWorkerStatus(process.worker_key))"
                >
                  {{ getWorkerStatus(process.worker_key) }}
                </span>
                <span v-if="getWorkerTaskCount(process.worker_key)" class="text-xs text-gray-500">
                  Tasks: {{ getWorkerTaskCount(process.worker_key) }}
                </span>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2">
              <button
                v-if="process.is_running"
                @click="stopWorker(process.worker_key)"
                :disabled="loading"
                class="px-3 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 disabled:opacity-50 transition-all"
                title="Stop Worker"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Worker Management Modal -->
    <WorkerManagementModal
      :is-open="showWorkerModal"
      @close="showWorkerModal = false"
      @workers-started="handleWorkersStarted"
    />

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { adminApi } from '@/api'
import { useToastStore } from '@/stores/toastStore'
import WorkerManagementModal from './modals/WorkerManagementModal.vue'

const toastStore = useToastStore()

// State
const loading = ref(false)
const processes = ref([])
const workers = ref([])
const showWorkerModal = ref(false)
let refreshInterval = null

// Computed
const stats = computed(() => ({
  total: processes.value.length,
  running: processes.value.filter(p => p.is_running).length,
  registered: workers.value.length,
  active: workers.value.filter(w => w.status === 'idle' || w.status === 'busy').length,
}))

// Methods
const refresh = async () => {
  await Promise.all([
    fetchProcesses(),
    fetchWorkers(),
  ])
}

const fetchProcesses = async () => {
  try {
    const data = await adminApi.getWorkerProcesses()
    processes.value = Object.values(data.processes || {})
  } catch (error) {
    console.error('Failed to fetch worker processes:', error)
  }
}

const fetchWorkers = async () => {
  try {
    const data = await adminApi.getAllWorkers()
    workers.value = data.workers || []
  } catch (error) {
    console.error('Failed to fetch workers:', error)
  }
}

const openWorkerModal = () => {
  showWorkerModal.value = true
}

const stopWorker = async (workerKey) => {
  if (!confirm(`Stop worker ${workerKey}?`)) {
    return
  }

  loading.value = true
  try {
    await adminApi.stopWorker(workerKey)
    toastStore.success(`Worker ${workerKey} stopped`)
    await refresh()
  } catch (error) {
    console.error('Failed to stop worker:', error)
    toastStore.error(error.response?.data?.message || 'Failed to stop worker')
  } finally {
    loading.value = false
  }
}

const stopAllWorkers = async () => {
  if (!confirm('Are you sure you want to stop all workers?')) {
    return
  }

  loading.value = true
  try {
    const result = await adminApi.stopAllWorkers()
    toastStore.success(result.message)
    await refresh()
  } catch (error) {
    console.error('Failed to stop all workers:', error)
    toastStore.error(error.response?.data?.message || 'Failed to stop all workers')
  } finally {
    loading.value = false
  }
}

const cleanupProcesses = async () => {
  loading.value = true
  try {
    const result = await adminApi.cleanupWorkerProcesses()
    toastStore.success(result.message)
    await refresh()
  } catch (error) {
    console.error('Failed to cleanup processes:', error)
    toastStore.error('Failed to cleanup processes')
  } finally {
    loading.value = false
  }
}

const handleWorkersStarted = async () => {
  await refresh()
}

const getWorkerStatus = (workerKey) => {
  const worker = workers.value.find(w => w.worker_key === workerKey)
  return worker?.status || null
}

const getWorkerTaskCount = (workerKey) => {
  const worker = workers.value.find(w => w.worker_key === workerKey)
  return worker?.tasks_completed || 0
}

const getWorkerStatusClass = (status) => {
  const classes = {
    idle: 'bg-blue-100 text-blue-800',
    busy: 'bg-green-100 text-green-800',
    dead: 'bg-red-100 text-red-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const formatRelativeTime = (dateString) => {
  if (!dateString) return 'N/A'

  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now - date
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)

  if (diffMins < 1) return 'just now'
  if (diffMins < 60) return `${diffMins}m ago`
  if (diffHours < 24) return `${diffHours}h ago`
  return date.toLocaleDateString()
}

// Lifecycle
onMounted(() => {
  refresh()
  // Auto-refresh every 5 seconds
  refreshInterval = setInterval(refresh, 5000)
})

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
})
</script>
