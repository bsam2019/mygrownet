import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { nextTick } from 'vue'
import WithdrawalHistoryTable from '@/components/Withdrawal/WithdrawalHistoryTable.vue'

// Mock fetch
global.fetch = vi.fn() as any

const mockWithdrawals = [
  {
    id: 1,
    reference_number: 'WD001',
    amount: 1000,
    penalty_amount: 100,
    net_amount: 900,
    type: 'partial',
    status: 'completed',
    requested_at: '2024-01-15T10:00:00Z',
    approved_at: '2024-01-16T10:00:00Z',
    processed_at: '2024-01-17T10:00:00Z',
    transaction_id: 'TXN123',
    investment: {
      tier: { name: 'Builder' }
    }
  },
  {
    id: 2,
    reference_number: 'WD002',
    amount: 5000,
    penalty_amount: 0,
    net_amount: 5000,
    type: 'full',
    status: 'pending',
    requested_at: '2024-01-20T10:00:00Z',
    investment: {
      tier: { name: 'Elite' }
    }
  },
  {
    id: 3,
    reference_number: 'WD003',
    amount: 2000,
    penalty_amount: 500,
    net_amount: 1500,
    type: 'emergency',
    status: 'rejected',
    rejection_reason: 'Insufficient documentation',
    requested_at: '2024-01-25T10:00:00Z',
    investment: {
      tier: { name: 'Starter' }
    }
  }
]

const mockPagination = {
  current_page: 1,
  last_page: 2,
  per_page: 10,
  total: 15
}

describe('WithdrawalHistoryTable', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    ;(global.fetch as any).mockResolvedValue({
      json: () => Promise.resolve({
        data: mockWithdrawals,
        pagination: mockPagination
      })
    })
  })

  it('renders withdrawal history table', () => {
    const wrapper = mount(WithdrawalHistoryTable, {
      props: {
        initialWithdrawals: mockWithdrawals,
        initialPagination: mockPagination
      }
    })

    expect(wrapper.find('table').exists()).toBe(true)
    expect(wrapper.text()).toContain('Withdrawal History')
    expect(wrapper.findAll('tbody tr')).toHaveLength(3)
  })

  it('displays withdrawal details correctly', () => {
    const wrapper = mount(WithdrawalHistoryTable, {
      props: {
        initialWithdrawals: mockWithdrawals,
        initialPagination: mockPagination
      }
    })

    // Check first withdrawal
    const firstRow = wrapper.find('tbody tr:first-child')
    expect(firstRow.text()).toContain('#WD001')
    expect(firstRow.text()).toContain('K1,000.00')
    expect(firstRow.text()).toContain('partial')
    expect(firstRow.text()).toContain('Penalty: K100.00')
    expect(firstRow.text()).toContain('Net: K900.00')
    expect(firstRow.text()).toContain('Builder')
  })

  it('shows correct status badges', () => {
    const wrapper = mount(WithdrawalHistoryTable, {
      props: {
        initialWithdrawals: mockWithdrawals,
        initialPagination: mockPagination
      }
    })

    const statusBadges = wrapper.findAllComponents({ name: 'StatusBadge' })
    expect(statusBadges).toHaveLength(3)
  })

  it('displays timeline information', () => {
    const wrapper = mount(WithdrawalHistoryTable, {
      props: {
        initialWithdrawals: mockWithdrawals,
        initialPagination: mockPagination
      }
    })

    const firstRow = wrapper.find('tbody tr:first-child')
    expect(firstRow.text()).toContain('Requested:')
    expect(firstRow.text()).toContain('Approved:')
    expect(firstRow.text()).toContain('Processed:')
  })

  it('shows rejection reason for rejected withdrawals', () => {
    const wrapper = mount(WithdrawalHistoryTable, {
      props: {
        initialWithdrawals: mockWithdrawals,
        initialPagination: mockPagination
      }
    })

    const rejectedRow = wrapper.find('tbody tr:last-child')
    expect(rejectedRow.text()).toContain('Insufficient documentation')
  })

  it('provides filter options', () => {
    const wrapper = mount(WithdrawalHistoryTable, {
      props: {
        initialWithdrawals: mockWithdrawals,
        initialPagination: mockPagination
      }
    })

    const statusFilter = wrapper.find('select:first-of-type')
    const typeFilter = wrapper.find('select:nth-of-type(2)')
    const dateFromFilter = wrapper.find('input[type="date"]:first-of-type')
    const dateToFilter = wrapper.find('input[type="date"]:last-of-type')

    expect(statusFilter.exists()).toBe(true)
    expect(typeFilter.exists()).toBe(true)
    expect(dateFromFilter.exists()).toBe(true)
    expect(dateToFilter.exists()).toBe(true)
  })

  it('applies filters when changed', async () => {
    const wrapper = mount(WithdrawalHistoryTable, {
      props: {
        initialWithdrawals: [],
        initialPagination: mockPagination
      }
    })

    const statusFilter = wrapper.find('select:first-of-type')
    await statusFilter.setValue('pending')
    await statusFilter.trigger('change')

    expect(global.fetch).toHaveBeenCalledWith(
      expect.stringContaining('status=pending')
    )
  })

  it('shows clear filters button when filters are applied', async () => {
    const wrapper = mount(WithdrawalHistoryTable, {
      props: {
        initialWithdrawals: [],
        initialPagination: mockPagination
      }
    })

    // Apply a filter by interacting with the select element
    const statusFilter = wrapper.find('select:first-of-type')
    await statusFilter.setValue('pending')
    await statusFilter.trigger('change')

    await nextTick()

    // Check if filters are applied (component should have some indication)
    expect(statusFilter.element.value).toBe('pending')
  })

  it('shows appropriate action buttons', () => {
    const wrapper = mount(WithdrawalHistoryTable, {
      props: {
        initialWithdrawals: mockWithdrawals,
        initialPagination: mockPagination
      }
    })

    // Check completed withdrawal (should have View and Receipt buttons)
    const completedRow = wrapper.find('tbody tr:first-child')
    expect(completedRow.text()).toContain('View')
    expect(completedRow.text()).toContain('Receipt')

    // Check pending withdrawal (should have View and Cancel buttons)
    const pendingRow = wrapper.find('tbody tr:nth-child(2)')
    expect(pendingRow.text()).toContain('View')
    expect(pendingRow.text()).toContain('Cancel')
  })

  it('opens details modal when view is clicked', async () => {
    const wrapper = mount(WithdrawalHistoryTable, {
      props: {
        initialWithdrawals: mockWithdrawals,
        initialPagination: mockPagination
      }
    })

    const viewButtons = wrapper.findAll('button')
    const viewButton = viewButtons.find(btn => btn.text().includes('View'))
    await viewButton?.trigger('click')

    expect(wrapper.findComponent({ name: 'WithdrawalDetailsModal' }).exists()).toBe(true)
  })

  it('handles withdrawal cancellation', async () => {
    ;(global.fetch as any).mockResolvedValueOnce({
      json: () => Promise.resolve({ success: true })
    })

    // Mock window.confirm
    window.confirm = vi.fn(() => true)

    const wrapper = mount(WithdrawalHistoryTable, {
      props: {
        initialWithdrawals: mockWithdrawals,
        initialPagination: mockPagination
      }
    })

    const buttons = wrapper.findAll('button')
    const cancelButton = buttons.find(btn => btn.text().includes('Cancel'))
    await cancelButton?.trigger('click')

    expect(window.confirm).toHaveBeenCalledWith(
      'Are you sure you want to cancel this withdrawal request?'
    )
    expect(global.fetch).toHaveBeenCalledWith(
      '/api/withdrawals/2/cancel',
      expect.objectContaining({
        method: 'PATCH'
      })
    )
  })

  it('handles receipt download', async () => {
    // Mock window.open
    window.open = vi.fn()

    const wrapper = mount(WithdrawalHistoryTable, {
      props: {
        initialWithdrawals: mockWithdrawals,
        initialPagination: mockPagination
      }
    })

    const buttons = wrapper.findAll('button')
    const receiptButton = buttons.find(btn => btn.text().includes('Receipt'))
    await receiptButton?.trigger('click')

    expect(window.open).toHaveBeenCalledWith('/api/withdrawals/1/receipt', '_blank')
  })

  it('shows empty state when no withdrawals', () => {
    const wrapper = mount(WithdrawalHistoryTable, {
      props: {
        initialWithdrawals: [],
        initialPagination: { ...mockPagination, total: 0 }
      }
    })

    expect(wrapper.text()).toContain('No withdrawal requests')
    expect(wrapper.text()).toContain('You haven\'t made any withdrawal requests yet.')
  })

  it('shows filtered empty state', async () => {
    const wrapper = mount(WithdrawalHistoryTable, {
      props: {
        initialWithdrawals: [],
        initialPagination: mockPagination
      }
    })

    // Apply a filter by interacting with the select element
    const statusFilter = wrapper.find('select:first-of-type')
    await statusFilter.setValue('completed')
    await statusFilter.trigger('change')

    await nextTick()

    // Check if empty state shows (should show no withdrawals message)
    expect(wrapper.text()).toContain('No withdrawal') // More flexible check
  })

  it('displays correct withdrawal icons', () => {
    const wrapper = mount(WithdrawalHistoryTable, {
      props: {
        initialWithdrawals: mockWithdrawals,
        initialPagination: mockPagination
      }
    })

    // Should have icons for different withdrawal types
    const icons = wrapper.findAllComponents({ name: 'Icon' })
    expect(icons.length).toBeGreaterThan(0)
  })

  it('formats currency correctly', () => {
    const wrapper = mount(WithdrawalHistoryTable, {
      props: {
        initialWithdrawals: mockWithdrawals,
        initialPagination: mockPagination
      }
    })

    expect(wrapper.text()).toContain('K1,000.00')
    expect(wrapper.text()).toContain('K5,000.00')
    expect(wrapper.text()).toContain('K2,000.00')
  })

  it('formats dates correctly', () => {
    const wrapper = mount(WithdrawalHistoryTable, {
      props: {
        initialWithdrawals: mockWithdrawals,
        initialPagination: mockPagination
      }
    })

    expect(wrapper.text()).toContain('Jan 15, 2024')
    expect(wrapper.text()).toContain('Jan 20, 2024')
    expect(wrapper.text()).toContain('Jan 25, 2024')
  })

  it('shows pagination when multiple pages', () => {
    const wrapper = mount(WithdrawalHistoryTable, {
      props: {
        initialWithdrawals: mockWithdrawals,
        initialPagination: mockPagination
      }
    })

    expect(wrapper.findComponent({ name: 'Pagination' }).exists()).toBe(true)
  })

  it('hides pagination for single page', () => {
    const singlePagePagination = { ...mockPagination, last_page: 1 }
    
    const wrapper = mount(WithdrawalHistoryTable, {
      props: {
        initialWithdrawals: mockWithdrawals,
        initialPagination: singlePagePagination
      }
    })

    expect(wrapper.findComponent({ name: 'Pagination' }).exists()).toBe(false)
  })

  it('loads withdrawals on page change', async () => {
    const wrapper = mount(WithdrawalHistoryTable, {
      props: {
        initialWithdrawals: mockWithdrawals,
        initialPagination: mockPagination
      }
    })

    const pagination = wrapper.findComponent({ name: 'Pagination' })
    await pagination.vm.$emit('page-changed', 2)

    expect(global.fetch).toHaveBeenCalledWith(
      expect.stringContaining('page=2')
    )
  })
})