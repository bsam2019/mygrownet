import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import CommissionCalculator from '@/components/Employee/CommissionCalculator.vue'

// Mock the formatting utility
vi.mock('@/utils/formatting', () => ({
  formatCurrency: vi.fn((amount) => `$${amount.toFixed(2)}`)
}))

// Mock fetch
global.fetch = vi.fn()

describe('CommissionCalculator', () => {
  let wrapper

  const mockEmployees = [
    { id: 1, name: 'John Doe', position: 'Sales Manager', department: 'Sales' },
    { id: 2, name: 'Jane Smith', position: 'Account Executive', department: 'Sales' }
  ]

  const mockCalculationResult = {
    totalCommission: 1500.00,
    baseAmount: 10000.00,
    commissionRate: 15,
    breakdown: [
      { type: 'Sales Commission', baseAmount: 8000, rate: 10, commission: 800 },
      { type: 'Performance Bonus', baseAmount: 2000, rate: 35, commission: 700 }
    ],
    period: 'current_month',
    employeeName: 'John Doe'
  }

  beforeEach(() => {
    vi.clearAllMocks()
    fetch.mockClear()
  })

  it('renders commission calculator form', () => {
    wrapper = mount(CommissionCalculator, {
      props: { employees: mockEmployees }
    })

    expect(wrapper.find('h2').text()).toBe('Commission Calculator')
    expect(wrapper.find('#employee').exists()).toBe(true)
    expect(wrapper.find('#period').exists()).toBe(true)
    expect(wrapper.find('#commissionType').exists()).toBe(true)
  })

  it('populates employee dropdown with provided employees', () => {
    wrapper = mount(CommissionCalculator, {
      props: { employees: mockEmployees }
    })

    const employeeSelect = wrapper.find('#employee')
    const options = employeeSelect.findAll('option')
    
    expect(options).toHaveLength(3) // Including default option
    expect(options[1].text()).toBe('John Doe - Sales Manager')
    expect(options[2].text()).toBe('Jane Smith - Account Executive')
  })

  it('shows custom date fields when custom period is selected', async () => {
    wrapper = mount(CommissionCalculator, {
      props: { employees: mockEmployees }
    })

    expect(wrapper.find('#startDate').exists()).toBe(false)
    expect(wrapper.find('#endDate').exists()).toBe(false)

    await wrapper.find('#period').setValue('custom')

    expect(wrapper.find('#startDate').exists()).toBe(true)
    expect(wrapper.find('#endDate').exists()).toBe(true)
  })

  it('disables calculate button when required fields are missing', () => {
    wrapper = mount(CommissionCalculator, {
      props: { employees: mockEmployees }
    })

    const calculateButton = wrapper.find('button[type="submit"]')
    expect(calculateButton.attributes('disabled')).toBeDefined()
  })

  it('enables calculate button when all required fields are filled', async () => {
    wrapper = mount(CommissionCalculator, {
      props: { employees: mockEmployees }
    })

    await wrapper.find('#employee').setValue('1')
    await wrapper.find('#period').setValue('current_month')
    await wrapper.find('#commissionType').setValue('sales')

    const calculateButton = wrapper.find('button')
    expect(calculateButton.attributes('disabled')).toBeUndefined()
  })

  it('calls API to calculate commission when form is submitted', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: mockCalculationResult })
    })

    wrapper = mount(CommissionCalculator, {
      props: { employees: mockEmployees }
    })

    await wrapper.find('#employee').setValue('1')
    await wrapper.find('#period').setValue('current_month')
    await wrapper.find('#commissionType').setValue('sales')

    await wrapper.find('form').trigger('submit.prevent')

    expect(fetch).toHaveBeenCalledWith('/api/employees/commissions/calculate', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': ''
      },
      body: JSON.stringify({
        employeeId: '1',
        period: 'current_month',
        startDate: '',
        endDate: '',
        commissionType: 'sales',
        baseSalary: null
      })
    })
  })

  it('displays calculation results when API call succeeds', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: mockCalculationResult })
    })

    wrapper = mount(CommissionCalculator, {
      props: { employees: mockEmployees }
    })

    await wrapper.find('#employee').setValue('1')
    await wrapper.find('#period').setValue('current_month')
    await wrapper.find('#commissionType').setValue('sales')

    await wrapper.find('form').trigger('submit.prevent')
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('Commission Calculation Results')
    expect(wrapper.text()).toContain('$1500.00')
    expect(wrapper.text()).toContain('15%')
  })

  it('displays breakdown table with commission details', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: mockCalculationResult })
    })

    wrapper = mount(CommissionCalculator, {
      props: { employees: mockEmployees }
    })

    await wrapper.find('#employee').setValue('1')
    await wrapper.find('#period').setValue('current_month')
    await wrapper.find('#commissionType').setValue('sales')

    await wrapper.find('form').trigger('submit.prevent')
    await wrapper.vm.$nextTick()

    const table = wrapper.find('table')
    expect(table.exists()).toBe(true)
    
    const rows = table.findAll('tbody tr')
    expect(rows).toHaveLength(2)
    expect(rows[0].text()).toContain('Sales Commission')
    expect(rows[1].text()).toContain('Performance Bonus')
  })

  it('emits commissionCalculated event when calculation succeeds', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: mockCalculationResult })
    })

    wrapper = mount(CommissionCalculator, {
      props: { employees: mockEmployees }
    })

    await wrapper.find('#employee').setValue('1')
    await wrapper.find('#period').setValue('current_month')
    await wrapper.find('#commissionType').setValue('sales')

    await wrapper.find('form').trigger('submit.prevent')
    await wrapper.vm.$nextTick()

    expect(wrapper.emitted('commissionCalculated')).toBeTruthy()
    expect(wrapper.emitted('commissionCalculated')[0][0]).toEqual(mockCalculationResult)
  })

  it('displays error message when API call fails', async () => {
    fetch.mockRejectedValueOnce(new Error('API Error'))

    wrapper = mount(CommissionCalculator, {
      props: { employees: mockEmployees }
    })

    await wrapper.find('#employee').setValue('1')
    await wrapper.find('#period').setValue('current_month')
    await wrapper.find('#commissionType').setValue('sales')

    await wrapper.find('form').trigger('submit.prevent')
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('API Error')
  })

  it('shows loading state during calculation', async () => {
    let resolvePromise
    const promise = new Promise(resolve => {
      resolvePromise = resolve
    })
    
    fetch.mockReturnValueOnce(promise)

    wrapper = mount(CommissionCalculator, {
      props: { employees: mockEmployees }
    })

    await wrapper.find('#employee').setValue('1')
    await wrapper.find('#period').setValue('current_month')
    await wrapper.find('#commissionType').setValue('sales')

    await wrapper.find('form').trigger('submit.prevent')
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('Calculating...')

    resolvePromise({
      ok: true,
      json: async () => ({ data: mockCalculationResult })
    })
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).not.toContain('Calculating...')
  })

  it('calls save commission API when save button is clicked', async () => {
    fetch
      .mockResolvedValueOnce({
        ok: true,
        json: async () => ({ data: mockCalculationResult })
      })
      .mockResolvedValueOnce({
        ok: true,
        json: async () => ({ success: true })
      })

    wrapper = mount(CommissionCalculator, {
      props: { employees: mockEmployees }
    })

    await wrapper.find('#employee').setValue('1')
    await wrapper.find('#period').setValue('current_month')
    await wrapper.find('#commissionType').setValue('sales')

    await wrapper.find('form').trigger('submit.prevent')
    await wrapper.vm.$nextTick()

    const saveButton = wrapper.find('button:contains("Save Commission")')
    await saveButton.trigger('click')

    expect(fetch).toHaveBeenCalledWith('/api/employees/commissions', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': ''
      },
      body: JSON.stringify({
        employeeId: '1',
        period: 'current_month',
        startDate: '',
        endDate: '',
        commissionType: 'sales',
        baseSalary: null,
        ...mockCalculationResult
      })
    })
  })

  it('emits exportRequested event when export button is clicked', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: mockCalculationResult })
    })

    wrapper = mount(CommissionCalculator, {
      props: { employees: mockEmployees }
    })

    await wrapper.find('#employee').setValue('1')
    await wrapper.find('#period').setValue('current_month')
    await wrapper.find('#commissionType').setValue('sales')

    await wrapper.find('form').trigger('submit.prevent')
    await wrapper.vm.$nextTick()

    const exportButton = wrapper.find('button:contains("Export Results")')
    await exportButton.trigger('click')

    expect(wrapper.emitted('exportRequested')).toBeTruthy()
    expect(wrapper.emitted('exportRequested')[0][0]).toEqual(mockCalculationResult)
  })

  it('loads employees from API when not provided as props', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: mockEmployees })
    })

    wrapper = mount(CommissionCalculator)
    await wrapper.vm.$nextTick()

    expect(fetch).toHaveBeenCalledWith('/api/employees')
  })

  it('sets initial employee when initialEmployeeId prop is provided', () => {
    wrapper = mount(CommissionCalculator, {
      props: { 
        employees: mockEmployees,
        initialEmployeeId: 1
      }
    })

    expect(wrapper.find('#employee').element.value).toBe('1')
  })
})