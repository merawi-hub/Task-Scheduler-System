<template>
  <div class="card">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Submit New Job</h2>
    
    <form @submit.prevent="handleSubmit" class="space-y-4">
      <!-- Job Name -->
      <div>
        <label for="name" class="label">Job Name *</label>
        <input
          id="name"
          v-model="form.name"
          type="text"
          class="input"
          :class="{ 'border-red-500': errors.name }"
          placeholder="e.g., Batch Process Images"
          required
        />
        <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
      </div>

      <!-- Job Type -->
      <div>
        <label for="type" class="label">Job Type *</label>
        <select
          id="type"
          v-model="form.type"
          class="input"
          :class="{ 'border-red-500': errors.type }"
          required
        >
          <option value="">Select a type</option>
          <option value="image_process">Image Processing</option>
          <option value="csv_aggregate">CSV Aggregation</option>
          <option value="data_transform">Data Transformation</option>
          <option value="video_encode">Video Encoding</option>
          <option value="report_generate">Report Generation</option>
          <option value="email_batch">Email Batch Send</option>
          <option value="custom">Custom Task</option>
        </select>
        <p v-if="errors.type" class="mt-1 text-sm text-red-600">{{ errors.type }}</p>
      </div>

      <!-- Description -->
      <div>
        <label for="description" class="label">Description</label>
        <textarea
          id="description"
          v-model="form.description"
          rows="3"
          class="input"
          placeholder="Optional description of what this job does..."
        ></textarea>
      </div>

      <!-- Task Count -->
      <div>
        <label for="task_count" class="label">Number of Tasks *</label>
        <input
          id="task_count"
          v-model.number="form.task_count"
          type="number"
          min="1"
          max="10000"
          class="input"
          :class="{ 'border-red-500': errors.task_count }"
          placeholder="e.g., 100"
          required
        />
        <p class="mt-1 text-sm text-gray-500">
          The job will be split into {{ form.task_count || 0 }} parallel tasks (1-10,000)
        </p>
        <p v-if="errors.task_count" class="mt-1 text-sm text-red-600">{{ errors.task_count }}</p>
      </div>

      <!-- Priority -->
      <div>
        <label for="priority" class="label">Priority</label>
        <div class="flex items-center space-x-4">
          <input
            id="priority"
            v-model.number="form.priority"
            type="range"
            min="1"
            max="10"
            class="flex-1"
          />
          <span class="text-lg font-semibold text-gray-700 w-12 text-center">
            {{ form.priority }}
          </span>
        </div>
        <p class="mt-1 text-sm text-gray-500">
          Higher priority jobs are processed first (1 = lowest, 10 = highest)
        </p>
      </div>

      <!-- Submit Button -->
      <div class="flex items-center justify-end space-x-3 pt-4">
        <button
          type="button"
          @click="resetForm"
          class="btn btn-secondary"
          :disabled="submitting"
        >
          Reset
        </button>
        <button
          type="submit"
          class="btn btn-primary"
          :disabled="submitting"
        >
          <span v-if="submitting" class="flex items-center">
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Submitting...
          </span>
          <span v-else>Submit Job</span>
        </button>
      </div>

      <!-- Success Message -->
      <div v-if="successMessage" class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
        <p class="text-sm text-green-800">{{ successMessage }}</p>
      </div>

      <!-- Error Message -->
      <div v-if="errorMessage" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
        <p class="text-sm text-red-800">{{ errorMessage }}</p>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useJobsStore } from '@/stores/jobsStore'
import { useRouter } from 'vue-router'

const jobsStore = useJobsStore()
const router = useRouter()

const emit = defineEmits(['job-submitted'])

const form = reactive({
  name: '',
  type: '',
  description: '',
  task_count: 10,
  priority: 5
})

const errors = reactive({
  name: '',
  type: '',
  task_count: ''
})

const submitting = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

function validateForm() {
  let isValid = true
  
  // Reset errors
  errors.name = ''
  errors.type = ''
  errors.task_count = ''

  // Validate name
  if (!form.name || form.name.trim().length < 3) {
    errors.name = 'Job name must be at least 3 characters'
    isValid = false
  }

  // Validate type
  if (!form.type) {
    errors.type = 'Please select a job type'
    isValid = false
  }

  // Validate task count
  if (!form.task_count || form.task_count < 1) {
    errors.task_count = 'Task count must be at least 1'
    isValid = false
  } else if (form.task_count > 10000) {
    errors.task_count = 'Task count cannot exceed 10,000'
    isValid = false
  }

  return isValid
}

async function handleSubmit() {
  successMessage.value = ''
  errorMessage.value = ''

  if (!validateForm()) {
    return
  }

  submitting.value = true

  try {
    const jobData = {
      name: form.name.trim(),
      type: form.type,
      description: form.description?.trim() || null,
      task_count: form.task_count,
      priority: form.priority
    }

    const newJob = await jobsStore.submitJob(jobData)
    
    successMessage.value = `Job "${newJob.name}" submitted successfully with ${newJob.total_tasks} tasks!`
    
    emit('job-submitted', newJob)
    
    // Reset form after 2 seconds and navigate to job detail
    setTimeout(() => {
      resetForm()
      router.push(`/jobs/${newJob.id}`)
    }, 2000)
    
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Failed to submit job. Please try again.'
    console.error('Job submission error:', error)
  } finally {
    submitting.value = false
  }
}

function resetForm() {
  form.name = ''
  form.type = ''
  form.description = ''
  form.task_count = 10
  form.priority = 5
  
  errors.name = ''
  errors.type = ''
  errors.task_count = ''
  
  successMessage.value = ''
  errorMessage.value = ''
}
</script>
