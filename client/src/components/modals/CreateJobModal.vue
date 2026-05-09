<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <!-- Enhanced Glassmorphism Background -->
      <div 
        class="fixed inset-0 backdrop-blur-md bg-gradient-to-br from-slate-900/50 via-purple-900/50 to-slate-900/50 transition-opacity duration-300" 
        @click="$emit('close')"
      ></div>

      <!-- Modal Panel - Extra Wide, No Scroll -->
      <div class="relative bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl w-full max-w-7xl z-10 border border-white/20 overflow-hidden">
        <!-- Gradient Header -->
        <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 px-8 py-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
              <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center shadow-lg">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
              </div>
              <div>
                <h3 class="text-2xl font-bold text-white">✨ Create New Job</h3>
                <p class="text-sm text-indigo-100 mt-1">Submit a distributed processing job to the task scheduler</p>
              </div>
            </div>
            <button 
              @click="$emit('close')" 
              class="text-white/80 hover:text-white transition-all hover:rotate-90 duration-300 hover:scale-110"
            >
              <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Body - Three Column Layout (No Scroll) -->
        <form @submit.prevent="handleSubmit" class="p-8">
          <div class="grid grid-cols-12 gap-6">
            <!-- Left Column - Job Details (5 cols) -->
            <div class="col-span-5 space-y-5">
              <div class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-2xl p-6 border border-slate-200">
                <h4 class="text-lg font-bold text-slate-800 mb-5 flex items-center gap-2">
                  <span class="text-2xl">📝</span>
                  Job Configuration
                </h4>

                <!-- Job Name -->
                <div class="mb-4">
                  <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Job Name <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.name"
                    type="text"
                    required
                    placeholder="e.g., Process Customer Images"
                    class="w-full px-4 py-3 bg-white border-2 border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all hover:border-indigo-400 text-slate-800 placeholder-slate-400 font-medium"
                  />
                </div>

                <!-- Job Type -->
                <div class="mb-4">
                  <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Job Type <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model="form.type"
                    required
                    class="w-full px-4 py-3 bg-white border-2 border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all hover:border-indigo-400 text-slate-800 font-medium"
                  >
                    <option value="">Select job type...</option>
                    <option value="result_processing">🎓 Result Processing (Demo)</option>
                    <option value="image_processing">🖼️ Image Processing</option>
                    <option value="video_conversion">🎥 Video Conversion</option>
                    <option value="data_processing">📊 Data Processing</option>
                    <option value="report_generation">📄 Report Generation</option>
                    <option value="email_batch">📧 Email Batch</option>
                    <option value="ml_training">🤖 ML Training</option>
                    <option value="batch_processing">⚡ Batch Processing</option>
                  </select>
                </div>

                <!-- Priority & Task Count Row -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                  <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                      Priority <span class="text-red-500">*</span>
                    </label>
                    <select
                      v-model.number="form.priority"
                      required
                      class="w-full px-4 py-3 bg-white border-2 border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all hover:border-indigo-400 text-slate-800 font-medium"
                    >
                      <option :value="3">🟢 Low (3)</option>
                      <option :value="5">🔵 Normal (5)</option>
                      <option :value="7">🟡 High (7)</option>
                      <option :value="10">🔴 Urgent (10)</option>
                    </select>
                  </div>

                  <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                      Tasks <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model.number="form.task_count"
                      type="number"
                      min="1"
                      max="10000"
                      required
                      placeholder="100"
                      class="w-full px-4 py-3 bg-white border-2 border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all hover:border-indigo-400 text-slate-800 placeholder-slate-400 font-medium"
                    />
                  </div>
                </div>

                <!-- Description -->
                <div class="mb-4">
                  <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Description (Optional)
                  </label>
                  <textarea
                    v-model="form.description"
                    rows="3"
                    placeholder="Describe what this job does..."
                    class="w-full px-4 py-3 bg-white border-2 border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all resize-none hover:border-indigo-400 text-slate-800 placeholder-slate-400 font-medium"
                  ></textarea>
                </div>

                <!-- Data Upload Section (shown for non-result_processing types) -->
                <div v-if="form.type && form.type !== 'result_processing'" class="mt-4">
                  <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Upload Data <span class="text-red-500">*</span>
                  </label>
                  
                  <!-- Upload Type Selector -->
                  <div class="grid grid-cols-4 gap-2 mb-3">
                    <button
                      v-for="uploadType in uploadTypes"
                      :key="uploadType.value"
                      type="button"
                      @click="selectedUploadType = uploadType.value"
                      :class="[
                        'flex flex-col items-center gap-1 p-2 rounded-lg border text-xs font-medium transition-all',
                        selectedUploadType === uploadType.value
                          ? 'bg-indigo-100 border-indigo-500 text-indigo-700'
                          : 'bg-white border-slate-300 text-slate-600 hover:border-indigo-300'
                      ]"
                    >
                      <span class="text-lg">{{ uploadType.icon }}</span>
                      <span>{{ uploadType.label }}</span>
                    </button>
                  </div>

                  <!-- CSV Upload -->
                  <div v-if="selectedUploadType === 'csv'" 
                    class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center hover:border-indigo-400 hover:bg-indigo-50/30 transition-all cursor-pointer"
                    @click="$refs.csvInput?.click()">
                    <input ref="csvInput" type="file" accept=".csv" @change="handleCSVUpload" class="hidden"/>
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mx-auto mb-2">
                      <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                      </svg>
                    </div>
                    <p class="text-sm font-medium text-slate-700">Click to upload CSV</p>
                    <p class="text-xs text-slate-500 mt-1">Max 50MB</p>
                    <p v-if="uploadedCSV" class="mt-2 text-sm font-semibold text-green-600">
                      ✓ {{ uploadedCSV.name }} ({{ formatFileSize(uploadedCSV.size) }})
                    </p>
                  </div>

                  <!-- JSON Upload -->
                  <div v-if="selectedUploadType === 'json'" 
                    class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center hover:border-indigo-400 hover:bg-indigo-50/30 transition-all cursor-pointer"
                    @click="$refs.jsonInput?.click()">
                    <input ref="jsonInput" type="file" accept=".json" @change="handleJSONUpload" class="hidden"/>
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mx-auto mb-2">
                      <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                      </svg>
                    </div>
                    <p class="text-sm font-medium text-slate-700">Click to upload JSON</p>
                    <p class="text-xs text-slate-500 mt-1">Max 50MB</p>
                    <p v-if="uploadedJSON" class="mt-2 text-sm font-semibold text-blue-600">
                      ✓ {{ uploadedJSON.name }} ({{ formatFileSize(uploadedJSON.size) }})
                    </p>
                  </div>

                  <!-- Dataset Upload -->
                  <div v-if="selectedUploadType === 'dataset'" 
                    class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center hover:border-indigo-400 hover:bg-indigo-50/30 transition-all cursor-pointer"
                    @click="$refs.datasetInput?.click()">
                    <input ref="datasetInput" type="file" accept=".txt,.dat,.zip" @change="handleDatasetUpload" class="hidden"/>
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mx-auto mb-2">
                      <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                      </svg>
                    </div>
                    <p class="text-sm font-medium text-slate-700">Click to upload dataset</p>
                    <p class="text-xs text-slate-500 mt-1">TXT, DAT, ZIP - Max 100MB</p>
                    <p v-if="uploadedDataset" class="mt-2 text-sm font-semibold text-purple-600">
                      ✓ {{ uploadedDataset.name }} ({{ formatFileSize(uploadedDataset.size) }})
                    </p>
                  </div>

                  <!-- Images Upload -->
                  <div v-if="selectedUploadType === 'images'" 
                    class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center hover:border-indigo-400 hover:bg-indigo-50/30 transition-all cursor-pointer"
                    @click="$refs.imageInput?.click()">
                    <input ref="imageInput" type="file" multiple accept="image/*" @change="handleImageUpload" class="hidden"/>
                    <div class="w-12 h-12 bg-pink-100 rounded-xl flex items-center justify-center mx-auto mb-2">
                      <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                      </svg>
                    </div>
                    <p class="text-sm font-medium text-slate-700">Click to upload images</p>
                    <p class="text-xs text-slate-500 mt-1">PNG, JPG - Max 10MB each</p>
                    <p v-if="uploadedImages.length > 0" class="mt-2 text-sm font-semibold text-pink-600">
                      ✓ {{ uploadedImages.length }} image(s) selected
                    </p>
                  </div>
                </div>

                <!-- Demo Mode Info (for result_processing) -->
                <div v-if="form.type === 'result_processing'" class="mt-4 bg-indigo-50 border-2 border-indigo-200 rounded-xl p-4">
                  <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-indigo-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-xs text-indigo-800">
                      <p class="font-bold mb-1">Demo Mode: Auto-Generate Test Data</p>
                      <p>System will generate <strong>{{ totalRecords.toLocaleString() }} student records</strong> across <strong>{{ form.task_count }} tasks</strong>.</p>
                      <p class="mt-1">No file upload required.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Middle Column - Preview & Stats (4 cols) -->
            <div class="col-span-4 space-y-5">
              <!-- Job Preview Card -->
              <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl p-6 border-2 border-indigo-200">
                <h4 class="text-lg font-bold text-indigo-900 mb-4 flex items-center gap-2">
                  <span class="text-2xl">📊</span>
                  Job Preview
                </h4>

                <div class="space-y-3">
                  <!-- Job Name Preview -->
                  <div class="bg-white/80 rounded-xl p-4 border border-indigo-100">
                    <p class="text-xs font-semibold text-indigo-600 mb-1">JOB NAME</p>
                    <p class="text-sm font-bold text-slate-800">{{ form.name || 'Untitled Job' }}</p>
                  </div>

                  <!-- Type & Priority -->
                  <div class="grid grid-cols-2 gap-3">
                    <div class="bg-white/80 rounded-xl p-4 border border-indigo-100">
                      <p class="text-xs font-semibold text-indigo-600 mb-1">TYPE</p>
                      <p class="text-sm font-bold text-slate-800">{{ getTypeLabel(form.type) }}</p>
                    </div>
                    <div class="bg-white/80 rounded-xl p-4 border border-indigo-100">
                      <p class="text-xs font-semibold text-indigo-600 mb-1">PRIORITY</p>
                      <p class="text-sm font-bold text-slate-800">{{ getPriorityLabel(form.priority) }}</p>
                    </div>
                  </div>

                  <!-- Task Count -->
                  <div class="bg-white/80 rounded-xl p-4 border border-indigo-100">
                    <p class="text-xs font-semibold text-indigo-600 mb-1">TOTAL TASKS</p>
                    <p class="text-2xl font-bold text-indigo-600">{{ form.task_count || 0 }}</p>
                    <p class="text-xs text-slate-600 mt-1">Will be distributed across workers</p>
                  </div>
                </div>
              </div>

              <!-- Estimation Card -->
              <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl p-6 border-2 border-green-200">
                <h4 class="text-lg font-bold text-green-900 mb-4 flex items-center gap-2">
                  <span class="text-2xl">⏱️</span>
                  Estimated Processing
                </h4>

                <div class="space-y-3">
                  <div class="bg-white/80 rounded-xl p-4 border border-green-100">
                    <div class="flex justify-between items-center mb-2">
                      <span class="text-xs font-semibold text-green-600">SINGLE WORKER</span>
                      <span class="text-lg font-bold text-slate-800">{{ estimateSingleWorker }}</span>
                    </div>
                    <div class="w-full bg-green-200 rounded-full h-2">
                      <div class="bg-green-600 h-2 rounded-full" style="width: 100%"></div>
                    </div>
                  </div>

                  <div class="bg-white/80 rounded-xl p-4 border border-green-100">
                    <div class="flex justify-between items-center mb-2">
                      <span class="text-xs font-semibold text-green-600">10 WORKERS</span>
                      <span class="text-lg font-bold text-green-600">{{ estimateMultipleWorkers }}</span>
                    </div>
                    <div class="w-full bg-green-200 rounded-full h-2">
                      <div class="bg-gradient-to-r from-green-600 to-emerald-600 h-2 rounded-full" style="width: 10%"></div>
                    </div>
                  </div>

                  <div class="bg-gradient-to-r from-green-600 to-emerald-600 rounded-xl p-4 text-white">
                    <p class="text-xs font-semibold mb-1">SPEED IMPROVEMENT</p>
                    <p class="text-2xl font-bold">~10x Faster</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Right Column - Information (3 cols) -->
            <div class="col-span-3 space-y-5">
              <!-- How It Works -->
              <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl p-6 border-2 border-amber-200">
                <h4 class="text-lg font-bold text-amber-900 mb-4 flex items-center gap-2">
                  <span class="text-2xl">💡</span>
                  How It Works
                </h4>

                <div class="space-y-3">
                  <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-amber-600 rounded-lg flex items-center justify-center flex-shrink-0 text-white font-bold">
                      1
                    </div>
                    <div>
                      <p class="text-sm font-bold text-slate-800">Job Submission</p>
                      <p class="text-xs text-slate-600 mt-1">Your job is created and queued</p>
                    </div>
                  </div>

                  <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-amber-600 rounded-lg flex items-center justify-center flex-shrink-0 text-white font-bold">
                      2
                    </div>
                    <div>
                      <p class="text-sm font-bold text-slate-800">Task Distribution</p>
                      <p class="text-xs text-slate-600 mt-1">Split into parallel tasks</p>
                    </div>
                  </div>

                  <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-amber-600 rounded-lg flex items-center justify-center flex-shrink-0 text-white font-bold">
                      3
                    </div>
                    <div>
                      <p class="text-sm font-bold text-slate-800">Worker Processing</p>
                      <p class="text-xs text-slate-600 mt-1">Multiple workers execute tasks</p>
                    </div>
                  </div>

                  <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-amber-600 rounded-lg flex items-center justify-center flex-shrink-0 text-white font-bold">
                      4
                    </div>
                    <div>
                      <p class="text-sm font-bold text-slate-800">Completion</p>
                      <p class="text-xs text-slate-600 mt-1">Results aggregated and ready</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Tips -->
              <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-2xl p-6 border-2 border-blue-200">
                <h4 class="text-lg font-bold text-blue-900 mb-4 flex items-center gap-2">
                  <span class="text-2xl">💎</span>
                  Pro Tips
                </h4>

                <div class="space-y-3 text-xs text-slate-700">
                  <div class="flex items-start gap-2">
                    <span class="text-blue-600 font-bold">•</span>
                    <p><strong>More tasks</strong> = better parallelization</p>
                  </div>
                  <div class="flex items-start gap-2">
                    <span class="text-blue-600 font-bold">•</span>
                    <p><strong>Higher priority</strong> = faster processing</p>
                  </div>
                  <div class="flex items-start gap-2">
                    <span class="text-blue-600 font-bold">•</span>
                    <p><strong>Clear names</strong> = easier tracking</p>
                  </div>
                  <div class="flex items-start gap-2">
                    <span class="text-blue-600 font-bold">•</span>
                    <p><strong>Descriptions</strong> = better debugging</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="error" class="mt-6 bg-red-50 border-2 border-red-300 text-red-700 px-5 py-4 rounded-xl flex items-start gap-3 shadow-sm">
            <svg class="w-6 h-6 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
              <p class="font-bold text-sm">Error Creating Job</p>
              <p class="text-sm mt-1">{{ error }}</p>
            </div>
          </div>

          <!-- Footer Actions -->
          <div class="flex items-center justify-between pt-6 mt-6 border-t-2 border-slate-200">
            <div class="text-sm text-slate-600">
              <span class="font-semibold">Required fields</span> are marked with <span class="text-red-500 font-bold">*</span>
            </div>
            <div class="flex items-center gap-4">
              <button
                type="button"
                @click="$emit('close')"
                class="px-8 py-3 border-2 border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 transition-all font-bold hover:shadow-md hover:scale-105"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="submitting || !isFormValid"
                class="px-8 py-3 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 text-white rounded-xl hover:from-indigo-700 hover:via-purple-700 hover:to-pink-700 transition-all font-bold disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 shadow-lg hover:shadow-xl hover:scale-105"
              >
                <svg v-if="submitting" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ submitting ? 'Creating Job...' : '✨ Create Job' }}</span>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue'
import api from '@/api'

const emit = defineEmits(['close', 'created'])

const form = ref({
  name: '',
  type: '',
  description: '',
  priority: 5,
  task_count: 10,
})

const selectedUploadType = ref('csv')
const uploadedCSV = ref(null)
const uploadedJSON = ref(null)
const uploadedDataset = ref(null)
const uploadedImages = ref([])

const submitting = ref(false)
const error = ref(null)

const uploadTypes = [
  { value: 'csv', icon: '📄', label: 'CSV' },
  { value: 'json', icon: '📋', label: 'JSON' },
  { value: 'dataset', icon: '💾', label: 'Dataset' },
  { value: 'images', icon: '🖼️', label: 'Images' },
]

const RECORDS_PER_TASK = 100
const totalRecords = computed(() => form.value.task_count * RECORDS_PER_TASK)

const isFormValid = computed(() => {
  const basicValid = form.value.name && form.value.type && form.value.task_count > 0
  
  // For result_processing, no file upload required
  if (form.value.type === 'result_processing') {
    return basicValid
  }
  
  // For other types, require at least one file
  const hasFile = uploadedCSV.value || uploadedJSON.value || uploadedDataset.value || uploadedImages.value.length > 0
  return basicValid && hasFile
})

const estimateSingleWorker = computed(() => {
  const minutes = Math.ceil(form.value.task_count * 0.5)
  if (minutes < 60) return `${minutes} min`
  const hours = Math.floor(minutes / 60)
  const mins = minutes % 60
  return `${hours}h ${mins}m`
})

const estimateMultipleWorkers = computed(() => {
  const minutes = Math.ceil(form.value.task_count * 0.5 / 10)
  if (minutes < 60) return `${minutes} min`
  const hours = Math.floor(minutes / 60)
  const mins = minutes % 60
  return `${hours}h ${mins}m`
})

function getTypeLabel(type) {
  const types = {
    'result_processing': '🎓 Result Processing',
    'image_processing': '🖼️ Image Processing',
    'video_conversion': '🎥 Video Conversion',
    'data_processing': '📊 Data Processing',
    'report_generation': '📄 Report Generation',
    'email_batch': '📧 Email Batch',
    'ml_training': '🤖 ML Training',
    'batch_processing': '⚡ Batch Processing'
  }
  return types[type] || 'Not selected'
}

function getPriorityLabel(priority) {
  const priorities = {
    3: '🟢 Low',
    5: '🔵 Normal',
    7: '🟡 High',
    10: '🔴 Urgent'
  }
  return priorities[priority] || 'Normal'
}

function handleCSVUpload(event) {
  const file = event.target.files[0]
  if (file) {
    if (file.size > 50 * 1024 * 1024) {
      error.value = 'CSV file size exceeds 50MB limit.'
      return
    }
    uploadedCSV.value = file
    error.value = null
  }
}

function handleJSONUpload(event) {
  const file = event.target.files[0]
  if (file) {
    if (file.size > 50 * 1024 * 1024) {
      error.value = 'JSON file size exceeds 50MB limit.'
      return
    }
    uploadedJSON.value = file
    error.value = null
  }
}

function handleDatasetUpload(event) {
  const file = event.target.files[0]
  if (file) {
    if (file.size > 100 * 1024 * 1024) {
      error.value = 'Dataset file size exceeds 100MB limit.'
      return
    }
    uploadedDataset.value = file
    error.value = null
  }
}

function handleImageUpload(event) {
  const files = Array.from(event.target.files)
  const oversizedImage = files.find(img => img.size > 10 * 1024 * 1024)
  if (oversizedImage) {
    error.value = `Image "${oversizedImage.name}" exceeds 10MB limit.`
    return
  }
  uploadedImages.value = files
  error.value = null
}

function formatFileSize(bytes) {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

async function handleSubmit() {
  if (!isFormValid.value) {
    error.value = 'Please fill in all required fields.'
    return
  }

  submitting.value = true
  error.value = null

  try {
    let response

    // Handle different upload scenarios
    if (form.value.type === 'image_processing' && uploadedImages.value.length > 0) {
      // Image processing with multipart upload
      const fd = new FormData()
      fd.append('name', form.value.name)
      fd.append('description', form.value.description || '')
      fd.append('type', form.value.type)
      fd.append('priority', form.value.priority)
      uploadedImages.value.forEach(f => fd.append('images[]', f))
      response = await api.post('/jobs', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    } else if (uploadedCSV.value || uploadedJSON.value || uploadedDataset.value) {
      // CSV, JSON, or Dataset upload with multipart
      const fd = new FormData()
      fd.append('name', form.value.name)
      fd.append('description', form.value.description || '')
      fd.append('type', form.value.type)
      fd.append('task_count', form.value.task_count)
      fd.append('priority', form.value.priority)
      
      if (uploadedCSV.value) {
        fd.append('data_file', uploadedCSV.value)
        fd.append('upload_type', 'csv')
      } else if (uploadedJSON.value) {
        fd.append('data_file', uploadedJSON.value)
        fd.append('upload_type', 'json')
      } else if (uploadedDataset.value) {
        fd.append('data_file', uploadedDataset.value)
        fd.append('upload_type', 'dataset')
      }
      
      response = await api.post('/jobs', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    } else {
      // result_processing or demo mode - no file upload required
      response = await api.post('/jobs', {
        name: form.value.name,
        description: form.value.description || null,
        type: form.value.type,
        task_count: form.value.task_count,
        priority: form.value.priority,
      })
    }

    // Success - emit the created job
    emit('created', response.data.job)
    emit('close')
  } catch (err) {
    console.error('Job creation error:', err)
    error.value = err.response?.data?.message || 'Failed to create job. Please try again.'
  } finally {
    submitting.value = false
  }
}
</script>
