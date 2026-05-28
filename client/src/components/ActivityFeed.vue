<template>
  <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
      <h3 class="text-sm font-bold text-gray-900">Activity Feed</h3>
      <span class="text-xs text-gray-500">Last 20 events</span>
    </div>

    <!-- Events List -->
    <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
      <!-- Event items (Requirement 11.1: reverse chronological order) -->
      <div
        v-for="event in displayEvents"
        :key="event.id"
        class="px-6 py-4 hover:bg-gray-50 transition-colors activity-event"
        :class="{ 'fade-in': event.isNew }"
      >
        <div class="flex items-start gap-3">
          <!-- Event type icon (Requirement 11.6) -->
          <div
            class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0"
            :class="eventIconBgClass(event.type)"
          >
            <svg
              class="w-5 h-5"
              :class="eventIconColorClass(event.type)"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                v-if="event.type === 'completion'"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
              />
              <path
                v-else-if="event.type === 'failure'"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
              />
              <path
                v-else-if="event.type === 'retry'"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
              />
            </svg>
          </div>

          <!-- Event details (Requirement 11.6: descriptive message) -->
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900">
              {{ event.message }}
            </p>
            <p class="text-xs text-gray-500 mt-1">
              <!-- Timestamp (Requirement 11.6) -->
              {{ formatTimestamp(event.timestamp) }}
            </p>
          </div>

          <!-- Event badge -->
          <span
            class="px-2.5 py-1 text-xs font-bold rounded-full uppercase tracking-wide flex-shrink-0"
            :class="eventBadgeClass(event.type)"
          >
            {{ event.type }}
          </span>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="displayEvents.length === 0" class="px-6 py-12 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
          <svg
            class="w-8 h-8 text-gray-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
        </div>
        <p class="text-sm font-medium text-gray-500">No activity yet</p>
        <p class="text-xs text-gray-400 mt-1">Events will appear here as tasks are processed</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  tasks: {
    type: Array,
    required: true,
    default: () => [],
  },
})

// Track previous task states to detect new events
const previousTaskStates = ref(new Map())

// Store events with metadata
const events = ref([])

/**
 * Extract events from tasks (Requirement 11.2: completion, failure, retry)
 * Events are derived from task status changes and timestamps
 */
function extractEventsFromTasks(tasks) {
  const newEvents = []

  tasks.forEach((task) => {
    const taskId = task.id
    const previousState = previousTaskStates.value.get(taskId)

    // Task completion event (Requirement 11.2)
    if (task.status === 'done' && task.completed_at) {
      const eventId = `${taskId}-completion-${task.completed_at}`
      if (!events.value.some((e) => e.id === eventId)) {
        newEvents.push({
          id: eventId,
          type: 'completion',
          message: `Task #${task.task_index + 1} completed successfully`,
          timestamp: task.completed_at,
          taskId: task.id,
          isNew: previousState !== undefined, // Mark as new if we've seen this task before
        })
      }
    }

    // Task failure event (Requirement 11.2)
    if (task.status === 'failed' && task.completed_at) {
      const eventId = `${taskId}-failure-${task.completed_at}`
      if (!events.value.some((e) => e.id === eventId)) {
        const reason = task.failure_reason ? `: ${task.failure_reason}` : ''
        newEvents.push({
          id: eventId,
          type: 'failure',
          message: `Task #${task.task_index + 1} failed${reason}`,
          timestamp: task.completed_at,
          taskId: task.id,
          isNew: previousState !== undefined,
        })
      }
    }

    // Task retry event (Requirement 11.2)
    if (task.retry_count > 0) {
      const retryEventId = `${taskId}-retry-${task.retry_count}`
      if (!events.value.some((e) => e.id === retryEventId)) {
        // Only add retry event if retry count changed
        if (!previousState || previousState.retry_count !== task.retry_count) {
          newEvents.push({
            id: retryEventId,
            type: 'retry',
            message: `Task #${task.task_index + 1} retry attempt ${task.retry_count}/${task.max_retries}`,
            timestamp: task.updated_at || task.started_at || new Date().toISOString(),
            taskId: task.id,
            isNew: previousState !== undefined,
          })
        }
      }
    }

    // Update previous state
    previousTaskStates.value.set(taskId, {
      status: task.status,
      retry_count: task.retry_count,
      completed_at: task.completed_at,
    })
  })

  // Add new events to the beginning of the array (Requirement 11.4: prepend new events)
  if (newEvents.length > 0) {
    events.value = [...newEvents, ...events.value]
  }
}

/**
 * Display events in reverse chronological order, limited to 20 most recent
 * (Requirement 11.1: reverse chronological order)
 * (Requirement 11.5: limit to 20 events)
 */
const displayEvents = computed(() => {
  // Sort by timestamp descending (newest first)
  const sorted = [...events.value].sort((a, b) => {
    return new Date(b.timestamp) - new Date(a.timestamp)
  })

  // Limit to 20 most recent events (Requirement 11.5)
  return sorted.slice(0, 20)
})

/**
 * Format timestamp for display (Requirement 11.6)
 */
function formatTimestamp(timestamp) {
  if (!timestamp) return '—'
  const date = new Date(timestamp)
  const now = new Date()
  const diffMs = now - date
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)

  // Relative time for recent events
  if (diffMins < 1) return 'Just now'
  if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`
  if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`
  if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`

  // Absolute time for older events
  return date.toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

/**
 * Event icon background color classes (Requirement 11.6)
 */
function eventIconBgClass(type) {
  const classes = {
    completion: 'bg-green-100',
    failure: 'bg-red-100',
    retry: 'bg-orange-100',
  }
  return classes[type] || 'bg-gray-100'
}

/**
 * Event icon color classes (Requirement 11.6)
 */
function eventIconColorClass(type) {
  const classes = {
    completion: 'text-green-600',
    failure: 'text-red-600',
    retry: 'text-orange-600',
  }
  return classes[type] || 'text-gray-600'
}

/**
 * Event badge classes (Requirement 11.6)
 */
function eventBadgeClass(type) {
  const classes = {
    completion: 'bg-green-100 text-green-700',
    failure: 'bg-red-100 text-red-700',
    retry: 'bg-orange-100 text-orange-700',
  }
  return classes[type] || 'bg-gray-100 text-gray-600'
}

// Watch for task changes and extract events (Requirement 11.3: poll for new events)
watch(
  () => props.tasks,
  (newTasks) => {
    if (newTasks && newTasks.length > 0) {
      extractEventsFromTasks(newTasks)
    }
  },
  { immediate: true, deep: true }
)

// Remove 'isNew' flag after animation completes (Requirement 11.4: fade-in animation)
watch(
  displayEvents,
  () => {
    setTimeout(() => {
      events.value = events.value.map((e) => ({ ...e, isNew: false }))
    }, 1000) // Match animation duration
  },
  { deep: true }
)
</script>

<style scoped>
/* Fade-in animation for new events (Requirement 11.4) */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.fade-in {
  animation: fadeIn 0.5s ease-out;
}

/* Smooth transitions */
.activity-event {
  transition: background-color 0.2s ease;
}

/* Custom scrollbar for activity feed */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f3f4f6;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}
</style>
