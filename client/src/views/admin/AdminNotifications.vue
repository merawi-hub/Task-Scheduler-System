<template>
  <div class="flex-1 overflow-auto">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
      <div class="px-8 py-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
            <p class="text-sm text-gray-500 mt-1">Stay updated with your jobs and system events</p>
          </div>
        </div>
      </div>
    </header>

    <main class="p-8">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <div class="flex items-center">
            <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Total</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <div class="flex items-center">
            <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Unread</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.unread }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <div class="flex items-center">
            <div class="flex-shrink-0 bg-gray-100 rounded-lg p-3">
              <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Read</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.read }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions Bar -->
      <div class="bg-white rounded-xl shadow-sm mb-6 p-4 border border-gray-100">
        <div class="flex flex-wrap items-center justify-between gap-4">
          <!-- Filters -->
          <div class="flex items-center gap-4">
            <label class="flex items-center">
              <input
                type="checkbox"
                v-model="showUnreadOnly"
                @change="fetchNotifications"
                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
              />
              <span class="ml-2 text-sm text-gray-700">Unread only</span>
            </label>

            <select
              v-model="filterType"
              @change="fetchNotifications"
              class="rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500"
            >
              <option value="">All types</option>
              <option value="job_completed">Job Completed</option>
              <option value="job_failed">Job Failed</option>
              <option value="job_started">Job Started</option>
              <option value="job_progress">Job Progress</option>
              <option value="job_delayed">Job Delayed</option>
              <option value="worker_died">Worker Died</option>
              <option value="no_workers">No Workers</option>
              <option value="system_health">System Health</option>
            </select>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-2">
            <button
              v-if="stats.unread > 0"
              @click="markAllAsRead"
              class="px-4 py-2 text-sm font-medium text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition-colors"
            >
              Mark all as read
            </button>
            <button
              v-if="stats.read > 0"
              @click="deleteAllRead"
              class="px-4 py-2 text-sm font-medium text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors"
            >
              Delete all read
            </button>
          </div>
        </div>
      </div>

      <!-- Notifications List -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div v-if="loading" class="p-8 text-center text-gray-500">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
          <p class="mt-2">Loading notifications...</p>
        </div>

        <div v-else-if="notifications.length === 0" class="p-12 text-center">
          <svg
            class="w-16 h-16 mx-auto mb-4 text-gray-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
            />
          </svg>
          <p class="text-lg text-gray-600">No notifications found</p>
          <p class="text-sm text-gray-500 mt-2">
            {{ showUnreadOnly ? 'You have no unread notifications' : 'You have no notifications yet' }}
          </p>
        </div>

        <div v-else class="divide-y divide-gray-100">
          <div
            v-for="notification in notifications"
            :key="notification.id"
            class="p-6 hover:bg-gray-50 transition-colors"
            :class="{ 'bg-indigo-50': !notification.read_at }"
          >
            <div class="flex items-start gap-4">
              <!-- Icon -->
              <div
                class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center"
                :class="getIconColor(notification.data.color)"
              >
                <component :is="getIcon(notification.data.icon)" class="w-6 h-6" />
              </div>

              <!-- Content -->
              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <h3 class="text-base font-semibold text-gray-900">
                      {{ notification.data.title }}
                    </h3>
                    <p class="text-gray-700 mt-1 text-sm">
                      {{ notification.data.message }}
                    </p>

                    <!-- Additional Details -->
                    <div v-if="notification.data.job_id" class="mt-2">
                      <router-link
                        :to="`/admin/jobs`"
                        class="text-sm text-indigo-600 hover:text-indigo-800 font-medium"
                      >
                        View Jobs →
                      </router-link>
                    </div>

                    <p class="text-xs text-gray-500 mt-2">
                      {{ formatTime(notification.created_at) }}
                    </p>
                  </div>

                  <!-- Actions -->
                  <div class="flex items-center gap-2 ml-4">
                    <button
                      v-if="!notification.read_at"
                      @click="markAsRead(notification)"
                      class="p-2 text-indigo-600 hover:bg-indigo-100 rounded-lg transition-colors"
                      title="Mark as read"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                    </button>
                    <button
                      v-else
                      @click="markAsUnread(notification)"
                      class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                      title="Mark as unread"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                      </svg>
                    </button>
                    <button
                      @click="deleteNotification(notification)"
                      class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors"
                      title="Delete"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="px-6 py-4 border-t border-gray-100">
          <div class="flex items-center justify-between">
            <button
              @click="goToPage(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Previous
            </button>

            <span class="text-sm text-gray-700">
              Page {{ pagination.current_page }} of {{ pagination.last_page }}
            </span>

            <button
              @click="goToPage(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, h } from 'vue'
import { notificationsApi } from '@/api'

const notifications = ref([])
const loading = ref(true)
const showUnreadOnly = ref(false)
const filterType = ref('')
const stats = ref({
  total: 0,
  unread: 0,
  read: 0,
})
const pagination = ref({
  current_page: 1,
  per_page: 20,
  total: 0,
  last_page: 1,
})

const fetchNotifications = async (page = 1) => {
  try {
    loading.value = true
    const params = {
      page,
      per_page: pagination.value.per_page,
    }
    if (showUnreadOnly.value) {
      params.unread_only = true
    }
    if (filterType.value) {
      params.type = filterType.value
    }

    const data = await notificationsApi.getNotifications(params)
    notifications.value = data.notifications
    pagination.value = data.pagination
  } catch (error) {
    console.error('Error fetching notifications:', error)
  } finally {
    loading.value = false
  }
}

const fetchStats = async () => {
  try {
    const data = await notificationsApi.getStats()
    stats.value = data
  } catch (error) {
    console.error('Error fetching stats:', error)
  }
}

const markAsRead = async (notification) => {
  try {
    await notificationsApi.markAsRead(notification.id)
    notification.read_at = new Date().toISOString()
    stats.value.unread--
    stats.value.read++
  } catch (error) {
    console.error('Error marking as read:', error)
  }
}

const markAsUnread = async (notification) => {
  try {
    await notificationsApi.markAsUnread(notification.id)
    notification.read_at = null
    stats.value.unread++
    stats.value.read--
  } catch (error) {
    console.error('Error marking as unread:', error)
  }
}

const markAllAsRead = async () => {
  try {
    await notificationsApi.markAllAsRead()
    notifications.value.forEach(n => n.read_at = new Date().toISOString())
    await fetchStats()
  } catch (error) {
    console.error('Error marking all as read:', error)
  }
}

const deleteNotification = async (notification) => {
  if (!confirm('Are you sure you want to delete this notification?')) return

  try {
    await notificationsApi.deleteNotification(notification.id)
    notifications.value = notifications.value.filter(n => n.id !== notification.id)
    await fetchStats()
  } catch (error) {
    console.error('Error deleting notification:', error)
  }
}

const deleteAllRead = async () => {
  if (!confirm('Are you sure you want to delete all read notifications?')) return

  try {
    await notificationsApi.deleteAllRead()
    await fetchNotifications()
    await fetchStats()
  } catch (error) {
    console.error('Error deleting read notifications:', error)
  }
}

const goToPage = (page) => {
  fetchNotifications(page)
}

const getIcon = (iconName) => {
  const icons = {
    'check-circle': () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' })
    ]),
    'x-circle': () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z' })
    ]),
    'play': () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z' }),
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M21 12a9 9 0 11-18 0 9 9 0 0118 0z' })
    ]),
    'clock': () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' })
    ]),
    'trending-up': () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6' })
    ]),
    'alert-circle': () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' })
    ]),
    'alert-triangle': () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z' })
    ]),
    'activity': () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' })
    ]),
    'user': () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' })
    ]),
  }
  return icons[iconName] || icons['alert-circle']
}

const getIconColor = (color) => {
  const colors = {
    green: 'bg-green-100 text-green-600',
    blue: 'bg-blue-100 text-blue-600',
    red: 'bg-red-100 text-red-600',
    yellow: 'bg-yellow-100 text-yellow-600',
    orange: 'bg-orange-100 text-orange-600',
  }
  return colors[color] || 'bg-gray-100 text-gray-600'
}

const formatTime = (timestamp) => {
  const date = new Date(timestamp)
  const now = new Date()
  const diffMs = now - date
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)

  if (diffMins < 1) return 'Just now'
  if (diffMins < 60) return `${diffMins} minutes ago`
  if (diffHours < 24) return `${diffHours} hours ago`
  if (diffDays < 7) return `${diffDays} days ago`
  return date.toLocaleDateString() + ' at ' + date.toLocaleTimeString()
}

onMounted(() => {
  fetchNotifications()
  fetchStats()
})
</script>
