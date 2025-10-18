import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import CommissionReports from '@/components/Employee/CommissionReports.vue'

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

describe('CommissionReports', () => {
  const mockEmployees = [
    { id: 1, firstName: 'John', lastName: 'Doe' },
    { id: 2, firstName: 'Jane', lastName: 'Smith' }
  ]

  const mockCommissionData = [
    {
      id: 1,
      employeeName: 'John Doe',
      employeePosition: 'Sales Manager',
      commissionType: 'investment_facilitation',
      baseAmount: 50000,
      commissionRate: 5.0,
      commissionAmount: 2500,
      calculationDate: '2024-01-15',
      paymentDate: '2024-01-30',
      status: 'paid'
    },
    {
      id: 2,
      employeeName: 'Jane Smith',
      employeePosition: 'Field Agent',
      commissionType: 'referral',
      baseAmount: 30000,
      commissionRate: 3.0,
      commissionAmount: 900,
      calculationDate: '2024-01-20',
      paymentDate: null,
      status: 'pending'
    }
  ]

  const mockSummary = {
    totalCommissions: 3400,
    paidCommissions: 2500,
    pendingCommissions: 900,
    avgCommission: 1700
  }

  const mockTopEarners = [
    {
      id: 1,
      name: 'John Doe',
      position: 'Sales Manager',
      totalCommission: 2500,
      commissionCount: 1
    },
    {
      id: 2,
      name: 'Jane Smith',
      position: 'Field Agent',
      totalCommission: 900,
      commissionCount: 1
    }
  ]

  beforeEach(() => {
    vi.clearAllMocks()
    fetch.mockResolvedValue({
      ok: true,
      json: () => Promise.resolve({
        commissions: mockCommissionData,
        summary: mockSummary,
        topEarners: mockTopEarners,
        chartData: {
          trends: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            totalCommissions: [12000, 15000, 18000, 16000, 20000, 22000],
            paidCommissions: [10000, 13000, 16000, 14000, 18000, 20000]
          },
          byType: {
            labels: ['Investment Facilitation', 'Referral', 'Performance Bonus', 'Retention Bonus'],
            values: [45, 25, 20, 10]
          }
        }
      })
    })
  })

  it('renders commission reports header correctly', () => {
    const wrapper = mount(CommissionReports, {
      props: {
        employees: mockEmployees,
        initialData: mockCommissionData,
        initialSummary: mockSummary,
        initialTopEarners: mockTopEarners
      }
    })

    expect(wrapper.find('h2').text()).toBe('Commission Reports')
    expect(wrapper.text()).toContain('Employee commission tracking and analytics')
  })

  it('displays commission summary cards correctly', () => {
    const wrapper = mount(CommissionReports, {
      props: {
        employees: mockEmployees,
        initialData: mockCommissionData,
        initialSummary: mockSummary,
        initialTopEarners: mockTopEarners
      }
    })

    const summaryCards = wrapper.findAll('.bg-white.p-6.rounded-lg.shadow.border')
    expect(summaryCards).toHaveLength(4)

    // Check total commissions
    expect(wrapper.text()).toContain('K3,400')
    expect(wrapper.text()).toContain('Total Commissions')

    // Check paid commissions
    expect(wrapper.text()).toContain('K2,500')
    expect(wrapper.text()).toContain('Paid Commissions')

    // Check pending commissions
    expect(wrapper.text()).toContain('K900')
    expect(wrapper.text()).toContain('Pending Commissions')

    // Check average commission
    expect(wrapper.text()).toContain('K1,700')
    expect(wrapper.text()).toContain('Avg Commission')
  })

  it('renders commission data table correctly', () => {
    const wrapper = mount(CommissionReports, {
      props: {
        employees: mockEmployees,
        initialData: mockCommissionData,
        initialSummary: mockSummary,
        initialTopEarners: mockTopEarners
      }
    })

    const table = wrapper.find('table')
    expect(table.exists()).toBe(true)

    const rows = wrapper.findAll('tbody tr')
    expect(rows).toHaveLength(2)

    // Check first commission row
    const firstRow = rows[0]
    expect(firstRow.text()).toContain('John Doe')
    expect(firstRow.text()).toContain('Sales Manager')
    expect(firstRow.text()).toContain('Investment Facilitation')
    expect(firstRow.text()).toContain('K50,000')
    expect(firstRow.text()).toContain('5%')
    expect(firstRow.text()).toContain('K2,500')
  })

  it('displays top earners section correctly', () => {
    const wrapper = mount(CommissionReports, {
      props: {
        employees: mockEmployees,
        initialData: mockCommissionData,
        initialSummary: mockSummary,
        initialTopEarners: mockTopEarners
      }
    })

    expect(wrapper.text()).toContain('Top Earners')
    
    const earnerItems = wrapper.findAll('.flex.items-center.justify-between.p-3.bg-gray-50.rounded-lg')
    expect(earnerItems).toHaveLength(2)

    // Check first top earner
    const firstEarner = earnerItems[0]
    expect(firstEarner.text()).toContain('John Doe')
    expect(firstEarner.text()).toContain('Sales Manager')
    expect(firstEarner.text()).toContain('K2,500')
    expect(firstEarner.text()).toContain('1 commissions')
  })

  it('handles filter changes correctly', async () => {
    const wrapper = mount(CommissionReports, {
      props: {
        employees: mockEmployees,
        initialData: mockCommissionData,
        initialSummary: mockSummary,
        initialTopEarners: mockTopEarners
      }
    })

    const employeeSelect = wrapper.find('select')
    await employeeSelect.setValue('1')

    expect(fetch).toHaveBeenCalledWith('/api/employee/reports/commissions', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': ''
      },
      body: JSON.stringify({
        employeeId: '1',
        commissionType: '',
        status: '',
        period: 'current_month',
        startDate: '',
        endDate: ''
      })
    })
  })

  it('handles sorting correctly', async () => {
    const wrapper = mount(CommissionReports, {
      props: {
        employees: mockEmployees,
        initialData: mockCommissionData,
        initialSummary: mockSummary,
        initialTopEarners: mockTopEarners
      }
    })

    // Find the commission amount header and click it
    const headers = wrapper.findAll('th')
    const commissionHeader = headers.find(header => header.text().includes('Commission'))
    await commissionHeader.trigger('click')

    // Should sort by commission amount in ascending order (since default is desc)
    const rows = wrapper.findAll('tbody tr')
    expect(rows[0].text()).toContain('Jane Smith') // Lower commission first
    expect(rows[1].text()).toContain('John Doe') // Higher commission second
  })

  it('applies correct commission type classes', () => {
    const wrapper = mount(CommissionReports, {
      props: {
        employees: mockEmployees,
        initialData: mockCommissionData,
        initialSummary: mockSummary,
        initialTopEarners: mockTopEarners
      }
    })

    const typeBadges = wrapper.findAll('.inline-flex.px-2.py-1.text-xs.font-semibold.rounded-full')
    
    // Investment facilitation should have blue styling
    expect(typeBadges[0].classes()).toContain('bg-blue-100')
    expect(typeBadges[0].classes()).toContain('text-blue-800')
    expect(typeBadges[0].text()).toBe('Investment Facilitation')
    
    // Referral should have green styling
    expect(typeBadges[1].classes()).toContain('bg-green-100')
    expect(typeBadges[1].classes()).toContain('text-green-800')
    expect(typeBadges[1].text()).toBe('Referral')
  })

  it('applies correct status classes', () => {
    const wrapper = mount(CommissionReports, {
      props: {
        employees: mockEmployees,
        initialData: mockCommissionData,
        initialSummary: mockSummary,
        initialTopEarners: mockTopEarners
      }
    })

    const statusBadges = wrapper.findAll('.inline-flex.px-2.py-1.text-xs.font-semibold.rounded-full')
    
    // Find paid status badge (should be green)
    const paidBadge = statusBadges.find(badge => badge.text() === 'paid')
    expect(paidBadge.classes()).toContain('bg-green-100')
    expect(paidBadge.classes()).toContain('text-green-800')
    
    // Find pending status badge (should be yellow)
    const pendingBadge = statusBadges.find(badge => badge.text() === 'pending')
    expect(pendingBadge.classes()).toContain('bg-yellow-100')
    expect(pendingBadge.classes()).toContain('text-yellow-800')
  })

  it('handles approve commission functionality', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve({ success: true })
    })

    const wrapper = mount(CommissionReports, {
      props: {
        employees: mockEmployees,
        initialData: mockCommissionData,
        initialSummary: mockSummary,
        initialTopEarners: mockTopEarners
      }
    })

    const approveButton = wrapper.find('button[class*="text-green-600"]')
    await approveButton.trigger('click')

    expect(fetch).toHaveBeenCalledWith('/api/employee/commissions/2/approve', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': ''
      }
    })
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

    const wrapper = mount(CommissionReports, {
      props: {
        employees: mockEmployees,
        initialData: mockCommissionData,
        initialSummary: mockSummary,
        initialTopEarners: mockTopEarners
      }
    })

    const exportButton = wrapper.find('button:first-child')
    await exportButton.trigger('click')

    expect(fetch).toHaveBeenCalledWith('/api/employee/reports/commissions/export', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': ''
      },
      body: JSON.stringify({
        employeeId: '',
        commissionType: '',
        status: '',
        period: 'current_month',
        startDate: '',
        endDate: ''
      })
    })
  })

  it('displays loading state correctly', async () => {
    const wrapper = mount(CommissionReports, {
      props: {
        employees: mockEmployees
      }
    })

    // Should show loading state initially
    expect(wrapper.find('.animate-spin').exists()).toBe(true)
  })

  it('displays empty state when no data', () => {
    const wrapper = mount(CommissionReports, {
      props: {
        employees: mockEmployees,
        initialData: [],
        initialSummary: mockSummary,
        initialTopEarners: mockTopEarners
      }
    })

    expect(wrapper.text()).toContain('No commission data')
    expect(wrapper.text()).toContain('No commission records found for the selected criteria.')
  })

  it('handles view commission details', async () => {
    const { router } = await import('@inertiajs/vue3')
    
    const wrapper = mount(CommissionReports, {
      props: {
        employees: mockEmployees,
        initialData: mockCommissionData,
        initialSummary: mockSummary,
        initialTopEarners: mockTopEarners
      }
    })

    const viewButton = wrapper.find('button[class*="text-blue-600"]')
    await viewButton.trigger('click')

    expect(router.visit).toHaveBeenCalledWith('/employee/commissions/1')
  })

  it('renders commission and type charts canvas', () => {
    const wrapper = mount(CommissionReports, {
      props: {
        employees: mockEmployees,
        initialData: mockCommissionData,
        initialSummary: mockSummary,
        initialTopEarners: mockTopEarners
      }
    })

    const canvases = wrapper.findAll('canvas')
    expect(canvases).toHaveLength(2) // Commission trends chart and type chart
  })

  it('formats dates correctly', () => {
    const wrapper = mount(CommissionReports, {
      props: {
        employees: mockEmployees,
        initialData: mockCommissionData,
        initialSummary: mockSummary,
        initialTopEarners: mockTopEarners
      }
    })

    // Check that dates are displayed (exact format may vary based on locale)
    expect(wrapper.text()).toContain('2024')
  })
})