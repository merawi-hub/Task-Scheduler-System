<template>
  <div class="flex h-screen bg-gray-50">
    <UserSidebar />
    <div class="flex-1 ml-64 overflow-auto">

      <!-- Header -->
      <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="px-8 py-4 flex items-center justify-between">
          <div>
            <!-- Breadcrumb Navigation -->
            <nav class="flex items-center gap-1.5 text-sm mb-1">
              <router-link to="/my-jobs" class="text-gray-500 hover:text-indigo-600 transition-colors">Jobs</router-link>
              <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
              <span class="text-gray-900 font-medium truncate max-w-xs">{{ job ? job.name : 'Loading...' }}</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900">Job Monitoring</h1>
          </div>
          <div class="flex items-center gap-2">
            <!-- Back Button -->
            <button @click="$router.push('/my-jobs')" class="flex items-center gap-2 px-4 py-2 text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-100 transition-colors">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
              <span class="text-sm font-medium">Back to Jobs</span>
            </button>
            <!-- Manual Refresh Button (Requirement 12.3) -->
            <button 
              @click="manualRefresh" 
              class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors" 
              :class="{'animate-spin': loading}"
              :disabled="loading"
              title="Refresh data"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </button>
          </div>
        </div>
        
        <!-- Connection Warning Banner (Requirement 12.5) -->
        <div v-if="connectionWarning" class="px-8 py-3 bg-yellow-50 border-t border-yellow-200">
          <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="flex-1">
              <p class="text-sm font-medium text-yellow-800">Connection issue detected</p>
              <p class="text-xs text-yellow-700 mt-0.5">Unable to fetch updated data. Retrying in 10 seconds...</p>
            </div>
            <button 
              @click="manualRefresh" 
              class="px-3 py-1.5 text-xs font-medium text-yellow-700 hover:text-yellow-900 bg-yellow-100 hover:bg-yellow-200 rounded-lg transition-colors"
            >
              Retry Now
            </button>
          </div>
        </div>
      </header>

      <!-- Loading Skeleton (Requirement 13.4) -->
      <div v-if="loading && !job" class="p-8">
        <div class="max-w-7xl mx-auto space-y-6">
          <!-- Header skeleton -->
          <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 bg-gray-200 rounded-xl animate-pulse"></div>
            <div class="flex-1">
              <div class="h-6 bg-gray-200 rounded w-48 mb-2 animate-pulse"></div>
              <div class="h-4 bg-gray-200 rounded w-24 animate-pulse"></div>
            </div>
          </div>

          <!-- Overview card skeleton -->
          <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <div class="h-4 bg-gray-200 rounded w-32 mb-5 animate-pulse"></div>
            <div class="grid grid-cols-4 gap-6">
              <div v-for="i in 4" :key="i">
                <div class="h-3 bg-gray-200 rounded w-20 mb-2 animate-pulse"></div>
                <div class="h-5 bg-gray-200 rounded w-32 animate-pulse"></div>
              </div>
            </div>
          </div>

          <!-- Progress skeleton -->
          <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl border border-indigo-100 shadow-sm p-8">
            <div class="flex items-center justify-center">
              <div class="w-64 h-64 bg-gray-200 rounded-full animate-pulse"></div>
            </div>
          </div>

          <!-- Cards skeleton -->
          <div class="grid grid-cols-2 gap-6">
            <div v-for="i in 2" :key="i" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
              <div class="h-4 bg-gray-200 rounded w-32 mb-5 animate-pulse"></div>
              <div class="space-y-4">
                <div v-for="j in 4" :key="j" class="flex justify-between">
                  <div class="h-3 bg-gray-200 rounded w-24 animate-pulse"></div>
                  <div class="h-3 bg-gray-200 rounded w-32 animate-pulse"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Error State with Back Button -->
      <div v-else-if="pageError && !job" class="p-8">
        <div class="max-w-2xl mx-auto">
          <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8 text-center">
            <!-- Error Icon -->
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            
            <!-- Error Message -->
            <h2 class="text-xl font-bold text-gray-900 mb-2">Unable to Load Job</h2>
            <p class="text-gray-600 mb-6">{{ pageError }}</p>
            
            <!-- Action Buttons -->
            <div class="flex items-center justify-center gap-3">
              <!-- Retry Button (Requirement 14.5) -->
              <button 
                @click="manualRefresh" 
                class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium"
                :disabled="loading"
              >
                <svg class="w-5 h-5" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                {{ loading ? 'Retrying...' : 'Retry' }}
              </button>
              
              <!-- Back Button -->
              <button 
                @click="$router.push('/my-jobs')" 
                class="inline-flex items-center gap-2 px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Jobs Page
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Content -->
      <main v-else-if="job" class="p-8">

        <!-- Job title row -->
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
              <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div>
              <h2 class="text-xl font-bold text-gray-900">{{ job.name }}</h2>
              <div class="flex items-center gap-2 mt-1">
                <span class="w-2 h-2 rounded-full" :class="dotClass(job.status)"></span>
                <span :class="textClass(job.status)" class="text-sm font-medium capitalize">{{ job.status }}</span>
              </div>
            </div>
          </div>
          <div class="relative">
            <button @click="actionsOpen = !actionsOpen" class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
              Actions <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div v-if="actionsOpen" class="absolute right-0 mt-1 w-44 bg-white border border-gray-200 rounded-xl shadow-lg z-20 py-1">
              <button @click="manualRefresh(); actionsOpen=false" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Refresh
              </button>
              <button v-if="['pending','running'].includes(job.status)" @click="doCancel(); actionsOpen=false" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Cancel Job
              </button>
            </div>
          </div>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-6">
          <nav class="flex gap-6">
            <button v-for="t in tabs" :key="t.id" @click="tab = t.id"
              :class="['pb-3 text-sm font-medium border-b-2 transition-colors',
                tab === t.id ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700']">
              {{ t.id === 'tasks' ? 'Tasks (' + taskList.length + ')' : t.label }}
            </button>
          </nav>
        </div>

        <!-- OVERVIEW -->
        <div v-show="tab === 'overview'" class="space-y-6">
          <!-- Job Overview Card (Requirement 7) -->
          <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-sm font-bold text-gray-900 mb-5">Job Overview</h3>
            <div class="grid grid-cols-4 gap-6">
              <!-- Job Name -->
              <div>
                <dt class="text-sm text-gray-500 mb-2">Job Name</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ job.name }}</dd>
              </div>
              
              <!-- Status with color-coded badge (Requirement 7.5) -->
              <!-- Pulsing indicator while job is running (Requirement 13.3) -->
              <div>
                <dt class="text-sm text-gray-500 mb-2">Status</dt>
                <dd>
                  <span :class="statusBadgeClass(job.status)" class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-bold uppercase tracking-wide">
                    <span 
                      :class="statusDotClass(job.status)" 
                      class="w-2 h-2 rounded-full mr-2"
                      :style="job.status === 'running' ? 'animation: pulse-dot 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;' : ''"
                    ></span>
                    {{ job.status }}
                  </span>
                </dd>
              </div>
              
              <!-- Started Time (Requirement 7.2) -->
              <div>
                <dt class="text-sm text-gray-500 mb-2">Started Time</dt>
                <dd class="text-lg font-medium text-gray-900">
                  {{ job.started_at ? fmtDate(job.started_at) : 'Not Started' }}
                </dd>
              </div>
              
              <!-- Estimated Completion (Requirement 7.3) -->
              <div>
                <dt class="text-sm text-gray-500 mb-2">Estimated Completion</dt>
                <dd class="text-lg font-medium text-gray-900">{{ estCompletion }}</dd>
              </div>
            </div>
          </div>

          <!-- Large Animated Progress Visualization (Requirement 8 - Task 7) -->
          <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl border border-indigo-100 shadow-sm p-8">
            <div class="flex items-center justify-center">
              <div class="relative">
                <!-- Large Circular Progress Indicator (Requirement 8.1) -->
                <svg class="w-64 h-64 -rotate-90" viewBox="0 0 200 200">
                  <!-- Background circle -->
                  <circle 
                    cx="100" 
                    cy="100" 
                    r="85" 
                    fill="none" 
                    stroke="#e0e7ff" 
                    stroke-width="12"
                  />
                  <!-- Animated progress circle (Requirement 8.2) -->
                  <circle 
                    cx="100" 
                    cy="100" 
                    r="85" 
                    fill="none" 
                    :stroke="progressColor" 
                    stroke-width="12" 
                    stroke-linecap="round"
                    :stroke-dasharray="progressDashArray"
                    class="transition-all duration-700 ease-out"
                    :class="{ 'animate-pulse-slow': pct === 100 }"
                  />
                </svg>
                
                <!-- Center content with numeric percentage (Requirement 8.3) -->
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                  <!-- Completion animation (Requirement 8.4) -->
                  <div v-if="pct === 100" class="absolute inset-0 flex items-center justify-center">
                    <div class="w-20 h-20 bg-green-500 rounded-full animate-ping opacity-20"></div>
                  </div>
                  
                  <div class="relative z-10 text-center">
                    <div class="text-6xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent mb-2 transition-all duration-700">
                      {{ pct }}%
                    </div>
                    <div class="text-sm font-medium text-gray-600 uppercase tracking-wide">
                      {{ pct === 100 ? 'Completed' : 'In Progress' }}
                    </div>
                    <div class="mt-3 text-2xl font-bold text-gray-900">
                      {{ doneCount }} / {{ job.total_tasks }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">tasks completed</div>
                  </div>
                  
                  <!-- Completion checkmark animation (Requirement 8.4) -->
                  <div v-if="pct === 100" class="absolute inset-0 flex items-center justify-center animate-scale-in">
                    <svg class="w-24 h-24 text-green-500 opacity-20" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Progress bar alternative visualization -->
            <div class="mt-8 max-w-2xl mx-auto">
              <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700">Overall Progress</span>
                <span class="text-sm font-bold text-indigo-600">{{ pct }}%</span>
              </div>
              <!-- Animated horizontal progress bar (Requirement 8.2) -->
              <div class="h-4 bg-white rounded-full overflow-hidden shadow-inner">
                <div 
                  class="h-full rounded-full transition-all duration-700 ease-out"
                  :class="progressBarClass"
                  :style="{ width: pct + '%' }"
                >
                  <div class="h-full w-full bg-gradient-to-r from-transparent to-white opacity-30 animate-shimmer"></div>
                </div>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-6">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
              <h3 class="text-sm font-bold text-gray-900 mb-5">Job Information</h3>
              <dl class="space-y-4">
                <div class="flex justify-between"><dt class="text-sm text-gray-500">Job ID</dt><dd class="text-sm font-semibold text-indigo-600">#JOB-{{ String(job.id).padStart(3,'0') }}</dd></div>
                <div class="flex justify-between border-t border-gray-50 pt-4"><dt class="text-sm text-gray-500">Created At</dt><dd class="text-sm font-medium text-gray-900">{{ fmtDate(job.created_at) }}</dd></div>
                <div class="flex justify-between border-t border-gray-50 pt-4"><dt class="text-sm text-gray-500">Created By</dt><dd class="text-sm font-medium text-gray-900">{{ job.user ? job.user.email : (job.submitted_by || 'You') }}</dd></div>
                <div class="flex justify-between border-t border-gray-50 pt-4"><dt class="text-sm text-gray-500">Priority</dt><dd :class="prioClass(job.priority)" class="text-sm font-bold">{{ prioLabel(job.priority) }}</dd></div>
              </dl>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
              <h3 class="text-sm font-bold text-gray-900 mb-5">Timing Information</h3>
              <dl class="space-y-4">
                <div class="flex justify-between"><dt class="text-sm text-gray-500">Estimated Completion</dt><dd class="text-sm font-medium text-gray-900">{{ estCompletion }}</dd></div>
                <div class="flex justify-between border-t border-gray-50 pt-4"><dt class="text-sm text-gray-500">Duration</dt><dd class="text-sm font-medium text-gray-900">{{ jobDuration }}</dd></div>
                <div class="flex justify-between border-t border-gray-50 pt-4"><dt class="text-sm text-gray-500">Started At</dt><dd class="text-sm font-medium text-gray-900">{{ job.started_at ? fmtDate(job.started_at) : 'Not Started' }}</dd></div>
                <div class="flex justify-between border-t border-gray-50 pt-4"><dt class="text-sm text-gray-500">Last Updated</dt><dd class="text-sm font-medium text-gray-900">{{ fmtTime(new Date()) }}</dd></div>
              </dl>
            </div>
          </div>
          <!-- Task Status Grid (Requirement 9 - Task 8) -->
          <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-sm font-bold text-gray-900 mb-5">Task Status Grid</h3>
            <div class="grid grid-cols-5 gap-6">
              <!-- Pending (Requirement 9.1) -->
              <div class="text-center transition-all duration-300" :class="{ 'highlight-pulse': isHighlighted('pending') }">
                <p class="text-3xl font-bold text-gray-600 mb-2 transition-all duration-500">{{ stats.pending }}</p>
                <div class="w-full h-2 rounded-full bg-gray-400 mb-2"></div>
                <p class="text-sm font-medium text-gray-700">Pending</p>
              </div>
              
              <!-- Running (Requirement 9.1) -->
              <div class="text-center transition-all duration-300" :class="{ 'highlight-pulse': isHighlighted('running') }">
                <p class="text-3xl font-bold text-blue-600 mb-2 transition-all duration-500">{{ stats.running }}</p>
                <div class="w-full h-2 rounded-full bg-blue-500 mb-2"></div>
                <p class="text-sm font-medium text-gray-700">Running</p>
              </div>
              
              <!-- Completed (Requirement 9.1) -->
              <div class="text-center transition-all duration-300" :class="{ 'highlight-pulse': isHighlighted('done') }">
                <p class="text-3xl font-bold text-green-600 mb-2 transition-all duration-500">{{ stats.done }}</p>
                <div class="w-full h-2 rounded-full bg-green-500 mb-2"></div>
                <p class="text-sm font-medium text-gray-700">Completed</p>
              </div>
              
              <!-- Failed (Requirement 9.1) -->
              <div class="text-center transition-all duration-300" :class="{ 'highlight-pulse': isHighlighted('failed') }">
                <p class="text-3xl font-bold text-red-600 mb-2 transition-all duration-500">{{ stats.failed }}</p>
                <div class="w-full h-2 rounded-full bg-red-500 mb-2"></div>
                <p class="text-sm font-medium text-gray-700">Failed</p>
              </div>
              
              <!-- Retried (Requirement 9.1) -->
              <div class="text-center transition-all duration-300" :class="{ 'highlight-pulse': isHighlighted('retried') }">
                <p class="text-3xl font-bold text-orange-600 mb-2 transition-all duration-500">{{ stats.retried }}</p>
                <div class="w-full h-2 rounded-full bg-orange-500 mb-2"></div>
                <p class="text-sm font-medium text-gray-700">Retried</p>
              </div>
            </div>
          </div>

          <!-- Worker Assignment Display (Requirement 10 - Task 9) -->
          <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-sm font-bold text-gray-900 mb-5">Worker Assignments</h3>
            
            <!-- Active assignments (Requirement 10.3) -->
            <div v-if="workerAssignments.length > 0" class="space-y-3">
              <div 
                v-for="assignment in workerAssignments" 
                :key="assignment.taskId"
                class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-100 hover:shadow-md transition-shadow"
              >
                <div class="flex items-center gap-3">
                  <!-- Worker icon -->
                  <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                    </svg>
                  </div>
                  
                  <!-- Assignment text (Requirement 10.3: "Worker-{ID} → Task {Task_Number}") -->
                  <div>
                    <p class="text-sm font-bold text-gray-900">
                      <span class="text-blue-600">{{ assignment.workerKey }}</span>
                      <span class="mx-2 text-gray-400">→</span>
                      <span class="text-indigo-600">Task {{ assignment.taskNumber }}</span>
                    </p>
                    <p class="text-xs text-gray-500 mt-0.5">
                      {{ assignment.status === 'running' ? 'Currently processing' : 'Assigned' }}
                      <span v-if="assignment.hostname" class="ml-2">• {{ assignment.hostname }}</span>
                    </p>
                  </div>
                </div>
                
                <!-- Status indicator -->
                <div class="flex items-center gap-2">
                  <span 
                    :class="assignment.status === 'running' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700'"
                    class="px-2.5 py-1 text-xs font-bold rounded-full uppercase tracking-wide"
                  >
                    {{ assignment.status }}
                  </span>
                  <div 
                    v-if="assignment.status === 'running'"
                    class="w-2 h-2 bg-orange-500 rounded-full animate-pulse"
                  ></div>
                </div>
              </div>
            </div>
            
            <!-- No active assignments message (Requirement 10.5) -->
            <div v-else class="text-center py-8">
              <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
              </div>
              <p class="text-sm font-medium text-gray-500">No active assignments</p>
              <p class="text-xs text-gray-400 mt-1">Workers will appear here when tasks are assigned</p>
            </div>
          </div>

          <!-- Real-Time Activity Feed (Requirement 11 - Task 11) -->
          <ActivityFeed :tasks="taskList" />
        </div>

        <!-- TASKS -->
        <div v-show="tab === 'tasks'">
          <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <!-- Toolbar -->
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
              <div class="relative flex-1 max-w-xs">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input v-model="search" type="text" placeholder="Search tasks..." class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
              </div>
              <span class="text-sm text-gray-500">Filter:</span>
              <select v-model="statusFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="assigned">Assigned</option>
                <option value="running">Running</option>
                <option value="done">Done</option>
                <option value="failed">Failed</option>
                <option value="cancelled">Cancelled</option>
              </select>
              <span class="text-sm text-gray-500 ml-auto">{{ visibleTasks.length }} of {{ taskList.length }} tasks</span>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Task ID</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Task Name</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Job Name</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Worker</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Duration</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Started At</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Retries</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <template v-if="pagedTasks.length > 0">
                    <tr v-for="task in pagedTasks" :key="task.id" class="hover:bg-gray-50 transition-colors">
                      <td class="px-6 py-4 text-sm font-mono text-indigo-600 font-semibold">#TASK-{{ String(task.task_index + 1).padStart(3,'0') }}</td>
                      <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        {{ buildTaskName(task) }}
                        <span v-if="task.failure_reason" class="block text-xs text-red-500 mt-0.5 truncate max-w-xs">{{ task.failure_reason }}</span>
                      </td>
                      <td class="px-6 py-4 text-sm text-gray-600">{{ job.name }}</td>
                      <td class="px-6 py-4">
                        <span :class="badgeClass(task.status)" class="px-2.5 py-1 text-xs font-bold rounded-full uppercase tracking-wide">
                          {{ task.status === 'done' ? 'COMPLETED' : task.status.toUpperCase() }}
                        </span>
                      </td>
                      <td class="px-6 py-4 text-sm text-gray-600 font-mono">{{ task.worker ? task.worker.worker_key : '—' }}</td>
                      <td class="px-6 py-4 text-sm text-gray-600">{{ taskDur(task) }}</td>
                      <td class="px-6 py-4 text-sm text-gray-600">{{ task.started_at ? fmtTime(task.started_at) : '—' }}</td>
                      <td class="px-6 py-4 text-sm text-gray-600">{{ task.retry_count }}/{{ task.max_retries }}</td>
                    </tr>
                  </template>
                  <tr v-else>
                    <td colspan="8" class="px-6 py-12 text-center">
                      <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                      <p class="text-sm font-medium text-gray-500">No tasks found</p>
                      <p v-if="statusFilter || search" class="text-xs text-gray-400 mt-1">Try clearing the filters</p>
                      <p v-else class="text-xs text-gray-400 mt-1">Tasks will appear here once the job starts processing</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div v-if="totalPages > 1" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
              <p class="text-sm text-gray-500">
                Showing {{ (page - 1) * perPage + 1 }}–{{ Math.min(page * perPage, visibleTasks.length) }} of {{ visibleTasks.length }}
              </p>
              <div class="flex items-center gap-1">
                <button @click="page--" :disabled="page === 1" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button v-for="p in pageNums" :key="p" @click="page = p"
                  :class="['w-8 h-8 flex items-center justify-center rounded-lg text-sm font-medium transition-colors',
                    p === page ? 'bg-indigo-600 text-white' : 'border border-gray-300 text-gray-600 hover:bg-gray-50']">
                  {{ p }}
                </button>
                <button @click="page++" :disabled="page === totalPages" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- LOGS -->
        <div v-show="tab === 'logs'">
          <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100"><h3 class="text-sm font-semibold text-gray-900">Error Logs</h3></div>
            <div class="divide-y divide-gray-100">
              <div v-for="t in taskList.filter(x => x.failure_reason)" :key="'log-'+t.id" class="px-6 py-4">
                <div class="flex items-start gap-3">
                  <span class="w-2 h-2 rounded-full bg-red-500 mt-1.5 flex-shrink-0"></span>
                  <div>
                    <p class="text-sm font-medium text-gray-900">Task #{{ t.task_index + 1 }} — {{ t.failure_reason }}</p>
                    <p class="text-xs text-gray-400 mt-1">Retries: {{ t.retry_count }}/{{ t.max_retries }}</p>
                  </div>
                </div>
              </div>
              <div v-if="!taskList.some(x => x.failure_reason)" class="px-6 py-8 text-center text-sm text-gray-400">No error logs</div>
            </div>
          </div>
        </div>

        <!-- METRICS -->
        <div v-show="tab === 'metrics'">
          <div class="grid grid-cols-3 gap-6">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 text-center"><p class="text-3xl font-bold text-indigo-600">{{ pct }}%</p><p class="text-sm text-gray-500 mt-1">Completion Rate</p></div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 text-center"><p class="text-3xl font-bold text-green-600">{{ stats.done }}</p><p class="text-sm text-gray-500 mt-1">Tasks Completed</p></div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 text-center"><p class="text-3xl font-bold text-red-600">{{ stats.failed }}</p><p class="text-sm text-gray-500 mt-1">Tasks Failed</p></div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 text-center"><p class="text-3xl font-bold text-yellow-600">{{ stats.running }}</p><p class="text-sm text-gray-500 mt-1">Currently Running</p></div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 text-center"><p class="text-3xl font-bold text-gray-600">{{ stats.pending }}</p><p class="text-sm text-gray-500 mt-1">Pending</p></div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 text-center"><p class="text-3xl font-bold text-gray-900">{{ jobDuration }}</p><p class="text-sm text-gray-500 mt-1">Total Duration</p></div>
          </div>
        </div>

      </main>
    </div>
  </div>
</template>
<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import UserSidebar from '@/components/UserSidebar.vue'
import ActivityFeed from '@/components/ActivityFeed.vue'
import api from '@/api'

const route = useRoute()

// ── plain reactive state (no computed chains that could break) ─────────────────
const job       = ref(null)
const taskList  = ref([])    // raw array from API — populated in loadData()
const loading   = ref(true)
const pageError = ref('')
const actionsOpen = ref(false)
const tab       = ref('overview')
const search    = ref('')
const statusFilter = ref('')
const page      = ref(1)
const perPage   = 15

// Polling and connection state (Requirement 12)
let timer       = null
let retryTimer  = null
let abortController = null
const connectionWarning = ref(false)
const pollingFailed = ref(false)

// Track previous values for highlight animations (Requirement 13.5)
const previousValues = ref({
  progress: 0,
  pending: 0,
  running: 0,
  done: 0,
  failed: 0,
  retried: 0,
})
const highlightedFields = ref(new Set())

const tabs = [
  { id: 'overview', label: 'Overview' },
  { id: 'tasks',    label: 'Tasks' },
  { id: 'logs',     label: 'Logs' },
  { id: 'metrics',  label: 'Metrics' },
]

// ── derived values ─────────────────────────────────────────────────────────────

const stats = computed(() => {
  const s = { pending: 0, assigned: 0, running: 0, done: 0, failed: 0, cancelled: 0, retried: 0 }
  for (const t of taskList.value) {
    if (t.status in s) s[t.status]++
    // Count tasks that have been retried (retry_count > 0) - Requirement 9.1
    if (t.retry_count && t.retry_count > 0) s.retried++
  }
  return s
})

const doneCount = computed(() => stats.value.done)

const pct = computed(() => {
  if (!job.value || !job.value.total_tasks) return 0
  const done = taskList.value.length > 0 ? doneCount.value : (job.value.completed_tasks || 0)
  return Math.round((done / job.value.total_tasks) * 100)
})

// Worker Assignments computed property (Requirement 10 - Task 9)
// Extracts active worker-to-task mappings from taskList
const workerAssignments = computed(() => {
  // Filter tasks that have workers assigned (assigned or running status)
  // Requirement 10.1: Show active worker-to-task mappings
  const activeTasks = taskList.value.filter(t => 
    t.worker && (t.status === 'assigned' || t.status === 'running')
  )
  
  // Map to display format (Requirement 10.3)
  return activeTasks.map(t => ({
    taskId: t.id,
    taskNumber: t.task_index + 1,  // Task number (1-indexed)
    workerKey: t.worker.worker_key, // Worker-{ID}
    hostname: t.worker.hostname,
    status: t.status,
  }))
})

// visibleTasks = taskList filtered by statusFilter + search
const visibleTasks = computed(() => {
  const src = taskList.value   // direct ref — no intermediate variable
  if (!statusFilter.value && !search.value.trim()) return src

  const sf = statusFilter.value
  const sq = search.value.trim().toLowerCase()

  return src.filter(t => {
    if (sf && t.status !== sf) return false
    if (sq) {
      const haystack = [
        String(t.task_index + 1),
        String(t.id),
        t.status || '',
        t.worker?.worker_key || '',
        t.failure_reason || '',
      ].join(' ').toLowerCase()
      if (!haystack.includes(sq)) return false
    }
    return true
  })
})

const totalPages = computed(() => Math.max(1, Math.ceil(visibleTasks.value.length / perPage)))

const pagedTasks = computed(() => {
  const start = (page.value - 1) * perPage
  return visibleTasks.value.slice(start, start + perPage)
})

const pageNums = computed(() => {
  const total = totalPages.value
  const cur   = page.value
  const nums  = []
  for (let i = Math.max(1, cur - 2); i <= Math.min(total, cur + 2); i++) nums.push(i)
  return nums
})

const estCompletion = computed(() => {
  if (!job.value) return '—'
  if (job.value.status === 'completed') return job.value.completed_at ? fmtDate(job.value.completed_at) : '—'
  if (!job.value.started_at || !job.value.completed_tasks) return 'Calculating...'
  const elapsed = Date.now() - new Date(job.value.started_at).getTime()
  const avg = elapsed / job.value.completed_tasks
  const rem = (job.value.total_tasks - job.value.completed_tasks) * avg
  return new Date(Date.now() + rem).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
})

const jobDuration = computed(() => {
  if (!job.value?.started_at) return '—'
  const end = job.value.completed_at ? new Date(job.value.completed_at) : new Date()
  const ms  = end - new Date(job.value.started_at)
  const h = Math.floor(ms / 3600000)
  const m = Math.floor((ms % 3600000) / 60000)
  const s = Math.floor((ms % 60000) / 1000)
  return h > 0 ? `${h}h ${m}m ${s}s` : m > 0 ? `${m}m ${s}s` : `${s}s`
})

// Progress visualization computed properties (Requirement 8)
const progressDashArray = computed(() => {
  // Circle circumference = 2 * π * r = 2 * π * 85 ≈ 534.07
  const circumference = 534.07
  const progress = (pct.value / 100) * circumference
  return `${progress} ${circumference}`
})

const progressColor = computed(() => {
  if (pct.value === 100) return '#10b981' // green-500
  if (pct.value >= 75) return '#8b5cf6'   // purple-500
  if (pct.value >= 50) return '#6366f1'   // indigo-500
  if (pct.value >= 25) return '#3b82f6'   // blue-500
  return '#60a5fa'                         // blue-400
})

const progressBarClass = computed(() => {
  if (pct.value === 100) return 'bg-gradient-to-r from-green-500 to-emerald-500'
  if (pct.value >= 75) return 'bg-gradient-to-r from-purple-500 to-indigo-500'
  if (pct.value >= 50) return 'bg-gradient-to-r from-indigo-500 to-blue-500'
  if (pct.value >= 25) return 'bg-gradient-to-r from-blue-500 to-cyan-500'
  return 'bg-gradient-to-r from-blue-400 to-blue-500'
})

// reset page when filters change
watch([statusFilter, search], () => { page.value = 1 })

// Watch for value changes and trigger highlight animations (Requirement 13.5)
watch([pct, stats], ([newPct, newStats], [oldPct, oldStats]) => {
  // Check progress change
  if (oldPct !== undefined && newPct !== oldPct) {
    highlightedFields.value.add('progress')
    setTimeout(() => highlightedFields.value.delete('progress'), 2000)
  }
  
  // Check stats changes
  if (oldStats) {
    const fields = ['pending', 'running', 'done', 'failed', 'retried']
    fields.forEach(field => {
      if (newStats[field] !== oldStats[field]) {
        highlightedFields.value.add(field)
        setTimeout(() => highlightedFields.value.delete(field), 2000)
      }
    })
  }
}, { deep: true })

// ── helpers ────────────────────────────────────────────────────────────────────

function fmtDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}
function fmtTime(d) {
  if (!d) return '—'
  return new Date(d).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
}
function taskDur(t) {
  if (!t.started_at || !t.completed_at) return '—'
  const ms = new Date(t.completed_at) - new Date(t.started_at)
  const s  = Math.floor(ms / 1000)
  return s < 60 ? `${s}s` : `${Math.floor(s / 60)}m ${s % 60}s`
}
function buildTaskName(t) {
  const type = (job.value?.type || 'task').replace(/_/g, ' ')
  return `Process ${type} ${t.task_index + 1}`
}
function dotClass(s)  { return { pending:'bg-yellow-400', running:'bg-blue-500', completed:'bg-green-500', failed:'bg-red-500', cancelled:'bg-gray-400' }[s] || 'bg-gray-400' }
function textClass(s) { return { pending:'text-yellow-600', running:'text-blue-600', completed:'text-green-600', failed:'text-red-600', cancelled:'text-gray-500' }[s] || 'text-gray-600' }
function badgeClass(s){ return { pending:'bg-yellow-100 text-yellow-700', assigned:'bg-blue-100 text-blue-700', running:'bg-orange-100 text-orange-700', done:'bg-green-100 text-green-700', failed:'bg-red-100 text-red-700', cancelled:'bg-gray-100 text-gray-600' }[s] || 'bg-gray-100 text-gray-600' }
function prioLabel(p) { return { 3:'Low', 5:'Normal', 7:'High', 10:'Urgent' }[p] || 'Normal' }
function prioClass(p) { return { 3:'text-green-600', 5:'text-blue-600', 7:'text-yellow-600', 10:'text-red-600' }[p] || 'text-blue-600' }

// Status badge styling for Job Overview Card (Requirement 7.5)
function statusBadgeClass(s) {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-700',
    running: 'bg-blue-100 text-blue-700',
    completed: 'bg-green-100 text-green-700',
    failed: 'bg-red-100 text-red-700',
    cancelled: 'bg-gray-100 text-gray-600'
  }
  return classes[s] || 'bg-gray-100 text-gray-600'
}

function statusDotClass(s) {
  const classes = {
    pending: 'bg-yellow-500',
    running: 'bg-blue-500',
    completed: 'bg-green-500',
    failed: 'bg-red-500',
    cancelled: 'bg-gray-400'
  }
  return classes[s] || 'bg-gray-400'
}

// Check if a field is highlighted (Requirement 13.5)
function isHighlighted(field) {
  return highlightedFields.value.has(field)
}

// ── data loading ───────────────────────────────────────────────────────────────

async function loadData() {
  const id = route.params.id
  
  // Guard: if no ID in route, show error
  if (!id) {
    pageError.value = 'No job ID provided in the URL. Please select a job from the Jobs Page.'
    loading.value = false
    return
  }
  
  // Validate that ID is a number
  if (isNaN(Number(id))) {
    pageError.value = `Invalid job ID "${id}". Job IDs must be numeric.`
    loading.value = false
    return
  }
  
  // Cancel any pending requests (Requirement 15.5 & 12.4)
  if (abortController) {
    abortController.abort()
  }
  abortController = new AbortController()
  
  try {
    // Requirement 15.3: Optimize initial render to complete within 1 second
    // Fetch job and tasks in parallel for faster initial load
    const [jr, tr] = await Promise.all([
      api.get(`/jobs/${id}`, { signal: abortController.signal }),
      api.get(`/jobs/${id}/tasks`, { signal: abortController.signal }),
    ])

    // job detail: { job: {...}, progress, pending_tasks }
    job.value = jr.data.job || jr.data

    // tasks: { job_id, tasks: [...] }
    const raw = tr.data.tasks
    // Force a brand-new array so Vue's reactivity always triggers
    taskList.value = Array.isArray(raw) ? raw.slice() : []

    pageError.value = ''
    
    // Clear connection warning on successful load (Requirement 12.5)
    connectionWarning.value = false
    pollingFailed.value = false
    
    // Stop polling if job reached terminal state (Requirement 12.2)
    if (job.value && ['completed', 'failed', 'cancelled'].includes(job.value.status)) {
      stopPolling()
    }
  } catch (e) {
    // Ignore abort errors (user navigated away)
    if (e.name === 'AbortError' || e.code === 'ERR_CANCELED') {
      return
    }
    
    // Handle specific error cases (Requirement 14.2)
    if (e.response?.status === 404) {
      pageError.value = `Job #${id} does not exist or you don't have permission to view it.`
    } else if (e.response?.status === 403) {
      pageError.value = `You don't have permission to access Job #${id}.`
    } else if (e.response?.status === 401) {
      pageError.value = 'Your session has expired. Please log in again.'
    } else if (e.code === 'ERR_NETWORK' || e.message === 'Network Error') {
      // Network error - show connection warning (Requirement 14.4)
      if (!job.value) {
        // Initial load failed
        pageError.value = 'Unable to connect to the server. Please check your internet connection and try again.'
      } else {
        // Polling failed - show connection warning and retry (Requirement 12.5)
        connectionWarning.value = true
        pollingFailed.value = true
        scheduleRetry()
      }
    } else if (e.response?.status === 500) {
      pageError.value = 'A server error occurred while loading job details. Please try again later.'
    } else if (e.response?.status === 503) {
      pageError.value = 'The service is temporarily unavailable. Please try again in a few moments.'
    } else {
      // Network or other error - show connection warning (Requirement 12.5)
      if (!job.value) {
        // Initial load failed
        pageError.value = e.response?.data?.message || `Failed to load job details for Job #${id}. Please try again.`
      } else {
        // Polling failed - show connection warning and retry (Requirement 12.5)
        connectionWarning.value = true
        pollingFailed.value = true
        scheduleRetry()
      }
    }
  } finally {
    loading.value = false
  }
}

// Stop polling (Requirement 12.2)
function stopPolling() {
  if (timer) {
    clearInterval(timer)
    timer = null
  }
  if (retryTimer) {
    clearTimeout(retryTimer)
    retryTimer = null
  }
}

// Start polling (Requirement 12.1)
function startPolling() {
  // Clear any existing timers
  stopPolling()
  
  // Poll every 5 seconds while job is running (Requirement 12.1)
  timer = setInterval(() => {
    if (job.value && ['running', 'pending'].includes(job.value.status)) {
      loadData()
    } else if (job.value && ['completed', 'failed', 'cancelled'].includes(job.value.status)) {
      // Stop polling when job reaches terminal state (Requirement 12.2)
      stopPolling()
    }
  }, 5000)
}

// Schedule retry after polling failure (Requirement 12.5)
function scheduleRetry() {
  // Clear any existing retry timer
  if (retryTimer) {
    clearTimeout(retryTimer)
  }
  
  // Retry after 10 seconds (Requirement 12.5)
  retryTimer = setTimeout(() => {
    if (job.value && ['running', 'pending'].includes(job.value.status)) {
      loadData()
    }
  }, 10000)
}

// Manual refresh (Requirement 12.3)
async function manualRefresh() {
  loading.value = true
  await loadData()
}

async function doCancel() {
  if (!confirm('Cancel this job? Running tasks will be stopped.')) return
  try {
    await api.delete(`/jobs/${route.params.id}`)
    await loadData()
  } catch (e) {
    // Requirement 14.3: Show error notification with failure reason
    let errorMessage = 'Failed to cancel job'
    
    if (e.code === 'ERR_NETWORK' || e.message === 'Network Error') {
      errorMessage = 'Unable to cancel job: Network connection lost'
    } else if (e.response?.status === 404) {
      errorMessage = 'Job not found. It may have already been cancelled or deleted.'
    } else if (e.response?.status === 403) {
      errorMessage = 'You do not have permission to cancel this job.'
    } else if (e.response?.status === 409) {
      errorMessage = e.response?.data?.message || 'Job cannot be cancelled in its current state.'
    } else if (e.response?.data?.message) {
      errorMessage = `Failed to cancel job: ${e.response.data.message}`
    }
    
    alert(errorMessage)
  }
}

onMounted(() => {
  loadData()
  startPolling()
})

onUnmounted(() => {
  // Cancel pending polling requests when user navigates away (Requirement 12.4)
  stopPolling()
  if (abortController) {
    abortController.abort()
  }
})
</script>

<style scoped>
/* Animated progress visualization styles (Requirement 8.2, 8.4) */

/* Pulsing dot animation for running status (Requirement 13.3) */
@keyframes pulse-dot {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.5;
    transform: scale(1.2);
  }
}

/* Slow pulse animation for completion (Requirement 8.4) */
@keyframes pulse-slow {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.8;
    transform: scale(1.02);
  }
}

.animate-pulse-slow {
  animation: pulse-slow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Scale-in animation for completion checkmark (Requirement 8.4) */
@keyframes scale-in {
  0% {
    opacity: 0;
    transform: scale(0);
  }
  50% {
    opacity: 1;
    transform: scale(1.2);
  }
  100% {
    opacity: 1;
    transform: scale(1);
  }
}

.animate-scale-in {
  animation: scale-in 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}

/* Shimmer animation for progress bar (Requirement 8.2) */
@keyframes shimmer {
  0% {
    transform: translateX(-100%);
  }
  100% {
    transform: translateX(100%);
  }
}

.animate-shimmer {
  animation: shimmer 2s infinite;
}

/* Highlight pulse animation for changed values (Requirement 13.5) */
@keyframes highlight-pulse {
  0% {
    background-color: transparent;
    transform: scale(1);
  }
  25% {
    background-color: rgba(99, 102, 241, 0.1);
    transform: scale(1.05);
  }
  50% {
    background-color: rgba(99, 102, 241, 0.15);
  }
  75% {
    background-color: rgba(99, 102, 241, 0.1);
    transform: scale(1.05);
  }
  100% {
    background-color: transparent;
    transform: scale(1);
  }
}

.highlight-pulse {
  animation: highlight-pulse 2s ease-in-out;
  border-radius: 0.5rem;
}

/* Smooth transitions for progress changes (Requirement 8.2) */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

.duration-700 {
  transition-duration: 700ms;
}

.ease-out {
  transition-timing-function: cubic-bezier(0, 0, 0.2, 1);
}
</style>
