<template>
  <div class="min-h-screen flex">
    <!-- Left Side - Register Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center px-12 py-12 bg-white">
      <div class="max-w-md w-full">
        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-[2.5rem] font-bold text-[#1e293b] mb-3 leading-tight">Register</h1>
          <p class="text-[#64748b] text-base">Create your account to get started</p>
        </div>

        <!-- Error Message -->
        <div v-if="errorMessage" class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
          {{ errorMessage }}
        </div>

        <!-- Register Form -->
        <form @submit.prevent="handleRegister" class="space-y-5">
          <!-- Full Name Field -->
          <div>
            <label for="name" class="block text-sm font-medium text-[#1e293b] mb-2">
              Full Name
            </label>
            <input
              id="name"
              v-model="name"
              type="text"
              required
              class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 placeholder-gray-400 text-sm transition-colors"
              placeholder="John Doe"
            />
          </div>

          <!-- Email Field -->
          <div>
            <label for="email" class="block text-sm font-medium text-[#1e293b] mb-2">
              Email Address
            </label>
            <input
              id="email"
              v-model="email"
              type="email"
              required
              class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 placeholder-gray-400 text-sm transition-colors"
              placeholder="john@example.com"
            />
          </div>

          <!-- Password Field -->
          <div>
            <label for="password" class="block text-sm font-medium text-[#1e293b] mb-2">
              Password
            </label>
            <div class="relative">
              <input
                id="password"
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                required
                class="block w-full pr-11 pl-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 text-sm transition-colors"
                placeholder="••••••••••••"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-3.5 flex items-center"
              >
                <svg class="h-5 w-5 text-gray-400 hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path v-if="!showPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path v-if="!showPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  <path v-if="showPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Confirm Password Field -->
          <div>
            <label for="password-confirm" class="block text-sm font-medium text-[#1e293b] mb-2">
              Confirm Password
            </label>
            <div class="relative">
              <input
                id="password-confirm"
                v-model="passwordConfirmation"
                :type="showPasswordConfirm ? 'text' : 'password'"
                required
                class="block w-full pr-11 pl-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 text-sm transition-colors"
                placeholder="••••••••••••"
              />
              <button
                type="button"
                @click="showPasswordConfirm = !showPasswordConfirm"
                class="absolute inset-y-0 right-0 pr-3.5 flex items-center"
              >
                <svg class="h-5 w-5 text-gray-400 hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path v-if="!showPasswordConfirm" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path v-if="!showPasswordConfirm" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  <path v-if="showPasswordConfirm" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Terms & Conditions -->
          <div class="flex items-start pt-1">
            <div class="flex items-center h-5">
              <input
                id="terms"
                v-model="agreeToTerms"
                type="checkbox"
                required
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded cursor-pointer"
              />
            </div>
            <div class="ml-3 text-sm">
              <label for="terms" class="text-[#64748b] cursor-pointer select-none">
                I agree to the
                <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500 transition-colors">Terms of Service</a>
                and
                <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500 transition-colors">Privacy Policy</a>
              </label>
            </div>
          </div>

          <!-- Create Account Button -->
          <button
            type="submit"
            :disabled="loading"
            class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
          >
            <span v-if="loading">Creating Account...</span>
            <span v-else>Create Account</span>
          </button>

          <!-- Sign In Link -->
          <div class="text-center pt-2">
            <p class="text-sm text-[#64748b]">
              Already have an account?
              <router-link to="/login" class="font-medium text-indigo-600 hover:text-indigo-500 transition-colors">
                Sign in
              </router-link>
            </p>
          </div>
        </form>
      </div>
    </div>

    <!-- Right Side - Branding (Exact match to image) -->
    <div class="hidden lg:flex lg:w-1/2 bg-[#1e1b4b] items-center justify-center p-12 relative overflow-hidden">
      <!-- Grid Pattern Background -->
      <div class="absolute inset-0 opacity-[0.03]">
        <div class="absolute inset-0" style="background-image: linear-gradient(#fff 1px, transparent 1px), linear-gradient(90deg, #fff 1px, transparent 1px); background-size: 50px 50px;"></div>
      </div>

      <div class="relative z-10 flex flex-col items-center">
        <!-- Logo and Brand -->
        <div class="flex items-center gap-4 mb-3">
          <!-- Hexagon Logo -->
          <svg width="64" height="64" viewBox="0 0 64 64" class="flex-shrink-0">
            <defs>
              <linearGradient id="hexGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:#6366f1;stop-opacity:1" />
                <stop offset="100%" style="stop-color:#8b5cf6;stop-opacity:1" />
              </linearGradient>
            </defs>
            <polygon points="32,4 56,18 56,46 32,60 8,46 8,18" fill="url(#hexGrad)" stroke="#7c3aed" stroke-width="1.5"/>
            <circle cx="32" cy="32" r="12" fill="#ec4899"/>
          </svg>
          
          <div class="text-left">
            <h2 class="text-[2.5rem] font-bold text-white leading-none">TaskFlow</h2>
          </div>
        </div>
        
        <p class="text-[#a5b4fc] text-base mb-16">Distributed Task Scheduler</p>

        <!-- 3D Server Illustration (Matching the register image) -->
        <div class="w-full max-w-md">
          <svg viewBox="0 0 400 350" class="w-full h-auto">
            <!-- Main Central Server Stack -->
            <g>
              <!-- Bottom Layer -->
              <g>
                <polygon points="200,220 150,240 250,240" fill="#4338ca" opacity="0.9"/>
                <polygon points="200,220 250,240 250,195 200,175" fill="#5b21b6" opacity="0.95"/>
                <polygon points="200,220 150,240 150,195 200,175" fill="#4c1d95" opacity="0.9"/>
                <rect x="188" y="202" width="24" height="3" fill="#7c3aed" rx="1.5"/>
                <rect x="188" y="208" width="24" height="3" fill="#7c3aed" rx="1.5"/>
                <circle cx="180" cy="205" r="2.5" fill="#a78bfa"/>
              </g>
              
              <!-- Middle Layer -->
              <g>
                <polygon points="200,175 150,195 250,195" fill="#5b21b6" opacity="0.95"/>
                <polygon points="200,175 250,195 250,150 200,130" fill="#6d28d9"/>
                <polygon points="200,175 150,195 150,150 200,130" fill="#5b21b6" opacity="0.95"/>
                <rect x="188" y="157" width="24" height="3" fill="#8b5cf6" rx="1.5"/>
                <rect x="188" y="163" width="24" height="3" fill="#8b5cf6" rx="1.5"/>
                <circle cx="180" cy="160" r="2.5" fill="#c4b5fd"/>
              </g>
              
              <!-- Top Layer -->
              <g>
                <polygon points="200,130 150,150 250,150" fill="#6d28d9"/>
                <polygon points="200,130 250,150 250,105 200,85" fill="#7c3aed"/>
                <polygon points="200,130 150,150 150,105 200,85" fill="#6d28d9"/>
                <rect x="188" y="112" width="24" height="3" fill="#a78bfa" rx="1.5"/>
                <rect x="188" y="118" width="24" height="3" fill="#a78bfa" rx="1.5"/>
                <circle cx="180" cy="115" r="2.5" fill="#ddd6fe"/>
              </g>
            </g>

            <!-- Left Front Node -->
            <g opacity="0.85">
              <polygon points="110,250 85,262 135,262" fill="#4338ca"/>
              <polygon points="110,250 135,262 135,238 110,226" fill="#5b21b6"/>
              <polygon points="110,250 85,262 85,238 110,226" fill="#4c1d95"/>
              <circle cx="105" cy="244" r="2" fill="#8b5cf6"/>
            </g>

            <!-- Right Front Node -->
            <g opacity="0.85">
              <polygon points="290,250 265,262 315,262" fill="#4338ca"/>
              <polygon points="290,250 315,262 315,238 290,226" fill="#5b21b6"/>
              <polygon points="290,250 265,262 265,238 290,226" fill="#4c1d95"/>
              <circle cx="295" cy="244" r="2" fill="#8b5cf6"/>
            </g>

            <!-- Back Left Node -->
            <g opacity="0.75">
              <polygon points="130,210 110,220 150,220" fill="#5b21b6"/>
              <polygon points="130,210 150,220 150,195 130,185" fill="#6d28d9"/>
              <polygon points="130,210 110,220 110,195 130,185" fill="#5b21b6"/>
            </g>

            <!-- Back Right Node -->
            <g opacity="0.75">
              <polygon points="270,210 250,220 290,220" fill="#5b21b6"/>
              <polygon points="270,210 290,220 290,195 270,185" fill="#6d28d9"/>
              <polygon points="270,210 250,220 250,195 270,185" fill="#5b21b6"/>
            </g>

            <!-- Connection Lines -->
            <g stroke="#8b5cf6" stroke-width="2" opacity="0.5" fill="none">
              <line x1="135" y1="250" x2="150" y2="230" stroke-dasharray="4,4">
                <animate attributeName="stroke-dashoffset" from="0" to="8" dur="1s" repeatCount="indefinite"/>
              </line>
              <line x1="265" y1="250" x2="250" y2="230" stroke-dasharray="4,4">
                <animate attributeName="stroke-dashoffset" from="0" to="8" dur="1s" repeatCount="indefinite"/>
              </line>
              <line x1="150" y1="210" x2="150" y2="195" stroke-dasharray="4,4">
                <animate attributeName="stroke-dashoffset" from="0" to="8" dur="1.2s" repeatCount="indefinite"/>
              </line>
              <line x1="250" y1="210" x2="250" y2="195" stroke-dasharray="4,4">
                <animate attributeName="stroke-dashoffset" from="0" to="8" dur="1.2s" repeatCount="indefinite"/>
              </line>
            </g>

            <!-- Base Shadow -->
            <ellipse cx="200" cy="270" rx="110" ry="12" fill="#000" opacity="0.15"/>
          </svg>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const router = useRouter()
const authStore = useAuthStore()

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const agreeToTerms = ref(false)
const showPassword = ref(false)
const showPasswordConfirm = ref(false)
const loading = ref(false)
const errorMessage = ref('')
const validationErrors = ref({})

async function handleRegister() {
  // Clear previous errors
  errorMessage.value = ''
  validationErrors.value = {}

  // Client-side validation
  if (password.value.length < 8) {
    errorMessage.value = 'Password must be at least 8 characters'
    return
  }

  if (password.value !== passwordConfirmation.value) {
    errorMessage.value = 'Passwords do not match'
    return
  }

  if (!agreeToTerms.value) {
    errorMessage.value = 'You must agree to the Terms of Service and Privacy Policy'
    return
  }

  loading.value = true

  const result = await authStore.register(
    name.value,
    email.value,
    password.value,
    passwordConfirmation.value
  )

  if (result.success) {
    router.push('/dashboard')
  } else {
    errorMessage.value = result.message
    validationErrors.value = result.errors || {}
    
    // Show specific validation errors
    if (validationErrors.value.email) {
      errorMessage.value = validationErrors.value.email[0]
    } else if (validationErrors.value.password) {
      errorMessage.value = validationErrors.value.password[0]
    } else if (validationErrors.value.name) {
      errorMessage.value = validationErrors.value.name[0]
    }
  }

  loading.value = false
}
</script>
