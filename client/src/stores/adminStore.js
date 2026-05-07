import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/api'

export const useAdminStore = defineStore('admin', () => {
  const allJobs = ref([])
  const allUsers = ref([])
  const allWorkers = ref([])
  const systemMetrics = ref(null)
  const loading = ref(false)
  const error = ref(null)

  // Fetch all jobs (admin view)
  async function fetchAllJobs(params = {}) {
    loading.value = true
    error.value = null
    try {
      const response = await api.get('/admin/jobs', { params })
      allJobs.value = response.data.data || response.data
      return { success: true, data: response.data }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch jobs'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  // Fetch all users
  async function fetchAllUsers(params = {}) {
    loading.value = true
    error.value = null
    try {
      const response = await api.get('/admin/users', { params })
      allUsers.value = response.data.data || response.data
      return { success: true, data: response.data }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch users'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  // Fetch all workers
  async function fetchAllWorkers(params = {}) {
    loading.value = true
    error.value = null
    try {
      const response = await api.get('/admin/workers', { params })
      allWorkers.value = response.data.workers || response.data
      return { success: true, data: response.data }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch workers'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  // Fetch system metrics
  async function fetchSystemMetrics() {
    loading.value = true
    error.value = null
    try {
      const response = await api.get('/admin/metrics')
      systemMetrics.value = response.data
      return { success: true, data: response.data }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch metrics'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  // Fetch dashboard data (metrics + recent jobs)
  async function fetchDashboardData(params = {}) {
    loading.value = true
    error.value = null
    try {
      // Fetch metrics and jobs in parallel
      const [metricsResponse, jobsResponse] = await Promise.all([
        api.get('/admin/metrics'),
        api.get('/admin/jobs', { params: { per_page: params.limit || 10, sort_by: 'created_at', sort_order: 'desc', ...params } })
      ])

      // Store the metrics
      systemMetrics.value = metricsResponse.data

      // Store the recent jobs
      allJobs.value = jobsResponse.data.data || jobsResponse.data.jobs || []

      const dashboardData = {
        metrics: metricsResponse.data,
        recentJobs: allJobs.value
      }

      return { success: true, data: dashboardData }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch dashboard data'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  // Force cancel a job
  async function forceCancelJob(jobId) {
    try {
      const response = await api.post(`/admin/jobs/${jobId}/cancel`)
      return { success: true, data: response.data }
    } catch (err) {
      return {
        success: false,
        error: err.response?.data?.message || 'Failed to cancel job'
      }
    }
  }

  // Delete a job
  async function deleteJob(jobId) {
    try {
      const response = await api.delete(`/admin/jobs/${jobId}`)
      return { success: true, data: response.data }
    } catch (err) {
      return {
        success: false,
        error: err.response?.data?.message || 'Failed to delete job'
      }
    }
  }

  // Mark worker as dead
  async function markWorkerDead(workerKey) {
    try {
      const response = await api.post(`/admin/workers/${workerKey}/mark-dead`)
      return { success: true, data: response.data }
    } catch (err) {
      return {
        success: false,
        error: err.response?.data?.message || 'Failed to mark worker as dead'
      }
    }
  }

  // Delete a worker
  async function deleteWorker(workerKey) {
    try {
      const response = await api.delete(`/admin/workers/${workerKey}`)
      return { success: true, data: response.data }
    } catch (err) {
      return {
        success: false,
        error: err.response?.data?.message || 'Failed to delete worker'
      }
    }
  }

  // Update user
  async function updateUser(userId, data) {
    try {
      const response = await api.put(`/admin/users/${userId}`, data)
      return { success: true, data: response.data }
    } catch (err) {
      return {
        success: false,
        error: err.response?.data?.message || 'Failed to update user'
      }
    }
  }

  // Delete user
  async function deleteUser(userId) {
    try {
      const response = await api.delete(`/admin/users/${userId}`)
      return { success: true, data: response.data }
    } catch (err) {
      return {
        success: false,
        error: err.response?.data?.message || 'Failed to delete user'
      }
    }
  }

  return {
    allJobs,
    allUsers,
    allWorkers,
    systemMetrics,
    loading,
    error,
    fetchAllJobs,
    fetchAllUsers,
    fetchAllWorkers,
    fetchSystemMetrics,
    fetchDashboardData,
    forceCancelJob,
    deleteJob,
    markWorkerDead,
    deleteWorker,
    updateUser,
    deleteUser
  }
})
