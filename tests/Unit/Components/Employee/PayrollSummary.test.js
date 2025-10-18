import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import PayrollSummary from '@/components/Employee/PayrollSummary.vue'

// Mock the formatting utility
vi.mock('@/utils/formatting', () => ({
  formatCurrency: vi.fn((amount) => `$${amount.toFixed(2)}`)
}))

// Mock fetch
global.fetch = vi.fn()

describe('PayrollSummary', () => {
  let wrapper

  const mockPayrollData = {
    summary: {
      totalEmployees: 25,
      totalGrossPay: 125000.00,
      totalDeductions: 25000.00,
      totalNetPay: 100000.00
    },
    departmentBreakdown: [
      { department: 'Sales', employeeCount: 10, totalPay: 50000.00 },
      { department: 'Marketing', employeeCount: 8, totalPay: 30000.00 },
      { department: 'Engineering', employeeCount: 7, totalPay: 45000.00 }
    ],
    employees: [
      {
        id: 1,
        name: 'John Doe',
        position: 'Sales Manager',
        department: 'Sales',
        baseSalary: 5000.00,
        commission: 1500.00,
        bonuses: 500.00,
        deductions: 1200.00,
        netPay: 5800.00,
        status: 'paid'
      },
      {
        id: 2,
        name: 'Jane Smith',
        position: 'Developer',
        department: 'Engineering',
        baseSalary: 6000.00,
        commission: 0.00,
        bonuses: 1000.00,
        deductions: 1400.00,
        netPay: 5600.00,
        status: 'pending'
      }
    ],
    period: 'current_month'
  }

  beforeEach(() => {
    vi.clearAllMocks()
    fetch.mockClear()
  })

  it('renders payroll summary with title and controls', () => {
    wrapper = mount(PayrollSummary)

    expect(wrapper.find('h2').text()).toBe('Payroll Summary')
    expect(wrapper.find('select').exists()).toBe(true)
    expect(wrapper.find('button:contains("Export")').exists()).toBe(true)
  })

  it('displays loading state initially', () => {
    fetch.mockImplementation(() => new Promise(() => {})) // Never resolves

    wrapper = mount(PayrollSummary)

    expect(wrapper.find('.animate-spin').exists()).toBe(true)
  })

  it('loads payroll data on mount', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: mockPayrollData })
    })

    wrapper = mount(PayrollSummary)
    await wrapper.vm.$nextTick()

    expect(fetch).toHaveBeenCalledWith('/api/payroll/summary?period=current_month')
  })

  it('displays summary cards with correct data', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: mockPayrollData })
    })

    wrapper = mount(PayrollSummary)
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('25') // Total employees
    expect(wrapper.text()).toContain('$125000.00') // Total gross pay
    expect(wrapper.text()).toContain('$25000.00') // Total deductions
    expect(wrapper.text()).toContain('$100000.00') // Total net pay
  })

  it('displays department breakdown', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: mockPayrollData })
    })

    wrapper = mount(PayrollSummary)
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('Department Breakdown')
    expect(wrapper.text()).toContain('Sales')
    expect(wrapper.text()).toContain('10 employees')
    expect(wrapper.text()).toContain('Marketing')
    expect(wrapper.text()).toContain('8 employees')
    expect(wrapper.text()).toContain('Engineering')
    expect(wrapper.text()).toContain('7 employees')
  })

  it('displays employee details table', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: mockPayrollData })
    })

    wrapper = mount(PayrollSummary)
    await wrapper.vm.$nextTick()

    const table = wrapper.find('table')
    expect(table.exists()).toBe(true)

    const rows = table.findAll('tbody tr')
    expect(rows).toHaveLength(2)

    // Check first employee row
    expect(rows[0].text()).toContain('John Doe')
    expect(rows[0].text()).toContain('Sales Manager')
    expect(rows[0].text()).toContain('$5000.00')
    expect(rows[0].text()).toContain('paid')

    // Check second employee row
    expect(rows[1].text()).toContain('Jane Smith')
    expect(rows[1].text()).toContain('Developer')
    expect(rows[1].text()).toContain('$6000.00')
    expect(rows[1].text()).toContain('pending')
  })

  it('applies correct status styling', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: mockPayrollData })
    })

    wrapper = mount(PayrollSummary)
    await wrapper.vm.$nextTick()

    const statusElements = wrapper.findAll('.rounded-full')
    expect(statusElements[0].classes()).toContain('bg-green-100')
    expect(statusElements[0].classes()).toContain('text-green-800')
    expect(statusElements[1].classes()).toContain('bg-yellow-100')
    expect(statusElements[1].classes()).toContain('text-yellow-800')
  })

  it('reloads data when period is changed', async () => {
    fetch
      .mockResolvedValueOnce({
        ok: true,
        json: async () => ({ data: mockPayrollData })
      })
      .mockResolvedValueOnce({
        ok: true,
        json: async () => ({ data: { ...mockPayrollData, period: 'last_month' } })
      })

    wrapper = mount(PayrollSummary)
    await wrapper.vm.$nextTick()

    await wrapper.find('select').setValue('last_month')

    expect(fetch).toHaveBeenCalledWith('/api/payroll/summary?period=last_month')
  })

  it('processes payroll when process button is clicked', async () => {
    fetch
      .mockResolvedValueOnce({
        ok: true,
        json: async () => ({ data: mockPayrollData })
      })
      .mockResolvedValueOnce({
        ok: true,
        json: async () => ({ data: mockPayrollData })
      })

    wrapper = mount(PayrollSummary)
    await wrapper.vm.$nextTick()

    const processButton = wrapper.find('button:contains("Process Payroll")')
    await processButton.trigger('click')

    expect(fetch).toHaveBeenCalledWith('/api/payroll/process', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': ''
      },
      body: JSON.stringify({
        period: 'current_month',
        employees: [1, 2]
      })
    })
  })

  it('emits payrollProcessed event when processing succeeds', async () => {
    fetch
      .mockResolvedValueOnce({
        ok: true,
        json: async () => ({ data: mockPayrollData })
      })
      .mockResolvedValueOnce({
        ok: true,
        json: async () => ({ data: mockPayrollData })
      })

    wrapper = mount(PayrollSummary)
    await wrapper.vm.$nextTick()

    const processButton = wrapper.find('button:contains("Process Payroll")')
    await processButton.trigger('click')
    await wrapper.vm.$nextTick()

    expect(wrapper.emitted('payrollProcessed')).toBeTruthy()
    expect(wrapper.emitted('payrollProcessed')[0][0]).toEqual(mockPayrollData)
  })

  it('shows processing state when processing payroll', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: mockPayrollData })
    })

    let resolveProcessing
    const processingPromise = new Promise(resolve => {
      resolveProcessing = resolve
    })
    
    fetch.mockReturnValueOnce(processingPromise)

    wrapper = mount(PayrollSummary)
    await wrapper.vm.$nextTick()

    const processButton = wrapper.find('button:contains("Process Payroll")')
    await processButton.trigger('click')
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('Processing...')
    expect(processButton.attributes('disabled')).toBeDefined()

    resolveProcessing({
      ok: true,
      json: async () => ({ data: mockPayrollData })
    })
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).not.toContain('Processing...')
  })

  it('emits reportsGenerated event when generate reports is clicked', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: mockPayrollData })
    })

    wrapper = mount(PayrollSummary)
    await wrapper.vm.$nextTick()

    const reportsButton = wrapper.find('button:contains("Generate Reports")')
    await reportsButton.trigger('click')

    expect(wrapper.emitted('reportsGenerated')).toBeTruthy()
    expect(wrapper.emitted('reportsGenerated')[0][0]).toEqual(mockPayrollData)
  })

  it('emits dataExported event when export is clicked', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: mockPayrollData })
    })

    wrapper = mount(PayrollSummary)
    await wrapper.vm.$nextTick()

    const exportButton = wrapper.find('button:contains("Export")')
    await exportButton.trigger('click')

    expect(wrapper.emitted('dataExported')).toBeTruthy()
    expect(wrapper.emitted('dataExported')[0][0]).toEqual(mockPayrollData)
  })

  it('displays error message when API call fails', async () => {
    fetch.mockRejectedValueOnce(new Error('API Error'))

    wrapper = mount(PayrollSummary)
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('API Error')
    expect(wrapper.find('button:contains("Retry")').exists()).toBe(true)
  })

  it('retries loading data when retry button is clicked', async () => {
    fetch
      .mockRejectedValueOnce(new Error('API Error'))
      .mockResolvedValueOnce({
        ok: true,
        json: async () => ({ data: mockPayrollData })
      })

    wrapper = mount(PayrollSummary)
    await wrapper.vm.$nextTick()

    const retryButton = wrapper.find('button:contains("Retry")')
    await retryButton.trigger('click')

    expect(fetch).toHaveBeenCalledTimes(2)
  })

  it('uses initial period prop when provided', () => {
    wrapper = mount(PayrollSummary, {
      props: { initialPeriod: 'last_quarter' }
    })

    expect(wrapper.find('select').element.value).toBe('last_quarter')
  })

  it('formats period names correctly', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: mockPayrollData })
    })

    wrapper = mount(PayrollSummary)
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('Current Month')
  })

  it('displays employee count correctly in footer', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: mockPayrollData })
    })

    wrapper = mount(PayrollSummary)
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('Showing 2 employees for Current Month')
  })
})