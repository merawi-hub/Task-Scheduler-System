import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import apiClient from '@/api/axios'

export const useJobsStore = defineStore('jobs', () => {
  // ── State ──────────────────────────────────────────────────────────────────
  const jobs       = ref([])
  const currentJob = ref(null)
  const loading    = ref(false)
  const error      = ref(null)

  // ── Computed ───────────────────────────────────────────────────────────────
  const sortedJobs = computed(() =>
    [...jobs.value].sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
  )

  const jobsByStatus = computed(() => (status) =>
    jobs.value.filter(j => j.status === status)
  )

  const totalJobs     = computed(() => jobs.value.length)
  const runningJobs   = computed(() => jobs.value.filter(j => j.status === 'running').length)
  const completedJobs = computed(() => jobs.value.filter(j => j.status === 'completed').length)
  const failedJobs    = computed(() => jobs.value.filter(j => j.status === 'failed').length)

  // ── Actions ────────────────────────────────────────────────────────────────

  /**
   * Fetch paginated list of user's jobs.
   * Backend returns: { current_page, data: [...], total, ... }
   */
  async function fetchJobs(params = {}) {
    loading.value = true
    error.value = null
    try {
      const response = await apiClient.get('/jobs', { params })
      jobs.value = response.data.data || []
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch jobs'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Fetch a single job with its tasks.
   * Backend returns: { job: {...}, progress: float, pending_tasks: int }
   */
  async function fetchJob(id) {
    // Validate ID parameter
    if (!id || id === 'undefined' || id === 'null' || isNaN(Number(id)) || Number(id) <= 0) {
      const msg = 'Invalid job ID provided';
      error.value = msg;
      throw new Error(msg);
    }

    loading.value = true
    error.value = null
    try {
      const response = await apiClient.get(`/jobs/${id}`)
      const jobData = response.data.job || response.data
      currentJob.value = jobData

      const idx = jobs.value.findIndex(j => j.id === Number(id))
      if (idx !== -1) jobs.value[idx] = jobData

      return jobData
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch job'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Submit a new job.
   * Backend returns: { message, job: {...} }
   */
  async function submitJob(jobData) {
    loading.value = true
    error.value = null
    try {
      const response = await apiClient.post('/jobs', jobData)
      const newJob = response.data.job || response.data
      jobs.value.unshift(newJob)
      return newJob
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to submit job'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Create a job — returns { success, data } shape for modal usage.
   */
  async function createJob(jobData) {
    loading.value = true
    error.value = null
    try {
      const response = await apiClient.post('/jobs', jobData)
      const newJob = response.data.job || response.data
      jobs.value.unshift(newJob)
      return { success: true, data: newJob }
    } catch (err) {
      const msg = err.response?.data?.message || 'Failed to create job'
      error.value = msg
      return { success: false, error: msg }
    } finally {
      loading.value = false
    }
  }

  /**
   * Cancel a job.
   */
  async function cancelJob(id) {
    // Validate ID parameter
    if (!id || id === 'undefined' || id === 'null' || isNaN(Number(id)) || Number(id) <= 0) {
      const msg = 'Invalid job ID provided';
      error.value = msg;
      throw new Error(msg);
    }

    loading.value = true
    error.value = null
    try {
      const response = await apiClient.delete(`/jobs/${id}`)
      const idx = jobs.value.findIndex(j => j.id === Number(id))
      if (idx !== -1) jobs.value[idx].status = 'cancelled'
      if (currentJob.value?.id === Number(id)) currentJob.value.status = 'cancelled'
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to cancel job'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Fetch tasks for a job.
   * Backend returns: { job_id, job_name, job_type, tasks: [...enriched], status_counts: {...} }
   * Each task already has record_from, record_to, records_count, operations from the payload.
   */
  async function fetchJobTasks(jobId) {
    // Validate ID parameter
    if (!jobId || jobId === 'undefined' || jobId === 'null' || isNaN(Number(jobId)) || Number(jobId) <= 0) {
      const msg = 'Invalid job ID provided';
      error.value = msg;
      throw new Error(msg);
    }

    try {
      const response = await apiClient.get(`/jobs/${jobId}/tasks`)
      // Return the enriched tasks array
      return response.data.tasks || response.data.data || response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch job tasks'
      throw err
    }
  }

  function updateJob(updatedJob) {
    const idx = jobs.value.findIndex(j => j.id === updatedJob.id)
    if (idx !== -1) jobs.value[idx] = { ...jobs.value[idx], ...updatedJob }
    if (currentJob.value?.id === updatedJob.id) {
      currentJob.value = { ...currentJob.value, ...updatedJob }
    }
  }

  function clearCurrentJob() { currentJob.value = null }
  function clearError()      { error.value = null }

  return {
    // State
    jobs,
    currentJob,
    loading,
    error,

    // Computed
    sortedJobs,
    jobsByStatus,
    totalJobs,
    runningJobs,
    completedJobs,
    failedJobs,

    // Actions
    fetchJobs,
    fetchJob,
    submitJob,
    createJob,
    cancelJob,
    fetchJobTasks,
    updateJob,
    clearCurrentJob,
    clearError,
  }
})
