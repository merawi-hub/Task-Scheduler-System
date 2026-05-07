<template>
  <span 
    :class="badgeClasses"
    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
  >
    <span v-if="showDot" :class="dotClasses" class="w-2 h-2 rounded-full mr-1.5"></span>
    {{ displayText }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: {
    type: String,
    required: true
  },
  showDot: {
    type: Boolean,
    default: true
  }
})

const statusConfig = {
  // Job statuses
  pending: {
    bg: 'bg-yellow-100',
    text: 'text-yellow-800',
    dot: 'bg-yellow-400',
    label: 'Pending'
  },
  running: {
    bg: 'bg-blue-100',
    text: 'text-blue-800',
    dot: 'bg-blue-400',
    label: 'Running'
  },
  completed: {
    bg: 'bg-green-100',
    text: 'text-green-800',
    dot: 'bg-green-400',
    label: 'Completed'
  },
  done: {
    bg: 'bg-green-100',
    text: 'text-green-800',
    dot: 'bg-green-400',
    label: 'Done'
  },
  failed: {
    bg: 'bg-red-100',
    text: 'text-red-800',
    dot: 'bg-red-400',
    label: 'Failed'
  },
  cancelled: {
    bg: 'bg-gray-100',
    text: 'text-gray-800',
    dot: 'bg-gray-400',
    label: 'Cancelled'
  },
  // Task statuses
  assigned: {
    bg: 'bg-purple-100',
    text: 'text-purple-800',
    dot: 'bg-purple-400',
    label: 'Assigned'
  },
  // Worker statuses
  idle: {
    bg: 'bg-gray-100',
    text: 'text-gray-800',
    dot: 'bg-gray-400',
    label: 'Idle'
  },
  busy: {
    bg: 'bg-blue-100',
    text: 'text-blue-800',
    dot: 'bg-blue-400',
    label: 'Busy'
  },
  dead: {
    bg: 'bg-red-100',
    text: 'text-red-800',
    dot: 'bg-red-400',
    label: 'Dead'
  }
}

const config = computed(() => {
  const status = props.status?.toLowerCase() || 'pending'
  return statusConfig[status] || statusConfig.pending
})

const badgeClasses = computed(() => {
  return `${config.value.bg} ${config.value.text}`
})

const dotClasses = computed(() => {
  return config.value.dot
})

const displayText = computed(() => {
  return config.value.label
})
</script>
