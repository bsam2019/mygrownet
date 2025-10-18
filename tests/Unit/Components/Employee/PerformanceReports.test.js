import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import PerformanceReports from '@/components/Employee/PerformanceReports.vue'

// Mock Chart.js
vi.mock('chart.js', () => ({
  Chart: vi.fn().mockImplementation(() => ({
    destroy: vi.fn()
  })),
  registerables: []
}))

// Mock the formatting utilities
vi.mock('@/utils/formatting', () => ({
  formatCurrency: vi.fn((value) => `K${value.toLocaleString()}`),
  formatNumber: vi.fn((value, decimals = 0) => value.toFixed(decimals)),
  formatPercentage: vi.fn((value) => `${value}%`)
}))

// Mock Inertia router
vi.mock('@inertiajs/vue3', () => ({
  router: {
    visit: vi.fn()
  }
}))

// Mock fetch
global.fetch = vi.fn()

describe('PerformanceReports', () => {
  const mockDepartments = [
    { id: 1, name: 'Sales' },
    { id: 2, name: 'Marketing' }
  ]

  const mockPositions = [
    { id: 1, title: 'Sales Manager' },
    { id: 2, title: 'Field Agent' }
  ]

  const mockEmployeeData = [
    {
      id: 1,
      firstName: 'John',
      lastName: 'Doe',
      department: 'Sales',
      position: 'Sales Manager',
      overallScore: 8.5,
      goalAchievement: 85,
      investmentsFacilitated: 12,
      commissionGenerated: 15000,
      clientRetention: 92
    },
    {
      id: 2,
      firstName: 'Jane',
      lastName: 'Smith',
      department: 'Marketing',
      position: 'Field Agent',
      overallScore: 7.2,
      goalAchievement: 72,
      investmentsFacilitated: 8,
      commissionGenerated: 9500,
      clientRetention: 88
    }
  ]

  const mockOverview = {
    avgScore: 7.9,
    goalAchievement: 78.5,
    topPerformers: 3,
    needsImprovement: 1
  }

  beforeEach(() => {
    vi.clearAllMocks()
    fetch.mockResolvedValue({
      ok: true,
      json: () => Promise.resolve({
        employees: mockEmployeeData,
        overview: mockOverview,
        chartData: {
          labels: ['Q1', 'Q2', 'Q3', 'Q4'],
          performanceScores: [7.2, 7.8, 8.1, 8.3],
          goalAchievement: [65, 72, 78, 82]
        }
      })
    })
  })

  it('renders performance reports header correctly', () => {
    const wrapper = mount(PerformanceReports, {
      props: {
        departments: mockDepartments,
        positions: mockPositions,
        initialData: mockEmployeeData,
        initialOverview: mockOverview
      }
    })

    expect(wrapper.find('h2').text()).toBe('Performance Reports')
    expect(wrapper.text()).toContain('Employee performance analytics and trends')
  })

  it('displays performance overview cards correctly', () => {
    const wrapper = mount(PerformanceReports, {
      props: {
        departments: mockDepartments,
        positions: mockPositions,
        initialData: mockEmployeeData,
        initialOverview: mockOverview
      }
    })

    const overviewCards = wrapper.findAll('.bg-white.p-6.rounded-lg.shadow.border')
    expect(overviewCards).toHaveLength(4)

    // Check average performance score
    expect(wrapper.text()).toContain('7.9')
    expect(wrapper.text()).toContain('Avg Performance Score')

    // Check goal achievement
    expect(wrapper.text()).toContain('78.5%')
    expect(wrapper.text()).toContain('Goal Achievement')

    // Check top performers
    expect(wrapper.text()).toContain('3')
    expect(wrapper.text()).toContain('Top Performers')

    // Check needs improvement
    expect(wrapper.text()).toContain('1')
    expect(wrapper.text()).toContain('Needs Improvement')
  })

  it('renders employee performance table correctly', () => {
    const wrapper = mount(PerformanceReports, {
      props: {
        departments: mockDepartments,
        positions: mockPositions,
        initialData: mockEmployeeData,
        initialOverview: mockOverview
      }
    })

    const table = wrapper.find('table')
    expect(table.exists()).toBe(true)

    const rows = wrapper.findAll('tbody tr')
    expect(rows).toHaveLength(2)

    // Check first employee row
    const firstRow = rows[0]
    expect(firstRow.text()).toContain('John Doe')
    expect(firstRow.text()).toContain('Sales')
    expect(firstRow.text()).toContain('8.5')
    expect(firstRow.text()).toContain('85%')
    expect(firstRow.text()).toContain('12')
    expect(firstRow.text()).toContain('K15,000')
    expect(firstRow.text()).toContain('92%')
  })

  it('handles filter changes correctly', async () => {
    const wrapper = mount(PerformanceReports, {
      props: {
        departments: mockDepartments,
        positions: mockPositions,
        initialData: mockEmployeeData,
        initialOverview: mockOverview
      }
    })

    const departmentSelect = wrapper.find('select')
    await departmentSelect.setValue('1')

    expect(fetch).toHaveBeenCalledWith('/api/employee/reports/performance', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': ''
      },
      body: JSON.stringify({
        departmentId: '1',
        positionId: '',
        performanceRating: '',
        period: 'current_quarter',
        startDate: '',
        endDate: ''
      })
    })
  })

  it('handles sorting correctly', async () => {
    const wrapper = mount(PerformanceReports, {
      props: {
        departments: mockDepartments,
        positions: mockPositions,
        initialData: mockEmployeeData,
        initialOverview: mockOverview
      }
    })

    // Find the performance score header and click it
    const headers = wrapper.findAll('th')
    const scoreHeader = headers.find(header => header.text().includes('Performance Score'))
    await scoreHeader.trigger('click')

    // Should sort by performance score in ascending order (since default is desc)
    const rows = wrapper.findAll('tbody tr')
    expect(rows[0].text()).toContain('Jane Smith') // Lower score first
    expect(rows[1].text()).toContain('John Doe') // Higher score second
  })

  it('applies correct performance score colors', () => {
    const wrapper = mount(PerformanceReports, {
      props: {
        departments: mockDepartments,
        positions: mockPositions,
        initialData: mockEmployeeData,
        initialOverview: mockOverview
      }
    })

    const progressBars = wrapper.findAll('.h-2.rounded-full')
    
    // John Doe (8.5 score) should have green color
    expect(progressBars[0].classes()).toContain('bg-green-500')
    
    // Jane Smith (7.2 score) should have blue color
    expect(progressBars[1].classes()).toContain('bg-blue-500')
  })

  it('applies correct performance status badges', () => {
    const wrapper = mount(PerformanceReports, {
      props: {
        departments: mockDepartments,
        positions: mockPositions,
        initialData: mockEmployeeData,
        initialOverview: mockOverview
      }
    })

    const statusBadges = wrapper.findAll('.inline-flex.px-2.py-1.text-xs.font-semibold.rounded-full')
    
    // John Doe (8.5 score) should have "Good" status with blue styling
    expect(statusBadges[0].text()).toBe('Good')
    expect(statusBadges[0].classes()).toContain('bg-blue-100')
    expect(statusBadges[0].classes()).toContain('text-blue-800')
    
    // Jane Smith (7.2 score) should have "Good" status with blue styling
    expect(statusBadges[1].text()).toBe('Good')
    expect(statusBadges[1].classes()).toContain('bg-blue-100')
    expect(statusBadges[1].classes()).toContain('text-blue-800')
  })

  it('handles export functionality', async () => {
    // Mock blob response
    const mockBlob = new Blob(['test'], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
    fetch.mockResolvedValueOnce({
      ok: true,
      blob: () => Promise.resolve(mockBlob)
    })

    // Mock URL.createObjectURL and related methods
    global.URL.createObjectURL = vi.fn(() => 'mock-url')
    global.URL.revokeObjectURL = vi.fn()

    const wrapper = mount(PerformanceReports, {
      props: {
        departments: mockDepartments,
        positions: mockPositions,
        initialData: mockEmployeeData,
        initialOverview: mockOverview
      }
    })

    const exportButton = wrapper.find('button:first-child')
    await exportButton.trigger('click')

    expect(fetch).toHaveBeenCalledWith('/api/employee/reports/performance/export', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': ''
      },
      body: JSON.stringify({
        departmentId: '',
        positionId: '',
        performanceRating: '',
        period: 'current_quarter',
        startDate: '',
        endDate: ''
      })
    })
  })

  it('displays loading state correctly', async () => {
    const wrapper = mount(PerformanceReports, {
      props: {
        departments: mockDepartments,
        positions: mockPositions
      }
    })

    // Should show loading state initially
    expect(wrapper.find('.animate-spin').exists()).toBe(true)
  })

  it('displays empty state when no data', () => {
    const wrapper = mount(PerformanceReports, {
      props: {
        departments: mockDepartments,
        positions: mockPositions,
        initialData: [],
        initialOverview: mockOverview
      }
    })

    expect(wrapper.text()).toContain('No performance data')
    expect(wrapper.text()).toContain('No employee performance data found for the selected criteria.')
  })

  it('handles view employee details', async () => {
    const { router } = await import('@inertiajs/vue3')
    
    const wrapper = mount(PerformanceReports, {
      props: {
        departments: mockDepartments,
        positions: mockPositions,
        initialData: mockEmployeeData,
        initialOverview: mockOverview
      }
    })

    const viewButton = wrapper.find('button[class*="text-blue-600"]')
    await viewButton.trigger('click')

    expect(router.visit).toHaveBeenCalledWith('/employee/employees/1')
  })

  it('handles create performance plan', async () => {
    const { router } = await import('@inertiajs/vue3')
    
    const wrapper = mount(PerformanceReports, {
      props: {
        departments: mockDepartments,
        positions: mockPositions,
        initialData: mockEmployeeData,
        initialOverview: mockOverview
      }
    })

    const planButton = wrapper.find('button[class*="text-green-600"]')
    await planButton.trigger('click')

    expect(router.visit).toHaveBeenCalledWith('/employee/performance/plan/1')
  })

  it('handles refresh functionality', async () => {
    const wrapper = mount(PerformanceReports, {
      props: {
        departments: mockDepartments,
        positions: mockPositions,
        initialData: mockEmployeeData,
        initialOverview: mockOverview
      }
    })

    const refreshButton = wrapper.findAll('button')[1]
    await refreshButton.trigger('click')

    expect(fetch).toHaveBeenCalledWith('/api/employee/reports/performance', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': ''
      },
      body: JSON.stringify({
        departmentId: '',
        positionId: '',
        performanceRating: '',
        period: 'current_quarter',
        startDate: '',
        endDate: ''
      })
    })
  })

  it('renders performance chart canvas', () => {
    const wrapper = mount(PerformanceReports, {
      props: {
        departments: mockDepartments,
        positions: mockPositions,
        initialData: mockEmployeeData,
        initialOverview: mockOverview
      }
    })

    const canvas = wrapper.find('canvas')
    expect(canvas.exists()).toBe(true)
  })
})