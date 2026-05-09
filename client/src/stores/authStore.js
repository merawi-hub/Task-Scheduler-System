import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import apiClient from '@/api/axios'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('auth_token') || null)

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.is_admin === true)

  // ── Token management ────────────────────────────────────────────────────────
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

  // Restore token from localStorage on store init
  if (token.value) {
    apiClient.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
  }

  // ── Register ────────────────────────────────────────────────────────────────
  async function register(name, email, password, passwordConfirmation) {
    try {
      const response = await apiClient.post('/auth/register', {
        name,
        email,
        password,
        password_confirmation: passwordConfirmation,
      })
      user.value = response.data.user
      setToken(response.data.token)
      return { success: true }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Registration failed',
        errors: error.response?.data?.errors || {},
      }
    }
  }

  // ── Login ───────────────────────────────────────────────────────────────────
  async function login(email, password) {
    try {
      const response = await apiClient.post('/auth/login', { email, password })
      user.value = response.data.user
      setToken(response.data.token)
      return { success: true }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Login failed',
        errors: error.response?.data?.errors || {},
      }
    }
  }

  // ── Logout ──────────────────────────────────────────────────────────────────
  async function logout() {
    try {
      await apiClient.post('/auth/logout')
    } catch (error) {
      // Ignore errors — clear local state regardless
    } finally {
      user.value = null
      setToken(null)
    }
  }

  // ── Fetch current user ──────────────────────────────────────────────────────
  async function fetchUser() {
    try {
      const response = await apiClient.get('/auth/me')
      user.value = response.data.user
      return { success: true }
    } catch (error) {
      user.value = null
      setToken(null)
      return { success: false }
    }
  }

  // ── Update profile ──────────────────────────────────────────────────────────
  async function updateProfile(data) {
    try {
      const response = await apiClient.put('/auth/profile', data)
      user.value = response.data.user
      return { success: true, message: response.data.message }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Failed to update profile',
        errors: error.response?.data?.errors || {},
      }
    }
  }

  // ── Change password ─────────────────────────────────────────────────────────
  async function changePassword(currentPassword, password, passwordConfirmation) {
    try {
      const response = await apiClient.put('/auth/password', {
        current_password: currentPassword,
        password,
        password_confirmation: passwordConfirmation,
      })
      // Server issues a new token after password change
      if (response.data.token) {
        setToken(response.data.token)
      }
      return { success: true, message: response.data.message }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Failed to change password',
        errors: error.response?.data?.errors || {},
      }
    }
  }

  return {
    user,
    token,
    isAuthenticated,
    isAdmin,
    setToken,
    register,
    login,
    logout,
    fetchUser,
    updateProfile,
    changePassword,
  }
})
