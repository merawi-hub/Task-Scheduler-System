<template>
  <div class="flex h-screen bg-gray-50">
    <UserSidebar />

    <div class="flex-1 ml-64 overflow-auto">
      <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="px-8 py-4">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
              <p class="text-sm text-gray-500 mt-1">Stay updated with your job activities</p>
            </div>
            <button @click="markAllAsRead" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">
              Mark all as read
            </button>
          </div>
        </div>
      </header>

      <main class="p-8">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
          <div v-if="notifications.length === 0" class="text-center py-16">
            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <p class="mt-3 text-sm text-gray-500">No notifications yet</p>
          </div>

          <div v-else class="divide-y divide-gray-100">
            <div v-for="notif in notifications" :key="notif.id" 
              :class="['p-6 hover:bg-gray-50 transition-colors cursor-pointer', !notif.read && 'bg-blue-50']"
              @click="markAsRead(notif)">
              <div class="flex items-start gap-4">
                <div :class="['w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0', getNotifBg(notif.type)]">
                  <svg class="w-5 h-5" :class="getNotifColor(notif.type)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path v-if="notif.type === 'success'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    <path v-else-if="notif.type === 'error'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-start justify-between">
                    <div>
                      <p class="text-sm font-medium text-gray-900">{{ notif.title }}</p>
                      <p class="text-sm text-gray-600 mt-1">{{ notif.message }}</p>
                      <p class="text-xs text-gray-400 mt-2">{{ notif.time }}</p>
                    </div>
                    <span v-if="!notif.read" class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0"></span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import UserSidebar from '@/components/UserSidebar.vue'

const notifications = ref([
  {
    id: 1,
    type: 'success',
    title: 'Job Completed',
    message: 'Your image processing job has completed successfully',
    time: '5 minutes ago',
    read: false
  },
  {
    id: 2,
    type: 'info',
    title: 'Task Retry',
    message: 'Task retry happened for data analysis job',
    time: '1 hour ago',
    read: false
  },
  {
    id: 3,
    type: 'error',
    title: 'Job Failed',
    message: 'Your batch processing job has failed',
    time: '2 hours ago',
    read: true
  }
])

function getNotifBg(type) {
  return {
    success: 'bg-green-100',
    error: 'bg-red-100',
    info: 'bg-blue-100'
  }[type] || 'bg-gray-100'
}

function getNotifColor(type) {
  return {
    success: 'text-green-600',
    error: 'text-red-600',
    info: 'text-blue-600'
  }[type] || 'text-gray-600'
}

function markAsRead(notif) {
  notif.read = true
}

function markAllAsRead() {
  notifications.value.forEach(n => n.read = true)
}
</script>
