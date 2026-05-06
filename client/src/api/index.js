import apiClient from './axios'

// API service methods
export const api = {
  // Test connection
  testConnection: async () => {
    try {
      const response = await apiClient.get('/test')
      return response.data
    } catch (error) {
      throw error
    }
  },

  // Example GET request
  get: async (endpoint) => {
    try {
      const response = await apiClient.get(endpoint)
      return response.data
    } catch (error) {
      throw error
    }
  },

  // Example POST request
  post: async (endpoint, data) => {
    try {
      const response = await apiClient.post(endpoint, data)
      return response.data
    } catch (error) {
      throw error
    }
  },

  // Example PUT request
  put: async (endpoint, data) => {
    try {
      const response = await apiClient.put(endpoint, data)
      return response.data
    } catch (error) {
      throw error
    }
  },

  // Example DELETE request
  delete: async (endpoint) => {
    try {
      const response = await apiClient.delete(endpoint)
      return response.data
    } catch (error) {
      throw error
    }
  }
}

export default api
