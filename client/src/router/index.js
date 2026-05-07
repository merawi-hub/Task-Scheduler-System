import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import Login from '@/views/Login.vue'
import Register from '@/views/Register.vue'
import UserDashboard from '@/views/UserDashboard.vue'
import MyJobs from '@/views/MyJobs.vue'
import JobDetailView from '@/views/JobDetailView.vue'
import AdminDashboard from '@/views/admin/AdminDashboard.vue'
import AllJobs from '@/views/admin/AllJobs.vue'
import AllUsers from '@/views/admin/AllUsers.vue'
import WorkerManagement from '@/views/admin/WorkerManagement.vue'
import AdminLayout from '@/components/AdminLayout.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/login'
    },
    {
      path: '/login',
      name: 'login',
      component: Login,
      meta: {
        title: 'Login - Task Scheduler',
        requiresGuest: true
      }
    },
    {
      path: '/register',
      name: 'register',
      component: Register,
      meta: {
        title: 'Register - Task Scheduler',
        requiresGuest: true
      }
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: UserDashboard,
      meta: {
        title: 'Dashboard - Task Scheduler',
        requiresAuth: true
      }
    },
    {
      path: '/my-jobs',
      name: 'my-jobs',
      component: MyJobs,
      meta: {
        title: 'My Jobs - Task Scheduler',
        requiresAuth: true
      }
    },
    {
      path: '/jobs/:id',
      name: 'job-detail',
      component: JobDetailView,
      meta: {
        title: 'Job Details - Task Scheduler',
        requiresAuth: true
      }
    },
    {
      path: '/admin',
      component: AdminLayout,
      meta: {
        requiresAuth: true,
        requiresAdmin: true
      },
      children: [
        {
          path: '',
          name: 'admin-dashboard',
          component: AdminDashboard,
          meta: {
            title: 'Admin Dashboard - Task Scheduler'
          }
        },
        {
          path: 'jobs',
          name: 'admin-jobs',
          component: AllJobs,
          meta: {
            title: 'All Jobs - Admin - Task Scheduler'
          }
        },
        {
          path: 'users',
          name: 'admin-users',
          component: AllUsers,
          meta: {
            title: 'All Users - Admin - Task Scheduler'
          }
        },
        {
          path: 'workers',
          name: 'admin-workers',
          component: WorkerManagement,
          meta: {
            title: 'Worker Management - Admin - Task Scheduler'
          }
        }
      ]
    },
    {
      path: '/:pathMatch(.*)*',
      redirect: '/login'
    }
  ],
})

// Authentication guard
router.beforeEach(async (to, from) => {
  const authStore = useAuthStore()
  
  // Update page title
  document.title = to.meta.title || 'Task Scheduler'
  
  // If route requires authentication
  if (to.meta.requiresAuth) {
    if (!authStore.isAuthenticated) {
      // Not logged in, redirect to login
      return '/login'
    }
    
    // If user data not loaded, fetch it
    if (!authStore.user) {
      const result = await authStore.fetchUser()
      if (!result.success) {
        // Token invalid, redirect to login
        return '/login'
      }
    }
    
    // Check admin requirement
    if (to.meta.requiresAdmin && !authStore.isAdmin) {
      // Not an admin, redirect to user dashboard
      return '/dashboard'
    }
  }
  
  // If route requires guest (login/register pages)
  if (to.meta.requiresGuest && authStore.isAuthenticated) {
    // Already logged in, redirect based on role
    if (authStore.isAdmin) {
      return '/admin'
    } else {
      return '/dashboard'
    }
  }
  
  // Allow navigation
  return true
})

export default router

