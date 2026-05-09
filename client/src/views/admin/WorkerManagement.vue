<template>
  <div class="flex-1 overflow-auto">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
      <div class="px-8 py-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Worker Management</h1>
            <p class="text-sm text-gray-500 mt-1">Monitor and manage worker nodes</p>
          </div>
          <button
            @click="refreshWorkers"
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

      <!-- Pull-based explanation -->
      <div class="mb-6 flex items-start gap-4 p-5 bg-indigo-50 border border-indigo-200 rounded-xl">
        <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13 10V3L4 14h7v7l9-11h-7z"/>
          </svg>
        </div>
        <div class="flex-1">
          <p class="text-sm font-semibold text-indigo-900 mb-1">Pull-Based Scheduling</p>
          <p class="text-xs text-indigo-700">
            Workers constantly ask <code class="bg-indigo-100 px-1 rounded font-mono">GET /tasks/next</code>.
            The coordinator atomically assigns the next pending task using
            <code class="bg-indigo-100 px-1 rounded font-mono">SELECT FOR UPDATE</code>.
            No two workers ever get the same task.
          </p>
        </div>
        <div class="flex-shrink-0 bg-gray-900 rounded-xl p-3 text-xs font-mono">
          <p class="text-gray-400 mb-1"># Start workers:</p>
          <p class="text-green-400">php artisan worker:run --key=worker-1</p>
          <p class="text-green-400">php artisan worker:run --key=worker-2</p>
          <p class="text-green-400">php artisan worker:run --key=worker-3</p>
        </div>
      </div>

      <!-- Worker Summary Cards -->
      <div v-if="workerSummary" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <div class="flex items-start justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500">Total Workers</p>
              <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ workerSummary.total || 0 }}</h3>
            </div>
            <div class="p-3 bg-blue-50 rounded-lg">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <div class="flex items-start justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500">Active</p>
              <h3 class="text-3xl font-bold text-green-600 mt-2">{{ workerSummary.active || 0 }}</h3>
            </div>
            <div class="p-3 bg-green-50 rounded-lg">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <div class="flex items-start justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500">Idle</p>
              <h3 class="text-3xl font-bold text-yellow-600 mt-2">{{ workerSummary.idle || 0 }}</h3>
            </div>
            <div class="p-3 bg-yellow-50 rounded-lg">
              <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <div class="flex items-start justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500">Dead</p>
              <h3 class="text-3xl font-bold text-red-600 mt-2">{{ workerSummary.dead || 0 }}</h3>
            </div>
            <div class="p-3 bg-red-50 rounded-lg">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Live Worker Activity Panel -->
      <WorkerActivityPanel class="mb-8" />

      <!-- Load Balance Panel -->
      <LoadBalancePanel class="mb-8" />

      <!-- Fault Tolerance Panel -->
      <FaultTolerancePanel class="mb-8" />

      <!-- Workers Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div v-if="loading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
          <p class="text-gray-600 mt-4">Loading workers...</p>
        </div>

        <div v-else-if="error" class="p-6">
          <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            {{ error }}
          </div>
        </div>

        <div v-else-if="workers.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
          </svg>
          <p class="text-gray-600 mt-4">No workers registered.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-100">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Worker Key</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hostname</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Heartbeat</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tasks ✓/✗</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="worker in workers" :key="worker.id"
                :class="['hover:bg-gray-50', worker.status === 'dead' ? 'bg-red-50/30' : '']">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                  {{ worker.worker_key }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ worker.hostname }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="['px-3 py-1 text-xs font-semibold rounded-full', getStatusClass(worker.status)]">
                    {{ worker.status.toUpperCase() }}
                  </span>
                </td>
                <!-- Heartbeat column with live countdown bar -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex flex-col gap-1 min-w-[120px]">
                    <div class="flex items-center justify-between text-xs">
                      <span :class="heartbeatTextColor(worker)">
                        {{ formatHeartbeat(worker.last_heartbeat_at) }}
                      </span>
                      <span v-if="worker.status !== 'dead'" class="text-gray-400 text-[10px]">
                        /{{ worker.seconds_since_heartbeat ?? '?' }}s
                      </span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                      <div
                        :class="['h-1.5 rounded-full transition-all duration-1000', heartbeatBarColor(worker)]"
                        :style="{ width: heartbeatBarWidth(worker) + '%' }"
                      ></div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <span class="text-green-600 font-medium">{{ worker.tasks_completed || 0 }}</span>
                  <span class="text-gray-400 mx-1">/</span>
                  <span class="text-red-600 font-medium">{{ worker.tasks_failed || 0 }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                  <button
                    v-if="worker.status !== 'dead'"
                    @click="markDead(worker.worker_key)"
                    class="text-yellow-600 hover:text-yellow-900 font-medium"
                  >
                    Mark Dead
                  </button>
                  <button
                    @click="deleteWorker(worker.worker_key)"
                    class="text-red-600 hover:text-red-900 font-medium"
                  >
                    Remove
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAdminStore } from '@/stores/adminStore'
import WorkerActivityPanel from '@/components/WorkerActivityPanel.vue'
import LoadBalancePanel from '@/components/LoadBalancePanel.vue'
import FaultTolerancePanel from '@/components/FaultTolerancePanel.vue'

const adminStore = useAdminStore()

const workers = ref([])
const workerSummary = ref(null)
const loading = ref(false)
const error = ref(null)

function getStatusClass(status) {
  const classes = {
    active: 'bg-green-100 text-green-800',
    idle:   'bg-yellow-100 text-yellow-800',
    dead:   'bg-red-100 text-red-800',
    busy:   'bg-blue-100 text-blue-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

function formatHeartbeat(dateString) {
  if (!dateString) return 'Never'
  const date = new Date(dateString)
  const now = new Date()
  const seconds = Math.floor((now - date) / 1000)
  if (seconds < 60)   return `${seconds}s ago`
  if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`
  if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`
  return `${Math.floor(seconds / 86400)}d ago`
}

// ── Heartbeat bar helpers (mirrors FaultTolerancePanel logic) ─────────────────
const THRESHOLD = 45 // seconds — matches scheduler config default

function heartbeatBarWidth(worker) {
  if (worker.status === 'dead') return 0
  const s = worker.seconds_since_heartbeat ?? 0
  const t = worker.threshold_seconds ?? THRESHOLD
  return Math.max(0, Math.round(((t - s) / t) * 100))
}

function heartbeatBarColor(worker) {
  const h = worker.heartbeat_health
  return {
    healthy:  'bg-green-500',
    warning:  'bg-yellow-400',
    critical: 'bg-red-500 animate-pulse',
    dead:     'bg-red-700',
    unknown:  'bg-gray-300',
  }[h] ?? 'bg-gray-300'
}

function heartbeatTextColor(worker) {
  const h = worker.heartbeat_health
  return {
    healthy:  'text-green-600',
    warning:  'text-yellow-600',
    critical: 'text-red-600',
    dead:     'text-red-700',
    unknown:  'text-gray-500',
  }[h] ?? 'text-gray-500'
}

async function refreshWorkers() {
  loading.value = true
  error.value = null

  // Use the admin workers endpoint which returns heartbeat_health + seconds_since_heartbeat
  const result = await adminStore.fetchAllWorkers()
  if (result.success) {
    workers.value = result.data.workers || []
    workerSummary.value = {
      total:  result.data.total  || 0,
      active: result.data.active || 0,
      idle:   result.data.idle   || 0,
      dead:   result.data.dead   || 0,
    }
  } else {
    error.value = result.error
  }

  loading.value = false
}

async function markDead(workerKey) {
  if (!confirm('Are you sure you want to mark this worker as dead? Its running tasks will be released.')) return
  
  const result = await adminStore.markWorkerDead(workerKey)
  if (result.success) {
    alert('Worker marked as dead')
    refreshWorkers()
  } else {
    alert('Failed to mark worker as dead: ' + result.error)
  }
}

async function deleteWorker(workerKey) {
  if (!confirm('Are you sure you want to remove this worker from the system?')) return
  
  const result = await adminStore.deleteWorker(workerKey)
  if (result.success) {
    alert('Worker removed successfully')
    refreshWorkers()
  } else {
    alert('Failed to remove worker: ' + result.error)
  }
}

onMounted(() => {
  refreshWorkers()
})
</script>
