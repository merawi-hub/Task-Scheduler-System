<template>
  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

    <!-- ── Header ─────────────────────────────────────────────────────────── -->
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
          <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
        </div>
        <div>
          <h3 class="text-sm font-semibold text-gray-900">Retry Logic — Exponential Backoff</h3>
          <p class="text-xs text-gray-500">Failed tasks are automatically retried with increasing delays</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
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
    <div v-if="loading && !data" class="text-center py-10">
      <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-orange-500"></div>
      <p class="text-xs text-gray-500 mt-2">Loading retry data…</p>
    </div>

    <!-- ── No retries yet ─────────────────────────────────────────────────── -->
    <div v-else-if="!hasAnyRetries" class="px-6 py-8 text-center">
      <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
      </div>
      <p class="text-sm font-semibold text-gray-700">No retries needed</p>
      <p class="text-xs text-gray-500 mt-1">All tasks are running cleanly — no failures detected.</p>
    </div>

    <div v-else class="p-6 space-y-6">

      <!-- ── Summary cards ──────────────────────────────────────────────── -->
      <div class="grid grid-cols-4 gap-3">
        <div class="bg-orange-50 rounded-xl p-3 text-center border border-orange-100">
          <p class="text-xl font-bold text-orange-600">{{ summary.total_retried }}</p>
          <p class="text-xs text-gray-500 mt-0.5">Retried</p>
        </div>
        <div class="bg-yellow-50 rounded-xl p-3 text-center border border-yellow-100">
          <p class="text-xl font-bold text-yellow-600">{{ summary.in_backoff }}</p>
          <p class="text-xs text-gray-500 mt-0.5">In Backoff</p>
        </div>
        <div class="bg-green-50 rounded-xl p-3 text-center border border-green-100">
          <p class="text-xl font-bold text-green-600">{{ summary.recovered }}</p>
          <p class="text-xs text-gray-500 mt-0.5">Recovered ✓</p>
        </div>
        <div class="bg-red-50 rounded-xl p-3 text-center border border-red-100">
          <p class="text-xl font-bold text-red-600">{{ summary.permanently_failed }}</p>
          <p class="text-xs text-gray-500 mt-0.5">Permanent ✗</p>
        </div>
      </div>

      <!-- ── Backoff schedule ────────────────────────────────────────────── -->
      <div>
        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-3">
          Backoff Schedule
          <span class="ml-1 font-normal text-gray-400 normal-case font-mono">
            formula: 2^attempt × 5s
          </span>
        </p>

        <!-- Visual timeline -->
        <div class="flex items-end gap-3 mb-4">
          <div v-for="step in backoffSchedule" :key="step.attempt"
            class="flex flex-col items-center flex-1">
            <!-- Bar height proportional to delay -->
            <div class="w-full flex flex-col items-center">
              <span class="text-xs font-bold text-orange-600 mb-1">{{ step.delay_label }}</span>
              <div
                class="w-full bg-gradient-to-t from-orange-500 to-orange-300 rounded-t-lg transition-all"
                :style="{ height: barHeight(step.delay_seconds) + 'px' }"
              ></div>
            </div>
            <div class="mt-2 text-center">
              <p class="text-[10px] font-bold text-gray-600">Attempt {{ step.attempt }}</p>
              <p class="text-[9px] text-gray-400">2^{{ step.attempt - 1 }} × 5</p>
            </div>
          </div>
        </div>

        <!-- Formula explanation -->
        <div class="bg-gray-900 rounded-xl p-4">
          <p class="text-xs text-gray-400 mb-2">// Exponential backoff formula (Task.php)</p>
          <p class="text-xs text-green-400 font-mono">
            public function calculateBackoffDelay(): int
          </p>
          <p class="text-xs text-blue-300 font-mono pl-4">
            return (int) (pow(2, $this->retry_count) * 5);
          </p>
          <div class="mt-3 space-y-1">
            <p v-for="step in backoffSchedule" :key="step.attempt"
              class="text-[11px] text-gray-400 font-mono">
              // Attempt {{ step.attempt }}: 2^{{ step.attempt - 1 }} × 5 =
              <span class="text-yellow-400">{{ step.delay_seconds }}s</span>
            </p>
          </div>
        </div>
      </div>

      <!-- ── Tasks currently in backoff cooldown ─────────────────────────── -->
      <div v-if="backoffTasks.length > 0">
        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-3">
          Tasks in Backoff Cooldown
          <span class="ml-1 font-normal text-gray-400 normal-case">(waiting before next retry)</span>
        </p>
        <div class="space-y-2">
          <div v-for="task in backoffTasks" :key="task.task_id"
            class="p-3 bg-yellow-50 border border-yellow-200 rounded-xl">
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center gap-2">
                <span class="font-mono text-xs font-bold text-gray-800">Task #{{ task.task_number }}</span>
                <span class="px-2 py-0.5 bg-yellow-100 text-yellow-800 text-[10px] font-bold rounded-full">
                  PENDING (retry {{ task.retry_count }}/{{ task.max_retries }})
                </span>
              </div>
              <span class="text-xs font-bold text-orange-600">
                retry in {{ task.seconds_until_available }}s
              </span>
            </div>
            <!-- Countdown bar -->
            <div class="w-full bg-yellow-100 rounded-full h-2 overflow-hidden">
              <div
                class="bg-orange-400 h-2 rounded-full transition-all duration-1000"
                :style="{ width: backoffCountdownWidth(task) + '%' }"
              ></div>
            </div>
            <p class="text-[10px] text-yellow-700 mt-1.5 truncate">
              Reason: {{ task.failure_reason || 'Unknown error' }}
            </p>
          </div>
        </div>
      </div>

      <!-- ── Retry history ───────────────────────────────────────────────── -->
      <div v-if="retriedTasks.length > 0">
        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-3">
          Retry History
        </p>
        <div class="space-y-2">
          <div v-for="task in retriedTasks" :key="task.task_id"
            :class="['p-3 rounded-xl border', task.is_permanent ? 'bg-red-50 border-red-200' :
              task.status === 'done' ? 'bg-green-50 border-green-200' : 'bg-orange-50 border-orange-200']">

            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="font-mono text-xs font-bold text-gray-800">Task #{{ task.task_number }}</span>
                <span v-if="task.record_from != null"
                  class="font-mono text-[10px] text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded">
                  records {{ task.record_from }}→{{ task.record_to }}
                </span>
              </div>

              <!-- Outcome badge -->
              <span v-if="task.is_permanent"
                class="px-2 py-0.5 bg-red-100 text-red-800 text-[10px] font-bold rounded-full">
                ✗ PERMANENT FAILURE
              </span>
              <span v-else-if="task.status === 'done'"
                class="px-2 py-0.5 bg-green-100 text-green-800 text-[10px] font-bold rounded-full">
                ✓ RECOVERED
              </span>
              <span v-else
                class="px-2 py-0.5 bg-orange-100 text-orange-800 text-[10px] font-bold rounded-full">
                ↻ RETRYING ({{ task.retry_count }}/{{ task.max_retries }})
              </span>
            </div>

            <!-- Retry attempt dots -->
            <div class="flex items-center gap-1.5 mt-2">
              <span class="text-[10px] text-gray-500 mr-1">Attempts:</span>
              <div v-for="n in task.max_retries" :key="n"
                :class="['w-4 h-4 rounded-full flex items-center justify-center text-[9px] font-bold',
                  n <= task.retry_count
                    ? (task.is_permanent && n === task.retry_count ? 'bg-red-500 text-white' : 'bg-orange-400 text-white')
                    : 'bg-gray-100 text-gray-400']">
                {{ n }}
              </div>
              <div v-if="task.status === 'done'"
                class="w-4 h-4 rounded-full bg-green-500 flex items-center justify-center text-[9px] text-white font-bold">
                ✓
              </div>
            </div>

            <p v-if="task.failure_reason" class="text-[10px] text-gray-500 mt-1.5 truncate">
              {{ task.failure_reason }}
            </p>
          </div>
        </div>
      </div>

      <!-- ── Why exponential backoff ─────────────────────────────────────── -->
      <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
        <p class="text-xs font-semibold text-blue-900 mb-2">Why exponential backoff?</p>
        <div class="space-y-1.5 text-xs text-blue-800">
          <p>
            <span class="font-semibold">Immediate retry</span> would hammer a failing service
            (e.g. database down) and make it worse.
          </p>
          <p>
            <span class="font-semibold">Exponential backoff</span> gives the service time to recover:
            5s → 10s → 20s → 40s.
          </p>
          <p>
            <span class="font-semibold">Max {{ maxRetries }} retries</span> prevents infinite loops.
            After that, the task is permanently failed.
          </p>
        </div>
      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import apiClient from '@/api/axios'

// ── Props ─────────────────────────────────────────────────────────────────────
const props = defineProps({
  jobId: { type: [Number, String], required: true },
})

// ── State ─────────────────────────────────────────────────────────────────────
const data    = ref(null)
const loading = ref(false)
let   timer   = null

// ── Computed ──────────────────────────────────────────────────────────────────
const backoffSchedule = computed(() => data.value?.backoff_schedule ?? [])
const backoffTasks    = computed(() => data.value?.backoff_tasks    ?? [])
const retriedTasks    = computed(() => data.value?.retried_tasks    ?? [])
const summary         = computed(() => data.value?.summary ?? {
  total_retried: 0, in_backoff: 0, recovered: 0, permanently_failed: 0,
})
const maxRetries = computed(() => data.value?.max_retries ?? 3)

const hasAnyRetries = computed(() =>
  summary.value.total_retried > 0 ||
  summary.value.in_backoff > 0 ||
  summary.value.permanently_failed > 0
)

// ── Fetch ─────────────────────────────────────────────────────────────────────
async function refresh() {
  loading.value = true
  try {
    const response = await apiClient.get(`/jobs/${props.jobId}/retry-stats`)
    data.value = response.data
  } catch {
    // Non-critical
  } finally {
    loading.value = false
  }
}

// ── Helpers ───────────────────────────────────────────────────────────────────

// Bar height for the backoff schedule chart (max 80px for the longest delay)
function barHeight(seconds) {
  const maxDelay = backoffSchedule.value.reduce((m, s) => Math.max(m, s.delay_seconds), 1)
  return Math.max(8, Math.round((seconds / maxDelay) * 80))
}

// Countdown bar: full = just failed, empty = ready to retry
function backoffCountdownWidth(task) {
  const total = task.next_delay_seconds ?? 5
  const remaining = task.seconds_until_available ?? 0
  return Math.max(0, Math.round((remaining / total) * 100))
}

// ── Lifecycle ─────────────────────────────────────────────────────────────────
onMounted(() => {
  refresh()
  // Poll every 3s when tasks are in backoff, otherwise every 10s
  timer = setInterval(() => {
    refresh()
  }, backoffTasks.value.length > 0 ? 3000 : 10000)
})
onUnmounted(() => {
  if (timer) clearInterval(timer)
})
</script>
