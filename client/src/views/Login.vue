<template>
  <div class="min-h-screen flex">
    <!-- Left Side - Login Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center px-12 py-12 bg-white">
      <div class="max-w-md w-full">
        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-[2.5rem] font-bold text-[#1e293b] mb-3 leading-tight">Login</h1>
          <p class="text-[#64748b] text-base">Welcome back! Please sign in to your account</p>
        </div>

        <!-- Error Message -->
        <div v-if="errorMessage" class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
          {{ errorMessage }}
        </div>

        <!-- Login Form -->
        <form @submit.prevent="handleLogin" class="space-y-5">
          <!-- Email Field -->
          <div>
            <label for="email" class="block text-sm font-medium text-[#1e293b] mb-2">
              Email Address
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                </svg>
              </div>
              <input
                id="email"
                v-model="email"
                type="email"
                required
                class="block w-full pl-11 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 placeholder-gray-400 text-sm transition-colors"
                placeholder="admin@taskflow.com"
              />
            </div>
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

          <!-- Remember Me & Forgot Password -->
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <input
                id="remember-me"
                v-model="rememberMe"
                type="checkbox"
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded cursor-pointer"
              />
              <label for="remember-me" class="ml-2 block text-sm text-[#64748b] cursor-pointer select-none">
                Remember me
              </label>
            </div>
            <div class="text-sm">
              <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500 transition-colors">
                Forgot password?
              </a>
            </div>
          </div>

          <!-- Sign In Button -->
          <button
            type="submit"
            :disabled="loading"
            class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
          >
            <span v-if="loading">Signing in...</span>
            <span v-else>Sign In</span>
          </button>

          <!-- Sign Up Link -->
          <div class="text-center pt-2">
            <p class="text-sm text-[#64748b]">
              Don't have an account?
              <router-link to="/register" class="font-medium text-indigo-600 hover:text-indigo-500 transition-colors">
                Sign up
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

        <!-- 3D Server Illustration (Matching the image exactly) -->
        <div class="w-full max-w-md">
          <svg viewBox="0 0 400 400" class="w-full h-auto">
            <!-- Main Server Tower -->
            <g>
              <!-- Bottom Server -->
              <g>
                <polygon points="200,240 140,265 260,265" fill="#4338ca" opacity="0.9"/>
                <polygon points="200,240 260,265 260,215 200,190" fill="#5b21b6" opacity="0.95"/>
                <polygon points="200,240 140,265 140,215 200,190" fill="#4c1d95" opacity="0.9"/>
                <!-- Server Details -->
                <rect x="185" y="220" width="30" height="4" fill="#7c3aed" rx="2"/>
                <rect x="185" y="228" width="30" height="4" fill="#7c3aed" rx="2"/>
                <circle cx="175" cy="224" r="3" fill="#a78bfa"/>
              </g>
              
              <!-- Middle Server -->
              <g>
                <polygon points="200,190 140,215 260,215" fill="#5b21b6" opacity="0.95"/>
                <polygon points="200,190 260,215 260,165 200,140" fill="#6d28d9"/>
                <polygon points="200,190 140,215 140,165 200,140" fill="#5b21b6" opacity="0.95"/>
                <!-- Server Details -->
                <rect x="185" y="170" width="30" height="4" fill="#8b5cf6" rx="2"/>
                <rect x="185" y="178" width="30" height="4" fill="#8b5cf6" rx="2"/>
                <circle cx="175" cy="174" r="3" fill="#c4b5fd"/>
              </g>
              
              <!-- Top Server -->
              <g>
                <polygon points="200,140 140,165 260,165" fill="#6d28d9"/>
                <polygon points="200,140 260,165 260,115 200,90" fill="#7c3aed"/>
                <polygon points="200,140 140,165 140,115 200,90" fill="#6d28d9"/>
                <!-- Server Details -->
                <rect x="185" y="120" width="30" height="4" fill="#a78bfa" rx="2"/>
                <rect x="185" y="128" width="30" height="4" fill="#a78bfa" rx="2"/>
                <circle cx="175" cy="124" r="3" fill="#ddd6fe"/>
              </g>
            </g>

            <!-- Side Nodes -->
            <g opacity="0.85">
              <!-- Left Node -->
              <polygon points="100,260 75,272 125,272" fill="#4338ca"/>
              <polygon points="100,260 125,272 125,248 100,236" fill="#5b21b6"/>
              <polygon points="100,260 75,272 75,248 100,236" fill="#4c1d95"/>
              
              <!-- Right Node -->
              <polygon points="300,260 275,272 325,272" fill="#4338ca"/>
              <polygon points="300,260 325,272 325,248 300,236" fill="#5b21b6"/>
              <polygon points="300,260 275,272 275,248 300,236" fill="#4c1d95"/>
              
              <!-- Back Left Node -->
              <polygon points="120,220 100,230 140,230" fill="#5b21b6"/>
              <polygon points="120,220 140,230 140,210 120,200" fill="#6d28d9"/>
              <polygon points="120,220 100,230 100,210 120,200" fill="#5b21b6"/>
              
              <!-- Back Right Node -->
              <polygon points="280,220 260,230 300,230" fill="#5b21b6"/>
              <polygon points="280,220 300,230 300,210 280,200" fill="#6d28d9"/>
              <polygon points="280,220 260,230 260,210 280,200" fill="#5b21b6"/>
            </g>

            <!-- Connection Lines -->
            <g stroke="#8b5cf6" stroke-width="2" opacity="0.5" fill="none">
              <line x1="125" y1="260" x2="140" y2="240" stroke-dasharray="4,4">
                <animate attributeName="stroke-dashoffset" from="0" to="8" dur="1s" repeatCount="indefinite"/>
              </line>
              <line x1="275" y1="260" x2="260" y2="240" stroke-dasharray="4,4">
                <animate attributeName="stroke-dashoffset" from="0" to="8" dur="1s" repeatCount="indefinite"/>
              </line>
              <line x1="140" y1="220" x2="140" y2="215" stroke-dasharray="4,4">
                <animate attributeName="stroke-dashoffset" from="0" to="8" dur="1.2s" repeatCount="indefinite"/>
              </line>
              <line x1="260" y1="220" x2="260" y2="215" stroke-dasharray="4,4">
                <animate attributeName="stroke-dashoffset" from="0" to="8" dur="1.2s" repeatCount="indefinite"/>
              </line>
            </g>

            <!-- Base Shadow -->
            <ellipse cx="200" cy="285" rx="100" ry="15" fill="#000" opacity="0.15"/>
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

const email = ref('')
const password = ref('')
const rememberMe = ref(false)
const showPassword = ref(false)
const loading = ref(false)
const errorMessage = ref('')
const validationErrors = ref({})

async function handleLogin() {
  loading.value = true
  errorMessage.value = ''
  validationErrors.value = {}

  const result = await authStore.login(email.value, password.value)

  if (result.success) {
    if (authStore.isAdmin) {
      router.push('/admin')
    } else {
      router.push('/dashboard')
    }
  } else {
    errorMessage.value = result.message || 'Login failed. Please check your credentials.'
    validationErrors.value = result.errors || {}
    
    // Show specific validation errors
    if (validationErrors.value.email) {
      errorMessage.value = validationErrors.value.email[0]
    } else if (validationErrors.value.password) {
      errorMessage.value = validationErrors.value.password[0]
    }
  }

  loading.value = false
}
</script>
