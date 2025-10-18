import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import CommissionHistory from '@/components/Employee/CommissionHistory.vue'

// Mock the formatting utility
vi.mock('@/utils/formatting', () => ({
  formatCurrency: vi.fn((amount) => `$${amount.toFixed(2)}`)
}))

// Mock fetch
global.fetch = vi.fn()

describe('CommissionHistory', () => {
  let wrapper

  const mockCommissions = [
    {
      id: 1,
      employeeName: 'John Doe',
      department: 'Sales',
      type: 'sales',
      amount: 1500.00,
      status: 'paid',
      createdAt: '2024-01-15T10:00:00Z',
      paidAt: '2024-01-20T14:30:00Z'
    },
    {
      id: 2,
      employeeName: 'Jane Smith',
      department: 'Marketing',
      type: 'performance',
      amount: 800.00,
      status: 'pending',
      createdAt: '2024-01-10T09:15:00Z'
    },
    {
      id: 3,
      employeeName: 'Bob Johnson',
      department: 'Sales',
      type: 'referral',
      amount: 300.00,
      status: 'cancelled',
      createdAt: '2024-01-05T16:45:00Z'
    }
  ]

  beforeEach(() => {
    vi.clearAllMocks()
    fetch.mockClear()
  })

  it('renders commission history with title and filters', () => {
    wrapper = mount(CommissionHistory)

    expect(wrapper.find('h2').text()).toBe('Commission History')
    expect(wrapper.find('select[v-model="filters.period"]').exists()).toBe(true)
    expect(wrapper.find('select[v-model="filters.status"]').exists()).toBe(true)
    expect(wrapper.find('input[placeholder*="Search"]').exists()).toBe(true)
  })

  it('loads commission data on mount', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: mockCommissions })
    })

    wrapper = mount(CommissionHistory)
    await wrapper.vm.$nextTick()

    expect(fetch).toHaveBeenCalledWith('/api/employees/commissions?period=all&status=all&type=all')
  })

  it('displays summary statistics correctly', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: mockCommissions })
    })

    wrapper = mount(CommissionHistory)
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('3') // Total commissions
    expect(wrapper.text()).toContain('$2600.00') // Total amount (1500 + 800 + 300)
    expect(wrapper.text()).toContain('$800.00') // Pending amount
    expect(wrapper.text()).toContain('$1500.00') // Paid amount
  })

  it('displays commission table with correct data', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: mockCommissions })
    })

    wrapper = mount(CommissionHistory)
    await wrapper.vm.$nextTick()

    const table = wrapper.find('table')
    expect(table.exists()).toBe(true)

    const rows = table.findAll('tbody tr')
    expect(rows).toHaveLength(3)

    // Check first row
    expect(rows[0].text()).toContain('John Doe')
    expect(rows[0].text()).toContain('Sales')
    expect(rows[0].text()).toContain('$1500.00')
    expect(rows[0].text()).toContain('paid')

    // Check second row
    expect(rows[1].text()).toContain('Jane Smith')
    expect(rows[1].text()).toContain('performance')
    expect(rows[1].text()).toContain('$800.00')
    expect(rows[1].text()).toContain('pending')
  })

  it('applies correct status styling', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: mockCommissions })
    })

    wrapper = mount(CommissionHistory)
    await wrapper.vm.$nextTick()

    const statusBadges = wrapper.findAll('.rounded-full')
    
    // Find status badges (not type badges)
    const paidBadge = statusBadges.find(badge => badge.text() === 'paid')
    const pendingBadge = statusBadges.find(badge => badge.text() === 'pending')
    const cancelledBadge = statusBadges.find(badge => badge.text() === 'cancelled')

    expect(paidBadge.classes()).toContain('bg-green-100')
    expect(pendingBadge.classes()).toContain('bg-yellow-100')
    expect(cancelledBadge.classes()).toContain('bg-red-100')
  })

  it('applies correct type styling', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: mockCommissions })
    })

    wrapper = mount(CommissionHistory)
    await wrapper.vm.$nextTick()

    const typeBadges = wrapper.findAll('.rounded-full')
    
    const salesBadge = typeBadges.find(badge => badge.text() === 'sales')
    const performanceBadge = typeBadges.find(badge => badge.text() === 'performance')
    const referralBadge = typeBadges.find(badge => badge.text() === 'referral')

    expect(salesBadge.classes()).toContain('bg-blue-100')
    expect(performanceBadge.classes()).toContain('bg-green-100')
    expect(referralBadge.classes()).toContain('bg-purple-100')
  })

  it('filters commissions by search term', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: mockCommissions })
    })

    wrapper = mount(CommissionHistory)
    await wrapper.vm.$nextTick()

    const searchInput = wrapper.find('input[placeholder*="Search"]')
    await searchInput.setValue('John')

    // Should show only John Doe's commission
    const rows = wrapper.findAll('tbody tr')
    expect(rows).toHaveLength(1)
    expect(rows[0].text()).toContain('John Doe')
  })

  it('filters commissions by status', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: mockCommissions })
    })

    wrapper = mount(CommissionHistory)
    await wrapper.vm.$nextTick()

    const statusSelect = wrapper.find('select[v-model="filters.status"]')
    await statusSelect.setValue('pending')

    // Should show only pending commissions
    const rows = wrapper.findAll('tbody tr')
    expect(rows).toHaveLength(1)
    expect(rows[0].text()).toContain('Jane Smith')
    expect(rows[0].text()).toContain('pending')
  })

  it('filters commissions by type', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: mockCommissions })
    })

    wrapper = mount(CommissionHistory)
    await wrapper.vm.$nextTick()

    const typeSelect = wrapper.find('select[v-model="filters.type"]')
    await typeSelect.setValue('sales')

    // Should show only sales commissions
    const rows = wrapper.findAll('tbody tr')
    expect(rows).toHaveLength(1)
    expect(rows[0].text()).toContain('John Doe')
    expect(rows[0].text()).toContain('sales')
  })

  it('sorts commissions when column header is clicked', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: mockCommissions })
    })

    wrapper = mount(CommissionHistory)
    await wrapper.vm.$nextTick()

    const amountHeader = wrapper.find('button:contains("Amount")')
    await amountHeader.trigger('click')

    // Should sort by amount descending (highest first)
    const rows = wrapper.findAll('tbody tr')
    expect(rows[0].text()).toContain('$1500.00')
    expect(rows[1].text()).toContain('$800.00')
    expect(rows[2].text()).toContain('$300.00')
  })

  it('shows action buttons for pending commissions', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: mockCommissions })
    })

    wrapper = mount(CommissionHistory)
    await wrapper.vm.$nextTick()

    const rows = wrapper.findAll('tbody tr')
    
    // Pending commission should have Mark Paid and Cancel buttons
    const pendingRow = rows.find(row => row.text().includes('pending'))
    expect(pendingRow.text()).toContain('Mark Paid')
    expect(pendingRow.text()).toContain('Cancel')

    // Paid commission should not have these buttons
    const paidRow = rows.find(row => row.text().includes('paid'))
    expect(paidRow.text()).not.toContain('Mark Paid')
    expect(paidRow.text()).not.toContain('Cancel')
  })

  it('marks commission as paid when Mark Paid is clicked', async () => {
    fetch
      .mockResolvedValueOnce({
        ok: true,
        json: async () => ({ data: mockCommissions })
      })
      .mockResolvedValueOnce({
        ok: true,
        json: async () => ({ success: true })
      })

    wrapper = mount(CommissionHistory)
    await wrapper.vm.$nextTick()

    const markPaidButton = wrapper.find('button:contains("Mark Paid")')
    await markPaidButton.trigger('click')

    expect(fetch).toHaveBeenCalledWith('/api/employees/commissions/2/mark-paid', {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': ''
      }
    })
  })

  it('emits commissionPaid event when marking as paid succeeds', async () => {
    fetch
      .mockResolvedValueOnce({
        ok: true,
        json: async () => ({ data: mockCommissions })
      })
      .mockResolvedValueOnce({
        ok: true,
        json: async () => ({ success: true })
      })

    wrapper = mount(CommissionHistory)
    await wrapper.vm.$nextTick()

    const markPaidButton = wrapper.find('button:contains("Mark Paid")')
    await markPaidButton.trigger('click')
    await wrapper.vm.$nextTick()

    expect(wrapper.emitted('commissionPaid')).toBeTruthy()
  })

  it('cancels commission when Cancel is clicked and confirmed', async () => {
    // Mock window.confirm
    window.confirm = vi.fn(() => true)

    fetch
      .mockResolvedValueOnce({
        ok: true,
        json: async () => ({ data: mockCommissions })
      })
      .mockResolvedValueOnce({
        ok: true,
        json: async () => ({ success: true })
      })

    wrapper = mount(CommissionHistory)
    await wrapper.vm.$nextTick()

    const cancelButton = wrapper.find('button:contains("Cancel")')
    await cancelButton.trigger('click')

    expect(window.confirm).toHaveBeenCalledWith('Are you sure you want to cancel this commission?')
    expect(fetch).toHaveBeenCalledWith('/api/employees/commissions/2', {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': ''
      }
    })
  })

  it('does not cancel commission when not confirmed', async () => {
    window.confirm = vi.fn(() => false)

    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: mockCommissions })
    })

    wrapper = mount(CommissionHistory)
    await wrapper.vm.$nextTick()

    const cancelButton = wrapper.find('button:contains("Cancel")')
    await cancelButton.trigger('click')

    expect(fetch).toHaveBeenCalledTimes(1) // Only the initial load
  })

  it('emits commissionViewed event when View is clicked', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: mockCommissions })
    })

    wrapper = mount(CommissionHistory)
    await wrapper.vm.$nextTick()

    const viewButton = wrapper.find('button:contains("View")')
    await viewButton.trigger('click')

    expect(wrapper.emitted('commissionViewed')).toBeTruthy()
    expect(wrapper.emitted('commissionViewed')[0][0]).toEqual(mockCommissions[0])
  })

  it('emits dataExported event when Export is clicked', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: mockCommissions })
    })

    wrapper = mount(CommissionHistory)
    await wrapper.vm.$nextTick()

    const exportButton = wrapper.find('button:contains("Export")')
    await exportButton.trigger('click')

    expect(wrapper.emitted('dataExported')).toBeTruthy()
    expect(wrapper.emitted('dataExported')[0][0]).toEqual(mockCommissions)
  })

  it('displays pagination controls', async () => {
    const manyCommissions = Array.from({ length: 25 }, (_, i) => ({
      ...mockCommissions[0],
      id: i + 1,
      employeeName: `Employee ${i + 1}`
    }))

    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: manyCommissions })
    })

    wrapper = mount(CommissionHistory)
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('1 of 3') // 25 items, 10 per page = 3 pages
    expect(wrapper.find('button:contains("Previous")').exists()).toBe(true)
    expect(wrapper.find('button:contains("Next")').exists()).toBe(true)
  })

  it('navigates between pages', async () => {
    const manyCommissions = Array.from({ length: 25 }, (_, i) => ({
      ...mockCommissions[0],
      id: i + 1,
      employeeName: `Employee ${i + 1}`
    }))

    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: manyCommissions })
    })

    wrapper = mount(CommissionHistory)
    await wrapper.vm.$nextTick()

    const nextButton = wrapper.find('button:contains("Next")')
    await nextButton.trigger('click')

    expect(wrapper.text()).toContain('2 of 3')
  })

  it('shows empty state when no commissions found', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: [] })
    })

    wrapper = mount(CommissionHistory)
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('No commissions found')
    expect(wrapper.text()).toContain('No commission records match your current filters')
  })

  it('displays error message when API call fails', async () => {
    fetch.mockRejectedValueOnce(new Error('API Error'))

    wrapper = mount(CommissionHistory)
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('API Error')
  })

  it('includes employee filter when employeeId prop is provided', async () => {
    fetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ data: mockCommissions })
    })

    wrapper = mount(CommissionHistory, {
      props: { employeeId: 123 }
    })
    await wrapper.vm.$nextTick()

    expect(fetch).toHaveBeenCalledWith('/api/employees/commissions?period=all&status=all&type=all&employee_id=123')
  })

  it('debounces search input', async () => {
    vi.useFakeTimers()
    
    fetch.mockResolvedValue({
      ok: true,
      json: async () => ({ data: mockCommissions })
    })

    wrapper = mount(CommissionHistory)
    await wrapper.vm.$nextTick()

    const searchInput = wrapper.find('input[placeholder*="Search"]')
    await searchInput.setValue('test')

    // Should not call API immediately
    expect(fetch).toHaveBeenCalledTimes(1)

    // Fast-forward time
    vi.advanceTimersByTime(300)
    await wrapper.vm.$nextTick()

    // Should call API after debounce
    expect(fetch).toHaveBeenCalledTimes(2)

    vi.useRealTimers()
  })
})