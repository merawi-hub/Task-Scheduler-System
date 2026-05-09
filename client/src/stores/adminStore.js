import { defineStore } from 'pinia'
import { ref } from 'vue'
import apiClient from '@/api/axios'

export const useAdminStore = defineStore('admin', () => {
  // ── State ──────────────────────────────────────────────────────────────────
  const allJobs    = ref([])
  const allUsers   = ref([])
  const allWorkers = ref([])
  const systemMetrics  = ref(null)
  const metricsHistory = ref(null)
  const activityFeed   = ref([])
  const loading = ref(false)
  const error   = ref(null)

  // ── Jobs ───────────────────────────────────────────────────────────────────
  async function fetchAllJobs(params = {}) {
    loading.value = true
    error.value = null
    try {
      const response = await apiClient.get('/admin/jobs', { params })
      // Paginated response: { data: [...], total, ... }
      allJobs.value = response.data.data || response.data.jobs || []
      return { success: true, data: response.data }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch jobs'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function forceCancelJob(jobId) {
    try {
      const response = await apiClient.post(`/admin/jobs/${jobId}/cancel`)
      return { success: true, data: response.data }
    } catch (err) {
      return { success: false, error: err.response?.data?.message || 'Failed to cancel job' }
    }
  }

  async function retryJob(jobId) {
    try {
      const response = await apiClient.post(`/admin/jobs/${jobId}/retry`)
      return { success: true, data: response.data }
    } catch (err) {
      return { success: false, error: err.response?.data?.message || 'Failed to retry job' }
    }
  }

  async function deleteJob(jobId) {
    try {
      const response = await apiClient.delete(`/admin/jobs/${jobId}`)
      allJobs.value = allJobs.value.filter(j => j.id !== jobId)
      return { success: true, data: response.data }
    } catch (err) {
      return { success: false, error: err.response?.data?.message || 'Failed to delete job' }
    }
  }

  // ── Users ──────────────────────────────────────────────────────────────────
  async function fetchAllUsers(params = {}) {
    loading.value = true
    error.value = null
    try {
      const response = await apiClient.get('/admin/users', { params })
      allUsers.value = response.data.data || response.data.users || []
      return { success: true, data: response.data }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch users'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function updateUser(userId, data) {
    try {
      const response = await apiClient.put(`/admin/users/${userId}`, data)
      // Update local state
      const idx = allUsers.value.findIndex(u => u.id === userId)
      if (idx !== -1) allUsers.value[idx] = { ...allUsers.value[idx], ...response.data.user }
      return { success: true, data: response.data }
    } catch (err) {
      return { success: false, error: err.response?.data?.message || 'Failed to update user' }
    }
  }

  async function deleteUser(userId) {
    try {
      const response = await apiClient.delete(`/admin/users/${userId}`)
      allUsers.value = allUsers.value.filter(u => u.id !== userId)
      return { success: true, data: response.data }
    } catch (err) {
      return { success: false, error: err.response?.data?.message || 'Failed to delete user' }
    }
  }

  // ── Workers ────────────────────────────────────────────────────────────────
  async function fetchAllWorkers(params = {}) {
    loading.value = true
    error.value = null
    try {
      const response = await apiClient.get('/admin/workers', { params })
      allWorkers.value = response.data.workers || response.data.data || []
      return { success: true, data: response.data }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch workers'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function markWorkerDead(workerKey) {
    try {
      const response = await apiClient.post(`/admin/workers/${workerKey}/mark-dead`)
      // Update local state
      const idx = allWorkers.value.findIndex(w => w.worker_key === workerKey)
      if (idx !== -1) allWorkers.value[idx].status = 'dead'
      return { success: true, data: response.data }
    } catch (err) {
      return { success: false, error: err.response?.data?.message || 'Failed to mark worker as dead' }
    }
  }

  async function deleteWorker(workerKey) {
    try {
      const response = await apiClient.delete(`/admin/workers/${workerKey}`)
      allWorkers.value = allWorkers.value.filter(w => w.worker_key !== workerKey)
      return { success: true, data: response.data }
    } catch (err) {
      return { success: false, error: err.response?.data?.message || 'Failed to delete worker' }
    }
  }

  // ── Metrics ────────────────────────────────────────────────────────────────
  async function fetchSystemMetrics() {
    loading.value = true
    error.value = null
    try {
      const response = await apiClient.get('/admin/metrics')
      systemMetrics.value = response.data
      activityFeed.value  = response.data?.activity_feed || []
      return { success: true, data: response.data }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch metrics'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function fetchMetricsHistory(period = 'day') {
    loading.value = true
    error.value = null
    try {
      const response = await apiClient.get('/admin/metrics/history', { params: { period } })
      metricsHistory.value = response.data
      return { success: true, data: response.data }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch metrics history'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function fetchActivityFeed(limit = 50) {
    loading.value = true
    error.value = null
    try {
      const response = await apiClient.get('/admin/metrics/activity', { params: { limit } })
      activityFeed.value = response.data?.activity || []
      return { success: true, data: response.data }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch activity feed'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  // ── Dashboard (parallel fetch) ─────────────────────────────────────────────
  async function fetchDashboardData(params = {}) {
    loading.value = true
    error.value = null
    try {
      const [metricsRes, jobsRes] = await Promise.all([
        apiClient.get('/admin/metrics'),
        apiClient.get('/admin/jobs', {
          params: {
            per_page: params.limit || 10,
            sort_by: 'created_at',
            sort_order: 'desc',
            ...params,
          },
        }),
      ])

      systemMetrics.value = metricsRes.data
      activityFeed.value  = metricsRes.data?.activity_feed || []
      allJobs.value       = jobsRes.data.data || jobsRes.data.jobs || []

      return {
        success: true,
        data: { metrics: metricsRes.data, recentJobs: allJobs.value },
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch dashboard data'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  return {
    // State
    allJobs,
    allUsers,
    allWorkers,
    systemMetrics,
    metricsHistory,
    activityFeed,
    loading,
    error,

    // Actions
    fetchAllJobs,
    forceCancelJob,
    retryJob,
    deleteJob,
    fetchAllUsers,
    updateUser,
    deleteUser,
    fetchAllWorkers,
    markWorkerDead,
    deleteWorker,
    fetchSystemMetrics,
    fetchMetricsHistory,
    fetchActivityFeed,
    fetchDashboardData,
  }
})
