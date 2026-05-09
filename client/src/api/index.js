import apiClient from './axios'

// ─── Auth API ────────────────────────────────────────────────────────────────
export const authApi = {
  login: (email, password) =>
    apiClient.post('/auth/login', { email, password }),

  register: (name, email, password, passwordConfirmation) =>
    apiClient.post('/auth/register', {
      name,
      email,
      password,
      password_confirmation: passwordConfirmation,
    }),

  logout: () => apiClient.post('/auth/logout'),

  me: () => apiClient.get('/auth/me'),

  updateProfile: (data) => apiClient.put('/auth/profile', data),

  changePassword: (currentPassword, password, passwordConfirmation) =>
    apiClient.put('/auth/password', {
      current_password: currentPassword,
      password,
      password_confirmation: passwordConfirmation,
    }),
}

// ─── Jobs API ─────────────────────────────────────────────────────────────────
export const jobsApi = {
  getJobs: (params = {}) =>
    apiClient.get('/jobs', { params }).then((r) => r.data),

  getJob: (id) =>
    apiClient.get(`/jobs/${id}`).then((r) => r.data),

  submitJob: (jobData) =>
    apiClient.post('/jobs', jobData).then((r) => r.data),

  cancelJob: (id) =>
    apiClient.delete(`/jobs/${id}`).then((r) => r.data),

  getJobTasks: (jobId, params = {}) =>
    apiClient.get(`/jobs/${jobId}/tasks`, { params }).then((r) => r.data),

  downloadJob: (jobId) =>
    apiClient.get(`/jobs/${jobId}/download`).then((r) => r.data),
}

// ─── Workers API ──────────────────────────────────────────────────────────────
export const workersApi = {
  getWorkers: () =>
    apiClient.get('/workers').then((r) => r.data),

  getWorker: (key) =>
    apiClient.get(`/workers/${key}`).then((r) => r.data),

  registerWorker: (workerData) =>
    apiClient.post('/workers/register', workerData).then((r) => r.data),

  sendHeartbeat: (key, data = {}) =>
    apiClient.post(`/workers/${key}/heartbeat`, data).then((r) => r.data),
}

// ─── Tasks API (worker-facing) ────────────────────────────────────────────────
export const tasksApi = {
  getNextTask: (workerToken) =>
    apiClient.get('/tasks/next', {
      headers: { 'X-Worker-Token': workerToken },
    }).then((r) => r.data),

  startTask: (taskId, data) =>
    apiClient.post(`/tasks/${taskId}/start`, data).then((r) => r.data),

  completeTask: (taskId, data) =>
    apiClient.post(`/tasks/${taskId}/complete`, data).then((r) => r.data),

  failTask: (taskId, data) =>
    apiClient.post(`/tasks/${taskId}/fail`, data).then((r) => r.data),
}

// ─── Metrics API ──────────────────────────────────────────────────────────────
export const metricsApi = {
  getMetrics: () =>
    apiClient.get('/metrics').then((r) => r.data),

  getMetricsHistory: (params = {}) =>
    apiClient.get('/metrics/history', { params }).then((r) => r.data),
}

// ─── Admin API ────────────────────────────────────────────────────────────────
export const adminApi = {
  // Jobs
  getAllJobs: (params = {}) =>
    apiClient.get('/admin/jobs', { params }).then((r) => r.data),

  getJob: (id) =>
    apiClient.get(`/admin/jobs/${id}`).then((r) => r.data),

  getJobStatistics: () =>
    apiClient.get('/admin/jobs/statistics').then((r) => r.data),

  forceCancelJob: (id) =>
    apiClient.post(`/admin/jobs/${id}/cancel`).then((r) => r.data),

  retryJob: (id) =>
    apiClient.post(`/admin/jobs/${id}/retry`).then((r) => r.data),

  deleteJob: (id) =>
    apiClient.delete(`/admin/jobs/${id}`).then((r) => r.data),

  // Workers
  getAllWorkers: (params = {}) =>
    apiClient.get('/admin/workers', { params }).then((r) => r.data),

  getWorkerStatistics: () =>
    apiClient.get('/admin/workers/statistics').then((r) => r.data),

  markWorkerDead: (key) =>
    apiClient.post(`/admin/workers/${key}/mark-dead`).then((r) => r.data),

  deleteWorker: (key) =>
    apiClient.delete(`/admin/workers/${key}`).then((r) => r.data),

  // Users
  getAllUsers: (params = {}) =>
    apiClient.get('/admin/users', { params }).then((r) => r.data),

  getUserStatistics: () =>
    apiClient.get('/admin/users/statistics').then((r) => r.data),

  getUser: (id) =>
    apiClient.get(`/admin/users/${id}`).then((r) => r.data),

  updateUser: (id, data) =>
    apiClient.put(`/admin/users/${id}`, data).then((r) => r.data),

  deleteUser: (id) =>
    apiClient.delete(`/admin/users/${id}`).then((r) => r.data),

  // Metrics
  getMetrics: () =>
    apiClient.get('/admin/metrics').then((r) => r.data),

  getMetricsHistory: (period = 'day') =>
    apiClient.get('/admin/metrics/history', { params: { period } }).then((r) => r.data),

  getHealth: () =>
    apiClient.get('/admin/metrics/health').then((r) => r.data),

  getActivityFeed: (limit = 50) =>
    apiClient.get('/admin/metrics/activity', { params: { limit } }).then((r) => r.data),
}

// ─── Default export — thin wrapper around apiClient ───────────────────────────
export const api = {
  auth: authApi,
  jobs: jobsApi,
  workers: workersApi,
  tasks: tasksApi,
  metrics: metricsApi,
  admin: adminApi,

  // Raw HTTP methods for one-off calls
  get: (endpoint, config = {}) => apiClient.get(endpoint, config),
  post: (endpoint, data, config = {}) => apiClient.post(endpoint, data, config),
  put: (endpoint, data, config = {}) => apiClient.put(endpoint, data, config),
  delete: (endpoint, config = {}) => apiClient.delete(endpoint, config),

  testConnection: () => apiClient.get('/test').then((r) => r.data),
}

export default api
