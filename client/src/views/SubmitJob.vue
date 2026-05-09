<template>
  <div class="flex h-screen bg-gray-50">
    <UserSidebar />

    <div class="flex-1 ml-64 overflow-auto">

      <!-- ── Header ─────────────────────────────────────────────────────── -->
      <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="px-8 py-4 flex items-center justify-between">
          <div>
            <router-link to="/dashboard"
              class="flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-700 font-medium mb-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
              </svg>
              Back to Dashboard
            </router-link>
            <h1 class="text-2xl font-bold text-gray-900">Submit New Job</h1>
            <p class="text-sm text-gray-500 mt-0.5">Create a distributed processing job across worker nodes</p>
          </div>
          <div class="flex items-center gap-2 px-3 py-1.5 bg-indigo-50 border border-indigo-100 rounded-lg">
            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
            <span class="text-xs font-medium text-indigo-700">System Ready</span>
          </div>
        </div>
      </header>

      <!-- ── Main ───────────────────────────────────────────────────────── -->
      <main class="p-8">

        <!-- ════════════════════════════════════════════════════════════════
             VIEW A — Job Form
             ════════════════════════════════════════════════════════════════ -->
        <div v-if="view === 'form'" class="max-w-5xl mx-auto">
          <div class="grid grid-cols-3 gap-6">

            <!-- Left: form (2 cols) -->
            <div class="col-span-2 space-y-6">

              <!-- Job Details -->
              <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                  <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                  </div>
                  <div>
                    <h2 class="text-sm font-semibold text-gray-900">Job Details</h2>
                    <p class="text-xs text-gray-500">Basic information about your job</p>
                  </div>
                </div>
                <div class="p-6 space-y-5">

                  <!-- Job Name -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                      Job Name <span class="text-red-500">*</span>
                    </label>
                    <input v-model="form.name" type="text" required
                      placeholder="e.g., Student Result Processing"
                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-900
                             placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500
                             focus:border-transparent transition-all"/>
                  </div>

                  <!-- Description -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                    <textarea v-model="form.description" rows="3"
                      placeholder="Describe what this job does, input data, expected output..."
                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-900
                             placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500
                             focus:border-transparent transition-all resize-none"></textarea>
                  </div>

                  <!-- Job Type grid -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                      Job Type <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-3 gap-2">
                      <button v-for="type in jobTypes" :key="type.value" type="button"
                        @click="form.type = type.value"
                        :class="[
                          'flex flex-col items-center gap-1.5 p-3 rounded-lg border text-xs font-medium transition-all',
                          form.type === type.value
                            ? 'bg-indigo-50 border-indigo-500 text-indigo-700 ring-1 ring-indigo-400'
                            : 'bg-white border-gray-200 text-gray-600 hover:border-indigo-300 hover:bg-indigo-50/50'
                        ]">
                        <span class="text-lg">{{ type.icon }}</span>
                        <span>{{ type.label }}</span>
                        <!-- "NEW" badge for result_processing -->
                        <span v-if="type.value === 'result_processing'"
                          class="mt-0.5 px-1.5 py-0.5 bg-green-100 text-green-700 text-[10px] font-bold rounded-full">
                          NEW
                        </span>
                      </button>
                    </div>
                  </div>

                  <!-- result_processing info box -->
                  <div v-if="form.type === 'result_processing'"
                    class="flex items-start gap-3 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                    <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-xs text-blue-800">
                      <p class="font-semibold mb-1">How result_processing works</p>
                      <p>The system will generate <strong>{{ totalRecords }} student records</strong> and split them
                         across <strong>{{ form.task_count }} tasks</strong>
                         (~<strong>{{ recordsPerTask }} records / task</strong>).</p>
                      <p class="mt-1">Each task runs:
                        <span class="font-mono bg-blue-100 px-1 rounded">calculate_grades</span> →
                        <span class="font-mono bg-blue-100 px-1 rounded">generate_report</span> →
                        <span class="font-mono bg-blue-100 px-1 rounded">validate_data</span>
                      </p>
                    </div>
                  </div>

                </div>
              </div>

              <!-- Execution Config -->
              <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                  <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                  </div>
                  <div>
                    <h2 class="text-sm font-semibold text-gray-900">Execution Configuration</h2>
                    <p class="text-xs text-gray-500">Control how tasks are distributed</p>
                  </div>
                </div>
                <div class="p-6 space-y-5">
                  <div class="grid grid-cols-2 gap-5">
                    <!-- Task Count -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Number of Tasks <span class="text-red-500">*</span>
                      </label>
                      <input v-model.number="form.task_count" type="number" min="1" max="10000" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-900
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"/>
                      <p class="text-xs text-gray-500 mt-1.5">Job will be split into this many parallel tasks</p>
                    </div>
                    <!-- Priority -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1.5">Priority</label>
                      <select v-model.number="form.priority"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-900
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                        <option :value="3">🟢 Low (3)</option>
                        <option :value="5">🔵 Normal (5)</option>
                        <option :value="7">🟡 High (7)</option>
                        <option :value="10">🔴 Urgent (10)</option>
                      </select>
                    </div>
                  </div>

                  <!-- Slider -->
                  <div>
                    <div class="flex justify-between text-xs text-gray-500 mb-1.5">
                      <span>Task Distribution</span>
                      <span class="font-medium text-indigo-600">{{ form.task_count }} tasks</span>
                    </div>
                    <input v-model.number="form.task_count" type="range" min="1" max="100"
                      class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600"/>
                    <div class="flex justify-between text-xs text-gray-400 mt-1">
                      <span>1</span><span>25</span><span>50</span><span>75</span><span>100</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Data Upload Section -->
              <div v-if="form.type && form.type !== 'result_processing'" class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                  <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                  </div>
                  <div>
                    <h2 class="text-sm font-semibold text-gray-900">Data Upload</h2>
                    <p class="text-xs text-gray-500">Upload your data files for processing</p>
                  </div>
                </div>
                <div class="p-6">
                  <!-- Upload Type Selector -->
                  <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Upload Type</label>
                    <div class="grid grid-cols-4 gap-2">
                      <button v-for="uploadType in uploadTypes" :key="uploadType.value" type="button"
                        @click="selectedUploadType = uploadType.value"
                        :class="[
                          'flex flex-col items-center gap-1.5 p-3 rounded-lg border text-xs font-medium transition-all',
                          selectedUploadType === uploadType.value
                            ? 'bg-green-50 border-green-500 text-green-700 ring-1 ring-green-400'
                            : 'bg-white border-gray-200 text-gray-600 hover:border-green-300 hover:bg-green-50/50'
                        ]">
                        <span class="text-lg">{{ uploadType.icon }}</span>
                        <span>{{ uploadType.label }}</span>
                      </button>
                    </div>
                  </div>

                  <!-- CSV Upload -->
                  <div v-if="selectedUploadType === 'csv'" class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center
                              hover:border-green-400 hover:bg-green-50/30 transition-all cursor-pointer"
                    @click="$refs.csvInput.click()">
                    <input ref="csvInput" type="file" accept=".csv" @change="handleCSVUpload" class="hidden"/>
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                      <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                      </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-700">Click to upload CSV file</p>
                    <p class="text-xs text-gray-500 mt-1">CSV files up to 50MB</p>
                    <p v-if="uploadedCSV" class="mt-3 text-sm font-semibold text-green-600">
                      ✓ {{ uploadedCSV.name }} ({{ formatFileSize(uploadedCSV.size) }})
                    </p>
                  </div>

                  <!-- JSON Upload -->
                  <div v-if="selectedUploadType === 'json'" class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center
                              hover:border-blue-400 hover:bg-blue-50/30 transition-all cursor-pointer"
                    @click="$refs.jsonInput.click()">
                    <input ref="jsonInput" type="file" accept=".json" @change="handleJSONUpload" class="hidden"/>
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                      <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                      </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-700">Click to upload JSON file</p>
                    <p class="text-xs text-gray-500 mt-1">JSON files up to 50MB</p>
                    <p v-if="uploadedJSON" class="mt-3 text-sm font-semibold text-blue-600">
                      ✓ {{ uploadedJSON.name }} ({{ formatFileSize(uploadedJSON.size) }})
                    </p>
                  </div>

                  <!-- Dataset Upload -->
                  <div v-if="selectedUploadType === 'dataset'" class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center
                              hover:border-purple-400 hover:bg-purple-50/30 transition-all cursor-pointer"
                    @click="$refs.datasetInput.click()">
                    <input ref="datasetInput" type="file" accept=".txt,.dat,.zip" @change="handleDatasetUpload" class="hidden"/>
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                      <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                      </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-700">Click to upload dataset</p>
                    <p class="text-xs text-gray-500 mt-1">TXT, DAT, ZIP files up to 100MB</p>
                    <p v-if="uploadedDataset" class="mt-3 text-sm font-semibold text-purple-600">
                      ✓ {{ uploadedDataset.name }} ({{ formatFileSize(uploadedDataset.size) }})
                    </p>
                  </div>

                  <!-- Image Upload (for image_processing) -->
                  <div v-if="selectedUploadType === 'images'" class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center
                              hover:border-pink-400 hover:bg-pink-50/30 transition-all cursor-pointer"
                    @click="$refs.imageInput.click()">
                    <input ref="imageInput" type="file" multiple accept="image/*" @change="handleImageUpload" class="hidden"/>
                    <div class="w-12 h-12 bg-pink-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                      <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                      </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-700">Click to upload images</p>
                    <p class="text-xs text-gray-500 mt-1">PNG, JPG, GIF up to 10MB each</p>
                    <p v-if="uploadedImages.length > 0" class="mt-3 text-sm font-semibold text-pink-600">
                      ✓ {{ uploadedImages.length }} image(s) selected
                    </p>
                  </div>
                </div>
              </div>

              <!-- Demo Mode: Auto Generate Test Tasks (for result_processing) -->
              <div v-if="form.type === 'result_processing'" class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                  <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                  </div>
                  <div>
                    <h2 class="text-sm font-semibold text-gray-900">Demo Mode: Auto Generate Test Tasks</h2>
                    <p class="text-xs text-gray-500">System will automatically generate test data</p>
                  </div>
                </div>
                <div class="p-6">
                  <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl p-6 border border-indigo-100">
                    <div class="flex items-start gap-4">
                      <div class="w-12 h-12 bg-indigo-600 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                      </div>
                      <div class="flex-1">
                        <h3 class="text-sm font-bold text-indigo-900 mb-2">Simulation Preview</h3>
                        <div class="space-y-2 text-sm text-indigo-800">
                          <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span><strong>{{ form.task_count }} tasks</strong> will be generated</span>
                          </div>
                          <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span><strong>{{ totalRecords.toLocaleString() }} student records</strong> will be processed</span>
                          </div>
                          <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Estimated execution time: <strong>{{ estimateParallel }}</strong></span>
                          </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-indigo-200">
                          <p class="text-xs text-indigo-700 font-medium mb-2">Operations per task:</p>
                          <div class="flex flex-wrap gap-2">
                            <span class="px-2 py-1 bg-indigo-100 text-indigo-800 text-xs font-mono rounded">calculate_grades()</span>
                            <span class="text-indigo-400">→</span>
                            <span class="px-2 py-1 bg-indigo-100 text-indigo-800 text-xs font-mono rounded">generate_report()</span>
                            <span class="text-indigo-400">→</span>
                            <span class="px-2 py-1 bg-indigo-100 text-indigo-800 text-xs font-mono rounded">validate_data()</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Error -->
              <div v-if="error"
                class="flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ error }}
              </div>

              <!-- Actions -->
              <div class="flex items-center gap-3">
                <button type="button" @click="submitJob" :disabled="loading || !isFormValid"
                  class="flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white
                         text-sm font-semibold rounded-lg transition-all disabled:opacity-50
                         disabled:cursor-not-allowed shadow-sm hover:shadow-md">
                  <svg v-if="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                  </svg>
                  <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 10V3L4 14h7v7l9-11h-7z"/>
                  </svg>
                  {{ loading ? 'Submitting...' : 'Submit Job' }}
                </button>
                <router-link to="/dashboard"
                  class="px-6 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium
                         rounded-lg hover:bg-gray-50 transition-colors">
                  Cancel
                </router-link>
              </div>
            </div>

            <!-- Right: summary panel -->
            <div class="space-y-5">

              <!-- Job Preview -->
              <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="px-5 py-4 border-b border-gray-100">
                  <h3 class="text-sm font-semibold text-gray-900">Job Preview</h3>
                </div>
                <div class="p-5 space-y-4">
                  <div>
                    <p class="text-xs text-gray-500 mb-1">Name</p>
                    <p class="text-sm font-medium text-gray-900 truncate">{{ form.name || '—' }}</p>
                  </div>
                  <div>
                    <p class="text-xs text-gray-500 mb-1">Type</p>
                    <span v-if="form.type"
                      class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-indigo-50 text-indigo-700 text-xs font-medium rounded-full">
                      {{ selectedType?.icon }} {{ selectedType?.label }}
                    </span>
                    <span v-else class="text-sm text-gray-400">Not selected</span>
                  </div>
                  <div class="grid grid-cols-2 gap-3">
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                      <p class="text-lg font-bold text-indigo-600">{{ form.task_count }}</p>
                      <p class="text-xs text-gray-500 mt-0.5">Tasks</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                      <p class="text-lg font-bold" :class="priorityColor">{{ priorityLabel }}</p>
                      <p class="text-xs text-gray-500 mt-0.5">Priority</p>
                    </div>
                  </div>
                  <!-- records breakdown for result_processing -->
                  <div v-if="form.type === 'result_processing'"
                    class="bg-indigo-50 rounded-lg p-3 space-y-1.5">
                    <div class="flex justify-between text-xs">
                      <span class="text-indigo-600">Total records</span>
                      <span class="font-bold text-indigo-800">{{ totalRecords.toLocaleString() }}</span>
                    </div>
                    <div class="flex justify-between text-xs">
                      <span class="text-indigo-600">Records / task</span>
                      <span class="font-bold text-indigo-800">{{ recordsPerTask }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Estimation -->
              <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="px-5 py-4 border-b border-gray-100">
                  <h3 class="text-sm font-semibold text-gray-900">Estimation</h3>
                </div>
                <div class="p-5 space-y-3">
                  <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Single worker</span>
                    <span class="font-medium text-gray-900">{{ estimateSingle }}</span>
                  </div>
                  <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">10 workers</span>
                    <span class="font-medium text-green-600">{{ estimateParallel }}</span>
                  </div>
                  <div class="pt-2 border-t border-gray-100 flex items-center justify-between text-sm">
                    <span class="text-gray-500">Speed gain</span>
                    <span class="font-bold text-indigo-600">~10× faster</span>
                  </div>
                </div>
              </div>

              <!-- How it works -->
              <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-indigo-900 mb-3">How it works</h3>
                <ol class="space-y-2.5">
                  <li v-for="(step, i) in steps" :key="i" class="flex items-start gap-2.5">
                    <span class="w-5 h-5 bg-indigo-600 text-white text-xs font-bold rounded-full
                                 flex items-center justify-center flex-shrink-0 mt-0.5">{{ i + 1 }}</span>
                    <div>
                      <p class="text-xs font-semibold text-indigo-900">{{ step.title }}</p>
                      <p class="text-xs text-indigo-700 mt-0.5">{{ step.desc }}</p>
                    </div>
                  </li>
                </ol>
              </div>

            </div>
          </div>
        </div>


        <!-- ════════════════════════════════════════════════════════════════
             VIEW B — Confirmation Screen (shown after successful submit)
             ════════════════════════════════════════════════════════════════ -->
        <div v-if="view === 'confirm'" class="max-w-4xl mx-auto">

          <!-- Success banner -->
          <div class="flex items-center gap-4 p-5 bg-green-50 border border-green-200 rounded-xl mb-6">
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
            <div>
              <h2 class="text-lg font-bold text-green-800">Job Created Successfully!</h2>
              <p class="text-sm text-green-700 mt-0.5">
                <strong>{{ createdJob?.name }}</strong> has been queued.
                {{ createdSummary?.total_tasks }} tasks are ready for workers to pick up.
              </p>
            </div>
          </div>

          <div class="grid grid-cols-3 gap-6">

            <!-- Left: Job card + task breakdown (2 cols) -->
            <div class="col-span-2 space-y-5">

              <!-- Job record card -->
              <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                  <h3 class="text-sm font-semibold text-gray-900">Job Record Created</h3>
                  <span class="px-2.5 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full">
                    PENDING
                  </span>
                </div>
                <div class="p-6">
                  <div class="grid grid-cols-2 gap-x-8 gap-y-4 text-sm">
                    <div>
                      <p class="text-xs text-gray-500 mb-0.5">Job ID</p>
                      <p class="font-bold text-gray-900">#{{ createdJob?.id }}</p>
                    </div>
                    <div>
                      <p class="text-xs text-gray-500 mb-0.5">Job Name</p>
                      <p class="font-semibold text-gray-900">{{ createdJob?.name }}</p>
                    </div>
                    <div>
                      <p class="text-xs text-gray-500 mb-0.5">Type</p>
                      <p class="font-semibold text-gray-900">{{ createdJob?.type }}</p>
                    </div>
                    <div>
                      <p class="text-xs text-gray-500 mb-0.5">Priority</p>
                      <p class="font-semibold" :class="priorityColorFor(createdJob?.priority)">
                        {{ priorityLabelFor(createdJob?.priority) }} ({{ createdJob?.priority }})
                      </p>
                    </div>
                    <div>
                      <p class="text-xs text-gray-500 mb-0.5">Total Tasks</p>
                      <p class="font-bold text-indigo-600 text-lg">{{ createdSummary?.total_tasks }}</p>
                    </div>
                    <div v-if="createdSummary?.total_records">
                      <p class="text-xs text-gray-500 mb-0.5">Total Records</p>
                      <p class="font-bold text-indigo-600 text-lg">
                        {{ createdSummary.total_records.toLocaleString() }}
                      </p>
                    </div>
                  </div>

                  <!-- Visual: 1 job → N tasks -->
                  <div class="mt-6 p-4 bg-gray-50 rounded-xl border border-gray-100">
                    <p class="text-xs font-semibold text-gray-600 mb-3 uppercase tracking-wide">
                      Partitioning Diagram
                    </p>
                    <div class="flex items-center gap-3 flex-wrap">
                      <!-- Job bubble -->
                      <div class="flex flex-col items-center">
                        <div class="w-14 h-14 bg-indigo-600 rounded-xl flex items-center justify-center shadow-md">
                          <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                          </svg>
                        </div>
                        <p class="text-xs font-bold text-gray-700 mt-1.5">1 Job</p>
                        <p class="text-[10px] text-gray-500">#{{ createdJob?.id }}</p>
                      </div>

                      <!-- Arrow -->
                      <div class="flex flex-col items-center gap-1 flex-1 min-w-[60px]">
                        <div class="flex items-center gap-1 w-full">
                          <div class="flex-1 h-0.5 bg-indigo-300"></div>
                          <svg class="w-4 h-4 text-indigo-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                              d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                              clip-rule="evenodd"/>
                          </svg>
                        </div>
                        <p class="text-[10px] text-indigo-500 font-medium">partitioned into</p>
                      </div>

                      <!-- Task bubbles (show up to 6, then +N more) -->
                      <div class="flex items-center gap-1.5 flex-wrap">
                        <div v-for="t in visibleTasks" :key="t.task_number"
                          class="flex flex-col items-center">
                          <div class="w-10 h-10 bg-yellow-400 rounded-lg flex items-center justify-center shadow-sm">
                            <span class="text-xs font-bold text-yellow-900">T{{ t.task_number }}</span>
                          </div>
                          <p class="text-[10px] text-gray-500 mt-1">{{ t.records_count }}r</p>
                        </div>
                        <div v-if="hiddenTaskCount > 0"
                          class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center">
                          <span class="text-xs font-bold text-gray-600">+{{ hiddenTaskCount }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Task breakdown table -->
              <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                  <h3 class="text-sm font-semibold text-gray-900">
                    Task Breakdown
                    <span class="ml-2 text-xs font-normal text-gray-500">
                      ({{ createdSummary?.total_tasks }} tasks created)
                    </span>
                  </h3>
                  <span class="text-xs text-gray-400">All tasks status: PENDING</span>
                </div>
                <div class="overflow-x-auto">
                  <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                      <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task #</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Records Range</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Count</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                      <tr v-for="task in createdSummary?.task_breakdown" :key="task.task_number"
                        class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3 font-semibold text-gray-900">
                          Task {{ task.task_number }}
                        </td>
                        <td class="px-5 py-3 text-gray-600 font-mono text-xs">
                          Record {{ task.start_index + 1 }} → {{ task.end_index + 1 }}
                        </td>
                        <td class="px-5 py-3">
                          <span class="font-semibold text-indigo-600">{{ task.records_count }}</span>
                          <span class="text-gray-400 text-xs ml-1">records</span>
                        </td>
                        <td class="px-5 py-3">
                          <span class="px-2.5 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full">
                            PENDING
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

            </div>

            <!-- Right: next steps + actions -->
            <div class="space-y-5">

              <!-- What happens next -->
              <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">What Happens Next?</h3>
                <ol class="space-y-4">
                  <li class="flex items-start gap-3">
                    <span class="w-6 h-6 bg-green-500 text-white text-xs font-bold rounded-full
                                 flex items-center justify-center flex-shrink-0 mt-0.5">✓</span>
                    <div>
                      <p class="text-xs font-semibold text-gray-800">Job Created</p>
                      <p class="text-xs text-gray-500 mt-0.5">
                        1 job + {{ createdSummary?.total_tasks }} tasks saved to database
                      </p>
                    </div>
                  </li>
                  <li class="flex items-start gap-3">
                    <span class="w-6 h-6 bg-yellow-400 text-white text-xs font-bold rounded-full
                                 flex items-center justify-center flex-shrink-0 mt-0.5">2</span>
                    <div>
                      <p class="text-xs font-semibold text-gray-800">Workers Claim Tasks</p>
                      <p class="text-xs text-gray-500 mt-0.5">
                        Each worker atomically picks the next pending task
                      </p>
                    </div>
                  </li>
                  <li class="flex items-start gap-3">
                    <span class="w-6 h-6 bg-gray-300 text-white text-xs font-bold rounded-full
                                 flex items-center justify-center flex-shrink-0 mt-0.5">3</span>
                    <div>
                      <p class="text-xs font-semibold text-gray-800">Parallel Execution</p>
                      <p class="text-xs text-gray-500 mt-0.5">
                        Multiple workers process tasks simultaneously
                      </p>
                    </div>
                  </li>
                  <li class="flex items-start gap-3">
                    <span class="w-6 h-6 bg-gray-300 text-white text-xs font-bold rounded-full
                                 flex items-center justify-center flex-shrink-0 mt-0.5">4</span>
                    <div>
                      <p class="text-xs font-semibold text-gray-800">Job Completes</p>
                      <p class="text-xs text-gray-500 mt-0.5">
                        When all tasks finish, job status → completed
                      </p>
                    </div>
                  </li>
                </ol>
              </div>

              <!-- Stats summary -->
              <div class="bg-indigo-600 rounded-xl p-5 text-white">
                <h3 class="text-sm font-semibold mb-4">Job Summary</h3>
                <div class="space-y-3">
                  <div class="flex justify-between items-center">
                    <span class="text-indigo-200 text-xs">Jobs created</span>
                    <span class="font-bold text-lg">1</span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-indigo-200 text-xs">Tasks created</span>
                    <span class="font-bold text-lg">{{ createdSummary?.total_tasks }}</span>
                  </div>
                  <div v-if="createdSummary?.total_records" class="flex justify-between items-center">
                    <span class="text-indigo-200 text-xs">Records to process</span>
                    <span class="font-bold text-lg">{{ createdSummary.total_records.toLocaleString() }}</span>
                  </div>
                  <div v-if="createdSummary?.records_per_task" class="flex justify-between items-center">
                    <span class="text-indigo-200 text-xs">Records / task</span>
                    <span class="font-bold text-lg">{{ createdSummary.records_per_task }}</span>
                  </div>
                  <div class="pt-2 border-t border-indigo-500 flex justify-between items-center">
                    <span class="text-indigo-200 text-xs">Status</span>
                    <span class="px-2.5 py-1 bg-yellow-400 text-yellow-900 text-xs font-bold rounded-full">
                      PENDING
                    </span>
                  </div>
                </div>
              </div>

              <!-- Action buttons -->
              <div class="space-y-3">
                <button @click="goToJobDetail"
                  class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-indigo-600
                         hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  </svg>
                  Monitor This Job
                </button>
                <button @click="goToMyJobs"
                  class="w-full flex items-center justify-center gap-2 px-4 py-3 border border-gray-300
                         text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                  </svg>
                  View All My Jobs
                </button>
                <button @click="submitAnother"
                  class="w-full flex items-center justify-center gap-2 px-4 py-3 border border-indigo-300
                         text-indigo-700 text-sm font-medium rounded-lg hover:bg-indigo-50 transition-colors">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                  </svg>
                  Submit Another Job
                </button>
              </div>

            </div>
          </div>
        </div>

      </main>
    </div>
  </div>
</template>


<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import UserSidebar from '@/components/UserSidebar.vue'
import api from '@/api'

const router = useRouter()

// ── View state: 'form' | 'confirm' ──────────────────────────────────────────
const view = ref('form')

// ── Form ─────────────────────────────────────────────────────────────────────
const form = ref({
  name: '',
  description: '',
  type: '',
  task_count: 10,
  priority: 5,
})

const selectedFiles = ref([])
const selectedUploadType = ref('csv')
const uploadedCSV = ref(null)
const uploadedJSON = ref(null)
const uploadedDataset = ref(null)
const uploadedImages = ref([])
const loading       = ref(false)
const error         = ref('')
const fileInput     = ref(null)
const csvInput      = ref(null)
const jsonInput     = ref(null)
const datasetInput  = ref(null)
const imageInput    = ref(null)

// ── Created job data (populated after submit) ─────────────────────────────────
const createdJob     = ref(null)   // the job object from API
const createdSummary = ref(null)   // the summary object from API

// ── Job types ─────────────────────────────────────────────────────────────────
const jobTypes = [
  { value: 'result_processing',  icon: '🎓', label: 'Result Processing' },
  { value: 'data_processing',    icon: '📊', label: 'Data Processing'   },
  { value: 'report_generation',  icon: '📄', label: 'Report Generation' },
  { value: 'image_processing',   icon: '🖼️', label: 'Image Processing'  },
  { value: 'email_batch',        icon: '📧', label: 'Email Batch'       },
  { value: 'video_conversion',   icon: '🎥', label: 'Video Conversion'  },
  { value: 'ml_training',        icon: '🤖', label: 'ML Training'       },
  { value: 'batch_processing',   icon: '⚡', label: 'Batch Processing'  },
]

const uploadTypes = [
  { value: 'csv',     icon: '📄', label: 'CSV' },
  { value: 'json',    icon: '📋', label: 'JSON' },
  { value: 'dataset', icon: '💾', label: 'Dataset' },
  { value: 'images',  icon: '🖼️', label: 'Images' },
]

const steps = [
  { title: 'Job Submission',    desc: 'Your job is queued in the system'    },
  { title: 'Task Partitioning', desc: 'Split into parallel tasks'           },
  { title: 'Worker Execution',  desc: 'Multiple workers process tasks'      },
  { title: 'Completion',        desc: 'Results aggregated and ready'        },
]

// ── Computed helpers ──────────────────────────────────────────────────────────
const selectedType = computed(() =>
  jobTypes.find(t => t.value === form.value.type)
)

const isFormValid = computed(() =>
  form.value.name.trim() && form.value.type && form.value.task_count > 0
)

// For result_processing: 100 records per task by default
const RECORDS_PER_TASK = 100
const totalRecords  = computed(() => form.value.task_count * RECORDS_PER_TASK)
const recordsPerTask = computed(() => RECORDS_PER_TASK)

const estimateSingle = computed(() => {
  const m = Math.ceil(form.value.task_count * 0.5)
  return m < 60 ? `${m} min` : `${Math.floor(m / 60)}h ${m % 60}m`
})
const estimateParallel = computed(() => {
  const m = Math.ceil(form.value.task_count * 0.5 / 10)
  return m < 60 ? `${m} min` : `${Math.floor(m / 60)}h ${m % 60}m`
})

const priorityLabel = computed(() => priorityLabelFor(form.value.priority))
const priorityColor = computed(() => priorityColorFor(form.value.priority))

function priorityLabelFor(p) {
  return { 3: 'Low', 5: 'Normal', 7: 'High', 10: 'Urgent' }[p] || 'Normal'
}
function priorityColorFor(p) {
  return { 3: 'text-green-600', 5: 'text-blue-600', 7: 'text-yellow-600', 10: 'text-red-600' }[p] || 'text-blue-600'
}

// Confirmation screen: show first 6 tasks, collapse the rest
const visibleTasks = computed(() =>
  (createdSummary.value?.task_breakdown ?? []).slice(0, 6)
)
const hiddenTaskCount = computed(() =>
  Math.max(0, (createdSummary.value?.task_breakdown?.length ?? 0) - 6)
)

// ── File upload ───────────────────────────────────────────────────────────────
function handleFileUpload(event) {
  selectedFiles.value = Array.from(event.target.files)
}

function handleCSVUpload(event) {
  const file = event.target.files[0]
  if (file) {
    uploadedCSV.value = file
    console.log('CSV uploaded:', file.name)
  }
}

function handleJSONUpload(event) {
  const file = event.target.files[0]
  if (file) {
    uploadedJSON.value = file
    console.log('JSON uploaded:', file.name)
  }
}

function handleDatasetUpload(event) {
  const file = event.target.files[0]
  if (file) {
    uploadedDataset.value = file
    console.log('Dataset uploaded:', file.name)
  }
}

function handleImageUpload(event) {
  uploadedImages.value = Array.from(event.target.files)
  console.log('Images uploaded:', uploadedImages.value.length)
}

function formatFileSize(bytes) {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

// ── Submit ────────────────────────────────────────────────────────────────────
async function submitJob() {
  if (!isFormValid.value) return

  // Validate file uploads for non-result_processing types
  if (form.value.type !== 'result_processing') {
    if (!uploadedCSV.value && !uploadedJSON.value && !uploadedDataset.value && uploadedImages.value.length === 0) {
      error.value = 'Please upload at least one data file before submitting.'
      return
    }

    // Validate file sizes
    const maxSize = 50 * 1024 * 1024 // 50MB for CSV/JSON
    const maxDatasetSize = 100 * 1024 * 1024 // 100MB for datasets
    const maxImageSize = 10 * 1024 * 1024 // 10MB per image

    if (uploadedCSV.value && uploadedCSV.value.size > maxSize) {
      error.value = 'CSV file size exceeds 50MB limit.'
      return
    }

    if (uploadedJSON.value && uploadedJSON.value.size > maxSize) {
      error.value = 'JSON file size exceeds 50MB limit.'
      return
    }

    if (uploadedDataset.value && uploadedDataset.value.size > maxDatasetSize) {
      error.value = 'Dataset file size exceeds 100MB limit.'
      return
    }

    if (uploadedImages.value.length > 0) {
      const oversizedImage = uploadedImages.value.find(img => img.size > maxImageSize)
      if (oversizedImage) {
        error.value = `Image "${oversizedImage.name}" exceeds 10MB limit.`
        return
      }
    }
  }

  loading.value = true
  error.value   = ''

  try {
    let response

    // Handle different upload types
    if (form.value.type === 'image_processing' && uploadedImages.value.length > 0) {
      // Image processing with multipart upload
      const fd = new FormData()
      fd.append('name',        form.value.name)
      fd.append('description', form.value.description)
      fd.append('type',        form.value.type)
      fd.append('priority',    form.value.priority)
      uploadedImages.value.forEach(f => fd.append('images[]', f))
      response = await api.post('/jobs', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    } else if (uploadedCSV.value || uploadedJSON.value || uploadedDataset.value) {
      // CSV, JSON, or Dataset upload with multipart
      const fd = new FormData()
      fd.append('name',        form.value.name)
      fd.append('description', form.value.description)
      fd.append('type',        form.value.type)
      fd.append('task_count',  form.value.task_count)
      fd.append('priority',    form.value.priority)
      
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
        name:        form.value.name,
        description: form.value.description,
        type:        form.value.type,
        task_count:  form.value.task_count,
        priority:    form.value.priority,
      })
    }

    // ── Store the created job + summary, then show confirmation screen ──
    createdJob.value     = response.data.job
    createdSummary.value = response.data.summary ?? buildFallbackSummary(response.data)
    view.value           = 'confirm'

  } catch (err) {
    console.error('Job submission error:', err)
    error.value = err.response?.data?.message || 'Failed to submit job. Please try again.'
  } finally {
    loading.value = false
  }
}

/**
 * Fallback summary builder in case the backend doesn't return a summary
 * (e.g. older API version or image_processing path).
 */
function buildFallbackSummary(data) {
  const job   = data.job
  const tasks = job?.tasks ?? []
  return {
    job_id:          job?.id,
    job_name:        job?.name,
    job_type:        job?.type,
    status:          job?.status,
    priority:        job?.priority,
    total_tasks:     job?.total_tasks ?? tasks.length,
    total_records:   job?.total_tasks * RECORDS_PER_TASK,
    records_per_task: RECORDS_PER_TASK,
    operations:      [],
    task_breakdown:  tasks.map((t, i) => ({
      task_number:   i + 1,
      start_index:   t.payload?.start_index ?? i * RECORDS_PER_TASK,
      end_index:     t.payload?.end_index   ?? (i + 1) * RECORDS_PER_TASK - 1,
      records_count: t.payload?.records_count ?? RECORDS_PER_TASK,
      status:        t.status ?? 'pending',
    })),
  }
}

// ── Navigation helpers ────────────────────────────────────────────────────────
function goToJobDetail() {
  router.push(`/jobs/${createdJob.value?.id}`)
}
function goToMyJobs() {
  router.push('/my-jobs')
}
function submitAnother() {
  // Reset form and go back to form view
  form.value     = { name: '', description: '', type: '', task_count: 10, priority: 5 }
  selectedFiles.value = []
  selectedUploadType.value = 'csv'
  uploadedCSV.value = null
  uploadedJSON.value = null
  uploadedDataset.value = null
  uploadedImages.value = []
  error.value    = ''
  createdJob.value    = null
  createdSummary.value = null
  view.value     = 'form'
}
</script>
