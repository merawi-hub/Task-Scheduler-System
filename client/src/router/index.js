import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import Login from '@/views/Login.vue'
import Register from '@/views/Register.vue'
import UserDashboard from '@/views/UserDashboard.vue'
import MyJobs from '@/views/MyJobs.vue'
import SubmitJob from '@/views/SubmitJob.vue'
import JobMonitoring from '@/views/JobMonitoring.vue'
import JobDetailView from '@/views/JobDetailView.vue'
import SystemFlowView from '@/views/SystemFlowView.vue'
import Settings from '@/views/Settings.vue'
import Notifications from '@/views/Notifications.vue'
import Profile from '@/views/Profile.vue'
import HelpCenter from '@/views/HelpCenter.vue'
import AdminDashboard from '@/views/admin/AdminDashboard.vue'
import AllJobs from '@/views/admin/AllJobs.vue'
import AllUsers from '@/views/admin/AllUsers.vue'
import WorkerManagement from '@/views/admin/WorkerManagement.vue'
import AdminMonitoring from '@/views/admin/AdminMonitoring.vue'
import AdminLogs from '@/views/admin/AdminLogs.vue'
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
      path: '/submit-job',
      name: 'submit-job',
      component: SubmitJob,
      meta: {
        title: 'Submit Job - Task Scheduler',
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
      path: '/jobs/:id/monitoring',
      name: 'job-monitoring',
      component: JobMonitoring,
      meta: {
        title: 'Job Monitoring - Task Scheduler',
        requiresAuth: true
      }
    },
    {
      path: '/notifications',
      name: 'notifications',
      component: Notifications,
      meta: {
        title: 'Notifications - Task Scheduler',
        requiresAuth: true
      }
    },
    {
      path: '/profile',
      name: 'profile',
      component: Profile,
      meta: {
        title: 'Profile - Task Scheduler',
        requiresAuth: true
      }
    },
    {
      path: '/help',
      name: 'help',
      component: HelpCenter,
      meta: {
        title: 'Help Center - Task Scheduler',
        requiresAuth: true
      }
    },
    {
      path: '/settings',
      name: 'settings',
      component: Settings,
      meta: {
        title: 'Settings - Task Scheduler',
        requiresAuth: true
      }
    },
    {
      path: '/system-flow',
      name: 'system-flow',
      component: SystemFlowView,
      meta: {
        title: 'System Flow - Task Scheduler',
        requiresAuth: true
      }
    },
    // Legacy route for backward compatibility
    {
      path: '/job-detail/:id',
      redirect: to => `/jobs/${to.params.id}`
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
        },
        {
          path: 'monitoring',
          name: 'admin-monitoring',
          component: AdminMonitoring,
          meta: {
            title: 'Monitoring - Admin - Task Scheduler'
          }
        },
        {
          path: 'logs',
          name: 'admin-logs',
          component: AdminLogs,
          meta: {
            title: 'Task Logs - Admin - Task Scheduler'
          }
        },
        // Alias routes for sidebar items that map to existing views
        {
          path: 'tasks',
          name: 'admin-tasks',
          component: AllJobs,
          meta: { title: 'Tasks - Admin - Task Scheduler' }
        },
        {
          path: 'queues',
          name: 'admin-queues',
          component: AdminMonitoring,
          meta: { title: 'Queues - Admin - Task Scheduler' }
        },
        {
          path: 'schedulers',
          name: 'admin-schedulers',
          component: AdminMonitoring,
          meta: { title: 'Schedulers - Admin - Task Scheduler' }
        },
        {
          path: 'settings',
          name: 'admin-settings',
          component: AllUsers,
          meta: { title: 'Settings - Admin - Task Scheduler' }
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

