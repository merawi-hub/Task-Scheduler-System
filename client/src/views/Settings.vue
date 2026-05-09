<template>
  <div class="flex h-screen bg-[#f0f2f8]">
    <UserSidebar />

    <div class="flex-1 ml-64 overflow-auto">
      <!-- Page Header -->
      <header class="bg-[#f0f2f8] px-8 pt-8 pb-4">
        <div class="flex items-start justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Profile Settings</h1>
            <p class="text-sm text-gray-500 mt-1">Manage your account settings</p>
          </div>
        </div>
      </header>

      <!-- Main Content -->
      <main class="px-8 pb-8">
        <div class="flex gap-6">

          <!-- Left Sidebar Nav -->
          <div class="w-56 flex-shrink-0">
            <nav class="space-y-1">
              <button
                v-for="tab in tabs"
                :key="tab.id"
                @click="activeTab = tab.id"
                :class="[
                  'w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all text-left',
                  activeTab === tab.id
                    ? 'bg-white text-indigo-600 border-l-4 border-indigo-600 shadow-sm'
                    : 'text-gray-600 hover:bg-white hover:text-gray-900'
                ]"
              >
                <component :is="tab.icon" :active="activeTab === tab.id" />
                <span>{{ tab.label }}</span>
              </button>
            </nav>
          </div>

          <!-- Right Content Panel -->
          <div class="flex-1 bg-white rounded-xl shadow-sm border border-gray-100 p-8">

            <!-- ── Profile Tab ─────────────────────────────────────────────── -->
            <div v-if="activeTab === 'profile'">
              <h2 class="text-base font-bold text-gray-900 mb-6">Profile Information</h2>

              <form @submit.prevent="saveProfile" class="space-y-5">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                  <input
                    v-model="profile.name"
                    type="text"
                    placeholder="Your name"
                    class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                  <input
                    v-model="profile.email"
                    type="email"
                    placeholder="you@example.com"
                    class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white"
                  />
                </div>

                <!-- Role badge (read-only) -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                  <div class="flex items-center gap-2">
                    <span :class="[
                      'px-3 py-1 text-xs font-semibold rounded-full',
                      authStore.isAdmin ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800'
                    ]">
                      {{ authStore.isAdmin ? 'Administrator' : 'User' }}
                    </span>
                  </div>
                </div>

                <div v-if="profileError" class="flex items-center gap-2 text-red-600 text-sm bg-red-50 border border-red-200 rounded-lg px-4 py-3">
                  <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  {{ profileError }}
                </div>

                <div v-if="profileSuccess" class="flex items-center gap-2 text-green-600 text-sm bg-green-50 border border-green-200 rounded-lg px-4 py-3">
                  <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                  Profile updated successfully.
                </div>

                <button
                  type="submit"
                  :disabled="savingProfile"
                  class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors disabled:opacity-60 disabled:cursor-not-allowed mt-2"
                >
                  {{ savingProfile ? 'Saving...' : 'Save Changes' }}
                </button>
              </form>
            </div>

            <!-- ── Security Tab ────────────────────────────────────────────── -->
            <div v-if="activeTab === 'security'">
              <h2 class="text-base font-bold text-gray-900 mb-6">Security Settings</h2>

              <form @submit.prevent="savePassword" class="space-y-5">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                  <input
                    v-model="security.currentPassword"
                    type="password"
                    placeholder="Enter current password"
                    class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                  <input
                    v-model="security.newPassword"
                    type="password"
                    placeholder="Enter new password (min 8 chars)"
                    class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                  <input
                    v-model="security.confirmPassword"
                    type="password"
                    placeholder="Confirm new password"
                    class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white"
                  />
                </div>

                <div v-if="securityError" class="flex items-center gap-2 text-red-600 text-sm bg-red-50 border border-red-200 rounded-lg px-4 py-3">
                  <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  {{ securityError }}
                </div>

                <div v-if="securitySuccess" class="flex items-center gap-2 text-green-600 text-sm bg-green-50 border border-green-200 rounded-lg px-4 py-3">
                  <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                  Password changed successfully.
                </div>

                <button
                  type="submit"
                  :disabled="savingPassword"
                  class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                >
                  {{ savingPassword ? 'Updating...' : 'Change Password' }}
                </button>
              </form>
            </div>

            <!-- ── Notifications Tab ───────────────────────────────────────── -->
            <div v-if="activeTab === 'notifications'">
              <h2 class="text-base font-bold text-gray-900 mb-6">Notification Preferences</h2>

              <div class="space-y-5">
                <div
                  v-for="item in notificationItems"
                  :key="item.id"
                  class="flex items-center justify-between py-4 border-b border-gray-100 last:border-0"
                >
                  <div>
                    <p class="text-sm font-medium text-gray-800">{{ item.label }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ item.description }}</p>
                  </div>
                  <button
                    @click="item.enabled = !item.enabled"
                    :class="[
                      'relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none',
                      item.enabled ? 'bg-indigo-600' : 'bg-gray-200'
                    ]"
                  >
                    <span
                      :class="[
                        'inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform',
                        item.enabled ? 'translate-x-6' : 'translate-x-1'
                      ]"
                    />
                  </button>
                </div>
              </div>

              <button
                @click="saveNotifications"
                class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors mt-6"
              >
                Save Preferences
              </button>
            </div>

            <!-- ── Preferences Tab ─────────────────────────────────────────── -->
            <div v-if="activeTab === 'preferences'">
              <h2 class="text-base font-bold text-gray-900 mb-6">Preferences</h2>

              <div class="space-y-5">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Language</label>
                  <div class="relative">
                    <select
                      v-model="preferences.language"
                      class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white appearance-none pr-10"
                    >
                      <option value="en">English</option>
                      <option value="fr">French</option>
                      <option value="de">German</option>
                      <option value="es">Spanish</option>
                      <option value="ar">Arabic</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                      </svg>
                    </div>
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Date Format</label>
                  <div class="relative">
                    <select
                      v-model="preferences.dateFormat"
                      class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white appearance-none pr-10"
                    >
                      <option value="MM/DD/YYYY">MM/DD/YYYY</option>
                      <option value="DD/MM/YYYY">DD/MM/YYYY</option>
                      <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                      </svg>
                    </div>
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-3">Theme</label>
                  <div class="flex gap-3">
                    <button
                      v-for="theme in ['Light', 'Dark', 'System']"
                      :key="theme"
                      @click="preferences.theme = theme.toLowerCase()"
                      :class="[
                        'flex-1 py-2.5 text-sm font-medium rounded-lg border transition-all',
                        preferences.theme === theme.toLowerCase()
                          ? 'bg-indigo-600 text-white border-indigo-600'
                          : 'bg-white text-gray-600 border-gray-200 hover:border-indigo-300'
                      ]"
                    >
                      {{ theme }}
                    </button>
                  </div>
                </div>

                <button
                  @click="savePreferences"
                  class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors mt-2"
                >
                  Save Preferences
                </button>
              </div>
            </div>

            <!-- ── API Keys Tab ─────────────────────────────────────────────── -->
            <div v-if="activeTab === 'api-keys'">
              <div class="flex items-center justify-between mb-6">
                <h2 class="text-base font-bold text-gray-900">API Keys</h2>
                <button
                  @click="generateApiKey"
                  class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                  </svg>
                  Generate New Key
                </button>
              </div>

              <div v-if="apiKeys.length === 0" class="text-center py-12 text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
                <p class="text-sm">No API keys yet. Generate one to get started.</p>
              </div>

              <div v-else class="space-y-3">
                <div
                  v-for="key in apiKeys"
                  :key="key.id"
                  class="flex items-center justify-between p-4 border border-gray-200 rounded-lg bg-gray-50"
                >
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800">{{ key.name }}</p>
                    <p class="text-xs text-gray-500 font-mono mt-1 truncate">{{ key.value }}</p>
                    <p class="text-xs text-gray-400 mt-1">Created {{ key.created }}</p>
                  </div>
                  <button
                    @click="revokeApiKey(key.id)"
                    class="ml-4 text-xs text-red-500 hover:text-red-700 font-medium flex-shrink-0"
                  >
                    Revoke
                  </button>
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
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import UserSidebar from '@/components/UserSidebar.vue'

const authStore = useAuthStore()
const activeTab = ref('profile')

// ── Inline icon components ────────────────────────────────────────────────────
const ProfileIcon = {
  props: ['active'],
  template: `<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>`
}
const SecurityIcon = {
  props: ['active'],
  template: `<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>`
}
const NotificationsIcon = {
  props: ['active'],
  template: `<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>`
}
const PreferencesIcon = {
  props: ['active'],
  template: `<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
      d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>`
}
const ApiKeysIcon = {
  props: ['active'],
  template: `<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
      d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>`
}

const tabs = [
  { id: 'profile',       label: 'Profile',       icon: ProfileIcon },
  { id: 'security',      label: 'Security',      icon: SecurityIcon },
  { id: 'notifications', label: 'Notifications', icon: NotificationsIcon },
  { id: 'preferences',   label: 'Preferences',   icon: PreferencesIcon },
  { id: 'api-keys',      label: 'API Keys',      icon: ApiKeysIcon },
]

// ── Profile ───────────────────────────────────────────────────────────────────
const profile = ref({ name: '', email: '' })
const savingProfile = ref(false)
const profileSuccess = ref(false)
const profileError = ref('')

onMounted(() => {
  const u = authStore.user
  if (u) {
    profile.value.name  = u.name  || ''
    profile.value.email = u.email || ''
  }
})

async function saveProfile() {
  savingProfile.value = true
  profileSuccess.value = false
  profileError.value = ''
  try {
    const result = await authStore.updateProfile({
      name: profile.value.name,
      email: profile.value.email,
    })
    if (result.success) {
      profileSuccess.value = true
      setTimeout(() => { profileSuccess.value = false }, 3000)
    } else {
      profileError.value = result.message || 'Failed to update profile'
    }
  } finally {
    savingProfile.value = false
  }
}

// ── Security ──────────────────────────────────────────────────────────────────
const security = ref({ currentPassword: '', newPassword: '', confirmPassword: '' })
const savingPassword = ref(false)
const securitySuccess = ref(false)
const securityError = ref('')

async function savePassword() {
  securityError.value   = ''
  securitySuccess.value = false

  if (security.value.newPassword !== security.value.confirmPassword) {
    securityError.value = 'New passwords do not match.'
    return
  }
  if (security.value.newPassword.length < 8) {
    securityError.value = 'Password must be at least 8 characters.'
    return
  }

  savingPassword.value = true
  try {
    const result = await authStore.changePassword(
      security.value.currentPassword,
      security.value.newPassword,
      security.value.confirmPassword
    )
    if (result.success) {
      securitySuccess.value = true
      security.value = { currentPassword: '', newPassword: '', confirmPassword: '' }
      setTimeout(() => { securitySuccess.value = false }, 3000)
    } else {
      securityError.value = result.message || 'Failed to change password'
    }
  } finally {
    savingPassword.value = false
  }
}

// ── Notifications ─────────────────────────────────────────────────────────────
const notificationItems = ref([
  { id: 'job_complete',   label: 'Job Completed',   description: 'Get notified when a job finishes successfully.',       enabled: true  },
  { id: 'job_failed',     label: 'Job Failed',       description: 'Get notified when a job fails or encounters an error.', enabled: true  },
  { id: 'worker_down',    label: 'Worker Offline',   description: 'Alert when a worker node goes offline.',               enabled: false },
  { id: 'weekly_report',  label: 'Weekly Summary',   description: 'Receive a weekly digest of your job activity.',        enabled: true  },
  { id: 'security_alert', label: 'Security Alerts',  description: 'Notify on suspicious login attempts.',                 enabled: true  },
])

function saveNotifications() {
  // Persist notification preferences (local for now)
}

// ── Preferences ───────────────────────────────────────────────────────────────
const preferences = ref({ language: 'en', dateFormat: 'MM/DD/YYYY', theme: 'light' })

function savePreferences() {
  // Persist preferences (local for now)
}

// ── API Keys ──────────────────────────────────────────────────────────────────
const apiKeys = ref([])

function generateApiKey() {
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'
  const key = Array.from({ length: 40 }, () => chars[Math.floor(Math.random() * chars.length)]).join('')
  apiKeys.value.push({
    id:      Date.now(),
    name:    `Key #${apiKeys.value.length + 1}`,
    value:   `sk_live_${key}`,
    created: new Date().toLocaleDateString(),
  })
}

function revokeApiKey(id) {
  apiKeys.value = apiKeys.value.filter(k => k.id !== id)
}
</script>
