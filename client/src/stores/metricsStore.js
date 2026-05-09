import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import apiClient from '@/api/axios'

export const useMetricsStore = defineStore('metrics', () => {
  // ── State ──────────────────────────────────────────────────────────────────
  const metrics = ref({
    jobs: { total: 0, pending: 0, running: 0, completed: 0, failed: 0, cancelled: 0 },
    tasks: { total: 0, pending: 0, assigned: 0, running: 0, done: 0, failed: 0, cancelled: 0, success_rate: 0 },
    workers: { total: 0, idle: 0, busy: 0, dead: 0, active: 0, utilization: 0 },
    performance: { tasks_per_second: 0, avg_task_duration_seconds: 0, retried_tasks: 0 },
  })

  const history = ref([])
  const loading = ref(false)
  const error   = ref(null)

  // ── Computed ───────────────────────────────────────────────────────────────
  const taskCompletionRate = computed(() => {
    const t = metrics.value.tasks
    if (!t.total) return 0
    return Math.round((t.done / t.total) * 100)
  })

  const taskFailureRate = computed(() => {
    const t = metrics.value.tasks
    if (!t.total) return 0
    return Math.round((t.failed / t.total) * 100)
  })

  const systemHealth = computed(() => {
    const cr = taskCompletionRate.value
    const fr = taskFailureRate.value
    if (cr >= 90 && fr < 5)  return 'excellent'
    if (cr >= 75 && fr < 10) return 'good'
    if (cr >= 50 && fr < 20) return 'fair'
    return 'poor'
  })

  const throughput = computed(() =>
    metrics.value.performance?.tasks_per_second || 0
  )

  // ── Actions ────────────────────────────────────────────────────────────────

  /**
   * Fetch public metrics.
   * Backend returns: { jobs: {...}, tasks: {...}, workers: {...}, performance: {...} }
   */
  async function fetchMetrics() {
    loading.value = true
    error.value = null
    try {
      const response = await apiClient.get('/metrics')
      // Merge so we keep the nested structure
      metrics.value = { ...metrics.value, ...(response.data.data || response.data) }
      return metrics.value
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch metrics'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchMetricsHistory(params = {}) {
    loading.value = true
    error.value = null
    try {
      const response = await apiClient.get('/metrics/history', { params })
      history.value = response.data.data || response.data
      return history.value
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch metrics history'
      throw err
    } finally {
      loading.value = false
    }
  }

  function updateMetrics(newMetrics) {
    metrics.value = { ...metrics.value, ...newMetrics }
  }

  function addHistoryPoint(point) {
    history.value.push(point)
    if (history.value.length > 100) history.value.shift()
  }

  function clearError() { error.value = null }

  function reset() {
    metrics.value = {
      jobs: { total: 0, pending: 0, running: 0, completed: 0, failed: 0, cancelled: 0 },
      tasks: { total: 0, pending: 0, assigned: 0, running: 0, done: 0, failed: 0, cancelled: 0, success_rate: 0 },
      workers: { total: 0, idle: 0, busy: 0, dead: 0, active: 0, utilization: 0 },
      performance: { tasks_per_second: 0, avg_task_duration_seconds: 0, retried_tasks: 0 },
    }
    history.value = []
  }

  return {
    // State
    metrics,
    history,
    loading,
    error,

    // Computed
    taskCompletionRate,
    taskFailureRate,
    systemHealth,
    throughput,

    // Actions
    fetchMetrics,
    fetchMetricsHistory,
    updateMetrics,
    addHistoryPoint,
    clearError,
    reset,
  }
})
