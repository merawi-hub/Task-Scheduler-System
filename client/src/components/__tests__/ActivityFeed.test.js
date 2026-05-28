import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import ActivityFeed from '../ActivityFeed.vue'

describe('ActivityFeed - Task 11: Real-Time Activity Feed', () => {
  let wrapper

  beforeEach(() => {
    vi.clearAllMocks()
  })

  afterEach(() => {
    if (wrapper) {
      wrapper.unmount()
    }
  })

  describe('Event Display in Reverse Chronological Order (Requirement 11.1)', () => {
    it('should display events in reverse chronological order (newest first)', async () => {
      const mockTasks = [
        {
          id: 1,
          task_index: 0,
          status: 'done',
          completed_at: '2024-01-15T10:00:00Z',
          retry_count: 0,
        },
        {
          id: 2,
          task_index: 1,
          status: 'done',
          completed_at: '2024-01-15T10:05:00Z',
          retry_count: 0,
        },
        {
          id: 3,
          task_index: 2,
          status: 'done',
          completed_at: '2024-01-15T10:10:00Z',
          retry_count: 0,
        },
      ]

      wrapper = mount(ActivityFeed, {
        props: {
          tasks: mockTasks,
        },
      })

      await flushPromises()

      const events = wrapper.findAll('.activity-event')
      expect(events.length).toBeGreaterThan(0)

      // First event should be the most recent (Task #3)
      expect(events[0].text()).toContain('Task #3')
      // Last event should be the oldest (Task #1)
      expect(events[events.length - 1].text()).toContain('Task #1')
    })
  })

  describe('Event Types (Requirement 11.2)', () => {
    it('should display task completion events', async () => {
      const mockTasks = [
        {
          id: 1,
          task_index: 0,
          status: 'done',
          completed_at: '2024-01-15T10:00:00Z',
          retry_count: 0,
        },
      ]

      wrapper = mount(ActivityFeed, {
        props: {
          tasks: mockTasks,
        },
      })

      await flushPromises()

      expect(wrapper.text()).toContain('Task #1 completed successfully')
      expect(wrapper.text().toLowerCase()).toContain('completion')
    })

    it('should display task failure events', async () => {
      const mockTasks = [
        {
          id: 2,
          task_index: 1,
          status: 'failed',
          completed_at: '2024-01-15T10:05:00Z',
          failure_reason: 'Connection timeout',
          retry_count: 0,
        },
      ]

      wrapper = mount(ActivityFeed, {
        props: {
          tasks: mockTasks,
        },
      })

      await flushPromises()

      expect(wrapper.text()).toContain('Task #2 failed')
      expect(wrapper.text()).toContain('Connection timeout')
      expect(wrapper.text().toLowerCase()).toContain('failure')
    })

    it('should display task retry events', async () => {
      const mockTasks = [
        {
          id: 3,
          task_index: 2,
          status: 'running',
          retry_count: 2,
          max_retries: 3,
          updated_at: '2024-01-15T10:10:00Z',
        },
      ]

      wrapper = mount(ActivityFeed, {
        props: {
          tasks: mockTasks,
        },
      })

      await flushPromises()

      expect(wrapper.text()).toContain('Task #3 retry attempt 2/3')
      expect(wrapper.text().toLowerCase()).toContain('retry')
    })
  })

  describe('20-Event Limit (Requirement 11.5)', () => {
    it('should limit display to most recent 20 events', async () => {
      // Create 25 completed tasks
      const mockTasks = Array.from({ length: 25 }, (_, i) => ({
        id: i + 1,
        task_index: i,
        status: 'done',
        completed_at: new Date(Date.now() - (25 - i) * 60000).toISOString(),
        retry_count: 0,
      }))

      wrapper = mount(ActivityFeed, {
        props: {
          tasks: mockTasks,
        },
      })

      await flushPromises()

      const events = wrapper.findAll('.activity-event')
      expect(events.length).toBeLessThanOrEqual(20)
    })
  })

  describe('Event Display Format (Requirement 11.6)', () => {
    it('should display timestamp, event type icon, and descriptive message', async () => {
      const mockTasks = [
        {
          id: 1,
          task_index: 0,
          status: 'done',
          completed_at: '2024-01-15T10:00:00Z',
          retry_count: 0,
        },
      ]

      wrapper = mount(ActivityFeed, {
        props: {
          tasks: mockTasks,
        },
      })

      await flushPromises()

      const event = wrapper.find('.activity-event')

      // Check for icon
      expect(event.find('svg').exists()).toBe(true)

      // Check for message
      expect(event.text()).toContain('Task #1 completed successfully')

      // Check for timestamp (should contain relative time or date)
      expect(event.text()).toMatch(/ago|Just now|Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec/)

      // Check for badge
      expect(event.text().toLowerCase()).toContain('completion')
    })

    it('should display appropriate icon colors for different event types', async () => {
      const mockTasks = [
        {
          id: 1,
          task_index: 0,
          status: 'done',
          completed_at: '2024-01-15T10:00:00Z',
          retry_count: 0,
        },
        {
          id: 2,
          task_index: 1,
          status: 'failed',
          completed_at: '2024-01-15T10:05:00Z',
          failure_reason: 'Error',
          retry_count: 0,
        },
        {
          id: 3,
          task_index: 2,
          status: 'running',
          retry_count: 1,
          max_retries: 3,
          updated_at: '2024-01-15T10:10:00Z',
        },
      ]

      wrapper = mount(ActivityFeed, {
        props: {
          tasks: mockTasks,
        },
      })

      await flushPromises()

      const events = wrapper.findAll('.activity-event')

      // Events are sorted newest first, so:
      // events[0] = Task #3 (retry) - most recent
      // events[1] = Task #2 (failure)
      // events[2] = Task #1 (completion) - oldest

      // Retry event should have orange styling
      expect(events[0].html()).toContain('bg-orange-100')
      expect(events[0].html()).toContain('text-orange-600')

      // Failure event should have red styling
      expect(events[1].html()).toContain('bg-red-100')
      expect(events[1].html()).toContain('text-red-600')

      // Completion event should have green styling
      expect(events[2].html()).toContain('bg-green-100')
      expect(events[2].html()).toContain('text-green-600')
    })
  })

  describe('Empty State', () => {
    it('should display empty state when no events exist', async () => {
      wrapper = mount(ActivityFeed, {
        props: {
          tasks: [],
        },
      })

      await flushPromises()

      expect(wrapper.text()).toContain('No activity yet')
      expect(wrapper.text()).toContain('Events will appear here as tasks are processed')
    })
  })

  describe('Event Updates', () => {
    it('should detect new events when tasks are updated', async () => {
      const initialTasks = [
        {
          id: 1,
          task_index: 0,
          status: 'running',
          retry_count: 0,
        },
      ]

      wrapper = mount(ActivityFeed, {
        props: {
          tasks: initialTasks,
        },
      })

      await flushPromises()

      // Initially no completion events
      expect(wrapper.text()).not.toContain('completed successfully')

      // Update task to completed
      const updatedTasks = [
        {
          id: 1,
          task_index: 0,
          status: 'done',
          completed_at: '2024-01-15T10:00:00Z',
          retry_count: 0,
        },
      ]

      await wrapper.setProps({ tasks: updatedTasks })
      await flushPromises()

      // Should now show completion event
      expect(wrapper.text()).toContain('Task #1 completed successfully')
    })
  })

  describe('Timestamp Formatting', () => {
    it('should display relative time for recent events', async () => {
      const recentTime = new Date(Date.now() - 5 * 60000).toISOString() // 5 minutes ago

      const mockTasks = [
        {
          id: 1,
          task_index: 0,
          status: 'done',
          completed_at: recentTime,
          retry_count: 0,
        },
      ]

      wrapper = mount(ActivityFeed, {
        props: {
          tasks: mockTasks,
        },
      })

      await flushPromises()

      expect(wrapper.text()).toMatch(/\d+ minute(s)? ago/)
    })

    it('should display "Just now" for very recent events', async () => {
      const justNow = new Date(Date.now() - 10000).toISOString() // 10 seconds ago

      const mockTasks = [
        {
          id: 1,
          task_index: 0,
          status: 'done',
          completed_at: justNow,
          retry_count: 0,
        },
      ]

      wrapper = mount(ActivityFeed, {
        props: {
          tasks: mockTasks,
        },
      })

      await flushPromises()

      expect(wrapper.text()).toContain('Just now')
    })
  })
})
