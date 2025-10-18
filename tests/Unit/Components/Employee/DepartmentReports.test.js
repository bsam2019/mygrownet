import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import DepartmentReports from '@/components/Employee/DepartmentReports.vue'

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

describe('DepartmentReports', () => {
  const mockDepartments = [
    { id: 1, name: 'Sales', description: 'Sales Department', is_active: true },
    { id: 2, name: 'Marketing', description: 'Marketing Department', is_active: true }
  ]

  const mockDepartmentData = [
    {
      id: 1,
      name: 'Sales',
      employeeCount: 15,
      avgPerformance: 8.2,
      totalCommissions: 45000,
      avgSalary: 3500,
      status: 'excellent'
    },
    {
      id: 2,
      name: 'Marketing',
      employeeCount: 8,
      avgPerformance: 7.5,
      totalCommissions: 28000,
      avgSalary: 3200,
      status: 'good'
    }
  ]

  const mockSummary = {
    totalEmployees: 23,
    avgPerformance: 7.9,
    totalCommissions: 73000,
    growthRate: 12.5
  }

  beforeEach(() => {
    vi.clearAllMocks()
    fetch.mockResolvedValue({
      ok: true,
      json: () => Promise.resolve({
        departments: mockDepartmentData,
        summary: mockSummary
      })
    })
  })

  it('renders department reports header correctly', () => {
    const wrapper = mount(DepartmentReports, {
      props: {
        departments: mockDepartments,
        initialData: mockDepartmentData,
        initialSummary: mockSummary
      }
    })

    expect(wrapper.find('h2').text()).toBe('Department Reports')
    expect(wrapper.text()).toContain('Comprehensive department performance and analytics')
  })

  it('displays summary cards with correct data', () => {
    const wrapper = mount(DepartmentReports, {
      props: {
        departments: mockDepartments,
        initialData: mockDepartmentData,
        initialSummary: mockSummary
      }
    })

    const summaryCards = wrapper.findAll('.bg-white.p-6.rounded-lg.shadow.border')
    expect(summaryCards).toHaveLength(4)

    // Check total employees
    expect(wrapper.text()).toContain('23')
    expect(wrapper.text()).toContain('Total Employees')

    // Check average performance
    expect(wrapper.text()).toContain('7.9')
    expect(wrapper.text()).toContain('Avg Performance')

    // Check total commissions
    expect(wrapper.text()).toContain('K73,000')
    expect(wrapper.text()).toContain('Total Commissions')

    // Check growth rate
    expect(wrapper.text()).toContain('12.5%')
    expect(wrapper.text()).toContain('Growth Rate')
  })

  it('renders department data table correctly', () => {
    const wrapper = mount(DepartmentReports, {
      props: {
        departments: mockDepartments,
        initialData: mockDepartmentData,
        initialSummary: mockSummary
      }
    })

    const table = wrapper.find('table')
    expect(table.exists()).toBe(true)

    const rows = wrapper.findAll('tbody tr')
    expect(rows).toHaveLength(2)

    // Check first department row
    const firstRow = rows[0]
    expect(firstRow.text()).toContain('Sales')
    expect(firstRow.text()).toContain('15')
    expect(firstRow.text()).toContain('8.2')
    expect(firstRow.text()).toContain('K45,000')
    expect(firstRow.text()).toContain('K3,500')
  })

  it('handles filter changes correctly', async () => {
    const wrapper = mount(DepartmentReports, {
      props: {
        departments: mockDepartments,
        initialData: mockDepartmentData,
        initialSummary: mockSummary
      }
    })

    const departmentSelect = wrapper.find('select')
    await departmentSelect.setValue('1')

    expect(fetch).toHaveBeenCalledWith('/api/employee/reports/departments', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': ''
      },
      body: JSON.stringify({
        departmentId: '1',
        period: 'current_month',
        startDate: '',
        endDate: ''
      })
    })
  })

  it('handles sorting correctly', async () => {
    const wrapper = mount(DepartmentReports, {
      props: {
        departments: mockDepartments,
        initialData: mockDepartmentData,
        initialSummary: mockSummary
      }
    })

    const nameHeader = wrapper.find('th')
    await nameHeader.trigger('click')

    // Should sort by name in ascending order
    const rows = wrapper.findAll('tbody tr')
    expect(rows[0].text()).toContain('Marketing')
    expect(rows[1].text()).toContain('Sales')
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

    const wrapper = mount(DepartmentReports, {
      props: {
        departments: mockDepartments,
        initialData: mockDepartmentData,
        initialSummary: mockSummary
      }
    })

    const exportButton = wrapper.find('button:first-child')
    await exportButton.trigger('click')

    expect(fetch).toHaveBeenCalledWith('/api/employee/reports/departments/export', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': ''
      },
      body: JSON.stringify({
        departmentId: '',
        period: 'current_month',
        startDate: '',
        endDate: ''
      })
    })
  })

  it('displays loading state correctly', async () => {
    const wrapper = mount(DepartmentReports, {
      props: {
        departments: mockDepartments
      }
    })

    // Should show loading state initially
    expect(wrapper.find('.animate-spin').exists()).toBe(true)
  })

  it('displays empty state when no data', () => {
    const wrapper = mount(DepartmentReports, {
      props: {
        departments: mockDepartments,
        initialData: [],
        initialSummary: mockSummary
      }
    })

    expect(wrapper.text()).toContain('No department data')
    expect(wrapper.text()).toContain('No departments found for the selected criteria.')
  })

  it('applies correct status classes', () => {
    const wrapper = mount(DepartmentReports, {
      props: {
        departments: mockDepartments,
        initialData: mockDepartmentData,
        initialSummary: mockSummary
      }
    })

    const statusBadges = wrapper.findAll('.inline-flex.px-2.py-1.text-xs.font-semibold.rounded-full')
    
    // Check excellent status class
    expect(statusBadges[0].classes()).toContain('bg-green-100')
    expect(statusBadges[0].classes()).toContain('text-green-800')
    
    // Check good status class
    expect(statusBadges[1].classes()).toContain('bg-blue-100')
    expect(statusBadges[1].classes()).toContain('text-blue-800')
  })

  it('handles refresh functionality', async () => {
    const wrapper = mount(DepartmentReports, {
      props: {
        departments: mockDepartments,
        initialData: mockDepartmentData,
        initialSummary: mockSummary
      }
    })

    const refreshButton = wrapper.findAll('button')[1]
    await refreshButton.trigger('click')

    expect(fetch).toHaveBeenCalledWith('/api/employee/reports/departments', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': ''
      },
      body: JSON.stringify({
        departmentId: '',
        period: 'current_month',
        startDate: '',
        endDate: ''
      })
    })
  })

  it('handles view department details', async () => {
    const { router } = await import('@inertiajs/vue3')
    
    const wrapper = mount(DepartmentReports, {
      props: {
        departments: mockDepartments,
        initialData: mockDepartmentData,
        initialSummary: mockSummary
      }
    })

    const viewButton = wrapper.find('button[class*="text-blue-600"]')
    await viewButton.trigger('click')

    expect(router.visit).toHaveBeenCalledWith('/employee/departments/1')
  })
})