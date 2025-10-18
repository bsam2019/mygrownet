import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import PerformanceWidget from '@/components/Employee/PerformanceWidget.vue'

// Mock the formatting utilities
vi.mock('@/utils/formatting', () => ({
  formatNumber: vi.fn((value, decimals = 0) => value.toFixed(decimals)),
  formatCurrency: vi.fn((value) => `K${value.toLocaleString()}`),
  formatPercentage: vi.fn((value) => `${value}%`)
}))

// Mock fetch
global.fetch = vi.fn()

describe('PerformanceWidget', () => {
  const mockPerformance = {
    overallScore: 8.5,
    goalsAchieved: 12,
    clientRetentionRate: 85.5,
    newClientsAcquired: 8,
    revenueGenerated: 45000
  }

  const mockRecentGoals = [
    {
      id: 1,
      title: 'Acquire 10 new clients',
      progress: 80,
      dueDate: '2024-02-15T00:00:00Z'
    },
    {
      id: 2,
      title: 'Increase portfolio value by 20%',
      progress: 65,
      dueDate: '2024-03-01T00:00:00Z'
    }
  ]

  const mockPerformanceTrend = [
    { period: 'Jan', score: 7.5 },
    { period: 'Feb', score: 8.0 },
    { period: 'Mar', score: 8.2 },
    { period: 'Apr', score: 8.5 }
  ]

  beforeEach(() => {
    vi.clearAllMocks()
    fetch.mockResolvedValue({
      ok: true,
      json: () => Promise.resolve({
        performance: mockPerformance,
        recentGoals: mockRecentGoals,
        performanceTrend: mockPerformanceTrend
      })
    })
  })

  it('renders performance widget header correctly', () => {
    const wrapper = mount(PerformanceWidget, {
      props: {
        employeeId: 1
      }
    })

    expect(wrapper.find('h3').text()).toBe('Performance Overview')
    expect(wrapper.find('button').text()).toBe('View Details')
  })

  it('displays loading state when fetching data', async () => {
    const wrapper = mount(PerformanceWidget, {
      props: {
        employeeId: 1
      }
    })

    // Should show loading state initially
    expect(wrapper.find('.animate-spin').exists()).toBe(true)
  })

  it('displays overall performance score correctly', async () => {
    const wrapper = mount(PerformanceWidget, {
      props: {
        employeeId: 1
      }
    })

    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 0))

    expect(wrapper.text()).toContain('8.5')
    expect(wrapper.text()).toContain('Overall Performance Score')
  })

  it('displays performance metrics correctly', async () => {
    const wrapper = mount(PerformanceWidget, {
      props: {
        employeeId: 1
      }
    })

    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 0))

    expect(wrapper.text()).toContain('12') // Goals Achieved
    expect(wrapper.text()).toContain('Goals Achieved')
    expect(wrapper.text()).toContain('85.5%') // Client Retention
    expect(wrapper.text()).toContain('Client Retention')
    expect(wrapper.text()).toContain('8') // New Clients
    expect(wrapper.text()).toContain('New Clients')
    expect(wrapper.text()).toContain('K45,000') // Revenue Generated
    expect(wrapper.text()).toContain('Revenue Generated')
  })

  it('displays recent goals with progress bars', async () => {
    const wrapper = mount(PerformanceWidget, {
      props: {
        employeeId: 1
      }
    })

    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 0))

    expect(wrapper.text()).toContain('Current Goals')
    expect(wrapper.text()).toContain('Acquire 10 new clients')
    expect(wrapper.text()).toContain('Increase portfolio value by 20%')
    expect(wrapper.text()).toContain('80%')
    expect(wrapper.text()).toContain('65%')
  })

  it('displays performance trend chart', async () => {
    const wrapper = mount(PerformanceWidget, {
      props: {
        employeeId: 1
      }
    })

    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 0))

    expect(wrapper.text()).toContain('Performance Trend')
    
    // Check that trend bars are rendered
    const trendBars = wrapper.findAll('.bg-blue-200.rounded-t')
    expect(trendBars.length).toBeGreaterThan(0)
  })

  it('displays quick action buttons', async () => {
    const wrapper = mount(PerformanceWidget, {
      props: {
        employeeId: 1
      }
    })

    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 0))

    const buttons = wrapper.findAll('button')
    const actionButtons = buttons.filter(button => 
      button.text() === 'Set Goal' || button.text() === 'View Reviews'
    )
    expect(actionButtons).toHaveLength(2)
  })

  it('emits events when buttons are clicked', async () => {
    const wrapper = mount(PerformanceWidget, {
      props: {
        employeeId: 1
      }
    })

    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 0))

    // Test view details button
    const viewDetailsButton = wrapper.find('button')
    await viewDetailsButton.trigger('click')
    expect(wrapper.emitted('view-detailed-performance')).toBeTruthy()

    // Test set goal button
    const setGoalButton = wrapper.findAll('button').find(btn => btn.text() === 'Set Goal')
    await setGoalButton.trigger('click')
    expect(wrapper.emitted('set-goal')).toBeTruthy()

    // Test view reviews button
    const reviewsButton = wrapper.findAll('button').find(btn => btn.text() === 'View Reviews')
    await reviewsButton.trigger('click')
    expect(wrapper.emitted('view-reviews')).toBeTruthy()
  })

  it('calculates progress bar colors correctly', () => {
    const wrapper = mount(PerformanceWidget, {
      props: {
        employeeId: 1
      }
    })

    const vm = wrapper.vm

    expect(vm.getProgressColor(90)).toBe('bg-green-500')
    expect(vm.getProgressColor(70)).toBe('bg-yellow-500')
    expect(vm.getProgressColor(50)).toBe('bg-orange-500')
    expect(vm.getProgressColor(30)).toBe('bg-red-500')
  })

  it('formats dates correctly', () => {
    const wrapper = mount(PerformanceWidget, {
      props: {
        employeeId: 1
      }
    })

    const vm = wrapper.vm

    const testDate = '2024-02-15T00:00:00Z'
    const formatted = vm.formatDate(testDate)
    expect(formatted).toMatch(/Feb \d+/)
  })

  it('calculates circumference correctly', () => {
    const wrapper = mount(PerformanceWidget, {
      props: {
        employeeId: 1
      }
    })

    expect(wrapper.vm.circumference).toBe(2 * Math.PI * 40)
  })

  it('displays no data message when performance is null', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve({
        performance: null,
        recentGoals: [],
        performanceTrend: []
      })
    })

    const wrapper = mount(PerformanceWidget, {
      props: {
        employeeId: 1
      }
    })

    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 0))

    expect(wrapper.text()).toContain('No performance data available')
  })

  it('does not load data when employeeId is not provided', () => {
    mount(PerformanceWidget, {
      props: {}
    })

    expect(fetch).not.toHaveBeenCalled()
  })

  it('handles fetch errors gracefully', async () => {
    fetch.mockRejectedValueOnce(new Error('Network error'))

    const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {})

    const wrapper = mount(PerformanceWidget, {
      props: {
        employeeId: 1
      }
    })

    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 0))

    expect(consoleSpy).toHaveBeenCalledWith('Failed to load performance data:', expect.any(Error))
    
    consoleSpy.mockRestore()
  })

  it('handles empty goals and trend data', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve({
        performance: mockPerformance,
        recentGoals: [],
        performanceTrend: []
      })
    })

    const wrapper = mount(PerformanceWidget, {
      props: {
        employeeId: 1
      }
    })

    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 0))

    // Should still display performance metrics
    expect(wrapper.text()).toContain('8.5')
    expect(wrapper.text()).toContain('Overall Performance Score')
    
    // Should not display goals or trend sections
    expect(wrapper.text()).not.toContain('Current Goals')
    expect(wrapper.text()).not.toContain('Performance Trend')
  })

  it('limits progress bar width to 100%', async () => {
    const highProgressGoal = {
      id: 1,
      title: 'Test Goal',
      progress: 150, // Over 100%
      dueDate: '2024-02-15T00:00:00Z'
    }

    fetch.mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve({
        performance: mockPerformance,
        recentGoals: [highProgressGoal],
        performanceTrend: []
      })
    })

    const wrapper = mount(PerformanceWidget, {
      props: {
        employeeId: 1
      }
    })

    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 0))

    const progressBar = wrapper.find('.h-2.rounded-full')
    expect(progressBar.attributes('style')).toContain('width: 100%')
  })
})