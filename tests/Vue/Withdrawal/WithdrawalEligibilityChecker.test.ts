import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { nextTick } from 'vue'

// Mock the components
const WithdrawalEligibilityChecker = {
  name: 'WithdrawalEligibilityChecker',
  template: `
    <div class="bg-white rounded-lg border border-gray-200 p-6">
      <h3 class="text-lg font-semibold text-gray-900">Withdrawal Eligibility</h3>
      <button @click="refreshEligibility">Refresh</button>
      <div v-if="loading">Loading...</div>
      <div v-else-if="eligibilityData">
        <div v-if="eligibilityData.is_eligible">Withdrawal Eligible</div>
        <div v-else>Withdrawal Restricted</div>
        <div v-if="eligibilityData.lock_in_validation.is_within_lock_in">
          Lock-in Period Status
          <div>{{ eligibilityData.lock_in_validation.days_remaining }} days remaining</div>
        </div>
      </div>
    </div>
  `,
  props: ['investment'],
  data() {
    return {
      loading: false,
      eligibilityData: null
    }
  },
  methods: {
    async refreshEligibility() {
      this.loading = true
      try {
        const response = await fetch(`/dashboard/withdrawal-eligibility?investment_id=${this.investment.id}`)
        this.eligibilityData = await response.json()
      } finally {
        this.loading = false
      }
    }
  },
  async mounted() {
    await this.refreshEligibility()
  }
}

// Mock fetch
global.fetch = vi.fn() as any

const mockInvestment = {
  id: 1,
  amount: 5000,
  tier: {
    name: 'Builder'
  }
}

const mockEligibilityData = {
  is_eligible: true,
  current_value: 5500,
  profit_amount: 500,
  lock_in_validation: {
    is_within_lock_in: true,
    days_remaining: 180,
    lock_in_end_date: '2024-12-31',
    investment_date: '2024-01-01',
    total_lock_in_days: 365
  },
  eligibility_checks: {
    investment_active: true,
    sufficient_balance: true,
    has_profits: true
  },
  reasons: []
}

describe('WithdrawalEligibilityChecker', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    ;(global.fetch as any).mockResolvedValue({
      json: () => Promise.resolve(mockEligibilityData)
    })
  })

  it('renders eligibility checker component', () => {
    const wrapper = mount(WithdrawalEligibilityChecker, {
      props: { investment: mockInvestment }
    })

    expect(wrapper.find('h3').text()).toContain('Withdrawal Eligibility')
    expect(wrapper.find('button').exists()).toBe(true)
  })
  })

  it('displays loading state initially', () => {
    ;(global.fetch as any).mockImplementationOnce(() => new Promise(() => {}))
    
    const wrapper = mount(WithdrawalEligibilityChecker, {
      props: { investment: mockInvestment }
    })

    expect(wrapper.findComponent({ name: 'LoadingSpinner' }).exists()).toBe(true)
  })

  it('shows eligible status when user can withdraw', async () => {
    const wrapper = mount(WithdrawalEligibilityChecker, {
      props: { investment: mockInvestment }
    })

    await nextTick()
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('Withdrawal Eligible')
    expect(wrapper.text()).toContain('You can proceed with withdrawal requests')
    expect(wrapper.find('.text-green-600').exists()).toBe(true)
  })

  it('shows ineligible status when user cannot withdraw', async () => {
    const ineligibleData = {
      ...mockEligibilityData,
      is_eligible: false,
      reasons: ['Insufficient balance', 'Investment not active']
    }

    ;(global.fetch as any).mockResolvedValueOnce({
      json: () => Promise.resolve(ineligibleData)
    })

    const wrapper = mount(WithdrawalEligibilityChecker, {
      props: { investment: mockInvestment }
    })

    await nextTick()
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('Withdrawal Restricted')
    expect(wrapper.text()).toContain('Some restrictions apply')
    expect(wrapper.find('.text-red-600').exists()).toBe(true)
  })

  it('displays lock-in period progress', async () => {
    const wrapper = mount(WithdrawalEligibilityChecker, {
      props: { investment: mockInvestment }
    })

    await nextTick()
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('Lock-in Period Status')
    expect(wrapper.text()).toContain('180 days remaining')
    expect(wrapper.text()).toContain('50.7% complete')
    expect(wrapper.find('.bg-blue-600').exists()).toBe(true) // Progress bar
  })

  it('shows completed lock-in period', async () => {
    const completedLockInData = {
      ...mockEligibilityData,
      lock_in_validation: {
        ...mockEligibilityData.lock_in_validation,
        is_within_lock_in: false
      }
    }

    ;(global.fetch as any).mockResolvedValueOnce({
      json: () => Promise.resolve(completedLockInData)
    })

    const wrapper = mount(WithdrawalEligibilityChecker, {
      props: { investment: mockInvestment }
    })

    await nextTick()
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('Lock-in period completed')
    expect(wrapper.text()).toContain('Full withdrawal available without early withdrawal penalties')
  })

  it('displays investment summary correctly', async () => {
    const wrapper = mount(WithdrawalEligibilityChecker, {
      props: { investment: mockInvestment }
    })

    await nextTick()
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('K5,000.00') // Original investment
    expect(wrapper.text()).toContain('K5,500.00') // Current value
    expect(wrapper.text()).toContain('+K500.00 profit')
    expect(wrapper.text()).toContain('+10.00%') // Growth rate
    expect(wrapper.text()).toContain('Builder Tier')
  })

  it('shows negative profit correctly', async () => {
    const lossData = {
      ...mockEligibilityData,
      current_value: 4500,
      profit_amount: -500
    }

    ;(global.fetch as any).mockResolvedValueOnce({
      json: () => Promise.resolve(lossData)
    })

    const wrapper = mount(WithdrawalEligibilityChecker, {
      props: { investment: mockInvestment }
    })

    await nextTick()
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('K4,500.00') // Current value
    expect(wrapper.text()).toContain('-K500.00 profit')
    expect(wrapper.text()).toContain('-10.00%') // Negative growth rate
    expect(wrapper.find('.text-red-600').exists()).toBe(true)
  })

  it('displays withdrawal options with availability', async () => {
    const wrapper = mount(WithdrawalEligibilityChecker, {
      props: { investment: mockInvestment }
    })

    await nextTick()
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('Available Withdrawal Options')
    expect(wrapper.text()).toContain('Full Withdrawal')
    expect(wrapper.text()).toContain('Partial Withdrawal')
    expect(wrapper.text()).toContain('Profits Only')
    expect(wrapper.text()).toContain('Emergency Withdrawal')
  })

  it('shows correct withdrawal amounts', async () => {
    const wrapper = mount(WithdrawalEligibilityChecker, {
      props: { investment: mockInvestment }
    })

    await nextTick()
    await wrapper.vm.$nextTick()

    // Full withdrawal should show current value
    expect(wrapper.text()).toContain('K5,500.00')
    
    // Partial withdrawal should show 50% of profits
    expect(wrapper.text()).toContain('Up to K250.00')
    
    // Profits only should show full profit amount
    expect(wrapper.text()).toContain('K500.00')
  })

  it('indicates unavailable withdrawal options', async () => {
    const restrictedData = {
      ...mockEligibilityData,
      eligibility_checks: {
        investment_active: false,
        sufficient_balance: false,
        has_profits: false
      }
    }

    ;(global.fetch as any).mockResolvedValueOnce({
      json: () => Promise.resolve(restrictedData)
    })

    const wrapper = mount(WithdrawalEligibilityChecker, {
      props: { investment: mockInvestment }
    })

    await nextTick()
    await wrapper.vm.$nextTick()

    // Should show x-circle icons for unavailable options
    expect(wrapper.findAll('.text-gray-400').length).toBeGreaterThan(0)
  })

  it('shows penalty information during lock-in period', async () => {
    const wrapper = mount(WithdrawalEligibilityChecker, {
      props: { investment: mockInvestment }
    })

    await nextTick()
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('Early Withdrawal Penalties')
    expect(wrapper.text()).toContain('Profit penalty: Varies based on time remaining')
    expect(wrapper.text()).toContain('Capital penalty: May apply for very early withdrawals')
    expect(wrapper.text()).toContain('Commission clawback: Referral commissions may be reversed')
  })

  it('hides penalty information after lock-in period', async () => {
    const completedLockInData = {
      ...mockEligibilityData,
      lock_in_validation: {
        ...mockEligibilityData.lock_in_validation,
        is_within_lock_in: false
      }
    }

    ;(global.fetch as any).mockResolvedValueOnce({
      json: () => Promise.resolve(completedLockInData)
    })

    const wrapper = mount(WithdrawalEligibilityChecker, {
      props: { investment: mockInvestment }
    })

    await nextTick()
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).not.toContain('Early Withdrawal Penalties')
  })

  it('displays withdrawal restrictions', async () => {
    const restrictedData = {
      ...mockEligibilityData,
      is_eligible: false,
      reasons: ['Account suspended', 'Pending verification', 'Minimum balance not met']
    }

    ;(global.fetch as any).mockResolvedValueOnce({
      json: () => Promise.resolve(restrictedData)
    })

    const wrapper = mount(WithdrawalEligibilityChecker, {
      props: { investment: mockInvestment }
    })

    await nextTick()
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('Withdrawal Restrictions')
    expect(wrapper.text()).toContain('Account suspended')
    expect(wrapper.text()).toContain('Pending verification')
    expect(wrapper.text()).toContain('Minimum balance not met')
  })

  it('refreshes eligibility data when refresh button is clicked', async () => {
    const wrapper = mount(WithdrawalEligibilityChecker, {
      props: { investment: mockInvestment }
    })

    await nextTick()
    
    // Clear previous calls
    ;(global.fetch as any).mockClear()

    const refreshButton = wrapper.find('button')
    await refreshButton.trigger('click')

    expect(global.fetch).toHaveBeenCalledWith(
      `/dashboard/withdrawal-eligibility?investment_id=${mockInvestment.id}`
    )
  })

  it('shows error state when data fails to load', async () => {
    ;(global.fetch as any).mockRejectedValueOnce(new Error('Network error'))

    const wrapper = mount(WithdrawalEligibilityChecker, {
      props: { investment: mockInvestment }
    })

    await nextTick()
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('Unable to load eligibility data')
    expect(wrapper.text()).toContain('Please try refreshing or contact support')
  })

  it('calculates lock-in progress correctly', async () => {
    const wrapper = mount(WithdrawalEligibilityChecker, {
      props: { investment: mockInvestment }
    })

    await nextTick()
    await wrapper.vm.$nextTick()

    // 180 days remaining out of 365 total = (365-180)/365 = 50.7%
    const progressBar = wrapper.find('.bg-blue-600')
    expect(progressBar.attributes('style')).toContain('width: 50.7%')
  })

  it('handles zero profit scenarios', async () => {
    const zeroProfitData = {
      ...mockEligibilityData,
      current_value: 5000,
      profit_amount: 0
    }

    ;(global.fetch as any).mockResolvedValueOnce({
      json: () => Promise.resolve(zeroProfitData)
    })

    const wrapper = mount(WithdrawalEligibilityChecker, {
      props: { investment: mockInvestment }
    })

    await nextTick()
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('K0.00 profit')
    expect(wrapper.text()).toContain('0.00%')
    expect(wrapper.text()).toContain('Up to K0.00') // Partial withdrawal
  })

  it('formats currency amounts correctly', async () => {
    const wrapper = mount(WithdrawalEligibilityChecker, {
      props: { investment: mockInvestment }
    })

    await nextTick()
    await wrapper.vm.$nextTick()

    // Check currency formatting
    expect(wrapper.text()).toMatch(/K\d{1,3}(,\d{3})*\.\d{2}/)
  })

  it('formats dates correctly', async () => {
    const wrapper = mount(WithdrawalEligibilityChecker, {
      props: { investment: mockInvestment }
    })

    await nextTick()
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('December 31, 2024') // Lock-in end date
    expect(wrapper.text()).toContain('January 1, 2024') // Investment date
  })
})