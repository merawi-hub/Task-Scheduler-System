import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import apiClient from '@/api/axios'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('auth_token') || null)
  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.is_admin === true)

  // Set auth token in localStorage and axios headers
  function setToken(newToken) {
    token.value = newToken
    if (newToken) {
      localStorage.setItem('auth_token', newToken)
      apiClient.defaults.headers.common['Authorization'] = `Bearer ${newToken}`
    } else {
      localStorage.removeItem('auth_token')
      delete apiClient.defaults.headers.common['Authorization']
    }
  }

  // Initialize token from localStorage
  if (token.value) {
    apiClient.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
  }

  // Register new user
  async function register(name, email, password, passwordConfirmation) {
    try {
      const response = await apiClient.post('/auth/register', {
        name,
        email,
        password,
        password_confirmation: passwordConfirmation
      })
      
      user.value = response.data.user
      setToken(response.data.token)
      
      return { success: true }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Registration failed',
        errors: error.response?.data?.errors || {}
      }
    }
  }

  // Login user
  async function login(email, password) {
    try {
      const response = await apiClient.post('/auth/login', {
        email,
        password
      })
      
      user.value = response.data.user
      setToken(response.data.token)
      
      return { success: true }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Login failed',
        errors: error.response?.data?.errors || {}
      }
    }
  }

  // Logout user
  async function logout() {
    try {
      await apiClient.post('/auth/logout')
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      user.value = null
      setToken(null)
    }
  }

  // Fetch current user
  async function fetchUser() {
    try {
      const response = await apiClient.get('/auth/me')
      user.value = response.data.user
      return { success: true }
    } catch (error) {
      // Token might be invalid, clear it
      user.value = null
      setToken(null)
      return { success: false }
    }
  }

  return {
    user,
    token,
    isAuthenticated,
    isAdmin,
    register,
    login,
    logout,
    fetchUser
  }
})
