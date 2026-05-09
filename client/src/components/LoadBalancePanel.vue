<template>
  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

    <!-- ── Header ─────────────────────────────────────────────────────────── -->
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
          <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
          </svg>
        </div>
        <div>
          <h3 class="text-sm font-semibold text-gray-900">Automatic Load Balancing</h3>
          <p class="text-xs text-gray-500">Fast workers do more — no manual assignment needed</p>
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

    <!-- ── How it works explanation ───────────────────────────────────────── -->
    <div class="px-6 py-3 bg-green-50 border-b border-green-100">
      <div class="flex items-start gap-3">
        <span class="text-lg flex-shrink-0">🛒</span>
        <p class="text-xs text-green-800">
          <strong>Supermarket analogy:</strong> Workers are cashiers. Tasks are customers.
          Fast cashiers serve more customers. Slow cashiers serve fewer.
          Customers naturally move to available cashiers — <strong>no manager needed</strong>.
          That's exactly how your pull-based system works.
        </p>
      </div>
    </div>

    <!-- ── Loading ────────────────────────────────────────────────────────── -->
    <div v-if="loading && !data" class="text-center py-10">
      <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-green-600"></div>
      <p class="text-xs text-gray-500 mt-2">Loading load balance data…</p>
    </div>

    <!-- ── No data ────────────────────────────────────────────────────────── -->
    <div v-else-if="!workerStats.length" class="px-6 py-8 text-center">
      <p class="text-sm text-gray-500">No workers have processed tasks yet.</p>
      <p class="text-xs text-gray-400 mt-1">Start workers and submit a job to see load balancing in action.</p>
    </div>

    <!-- ── Main content ───────────────────────────────────────────────────── -->
    <div v-else class="p-6">

      <!-- Key insight banner -->
      <div class="flex items-center gap-3 p-3 bg-indigo-50 border border-indigo-100 rounded-xl mb-5">
        <svg class="w-5 h-5 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
        </svg>
        <p class="text-xs text-indigo-800">
          <strong>{{ topWorker?.worker_key }}</strong> completed
          <strong>{{ topWorker?.tasks_completed }}</strong> tasks
          while <strong>{{ bottomWorker?.worker_key }}</strong> completed
          <strong>{{ bottomWorker?.tasks_completed }}</strong>.
          The difference is automatic — no configuration required.
        </p>
      </div>

      <!-- ── Worker leaderboard ──────────────────────────────────────────── -->
      <div class="space-y-3 mb-6">
        <div v-for="(worker, idx) in workerStats" :key="worker.worker_key"
          class="rounded-xl border overflow-hidden"
          :class="workerCardClass(worker, idx)">

          <div class="flex items-center gap-4 px-4 py-3">

            <!-- Rank + worker identity -->
            <div class="flex items-center gap-3 flex-shrink-0 w-36">
              <!-- Rank medal -->
              <div :class="['w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0',
                idx === 0 ? 'bg-yellow-400 text-yellow-900' :
                idx === 1 ? 'bg-gray-300 text-gray-700' :
                idx === 2 ? 'bg-orange-400 text-orange-900' :
                'bg-gray-100 text-gray-500']">
                {{ idx + 1 }}
              </div>
              <div>
                <p class="text-xs font-bold text-gray-800 leading-tight">{{ worker.worker_key }}</p>
                <div class="flex items-center gap-1 mt-0.5">
                  <span :class="['w-1.5 h-1.5 rounded-full', statusDot(worker.status)]"></span>
                  <span class="text-[10px] text-gray-500 uppercase">{{ worker.status }}</span>
                </div>
              </div>
            </div>

            <!-- Tasks completed bar -->
            <div class="flex-1">
              <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-semibold text-gray-700">
                  {{ worker.tasks_completed }} tasks
                </span>
                <div class="flex items-center gap-2">
                  <!-- Speed badge -->
                  <span :class="['px-2 py-0.5 text-[10px] font-bold rounded-full', speedBadge(worker.speed_label)]">
                    {{ speedEmoji(worker.speed_label) }} {{ worker.speed_label.toUpperCase() }}
                  </span>
                  <!-- Share percent -->
                  <span class="text-xs font-bold text-gray-600">{{ worker.share_percent }}%</span>
                </div>
              </div>
              <!-- Bar showing share of total work -->
              <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                <div
                  :class="['h-3 rounded-full transition-all duration-700', barColor(idx)]"
                  :style="{ width: Math.max(2, worker.share_percent) + '%' }"
                ></div>
              </div>
              <!-- Avg duration -->
              <div class="flex justify-between mt-1">
                <span class="text-[10px] text-gray-400">
                  avg {{ worker.avg_duration_ms ? formatMs(worker.avg_duration_ms) : '—' }} / task
                </span>
                <span class="text-[10px] text-gray-400">
                  {{ worker.tasks_failed > 0 ? `${worker.tasks_failed} failed` : 'no failures' }}
                </span>
              </div>
            </div>

          </div>
        </div>
      </div>

      <!-- ── Why this is load balancing ─────────────────────────────────── -->
      <div class="bg-gray-50 rounded-xl border border-gray-100 p-4">
        <p class="text-xs font-semibold text-gray-700 uppercase tracking-wide mb-3">
          Why this is automatic load balancing
        </p>
        <div class="grid grid-cols-3 gap-3">
          <div class="bg-white rounded-lg p-3 border border-gray-100">
            <p class="text-lg mb-1">⚡</p>
            <p class="text-xs font-semibold text-gray-800">Fast worker finishes</p>
            <p class="text-[11px] text-gray-500 mt-0.5">
              Immediately asks for the next task. No waiting.
            </p>
          </div>
          <div class="bg-white rounded-lg p-3 border border-gray-100">
            <p class="text-lg mb-1">🔄</p>
            <p class="text-xs font-semibold text-gray-800">Queue shrinks faster</p>
            <p class="text-[11px] text-gray-500 mt-0.5">
              Fast workers drain the queue proportionally to their speed.
            </p>
          </div>
          <div class="bg-white rounded-lg p-3 border border-gray-100">
            <p class="text-lg mb-1">🎯</p>
            <p class="text-xs font-semibold text-gray-800">No configuration</p>
            <p class="text-[11px] text-gray-500 mt-0.5">
              The pull model handles it. No AI, no rules, no manual assignment.
            </p>
          </div>
        </div>

        <!-- Total tasks summary -->
        <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between">
          <span class="text-xs text-gray-500">Total tasks completed across all workers</span>
          <span class="text-sm font-bold text-gray-900">{{ totalTasksDone }}</span>
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
const workerStats    = computed(() => data.value?.load_balance?.worker_stats ?? [])
const totalTasksDone = computed(() => data.value?.load_balance?.total_tasks_done ?? 0)
const topWorker      = computed(() => workerStats.value[0] ?? null)
const bottomWorker   = computed(() => {
  const s = workerStats.value
  return s.length > 1 ? s[s.length - 1] : null
})

// ── Fetch ─────────────────────────────────────────────────────────────────────
async function refresh() {
  loading.value = true
  try {
    const response = await apiClient.get('/tasks/activity')
    data.value = response.data
    snapshotTime.value = new Date().toLocaleTimeString()
  } catch {
    // Non-critical
  } finally {
    loading.value = false
  }
}

// ── Style helpers ─────────────────────────────────────────────────────────────
function workerCardClass(worker, idx) {
  if (idx === 0 && worker.tasks_completed > 0) return 'border-yellow-300 bg-yellow-50/40'
  if (worker.status === 'busy') return 'border-blue-200 bg-blue-50/30'
  return 'border-gray-100 bg-white'
}

function barColor(idx) {
  const colors = [
    'bg-gradient-to-r from-yellow-400 to-orange-400',
    'bg-gradient-to-r from-blue-400 to-indigo-400',
    'bg-gradient-to-r from-green-400 to-emerald-400',
    'bg-gradient-to-r from-purple-400 to-pink-400',
    'bg-gradient-to-r from-gray-400 to-gray-500',
  ]
  return colors[idx % colors.length]
}

function statusDot(status) {
  return {
    busy:  'bg-blue-500 animate-pulse',
    idle:  'bg-yellow-400',
    dead:  'bg-red-500',
  }[status] ?? 'bg-gray-400'
}

function speedBadge(label) {
  return {
    fast:   'bg-green-100 text-green-800',
    normal: 'bg-gray-100 text-gray-600',
    slow:   'bg-orange-100 text-orange-700',
  }[label] ?? 'bg-gray-100 text-gray-600'
}

function speedEmoji(label) {
  return { fast: '⚡', normal: '➡️', slow: '🐢' }[label] ?? '➡️'
}

function formatMs(ms) {
  if (!ms) return '—'
  if (ms < 1000) return `${ms}ms`
  const s = (ms / 1000).toFixed(1)
  return `${s}s`
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
