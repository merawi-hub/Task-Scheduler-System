<template>
  <div>

    <!-- ── Status summary bar ──────────────────────────────────────────────── -->
    <div class="grid grid-cols-6 gap-3 mb-5">
      <button
        v-for="s in statusFilters"
        :key="s.value"
        @click="filterStatus = filterStatus === s.value ? '' : s.value"
        :class="[
          'flex flex-col items-center py-3 px-2 rounded-xl border-2 transition-all text-center',
          filterStatus === s.value
            ? s.activeClass
            : 'bg-white border-gray-100 hover:border-gray-200'
        ]"
      >
        <span :class="['text-xl font-bold', s.countClass]">{{ taskStats[s.value] ?? taskStats.total }}</span>
        <span class="text-xs text-gray-500 mt-0.5">{{ s.label }}</span>
      </button>
    </div>

    <!-- ── Controls row ────────────────────────────────────────────────────── -->
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-3">
        <h2 class="text-sm font-semibold text-gray-900">
          Tasks Table
          <span class="ml-1.5 text-xs font-normal text-gray-400">({{ filteredTasks.length }} rows)</span>
        </h2>
        <!-- Active filter badge -->
        <span v-if="filterStatus"
          class="flex items-center gap-1 px-2.5 py-1 bg-indigo-100 text-indigo-700 text-xs font-medium rounded-full">
          {{ filterStatus.toUpperCase() }}
          <button @click="filterStatus = ''" class="ml-1 hover:text-indigo-900">✕</button>
        </span>
      </div>
      <div class="flex items-center gap-2">
        <!-- Search -->
        <div class="relative">
          <input v-model="searchQuery" type="text" placeholder="Search task…"
            class="pl-8 pr-3 py-1.5 border border-gray-200 rounded-lg text-xs focus:outline-none
                   focus:ring-2 focus:ring-indigo-400 w-44"/>
          <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-2" fill="none"
            stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </div>
        <!-- Expand all payloads toggle -->
        <button @click="expandAll = !expandAll"
          class="px-3 py-1.5 border border-gray-200 rounded-lg text-xs text-gray-600
                 hover:bg-gray-50 transition-colors">
          {{ expandAll ? 'Collapse All' : 'Expand All' }}
        </button>
      </div>
    </div>

    <!-- ── Loading ────────────────────────────────────────────────────────── -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
      <p class="mt-3 text-sm text-gray-500">Loading tasks…</p>
    </div>

    <!-- ── Empty ──────────────────────────────────────────────────────────── -->
    <div v-else-if="filteredTasks.length === 0" class="text-center py-12 bg-white rounded-xl border border-gray-100">
      <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
      </svg>
      <p class="text-sm text-gray-500">No tasks found</p>
    </div>

    <!-- ── Main table ─────────────────────────────────────────────────────── -->
    <div v-else class="bg-white rounded-xl border border-gray-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-8"></th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Task ID</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Job ID</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Records</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Count</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Worker</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Retries</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Duration</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Completed</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <template v-for="task in filteredTasks" :key="task.id">

              <!-- ── Main row ──────────────────────────────────────────────── -->
              <tr :class="rowBg(task.status)"
                class="hover:bg-indigo-50/30 transition-colors cursor-pointer"
                @click="toggleExpand(task.id)">

                <!-- Expand chevron -->
                <td class="px-4 py-3 text-center">
                  <svg :class="['w-3.5 h-3.5 text-gray-400 transition-transform', expandedRows.has(task.id) || expandAll ? 'rotate-90' : '']"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                  </svg>
                </td>

                <!-- Task ID -->
                <td class="px-4 py-3 whitespace-nowrap">
                  <span class="font-mono text-xs font-semibold text-gray-700 bg-gray-100 px-2 py-0.5 rounded">
                    #{{ task.id }}
                  </span>
                </td>

                <!-- Job ID -->
                <td class="px-4 py-3 whitespace-nowrap">
                  <span class="font-mono text-xs text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">
                    #{{ task.job_id }}
                  </span>
                </td>

                <!-- Records range -->
                <td class="px-4 py-3 whitespace-nowrap">
                  <span v-if="task.record_from != null && task.record_to != null"
                    class="font-mono text-xs font-medium text-gray-800">
                    {{ task.record_from.toLocaleString() }}
                    <span class="text-gray-400 mx-1">→</span>
                    {{ task.record_to.toLocaleString() }}
                  </span>
                  <span v-else class="text-xs text-gray-400">—</span>
                </td>

                <!-- Records count -->
                <td class="px-4 py-3 whitespace-nowrap">
                  <span v-if="task.records_count != null"
                    class="text-xs font-semibold text-indigo-600">
                    {{ task.records_count }}
                  </span>
                  <span v-else class="text-xs text-gray-400">—</span>
                </td>

                <!-- Status badge -->
                <td class="px-4 py-3 whitespace-nowrap">
                  <span :class="statusBadge(task.status)"
                    class="px-2.5 py-1 text-xs font-semibold rounded-full inline-flex items-center gap-1.5">
                    <span :class="statusDot(task.status)" class="w-1.5 h-1.5 rounded-full"></span>
                    {{ task.status.toUpperCase() }}
                  </span>
                </td>

                <!-- Worker -->
                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500">
                  <span v-if="task.worker" class="font-mono text-indigo-600">
                    {{ task.worker.worker_key }}
                  </span>
                  <span v-else class="text-gray-300 italic">waiting…</span>
                </td>

                <!-- Retries -->
                <td class="px-4 py-3 whitespace-nowrap text-xs">
                  <span v-if="task.retry_count > 0"
                    class="px-2 py-0.5 bg-orange-100 text-orange-700 rounded font-medium">
                    {{ task.retry_count }}/{{ task.max_retries }}
                  </span>
                  <span v-else class="text-gray-300">0</span>
                </td>

                <!-- Duration -->
                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500">
                  {{ formatDuration(task) }}
                </td>

                <!-- Completed at -->
                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500">
                  {{ formatDateTime(task.completed_at) }}
                </td>
              </tr>

              <!-- ── Expanded payload row ───────────────────────────────────── -->
              <tr v-if="expandedRows.has(task.id) || expandAll"
                :class="rowBg(task.status)" class="border-t-0">
                <td colspan="10" class="px-6 pb-4 pt-0">
                  <div class="bg-gray-50 rounded-xl border border-gray-100 p-4 mt-1">
                    <div class="grid grid-cols-2 gap-6">

                      <!-- Left: task metadata -->
                      <div>
                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-3">
                          Task Metadata
                        </p>
                        <div class="space-y-2 text-xs">
                          <div class="flex justify-between">
                            <span class="text-gray-500">Task Index</span>
                            <span class="font-mono font-medium text-gray-800">{{ task.task_index }}</span>
                          </div>
                          <div class="flex justify-between">
                            <span class="text-gray-500">Timeout</span>
                            <span class="font-medium text-gray-800">{{ task.timeout_seconds }}s</span>
                          </div>
                          <div class="flex justify-between">
                            <span class="text-gray-500">Max Retries</span>
                            <span class="font-medium text-gray-800">{{ task.max_retries }}</span>
                          </div>
                          <div v-if="task.assigned_at" class="flex justify-between">
                            <span class="text-gray-500">Assigned At</span>
                            <span class="font-medium text-gray-800">{{ formatDateTime(task.assigned_at) }}</span>
                          </div>
                          <div v-if="task.started_at" class="flex justify-between">
                            <span class="text-gray-500">Started At</span>
                            <span class="font-medium text-gray-800">{{ formatDateTime(task.started_at) }}</span>
                          </div>
                          <div v-if="task.failure_reason" class="flex justify-between">
                            <span class="text-gray-500">Failure</span>
                            <span class="font-medium text-red-600 max-w-[200px] text-right">{{ task.failure_reason }}</span>
                          </div>
                        </div>

                        <!-- Retry attempt dots -->
                        <div v-if="task.retry_count > 0 || task.status === 'failed'" class="mt-4">
                          <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                            Retry History
                          </p>
                          <div class="flex items-center gap-1.5">
                            <div v-for="n in (task.max_retries || 3)" :key="n"
                              :class="['w-5 h-5 rounded-full flex items-center justify-center text-[9px] font-bold',
                                n <= task.retry_count
                                  ? (task.status === 'failed' && n === task.retry_count
                                      ? 'bg-red-500 text-white'
                                      : 'bg-orange-400 text-white')
                                  : 'bg-gray-100 text-gray-400']">
                              {{ n }}
                            </div>
                            <div v-if="task.status === 'done' && task.retry_count > 0"
                              class="w-5 h-5 rounded-full bg-green-500 flex items-center justify-center text-[9px] text-white font-bold">
                              ✓
                            </div>
                          </div>
                          <!-- Backoff delays -->
                          <div class="mt-2 space-y-0.5">
                            <p v-for="n in task.retry_count" :key="n"
                              class="text-[10px] text-gray-400 font-mono">
                              Attempt {{ n }}: waited {{ Math.pow(2, n - 1) * 5 }}s before retry
                            </p>
                          </div>
                        </div>

                        <!-- Operations list -->
                        <div v-if="task.operations?.length" class="mt-4">
                          <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">Operations</p>
                          <div class="flex flex-wrap gap-1.5">
                            <span v-for="op in task.operations" :key="op"
                              class="px-2 py-0.5 bg-indigo-100 text-indigo-700 text-xs font-mono rounded">
                              {{ op }}
                            </span>
                          </div>
                        </div>
                      </div>

                      <!-- Right: payload preview -->
                      <div>
                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-3">
                          Payload
                          <span class="ml-1 text-gray-400 font-normal normal-case">(what the worker receives)</span>
                        </p>
                        <pre class="bg-white border border-gray-200 rounded-lg p-3 text-xs text-gray-700
                                    overflow-x-auto max-h-48 font-mono leading-relaxed">{{ formatPayload(task.payload) }}</pre>
                      </div>

                    </div>
                  </div>
                </td>
              </tr>

            </template>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

// ── Props ─────────────────────────────────────────────────────────────────────
const props = defineProps({
  tasks: {
    type: Array,
    required: true,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

// ── State ─────────────────────────────────────────────────────────────────────
const filterStatus  = ref('')
const searchQuery   = ref('')
const expandAll     = ref(false)
const expandedRows  = ref(new Set())

// ── Status filter buttons ─────────────────────────────────────────────────────
const statusFilters = [
  { value: '',         label: 'All',      countClass: 'text-gray-800',  activeClass: 'bg-gray-100 border-gray-300' },
  { value: 'pending',  label: 'Pending',  countClass: 'text-yellow-600', activeClass: 'bg-yellow-50 border-yellow-400' },
  { value: 'assigned', label: 'Assigned', countClass: 'text-blue-600',  activeClass: 'bg-blue-50 border-blue-400' },
  { value: 'running',  label: 'Running',  countClass: 'text-indigo-600', activeClass: 'bg-indigo-50 border-indigo-400' },
  { value: 'done',     label: 'Done',     countClass: 'text-green-600', activeClass: 'bg-green-50 border-green-400' },
  { value: 'failed',   label: 'Failed',   countClass: 'text-red-600',   activeClass: 'bg-red-50 border-red-400' },
]

// ── Computed ──────────────────────────────────────────────────────────────────
const taskStats = computed(() => ({
  total:    props.tasks.length,
  pending:  props.tasks.filter(t => t.status === 'pending').length,
  assigned: props.tasks.filter(t => t.status === 'assigned').length,
  running:  props.tasks.filter(t => t.status === 'running').length,
  done:     props.tasks.filter(t => t.status === 'done').length,
  failed:   props.tasks.filter(t => t.status === 'failed').length,
}))

const filteredTasks = computed(() => {
  let list = props.tasks

  // Status filter
  if (filterStatus.value) {
    list = list.filter(t => t.status === filterStatus.value)
  }

  // Search filter — match task id, record range, worker key
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(t => {
      const taskNum   = String(t.task_index + 1)
      const taskId    = String(t.id)
      const recordStr = `${t.record_from ?? ''} ${t.record_to ?? ''}`
      const worker    = t.worker?.worker_key ?? ''
      return (
        taskNum.includes(q) ||
        taskId.includes(q) ||
        recordStr.includes(q) ||
        worker.toLowerCase().includes(q)
      )
    })
  }

  return list
})

// ── Row expand/collapse ───────────────────────────────────────────────────────
function toggleExpand(taskId) {
  if (expandedRows.value.has(taskId)) {
    expandedRows.value.delete(taskId)
  } else {
    expandedRows.value.add(taskId)
  }
  // Trigger reactivity
  expandedRows.value = new Set(expandedRows.value)
}

// ── Style helpers ─────────────────────────────────────────────────────────────
function rowBg(status) {
  return {
    done:     'bg-green-50/40',
    failed:   'bg-red-50/40',
    running:  'bg-blue-50/30',
    assigned: 'bg-indigo-50/20',
  }[status] ?? ''
}

function statusBadge(status) {
  return {
    pending:  'bg-yellow-100 text-yellow-800',
    assigned: 'bg-blue-100 text-blue-800',
    running:  'bg-indigo-100 text-indigo-800',
    done:     'bg-green-100 text-green-800',
    failed:   'bg-red-100 text-red-800',
    cancelled:'bg-gray-100 text-gray-600',
  }[status] ?? 'bg-gray-100 text-gray-600'
}

function statusDot(status) {
  return {
    pending:  'bg-yellow-500',
    assigned: 'bg-blue-500',
    running:  'bg-indigo-500 animate-pulse',
    done:     'bg-green-500',
    failed:   'bg-red-500',
    cancelled:'bg-gray-400',
  }[status] ?? 'bg-gray-400'
}

// ── Payload formatter — hide the large "records" array to keep it readable ────
function formatPayload(payload) {
  if (!payload) return 'No payload'
  const display = { ...payload }
  if (display.records && Array.isArray(display.records)) {
    display.records = `[${display.records.length} student records — hidden for brevity]`
  }
  return JSON.stringify(display, null, 2)
}

// ── Duration ──────────────────────────────────────────────────────────────────
function formatDuration(task) {
  if (!task.started_at) return '—'
  const start = new Date(task.started_at)
  const end   = task.completed_at ? new Date(task.completed_at) : new Date()
  const ms    = end - start
  if (ms < 0) return '—'
  const s = Math.floor(ms / 1000)
  const m = Math.floor(s / 60)
  const h = Math.floor(m / 60)
  if (h > 0)  return `${h}h ${m % 60}m`
  if (m > 0)  return `${m}m ${s % 60}s`
  return `${s}s`
}

// ── Date/time ─────────────────────────────────────────────────────────────────
function formatDateTime(dateString) {
  if (!dateString) return '—'
  const date  = new Date(dateString)
  const now   = new Date()
  const diffMs = now - date
  const mins  = Math.floor(diffMs / 60000)
  const hours = Math.floor(diffMs / 3600000)
  if (mins < 1)   return 'Just now'
  if (mins < 60)  return `${mins}m ago`
  if (hours < 24) return `${hours}h ago`
  return date.toLocaleString()
}
</script>
