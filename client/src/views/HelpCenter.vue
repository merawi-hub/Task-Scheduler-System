<template>
  <div class="flex h-screen bg-gray-50">
    <UserSidebar />

    <div class="flex-1 ml-64 overflow-auto">
      <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="px-8 py-4">
          <h1 class="text-2xl font-bold text-gray-900">Help Center</h1>
          <p class="text-sm text-gray-500 mt-1">Find answers and get support</p>
        </div>
      </header>

      <main class="p-8">
        <!-- Search -->
        <div class="max-w-2xl mx-auto mb-12">
          <div class="relative">
            <input type="text" placeholder="Search for help..." class="w-full pl-12 pr-4 py-4 border border-gray-300 rounded-xl text-base focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm" />
            <svg class="w-6 h-6 text-gray-400 absolute left-4 top-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>

        <!-- Quick Links -->
        <div class="grid grid-cols-3 gap-6 mb-12">
          <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Documentation</h3>
            <p class="text-sm text-gray-500">Complete guides and API references</p>
          </div>

          <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Video Tutorials</h3>
            <p class="text-sm text-gray-500">Step-by-step video guides</p>
          </div>

          <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-shadow cursor-pointer">
            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Contact Support</h3>
            <p class="text-sm text-gray-500">Get help from our support team</p>
          </div>
        </div>

        <!-- FAQ -->
        <div class="max-w-4xl mx-auto">
          <h2 class="text-2xl font-bold text-gray-900 mb-6">Frequently Asked Questions</h2>
          <div class="space-y-4">
            <div v-for="faq in faqs" :key="faq.id" class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
              <button @click="toggleFaq(faq.id)" class="w-full flex items-center justify-between text-left">
                <h3 class="text-lg font-semibold text-gray-900">{{ faq.question }}</h3>
                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': faq.open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div v-if="faq.open" class="mt-4 text-sm text-gray-600">
                {{ faq.answer }}
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import UserSidebar from '@/components/UserSidebar.vue'

const faqs = ref([
  {
    id: 1,
    question: 'How do I submit a new job?',
    answer: 'Click on "Submit Job" in the sidebar, fill in the job details, and click submit. Your job will be queued and processed by available workers.',
    open: false
  },
  {
    id: 2,
    question: 'How can I monitor my job progress?',
    answer: 'Go to "My Jobs" to see all your jobs. Click "Monitor" on any job to see real-time progress, task status, and worker assignments.',
    open: false
  },
  {
    id: 3,
    question: 'What happens if a job fails?',
    answer: 'Failed jobs can be retried from the job details page. The system will automatically retry failed tasks based on your retry configuration.',
    open: false
  },
  {
    id: 4,
    question: 'How do I cancel a running job?',
    answer: 'Go to "My Jobs", find the running job, and click the "Cancel" button. This will stop all running tasks for that job.',
    open: false
  },
  {
    id: 5,
    question: 'Can I schedule jobs for later?',
    answer: 'Yes, when submitting a job, you can set a scheduled start time. The job will automatically start at the specified time.',
    open: false
  }
])

function toggleFaq(id) {
  const faq = faqs.value.find(f => f.id === id)
  if (faq) faq.open = !faq.open
}
</script>
