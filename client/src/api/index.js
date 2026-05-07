import apiClient from './axios'

// Jobs API
export const jobsApi = {
  // Get all jobs with optional filters
  getJobs: async (params = {}) => {
    const response = await apiClient.get('/jobs', { params })
    return response.data
  },

  // Get a single job by ID
  getJob: async (id) => {
    const response = await apiClient.get(`/jobs/${id}`)
    return response.data
  },

  // Submit a new job
  submitJob: async (jobData) => {
    const response = await apiClient.post('/jobs', jobData)
    return response.data
  },

  // Cancel a job
  cancelJob: async (id) => {
    const response = await apiClient.delete(`/jobs/${id}`)
    return response.data
  },

  // Get all tasks for a specific job
  getJobTasks: async (jobId, params = {}) => {
    const response = await apiClient.get(`/jobs/${jobId}/tasks`, { params })
    return response.data
  }
}

// Workers API
export const workersApi = {
  // Get all workers
  getWorkers: async () => {
    const response = await apiClient.get('/workers')
    return response.data
  },

  // Get a single worker by key
  getWorker: async (key) => {
    const response = await apiClient.get(`/workers/${key}`)
    return response.data
  },

  // Register a new worker
  registerWorker: async (workerData) => {
    const response = await apiClient.post('/workers/register', workerData)
    return response.data
  },

  // Send worker heartbeat
  sendHeartbeat: async (key, data = {}) => {
    const response = await apiClient.post(`/workers/${key}/heartbeat`, data)
    return response.data
  }
}

// Tasks API
export const tasksApi = {
  // Get next available task (for workers)
  getNextTask: async (workerKey) => {
    const response = await apiClient.get('/tasks/next', {
      headers: {
        'X-Worker-Key': workerKey
      }
    })
    return response.data
  },

  // Mark task as started
  startTask: async (taskId, data) => {
    const response = await apiClient.post(`/tasks/${taskId}/start`, data)
    return response.data
  },

  // Mark task as completed
  completeTask: async (taskId, data) => {
    const response = await apiClient.post(`/tasks/${taskId}/complete`, data)
    return response.data
  },

  // Mark task as failed
  failTask: async (taskId, data) => {
    const response = await apiClient.post(`/tasks/${taskId}/fail`, data)
    return response.data
  }
}

// Metrics API
export const metricsApi = {
  // Get current system metrics
  getMetrics: async () => {
    const response = await apiClient.get('/metrics')
    return response.data
  },

  // Get metrics history for charts
  getMetricsHistory: async (params = {}) => {
    const response = await apiClient.get('/metrics/history', { params })
    return response.data
  }
}

// Export all APIs as a single object
export const api = {
  jobs: jobsApi,
  workers: workersApi,
  tasks: tasksApi,
  metrics: metricsApi,

  // Legacy methods for backward compatibility
  get: async (endpoint) => {
    const response = await apiClient.get(endpoint)
    return response.data
  },

  post: async (endpoint, data) => {
    const response = await apiClient.post(endpoint, data)
    return response.data
  },

  put: async (endpoint, data) => {
    const response = await apiClient.put(endpoint, data)
    return response.data
  },

  delete: async (endpoint) => {
    const response = await apiClient.delete(endpoint)
    return response.data
  }
}

export default api
