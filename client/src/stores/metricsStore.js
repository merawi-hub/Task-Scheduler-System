import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import apiClient from '@/api/axios'

export const useMetricsStore = defineStore('metrics', () => {
  // State
  const metrics = ref({
    total_jobs: 0,
    total_tasks: 0,
    completed_tasks: 0,
    failed_tasks: 0,
    pending_tasks: 0,
    running_tasks: 0,
    active_workers: 0,
    total_workers: 0,
    tasks_per_second: 0,
    average_task_duration: 0,
    total_retries: 0,
    worker_utilization: 0,
    system_uptime: 0
  })
  
  const history = ref([])
  const loading = ref(false)
  const error = ref(null)

  // Computed
  const taskCompletionRate = computed(() => {
    if (metrics.value.total_tasks === 0) return 0
    return Math.round((metrics.value.completed_tasks / metrics.value.total_tasks) * 100)
  })

  const taskFailureRate = computed(() => {
    if (metrics.value.total_tasks === 0) return 0
    return Math.round((metrics.value.failed_tasks / metrics.value.total_tasks) * 100)
  })

  const systemHealth = computed(() => {
    const completionRate = taskCompletionRate.value
    const failureRate = taskFailureRate.value
    
    if (completionRate >= 90 && failureRate < 5) return 'excellent'
    if (completionRate >= 75 && failureRate < 10) return 'good'
    if (completionRate >= 50 && failureRate < 20) return 'fair'
    return 'poor'
  })

  const throughput = computed(() => {
    return metrics.value.tasks_per_second || 0
  })

  // Actions
  async function fetchMetrics() {
    loading.value = true
    error.value = null
    try {
      const response = await apiClient.get('/metrics')
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
    // Keep only last 100 points
    if (history.value.length > 100) {
      history.value.shift()
    }
  }

  function clearError() {
    error.value = null
  }

  function reset() {
    metrics.value = {
      total_jobs: 0,
      total_tasks: 0,
      completed_tasks: 0,
      failed_tasks: 0,
      pending_tasks: 0,
      running_tasks: 0,
      active_workers: 0,
      total_workers: 0,
      tasks_per_second: 0,
      average_task_duration: 0,
      total_retries: 0,
      worker_utilization: 0,
      system_uptime: 0
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
    reset
  }
})
