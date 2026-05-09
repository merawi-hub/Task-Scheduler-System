<template>
  <div v-if="summary" class="rounded-2xl overflow-hidden shadow-lg">

    <!-- ── Success banner ─────────────────────────────────────────────────── -->
    <div v-if="summary.is_success"
      class="bg-gradient-to-r from-green-500 via-emerald-500 to-teal-500 px-8 py-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-5">
          <!-- 100% circle -->
          <div class="relative w-20 h-20 flex-shrink-0">
            <svg class="w-full h-full -rotate-90">
              <circle cx="40" cy="40" r="34" stroke="rgba(255,255,255,0.3)"
                stroke-width="6" fill="none"/>
              <circle cx="40" cy="40" r="34" stroke="white"
                stroke-width="6" fill="none"
                stroke-dasharray="213.6"
                stroke-dashoffset="0"
                stroke-linecap="round"
                class="transition-all duration-1000"/>
            </svg>
            <div class="absolute inset-0 flex items-center justify-center">
              <span class="text-white font-bold text-lg">100%</span>
            </div>
          </div>

          <div>
            <div class="flex items-center gap-2 mb-1">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
              </svg>
              <h2 class="text-2xl font-bold text-white">Job Completed!</h2>
            </div>
            <p class="text-green-100 text-sm">
              <strong class="text-white">{{ summary.job_name }}</strong>
              finished in <strong class="text-white">{{ summary.duration_label ?? '—' }}</strong>
            </p>
            <p class="text-green-100 text-xs mt-0.5">
              {{ summary.completed_tasks }} tasks · {{ summary.workers_used }} worker{{ summary.workers_used !== 1 ? 's' : '' }}
              <span v-if="summary.total_records">
                · {{ summary.total_records.toLocaleString() }} records processed
              </span>
            </p>
          </div>
        </div>

        <!-- Key stats -->
        <div class="flex items-center gap-6">
          <div class="text-center">
            <p class="text-2xl font-bold text-white">{{ summary.completed_tasks }}</p>
            <p class="text-xs text-green-100">Tasks Done</p>
          </div>
          <div class="text-center">
            <p class="text-2xl font-bold text-white">{{ summary.duration_label ?? '—' }}</p>
            <p class="text-xs text-green-100">Total Time</p>
          </div>
          <div v-if="summary.throughput_per_sec > 0" class="text-center">
            <p class="text-2xl font-bold text-white">{{ summary.throughput_per_sec }}</p>
            <p class="text-xs text-green-100">tasks/sec</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Failed banner ──────────────────────────────────────────────────── -->
    <div v-else
      class="bg-gradient-to-r from-red-500 to-rose-600 px-8 py-6">
      <div class="flex items-center gap-5">
        <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0">
          <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <div>
          <h2 class="text-xl font-bold text-white">Job Failed</h2>
          <p class="text-red-100 text-sm mt-0.5">
            {{ summary.failed_tasks }} task{{ summary.failed_tasks !== 1 ? 's' : '' }} failed permanently.
            {{ summary.completed_tasks }} of {{ summary.total_tasks }} tasks completed.
          </p>
        </div>
      </div>
    </div>

    <!-- ── Stats grid ─────────────────────────────────────────────────────── -->
    <div class="bg-white border-x border-b border-gray-100 px-8 py-5">
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">

        <!-- Duration -->
        <div class="text-center">
          <p class="text-xs text-gray-500 mb-1">Total Duration</p>
          <p class="text-xl font-bold text-gray-900">{{ summary.duration_label ?? '—' }}</p>
          <p class="text-[10px] text-gray-400 mt-0.5">
            {{ formatTime(summary.started_at) }} → {{ formatTime(summary.completed_at) }}
          </p>
        </div>

        <!-- Tasks -->
        <div class="text-center">
          <p class="text-xs text-gray-500 mb-1">Tasks</p>
          <p class="text-xl font-bold text-gray-900">
            <span class="text-green-600">{{ summary.completed_tasks }}</span>
            <span class="text-gray-300 mx-1">/</span>
            <span>{{ summary.total_tasks }}</span>
          </p>
          <p v-if="summary.failed_tasks > 0" class="text-[10px] text-red-500 mt-0.5">
            {{ summary.failed_tasks }} failed
          </p>
          <p v-else class="text-[10px] text-green-500 mt-0.5">All succeeded</p>
        </div>

        <!-- Avg task time -->
        <div class="text-center">
          <p class="text-xs text-gray-500 mb-1">Avg Task Time</p>
          <p class="text-xl font-bold text-gray-900">
            {{ summary.avg_task_ms ? formatMs(summary.avg_task_ms) : '—' }}
          </p>
          <p class="text-[10px] text-gray-400 mt-0.5">per task</p>
        </div>

        <!-- Records or throughput -->
        <div class="text-center">
          <template v-if="summary.total_records">
            <p class="text-xs text-gray-500 mb-1">Records Processed</p>
            <p class="text-xl font-bold text-indigo-600">
              {{ summary.total_records.toLocaleString() }}
            </p>
            <p class="text-[10px] text-gray-400 mt-0.5">student records</p>
          </template>
          <template v-else>
            <p class="text-xs text-gray-500 mb-1">Throughput</p>
            <p class="text-xl font-bold text-indigo-600">{{ summary.throughput_per_sec }}</p>
            <p class="text-[10px] text-gray-400 mt-0.5">tasks/sec</p>
          </template>
        </div>

      </div>

      <!-- Worker contributions bar chart -->
      <div v-if="summary.worker_contributions?.length" class="mt-5 pt-5 border-t border-gray-100">
        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-3">
          Worker Contributions
        </p>
        <div class="space-y-2">
          <div v-for="(w, idx) in summary.worker_contributions" :key="w.worker_key"
            class="flex items-center gap-3">
            <span class="text-xs font-mono text-gray-700 w-24 flex-shrink-0 truncate">
              {{ w.worker_key }}
            </span>
            <div class="flex-1 bg-gray-100 rounded-full h-3 overflow-hidden">
              <div
                :class="['h-3 rounded-full transition-all duration-700', barColor(idx)]"
                :style="{ width: w.share_pct + '%' }"
              ></div>
            </div>
            <span class="text-xs font-semibold text-gray-700 w-16 text-right flex-shrink-0">
              {{ w.tasks_done }} tasks ({{ w.share_pct }}%)
            </span>
          </div>
        </div>
      </div>

      <!-- Action buttons -->
      <div class="flex items-center gap-3 mt-5 pt-5 border-t border-gray-100">
        <button @click="$emit('view-tasks')"
          class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
          View All Tasks
        </button>
        <button @click="$emit('submit-another')"
          class="flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Submit Another Job
        </button>
        <button @click="$emit('go-to-jobs')"
          class="flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
          </svg>
          My Jobs
        </button>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '@/api/axios'

// ── Props & emits ─────────────────────────────────────────────────────────────
const props = defineProps({
  jobId: { type: [Number, String], required: true },
})

defineEmits(['view-tasks', 'submit-another', 'go-to-jobs'])

// ── State ─────────────────────────────────────────────────────────────────────
const summary = ref(null)

// ── Fetch ─────────────────────────────────────────────────────────────────────
async function load() {
  try {
    const response = await apiClient.get(`/jobs/${props.jobId}/completion`)
    summary.value = response.data
  } catch {
    // Non-critical
  }
}

// ── Helpers ───────────────────────────────────────────────────────────────────
const BAR_COLORS = [
  'bg-gradient-to-r from-blue-500 to-indigo-500',
  'bg-gradient-to-r from-purple-500 to-pink-500',
  'bg-gradient-to-r from-green-500 to-emerald-500',
  'bg-gradient-to-r from-orange-500 to-amber-500',
  'bg-gradient-to-r from-gray-400 to-gray-500',
]
function barColor(idx) {
  return BAR_COLORS[idx % BAR_COLORS.length]
}

function formatMs(ms) {
  if (!ms) return '—'
  if (ms < 1000) return `${ms}ms`
  const s = (ms / 1000).toFixed(1)
  return `${s}s`
}

function formatTime(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
}

// ── Lifecycle ─────────────────────────────────────────────────────────────────
onMounted(load)
</script>
