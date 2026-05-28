<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <!-- Enhanced Background -->
      <div 
        class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity duration-300" 
        @click="$emit('close')"
      ></div>

      <!-- Modal Panel - Compact Card Design -->
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] z-10 border border-gray-200 overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
              </div>
              <div>
                <h3 class="text-xl font-bold text-white">Create New Job</h3>
                <p class="text-sm text-indigo-100">Submit a distributed processing job</p>
              </div>
            </div>
            <button 
              @click="$emit('close')" 
              class="text-white/80 hover:text-white transition-colors p-2 hover:bg-white/10 rounded-lg"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Body with Scroll -->
        <div class="overflow-y-auto max-h-[calc(90vh-140px)]">
          <form @submit.prevent="handleSubmit" class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Job Configuration -->
            <div class="lg:col-span-2 space-y-6">
              
              <!-- Basic Information Card -->
              <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                  <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                  Job Information
                </h4>

                <div class="space-y-4">
                  <!-- Job Name -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Job Name <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model="form.name"
                      type="text"
                      required
                      placeholder="e.g., Process Customer Images"
                      class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-gray-900 placeholder-gray-400"
                    />
                  </div>

                  <!-- Job Type -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Job Type <span class="text-red-500">*</span>
                    </label>
                    <select
                      v-model="form.type"
                      required
                      class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-gray-900"
                    >
                      <option value="">Select job type...</option>
                      <option value="result_processing">🎓 Result Processing</option>
                      <option value="image_processing">🖼️ Image Processing</option>
                      <option value="video_conversion">🎥 Video Conversion</option>
                      <option value="data_processing">📊 Data Processing</option>
                      <option value="report_generation">📄 Report Generation</option>
                      <option value="email_batch">📧 Email Batch</option>
                      <option value="ml_training">🤖 ML Training</option>
                      <option value="batch_processing">⚡ Batch Processing</option>
                    </select>
                  </div>

                  <!-- Priority & Task Count -->
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Priority <span class="text-red-500">*</span>
                      </label>
                      <select
                        v-model.number="form.priority"
                        required
                        class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-gray-900"
                      >
                        <option :value="3">🟢 Low (3)</option>
                        <option :value="5">🔵 Normal (5)</option>
                        <option :value="7">🟡 High (7)</option>
                        <option :value="10">🔴 Urgent (10)</option>
                      </select>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tasks <span class="text-red-500">*</span>
                      </label>
                      <input
                        v-model.number="form.task_count"
                        type="number"
                        min="1"
                        max="10000"
                        required
                        placeholder="100"
                        class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-gray-900 placeholder-gray-400"
                      />
                    </div>
                  </div>

                  <!-- Description -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Description (Optional)
                    </label>
                    <textarea
                      v-model="form.description"
                      rows="3"
                      placeholder="Describe what this job does..."
                      class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all resize-none text-gray-900 placeholder-gray-400"
                    ></textarea>
                  </div>
                </div>
              </div>

              <!-- Data Upload Card (shown for non-result_processing types) -->
              <div v-if="form.type && form.type !== 'result_processing'" class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                  <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                  </svg>
                  Upload Data
                </h4>
                
                <!-- Upload Type Selector -->
                <div class="grid grid-cols-4 gap-3 mb-4">
                  <button
                    v-for="uploadType in uploadTypes"
                    :key="uploadType.value"
                    type="button"
                    @click="selectedUploadType = uploadType.value"
                    :class="[
                      'flex flex-col items-center gap-2 p-3 rounded-lg border text-sm font-medium transition-all',
                      selectedUploadType === uploadType.value
                        ? 'bg-indigo-50 border-indigo-500 text-indigo-700'
                        : 'bg-white border-gray-300 text-gray-600 hover:border-indigo-300'
                    ]"
                  >
                    <span class="text-xl">{{ uploadType.icon }}</span>
                    <span>{{ uploadType.label }}</span>
                  </button>
                </div>

                <!-- Upload Areas -->
                <!-- CSV Upload -->
                <div v-if="selectedUploadType === 'csv'" 
                  class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-indigo-400 hover:bg-indigo-50/30 transition-all cursor-pointer"
                  @click="$refs.csvInput?.click()">
                  <input ref="csvInput" type="file" accept=".csv" @change="handleCSVUpload" class="hidden"/>
                  <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                  </div>
                  <p class="text-sm font-medium text-gray-700">Click to upload CSV</p>
                  <p class="text-xs text-gray-500 mt-1">Max 50MB</p>
                  <p v-if="uploadedCSV" class="mt-2 text-sm font-semibold text-green-600">
                    ✓ {{ uploadedCSV.name }} ({{ formatFileSize(uploadedCSV.size) }})
                  </p>
                </div>

                <!-- JSON Upload -->
                <div v-if="selectedUploadType === 'json'" 
                  class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-indigo-400 hover:bg-indigo-50/30 transition-all cursor-pointer"
                  @click="$refs.jsonInput?.click()">
                  <input ref="jsonInput" type="file" accept=".json" @change="handleJSONUpload" class="hidden"/>
                  <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg>
                  </div>
                  <p class="text-sm font-medium text-gray-700">Click to upload JSON</p>
                  <p class="text-xs text-gray-500 mt-1">Max 50MB</p>
                  <p v-if="uploadedJSON" class="mt-2 text-sm font-semibold text-blue-600">
                    ✓ {{ uploadedJSON.name }} ({{ formatFileSize(uploadedJSON.size) }})
                  </p>
                </div>

                <!-- Dataset Upload -->
                <div v-if="selectedUploadType === 'dataset'" 
                  class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-indigo-400 hover:bg-indigo-50/30 transition-all cursor-pointer"
                  @click="$refs.datasetInput?.click()">
                  <input ref="datasetInput" type="file" accept=".txt,.dat,.zip" @change="handleDatasetUpload" class="hidden"/>
                  <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                    </svg>
                  </div>
                  <p class="text-sm font-medium text-gray-700">Click to upload dataset</p>
                  <p class="text-xs text-gray-500 mt-1">TXT, DAT, ZIP - Max 100MB</p>
                  <p v-if="uploadedDataset" class="mt-2 text-sm font-semibold text-purple-600">
                    ✓ {{ uploadedDataset.name }} ({{ formatFileSize(uploadedDataset.size) }})
                  </p>
                </div>

                <!-- Images Upload -->
                <div v-if="selectedUploadType === 'images'" 
                  class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-indigo-400 hover:bg-indigo-50/30 transition-all cursor-pointer"
                  @click="$refs.imageInput?.click()">
                  <input ref="imageInput" type="file" multiple accept="image/*" @change="handleImageUpload" class="hidden"/>
                  <div class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                  </div>
                  <p class="text-sm font-medium text-gray-700">Click to upload images</p>
                  <p class="text-xs text-gray-500 mt-1">PNG, JPG - Max 10MB each</p>
                  <p v-if="uploadedImages.length > 0" class="mt-2 text-sm font-semibold text-pink-600">
                    ✓ {{ uploadedImages.length }} image(s) selected
                  </p>
                </div>
              </div>

              <!-- Result Processing Configuration -->
              <div v-if="form.type === 'result_processing'" class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                  <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v10z"/>
                  </svg>
                  Result Processing Configuration
                </h4>

                <div class="space-y-4">
                  <!-- Total Records -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Total Records to Process <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model.number="form.total_records"
                      type="number"
                      min="1"
                      max="1000000"
                      required
                      placeholder="e.g., 5000"
                      class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-gray-900 placeholder-gray-400"
                    />
                    <p class="text-xs text-gray-500 mt-1">Number of student records to generate and process</p>
                  </div>

                  <!-- Processing Operations -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Processing Operations <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-2">
                      <label class="flex items-center">
                        <input
                          v-model="form.operations"
                          type="checkbox"
                          value="calculate_grades"
                          class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        />
                        <span class="ml-2 text-sm text-gray-700">Calculate Grades</span>
                      </label>
                      <label class="flex items-center">
                        <input
                          v-model="form.operations"
                          type="checkbox"
                          value="generate_report"
                          class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        />
                        <span class="ml-2 text-sm text-gray-700">Generate Report</span>
                      </label>
                      <label class="flex items-center">
                        <input
                          v-model="form.operations"
                          type="checkbox"
                          value="validate_data"
                          class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        />
                        <span class="ml-2 text-sm text-gray-700">Validate Data</span>
                      </label>
                      <label class="flex items-center">
                        <input
                          v-model="form.operations"
                          type="checkbox"
                          value="export_results"
                          class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        />
                        <span class="ml-2 text-sm text-gray-700">Export Results</span>
                      </label>
                    </div>
                  </div>

                  <!-- Records per Task (Auto-calculated) -->
                  <div class="bg-indigo-50 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                      <svg class="w-5 h-5 text-indigo-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      </svg>
                      <div class="text-sm text-indigo-800">
                        <p class="font-semibold mb-1">Task Distribution</p>
                        <p>Your <strong>{{ form.total_records || 0 }} records</strong> will be distributed across <strong>{{ form.task_count }} tasks</strong>.</p>
                        <p class="mt-1">Each task will process approximately <strong>{{ recordsPerTask }} records</strong>.</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Right Column - Preview & Info -->
            <div class="space-y-6">
              
              <!-- Job Preview Card -->
              <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                  <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                  </svg>
                  Job Preview
                </h4>

                <div class="space-y-4">
                  <!-- Job Name Preview -->
                  <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs font-medium text-gray-500 mb-1">JOB NAME</p>
                    <p class="text-sm font-semibold text-gray-900">{{ form.name || 'Untitled Job' }}</p>
                  </div>

                  <!-- Type & Priority -->
                  <div class="grid grid-cols-2 gap-3">
                    <div class="bg-gray-50 rounded-lg p-3">
                      <p class="text-xs font-medium text-gray-500 mb-1">TYPE</p>
                      <p class="text-sm font-semibold text-gray-900">{{ getTypeLabel(form.type) }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                      <p class="text-xs font-medium text-gray-500 mb-1">PRIORITY</p>
                      <p class="text-sm font-semibold text-gray-900">{{ getPriorityLabel(form.priority) }}</p>
                    </div>
                  </div>

                  <!-- Task Count -->
                  <div class="bg-indigo-50 rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold text-indigo-600">{{ form.task_count || 0 }}</p>
                    <p class="text-xs text-indigo-600 mt-1">Total Tasks</p>
                  </div>

                  <!-- Result Processing Info -->
                  <div v-if="form.type === 'result_processing'" class="bg-blue-50 rounded-lg p-4">
                    <div class="grid grid-cols-2 gap-3 mb-3">
                      <div class="text-center">
                        <p class="text-lg font-bold text-blue-600">{{ (form.total_records || 0).toLocaleString() }}</p>
                        <p class="text-xs text-blue-600">Records</p>
                      </div>
                      <div class="text-center">
                        <p class="text-lg font-bold text-blue-600">{{ recordsPerTask }}</p>
                        <p class="text-xs text-blue-600">Per Task</p>
                      </div>
                    </div>
                    <div v-if="form.operations.length > 0" class="border-t border-blue-200 pt-3">
                      <p class="text-xs font-medium text-blue-600 mb-2">OPERATIONS</p>
                      <div class="flex flex-wrap gap-1">
                        <span 
                          v-for="op in form.operations" 
                          :key="op"
                          class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full"
                        >
                          {{ op.replace('_', ' ') }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Estimation Card -->
              <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                  <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  Time Estimate
                </h4>

                <div class="space-y-3">
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Single worker</span>
                    <span class="text-sm font-semibold text-gray-900">{{ estimateSingleWorker }}</span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">10 workers</span>
                    <span class="text-sm font-semibold text-green-600">{{ estimateMultipleWorkers }}</span>
                  </div>
                  <div class="pt-3 border-t border-gray-200">
                    <div class="bg-green-50 rounded-lg p-3 text-center">
                      <p class="text-lg font-bold text-green-600">~10x Faster</p>
                      <p class="text-xs text-green-600">Speed improvement</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Tips Card -->
              <div class="bg-amber-50 rounded-xl p-6 border border-amber-200">
                <h4 class="text-lg font-semibold text-amber-900 mb-4 flex items-center gap-2">
                  <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                  </svg>
                  Pro Tips
                </h4>

                <div class="space-y-2 text-sm text-amber-800">
                  <div class="flex items-start gap-2">
                    <span class="text-amber-600 font-bold">•</span>
                    <p><strong>More tasks</strong> = better parallelization</p>
                  </div>
                  <div class="flex items-start gap-2">
                    <span class="text-amber-600 font-bold">•</span>
                    <p><strong>Higher priority</strong> = faster processing</p>
                  </div>
                  <div class="flex items-start gap-2">
                    <span class="text-amber-600 font-bold">•</span>
                    <p><strong>Clear names</strong> = easier tracking</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="error" class="mt-6 bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg flex items-start gap-3">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
              <p class="font-semibold text-sm">Error Creating Job</p>
              <p class="text-sm mt-1">{{ error }}</p>
            </div>
          </div>
          </form>
        </div>

        <!-- Footer Actions -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex items-center justify-between">
          <div class="text-sm text-gray-600">
            <span class="font-medium">Required fields</span> are marked with <span class="text-red-500 font-bold">*</span>
          </div>
          <div class="flex items-center gap-3">
            <button
              type="button"
              @click="$emit('close')"
              class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all font-medium"
            >
              Cancel
            </button>
            <button
              type="submit"
              @click="handleSubmit"
              :disabled="submitting || !isFormValid"
              class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-all font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 shadow-sm"
            >
              <svg v-if="submitting" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>{{ submitting ? 'Creating Job...' : 'Create Job' }}</span>
            </button>
          </div>
        </div>
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
  total_records: 1000,
  operations: ['calculate_grades', 'generate_report', 'validate_data'],
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
const totalRecords = computed(() => form.value.total_records || 0)
const recordsPerTask = computed(() => {
  if (form.value.task_count > 0 && form.value.total_records > 0) {
    return Math.ceil(form.value.total_records / form.value.task_count)
  }
  return 0
})

const isFormValid = computed(() => {
  const basicValid = form.value.name && form.value.type && form.value.task_count > 0
  
  // For result_processing, require total_records and at least one operation
  if (form.value.type === 'result_processing') {
    return basicValid && form.value.total_records > 0 && form.value.operations.length > 0
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
      // result_processing - no file upload required, but include processing configuration
      const jobData = {
        name: form.value.name,
        description: form.value.description || null,
        type: form.value.type,
        task_count: form.value.task_count,
        priority: form.value.priority,
      }

      // Add result processing specific fields
      if (form.value.type === 'result_processing') {
        jobData.total_records = form.value.total_records
        jobData.operations = form.value.operations
      }

      response = await api.post('/jobs', jobData)
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
