<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <!-- Glassmorphism Background overlay with blur -->
      <div class="fixed inset-0 backdrop-blur-md bg-black/30 transition-opacity" @click="$emit('close')"></div>

      <!-- Modal panel with glassmorphism - Wider and centered -->
      <div class="relative bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl w-full max-w-5xl z-10 border border-white/20 max-h-[90vh] flex flex-col">
          <!-- Header with gradient -->
          <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 px-6 py-5 rounded-t-3xl">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center shadow-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
              </div>
              <div>
                <h3 class="text-xl font-bold text-white">✨ Create New Job</h3>
                <p class="text-sm text-indigo-100">Submit a new task for processing</p>
              </div>
            </div>
            <button @click="$emit('close')" class="text-white hover:text-gray-200 transition-all hover:rotate-90 duration-300">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Body - Two Column Layout -->
        <form @submit.prevent="handleSubmit" class="p-8 overflow-y-auto flex-1">
          <div class="grid grid-cols-2 gap-6">
            <!-- Left Column -->
            <div class="space-y-5">
              <!-- Job Name -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  📝 Job Name <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.name"
                  type="text"
                  required
                  placeholder="e.g., Video Processing Job"
                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all hover:border-indigo-300"
                />
              </div>

              <!-- Job Type -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  🏷️ Job Type <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.type"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all hover:border-indigo-300"
                >
                  <option value="">Select a type</option>
                  <option value="video_processing">🎥 Video Processing</option>
                  <option value="image_processing">🖼️ Image Processing</option>
                  <option value="data_processing">📊 Data Processing</option>
                  <option value="ml_training">🤖 ML Training</option>
                  <option value="report_generation">📄 Report Generation</option>
                  <option value="batch_processing">⚡ Batch Processing</option>
                  <option value="other">🔧 Other</option>
                </select>
              </div>

              <!-- Priority & Total Tasks Row -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    ⚡ Priority <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model.number="form.priority"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all hover:border-indigo-300"
                  >
                    <option :value="1">🟢 Low (1)</option>
                    <option :value="3">🔵 Normal (3)</option>
                    <option :value="5" selected>🟡 Medium (5)</option>
                    <option :value="7">🟠 High (7)</option>
                    <option :value="10">🔴 Critical (10)</option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    🔢 Number of Tasks <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model.number="form.task_count"
                    type="number"
                    min="1"
                    max="10000"
                    required
                    placeholder="e.g., 100"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all hover:border-indigo-300"
                  />
                </div>
              </div>

              <!-- Description -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  💬 Description
                </label>
                <textarea
                  v-model="form.description"
                  rows="8"
                  placeholder="Describe what this job does..."
                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all resize-none hover:border-indigo-300"
                ></textarea>
              </div>
            </div>

            <!-- Right Column - Info Card -->
            <div class="space-y-5">
              <!-- Info Card -->
              <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-200">
                <div class="flex items-start gap-3 mb-4">
                  <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  <div>
                    <h4 class="text-lg font-bold text-gray-900 mb-2">📋 How Jobs Work</h4>
                    <p class="text-sm text-gray-700 leading-relaxed">
                      A <strong>job</strong> is a collection of tasks that will be distributed across worker nodes for parallel processing.
                    </p>
                  </div>
                </div>

                <div class="space-y-3">
                  <div class="bg-white/70 rounded-xl p-4">
                    <div class="flex items-start gap-2">
                      <span class="text-2xl">🎯</span>
                      <div>
                        <p class="font-semibold text-gray-900 text-sm mb-1">Job Name & Type</p>
                        <p class="text-xs text-gray-600">Give your job a descriptive name and select the type of processing needed.</p>
                      </div>
                    </div>
                  </div>

                  <div class="bg-white/70 rounded-xl p-4">
                    <div class="flex items-start gap-2">
                      <span class="text-2xl">⚡</span>
                      <div>
                        <p class="font-semibold text-gray-900 text-sm mb-1">Priority Level</p>
                        <p class="text-xs text-gray-600">Higher priority jobs (7-10) are processed before lower priority ones (1-5).</p>
                      </div>
                    </div>
                  </div>

                  <div class="bg-white/70 rounded-xl p-4">
                    <div class="flex items-start gap-2">
                      <span class="text-2xl">🔢</span>
                      <div>
                        <p class="font-semibold text-gray-900 text-sm mb-1">Number of Tasks</p>
                        <p class="text-xs text-gray-600">Your job will be split into this many tasks for parallel processing (1-10,000).</p>
                      </div>
                    </div>
                  </div>

                  <div class="bg-white/70 rounded-xl p-4">
                    <div class="flex items-start gap-2">
                      <span class="text-2xl">💬</span>
                      <div>
                        <p class="font-semibold text-gray-900 text-sm mb-1">Description (Optional)</p>
                        <p class="text-xs text-gray-600">Add notes about what this job does, input files, or expected output.</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="error" class="mt-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-start gap-2 shadow-sm">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-sm">{{ error }}</span>
          </div>

          <!-- Footer -->
          <div class="flex items-center justify-end gap-3 pt-6 mt-6 border-t border-gray-200">
            <button
              type="button"
              @click="$emit('close')"
              class="px-8 py-3 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-all font-semibold hover:shadow-md hover:scale-105"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all font-semibold disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 shadow-lg hover:shadow-xl hover:scale-105"
            >
              <svg v-if="submitting" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>{{ submitting ? 'Creating...' : '✨ Create Job' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue'
import { useJobsStore } from '@/stores/jobsStore'

const emit = defineEmits(['close', 'created'])
const jobsStore = useJobsStore()

const form = ref({
  name: '',
  type: '',
  description: '',
  priority: 5,
  task_count: 1,
  dataset: null
})

const submitting = ref(false)
const error = ref(null)

async function handleSubmit() {
  submitting.value = true
  error.value = null

  try {
    // Prepare the payload
    const payload = {
      name: form.value.name,
      type: form.value.type,
      description: form.value.description || null,
      priority: form.value.priority,
      task_count: form.value.task_count,
      dataset: form.value.dataset
    }

    // Submit job
    const result = await jobsStore.createJob(payload)

    if (result.success) {
      emit('created', result.data)
      emit('close')
    } else {
      error.value = result.error || 'Failed to create job'
    }
  } catch (err) {
    error.value = err.message || 'An error occurred'
  } finally {
    submitting.value = false
  }
}
</script>
