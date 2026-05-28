<template>
  <div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

    <!-- ── Header ─────────────────────────────────────────────────────────── -->
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
          <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13 10V3L4 14h7v7l9-11h-7z"/>
          </svg>
        </div>
        <div>
          <h3 class="text-sm font-semibold text-gray-900">Parallel Execution</h3>
          <p class="text-xs text-gray-500">Workers processing simultaneously — this is why it's fast</p>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <div class="flex items-center gap-1.5 px-2.5 py-1 bg-green-50 border border-green-200 rounded-full">
          <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
          <span class="text-xs font-medium text-green-700">Live</span>
        </div>
        <button @click="refresh"
          class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
          <svg :class="['w-4 h-4', loading ? 'animate-spin' : '']"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- ── Loading ────────────────────────────────────────────────────────── -->
    <div v-if="loading && !activity" class="text-center py-10">
      <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div>
      <p class="text-xs text-gray-500 mt-2">Loading worker activity…</p>
    </div>

    <!-- ── No workers ─────────────────────────────────────────────────────── -->
    <div v-else-if="totalWorkers === 0" class="px-6 py-8">
      <div class="flex items-start gap-4 p-4 bg-amber-50 border border-amber-200 rounded-xl mb-5">
        <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div class="flex-1">
          <p class="text-sm font-semibold text-amber-800">No workers running</p>
          <p class="text-xs text-amber-700 mt-0.5">
            Without workers, all {{ pendingTasksAvailable }} tasks stay PENDING.
            Start workers to see parallel processing in action.
          </p>
        </div>
      </div>

      <!-- Admin: Show Start Workers Button -->
      <div v-if="isAdmin" class="text-center">
        <button
          @click="openWorkerModal"
          class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg hover:shadow-xl"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Start Workers
        </button>
        <p class="text-xs text-gray-500 mt-3">
          Click to start worker processes from the UI
        </p>
      </div>

      <!-- Non-Admin: Show Contact Message -->
      <div v-else class="text-center py-4">
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 border border-blue-200 rounded-lg">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <p class="text-sm text-blue-800 font-medium">
            Contact your administrator to start workers
          </p>
        </div>
      </div>
    </div>

    <!-- ── Main content ───────────────────────────────────────────────────── -->
    <div v-else>

      <!-- Speed comparison banner (shown when workers are active) -->
      <div v-if="parallel && parallel.active_workers > 1"
        class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13 10V3L4 14h7v7l9-11h-7z"/>
          </svg>
          <div>
            <p class="text-xs font-bold text-white">
              {{ parallel.active_workers }} workers running in parallel
            </p>
            <p class="text-[10px] text-indigo-200">
              ~{{ parallel.speed_multiplier }}× faster than sequential processing
            </p>
          </div>
        </div>
        <div class="flex items-center gap-4">
          <div class="text-center">
            <p class="text-lg font-bold text-white">{{ parallel.speed_multiplier }}×</p>
            <p class="text-[10px] text-indigo-200">Speed</p>
          </div>
          <div v-if="parallel.throughput_per_sec > 0" class="text-center">
            <p class="text-lg font-bold text-white">{{ parallel.throughput_per_sec }}</p>
            <p class="text-[10px] text-indigo-200">tasks/sec</p>
          </div>
          <div v-if="parallel.records_in_flight > 0" class="text-center">
            <p class="text-lg font-bold text-white">{{ parallel.records_in_flight.toLocaleString() }}</p>
            <p class="text-[10px] text-indigo-200">records now</p>
          </div>
        </div>
      </div>

      <div class="p-6">

        <!-- ── Summary stats ───────────────────────────────────────────────── -->
        <div class="grid grid-cols-4 gap-3 mb-6">
          <div class="bg-gray-50 rounded-xl p-3 text-center">
            <p class="text-xl font-bold text-gray-900">{{ totalWorkers }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Workers</p>
          </div>
          <div class="bg-blue-50 rounded-xl p-3 text-center">
            <p class="text-xl font-bold text-blue-600">{{ busyWorkers.length }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Processing</p>
          </div>
          <div class="bg-yellow-50 rounded-xl p-3 text-center">
            <p class="text-xl font-bold text-yellow-600">{{ pendingTasksAvailable }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Queued</p>
          </div>
          <div class="bg-green-50 rounded-xl p-3 text-center">
            <p class="text-xl font-bold text-green-600">
              {{ parallel?.total_records_done?.toLocaleString() ?? 0 }}
            </p>
            <p class="text-xs text-gray-500 mt-0.5">Records done</p>
          </div>
        </div>

        <!-- ── Parallel execution diagram ─────────────────────────────────── -->
        <!-- This is the core visual: Worker-1 → Records 1-100, Worker-2 → Records 101-200, etc. -->
        <div class="space-y-3 mb-6">

          <!-- Busy workers — actively processing in parallel -->
          <div v-for="(worker, idx) in busyWorkers" :key="worker.worker_key"
            class="rounded-xl border-2 border-blue-200 bg-blue-50 overflow-hidden">

            <div class="flex items-center gap-4 px-4 py-3">

              <!-- Worker identity -->
              <div class="flex items-center gap-2.5 flex-shrink-0 w-32">
                <div :class="['w-9 h-9 rounded-lg flex items-center justify-center shadow-sm', workerColor(idx).bg]">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-xs font-bold text-gray-800 leading-tight">{{ worker.worker_key }}</p>
                  <span class="text-[10px] font-bold text-blue-600">PROCESSING</span>
                </div>
              </div>

              <!-- Arrow -->
              <div class="flex-shrink-0 flex flex-col items-center gap-0.5">
                <svg class="w-4 h-4 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd"
                    d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                    clip-rule="evenodd"/>
                </svg>
                <span class="text-[9px] text-blue-400 font-medium">pulled</span>
              </div>

              <!-- Task info -->
              <div class="flex-1 min-w-0" v-if="worker.current_task">
                <div class="flex items-center justify-between mb-1.5">
                  <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-gray-800">
                      Task #{{ worker.current_task.task_number }}
                    </span>
                    <span v-if="worker.current_task.record_from != null"
                      :class="['px-2 py-0.5 text-[10px] font-bold rounded font-mono', workerColor(idx).badge]">
                      records {{ worker.current_task.record_from.toLocaleString() }}
                      →
                      {{ worker.current_task.record_to.toLocaleString() }}
                    </span>
                  </div>
                  <span :class="['px-2 py-0.5 text-[10px] font-bold rounded-full flex items-center gap-1',
                    taskStatusBadge(worker.current_task.status)]">
                    <span :class="['w-1.5 h-1.5 rounded-full', taskStatusDot(worker.current_task.status)]"></span>
                    {{ worker.current_task.status.toUpperCase() }}
                  </span>
                </div>

                <!-- Animated progress bar (time-based estimate) -->
                <div class="w-full bg-blue-100 rounded-full h-2 overflow-hidden">
                  <div
                    :class="['h-2 rounded-full transition-all duration-1000', workerColor(idx).bar]"
                    :style="{ width: taskProgressPct(worker.current_task) + '%' }"
                  ></div>
                </div>
                <div class="flex justify-between mt-1">
                  <span class="text-[10px] text-gray-400">
                    {{ worker.current_task.records_count ?? '—' }} records
                  </span>
                  <span class="text-[10px] text-gray-400">
                    {{ formatElapsed(worker.current_task.elapsed_ms) }}
                  </span>
                </div>
              </div>

              <!-- Completed count -->
              <div class="flex-shrink-0 text-right">
                <p class="text-xs text-gray-400">Done</p>
                <p class="text-base font-bold text-green-600">{{ worker.tasks_completed }}</p>
              </div>
            </div>
          </div>

        <!-- Idle workers -->
          <div v-for="worker in idleWorkers" :key="worker.worker_key"
            class="flex items-center gap-4 px-4 py-3 rounded-xl border border-dashed border-gray-200 bg-gray-50 opacity-60">

            <div class="flex items-center gap-2.5 flex-shrink-0 w-32">
              <div class="w-9 h-9 bg-gray-300 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
              </div>
              <div>
                <p class="text-xs font-bold text-gray-600 leading-tight">{{ worker.worker_key }}</p>
                <span class="text-[10px] font-bold text-gray-400">IDLE</span>
              </div>
            </div>

            <div class="flex-shrink-0 flex flex-col items-center gap-0.5">
              <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                  d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                  clip-rule="evenodd"/>
              </svg>
              <span class="text-[9px] text-gray-300 font-medium">asking…</span>
            </div>

            <div class="flex-1">
              <div class="bg-white border border-dashed border-gray-200 rounded-lg px-3 py-2 text-center">
                <p class="text-xs text-gray-400 italic">Waiting for next task…</p>
                <p class="text-[10px] text-gray-300 font-mono mt-0.5">GET /tasks/next</p>
              </div>
            </div>

            <div class="flex-shrink-0 text-right">
              <p class="text-xs text-gray-400">Done</p>
              <p class="text-base font-bold text-green-600">{{ worker.tasks_completed }}</p>
            </div>
          </div>

          <!-- Dead workers — crashed, tasks auto-recovered -->
          <div v-for="worker in deadWorkers" :key="'dead-' + worker.worker_key"
            class="flex items-center gap-4 px-4 py-3 rounded-xl border-2 border-red-200 bg-red-50/50">

            <div class="flex items-center gap-2.5 flex-shrink-0 w-32">
              <div class="w-9 h-9 bg-red-500 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
              </div>
              <div>
                <p class="text-xs font-bold text-red-800 leading-tight">{{ worker.worker_key }}</p>
                <span class="text-[10px] font-bold text-red-600">☠ DEAD</span>
              </div>
            </div>

            <div class="flex-1">
              <div class="bg-white border border-red-200 rounded-lg px-3 py-2">
                <p class="text-xs font-semibold text-red-700">Worker crashed or disconnected</p>
                <p class="text-[11px] text-red-600 mt-0.5">
                  Its task was automatically reset to
                  <span class="font-bold bg-yellow-100 text-yellow-800 px-1 rounded">pending</span>
                  — another worker will pick it up. <strong>No task lost.</strong>
                </p>
              </div>
            </div>

            <div class="flex-shrink-0 text-right">
              <p class="text-xs text-gray-400">Done</p>
              <p class="text-base font-bold text-green-600">{{ worker.tasks_completed }}</p>
            </div>
          </div>

        </div>

        <!-- ── Speed comparison ────────────────────────────────────────────── -->
        <div v-if="parallel && parallel.active_workers > 0"
          class="bg-gradient-to-br from-gray-50 to-white rounded-xl border border-gray-100 p-4">
          <p class="text-xs font-semibold text-gray-700 mb-3 uppercase tracking-wide">
            Why parallel is faster
          </p>
          <div class="space-y-3">

            <!-- Sequential (1 worker) -->
            <div>
              <div class="flex justify-between text-xs mb-1">
                <span class="text-gray-500 flex items-center gap-1.5">
                  <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
                  Sequential (1 worker)
                </span>
                <span class="font-medium text-gray-700">
                  {{ formatDuration(parallel.sequential_estimate_ms) }}
                </span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="bg-gray-400 h-3 rounded-full" style="width: 100%"></div>
              </div>
            </div>

            <!-- Parallel (N workers) -->
            <div>
              <div class="flex justify-between text-xs mb-1">
                <span class="text-indigo-600 flex items-center gap-1.5 font-semibold">
                  <span class="w-2 h-2 bg-indigo-500 rounded-full animate-pulse"></span>
                  Parallel ({{ parallel.active_workers }} workers)
                </span>
                <span class="font-bold text-indigo-600">
                  {{ formatDuration(parallel.parallel_estimate_ms) }}
                </span>
              </div>
              <div class="w-full bg-indigo-100 rounded-full h-3 overflow-hidden">
                <div
                  class="bg-gradient-to-r from-indigo-500 to-purple-500 h-3 rounded-full transition-all duration-700"
                  :style="{ width: parallelBarWidth + '%' }"
                ></div>
              </div>
            </div>

            <!-- Speed gain label -->
            <div class="flex items-center justify-between pt-1 border-t border-gray-100">
              <span class="text-xs text-gray-500">Time saved</span>
              <span class="text-sm font-bold text-green-600">
                ~{{ parallel.speed_multiplier }}× faster
              </span>
            </div>
          </div>
        </div>

        <!-- No workers hint -->
        <div v-if="busyWorkers.length === 0 && pendingTasksAvailable > 0"
          class="mt-4 bg-gray-900 rounded-xl p-4">
          <p class="text-xs text-gray-400 mb-2 font-mono"># Tasks are waiting. Start workers:</p>
          <p class="text-xs text-green-400 font-mono">php artisan worker:run --key=worker-1</p>
          <p class="text-xs text-green-400 font-mono">php artisan worker:run --key=worker-2</p>
          <p class="text-xs text-green-400 font-mono">php artisan worker:run --key=worker-3</p>
        </div>

      </div>
    </div>

    <!-- ── Footer ─────────────────────────────────────────────────────────── -->
    <div class="px-6 py-2.5 border-t border-gray-100 flex items-center justify-between bg-gray-50/50">
      <p class="text-xs text-gray-400">Snapshot: {{ snapshotTime }}</p>
      <p class="text-xs text-gray-400">Refreshes every 3s</p>
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
import apiClient from '@/api/axios'
import { useAuthStore } from '@/stores/authStore'
import WorkerManagementModal from './modals/WorkerManagementModal.vue'

// ── Props ─────────────────────────────────────────────────────────────────────
defineProps({
  jobId: { type: [Number, String], default: null },
})

// ── State ─────────────────────────────────────────────────────────────────────
const activity     = ref(null)
const loading      = ref(false)
const snapshotTime = ref('—')
const showWorkerModal = ref(false)
let   pollTimer    = null

// ── Auth ──────────────────────────────────────────────────────────────────────
const authStore = useAuthStore()
const isAdmin = computed(() => authStore.user?.is_admin === true || authStore.user?.is_admin === 1)

// ── Methods ───────────────────────────────────────────────────────────────────
const openWorkerModal = () => {
  showWorkerModal.value = true
}

const handleWorkersStarted = () => {
  refresh()
}

// ── Computed ──────────────────────────────────────────────────────────────────
const busyWorkers           = computed(() => activity.value?.busy_workers           ?? [])
const idleWorkers           = computed(() => activity.value?.idle_workers           ?? [])
const deadWorkers           = computed(() => activity.value?.dead_workers           ?? [])
const totalWorkers          = computed(() => activity.value?.total_workers          ?? 0)
const pendingTasksAvailable = computed(() => activity.value?.pending_tasks_available ?? 0)
const parallel              = computed(() => activity.value?.parallel               ?? null)

// Width of the parallel bar relative to sequential (always shorter = better)
const parallelBarWidth = computed(() => {
  if (!parallel.value?.active_workers || parallel.value.active_workers <= 1) return 100
  return Math.max(5, Math.round(100 / parallel.value.active_workers))
})

// ── Worker colour palette (cycles through 5 colours) ─────────────────────────
const WORKER_COLORS = [
  { bg: 'bg-blue-600',   badge: 'bg-blue-100 text-blue-800',   bar: 'bg-blue-500'   },
  { bg: 'bg-purple-600', badge: 'bg-purple-100 text-purple-800', bar: 'bg-purple-500' },
  { bg: 'bg-green-600',  badge: 'bg-green-100 text-green-800',  bar: 'bg-green-500'  },
  { bg: 'bg-orange-500', badge: 'bg-orange-100 text-orange-800', bar: 'bg-orange-500' },
  { bg: 'bg-pink-600',   badge: 'bg-pink-100 text-pink-800',    bar: 'bg-pink-500'   },
]
function workerColor(idx) {
  return WORKER_COLORS[idx % WORKER_COLORS.length]
}

// ── Task progress estimate (time-based, 0-95%) ────────────────────────────────
function taskProgressPct(task) {
  if (!task?.elapsed_ms || !task?.timeout_ms) return 10
  // Cap at 95% — we don't know exact completion time
  return Math.min(95, Math.round((task.elapsed_ms / task.timeout_ms) * 100))
}

// ── Fetch ─────────────────────────────────────────────────────────────────────
async function refresh() {
  loading.value = true
  try {
    const response = await apiClient.get('/tasks/activity')
    activity.value = response.data
    snapshotTime.value = new Date().toLocaleTimeString()
  } catch {
    // Non-critical panel — fail silently
  } finally {
    loading.value = false
  }
}

// ── Formatters ────────────────────────────────────────────────────────────────
function taskStatusBadge(status) {
  return {
    assigned: 'bg-blue-100 text-blue-800',
    running:  'bg-indigo-100 text-indigo-800',
    done:     'bg-green-100 text-green-800',
    pending:  'bg-yellow-100 text-yellow-800',
    failed:   'bg-red-100 text-red-800',
  }[status] ?? 'bg-gray-100 text-gray-600'
}

function taskStatusDot(status) {
  return {
    assigned: 'bg-blue-500',
    running:  'bg-indigo-500 animate-pulse',
    done:     'bg-green-500',
    pending:  'bg-yellow-500',
    failed:   'bg-red-500',
  }[status] ?? 'bg-gray-400'
}

function formatElapsed(ms) {
  if (!ms) return '0s'
  const s = Math.floor(ms / 1000)
  if (s < 60) return `${s}s`
  return `${Math.floor(s / 60)}m ${s % 60}s`
}

function formatDuration(ms) {
  if (!ms) return '—'
  const s = Math.round(ms / 1000)
  if (s < 60) return `${s}s`
  const m = Math.floor(s / 60)
  if (m < 60) return `${m}m ${s % 60}s`
  return `${Math.floor(m / 60)}h ${m % 60}m`
}

// ── Lifecycle ─────────────────────────────────────────────────────────────────
onMounted(() => {
  refresh()
  pollTimer = setInterval(refresh, 3000)
})
onUnmounted(() => {
  if (pollTimer) clearInterval(pollTimer)
})
</script>

<style scoped>
/* Add any component-specific styles here if needed */
</style>
