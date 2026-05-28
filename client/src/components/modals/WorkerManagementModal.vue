<template>
  <Teleport to="body">
    <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">

        <!-- Background overlay -->
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="close"></div>

        <!-- Centering trick -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div class="relative inline-block w-full max-w-3xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-2xl shadow-2xl z-10">

        <!-- Header -->
        <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-indigo-600 to-purple-600">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
              </div>
              <div>
                <h3 class="text-xl font-bold text-white">Worker Management</h3>
                <p class="text-sm text-indigo-100">Start and manage worker processes</p>
              </div>
            </div>
            <button @click="close" class="text-white/80 hover:text-white transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Content -->
        <div class="px-6 py-6">

          <!-- Tabs -->
          <div class="flex gap-2 mb-6 p-1 bg-gray-100 rounded-lg">
            <button
              @click="activeTab = 'start'"
              :class="[
                'flex-1 px-4 py-2 text-sm font-medium rounded-md transition-all',
                activeTab === 'start'
                  ? 'bg-white text-indigo-600 shadow-sm'
                  : 'text-gray-600 hover:text-gray-900'
              ]"
            >
              <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              Start Workers
            </button>
            <button
              @click="activeTab = 'manage'"
              :class="[
                'flex-1 px-4 py-2 text-sm font-medium rounded-md transition-all',
                activeTab === 'manage'
                  ? 'bg-white text-indigo-600 shadow-sm'
                  : 'text-gray-600 hover:text-gray-900'
              ]"
            >
              <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              Manage Active
            </button>
          </div>

          <!-- Start Workers Tab -->
          <div v-if="activeTab === 'start'" class="space-y-6">

            <!-- Quick Start Section -->
            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl p-5 border border-indigo-100">
              <h4 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Quick Start
              </h4>

              <div class="grid grid-cols-3 gap-3 mb-4">
                <button
                  v-for="preset in quickStartPresets"
                  :key="preset.count"
                  @click="applyPreset(preset)"
                  class="p-4 bg-white rounded-lg border-2 border-gray-200 hover:border-indigo-500 hover:shadow-md transition-all group"
                >
                  <div class="text-3xl font-bold text-indigo-600 mb-1">{{ preset.count }}</div>
                  <div class="text-xs text-gray-600 group-hover:text-gray-900">{{ preset.label }}</div>
                </button>
              </div>

              <button
                @click="startMultiple"
                :disabled="loading || form.count < 1"
                class="w-full px-4 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-lg hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg hover:shadow-xl"
              >
                <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span v-else>🚀</span>
                Start {{ form.count }} Worker{{ form.count !== 1 ? 's' : '' }}
              </button>
            </div>

            <!-- Advanced Configuration -->
            <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
              <h4 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                </svg>
                Advanced Configuration
              </h4>

              <div class="grid grid-cols-2 gap-4">
                <!-- Worker Count -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Number of Workers
                  </label>
                  <input
                    v-model.number="form.count"
                    type="number"
                    min="1"
                    max="20"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                  />
                  <p class="text-xs text-gray-500 mt-1">Max: 20 workers</p>
                </div>

                <!-- Worker Prefix -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Worker Prefix
                  </label>
                  <input
                    v-model="form.prefix"
                    type="text"
                    placeholder="worker"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                  />
                  <p class="text-xs text-gray-500 mt-1">e.g., "worker" → worker-1, worker-2</p>
                </div>

                <!-- Sleep Interval -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Sleep Interval (seconds)
                  </label>
                  <input
                    v-model.number="form.sleep"
                    type="number"
                    min="1"
                    max="60"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                  />
                  <p class="text-xs text-gray-500 mt-1">Wait time when no tasks available</p>
                </div>

                <!-- Heartbeat Interval -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Heartbeat Interval (seconds)
                  </label>
                  <input
                    v-model.number="form.heartbeat"
                    type="number"
                    min="5"
                    max="120"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                  />
                  <p class="text-xs text-gray-500 mt-1">How often to send heartbeat</p>
                </div>
              </div>
            </div>

          </div>

          <!-- Manage Active Workers Tab -->
          <div v-if="activeTab === 'manage'" class="space-y-4">

            <!-- Actions Bar -->
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
              <div class="flex items-center gap-3">
                <button
                  @click="refreshProcesses"
                  :disabled="loading"
                  class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50"
                >
                  <svg :class="['w-4 h-4 inline-block mr-1', loading ? 'animate-spin' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                  </svg>
                  Refresh
                </button>
                <button
                  @click="cleanupProcesses"
                  :disabled="loading"
                  class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50"
                >
                  <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                  Cleanup Stopped
                </button>
              </div>
              <button
                @click="stopAll"
                :disabled="loading || processes.length === 0"
                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                </svg>
                Stop All Workers
              </button>
            </div>

            <!-- Worker Processes List -->
            <div v-if="processes.length === 0" class="text-center py-12">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">No worker processes tracked</h3>
              <p class="mt-1 text-sm text-gray-500">Start workers from the "Start Workers" tab</p>
            </div>

            <div v-else class="space-y-3 max-h-96 overflow-y-auto">
              <div
                v-for="process in processes"
                :key="process.worker_key"
                class="p-4 bg-white border rounded-lg hover:shadow-md transition-all"
                :class="process.is_running ? 'border-green-200' : 'border-gray-200'"
              >
                <div class="flex items-center justify-between">
                  <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                      <h5 class="text-sm font-semibold text-gray-900">{{ process.worker_key }}</h5>
                      <span
                        class="px-2 py-0.5 text-xs font-medium rounded-full"
                        :class="process.is_running
                          ? 'bg-green-100 text-green-800'
                          : 'bg-gray-100 text-gray-800'"
                      >
                        {{ process.status }}
                      </span>
                      <span v-if="process.pid" class="text-xs text-gray-500">
                        PID: {{ process.pid }}
                      </span>
                    </div>
                    <div class="text-xs text-gray-500 space-y-1">
                      <div>Started: {{ formatDate(process.started_at) }}</div>
                      <div v-if="process.options" class="flex gap-3">
                        <span>Sleep: {{ process.options.sleep }}s</span>
                        <span>Heartbeat: {{ process.options.heartbeat }}s</span>
                      </div>
                    </div>
                  </div>
                  <button
                    v-if="process.is_running"
                    @click="stopWorker(process.worker_key)"
                    :disabled="loading"
                    class="px-3 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 disabled:opacity-50"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>

          </div>

        </div>

        <!-- Footer -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-3">
          <button
            @click="close"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
          >
            Close
          </button>
        </div>

      </div>
    </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { adminApi } from '@/api'
import { useToastStore } from '@/stores/toastStore'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['close', 'workersStarted'])

const toastStore = useToastStore()

// State
const activeTab = ref('start')
const loading = ref(false)
const processes = ref([])

// Form data
const form = ref({
  count: 3,
  prefix: 'worker',
  sleep: 2,
  heartbeat: 15,
  fail_rate: 0,
})

// Quick start presets
const quickStartPresets = [
  { count: 3, label: 'Small' },
  { count: 5, label: 'Medium' },
  { count: 10, label: 'Large' },
]

// Methods
const applyPreset = (preset) => {
  form.value.count = preset.count
}

const startMultiple = async () => {
  loading.value = true
  try {
    const result = await adminApi.startMultipleWorkers(form.value.count, {
      prefix: form.value.prefix,
      sleep: form.value.sleep,
      heartbeat: form.value.heartbeat,
      fail_rate: form.value.fail_rate,
    })

    toastStore.success(`Started ${result.successful} out of ${result.total} workers`)
    emit('workersStarted')

    // Switch to manage tab and refresh
    activeTab.value = 'manage'
    await refreshProcesses()

  } catch (error) {
    console.error('Failed to start workers:', error)
    toastStore.error(error.response?.data?.message || 'Failed to start workers')
  } finally {
    loading.value = false
  }
}

const stopWorker = async (workerKey) => {
  loading.value = true
  try {
    await adminApi.stopWorker(workerKey)
    toastStore.success(`Worker ${workerKey} stopped`)
    await refreshProcesses()
  } catch (error) {
    console.error('Failed to stop worker:', error)
    toastStore.error(error.response?.data?.message || 'Failed to stop worker')
  } finally {
    loading.value = false
  }
}

const stopAll = async () => {
  if (!confirm('Are you sure you want to stop all workers?')) {
    return
  }

  loading.value = true
  try {
    const result = await adminApi.stopAllWorkers()
    toastStore.success(result.message)
    await refreshProcesses()
  } catch (error) {
    console.error('Failed to stop all workers:', error)
    toastStore.error(error.response?.data?.message || 'Failed to stop all workers')
  } finally {
    loading.value = false
  }
}

const refreshProcesses = async () => {
  loading.value = true
  try {
    const data = await adminApi.getWorkerProcesses()
    processes.value = Object.values(data.processes || {})
  } catch (error) {
    console.error('Failed to fetch worker processes:', error)
    toastStore.error('Failed to fetch worker processes')
  } finally {
    loading.value = false
  }
}

const cleanupProcesses = async () => {
  loading.value = true
  try {
    const result = await adminApi.cleanupWorkerProcesses()
    toastStore.success(result.message)
    await refreshProcesses()
  } catch (error) {
    console.error('Failed to cleanup processes:', error)
    toastStore.error('Failed to cleanup processes')
  } finally {
    loading.value = false
  }
}

const close = () => {
  emit('close')
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleString()
}

// Watch for modal open
watch(() => props.isOpen, (isOpen) => {
  if (isOpen && activeTab.value === 'manage') {
    refreshProcesses()
  }
})
</script>
