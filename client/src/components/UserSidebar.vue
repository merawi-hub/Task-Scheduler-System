<template>
  <div class="h-screen w-64 bg-white border-r border-gray-200 flex flex-col fixed left-0 top-0">
    <!-- Logo Section -->
    <div class="p-6 border-b border-gray-200">
      <div class="flex items-center gap-3">
        <svg width="40" height="40" viewBox="0 0 64 64" class="flex-shrink-0">
          <defs>
            <linearGradient id="userHexGrad" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" style="stop-color:#6366f1;stop-opacity:1" />
              <stop offset="100%" style="stop-color:#8b5cf6;stop-opacity:1" />
            </linearGradient>
          </defs>
          <polygon points="32,4 56,18 56,46 32,60 8,46 8,18" fill="url(#userHexGrad)" stroke="#7c3aed" stroke-width="1.5"/>
          <circle cx="32" cy="32" r="10" fill="#ec4899"/>
        </svg>
        <div>
          <h2 class="text-gray-900 font-bold text-lg">TaskFlow</h2>
          <p class="text-gray-500 text-xs">Distributed Task Scheduler</p>
        </div>
      </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 px-3 py-4 overflow-y-auto">
      <!-- Main Navigation -->
      <div class="mb-2">
        <p class="px-3 mb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Main</p>
        <div class="space-y-0.5">
          <router-link
            to="/dashboard"
            :class="[
              'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
              isActive('/dashboard')
                ? 'bg-indigo-600 text-white'
                : 'text-gray-700 hover:bg-gray-100'
            ]"
          >
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span>Dashboard</span>
          </router-link>

          <router-link
            to="/submit-job"
            :class="[
              'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
              isActive('/submit-job')
                ? 'bg-indigo-600 text-white'
                : 'text-gray-700 hover:bg-gray-100'
            ]"
          >
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Submit Job</span>
          </router-link>

          <router-link
            to="/my-jobs"
            :class="[
              'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
              isActive('/my-jobs')
                ? 'bg-indigo-600 text-white'
                : 'text-gray-700 hover:bg-gray-100'
            ]"
          >
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <span>My Jobs</span>
          </router-link>
        </div>
      </div>

      <!-- Account Section -->
      <div class="mt-4">
        <p class="px-3 mb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Account</p>
        <div class="space-y-0.5">
          <router-link
            to="/notifications"
            :class="[
              'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
              isActive('/notifications')
                ? 'bg-indigo-600 text-white'
                : 'text-gray-700 hover:bg-gray-100'
            ]"
          >
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span>Notifications</span>
            <span v-if="unreadCount > 0" class="ml-auto bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">
              {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
          </router-link>

          <router-link
            to="/profile"
            :class="[
              'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
              isActive('/profile')
                ? 'bg-indigo-600 text-white'
                : 'text-gray-700 hover:bg-gray-100'
            ]"
          >
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span>Profile</span>
          </router-link>

          <router-link
            to="/settings"
            :class="[
              'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
              isActive('/settings')
                ? 'bg-indigo-600 text-white'
                : 'text-gray-700 hover:bg-gray-100'
            ]"
          >
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span>Settings</span>
          </router-link>
        </div>
      </div>

      <!-- Support Section -->
      <div class="mt-4">
        <p class="px-3 mb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Support</p>
        <div class="space-y-0.5">
          <router-link
            to="/help"
            :class="[
              'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
              isActive('/help')
                ? 'bg-indigo-600 text-white'
                : 'text-gray-700 hover:bg-gray-100'
            ]"
          >
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Help Center</span>
          </router-link>

          <button
            @click="showLogoutModal = true"
            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50 transition-colors"
          >
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            <span>Logout</span>
          </button>
        </div>
      </div>
    </nav>

    <!-- User Profile -->
    <div class="p-4 border-t border-gray-200">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center flex-shrink-0">
          <span class="text-white font-semibold text-sm">{{ getUserInitials() }}</span>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-900 truncate">{{ user?.name || 'User' }}</p>
          <p class="text-xs text-gray-500 truncate">{{ user?.email || 'Free Plan' }}</p>
        </div>
      </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <LogoutConfirmModal
      v-if="showLogoutModal"
      :loading="loggingOut"
      @confirm="handleLogout"
      @cancel="showLogoutModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { notificationsApi } from '@/api'
import LogoutConfirmModal from '@/components/modals/LogoutConfirmModal.vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const user = computed(() => authStore.user)
const showLogoutModal = ref(false)
const loggingOut = ref(false)
const unreadCount = ref(0)

let pollInterval = null

const fetchUnreadCount = async () => {
  // Only fetch if user is authenticated
  if (!authStore.user || !authStore.token) {
    return
  }

  try {
    const data = await notificationsApi.getUnreadCount()
    unreadCount.value = data.unread_count
  } catch (error) {
    // Silently fail - user might not be authenticated
    console.debug('Could not fetch unread count:', error)
  }
}

// Poll for new notifications every 30 seconds
const startPolling = () => {
  fetchUnreadCount()
  pollInterval = setInterval(fetchUnreadCount, 30000)
}

const stopPolling = () => {
  if (pollInterval) {
    clearInterval(pollInterval)
    pollInterval = null
  }
}

function isActive(path) {
  if (path === '/dashboard') return route.path === '/dashboard'
  return route.path.startsWith(path)
}

function getUserInitials() {
  if (!user.value?.name) return 'U'
  const names = user.value.name.split(' ')
  if (names.length >= 2) return names[0][0].toUpperCase() + names[1][0].toUpperCase()
  return names[0][0].toUpperCase()
}

async function handleLogout() {
  loggingOut.value = true
  try {
    await authStore.logout()
    showLogoutModal.value = false
    router.push('/login')
  } catch (error) {
    console.error('Logout failed:', error)
  } finally {
    loggingOut.value = false
  }
}

onMounted(() => {
  startPolling()
})

onUnmounted(() => {
  stopPolling()
})
</script>
