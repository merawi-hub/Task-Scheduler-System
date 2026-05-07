<template>
  <div class="flex-1 overflow-auto">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
      <div class="px-8 py-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">All Users</h1>
            <p class="text-sm text-gray-500 mt-1">Manage user accounts and permissions</p>
          </div>
          <button
            @click="refreshUsers"
            class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
          >
            <svg class="w-5 h-5" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span class="text-sm font-medium">Refresh</span>
          </button>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="p-8">
      <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div v-if="loading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
          <p class="text-gray-600 mt-4">Loading users...</p>
        </div>

        <div v-else-if="error" class="p-6">
          <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            {{ error }}
          </div>
        </div>

        <div v-else-if="users.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
          <p class="text-gray-600 mt-4">No users found.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-100">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jobs</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ user.id }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ user.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ user.email }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'px-3 py-1 text-xs font-semibold rounded-full',
                      user.is_admin
                        ? 'bg-purple-100 text-purple-800'
                        : 'bg-gray-100 text-gray-800'
                    ]"
                  >
                    {{ user.is_admin ? 'Admin' : 'User' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                  {{ user.jobs_count || 0 }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                  {{ formatDate(user.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                  <button
                    @click="toggleAdmin(user)"
                    class="text-blue-600 hover:text-blue-900 font-medium"
                  >
                    {{ user.is_admin ? 'Remove Admin' : 'Make Admin' }}
                  </button>
                  <button
                    @click="deleteUser(user.id)"
                    class="text-red-600 hover:text-red-900 font-medium"
                    :disabled="user.id === currentUserId"
                    :class="{ 'opacity-50 cursor-not-allowed': user.id === currentUserId }"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAdminStore } from '@/stores/adminStore'
import { useAuthStore } from '@/stores/authStore'

const adminStore = useAdminStore()
const authStore = useAuthStore()

const users = ref([])
const loading = ref(false)
const error = ref(null)

const currentUserId = computed(() => authStore.user?.id)

function formatDate(dateString) {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString()
}

async function refreshUsers() {
  loading.value = true
  error.value = null
  
  const result = await adminStore.fetchAllUsers()
  if (result.success) {
    users.value = result.data.data || result.data
  } else {
    error.value = result.error
  }
  
  loading.value = false
}

async function toggleAdmin(user) {
  const action = user.is_admin ? 'remove admin privileges from' : 'grant admin privileges to'
  if (!confirm(`Are you sure you want to ${action} ${user.name}?`)) return
  
  const result = await adminStore.updateUser(user.id, {
    is_admin: !user.is_admin
  })
  
  if (result.success) {
    alert('User updated successfully')
    refreshUsers()
  } else {
    alert('Failed to update user: ' + result.error)
  }
}

async function deleteUser(userId) {
  if (userId === currentUserId.value) {
    alert('You cannot delete your own account')
    return
  }
  
  if (!confirm('Are you sure you want to delete this user? This will also delete all their jobs.')) return
  
  const result = await adminStore.deleteUser(userId)
  if (result.success) {
    alert('User deleted successfully')
    refreshUsers()
  } else {
    alert('Failed to delete user: ' + result.error)
  }
}

onMounted(() => {
  refreshUsers()
})
</script>
