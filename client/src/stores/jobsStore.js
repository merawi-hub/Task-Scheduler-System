import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import apiClient from '@/api/axios'

export const useJobsStore = defineStore('jobs', () => {
  // State
  const jobs = ref([])
  const currentJob = ref(null)
  const loading = ref(false)
  const error = ref(null)

  // Computed
  const sortedJobs = computed(() => {
    return [...jobs.value].sort((a, b) => {
      return new Date(b.created_at) - new Date(a.created_at)
    })
  })

  const jobsByStatus = computed(() => {
    return (status) => jobs.value.filter(job => job.status === status)
  })

  const totalJobs = computed(() => jobs.value.length)

  const runningJobs = computed(() => 
    jobs.value.filter(job => job.status === 'running').length
  )

  const completedJobs = computed(() => 
    jobs.value.filter(job => job.status === 'completed').length
  )

  const failedJobs = computed(() => 
    jobs.value.filter(job => job.status === 'failed').length
  )

  // Actions
  async function fetchJobs(params = {}) {
    loading.value = true
    error.value = null
    try {
      const response = await apiClient.get('/jobs', { params })
      jobs.value = response.data.data || response.data
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch jobs'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchJob(id) {
    loading.value = true
    error.value = null
    try {
      const response = await apiClient.get(`/jobs/${id}`)
      currentJob.value = response.data.data || response.data
      
      // Update the job in the jobs list if it exists
      const index = jobs.value.findIndex(j => j.id === id)
      if (index !== -1) {
        jobs.value[index] = currentJob.value
      }
      
      return currentJob.value
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch job'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function submitJob(jobData) {
    loading.value = true
    error.value = null
    try {
      const response = await apiClient.post('/jobs', jobData)
      const newJob = response.data.data || response.data
      jobs.value.unshift(newJob)
      return newJob
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to submit job'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function createJob(jobData) {
    loading.value = true
    error.value = null
    try {
      const response = await apiClient.post('/jobs', jobData)
      const newJob = response.data.data || response.data.job || response.data
      jobs.value.unshift(newJob)
      return { success: true, data: newJob }
    } catch (err) {
      const errorMessage = err.response?.data?.message || 'Failed to create job'
      error.value = errorMessage
      return { success: false, error: errorMessage }
    } finally {
      loading.value = false
    }
  }

  async function cancelJob(id) {
    loading.value = true
    error.value = null
    try {
      const response = await apiClient.delete(`/jobs/${id}`)
      const index = jobs.value.findIndex(j => j.id === id)
      if (index !== -1) {
        jobs.value[index].status = 'cancelled'
      }
      if (currentJob.value?.id === id) {
        currentJob.value.status = 'cancelled'
      }
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to cancel job'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchJobTasks(jobId) {
    try {
      const response = await apiClient.get(`/jobs/${jobId}/tasks`)
      return response.data.data || response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch job tasks'
      throw err
    }
  }

  function updateJob(updatedJob) {
    const index = jobs.value.findIndex(j => j.id === updatedJob.id)
    if (index !== -1) {
      jobs.value[index] = { ...jobs.value[index], ...updatedJob }
    }
    if (currentJob.value?.id === updatedJob.id) {
      currentJob.value = { ...currentJob.value, ...updatedJob }
    }
  }

  function clearCurrentJob() {
    currentJob.value = null
  }

  function clearError() {
    error.value = null
  }

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
    clearError
  }
})
