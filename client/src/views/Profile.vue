<template>
  <div class="flex h-screen bg-gray-50">
    <UserSidebar />

    <div class="flex-1 ml-64 overflow-auto">
      <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="px-8 py-4">
          <h1 class="text-2xl font-bold text-gray-900">Profile</h1>
          <p class="text-sm text-gray-500 mt-1">Manage your personal information</p>
        </div>
      </header>

      <main class="p-8">
        <div class="max-w-3xl">
          <!-- Profile Header -->
          <div class="bg-white rounded-xl p-8 border border-gray-100 shadow-sm mb-6">
            <div class="flex items-center gap-6">
              <div class="w-24 h-24 rounded-full bg-indigo-600 flex items-center justify-center">
                <span class="text-white font-bold text-3xl">{{ getUserInitials() }}</span>
              </div>
              <div class="flex-1">
                <h2 class="text-2xl font-bold text-gray-900">{{ user?.name || 'User' }}</h2>
                <p class="text-gray-500 mt-1">{{ user?.email || 'user@example.com' }}</p>
                <div class="flex items-center gap-2 mt-3">
                  <span class="px-3 py-1 bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-full">
                    {{ user?.role === 'admin' ? 'Admin' : 'User' }}
                  </span>
                  <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">
                    Active
                  </span>
                </div>
              </div>
              <button class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                Change Photo
              </button>
            </div>
          </div>

          <!-- Profile Information -->
          <div class="bg-white rounded-xl p-8 border border-gray-100 shadow-sm mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Personal Information</h3>
            <div class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                  <input type="text" :value="user?.name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                  <input type="email" :value="user?.email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                <textarea rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Tell us about yourself..."></textarea>
              </div>
              <div class="flex justify-end gap-3 pt-4">
                <button class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                  Cancel
                </button>
                <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                  Save Changes
                </button>
              </div>
            </div>
          </div>

          <!-- Account Stats -->
          <div class="bg-white rounded-xl p-8 border border-gray-100 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Account Statistics</h3>
            <div class="grid grid-cols-3 gap-6">
              <div class="text-center">
                <p class="text-3xl font-bold text-indigo-600">{{ stats.totalJobs }}</p>
                <p class="text-sm text-gray-500 mt-1">Total Jobs</p>
              </div>
              <div class="text-center">
                <p class="text-3xl font-bold text-green-600">{{ stats.completed }}</p>
                <p class="text-sm text-gray-500 mt-1">Completed</p>
              </div>
              <div class="text-center">
                <p class="text-3xl font-bold text-blue-600">{{ stats.running }}</p>
                <p class="text-sm text-gray-500 mt-1">Running</p>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import UserSidebar from '@/components/UserSidebar.vue'
import api from '@/api'

const authStore = useAuthStore()
const user = computed(() => authStore.user)

const stats = ref({
  totalJobs: 0,
  completed: 0,
  running: 0
})

function getUserInitials() {
  if (!user.value?.name) return 'U'
  const names = user.value.name.split(' ')
  if (names.length >= 2) return names[0][0].toUpperCase() + names[1][0].toUpperCase()
  return names[0][0].toUpperCase()
}

async function loadStats() {
  try {
    const res = await api.get('/jobs', { params: { per_page: 100 } })
    const jobs = res.data.data || []
    stats.value = {
      totalJobs: jobs.length,
      completed: jobs.filter(j => j.status === 'completed').length,
      running: jobs.filter(j => j.status === 'running').length
    }
  } catch (error) {
    console.error('Failed to load stats:', error)
  }
}

onMounted(() => {
  loadStats()
})
</script>
