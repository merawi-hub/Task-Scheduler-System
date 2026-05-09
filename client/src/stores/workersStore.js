import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import apiClient from '@/api/axios'

export const useWorkersStore = defineStore('workers', () => {
  // ── State ──────────────────────────────────────────────────────────────────
  const workers = ref([])
  const loading = ref(false)
  const error   = ref(null)

  // ── Computed ───────────────────────────────────────────────────────────────
  const totalWorkers  = computed(() => workers.value.length)
  const activeWorkers = computed(() => workers.value.filter(w => w.status === 'idle' || w.status === 'busy').length)
  const busyWorkers   = computed(() => workers.value.filter(w => w.status === 'busy').length)
  const idleWorkers   = computed(() => workers.value.filter(w => w.status === 'idle').length)
  const deadWorkers   = computed(() => workers.value.filter(w => w.status === 'dead').length)

  const workersByStatus = computed(() => (status) =>
    workers.value.filter(w => w.status === status)
  )

  const totalTasksCompleted = computed(() =>
    workers.value.reduce((sum, w) => sum + (w.tasks_completed || 0), 0)
  )
  const totalTasksFailed = computed(() =>
    workers.value.reduce((sum, w) => sum + (w.tasks_failed || 0), 0)
  )
  const workerUtilization = computed(() => {
    if (totalWorkers.value === 0) return 0
    return Math.round((busyWorkers.value / totalWorkers.value) * 100)
  })

  // ── Actions ────────────────────────────────────────────────────────────────

  /**
   * Fetch all workers.
   * Backend returns: { workers: [...], summary: { total, idle, busy, dead } }
   */
  async function fetchWorkers() {
    loading.value = true
    error.value = null
    try {
      const response = await apiClient.get('/workers')
      // Handle both { workers: [...] } and flat array responses
      workers.value = response.data.workers || response.data.data || response.data || []
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch workers'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchWorker(key) {
    loading.value = true
    error.value = null
    try {
      const response = await apiClient.get(`/workers/${key}`)
      const worker = response.data.worker || response.data

      const idx = workers.value.findIndex(w => w.worker_key === key)
      if (idx !== -1) {
        workers.value[idx] = worker
      } else {
        workers.value.push(worker)
      }

      return worker
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch worker'
      throw err
    } finally {
      loading.value = false
    }
  }

  function updateWorker(updatedWorker) {
    const idx = workers.value.findIndex(
      w => w.id === updatedWorker.id || w.worker_key === updatedWorker.worker_key
    )
    if (idx !== -1) {
      workers.value[idx] = { ...workers.value[idx], ...updatedWorker }
    }
  }

  function clearError() { error.value = null }

  return {
    // State
    workers,
    loading,
    error,

    // Computed
    totalWorkers,
    activeWorkers,
    busyWorkers,
    idleWorkers,
    deadWorkers,
    workersByStatus,
    totalTasksCompleted,
    totalTasksFailed,
    workerUtilization,

    // Actions
    fetchWorkers,
    fetchWorker,
    updateWorker,
    clearError,
  }
})
