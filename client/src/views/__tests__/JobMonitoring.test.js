import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createRouter, createMemoryHistory } from 'vue-router'
import JobMonitoring from '../JobMonitoring.vue'
import api from '@/api'

// Mock the API module
vi.mock('@/api', () => ({
  default: {
    get: vi.fn(),
    delete: vi.fn()
  }
}))

// Mock UserSidebar component
vi.mock('@/components/UserSidebar.vue', () => ({
  default: {
    name: 'UserSidebar',
    template: '<div class="user-sidebar-mock"></div>'
  }
}))

describe('JobMonitoring - Task 5: Navigation and Error Handling', () => {
  let router
  let wrapper

  beforeEach(() => {
    // Create a fresh router for each test
    router = createRouter({
      history: createMemoryHistory(),
      routes: [
        {
          path: '/my-jobs',
          name: 'my-jobs',
          component: { template: '<div>My Jobs</div>' }
        },
        {
          path: '/jobs/:id/monitoring',
          name: 'job-monitoring',
          component: JobMonitoring
        }
      ]
    })

    // Clear all mocks
    vi.clearAllMocks()
  })

  afterEach(() => {
    if (wrapper) {
      wrapper.unmount()
    }
  })

  describe('Navigation from Jobs Page with job ID as route parameter', () => {
    it('should load job data when valid job ID is provided in route', async () => {
      const mockJob = {
        id: 123,
        name: 'Test Job',
        status: 'running',
        total_tasks: 10,
        completed_tasks: 5,
        created_at: '2024-01-01T00:00:00Z'
      }

      const mockTasks = [
        { id: 1, task_index: 0, status: 'done' },
        { id: 2, task_index: 1, status: 'running' }
      ]

      api.get.mockImplementation((url) => {
        if (url === '/jobs/123') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/123/tasks') {
          return Promise.resolve({ data: { tasks: mockTasks } })
        }
        return Promise.reject(new Error('Unknown endpoint'))
      })

      await router.push('/jobs/123/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      expect(api.get).toHaveBeenCalledWith('/jobs/123')
      expect(api.get).toHaveBeenCalledWith('/jobs/123/tasks')
      expect(wrapper.vm.job).toEqual(mockJob)
      expect(wrapper.vm.taskList).toEqual(mockTasks)
    })

    it('should display job name in breadcrumb navigation', async () => {
      const mockJob = {
        id: 123,
        name: 'Data Processing Job',
        status: 'running',
        total_tasks: 10
      }

      api.get.mockImplementation((url) => {
        if (url === '/jobs/123') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/123/tasks') {
          return Promise.resolve({ data: { tasks: [] } })
        }
      })

      await router.push('/jobs/123/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      const breadcrumb = wrapper.find('nav')
      expect(breadcrumb.text()).toContain('Jobs')
      expect(breadcrumb.text()).toContain('Data Processing Job')
    })
  })

  describe('Error handling for invalid or non-existent job IDs', () => {
    it('should display error message when job ID is missing', async () => {
      await router.push('/jobs//monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      expect(wrapper.vm.pageError).toContain('No job ID provided')
      expect(wrapper.text()).toContain('Job Not Found')
    })

    it('should display error message when job ID is not numeric', async () => {
      await router.push('/jobs/invalid-id/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      expect(wrapper.vm.pageError).toContain('Invalid job ID')
      expect(wrapper.vm.pageError).toContain('must be numeric')
    })

    it('should display error message when job does not exist (404)', async () => {
      api.get.mockRejectedValue({
        response: {
          status: 404,
          data: { message: 'Job not found' }
        }
      })

      await router.push('/jobs/999/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      expect(wrapper.vm.pageError).toContain('does not exist')
      expect(wrapper.text()).toContain('Job Not Found')
    })

    it('should display error message when user lacks permission (403)', async () => {
      api.get.mockRejectedValue({
        response: {
          status: 403,
          data: { message: 'Forbidden' }
        }
      })

      await router.push('/jobs/123/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      expect(wrapper.vm.pageError).toContain("don't have permission")
    })

    it('should display error message when session expired (401)', async () => {
      api.get.mockRejectedValue({
        response: {
          status: 401,
          data: { message: 'Unauthorized' }
        }
      })

      await router.push('/jobs/123/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      expect(wrapper.vm.pageError).toContain('session has expired')
    })
  })

  describe('User-friendly error message with back button', () => {
    it('should display back button when error occurs', async () => {
      api.get.mockRejectedValue({
        response: {
          status: 404,
          data: { message: 'Job not found' }
        }
      })

      await router.push('/jobs/999/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      const backButton = wrapper.findAll('button').find(btn => 
        btn.text().includes('Back to Jobs')
      )
      expect(backButton).toBeDefined()
      expect(backButton.text()).toContain('Back to Jobs')
    })

    it('should navigate to Jobs Page when back button is clicked', async () => {
      api.get.mockRejectedValue({
        response: {
          status: 404
        }
      })

      await router.push('/jobs/999/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      const backButton = wrapper.findAll('button').find(btn => 
        btn.text().includes('Back to Jobs Page')
      )
      
      expect(backButton).toBeDefined()
      await backButton.trigger('click')
      await flushPromises()

      expect(router.currentRoute.value.path).toBe('/my-jobs')
    })
  })

  describe('Breadcrumb navigation showing Jobs > Job Name', () => {
    it('should display breadcrumb with Jobs link and job name', async () => {
      const mockJob = {
        id: 456,
        name: 'Image Processing Task',
        status: 'completed',
        total_tasks: 20
      }

      api.get.mockImplementation((url) => {
        if (url === '/jobs/456') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/456/tasks') {
          return Promise.resolve({ data: { tasks: [] } })
        }
      })

      await router.push('/jobs/456/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      const breadcrumb = wrapper.find('nav')
      const jobsLink = breadcrumb.find('a[href="/my-jobs"]')
      
      expect(jobsLink.exists()).toBe(true)
      expect(jobsLink.text()).toBe('Jobs')
      expect(breadcrumb.text()).toContain('Image Processing Task')
    })

    it('should show "Loading..." in breadcrumb when job data is not yet loaded', async () => {
      api.get.mockImplementation(() => new Promise(() => {})) // Never resolves

      await router.push('/jobs/123/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      const breadcrumb = wrapper.find('nav')
      expect(breadcrumb.text()).toContain('Loading...')
    })

    it('should make Jobs link clickable and navigate to /my-jobs', async () => {
      const mockJob = {
        id: 789,
        name: 'Test Job',
        status: 'running',
        total_tasks: 5
      }

      api.get.mockImplementation((url) => {
        if (url === '/jobs/789') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/789/tasks') {
          return Promise.resolve({ data: { tasks: [] } })
        }
      })

      await router.push('/jobs/789/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      const jobsLink = wrapper.find('a[href="/my-jobs"]')
      await jobsLink.trigger('click')
      await flushPromises()

      expect(router.currentRoute.value.path).toBe('/my-jobs')
    })
  })

  describe('Back button in header', () => {
    it('should display back button in header', async () => {
      const mockJob = {
        id: 123,
        name: 'Test Job',
        status: 'running',
        total_tasks: 10
      }

      api.get.mockImplementation((url) => {
        if (url === '/jobs/123') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/123/tasks') {
          return Promise.resolve({ data: { tasks: [] } })
        }
      })

      await router.push('/jobs/123/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      const backButton = wrapper.findAll('button').find(btn => 
        btn.text().includes('Back to Jobs')
      )
      
      expect(backButton).toBeDefined()
      expect(backButton.text()).toContain('Back to Jobs')
    })

    it('should navigate to Jobs Page when header back button is clicked', async () => {
      const mockJob = {
        id: 123,
        name: 'Test Job',
        status: 'running',
        total_tasks: 10
      }

      api.get.mockImplementation((url) => {
        if (url === '/jobs/123') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/123/tasks') {
          return Promise.resolve({ data: { tasks: [] } })
        }
      })

      await router.push('/jobs/123/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      const backButton = wrapper.findAll('button').find(btn => 
        btn.text().includes('Back to Jobs')
      )
      
      await backButton.trigger('click')
      await flushPromises()

      expect(router.currentRoute.value.path).toBe('/my-jobs')
    })
  })
})

describe('JobMonitoring - Task 6: Job Overview Card Enhancement', () => {
  let router
  let wrapper

  beforeEach(() => {
    router = createRouter({
      history: createMemoryHistory(),
      routes: [
        {
          path: '/my-jobs',
          name: 'my-jobs',
          component: { template: '<div>My Jobs</div>' }
        },
        {
          path: '/jobs/:id/monitoring',
          name: 'job-monitoring',
          component: JobMonitoring
        }
      ]
    })

    vi.clearAllMocks()
  })

  afterEach(() => {
    if (wrapper) {
      wrapper.unmount()
    }
  })

  describe('Job Overview Card Display (Requirement 7.1)', () => {
    it('should display Job Overview Card with Job Name, Status, Started Time, and Estimated Completion', async () => {
      const mockJob = {
        id: 123,
        name: 'Data Processing Job',
        status: 'running',
        total_tasks: 100,
        completed_tasks: 50,
        started_at: '2024-01-15T10:30:00Z',
        created_at: '2024-01-15T10:00:00Z'
      }

      api.get.mockImplementation((url) => {
        if (url === '/jobs/123') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/123/tasks') {
          return Promise.resolve({ data: { tasks: [] } })
        }
      })

      await router.push('/jobs/123/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      // Check for Job Overview Card
      const overviewCard = wrapper.findAll('.bg-white').find(card => 
        card.text().includes('Job Overview')
      )
      
      expect(overviewCard).toBeDefined()
      expect(overviewCard.text()).toContain('Job Name')
      expect(overviewCard.text()).toContain('Data Processing Job')
      expect(overviewCard.text()).toContain('Status')
      expect(overviewCard.text()).toContain('Started Time')
      expect(overviewCard.text()).toContain('Estimated Completion')
    })
  })

  describe('Not Started State (Requirement 7.2)', () => {
    it('should display "Not Started" when job has not started', async () => {
      const mockJob = {
        id: 124,
        name: 'Pending Job',
        status: 'pending',
        total_tasks: 50,
        completed_tasks: 0,
        started_at: null,
        created_at: '2024-01-15T10:00:00Z'
      }

      api.get.mockImplementation((url) => {
        if (url === '/jobs/124') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/124/tasks') {
          return Promise.resolve({ data: { tasks: [] } })
        }
      })

      await router.push('/jobs/124/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      const overviewCard = wrapper.findAll('.bg-white').find(card => 
        card.text().includes('Job Overview')
      )
      
      expect(overviewCard.text()).toContain('Not Started')
    })

    it('should display formatted date when job has started', async () => {
      const mockJob = {
        id: 125,
        name: 'Running Job',
        status: 'running',
        total_tasks: 50,
        completed_tasks: 10,
        started_at: '2024-01-15T10:30:00Z',
        created_at: '2024-01-15T10:00:00Z'
      }

      api.get.mockImplementation((url) => {
        if (url === '/jobs/125') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/125/tasks') {
          return Promise.resolve({ data: { tasks: [] } })
        }
      })

      await router.push('/jobs/125/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      const overviewCard = wrapper.findAll('.bg-white').find(card => 
        card.text().includes('Job Overview')
      )
      
      expect(overviewCard.text()).not.toContain('Not Started')
      // Should contain a formatted date (checking for common date format elements)
      expect(overviewCard.text()).toMatch(/Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec/)
    })
  })

  describe('Estimated Completion Display (Requirement 7.3)', () => {
    it('should display "Calculating..." when job has no estimated completion time', async () => {
      const mockJob = {
        id: 126,
        name: 'New Running Job',
        status: 'running',
        total_tasks: 100,
        completed_tasks: 0,
        started_at: '2024-01-15T10:30:00Z',
        created_at: '2024-01-15T10:00:00Z'
      }

      api.get.mockImplementation((url) => {
        if (url === '/jobs/126') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/126/tasks') {
          return Promise.resolve({ data: { tasks: [] } })
        }
      })

      await router.push('/jobs/126/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      expect(wrapper.vm.estCompletion).toBe('Calculating...')
    })

    it('should display estimated completion time when available', async () => {
      const mockJob = {
        id: 127,
        name: 'Running Job',
        status: 'running',
        total_tasks: 100,
        completed_tasks: 50,
        started_at: new Date(Date.now() - 3600000).toISOString(), // Started 1 hour ago
        created_at: '2024-01-15T10:00:00Z'
      }

      api.get.mockImplementation((url) => {
        if (url === '/jobs/127') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/127/tasks') {
          return Promise.resolve({ data: { tasks: [] } })
        }
      })

      await router.push('/jobs/127/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      expect(wrapper.vm.estCompletion).not.toBe('Calculating...')
      expect(wrapper.vm.estCompletion).not.toBe('—')
    })

    it('should display completion date when job is completed', async () => {
      const mockJob = {
        id: 128,
        name: 'Completed Job',
        status: 'completed',
        total_tasks: 100,
        completed_tasks: 100,
        started_at: '2024-01-15T10:00:00Z',
        completed_at: '2024-01-15T12:00:00Z',
        created_at: '2024-01-15T09:00:00Z'
      }

      api.get.mockImplementation((url) => {
        if (url === '/jobs/128') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/128/tasks') {
          return Promise.resolve({ data: { tasks: [] } })
        }
      })

      await router.push('/jobs/128/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      expect(wrapper.vm.estCompletion).toMatch(/Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec/)
    })
  })

  describe('Status Visual Styling (Requirement 7.5)', () => {
    const statusTests = [
      { status: 'completed', expectedClass: 'bg-green-100 text-green-700', dotClass: 'bg-green-500' },
      { status: 'running', expectedClass: 'bg-blue-100 text-blue-700', dotClass: 'bg-blue-500' },
      { status: 'failed', expectedClass: 'bg-red-100 text-red-700', dotClass: 'bg-red-500' },
      { status: 'pending', expectedClass: 'bg-yellow-100 text-yellow-700', dotClass: 'bg-yellow-500' },
      { status: 'cancelled', expectedClass: 'bg-gray-100 text-gray-600', dotClass: 'bg-gray-400' }
    ]

    statusTests.forEach(({ status, expectedClass, dotClass }) => {
      it(`should apply ${status} styling with correct colors`, async () => {
        const mockJob = {
          id: 129,
          name: 'Test Job',
          status: status,
          total_tasks: 100,
          completed_tasks: status === 'completed' ? 100 : 50,
          started_at: '2024-01-15T10:00:00Z',
          created_at: '2024-01-15T09:00:00Z'
        }

        api.get.mockImplementation((url) => {
          if (url === '/jobs/129') {
            return Promise.resolve({ data: { job: mockJob } })
          }
          if (url === '/jobs/129/tasks') {
            return Promise.resolve({ data: { tasks: [] } })
          }
        })

        await router.push('/jobs/129/monitoring')
        wrapper = mount(JobMonitoring, {
          global: {
            plugins: [router]
          }
        })

        await flushPromises()

        expect(wrapper.vm.statusBadgeClass(status)).toBe(expectedClass)
        expect(wrapper.vm.statusDotClass(status)).toBe(dotClass)
      })
    })
  })

  describe('5-Second Polling (Requirement 7.4)', () => {
    beforeEach(() => {
      vi.useFakeTimers()
    })

    afterEach(() => {
      vi.restoreAllMocks()
    })

    it('should poll every 5 seconds when job is running', async () => {
      const mockJob = {
        id: 130,
        name: 'Running Job',
        status: 'running',
        total_tasks: 100,
        completed_tasks: 50,
        started_at: '2024-01-15T10:00:00Z',
        created_at: '2024-01-15T09:00:00Z'
      }

      api.get.mockImplementation((url) => {
        if (url === '/jobs/130') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/130/tasks') {
          return Promise.resolve({ data: { tasks: [] } })
        }
      })

      await router.push('/jobs/130/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      // Initial load
      expect(api.get).toHaveBeenCalledTimes(2) // job + tasks

      // Advance timer by 5 seconds
      vi.advanceTimersByTime(5000)
      await flushPromises()

      // Should have called API again
      expect(api.get).toHaveBeenCalledTimes(4) // job + tasks again

      // Advance another 5 seconds
      vi.advanceTimersByTime(5000)
      await flushPromises()

      expect(api.get).toHaveBeenCalledTimes(6) // job + tasks again
    })

    it('should poll every 5 seconds when job is pending', async () => {
      const mockJob = {
        id: 131,
        name: 'Pending Job',
        status: 'pending',
        total_tasks: 100,
        completed_tasks: 0,
        started_at: null,
        created_at: '2024-01-15T09:00:00Z'
      }

      api.get.mockImplementation((url) => {
        if (url === '/jobs/131') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/131/tasks') {
          return Promise.resolve({ data: { tasks: [] } })
        }
      })

      await router.push('/jobs/131/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      expect(api.get).toHaveBeenCalledTimes(2)

      vi.advanceTimersByTime(5000)
      await flushPromises()

      expect(api.get).toHaveBeenCalledTimes(4)
    })

    it('should not poll when job is completed', async () => {
      const mockJob = {
        id: 132,
        name: 'Completed Job',
        status: 'completed',
        total_tasks: 100,
        completed_tasks: 100,
        started_at: '2024-01-15T10:00:00Z',
        completed_at: '2024-01-15T12:00:00Z',
        created_at: '2024-01-15T09:00:00Z'
      }

      api.get.mockImplementation((url) => {
        if (url === '/jobs/132') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/132/tasks') {
          return Promise.resolve({ data: { tasks: [] } })
        }
      })

      await router.push('/jobs/132/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      expect(api.get).toHaveBeenCalledTimes(2)

      vi.advanceTimersByTime(5000)
      await flushPromises()

      // Should not poll again for completed jobs
      expect(api.get).toHaveBeenCalledTimes(2)
    })
  })
})


describe('JobMonitoring - Task 8: Task Status Grid', () => {
  let router
  let wrapper

  beforeEach(() => {
    router = createRouter({
      history: createMemoryHistory(),
      routes: [
        {
          path: '/my-jobs',
          name: 'my-jobs',
          component: { template: '<div>My Jobs</div>' }
        },
        {
          path: '/jobs/:id/monitoring',
          name: 'job-monitoring',
          component: JobMonitoring
        }
      ]
    })

    vi.clearAllMocks()
  })

  afterEach(() => {
    if (wrapper) {
      wrapper.unmount()
    }
  })

  describe('Task Status Grid Display (Requirement 9.1)', () => {
    it('should display all 5 status categories: Pending, Running, Completed, Failed, and Retried', async () => {
      const mockJob = {
        id: 200,
        name: 'Test Job',
        status: 'running',
        total_tasks: 100,
        completed_tasks: 40,
        started_at: '2024-01-15T10:00:00Z',
        created_at: '2024-01-15T09:00:00Z'
      }

      const mockTasks = [
        { id: 1, task_index: 0, status: 'pending', retry_count: 0 },
        { id: 2, task_index: 1, status: 'running', retry_count: 0 },
        { id: 3, task_index: 2, status: 'done', retry_count: 0 },
        { id: 4, task_index: 3, status: 'failed', retry_count: 0 },
        { id: 5, task_index: 4, status: 'done', retry_count: 2 } // Retried task
      ]

      api.get.mockImplementation((url) => {
        if (url === '/jobs/200') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/200/tasks') {
          return Promise.resolve({ data: { tasks: mockTasks } })
        }
      })

      await router.push('/jobs/200/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      const statusGrid = wrapper.findAll('.bg-white').find(card => 
        card.text().includes('Task Status Grid')
      )
      
      expect(statusGrid).toBeDefined()
      expect(statusGrid.text()).toContain('Pending')
      expect(statusGrid.text()).toContain('Running')
      expect(statusGrid.text()).toContain('Completed')
      expect(statusGrid.text()).toContain('Failed')
      expect(statusGrid.text()).toContain('Retried')
    })

    it('should calculate correct counts for each status category', async () => {
      const mockJob = {
        id: 201,
        name: 'Test Job',
        status: 'running',
        total_tasks: 100,
        started_at: '2024-01-15T10:00:00Z',
        created_at: '2024-01-15T09:00:00Z'
      }

      const mockTasks = [
        { id: 1, task_index: 0, status: 'pending', retry_count: 0 },
        { id: 2, task_index: 1, status: 'pending', retry_count: 0 },
        { id: 3, task_index: 2, status: 'running', retry_count: 0 },
        { id: 4, task_index: 3, status: 'running', retry_count: 0 },
        { id: 5, task_index: 4, status: 'running', retry_count: 0 },
        { id: 6, task_index: 5, status: 'done', retry_count: 0 },
        { id: 7, task_index: 6, status: 'done', retry_count: 0 },
        { id: 8, task_index: 7, status: 'done', retry_count: 0 },
        { id: 9, task_index: 8, status: 'done', retry_count: 0 },
        { id: 10, task_index: 9, status: 'failed', retry_count: 0 },
        { id: 11, task_index: 10, status: 'done', retry_count: 1 }, // Retried
        { id: 12, task_index: 11, status: 'done', retry_count: 2 }, // Retried
        { id: 13, task_index: 12, status: 'running', retry_count: 1 } // Retried
      ]

      api.get.mockImplementation((url) => {
        if (url === '/jobs/201') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/201/tasks') {
          return Promise.resolve({ data: { tasks: mockTasks } })
        }
      })

      await router.push('/jobs/201/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      expect(wrapper.vm.stats.pending).toBe(2)
      expect(wrapper.vm.stats.running).toBe(4)
      expect(wrapper.vm.stats.done).toBe(6)
      expect(wrapper.vm.stats.failed).toBe(1)
      expect(wrapper.vm.stats.retried).toBe(3) // Tasks with retry_count > 0
    })
  })

  describe('Task Status Counts from Backend API (Requirement 9.2)', () => {
    it('should fetch task data from /jobs/:id/tasks endpoint', async () => {
      const mockJob = {
        id: 202,
        name: 'Test Job',
        status: 'running',
        total_tasks: 10,
        started_at: '2024-01-15T10:00:00Z',
        created_at: '2024-01-15T09:00:00Z'
      }

      const mockTasks = [
        { id: 1, task_index: 0, status: 'done', retry_count: 0 }
      ]

      api.get.mockImplementation((url) => {
        if (url === '/jobs/202') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/202/tasks') {
          return Promise.resolve({ data: { tasks: mockTasks } })
        }
      })

      await router.push('/jobs/202/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      expect(api.get).toHaveBeenCalledWith('/jobs/202/tasks')
      expect(wrapper.vm.taskList).toEqual(mockTasks)
    })
  })

  describe('5-Second Update Interval (Requirement 9.3)', () => {
    beforeEach(() => {
      vi.useFakeTimers()
    })

    afterEach(() => {
      vi.restoreAllMocks()
    })

    it('should update Task Status Grid every 5 seconds while job is running', async () => {
      let taskUpdateCount = 0
      const mockJob = {
        id: 203,
        name: 'Running Job',
        status: 'running',
        total_tasks: 100,
        started_at: '2024-01-15T10:00:00Z',
        created_at: '2024-01-15T09:00:00Z'
      }

      api.get.mockImplementation((url) => {
        if (url === '/jobs/203') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/203/tasks') {
          taskUpdateCount++
          // Simulate task progress
          const mockTasks = [
            { id: 1, task_index: 0, status: taskUpdateCount > 1 ? 'done' : 'running', retry_count: 0 },
            { id: 2, task_index: 1, status: 'pending', retry_count: 0 }
          ]
          return Promise.resolve({ data: { tasks: mockTasks } })
        }
      })

      await router.push('/jobs/203/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      // Initial state
      expect(wrapper.vm.stats.running).toBe(1)
      expect(wrapper.vm.stats.done).toBe(0)

      // Advance 5 seconds
      vi.advanceTimersByTime(5000)
      await flushPromises()

      // Task status should be updated
      expect(wrapper.vm.stats.running).toBe(0)
      expect(wrapper.vm.stats.done).toBe(1)
    })
  })

  describe('Color-Coded Styling (Requirement 9.4)', () => {
    it('should apply distinct color styling for each status category', async () => {
      const mockJob = {
        id: 204,
        name: 'Test Job',
        status: 'running',
        total_tasks: 5,
        started_at: '2024-01-15T10:00:00Z',
        created_at: '2024-01-15T09:00:00Z'
      }

      const mockTasks = [
        { id: 1, task_index: 0, status: 'pending', retry_count: 0 },
        { id: 2, task_index: 1, status: 'running', retry_count: 0 },
        { id: 3, task_index: 2, status: 'done', retry_count: 0 },
        { id: 4, task_index: 3, status: 'failed', retry_count: 0 },
        { id: 5, task_index: 4, status: 'done', retry_count: 1 }
      ]

      api.get.mockImplementation((url) => {
        if (url === '/jobs/204') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/204/tasks') {
          return Promise.resolve({ data: { tasks: mockTasks } })
        }
      })

      await router.push('/jobs/204/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      const statusGrid = wrapper.findAll('.bg-white').find(card => 
        card.text().includes('Task Status Grid')
      )

      // Check for color-coded styling
      expect(statusGrid.html()).toContain('text-gray-600') // Pending
      expect(statusGrid.html()).toContain('bg-gray-400')
      expect(statusGrid.html()).toContain('text-blue-600') // Running
      expect(statusGrid.html()).toContain('bg-blue-500')
      expect(statusGrid.html()).toContain('text-green-600') // Completed
      expect(statusGrid.html()).toContain('bg-green-500')
      expect(statusGrid.html()).toContain('text-red-600') // Failed
      expect(statusGrid.html()).toContain('bg-red-500')
      expect(statusGrid.html()).toContain('text-orange-600') // Retried
      expect(statusGrid.html()).toContain('bg-orange-500')
    })
  })

  describe('Display Zero Counts (Requirement 9.5)', () => {
    it('should display all status categories even when count is 0', async () => {
      const mockJob = {
        id: 205,
        name: 'Test Job',
        status: 'running',
        total_tasks: 1,
        started_at: '2024-01-15T10:00:00Z',
        created_at: '2024-01-15T09:00:00Z'
      }

      const mockTasks = [
        { id: 1, task_index: 0, status: 'running', retry_count: 0 }
      ]

      api.get.mockImplementation((url) => {
        if (url === '/jobs/205') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/205/tasks') {
          return Promise.resolve({ data: { tasks: mockTasks } })
        }
      })

      await router.push('/jobs/205/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      const statusGrid = wrapper.findAll('.bg-white').find(card => 
        card.text().includes('Task Status Grid')
      )

      // All categories should be visible
      expect(statusGrid.text()).toContain('Pending')
      expect(statusGrid.text()).toContain('Running')
      expect(statusGrid.text()).toContain('Completed')
      expect(statusGrid.text()).toContain('Failed')
      expect(statusGrid.text()).toContain('Retried')

      // Check that zero counts are displayed
      expect(wrapper.vm.stats.pending).toBe(0)
      expect(wrapper.vm.stats.running).toBe(1)
      expect(wrapper.vm.stats.done).toBe(0)
      expect(wrapper.vm.stats.failed).toBe(0)
      expect(wrapper.vm.stats.retried).toBe(0)
    })

    it('should show 0 for all categories when no tasks exist', async () => {
      const mockJob = {
        id: 206,
        name: 'Empty Job',
        status: 'pending',
        total_tasks: 0,
        started_at: null,
        created_at: '2024-01-15T09:00:00Z'
      }

      api.get.mockImplementation((url) => {
        if (url === '/jobs/206') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/206/tasks') {
          return Promise.resolve({ data: { tasks: [] } })
        }
      })

      await router.push('/jobs/206/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      expect(wrapper.vm.stats.pending).toBe(0)
      expect(wrapper.vm.stats.running).toBe(0)
      expect(wrapper.vm.stats.done).toBe(0)
      expect(wrapper.vm.stats.failed).toBe(0)
      expect(wrapper.vm.stats.retried).toBe(0)
    })
  })
})

describe('JobMonitoring - Task 9: Worker Assignment Display', () => {
  let router
  let wrapper

  beforeEach(() => {
    router = createRouter({
      history: createMemoryHistory(),
      routes: [
        {
          path: '/my-jobs',
          name: 'my-jobs',
          component: { template: '<div>My Jobs</div>' }
        },
        {
          path: '/jobs/:id/monitoring',
          name: 'job-monitoring',
          component: JobMonitoring
        }
      ]
    })

    vi.clearAllMocks()
  })

  afterEach(() => {
    if (wrapper) {
      wrapper.unmount()
    }
  })

  describe('Worker Assignment Section Display (Requirement 10.1)', () => {
    it('should display Worker Assignments section showing active worker-to-task mappings', async () => {
      const mockJob = {
        id: 300,
        name: 'Test Job',
        status: 'running',
        total_tasks: 10,
        started_at: '2024-01-15T10:00:00Z',
        created_at: '2024-01-15T09:00:00Z'
      }

      const mockTasks = [
        {
          id: 1,
          task_index: 0,
          status: 'running',
          worker: {
            worker_key: 'Worker-1',
            hostname: 'worker-node-1'
          }
        },
        {
          id: 2,
          task_index: 1,
          status: 'assigned',
          worker: {
            worker_key: 'Worker-2',
            hostname: 'worker-node-2'
          }
        }
      ]

      api.get.mockImplementation((url) => {
        if (url === '/jobs/300') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/300/tasks') {
          return Promise.resolve({ data: { tasks: mockTasks } })
        }
      })

      await router.push('/jobs/300/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      const workerSection = wrapper.findAll('.bg-white').find(card => 
        card.text().includes('Worker Assignments')
      )
      
      expect(workerSection).toBeDefined()
      expect(workerSection.text()).toContain('Worker Assignments')
    })
  })

  describe('Worker Assignment Data from Backend API (Requirement 10.2)', () => {
    it('should fetch worker assignment data from Backend_API via /jobs/:id/tasks endpoint', async () => {
      const mockJob = {
        id: 301,
        name: 'Test Job',
        status: 'running',
        total_tasks: 5,
        started_at: '2024-01-15T10:00:00Z',
        created_at: '2024-01-15T09:00:00Z'
      }

      const mockTasks = [
        {
          id: 1,
          task_index: 0,
          status: 'running',
          worker: {
            worker_key: 'Worker-1',
            hostname: 'worker-node-1'
          }
        }
      ]

      api.get.mockImplementation((url) => {
        if (url === '/jobs/301') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/301/tasks') {
          return Promise.resolve({ data: { tasks: mockTasks } })
        }
      })

      await router.push('/jobs/301/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      // Verify API was called
      expect(api.get).toHaveBeenCalledWith('/jobs/301/tasks')
      
      // Verify worker data is extracted
      expect(wrapper.vm.workerAssignments).toHaveLength(1)
      expect(wrapper.vm.workerAssignments[0].workerKey).toBe('Worker-1')
      expect(wrapper.vm.workerAssignments[0].hostname).toBe('worker-node-1')
    })

    it('should extract worker assignments from tasks with assigned or running status', async () => {
      const mockJob = {
        id: 302,
        name: 'Test Job',
        status: 'running',
        total_tasks: 10,
        started_at: '2024-01-15T10:00:00Z',
        created_at: '2024-01-15T09:00:00Z'
      }

      const mockTasks = [
        {
          id: 1,
          task_index: 0,
          status: 'running',
          worker: { worker_key: 'Worker-1', hostname: 'node-1' }
        },
        {
          id: 2,
          task_index: 1,
          status: 'assigned',
          worker: { worker_key: 'Worker-2', hostname: 'node-2' }
        },
        {
          id: 3,
          task_index: 2,
          status: 'done',
          worker: { worker_key: 'Worker-3', hostname: 'node-3' }
        },
        {
          id: 4,
          task_index: 3,
          status: 'pending',
          worker: null
        }
      ]

      api.get.mockImplementation((url) => {
        if (url === '/jobs/302') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/302/tasks') {
          return Promise.resolve({ data: { tasks: mockTasks } })
        }
      })

      await router.push('/jobs/302/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      // Should only include running and assigned tasks
      expect(wrapper.vm.workerAssignments).toHaveLength(2)
      expect(wrapper.vm.workerAssignments[0].status).toBe('running')
      expect(wrapper.vm.workerAssignments[1].status).toBe('assigned')
    })
  })

  describe('Worker-to-Task Mapping Format (Requirement 10.3)', () => {
    it('should display each mapping in format "Worker-{ID} → Task {Task_Number}"', async () => {
      const mockJob = {
        id: 303,
        name: 'Test Job',
        status: 'running',
        total_tasks: 10,
        started_at: '2024-01-15T10:00:00Z',
        created_at: '2024-01-15T09:00:00Z'
      }

      const mockTasks = [
        {
          id: 1,
          task_index: 0,
          status: 'running',
          worker: {
            worker_key: 'Worker-1',
            hostname: 'worker-node-1'
          }
        },
        {
          id: 2,
          task_index: 4,
          status: 'assigned',
          worker: {
            worker_key: 'Worker-2',
            hostname: 'worker-node-2'
          }
        }
      ]

      api.get.mockImplementation((url) => {
        if (url === '/jobs/303') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/303/tasks') {
          return Promise.resolve({ data: { tasks: mockTasks } })
        }
      })

      await router.push('/jobs/303/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      const workerSection = wrapper.findAll('.bg-white').find(card => 
        card.text().includes('Worker Assignments')
      )

      // Check for correct format: "Worker-{ID} → Task {Task_Number}"
      expect(workerSection.text()).toContain('Worker-1')
      expect(workerSection.text()).toContain('→')
      expect(workerSection.text()).toContain('Task 1') // task_index 0 = Task 1
      expect(workerSection.text()).toContain('Worker-2')
      expect(workerSection.text()).toContain('Task 5') // task_index 4 = Task 5
    })

    it('should correctly map task_index to task number (1-indexed)', async () => {
      const mockJob = {
        id: 304,
        name: 'Test Job',
        status: 'running',
        total_tasks: 10,
        started_at: '2024-01-15T10:00:00Z',
        created_at: '2024-01-15T09:00:00Z'
      }

      const mockTasks = [
        {
          id: 1,
          task_index: 0,
          status: 'running',
          worker: { worker_key: 'Worker-1', hostname: 'node-1' }
        },
        {
          id: 2,
          task_index: 9,
          status: 'running',
          worker: { worker_key: 'Worker-2', hostname: 'node-2' }
        }
      ]

      api.get.mockImplementation((url) => {
        if (url === '/jobs/304') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/304/tasks') {
          return Promise.resolve({ data: { tasks: mockTasks } })
        }
      })

      await router.push('/jobs/304/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      // Verify computed property correctly converts task_index to task_number
      expect(wrapper.vm.workerAssignments[0].taskNumber).toBe(1) // task_index 0 → 1
      expect(wrapper.vm.workerAssignments[1].taskNumber).toBe(10) // task_index 9 → 10
    })
  })

  describe('5-Second Update Interval (Requirement 10.4)', () => {
    beforeEach(() => {
      vi.useFakeTimers()
    })

    afterEach(() => {
      vi.restoreAllMocks()
    })

    it('should update Worker Assignment section every 5 seconds while job is running', async () => {
      let updateCount = 0
      const mockJob = {
        id: 305,
        name: 'Running Job',
        status: 'running',
        total_tasks: 10,
        started_at: '2024-01-15T10:00:00Z',
        created_at: '2024-01-15T09:00:00Z'
      }

      api.get.mockImplementation((url) => {
        if (url === '/jobs/305') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/305/tasks') {
          updateCount++
          // Simulate changing worker assignments
          const mockTasks = updateCount === 1 ? [
            {
              id: 1,
              task_index: 0,
              status: 'running',
              worker: { worker_key: 'Worker-1', hostname: 'node-1' }
            }
          ] : [
            {
              id: 1,
              task_index: 0,
              status: 'done',
              worker: { worker_key: 'Worker-1', hostname: 'node-1' }
            },
            {
              id: 2,
              task_index: 1,
              status: 'running',
              worker: { worker_key: 'Worker-2', hostname: 'node-2' }
            }
          ]
          return Promise.resolve({ data: { tasks: mockTasks } })
        }
      })

      await router.push('/jobs/305/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      // Initial state: 1 worker assignment
      expect(wrapper.vm.workerAssignments).toHaveLength(1)
      expect(wrapper.vm.workerAssignments[0].workerKey).toBe('Worker-1')

      // Advance 5 seconds
      vi.advanceTimersByTime(5000)
      await flushPromises()

      // Updated state: different worker assignment
      expect(wrapper.vm.workerAssignments).toHaveLength(1)
      expect(wrapper.vm.workerAssignments[0].workerKey).toBe('Worker-2')
    })

    it('should not update when job is completed', async () => {
      const mockJob = {
        id: 306,
        name: 'Completed Job',
        status: 'completed',
        total_tasks: 10,
        started_at: '2024-01-15T10:00:00Z',
        completed_at: '2024-01-15T12:00:00Z',
        created_at: '2024-01-15T09:00:00Z'
      }

      api.get.mockImplementation((url) => {
        if (url === '/jobs/306') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/306/tasks') {
          return Promise.resolve({ data: { tasks: [] } })
        }
      })

      await router.push('/jobs/306/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      // Initial API calls
      expect(api.get).toHaveBeenCalledTimes(2)

      // Advance 5 seconds
      vi.advanceTimersByTime(5000)
      await flushPromises()

      // Should not poll again for completed jobs
      expect(api.get).toHaveBeenCalledTimes(2)
    })
  })

  describe('No Active Assignments Message (Requirement 10.5)', () => {
    it('should display "No active assignments" message when no workers are assigned', async () => {
      const mockJob = {
        id: 307,
        name: 'Test Job',
        status: 'pending',
        total_tasks: 10,
        started_at: null,
        created_at: '2024-01-15T09:00:00Z'
      }

      const mockTasks = [
        { id: 1, task_index: 0, status: 'pending', worker: null },
        { id: 2, task_index: 1, status: 'pending', worker: null }
      ]

      api.get.mockImplementation((url) => {
        if (url === '/jobs/307') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/307/tasks') {
          return Promise.resolve({ data: { tasks: mockTasks } })
        }
      })

      await router.push('/jobs/307/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      const workerSection = wrapper.findAll('.bg-white').find(card => 
        card.text().includes('Worker Assignments')
      )

      expect(workerSection.text()).toContain('No active assignments')
      expect(wrapper.vm.workerAssignments).toHaveLength(0)
    })

    it('should display message when all tasks are completed', async () => {
      const mockJob = {
        id: 308,
        name: 'Test Job',
        status: 'completed',
        total_tasks: 2,
        started_at: '2024-01-15T10:00:00Z',
        completed_at: '2024-01-15T11:00:00Z',
        created_at: '2024-01-15T09:00:00Z'
      }

      const mockTasks = [
        {
          id: 1,
          task_index: 0,
          status: 'done',
          worker: { worker_key: 'Worker-1', hostname: 'node-1' }
        },
        {
          id: 2,
          task_index: 1,
          status: 'done',
          worker: { worker_key: 'Worker-2', hostname: 'node-2' }
        }
      ]

      api.get.mockImplementation((url) => {
        if (url === '/jobs/308') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/308/tasks') {
          return Promise.resolve({ data: { tasks: mockTasks } })
        }
      })

      await router.push('/jobs/308/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      const workerSection = wrapper.findAll('.bg-white').find(card => 
        card.text().includes('Worker Assignments')
      )

      expect(workerSection.text()).toContain('No active assignments')
      expect(wrapper.vm.workerAssignments).toHaveLength(0)
    })

    it('should display message when tasks exist but none are assigned/running', async () => {
      const mockJob = {
        id: 309,
        name: 'Test Job',
        status: 'running',
        total_tasks: 3,
        started_at: '2024-01-15T10:00:00Z',
        created_at: '2024-01-15T09:00:00Z'
      }

      const mockTasks = [
        { id: 1, task_index: 0, status: 'pending', worker: null },
        { id: 2, task_index: 1, status: 'failed', worker: null },
        {
          id: 3,
          task_index: 2,
          status: 'done',
          worker: { worker_key: 'Worker-1', hostname: 'node-1' }
        }
      ]

      api.get.mockImplementation((url) => {
        if (url === '/jobs/309') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/309/tasks') {
          return Promise.resolve({ data: { tasks: mockTasks } })
        }
      })

      await router.push('/jobs/309/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      expect(wrapper.vm.workerAssignments).toHaveLength(0)
      
      const workerSection = wrapper.findAll('.bg-white').find(card => 
        card.text().includes('Worker Assignments')
      )

      expect(workerSection.text()).toContain('No active assignments')
    })
  })

  describe('Worker Assignment Display Details', () => {
    it('should display worker hostname when available', async () => {
      const mockJob = {
        id: 310,
        name: 'Test Job',
        status: 'running',
        total_tasks: 5,
        started_at: '2024-01-15T10:00:00Z',
        created_at: '2024-01-15T09:00:00Z'
      }

      const mockTasks = [
        {
          id: 1,
          task_index: 0,
          status: 'running',
          worker: {
            worker_key: 'Worker-1',
            hostname: 'production-worker-node-1'
          }
        }
      ]

      api.get.mockImplementation((url) => {
        if (url === '/jobs/310') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/310/tasks') {
          return Promise.resolve({ data: { tasks: mockTasks } })
        }
      })

      await router.push('/jobs/310/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      const workerSection = wrapper.findAll('.bg-white').find(card => 
        card.text().includes('Worker Assignments')
      )

      expect(workerSection.text()).toContain('production-worker-node-1')
    })

    it('should display status indicator for each assignment', async () => {
      const mockJob = {
        id: 311,
        name: 'Test Job',
        status: 'running',
        total_tasks: 5,
        started_at: '2024-01-15T10:00:00Z',
        created_at: '2024-01-15T09:00:00Z'
      }

      const mockTasks = [
        {
          id: 1,
          task_index: 0,
          status: 'running',
          worker: { worker_key: 'Worker-1', hostname: 'node-1' }
        },
        {
          id: 2,
          task_index: 1,
          status: 'assigned',
          worker: { worker_key: 'Worker-2', hostname: 'node-2' }
        }
      ]

      api.get.mockImplementation((url) => {
        if (url === '/jobs/311') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/311/tasks') {
          return Promise.resolve({ data: { tasks: mockTasks } })
        }
      })

      await router.push('/jobs/311/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      const workerSection = wrapper.findAll('.bg-white').find(card => 
        card.text().includes('Worker Assignments')
      )

      // Check for status badges
      expect(workerSection.text()).toContain('running')
      expect(workerSection.text()).toContain('assigned')
    })

    it('should handle multiple worker assignments correctly', async () => {
      const mockJob = {
        id: 312,
        name: 'Test Job',
        status: 'running',
        total_tasks: 10,
        started_at: '2024-01-15T10:00:00Z',
        created_at: '2024-01-15T09:00:00Z'
      }

      const mockTasks = [
        {
          id: 1,
          task_index: 0,
          status: 'running',
          worker: { worker_key: 'Worker-1', hostname: 'node-1' }
        },
        {
          id: 2,
          task_index: 1,
          status: 'running',
          worker: { worker_key: 'Worker-2', hostname: 'node-2' }
        },
        {
          id: 3,
          task_index: 2,
          status: 'assigned',
          worker: { worker_key: 'Worker-3', hostname: 'node-3' }
        },
        {
          id: 4,
          task_index: 3,
          status: 'running',
          worker: { worker_key: 'Worker-4', hostname: 'node-4' }
        }
      ]

      api.get.mockImplementation((url) => {
        if (url === '/jobs/312') {
          return Promise.resolve({ data: { job: mockJob } })
        }
        if (url === '/jobs/312/tasks') {
          return Promise.resolve({ data: { tasks: mockTasks } })
        }
      })

      await router.push('/jobs/312/monitoring')
      wrapper = mount(JobMonitoring, {
        global: {
          plugins: [router]
        }
      })

      await flushPromises()

      expect(wrapper.vm.workerAssignments).toHaveLength(4)
      
      const workerSection = wrapper.findAll('.bg-white').find(card => 
        card.text().includes('Worker Assignments')
      )

      // Verify all workers are displayed
      expect(workerSection.text()).toContain('Worker-1')
      expect(workerSection.text()).toContain('Worker-2')
      expect(workerSection.text()).toContain('Worker-3')
      expect(workerSection.text()).toContain('Worker-4')
      
      // Verify all task numbers are displayed
      expect(workerSection.text()).toContain('Task 1')
      expect(workerSection.text()).toContain('Task 2')
      expect(workerSection.text()).toContain('Task 3')
      expect(workerSection.text()).toContain('Task 4')
    })
  })
})
