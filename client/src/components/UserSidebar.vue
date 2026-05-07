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
      <div class="space-y-1">
        <router-link
          v-for="item in menuItems"
          :key="item.path"
          :to="item.path"
          :class="[
            'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
            isActive(item.path)
              ? 'bg-indigo-600 text-white'
              : 'text-gray-700 hover:bg-gray-100'
          ]"
        >
          <component :is="item.icon" class="w-5 h-5" />
          <span>{{ item.label }}</span>
          <span v-if="item.badge" class="ml-auto bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">
            {{ item.badge }}
          </span>
        </router-link>
      </div>
    </nav>

    <!-- Upgrade Plan Section -->
    <div class="p-4 border-t border-gray-200">
      <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-lg p-4 border border-indigo-100">
        <div class="flex items-start gap-3 mb-3">
          <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-900">Upgrade Plan</p>
            <p class="text-xs text-gray-600 mt-1">Unlock more features with higher limits.</p>
          </div>
        </div>
        <button class="w-full px-3 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
          Upgrade Now
        </button>
      </div>

      <!-- Usage Stats -->
      <div class="mt-4">
        <div class="flex items-center justify-between text-xs text-gray-600 mb-2">
          <span>Monthly Task Usage</span>
          <span class="font-semibold">2,450 / 5,000</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
          <div class="bg-indigo-600 h-2 rounded-full" style="width: 49%"></div>
        </div>
        <p class="text-xs text-gray-500 mt-1">49%</p>
      </div>
    </div>

    <!-- User Profile -->
    <div class="p-4 border-t border-gray-200">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center">
          <span class="text-white font-semibold text-sm">{{ getUserInitials() }}</span>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-900 truncate">{{ user?.name || 'User' }}</p>
          <p class="text-xs text-gray-500 truncate">Free Plan</p>
        </div>
        <button @click="showLogoutModal = true" class="text-gray-400 hover:text-red-600 transition-colors hover:scale-110">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
        </button>
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
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import LogoutConfirmModal from '@/components/modals/LogoutConfirmModal.vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const user = computed(() => authStore.user)
const showLogoutModal = ref(false)
const loggingOut = ref(false)

const menuItems = [
  {
    path: '/dashboard',
    label: 'Dashboard',
    icon: 'DashboardIcon'
  },
  {
    path: '/my-jobs',
    label: 'My Jobs',
    icon: 'JobsIcon'
  },
  {
    path: '/tasks',
    label: 'Tasks',
    icon: 'TasksIcon'
  },
  {
    path: '/schedules',
    label: 'Schedules',
    icon: 'SchedulesIcon'
  },
  {
    path: '/workers',
    label: 'Workers',
    icon: 'WorkersIcon'
  },
  {
    path: '/queues',
    label: 'Queues',
    icon: 'QueuesIcon'
  },
  {
    path: '/monitoring',
    label: 'Monitoring',
    icon: 'MonitoringIcon'
  },
  {
    path: '/logs',
    label: 'Logs',
    icon: 'LogsIcon'
  },
  {
    path: '/notifications',
    label: 'Notifications',
    icon: 'NotificationsIcon',
    badge: 3
  },
  {
    path: '/settings',
    label: 'Settings',
    icon: 'SettingsIcon'
  }
]

function isActive(path) {
  return route.path === path || (path !== '/dashboard' && route.path.startsWith(path))
}

function getUserInitials() {
  if (!user.value?.name) return 'U'
  const names = user.value.name.split(' ')
  if (names.length >= 2) {
    return names[0][0] + names[1][0]
  }
  return names[0][0]
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

// Icon Components
const DashboardIcon = {
  template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>`
}

const JobsIcon = {
  template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>`
}

const TasksIcon = {
  template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>`
}

const SchedulesIcon = {
  template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>`
}

const WorkersIcon = {
  template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>`
}

const QueuesIcon = {
  template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>`
}

const MonitoringIcon = {
  template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>`
}

const LogsIcon = {
  template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>`
}

const NotificationsIcon = {
  template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>`
}

const SettingsIcon = {
  template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>`
}
</script>
