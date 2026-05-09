<template>
  <div class="flex h-screen bg-gray-50">
    <UserSidebar />
    <div class="flex-1 ml-64 overflow-auto">

      <!-- Header -->
      <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="px-8 py-4 flex items-center justify-between">
          <div>
            <nav class="flex items-center gap-1.5 text-sm mb-1">
              <router-link to="/my-jobs" class="text-gray-500 hover:text-indigo-600">Jobs</router-link>
              <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
              <span class="text-gray-900 font-medium truncate max-w-xs">{{ job ? job.name : 'Loading...' }}</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900">Job Details</h1>
          </div>
          <div class="flex items-center gap-2">
            <button class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <button @click="loadData" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100" :class="{'animate-spin': loading}">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </button>
          </div>
        </div>
      </header>

      <!-- Loading -->
      <div v-if="loading && !job" class="flex items-center justify-center h-64">
        <div class="text-center">
          <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600 mx-auto mb-3"></div>
          <p class="text-sm text-gray-500">Loading job details...</p>
        </div>
      </div>

      <!-- Error -->
      <div v-else-if="pageError && !job" class="p-8">
        <div class="flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
          <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          {{ pageError }}
        </div>
      </div>

      <!-- Content -->
      <main v-else-if="job" class="p-8">

        <!-- Job title row -->
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
              <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div>
              <h2 class="text-xl font-bold text-gray-900">{{ job.name }}</h2>
              <div class="flex items-center gap-2 mt-1">
                <span class="w-2 h-2 rounded-full" :class="dotClass(job.status)"></span>
                <span :class="textClass(job.status)" class="text-sm font-medium capitalize">{{ job.status }}</span>
              </div>
            </div>
          </div>
          <div class="relative">
            <button @click="actionsOpen = !actionsOpen" class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
              Actions <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div v-if="actionsOpen" class="absolute right-0 mt-1 w-44 bg-white border border-gray-200 rounded-xl shadow-lg z-20 py-1">
              <button @click="loadData(); actionsOpen=false" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Refresh
              </button>
              <button v-if="['pending','running'].includes(job.status)" @click="doCancel(); actionsOpen=false" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Cancel Job
              </button>
            </div>
          </div>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-6">
          <nav class="flex gap-6">
            <button v-for="t in tabs" :key="t.id" @click="tab = t.id"
              :class="['pb-3 text-sm font-medium border-b-2 transition-colors',
                tab === t.id ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700']">
              {{ t.id === 'tasks' ? 'Tasks (' + taskList.length + ')' : t.label }}
            </button>
          </nav>
        </div>

        <!-- OVERVIEW -->
        <div v-show="tab === 'overview'" class="space-y-6">
          <div class="grid grid-cols-2 gap-6">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
              <h3 class="text-sm font-bold text-gray-900 mb-5">Job Information</h3>
              <dl class="space-y-4">
                <div class="flex justify-between"><dt class="text-sm text-gray-500">Job ID</dt><dd class="text-sm font-semibold text-indigo-600">#JOB-{{ String(job.id).padStart(3,'0') }}</dd></div>
                <div class="flex justify-between border-t border-gray-50 pt-4"><dt class="text-sm text-gray-500">Created At</dt><dd class="text-sm font-medium text-gray-900">{{ fmtDate(job.created_at) }}</dd></div>
                <div class="flex justify-between border-t border-gray-50 pt-4"><dt class="text-sm text-gray-500">Created By</dt><dd class="text-sm font-medium text-gray-900">{{ job.user ? job.user.email : (job.submitted_by || 'You') }}</dd></div>
                <div class="flex justify-between border-t border-gray-50 pt-4"><dt class="text-sm text-gray-500">Priority</dt><dd :class="prioClass(job.priority)" class="text-sm font-bold">{{ prioLabel(job.priority) }}</dd></div>
                <div v-if="job.started_at" class="flex justify-between border-t border-gray-50 pt-4"><dt class="text-sm text-gray-500">Started At</dt><dd class="text-sm font-medium text-gray-900">{{ fmtDate(job.started_at) }}</dd></div>
              </dl>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
              <h3 class="text-sm font-bold text-gray-900 mb-5">Progress</h3>
              <div class="flex items-center gap-6 mb-6">
                <div class="relative flex-shrink-0">
                  <svg class="w-28 h-28 -rotate-90" viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="40" fill="none" stroke="#e5e7eb" stroke-width="8"/>
                    <circle cx="50" cy="50" r="40" fill="none" stroke="#4f46e5" stroke-width="8" stroke-linecap="round"
                      :stroke-dasharray="(pct * 2.513) + ' 251.3'" class="transition-all duration-700"/>
                  </svg>
                  <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-xl font-bold text-gray-900">{{ pct }}%</span>
                  </div>
                </div>
                <div>
                  <p class="text-3xl font-bold text-gray-900">{{ doneCount }} / {{ job.total_tasks }}</p>
                  <p class="text-sm text-gray-500 mt-1">tasks completed</p>
                </div>
              </div>
              <dl class="space-y-3">
                <div class="flex justify-between"><dt class="text-sm text-gray-500">Estimated Completion</dt><dd class="text-sm font-medium text-gray-900">{{ estCompletion }}</dd></div>
                <div class="flex justify-between border-t border-gray-50 pt-3"><dt class="text-sm text-gray-500">Duration</dt><dd class="text-sm font-medium text-gray-900">{{ jobDuration }}</dd></div>
              </dl>
            </div>
          </div>
          <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-sm font-bold text-gray-900 mb-5">Task Status</h3>
            <div class="grid grid-cols-4 gap-6">
              <div><p class="text-3xl font-bold text-green-600 mb-2">{{ stats.done }}</p><div class="w-full h-2 rounded-full bg-green-500 mb-2"></div><p class="text-sm text-gray-500">Completed</p></div>
              <div><p class="text-3xl font-bold text-yellow-600 mb-2">{{ stats.running }}</p><div class="w-full h-2 rounded-full bg-yellow-400 mb-2"></div><p class="text-sm text-gray-500">Running</p></div>
              <div><p class="text-3xl font-bold text-red-600 mb-2">{{ stats.failed }}</p><div class="w-full h-2 rounded-full bg-red-500 mb-2"></div><p class="text-sm text-gray-500">Failed</p></div>
              <div><p class="text-3xl font-bold text-gray-500 mb-2">{{ stats.pending }}</p><div class="w-full h-2 rounded-full bg-gray-300 mb-2"></div><p class="text-sm text-gray-500">Pending</p></div>
            </div>
          </div>
        </div>

        <!-- TASKS -->
        <div v-show="tab === 'tasks'">
          <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <!-- Toolbar -->
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
              <div class="relative flex-1 max-w-xs">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input v-model="search" type="text" placeholder="Search tasks..." class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
              </div>
              <span class="text-sm text-gray-500">Filter:</span>
              <select v-model="statusFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="assigned">Assigned</option>
                <option value="running">Running</option>
                <option value="done">Done</option>
                <option value="failed">Failed</option>
                <option value="cancelled">Cancelled</option>
              </select>
              <span class="text-sm text-gray-500 ml-auto">{{ visibleTasks.length }} of {{ taskList.length }} tasks</span>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Task ID</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Task Name</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Job Name</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Worker</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Duration</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Started At</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Retries</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <template v-if="pagedTasks.length > 0">
                    <tr v-for="task in pagedTasks" :key="task.id" class="hover:bg-gray-50 transition-colors">
                      <td class="px-6 py-4 text-sm font-mono text-indigo-600 font-semibold">#TASK-{{ String(task.task_index + 1).padStart(3,'0') }}</td>
                      <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        {{ buildTaskName(task) }}
                        <span v-if="task.failure_reason" class="block text-xs text-red-500 mt-0.5 truncate max-w-xs">{{ task.failure_reason }}</span>
                      </td>
                      <td class="px-6 py-4 text-sm text-gray-600">{{ job.name }}</td>
                      <td class="px-6 py-4">
                        <span :class="badgeClass(task.status)" class="px-2.5 py-1 text-xs font-bold rounded-full uppercase tracking-wide">
                          {{ task.status === 'done' ? 'COMPLETED' : task.status.toUpperCase() }}
                        </span>
                      </td>
                      <td class="px-6 py-4 text-sm text-gray-600 font-mono">{{ task.worker ? task.worker.worker_key : '—' }}</td>
                      <td class="px-6 py-4 text-sm text-gray-600">{{ taskDur(task) }}</td>
                      <td class="px-6 py-4 text-sm text-gray-600">{{ task.started_at ? fmtTime(task.started_at) : '—' }}</td>
                      <td class="px-6 py-4 text-sm text-gray-600">{{ task.retry_count }}/{{ task.max_retries }}</td>
                    </tr>
                  </template>
                  <tr v-else>
                    <td colspan="8" class="px-6 py-12 text-center">
                      <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                      <p class="text-sm font-medium text-gray-500">No tasks found</p>
                      <p v-if="statusFilter || search" class="text-xs text-gray-400 mt-1">Try clearing the filters</p>
                      <p v-else class="text-xs text-gray-400 mt-1">Tasks will appear here once the job starts processing</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div v-if="totalPages > 1" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
              <p class="text-sm text-gray-500">
                Showing {{ (page - 1) * perPage + 1 }}–{{ Math.min(page * perPage, visibleTasks.length) }} of {{ visibleTasks.length }}
              </p>
              <div class="flex items-center gap-1">
                <button @click="page--" :disabled="page === 1" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button v-for="p in pageNums" :key="p" @click="page = p"
                  :class="['w-8 h-8 flex items-center justify-center rounded-lg text-sm font-medium transition-colors',
                    p === page ? 'bg-indigo-600 text-white' : 'border border-gray-300 text-gray-600 hover:bg-gray-50']">
                  {{ p }}
                </button>
                <button @click="page++" :disabled="page === totalPages" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- LOGS -->
        <div v-show="tab === 'logs'">
          <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100"><h3 class="text-sm font-semibold text-gray-900">Error Logs</h3></div>
            <div class="divide-y divide-gray-100">
              <div v-for="t in taskList.filter(x => x.failure_reason)" :key="'log-'+t.id" class="px-6 py-4">
                <div class="flex items-start gap-3">
                  <span class="w-2 h-2 rounded-full bg-red-500 mt-1.5 flex-shrink-0"></span>
                  <div>
                    <p class="text-sm font-medium text-gray-900">Task #{{ t.task_index + 1 }} — {{ t.failure_reason }}</p>
                    <p class="text-xs text-gray-400 mt-1">Retries: {{ t.retry_count }}/{{ t.max_retries }}</p>
                  </div>
                </div>
              </div>
              <div v-if="!taskList.some(x => x.failure_reason)" class="px-6 py-8 text-center text-sm text-gray-400">No error logs</div>
            </div>
          </div>
        </div>

        <!-- METRICS -->
        <div v-show="tab === 'metrics'">
          <div class="grid grid-cols-3 gap-6">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 text-center"><p class="text-3xl font-bold text-indigo-600">{{ pct }}%</p><p class="text-sm text-gray-500 mt-1">Completion Rate</p></div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 text-center"><p class="text-3xl font-bold text-green-600">{{ stats.done }}</p><p class="text-sm text-gray-500 mt-1">Tasks Completed</p></div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 text-center"><p class="text-3xl font-bold text-red-600">{{ stats.failed }}</p><p class="text-sm text-gray-500 mt-1">Tasks Failed</p></div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 text-center"><p class="text-3xl font-bold text-yellow-600">{{ stats.running }}</p><p class="text-sm text-gray-500 mt-1">Currently Running</p></div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 text-center"><p class="text-3xl font-bold text-gray-600">{{ stats.pending }}</p><p class="text-sm text-gray-500 mt-1">Pending</p></div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 text-center"><p class="text-3xl font-bold text-gray-900">{{ jobDuration }}</p><p class="text-sm text-gray-500 mt-1">Total Duration</p></div>
          </div>
        </div>

      </main>
    </div>
  </div>
</template>
<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import UserSidebar from '@/components/UserSidebar.vue'
import api from '@/api'

const route = useRoute()

// ── plain reactive state (no computed chains that could break) ─────────────────
const job       = ref(null)
const taskList  = ref([])    // raw array from API — populated in loadData()
const loading   = ref(true)
const pageError = ref('')
const actionsOpen = ref(false)
const tab       = ref('overview')
const search    = ref('')
const statusFilter = ref('')
const page      = ref(1)
const perPage   = 15
let timer       = null

const tabs = [
  { id: 'overview', label: 'Overview' },
  { id: 'tasks',    label: 'Tasks' },
  { id: 'logs',     label: 'Logs' },
  { id: 'metrics',  label: 'Metrics' },
]

// ── derived values ─────────────────────────────────────────────────────────────

const stats = computed(() => {
  const s = { pending: 0, assigned: 0, running: 0, done: 0, failed: 0, cancelled: 0 }
  for (const t of taskList.value) {
    if (t.status in s) s[t.status]++
  }
  return s
})

const doneCount = computed(() => stats.value.done)

const pct = computed(() => {
  if (!job.value || !job.value.total_tasks) return 0
  const done = taskList.value.length > 0 ? doneCount.value : (job.value.completed_tasks || 0)
  return Math.round((done / job.value.total_tasks) * 100)
})

// visibleTasks = taskList filtered by statusFilter + search
const visibleTasks = computed(() => {
  const src = taskList.value   // direct ref — no intermediate variable
  if (!statusFilter.value && !search.value.trim()) return src

  const sf = statusFilter.value
  const sq = search.value.trim().toLowerCase()

  return src.filter(t => {
    if (sf && t.status !== sf) return false
    if (sq) {
      const haystack = [
        String(t.task_index + 1),
        String(t.id),
        t.status || '',
        t.worker?.worker_key || '',
        t.failure_reason || '',
      ].join(' ').toLowerCase()
      if (!haystack.includes(sq)) return false
    }
    return true
  })
})

const totalPages = computed(() => Math.max(1, Math.ceil(visibleTasks.value.length / perPage)))

const pagedTasks = computed(() => {
  const start = (page.value - 1) * perPage
  return visibleTasks.value.slice(start, start + perPage)
})

const pageNums = computed(() => {
  const total = totalPages.value
  const cur   = page.value
  const nums  = []
  for (let i = Math.max(1, cur - 2); i <= Math.min(total, cur + 2); i++) nums.push(i)
  return nums
})

const estCompletion = computed(() => {
  if (!job.value) return '—'
  if (job.value.status === 'completed') return job.value.completed_at ? fmtDate(job.value.completed_at) : '—'
  if (!job.value.started_at || !job.value.completed_tasks) return 'Calculating...'
  const elapsed = Date.now() - new Date(job.value.started_at).getTime()
  const avg = elapsed / job.value.completed_tasks
  const rem = (job.value.total_tasks - job.value.completed_tasks) * avg
  return new Date(Date.now() + rem).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
})

const jobDuration = computed(() => {
  if (!job.value?.started_at) return '—'
  const end = job.value.completed_at ? new Date(job.value.completed_at) : new Date()
  const ms  = end - new Date(job.value.started_at)
  const h = Math.floor(ms / 3600000)
  const m = Math.floor((ms % 3600000) / 60000)
  const s = Math.floor((ms % 60000) / 1000)
  return h > 0 ? `${h}h ${m}m ${s}s` : m > 0 ? `${m}m ${s}s` : `${s}s`
})

// reset page when filters change
watch([statusFilter, search], () => { page.value = 1 })

// ── helpers ────────────────────────────────────────────────────────────────────

function fmtDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}
function fmtTime(d) {
  if (!d) return '—'
  return new Date(d).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
}
function taskDur(t) {
  if (!t.started_at || !t.completed_at) return '—'
  const ms = new Date(t.completed_at) - new Date(t.started_at)
  const s  = Math.floor(ms / 1000)
  return s < 60 ? `${s}s` : `${Math.floor(s / 60)}m ${s % 60}s`
}
function buildTaskName(t) {
  const type = (job.value?.type || 'task').replace(/_/g, ' ')
  return `Process ${type} ${t.task_index + 1}`
}
function dotClass(s)  { return { pending:'bg-yellow-400', running:'bg-blue-500', completed:'bg-green-500', failed:'bg-red-500', cancelled:'bg-gray-400' }[s] || 'bg-gray-400' }
function textClass(s) { return { pending:'text-yellow-600', running:'text-blue-600', completed:'text-green-600', failed:'text-red-600', cancelled:'text-gray-500' }[s] || 'text-gray-600' }
function badgeClass(s){ return { pending:'bg-yellow-100 text-yellow-700', assigned:'bg-blue-100 text-blue-700', running:'bg-orange-100 text-orange-700', done:'bg-green-100 text-green-700', failed:'bg-red-100 text-red-700', cancelled:'bg-gray-100 text-gray-600' }[s] || 'bg-gray-100 text-gray-600' }
function prioLabel(p) { return { 3:'Low', 5:'Normal', 7:'High', 10:'Urgent' }[p] || 'Normal' }
function prioClass(p) { return { 3:'text-green-600', 5:'text-blue-600', 7:'text-yellow-600', 10:'text-red-600' }[p] || 'text-blue-600' }

// ── data loading ───────────────────────────────────────────────────────────────

async function loadData() {
  const id = route.params.id
  try {
    // fetch job + tasks in parallel
    const [jr, tr] = await Promise.all([
      api.get(`/jobs/${id}`),
      api.get(`/jobs/${id}/tasks`),
    ])

    // job detail: { job: {...}, progress, pending_tasks }
    job.value = jr.data.job || jr.data

    // tasks: { job_id, tasks: [...] }
    const raw = tr.data.tasks
    // Force a brand-new array so Vue's reactivity always triggers
    taskList.value = Array.isArray(raw) ? raw.slice() : []

    pageError.value = ''
  } catch (e) {
    pageError.value = e.response?.data?.message || 'Failed to load job details'
  } finally {
    loading.value = false
  }
}

async function doCancel() {
  if (!confirm('Cancel this job? Running tasks will be stopped.')) return
  try {
    await api.delete(`/jobs/${route.params.id}`)
    await loadData()
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to cancel job')
  }
}

onMounted(() => {
  loadData()
  timer = setInterval(() => {
    if (job.value && ['running', 'pending'].includes(job.value.status)) loadData()
  }, 8000)
})
onUnmounted(() => clearInterval(timer))
</script>
