<template>
  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

    <!-- ── Header ─────────────────────────────────────────────────────────── -->
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
          <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
          </svg>
        </div>
        <div>
          <h3 class="text-sm font-semibold text-gray-900">Task Status — Live</h3>
          <p class="text-xs text-gray-500">Real-time status transitions as workers process tasks</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <!-- Live pulse when job is running -->
        <div v-if="isRunning" class="flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 border border-blue-200 rounded-full">
          <span class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-pulse"></span>
          <span class="text-xs font-medium text-blue-700">Updating</span>
        </div>
        <div v-else-if="isCompleted" class="flex items-center gap-1.5 px-2.5 py-1 bg-green-50 border border-green-200 rounded-full">
          <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
          <span class="text-xs font-medium text-green-700">Completed</span>
        </div>
      </div>
    </div>

    <!-- ── State machine diagram ───────────────────────────────────────────── -->
    <div class="px-6 py-5 border-b border-gray-100">
      <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">
        Status Flow
      </p>

      <!-- Flow: pending → assigned → running → done -->
      <div class="flex items-center gap-2">

        <!-- PENDING -->
        <div class="flex flex-col items-center flex-1">
          <div :class="[
            'w-full rounded-xl p-3 text-center border-2 transition-all',
            counts.pending > 0
              ? 'bg-yellow-50 border-yellow-300'
              : 'bg-gray-50 border-gray-100'
          ]">
            <p :class="['text-2xl font-bold', counts.pending > 0 ? 'text-yellow-600' : 'text-gray-300']">
              {{ counts.pending }}
            </p>
            <p class="text-xs font-semibold text-gray-500 mt-0.5">PENDING</p>
            <p class="text-[10px] text-gray-400 mt-0.5">waiting in queue</p>
          </div>
        </div>

        <!-- Arrow -->
        <div class="flex flex-col items-center flex-shrink-0">
          <svg class="w-5 h-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
              d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
              clip-rule="evenodd"/>
          </svg>
          <p class="text-[9px] text-gray-300 mt-0.5">worker pulls</p>
        </div>

        <!-- ASSIGNED -->
        <div class="flex flex-col items-center flex-1">
          <div :class="[
            'w-full rounded-xl p-3 text-center border-2 transition-all',
            counts.assigned > 0
              ? 'bg-blue-50 border-blue-300'
              : 'bg-gray-50 border-gray-100'
          ]">
            <p :class="['text-2xl font-bold', counts.assigned > 0 ? 'text-blue-600' : 'text-gray-300']">
              {{ counts.assigned }}
            </p>
            <p class="text-xs font-semibold text-gray-500 mt-0.5">ASSIGNED</p>
            <p class="text-[10px] text-gray-400 mt-0.5">worker claimed it</p>
          </div>
        </div>

        <!-- Arrow -->
        <div class="flex flex-col items-center flex-shrink-0">
          <svg class="w-5 h-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
              d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
              clip-rule="evenodd"/>
          </svg>
          <p class="text-[9px] text-gray-300 mt-0.5">worker starts</p>
        </div>

        <!-- RUNNING -->
        <div class="flex flex-col items-center flex-1">
          <div :class="[
            'w-full rounded-xl p-3 text-center border-2 transition-all',
            counts.running > 0
              ? 'bg-indigo-50 border-indigo-300'
              : 'bg-gray-50 border-gray-100'
          ]">
            <p :class="['text-2xl font-bold', counts.running > 0 ? 'text-indigo-600' : 'text-gray-300']">
              {{ counts.running }}
            </p>
            <p class="text-xs font-semibold text-gray-500 mt-0.5">RUNNING</p>
            <p class="text-[10px] text-gray-400 mt-0.5">being processed</p>
          </div>
        </div>

        <!-- Arrow -->
        <div class="flex flex-col items-center flex-shrink-0">
          <svg class="w-5 h-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
              d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
              clip-rule="evenodd"/>
          </svg>
          <p class="text-[9px] text-gray-300 mt-0.5">worker completes</p>
        </div>

        <!-- DONE -->
        <div class="flex flex-col items-center flex-1">
          <div :class="[
            'w-full rounded-xl p-3 text-center border-2 transition-all',
            counts.done > 0
              ? 'bg-green-50 border-green-300'
              : 'bg-gray-50 border-gray-100'
          ]">
            <p :class="['text-2xl font-bold', counts.done > 0 ? 'text-green-600' : 'text-gray-300']">
              {{ counts.done }}
            </p>
            <p class="text-xs font-semibold text-gray-500 mt-0.5">DONE</p>
            <p class="text-[10px] text-gray-400 mt-0.5">successfully finished</p>
          </div>
        </div>

        <!-- Failed (side branch) -->
        <div v-if="counts.failed > 0" class="flex flex-col items-center flex-shrink-0 ml-2">
          <div class="w-16 rounded-xl p-3 text-center border-2 bg-red-50 border-red-300">
            <p class="text-2xl font-bold text-red-600">{{ counts.failed }}</p>
            <p class="text-xs font-semibold text-gray-500 mt-0.5">FAILED</p>
          </div>
        </div>

      </div>

      <!-- Overall progress bar -->
      <div class="mt-4">
        <div class="flex justify-between text-xs text-gray-500 mb-1.5">
          <span>Overall Progress</span>
          <span class="font-semibold text-gray-700">
            {{ counts.done }} / {{ totalTasks }} tasks done
            ({{ progressPct }}%)
          </span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
          <!-- Done segment -->
          <div class="h-3 rounded-full flex overflow-hidden transition-all duration-700">
            <div class="bg-green-500 transition-all duration-700"
              :style="{ width: donePct + '%' }"></div>
            <div class="bg-indigo-400 transition-all duration-700"
              :style="{ width: runningPct + '%' }"></div>
            <div class="bg-blue-300 transition-all duration-700"
              :style="{ width: assignedPct + '%' }"></div>
            <div class="bg-red-400 transition-all duration-700"
              :style="{ width: failedPct + '%' }"></div>
          </div>
        </div>
        <div class="flex items-center gap-4 mt-2">
          <span class="flex items-center gap-1 text-[10px] text-gray-500">
            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Done
          </span>
          <span class="flex items-center gap-1 text-[10px] text-gray-500">
            <span class="w-2 h-2 bg-indigo-400 rounded-full"></span> Running
          </span>
          <span class="flex items-center gap-1 text-[10px] text-gray-500">
            <span class="w-2 h-2 bg-blue-300 rounded-full"></span> Assigned
          </span>
          <span v-if="counts.failed > 0" class="flex items-center gap-1 text-[10px] text-gray-500">
            <span class="w-2 h-2 bg-red-400 rounded-full"></span> Failed
          </span>
        </div>
      </div>
    </div>

    <!-- ── Recent transitions feed ────────────────────────────────────────── -->
    <div class="px-6 py-4">
      <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
        Recent Activity
        <span class="ml-1 font-normal text-gray-400 normal-case">(live log)</span>
      </p>

      <div v-if="!recentTransitions.length" class="text-center py-6">
        <p class="text-xs text-gray-400">No activity yet — waiting for workers to start processing</p>
      </div>

      <div v-else class="space-y-2 max-h-48 overflow-y-auto">
        <div v-for="(entry, idx) in recentTransitions" :key="idx"
          class="flex items-start gap-3 py-1.5 border-b border-gray-50 last:border-0">

          <!-- Level dot -->
          <div :class="['w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5',
            entry.level === 'error'   ? 'bg-red-100' :
            entry.level === 'warning' ? 'bg-yellow-100' :
            'bg-green-100']">
            <span :class="['w-2 h-2 rounded-full',
              entry.level === 'error'   ? 'bg-red-500' :
              entry.level === 'warning' ? 'bg-yellow-500' :
              'bg-green-500']"></span>
          </div>

          <!-- Message -->
          <div class="flex-1 min-w-0">
            <p class="text-xs text-gray-700 leading-snug">{{ entry.message }}</p>
            <div class="flex items-center gap-2 mt-0.5">
              <span v-if="entry.worker_key" class="text-[10px] font-mono text-indigo-500">
                {{ entry.worker_key }}
              </span>
              <span v-if="entry.task_index != null" class="text-[10px] text-gray-400">
                Task #{{ entry.task_index + 1 }}
              </span>
            </div>
          </div>

          <!-- Time -->
          <span class="text-[10px] text-gray-400 flex-shrink-0">
            {{ formatTime(entry.logged_at) }}
          </span>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import apiClient from '@/api/axios'

// ── Props ─────────────────────────────────────────────────────────────────────
const props = defineProps({
  jobId: { type: [Number, String], required: true },
  jobStatus: { type: String, default: 'pending' },
})

// ── State ─────────────────────────────────────────────────────────────────────
const pollData         = ref(null)
const recentTransitions = ref([])
let   pollTimer        = null

// ── Computed ──────────────────────────────────────────────────────────────────
const isRunning   = computed(() => ['pending', 'running'].includes(props.jobStatus))
const isCompleted = computed(() => props.jobStatus === 'completed')

const counts = computed(() => pollData.value?.task_counts ?? {
  pending: 0, assigned: 0, running: 0, done: 0, failed: 0, cancelled: 0,
})

const totalTasks  = computed(() => pollData.value?.total_tasks ?? 0)
const progressPct = computed(() => pollData.value?.progress ?? 0)

// Segment widths for the stacked progress bar
const donePct     = computed(() => totalTasks.value ? Math.round((counts.value.done     / totalTasks.value) * 100) : 0)
const runningPct  = computed(() => totalTasks.value ? Math.round((counts.value.running  / totalTasks.value) * 100) : 0)
const assignedPct = computed(() => totalTasks.value ? Math.round((counts.value.assigned / totalTasks.value) * 100) : 0)
const failedPct   = computed(() => totalTasks.value ? Math.round((counts.value.failed   / totalTasks.value) * 100) : 0)

// ── Fetch ─────────────────────────────────────────────────────────────────────
async function poll() {
  try {
    const response = await apiClient.get(`/jobs/${props.jobId}/status`)
    pollData.value = response.data
    // Prepend new transitions, keep last 30
    const incoming = response.data.recent_transitions ?? []
    recentTransitions.value = incoming.slice(0, 30)
  } catch {
    // Non-critical
  }
}

// ── Polling interval — faster when running, slower when done ─────────────────
function startPolling() {
  stopPolling()
  const interval = isRunning.value ? 2000 : 10000
  pollTimer = setInterval(poll, interval)
}

function stopPolling() {
  if (pollTimer) { clearInterval(pollTimer); pollTimer = null }
}

// Restart polling when job status changes
watch(() => props.jobStatus, () => startPolling())

// ── Formatters ────────────────────────────────────────────────────────────────
function formatTime(iso) {
  if (!iso) return ''
  const date = new Date(iso)
  const now  = new Date()
  const s    = Math.floor((now - date) / 1000)
  if (s < 5)  return 'just now'
  if (s < 60) return `${s}s ago`
  const m = Math.floor(s / 60)
  if (m < 60) return `${m}m ago`
  return `${Math.floor(m / 60)}h ago`
}

// ── Lifecycle ─────────────────────────────────────────────────────────────────
onMounted(() => {
  poll()
  startPolling()
})
onUnmounted(() => stopPolling())
</script>
