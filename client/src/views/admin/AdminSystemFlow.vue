<template>
  <div class="w-full h-full">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
      <div class="px-8 py-4 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">System Flow</h1>
          <p class="text-sm text-gray-500 mt-0.5">How the distributed task scheduler works — end to end</p>
        </div>
        <div class="flex items-center gap-2 px-3 py-1.5 bg-indigo-50 border border-indigo-100 rounded-lg">
          <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
          <span class="text-xs font-medium text-indigo-700">Live System</span>
        </div>
      </div>
    </header>

    <main class="p-8 max-w-6xl mx-auto">

      <!-- ── Mental Model ─────────────────────────────────────────────────── -->
      <div class="grid grid-cols-4 gap-4 mb-8">
        <div v-for="concept in mentalModel" :key="concept.term"
          :class="['rounded-xl p-4 border-2 text-center', concept.bg, concept.border]">
          <div class="text-2xl mb-2">{{ concept.emoji }}</div>
          <p :class="['text-xs font-bold uppercase tracking-wide mb-1', concept.labelColor]">
            {{ concept.term }}
          </p>
          <p class="text-xs text-gray-600 font-medium">{{ concept.analogy }}</p>
        </div>
      </div>

      <!-- ── Complete Flow ────────────────────────────────────────────────── -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-indigo-600 to-purple-600">
          <h2 class="text-base font-bold text-white">Complete System Flow</h2>
          <p class="text-xs text-indigo-200 mt-0.5">Click any step to learn more</p>
        </div>

        <div class="p-6">
          <div class="space-y-2">
            <div v-for="(step, idx) in flowSteps" :key="idx">
              <!-- Step row -->
              <div
                @click="activeStep = activeStep === idx ? null : idx"
                :class="[
                  'flex items-start gap-4 p-4 rounded-xl border-2 cursor-pointer transition-all',
                  activeStep === idx
                    ? step.activeBg + ' ' + step.activeBorder
                    : 'bg-gray-50 border-gray-100 hover:border-gray-200'
                ]">

                <!-- Step number + icon -->
                <div :class="['w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 text-white font-bold text-sm', step.iconBg]">
                  {{ idx + 1 }}
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-3">
                    <p class="text-sm font-bold text-gray-900">{{ step.title }}</p>
                    <span :class="['px-2 py-0.5 text-[10px] font-bold rounded-full', step.badgeBg, step.badgeText]">
                      {{ step.badge }}
                    </span>
                  </div>
                  <p class="text-xs text-gray-500 mt-0.5">{{ step.subtitle }}</p>
                </div>

                <!-- Expand chevron -->
                <svg :class="['w-4 h-4 text-gray-400 transition-transform flex-shrink-0 mt-1',
                  activeStep === idx ? 'rotate-180' : '']"
                  fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
              </div>

              <!-- Expanded detail -->
              <div v-if="activeStep === idx"
                :class="['mx-4 mb-2 p-4 rounded-xl border', step.activeBg, step.activeBorder]">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <p class="text-xs font-semibold text-gray-700 mb-2">What happens</p>
                    <p class="text-xs text-gray-600 leading-relaxed">{{ step.detail }}</p>
                  </div>
                  <div>
                    <p class="text-xs font-semibold text-gray-700 mb-2">API / Code</p>
                    <div class="bg-gray-900 rounded-lg p-3">
                      <p class="text-xs text-green-400 font-mono">{{ step.code }}</p>
                    </div>
                  </div>
                </div>
                <div v-if="step.analogy" class="mt-3 flex items-start gap-2 p-2.5 bg-white/60 rounded-lg">
                  <span class="text-base flex-shrink-0">🛒</span>
                  <p class="text-xs text-gray-600 italic">{{ step.analogy }}</p>
                </div>
              </div>

              <!-- Arrow between steps -->
              <div v-if="idx < flowSteps.length - 1" class="flex justify-center py-1">
                <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd"/>
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Live System Stats ────────────────────────────────────────────── -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h2 class="text-sm font-bold text-gray-900">Live System State</h2>
          <div class="flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
            <span class="text-xs text-gray-500">Updates every 5s</span>
          </div>
        </div>
        <div class="p-6">
          <div v-if="loadingStats" class="text-center py-6">
            <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div>
          </div>
          <div v-else class="grid grid-cols-3 gap-6">
            <!-- Jobs -->
            <div class="space-y-3">
              <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Jobs</p>
              <div v-for="s in jobStats" :key="s.label" class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span :class="['w-2 h-2 rounded-full', s.dot]"></span>
                  <span class="text-xs text-gray-600">{{ s.label }}</span>
                </div>
                <span :class="['text-sm font-bold', s.color]">{{ s.value }}</span>
              </div>
            </div>
            <!-- Tasks -->
            <div class="space-y-3">
              <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Tasks</p>
              <div v-for="s in taskStats" :key="s.label" class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span :class="['w-2 h-2 rounded-full', s.dot]"></span>
                  <span class="text-xs text-gray-600">{{ s.label }}</span>
                </div>
                <span :class="['text-sm font-bold', s.color]">{{ s.value }}</span>
              </div>
            </div>
            <!-- Workers -->
            <div class="space-y-3">
              <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Workers</p>
              <div v-for="s in workerStats" :key="s.label" class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span :class="['w-2 h-2 rounded-full', s.dot]"></span>
                  <span class="text-xs text-gray-600">{{ s.label }}</span>
                </div>
                <span :class="['text-sm font-bold', s.color]">{{ s.value }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Key Concepts ─────────────────────────────────────────────────── -->
      <div class="grid grid-cols-2 gap-6 mb-8">
        <div v-for="concept in keyConcepts" :key="concept.title"
          :class="['rounded-xl border-2 p-5', concept.bg, concept.border]">
          <div class="flex items-start gap-3 mb-3">
            <span class="text-xl flex-shrink-0">{{ concept.emoji }}</span>
            <div>
              <p :class="['text-sm font-bold', concept.titleColor]">{{ concept.title }}</p>
              <p class="text-xs text-gray-500 mt-0.5">{{ concept.subtitle }}</p>
            </div>
          </div>
          <p class="text-xs text-gray-600 leading-relaxed">{{ concept.description }}</p>
          <div class="mt-3 bg-gray-900 rounded-lg p-2.5">
            <p class="text-[11px] text-green-400 font-mono">{{ concept.code }}</p>
          </div>
        </div>
      </div>

    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useAdminStore } from '@/stores/adminStore'

const adminStore = useAdminStore()
const activeStep = ref(null)
const loadingStats = ref(true)
let statsTimer = null

// ── Mental model cards ────────────────────────────────────────────────────────
const mentalModel = [
  { emoji: '📦', term: 'Job',            analogy: 'Big Project',           bg: 'bg-indigo-50',  border: 'border-indigo-200', labelColor: 'text-indigo-700' },
  { emoji: '🧩', term: 'Task',           analogy: 'Small Piece',           bg: 'bg-blue-50',    border: 'border-blue-200',   labelColor: 'text-blue-700'   },
  { emoji: '🤖', term: 'Worker',         analogy: 'Employee',              bg: 'bg-purple-50',  border: 'border-purple-200', labelColor: 'text-purple-700' },
  { emoji: '🎯', term: 'Coordinator',    analogy: 'Manager',               bg: 'bg-green-50',   border: 'border-green-200',  labelColor: 'text-green-700'  },
  { emoji: '⚖️', term: 'Load Balancing', analogy: 'Smart Distribution',    bg: 'bg-yellow-50',  border: 'border-yellow-200', labelColor: 'text-yellow-700' },
  { emoji: '🔄', term: 'Retry',          analogy: 'Try Again',             bg: 'bg-orange-50',  border: 'border-orange-200', labelColor: 'text-orange-700' },
  { emoji: '💓', term: 'Heartbeat',      analogy: '"I\'m still alive"',    bg: 'bg-red-50',     border: 'border-red-200',    labelColor: 'text-red-700'    },
  { emoji: '🛡️', term: 'Fault Tolerance',analogy: 'Work Never Lost',       bg: 'bg-teal-50',    border: 'border-teal-200',   labelColor: 'text-teal-700'   },
]

// ── Complete flow steps ───────────────────────────────────────────────────────
const flowSteps = [
  {
    title:        'User Submits Job',
    subtitle:     'User fills the form and clicks Submit',
    badge:        'Step 1',
    iconBg:       'bg-indigo-600',
    activeBg:     'bg-indigo-50',
    activeBorder: 'border-indigo-300',
    badgeBg:      'bg-indigo-100',
    badgeText:    'text-indigo-800',
    detail:       'User provides job name, type (e.g. result_processing), task count, and priority. The frontend sends POST /jobs to the backend.',
    code:         'POST /api/jobs  { name, type, task_count, priority }',
    analogy:      'Like a manager receiving a big project request from a client.',
  },
  {
    title:        'Coordinator Creates Job Record',
    subtitle:     'One row inserted into scheduler_jobs table with status: pending',
    badge:        'Step 2',
    iconBg:       'bg-blue-600',
    activeBg:     'bg-blue-50',
    activeBorder: 'border-blue-300',
    badgeBg:      'bg-blue-100',
    badgeText:    'text-blue-800',
    detail:       'JobController creates a SchedulerJob record. Status starts as "pending". The job is the container — it holds all the tasks.',
    code:         'SchedulerJob::create([status => "pending", total_tasks => N])',
    analogy:      'Manager opens a new project folder and writes the project name on it.',
  },
  {
    title:        'Coordinator Splits Into Tasks',
    subtitle:     'TaskPartitionerService divides work into N equal chunks',
    badge:        'Step 3',
    iconBg:       'bg-purple-600',
    activeBg:     'bg-purple-50',
    activeBorder: 'border-purple-300',
    badgeBg:      'bg-purple-100',
    badgeText:    'text-purple-800',
    detail:       '1,000 records ÷ 10 tasks = 100 records per task. Each task gets a payload with its exact record range (e.g. records 1→100). All tasks start as "pending".',
    code:         'Task 1: records 1→100\nTask 2: records 101→200\n...\nTask 10: records 901→1000',
    analogy:      'Manager divides the project into 10 equal pieces and puts each in a separate folder.',
  },
  {
    title:        'Workers Request Tasks (Pull-Based)',
    subtitle:     'Workers constantly ask: "Do you have work for me?"',
    badge:        'Step 4',
    iconBg:       'bg-cyan-600',
    activeBg:     'bg-cyan-50',
    activeBorder: 'border-cyan-300',
    badgeBg:      'bg-cyan-100',
    badgeText:    'text-cyan-800',
    detail:       'Workers poll GET /tasks/next. The coordinator uses SELECT FOR UPDATE to atomically assign the next pending task. No two workers ever get the same task.',
    code:         'GET /api/tasks/next  →  { task: { id, payload, ... } }',
    analogy:      'Employees walk up to the manager and ask "What should I work on?" — manager never pushes work.',
  },
  {
    title:        'Coordinator Assigns Tasks',
    subtitle:     'pending → assigned, worker becomes busy',
    badge:        'Step 5',
    iconBg:       'bg-yellow-600',
    activeBg:     'bg-yellow-50',
    activeBorder: 'border-yellow-300',
    badgeBg:      'bg-yellow-100',
    badgeText:    'text-yellow-800',
    detail:       'Task status changes to "assigned", worker_id is set, worker status becomes "busy". Job status changes from "pending" to "running" on first assignment.',
    code:         'task.update({ status: "assigned", worker_id: worker.id })\nworker.update({ status: "busy" })',
    analogy:      'Manager hands the folder to the employee and marks it as "in progress".',
  },
  {
    title:        'Workers Process in Parallel',
    subtitle:     'Multiple workers execute simultaneously — this is the speed',
    badge:        'Step 6',
    iconBg:       'bg-green-600',
    activeBg:     'bg-green-50',
    activeBorder: 'border-green-300',
    badgeBg:      'bg-green-100',
    badgeText:    'text-green-800',
    detail:       'Worker calls POST /tasks/{id}/start (assigned→running), processes its records, then POST /tasks/{id}/complete (running→done). 3 workers = 3× faster than 1.',
    code:         'POST /tasks/{id}/start    → running\nPOST /tasks/{id}/complete → done',
    analogy:      'Three employees work on three different folders at the same time.',
  },
  {
    title:        'Fast Workers Get More Tasks (Load Balancing)',
    subtitle:     'No manual assignment — pull-based system balances automatically',
    badge:        'Step 7',
    iconBg:       'bg-orange-600',
    activeBg:     'bg-orange-50',
    activeBorder: 'border-orange-300',
    badgeBg:      'bg-orange-100',
    badgeText:    'text-orange-800',
    detail:       'When a worker finishes, it immediately asks for the next task. Fast workers finish sooner → ask more often → get more tasks. No AI, no configuration needed.',
    code:         '// Worker-1 finishes Task 1 → immediately asks for Task 4\n// Worker-3 still on Task 3 → gets nothing yet',
    analogy:      'Fast cashier finishes serving a customer and immediately calls "Next!" — slow cashier still serving their customer.',
  },
  {
    title:        'Failed Tasks Retry with Backoff',
    subtitle:     'retry_count < max_retries → re-queue with exponential delay',
    badge:        'Step 8',
    iconBg:       'bg-red-600',
    activeBg:     'bg-red-50',
    activeBorder: 'border-red-300',
    badgeBg:      'bg-red-100',
    badgeText:    'text-red-800',
    detail:       'If a task fails, the system checks retry_count < max_retries. If yes, task is re-queued with delay = 2^retry_count × 5s (5s, 10s, 20s, 40s). Prevents overload.',
    code:         'delay = 2^retry_count × 5\nAttempt 1: 5s  Attempt 2: 10s\nAttempt 3: 20s  Permanent: failed',
    analogy:      'Employee makes a mistake → waits a bit → tries again. After 3 tries, escalates to manager.',
  },
  {
    title:        'Dead Worker Tasks Reassigned',
    subtitle:     'Heartbeat stops → worker declared dead → task reset to pending',
    badge:        'Step 9',
    iconBg:       'bg-gray-600',
    activeBg:     'bg-gray-50',
    activeBorder: 'border-gray-300',
    badgeBg:      'bg-gray-100',
    badgeText:    'text-gray-800',
    detail:       'Workers send heartbeats every 30s. If 45s pass with no heartbeat, the scheduler marks the worker dead and resets its task to pending. Another worker picks it up.',
    code:         'POST /workers/{key}/heartbeat  (every 30s)\nphp artisan system:monitor-health  (every 1min)',
    analogy:      'Employee stops responding → manager reassigns their work to another employee. No work is lost.',
  },
  {
    title:        'All Tasks Complete → Job Completed',
    subtitle:     'JobStatusService detects 100% → job.status = "completed"',
    badge:        'Step 10',
    iconBg:       'bg-emerald-600',
    activeBg:     'bg-emerald-50',
    activeBorder: 'border-emerald-300',
    badgeBg:      'bg-emerald-100',
    badgeText:    'text-emerald-800',
    detail:       'After every task completion, JobStatusService.recalculate() checks if all tasks are done. When completed_tasks === total_tasks, job status becomes "completed". User sees 100%.',
    code:         'if (completedCount === totalTasks) → status = "completed"\nGET /jobs/{id}/completion  → { progress: 100, duration, ... }',
    analogy:      'All employees finish their folders → manager closes the project and marks it DONE.',
  },
]

// ── Key concepts ──────────────────────────────────────────────────────────────
const keyConcepts = [
  {
    emoji:      '🔒',
    title:      'Race Condition Prevention',
    subtitle:   'SELECT FOR UPDATE',
    titleColor: 'text-indigo-700',
    bg:         'bg-indigo-50',
    border:     'border-indigo-200',
    description:'When 100 workers ask for a task simultaneously, only ONE gets it. The database row is locked during the transaction so no two workers can claim the same task.',
    code:       'Task::lockForUpdate()->where(status, "pending")->first()',
  },
  {
    emoji:      '💓',
    title:      'Heartbeat & Fault Tolerance',
    subtitle:   'Workers say "I\'m alive" every 30s',
    titleColor: 'text-red-700',
    bg:         'bg-red-50',
    border:     'border-red-200',
    description:'If a worker crashes (power off, network gone), its heartbeat stops. After 45s, the coordinator declares it dead and reassigns its task. No task is ever permanently lost.',
    code:       'POST /workers/{key}/heartbeat\n// 45s silence → worker = dead → task = pending',
  },
  {
    emoji:      '⚡',
    title:      'Automatic Load Balancing',
    subtitle:   'No configuration needed',
    titleColor: 'text-yellow-700',
    bg:         'bg-yellow-50',
    border:     'border-yellow-200',
    description:'Fast workers finish tasks sooner and immediately pull the next one. Slow workers get fewer tasks. The pull-based model naturally distributes work proportionally to worker speed.',
    code:       '// Worker-1 (fast): 5 tasks done\n// Worker-2 (medium): 3 tasks done\n// Worker-3 (slow): 2 tasks done',
  },
  {
    emoji:      '🔄',
    title:      'Exponential Backoff Retry',
    subtitle:   '2^attempt × 5 seconds',
    titleColor: 'text-orange-700',
    bg:         'bg-orange-50',
    border:     'border-orange-200',
    description:'Failed tasks are not retried immediately — that would hammer a failing service. Instead, delays grow exponentially: 5s → 10s → 20s. After max_retries, the task permanently fails.',
    code:       'delay = pow(2, retry_count) * 5\n// 5s, 10s, 20s, then permanent failure',
  },
]

// ── Live stats ────────────────────────────────────────────────────────────────
async function loadStats() {
  try {
    await adminStore.fetchDashboardData({ limit: 5 })
  } catch {
    // Non-critical
  } finally {
    loadingStats.value = false
  }
}

const jobStats = computed(() => {
  const metrics = adminStore.systemMetrics
  const j = metrics?.jobs || metrics?.realtime?.jobs || {}
  return [
    { label: 'Total',     value: j.total     ?? 0, dot: 'bg-gray-400',   color: 'text-gray-700'   },
    { label: 'Running',   value: j.running   ?? 0, dot: 'bg-blue-500 animate-pulse', color: 'text-blue-600'   },
    { label: 'Completed', value: j.completed ?? 0, dot: 'bg-green-500',  color: 'text-green-600'  },
    { label: 'Failed',    value: j.failed    ?? 0, dot: 'bg-red-500',    color: 'text-red-600'    },
    { label: 'Pending',   value: j.pending   ?? 0, dot: 'bg-yellow-500', color: 'text-yellow-600' },
  ]
})

const taskStats = computed(() => {
  const metrics = adminStore.systemMetrics
  const t = metrics?.tasks || metrics?.realtime?.tasks || {}
  return [
    { label: 'Total',    value: t.total    ?? 0, dot: 'bg-gray-400',   color: 'text-gray-700'   },
    { label: 'Running',  value: t.running  ?? 0, dot: 'bg-indigo-500 animate-pulse', color: 'text-indigo-600' },
    { label: 'Done',     value: t.done || t.completed ?? 0, dot: 'bg-green-500',  color: 'text-green-600'  },
    { label: 'Pending',  value: t.pending  ?? 0, dot: 'bg-yellow-500', color: 'text-yellow-600' },
    { label: 'Failed',   value: t.failed   ?? 0, dot: 'bg-red-500',    color: 'text-red-600'    },
  ]
})

const workerStats = computed(() => {
  const metrics = adminStore.systemMetrics
  const w = metrics?.workers || metrics?.realtime?.workers || {}
  return [
    { label: 'Total',  value: w.total  ?? 0, dot: 'bg-gray-400',   color: 'text-gray-700'   },
    { label: 'Busy',   value: w.busy   ?? 0, dot: 'bg-blue-500 animate-pulse', color: 'text-blue-600'   },
    { label: 'Idle',   value: w.idle   ?? 0, dot: 'bg-yellow-500', color: 'text-yellow-600' },
    { label: 'Dead',   value: w.dead   ?? 0, dot: 'bg-red-500',    color: 'text-red-600'    },
    { label: 'Active', value: w.active ?? 0, dot: 'bg-green-500',  color: 'text-green-600'  },
  ]
})

onMounted(() => {
  loadStats()
  statsTimer = setInterval(loadStats, 5000)
})
onUnmounted(() => {
  if (statsTimer) clearInterval(statsTimer)
})
</script>
