<template>
  <div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <UserSidebar />

    <!-- Main Content -->
    <div class="flex-1 ml-64 overflow-auto">
      <!-- Header -->
      <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="px-8 py-4">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-2xl font-bold text-gray-900">My Jobs</h1>
              <p class="text-sm text-gray-500 mt-1">Manage and monitor all your submitted jobs</p>
            </div>
            <div class="flex items-center gap-3">
              <!-- Status Filters -->
              <div class="flex items-center gap-2 bg-gray-100 rounded-lg p-1">
                <button 
                  v-for="filter in statusFilters" 
                  :key="filter.value"
                  @click="filterStatus = filter.value"
                  :class="[
                    'px-4 py-2 rounded-md text-sm font-medium transition-all',
                    filterStatus === filter.value 
                      ? 'bg-white text-gray-900 shadow-sm' 
                      : 'text-gray-600 hover:text-gray-900'
                  ]"
                >
                  {{ filter.label }}
                </button>
              </div>

              <!-- Refresh Button -->
              <button @click="refreshJobs" class="flex items-center gap-2 px-4 py-2 text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span class="text-sm font-medium">Refresh</span>
              </button>

              <!-- Create Job Button -->
              <button @click="showCreateModal = true" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="text-sm font-medium">Create Job</span>
              </button>
            </div>
          </div>
        </div>
        
        <!-- Connection Warning Banner (Requirement 14.4) -->
        <div v-if="connectionWarning" class="px-8 py-3 bg-yellow-50 border-t border-yellow-200">
          <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="flex-1">
              <p class="text-sm font-medium text-yellow-800">Connection issue detected</p>
              <p class="text-xs text-yellow-700 mt-0.5">Unable to connect to the server. Please check your network connection.</p>
            </div>
            <button 
              @click="refreshJobs" 
              class="px-3 py-1.5 text-xs font-medium text-yellow-700 hover:text-yellow-900 bg-yellow-100 hover:bg-yellow-200 rounded-lg transition-colors"
              :disabled="loading"
            >
              {{ loading ? 'Retrying...' : 'Retry Now' }}
            </button>
          </div>
        </div>
      </header>

      <!-- Main Content -->
      <main class="p-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
          <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-500 mb-1">Total Jobs</p>
                <h3 class="text-2xl font-bold text-gray-900">{{ jobStats.total }}</h3>
              </div>
              <div class="p-3 bg-blue-50 rounded-lg">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-500 mb-1">Completed</p>
                <h3 class="text-2xl font-bold text-green-600">{{ jobStats.completed }}</h3>
              </div>
              <div class="p-3 bg-green-50 rounded-lg">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-500 mb-1">Running</p>
                <h3 class="text-2xl font-bold text-blue-600">{{ jobStats.running }}</h3>
              </div>
              <div class="p-3 bg-blue-50 rounded-lg">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-500 mb-1">Pending</p>
                <h3 class="text-2xl font-bold text-yellow-600">{{ jobStats.pending }}</h3>
              </div>
              <div class="p-3 bg-yellow-50 rounded-lg">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-500 mb-1">Failed</p>
                <h3 class="text-2xl font-bold text-red-600">{{ jobStats.failed }}</h3>
              </div>
              <div class="p-3 bg-red-50 rounded-lg">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Jobs Table -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
          <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <h3 class="text-lg font-semibold text-gray-900">All Jobs</h3>
                <!-- Show matching count when search is active -->
                <span v-if="isSearchActive" class="px-3 py-1 bg-indigo-100 text-indigo-700 text-sm font-medium rounded-full">
                  {{ matchingJobsCount }} {{ matchingJobsCount === 1 ? 'match' : 'matches' }}
                </span>
              </div>
              <div class="flex items-center gap-3">
                <div class="relative">
                  <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search by name, type, or date..."
                    :class="[
                      'pl-10 pr-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all',
                      isSearchActive ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300'
                    ]"
                  />
                  <svg class="w-5 h-5 absolute left-3 top-2.5 transition-colors" :class="isSearchActive ? 'text-indigo-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                  <!-- Clear search button when active -->
                  <button
                    v-if="isSearchActive"
                    @click="searchQuery = ''"
                    class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Loading State -->
          <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            <p class="text-gray-600 mt-4">Loading jobs...</p>
          </div>

          <!-- Error State -->
          <div v-else-if="error" class="p-6">
            <div class="bg-red-50 border border-red-200 rounded-lg p-6">
              <div class="flex items-start gap-4">
                <!-- Error Icon -->
                <div class="flex-shrink-0">
                  <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                
                <!-- Error Content -->
                <div class="flex-1">
                  <h3 class="text-sm font-semibold text-red-800 mb-1">Unable to Load Jobs</h3>
                  <p class="text-sm text-red-700">{{ error }}</p>
                  
                  <!-- Retry Button (Requirement 14.5) -->
                  <button 
                    @click="refreshJobs" 
                    class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors"
                    :disabled="loading"
                  >
                    <svg class="w-4 h-4" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    {{ loading ? 'Retrying...' : 'Retry' }}
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else-if="filteredJobs.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No jobs found</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by creating a new job.</p>
            <button @click="showCreateModal = true" class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
              Create Job
            </button>
          </div>

          <!-- Jobs Table -->
          <div v-else class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tasks</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="job in paginatedJobs" :key="job.id"
                  :class="[
                    'hover:bg-gray-50 cursor-pointer transition-colors',
                    recentlyCompleted.has(job.id) ? 'bg-green-50 ring-2 ring-green-300 ring-inset' : ''
                  ]"
                  @click="viewJob(job)">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-3">
                      <div :class="[
                        'w-10 h-10 rounded-lg flex items-center justify-center',
                        job.status === 'completed' ? 'bg-green-100' : 'bg-indigo-100'
                      ]">
                        <svg v-if="job.status === 'completed'" class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <svg v-else class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                      </div>
                      <div>
                        <div class="flex items-center gap-2">
                          <p class="text-sm font-medium text-gray-900">{{ job.name }}</p>
                          <!-- "Just completed" flash badge -->
                          <span v-if="recentlyCompleted.has(job.id)"
                            class="px-2 py-0.5 bg-green-500 text-white text-[10px] font-bold rounded-full animate-pulse">
                            ✓ Just completed!
                          </span>
                        </div>
                        <p class="text-xs text-gray-500">#{{ job.id }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ job.type || 'General' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="[
                      'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full',
                      getStatusClass(job.status)
                    ]">
                      {{ job.status.toUpperCase() }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-2">
                      <div class="flex-1 bg-gray-200 rounded-full h-2 max-w-[100px]">
                        <div :class="getProgressColor(job.status)" class="h-2 rounded-full transition-all duration-500 ease-in-out" :style="{ width: getProgress(job) + '%' }"></div>
                      </div>
                      <span class="text-xs font-medium text-gray-600">{{ getProgress(job) }}%</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ job.completed_tasks || 0 }} / {{ job.total_tasks || 0 }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(job.created_at) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <div class="flex items-center gap-2">
                      <button @click.stop="viewJob(job)" class="text-indigo-600 hover:text-indigo-900 font-medium px-3 py-1 rounded hover:bg-indigo-50">
                        View Details
                      </button>
                      <button @click.stop="$router.push(`/jobs/${job.id}/monitoring`)" class="text-blue-600 hover:text-blue-900 font-medium px-3 py-1 rounded hover:bg-blue-50">
                        Monitor
                      </button>
                      <button v-if="job.status === 'running' || job.status === 'pending'" @click.stop="cancelJob(job)" class="text-red-600 hover:text-red-900 font-medium px-3 py-1 rounded hover:bg-red-50">
                        Cancel
                      </button>
                      <button v-if="job.status === 'failed'" @click.stop="retryJob(job)" class="text-orange-600 hover:text-orange-900 font-medium px-3 py-1 rounded hover:bg-orange-50">
                        Retry
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
            
            <!-- Pagination Controls (Requirement 15.1) -->
            <div v-if="totalPages > 1" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
              <div class="text-sm text-gray-500">
                Showing {{ ((currentPage - 1) * pageSize) + 1 }} to {{ Math.min(currentPage * pageSize, filteredJobs.length) }} of {{ filteredJobs.length }} jobs
              </div>
              <div class="flex items-center gap-2">
                <button 
                  @click="currentPage = 1" 
                  :disabled="currentPage === 1"
                  class="px-3 py-1 text-sm font-medium rounded-lg transition-colors"
                  :class="currentPage === 1 ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-gray-100'"
                >
                  First
                </button>
                <button 
                  @click="currentPage--" 
                  :disabled="currentPage === 1"
                  class="px-3 py-1 text-sm font-medium rounded-lg transition-colors"
                  :class="currentPage === 1 ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-gray-100'"
                >
                  Previous
                </button>
                
                <div class="flex items-center gap-1">
                  <button 
                    v-for="page in visiblePageNumbers" 
                    :key="page"
                    @click="currentPage = page"
                    class="px-3 py-1 text-sm font-medium rounded-lg transition-colors"
                    :class="currentPage === page ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100'"
                  >
                    {{ page }}
                  </button>
                </div>
                
                <button 
                  @click="currentPage++" 
                  :disabled="currentPage === totalPages"
                  class="px-3 py-1 text-sm font-medium rounded-lg transition-colors"
                  :class="currentPage === totalPages ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-gray-100'"
                >
                  Next
                </button>
                <button 
                  @click="currentPage = totalPages" 
                  :disabled="currentPage === totalPages"
                  class="px-3 py-1 text-sm font-medium rounded-lg transition-colors"
                  :class="currentPage === totalPages ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-gray-100'"
                >
                  Last
                </button>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>

    <!-- Create Job Modal -->
    <CreateJobModal v-if="showCreateModal" @close="showCreateModal = false" @created="handleJobCreated" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import UserSidebar from '@/components/UserSidebar.vue'
import CreateJobModal from '@/components/modals/CreateJobModal.vue'
import { useToastStore } from '@/stores/toastStore'
import api from '@/api'

const router = useRouter()
const toastStore = useToastStore()

const statusFilters = [
  { label: 'All', value: '' },
  { label: 'Running', value: 'running' },
  { label: 'Completed', value: 'completed' },
  { label: 'Failed', value: 'failed' },
  { label: 'Cancelled', value: 'cancelled' }
]

const jobs = ref([])
const loading = ref(false)
const error = ref(null)
const filterStatus = ref('')
const searchQuery = ref('')
const debouncedSearchQuery = ref('')
const showCreateModal = ref(false)
// Track newly completed jobs to highlight them
const recentlyCompleted = ref(new Set())
// Connection status tracking (Requirement 14.4)
const connectionWarning = ref(false)
const isOffline = ref(false)
// Pagination state (Requirement 15.1)
const currentPage = ref(1)
const pageSize = 50 // Show 50 jobs per page
let pollTimer = null
let searchDebounceTimer = null

// Debounce search input with 300ms delay (Requirement 15.4)
watch(searchQuery, (newValue) => {
  if (searchDebounceTimer) {
    clearTimeout(searchDebounceTimer)
  }
  searchDebounceTimer = setTimeout(() => {
    debouncedSearchQuery.value = newValue
  }, 300)
})

const jobStats = computed(() => {
  return {
    total: jobs.value.length,
    completed: jobs.value.filter(j => j.status === 'completed').length,
    running: jobs.value.filter(j => j.status === 'running').length,
    pending: jobs.value.filter(j => j.status === 'pending').length,
    failed: jobs.value.filter(j => j.status === 'failed').length
  }
})

const filteredJobs = computed(() => {
  let filtered = jobs.value

  // Apply status filter
  if (filterStatus.value) {
    filtered = filtered.filter(j => j.status === filterStatus.value)
  }

  // Apply search filter (debounced) - search by Job Name, Job Type, and Created Date
  if (debouncedSearchQuery.value) {
    const query = debouncedSearchQuery.value.toLowerCase()
    filtered = filtered.filter(j => {
      const jobName = (j.name || '').toLowerCase()
      const jobType = (j.type || 'general').toLowerCase()
      const jobId = j.id.toString()
      const createdDate = formatDate(j.created_at).toLowerCase()
      
      return jobName.includes(query) || 
             jobType.includes(query) || 
             jobId.includes(query) ||
             createdDate.includes(query)
    })
  }

  return filtered
})

// Computed property to check if search is active
const isSearchActive = computed(() => debouncedSearchQuery.value.length > 0)

// Computed property for matching jobs count
const matchingJobsCount = computed(() => filteredJobs.value.length)

// Pagination computed properties (Requirement 15.1)
const totalPages = computed(() => Math.ceil(filteredJobs.value.length / pageSize))

const paginatedJobs = computed(() => {
  const start = (currentPage.value - 1) * pageSize
  const end = start + pageSize
  return filteredJobs.value.slice(start, end)
})

const visiblePageNumbers = computed(() => {
  const total = totalPages.value
  const current = currentPage.value
  const pages = []
  
  // Show up to 5 page numbers
  let startPage = Math.max(1, current - 2)
  let endPage = Math.min(total, current + 2)
  
  // Adjust if we're near the beginning or end
  if (current <= 3) {
    endPage = Math.min(5, total)
  }
  if (current >= total - 2) {
    startPage = Math.max(1, total - 4)
  }
  
  for (let i = startPage; i <= endPage; i++) {
    pages.push(i)
  }
  
  return pages
})

// Reset to page 1 when filters change
watch([filterStatus, debouncedSearchQuery], () => {
  currentPage.value = 1
})

async function refreshJobs() {
  loading.value = true
  error.value = null
  connectionWarning.value = false

  try {
    // Requirement 15.2: Optimize initial render for up to 100 jobs
    // Fetch with pagination to improve performance
    const response = await api.get('/jobs', {
      params: { 
        sort_by: 'created_at', 
        sort_order: 'desc', 
        per_page: 100  // Limit to 100 jobs for performance
      }
    })
    const newJobs = response.data.data || response.data || []

    // Detect jobs that just transitioned to completed
    const prevStatuses = Object.fromEntries(jobs.value.map(j => [j.id, j.status]))
    newJobs.forEach(j => {
      if (j.status === 'completed' && prevStatuses[j.id] === 'running') {
        recentlyCompleted.value.add(j.id)
        // Remove highlight after 8 seconds
        setTimeout(() => {
          recentlyCompleted.value.delete(j.id)
          recentlyCompleted.value = new Set(recentlyCompleted.value)
        }, 8000)
      }
    })

    jobs.value = newJobs
    isOffline.value = false
  } catch (err) {
    // Requirement 14.1: Display user-friendly error messages
    if (err.code === 'ERR_NETWORK' || err.message === 'Network Error') {
      error.value = 'Unable to connect to the server. Please check your internet connection.'
      connectionWarning.value = true
      isOffline.value = true
    } else if (err.response?.status === 401) {
      error.value = 'Your session has expired. Please log in again.'
    } else if (err.response?.status === 403) {
      error.value = 'You do not have permission to view jobs.'
    } else if (err.response?.status === 500) {
      error.value = 'Server error occurred. Please try again later.'
    } else if (err.response?.status === 503) {
      error.value = 'Service temporarily unavailable. Please try again in a few moments.'
    } else {
      error.value = err.response?.data?.message || 'Failed to load jobs. Please try again.'
    }
  } finally {
    loading.value = false
  }
}

// Auto-poll every 3s when any job is running, 15s otherwise
function scheduleNextPoll() {
  const hasActive = jobs.value.some(j => j.status === 'running' || j.status === 'pending')
  const interval = hasActive ? 3000 : 15000
  pollTimer = setTimeout(async () => {
    await refreshJobs()
    scheduleNextPoll()
  }, interval)
}

onUnmounted(() => {
  if (pollTimer) clearTimeout(pollTimer)
  if (searchDebounceTimer) clearTimeout(searchDebounceTimer)
})

function handleJobCreated(newJob) {
  showCreateModal.value = false
  // Navigate to the new job's detail page if we have an ID
  if (newJob?.id) {
    router.push(`/jobs/${newJob.id}`)
  } else {
    refreshJobs()
  }
}

function viewJob(job) {
  router.push(`/jobs/${job.id}`)
}

async function retryJob(job) {
  if (confirm(`Retry "${job.name}"?`)) {
    try {
      const response = await api.post(`/jobs/${job.id}/retry`)
      toastStore.success(response.data.message || 'Job retry initiated successfully')
      await refreshJobs()
    } catch (err) {
      // Requirement 14.3: Show error notification with failure reason
      let errorMessage = 'Failed to retry job'
      
      if (err.code === 'ERR_NETWORK' || err.message === 'Network Error') {
        errorMessage = 'Unable to retry job: Network connection lost'
      } else if (err.response?.status === 404) {
        errorMessage = 'Job not found. It may have been deleted.'
      } else if (err.response?.status === 403) {
        errorMessage = 'You do not have permission to retry this job.'
      } else if (err.response?.status === 409) {
        errorMessage = err.response?.data?.message || 'Job cannot be retried in its current state.'
      } else if (err.response?.data?.message) {
        errorMessage = `Failed to retry job: ${err.response.data.message}`
      }
      
      toastStore.error(errorMessage)
    }
  }
}

async function cancelJob(job) {
  if (confirm(`Are you sure you want to cancel "${job.name}"?`)) {
    try {
      const response = await api.delete(`/jobs/${job.id}`)
      toastStore.success(response.data.message || 'Job cancelled successfully')
      await refreshJobs()
    } catch (err) {
      // Requirement 14.3: Show error notification with failure reason
      let errorMessage = 'Failed to cancel job'
      
      if (err.code === 'ERR_NETWORK' || err.message === 'Network Error') {
        errorMessage = 'Unable to cancel job: Network connection lost'
      } else if (err.response?.status === 404) {
        errorMessage = 'Job not found. It may have already been cancelled or deleted.'
      } else if (err.response?.status === 403) {
        errorMessage = 'You do not have permission to cancel this job.'
      } else if (err.response?.status === 409) {
        errorMessage = err.response?.data?.message || 'Job cannot be cancelled in its current state.'
      } else if (err.response?.data?.message) {
        errorMessage = `Failed to cancel job: ${err.response.data.message}`
      }
      
      toastStore.error(errorMessage)
    }
  }
}

function getStatusClass(status) {
  const classes = {
    completed: 'bg-green-100 text-green-800',
    running: 'bg-blue-100 text-blue-800',
    failed: 'bg-red-100 text-red-800',
    pending: 'bg-yellow-100 text-yellow-800',
    cancelled: 'bg-gray-100 text-gray-800'
  }
  return classes[status] || classes.pending
}

function getProgressColor(status) {
  const colors = {
    completed: 'bg-green-500',
    running: 'bg-blue-500',
    failed: 'bg-red-500',
    pending: 'bg-yellow-500'
  }
  return colors[status] || colors.pending
}

function getProgress(job) {
  // Handle edge case: zero total tasks
  if (!job.total_tasks || job.total_tasks === 0) return 0
  
  // Ensure completed jobs always show 100%
  if (job.status === 'completed') return 100
  
  // Calculate progress: (completed / total) * 100
  return Math.round((job.completed_tasks / job.total_tasks) * 100)
}

function formatDate(dateString) {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now - date
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)
  
  if (diffMins < 1) return 'Just now'
  if (diffMins < 60) return `${diffMins}m ago`
  if (diffHours < 24) return `${diffHours}h ago`
  if (diffDays < 7) return `${diffDays}d ago`
  
  return date.toLocaleDateString()
}

onMounted(() => {
  refreshJobs().then(() => scheduleNextPoll())
})
</script>
