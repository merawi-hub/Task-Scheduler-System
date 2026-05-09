<template>
  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

    <!-- ── Header ─────────────────────────────────────────────────────────── -->
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
          <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
          </svg>
        </div>
        <div>
          <h3 class="text-sm font-semibold text-gray-900">Fault Tolerance — Heartbeat Monitor</h3>
          <p class="text-xs text-gray-500">Workers send heartbeats every {{ heartbeatInterval }}s · declared dead after {{ thresholdSeconds }}s silence</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
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

    <!-- ── How it works banner ────────────────────────────────────────────── -->
    <div class="px-6 py-3 bg-orange-50 border-b border-orange-100">
      <div class="flex items-start gap-3">
        <span class="text-base flex-shrink-0">💡</span>
        <p class="text-xs text-orange-800">
          <strong>Fault tolerance:</strong> Every {{ heartbeatInterval }}s workers send
          <code class="bg-orange-100 px-1 rounded font-mono">POST /workers/{key}/heartbeat</code>.
          If {{ thresholdSeconds }}s pass with no heartbeat → worker declared <strong>dead</strong>
          → its task is reset to <strong>pending</strong> → another worker picks it up.
          <strong>No task is ever lost.</strong>
        </p>
      </div>
    </div>

    <!-- ── Loading ────────────────────────────────────────────────────────── -->
    <div v-if="loading && !data" class="text-center py-10">
      <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-red-500"></div>
      <p class="text-xs text-gray-500 mt-2">Loading fault tolerance data…</p>
    </div>

    <div v-else class="p-6 space-y-6">

      <!-- ── Heartbeat timeline per worker ──────────────────────────────── -->
      <div>
        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-3">
          Worker Heartbeat Status
        </p>

        <div v-if="!workers.length" class="text-center py-6 text-gray-400 text-xs">
          No workers registered yet.
        </div>

        <div v-else class="space-y-3">
          <div v-for="worker in workers" :key="worker.worker_key"
            :class="['rounded-xl border-2 p-4 transition-all', workerCardClass(worker)]">

            <div class="flex items-center gap-4">

              <!-- Worker icon + name -->
              <div class="flex items-center gap-2.5 flex-shrink-0 w-36">
                <div :class="['w-9 h-9 rounded-lg flex items-center justify-center', workerIconBg(worker)]">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-xs font-bold text-gray-800 leading-tight">{{ worker.worker_key }}</p>
                  <span :class="['text-[10px] font-bold', healthTextColor(worker.heartbeat_health)]">
                    {{ worker.status.toUpperCase() }}
                  </span>
                </div>
              </div>

              <!-- Heartbeat countdown bar -->
              <div class="flex-1">
                <div class="flex items-center justify-between mb-1">
                  <span class="text-xs text-gray-500">
                    Last heartbeat:
                    <span :class="['font-semibold', healthTextColor(worker.heartbeat_health)]">
                      {{ formatSeconds(worker.seconds_since_heartbeat) }}
                    </span>
                  </span>
                  <span v-if="worker.status !== 'dead' && worker.seconds_until_dead > 0"
                    class="text-[10px] text-gray-400">
                    dead in {{ worker.seconds_until_dead }}s
                  </span>
                  <span v-else-if="worker.status === 'dead'"
                    class="text-[10px] font-bold text-red-600">
                    ☠ DEAD — tasks released
                  </span>
                </div>

                <!-- Countdown bar: fills red as time runs out -->
                <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                  <div
                    :class="['h-2.5 rounded-full transition-all duration-1000', heartbeatBarColor(worker)]"
                    :style="{ width: heartbeatBarWidth(worker) + '%' }"
                  ></div>
                </div>

                <!-- Tick marks at 15s, 30s, 45s -->
                <div class="flex justify-between mt-0.5 px-0.5">
                  <span class="text-[9px] text-gray-300">0s</span>
                  <span class="text-[9px] text-gray-300">15s</span>
                  <span class="text-[9px] text-gray-300">30s</span>
                  <span class="text-[9px] text-red-300 font-semibold">{{ thresholdSeconds }}s ☠</span>
                </div>
              </div>

              <!-- Health badge -->
              <div class="flex-shrink-0">
                <span :class="['px-2.5 py-1 text-[10px] font-bold rounded-full', healthBadge(worker.heartbeat_health)]">
                  {{ healthLabel(worker.heartbeat_health) }}
                </span>
              </div>

            </div>

            <!-- Dead worker recovery message -->
            <div v-if="worker.status === 'dead'"
              class="mt-3 flex items-start gap-2 p-2.5 bg-red-50 border border-red-200 rounded-lg">
              <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
              <p class="text-xs text-red-700">
                Worker crashed or disconnected. Its task has been automatically
                reset to <strong>pending</strong> and will be picked up by another worker.
                <strong>No data lost.</strong>
              </p>
            </div>

          </div>
        </div>
      </div>

      <!-- ── Recovery log ────────────────────────────────────────────────── -->
      <div v-if="recoveredTasks.length > 0">
        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-3">
          Recovered Tasks
          <span class="ml-1 font-normal text-gray-400 normal-case">(tasks rescued from dead workers)</span>
        </p>
        <div class="space-y-2">
          <div v-for="task in recoveredTasks" :key="task.task_id"
            class="flex items-start gap-3 p-3 bg-amber-50 border border-amber-200 rounded-xl">
            <div class="w-7 h-7 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
              <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-gray-800">Task #{{ task.task_id }}</span>
                <span class="font-mono text-xs text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded">
                  Job #{{ task.job_id }}
                </span>
                <span :class="['px-2 py-0.5 text-[10px] font-bold rounded-full',
                  task.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                  task.status === 'done'    ? 'bg-green-100 text-green-800' :
                  'bg-gray-100 text-gray-600']">
                  {{ task.status.toUpperCase() }}
                </span>
              </div>
              <p class="text-[11px] text-amber-700 mt-0.5 truncate">{{ task.failure_reason }}</p>
              <p class="text-[10px] text-gray-400 mt-0.5">
                Retry {{ task.retry_count }} · Recovered {{ formatTime(task.recovered_at) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- ── How the recovery works ──────────────────────────────────────── -->
      <div class="bg-gray-50 rounded-xl border border-gray-100 p-4">
        <p class="text-xs font-semibold text-gray-700 uppercase tracking-wide mb-3">
          How fault tolerance works
        </p>
        <div class="space-y-3">

          <!-- Step 1 -->
          <div class="flex items-start gap-3">
            <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0 text-white text-[10px] font-bold">1</div>
            <div>
              <p class="text-xs font-semibold text-gray-800">Worker sends heartbeat every {{ heartbeatInterval }}s</p>
              <p class="text-[11px] text-gray-500 mt-0.5">
                <code class="bg-gray-100 px-1 rounded font-mono">POST /workers/{key}/heartbeat</code>
                — coordinator records <code class="bg-gray-100 px-1 rounded font-mono">last_heartbeat_at = now()</code>
              </p>
            </div>
          </div>

          <!-- Step 2 -->
          <div class="flex items-start gap-3">
            <div class="w-6 h-6 bg-yellow-500 rounded-full flex items-center justify-center flex-shrink-0 text-white text-[10px] font-bold">2</div>
            <div>
              <p class="text-xs font-semibold text-gray-800">Worker crashes — heartbeats stop</p>
              <p class="text-[11px] text-gray-500 mt-0.5">
                Power off, network gone, process killed. No heartbeat arrives.
              </p>
            </div>
          </div>

          <!-- Step 3 -->
          <div class="flex items-start gap-3">
            <div class="w-6 h-6 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0 text-white text-[10px] font-bold">3</div>
            <div>
              <p class="text-xs font-semibold text-gray-800">After {{ thresholdSeconds }}s — coordinator declares worker dead</p>
              <p class="text-[11px] text-gray-500 mt-0.5">
                Scheduler runs every minute:
                <code class="bg-gray-100 px-1 rounded font-mono">php artisan system:monitor-health</code>
              </p>
            </div>
          </div>

          <!-- Step 4 -->
          <div class="flex items-start gap-3">
            <div class="w-6 h-6 bg-indigo-500 rounded-full flex items-center justify-center flex-shrink-0 text-white text-[10px] font-bold">4</div>
            <div>
              <p class="text-xs font-semibold text-gray-800">Task reset: running → pending</p>
              <p class="text-[11px] text-gray-500 mt-0.5">
                The task is re-queued with exponential backoff. Another worker picks it up.
                <strong class="text-green-700">No task is lost.</strong>
              </p>
            </div>
          </div>

        </div>

        <!-- Run scheduler hint -->
        <div class="mt-4 bg-gray-900 rounded-xl p-3">
          <p class="text-[10px] text-gray-400 mb-1.5 font-mono"># Run the scheduler to enable automatic recovery:</p>
          <p class="text-xs text-green-400 font-mono">php artisan schedule:work</p>
          <p class="text-[10px] text-gray-500 mt-1 font-mono"># Or manually trigger health check:</p>
          <p class="text-xs text-green-400 font-mono">php artisan system:monitor-health</p>
        </div>
      </div>

    </div>

    <!-- ── Footer ─────────────────────────────────────────────────────────── -->
    <div class="px-6 py-2.5 border-t border-gray-100 flex items-center justify-between bg-gray-50/50">
      <p class="text-xs text-gray-400">Snapshot: {{ snapshotTime }}</p>
      <p class="text-xs text-gray-400">Refreshes every 5s</p>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import apiClient from '@/api/axios'

// ── State ─────────────────────────────────────────────────────────────────────
const data         = ref(null)
const loading      = ref(false)
const snapshotTime = ref('—')
let   pollTimer    = null

// ── Computed ──────────────────────────────────────────────────────────────────
const workers          = computed(() => data.value?.workers          ?? [])
const recoveredTasks   = computed(() => data.value?.recovered_tasks  ?? [])
const thresholdSeconds = computed(() => data.value?.threshold_seconds ?? 45)
const heartbeatInterval = computed(() => data.value?.heartbeat_interval ?? 30)

// ── Fetch ─────────────────────────────────────────────────────────────────────
async function refresh() {
  loading.value = true
  try {
    const response = await apiClient.get('/admin/workers/fault-tolerance')
    data.value = response.data
    snapshotTime.value = new Date().toLocaleTimeString()
  } catch {
    // Non-critical
  } finally {
    loading.value = false
  }
}

// ── Heartbeat bar helpers ─────────────────────────────────────────────────────

/**
 * Bar width: 100% = just heartbeated, 0% = at threshold (about to die)
 * Inverted: full bar = healthy, empty bar = dead
 */
function heartbeatBarWidth(worker) {
  if (worker.status === 'dead') return 0
  const s = worker.seconds_since_heartbeat ?? 0
  const t = thresholdSeconds.value
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

// ── Card / badge styles ───────────────────────────────────────────────────────
function workerCardClass(worker) {
  if (worker.status === 'dead') return 'border-red-300 bg-red-50/50'
  return {
    healthy:  'border-green-200 bg-green-50/30',
    warning:  'border-yellow-300 bg-yellow-50/30',
    critical: 'border-red-300 bg-red-50/30',
    unknown:  'border-gray-200 bg-gray-50',
  }[worker.heartbeat_health] ?? 'border-gray-200 bg-white'
}

function workerIconBg(worker) {
  if (worker.status === 'dead') return 'bg-red-500'
  return {
    healthy:  'bg-green-500',
    warning:  'bg-yellow-500',
    critical: 'bg-red-500',
    unknown:  'bg-gray-400',
  }[worker.heartbeat_health] ?? 'bg-gray-400'
}

function healthBadge(health) {
  return {
    healthy:  'bg-green-100 text-green-800',
    warning:  'bg-yellow-100 text-yellow-800',
    critical: 'bg-red-100 text-red-800',
    dead:     'bg-red-200 text-red-900',
    unknown:  'bg-gray-100 text-gray-600',
  }[health] ?? 'bg-gray-100 text-gray-600'
}

function healthTextColor(health) {
  return {
    healthy:  'text-green-600',
    warning:  'text-yellow-600',
    critical: 'text-red-600',
    dead:     'text-red-700',
    unknown:  'text-gray-500',
  }[health] ?? 'text-gray-500'
}

function healthLabel(health) {
  return {
    healthy:  '✓ HEALTHY',
    warning:  '⚠ WARNING',
    critical: '🔴 CRITICAL',
    dead:     '☠ DEAD',
    unknown:  '? UNKNOWN',
  }[health] ?? health.toUpperCase()
}

// ── Formatters ────────────────────────────────────────────────────────────────
function formatSeconds(s) {
  if (s === null || s === undefined) return 'never'
  if (s < 60) return `${s}s ago`
  return `${Math.floor(s / 60)}m ${s % 60}s ago`
}

function formatTime(iso) {
  if (!iso) return ''
  const date = new Date(iso)
  const now  = new Date()
  const s    = Math.floor((now - date) / 1000)
  if (s < 60) return `${s}s ago`
  if (s < 3600) return `${Math.floor(s / 60)}m ago`
  return `${Math.floor(s / 3600)}h ago`
}

// ── Lifecycle ─────────────────────────────────────────────────────────────────
onMounted(() => {
  refresh()
  pollTimer = setInterval(refresh, 5000)
})
onUnmounted(() => {
  if (pollTimer) clearInterval(pollTimer)
})
</script>
