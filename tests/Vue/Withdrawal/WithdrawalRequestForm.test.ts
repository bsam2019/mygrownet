import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { nextTick } from 'vue'
import WithdrawalRequestForm from '@/components/Withdrawal/WithdrawalRequestForm.vue'

// Mock Inertia
vi.mock('@inertiajs/vue3', () => ({
  useForm: vi.fn(() => ({
    withdrawal_type: '',
    amount: '',
    reason: '',
    bank_name: '',
    bank_account: '',
    account_holder_name: '',
    otp_code: '',
    processing: false,
    errors: {},
    post: vi.fn(),
    reset: vi.fn()
  })),
  router: {
    post: vi.fn()
  }
}))

// Mock fetch
global.fetch = vi.fn() as any

const mockInvestment = {
  id: 1,
  amount: 5000,
  current_value: 5500,
  tier: {
    name: 'Builder',
    minimum_amount: 2500
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

describe('WithdrawalRequestForm', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    // Mock successful fetch responses
    ;(global.fetch as any).mockResolvedValue({
      json: () => Promise.resolve(mockEligibilityData)
    })
  })

  it('renders withdrawal eligibility checker', async () => {
    const wrapper = mount(WithdrawalRequestForm, {
      props: { investment: mockInvestment }
    })

    await nextTick()
    
    expect(wrapper.find('[data-testid="eligibility-checker"]').exists()).toBe(true)
    expect(wrapper.text()).toContain('Withdrawal Eligibility')
  })

  it('displays lock-in period information', async () => {
    const wrapper = mount(WithdrawalRequestForm, {
      props: { investment: mockInvestment }
    })

    await nextTick()
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('Lock-in Period Status')
    expect(wrapper.text()).toContain('180 days remaining')
  })

  it('shows withdrawal type options', () => {
    const wrapper = mount(WithdrawalRequestForm, {
      props: { investment: mockInvestment }
    })

    const withdrawalTypes = wrapper.findAll('input[type="radio"]')
    expect(withdrawalTypes).toHaveLength(4)
    
    expect(wrapper.text()).toContain('Full Withdrawal')
    expect(wrapper.text()).toContain('Partial Withdrawal')
    expect(wrapper.text()).toContain('Profits Only')
    expect(wrapper.text()).toContain('Emergency')
  })

  it('shows amount input for partial withdrawal', async () => {
    const wrapper = mount(WithdrawalRequestForm, {
      props: { investment: mockInvestment }
    })

    // Select partial withdrawal
    const partialRadio = wrapper.find('input[value="partial"]')
    await partialRadio.setValue(true)
    await partialRadio.trigger('change')

    expect(wrapper.find('input[type="number"]').exists()).toBe(true)
    expect(wrapper.text()).toContain('Withdrawal Amount')
  })

  it('shows reason textarea for emergency withdrawal', async () => {
    const wrapper = mount(WithdrawalRequestForm, {
      props: { investment: mockInvestment }
    })

    // Select emergency withdrawal
    const emergencyRadio = wrapper.find('input[value="emergency"]')
    await emergencyRadio.setValue(true)
    await emergencyRadio.trigger('change')

    expect(wrapper.find('textarea').exists()).toBe(true)
    expect(wrapper.text()).toContain('Emergency Reason')
  })

  it('shows bank details for non-emergency withdrawals', async () => {
    const wrapper = mount(WithdrawalRequestForm, {
      props: { investment: mockInvestment }
    })

    // Select full withdrawal
    const fullRadio = wrapper.find('input[value="full"]')
    await fullRadio.setValue(true)
    await fullRadio.trigger('change')

    expect(wrapper.text()).toContain('Bank Details')
    expect(wrapper.text()).toContain('Bank Name')
    expect(wrapper.text()).toContain('Account Number')
    expect(wrapper.text()).toContain('Account Holder Name')
  })

  it('hides bank details for emergency withdrawals', async () => {
    const wrapper = mount(WithdrawalRequestForm, {
      props: { investment: mockInvestment }
    })

    // Select emergency withdrawal
    const emergencyRadio = wrapper.find('input[value="emergency"]')
    await emergencyRadio.setValue(true)
    await emergencyRadio.trigger('change')

    expect(wrapper.text()).not.toContain('Bank Details')
  })

  it('shows penalty preview for early withdrawals', async () => {
    const mockPenaltyPreview = {
      withdrawal_amount: 5500,
      profit_penalty_rate: 50,
      profit_penalty_amount: 250,
      capital_penalty_rate: 0,
      capital_penalty_amount: 0,
      net_amount: 5250
    }

    ;(global.fetch as any).mockResolvedValueOnce({
      json: () => Promise.resolve(mockPenaltyPreview)
    })

    const wrapper = mount(WithdrawalRequestForm, {
      props: { investment: mockInvestment }
    })

    // Select emergency withdrawal to trigger penalty preview
    const emergencyRadio = wrapper.find('input[value="emergency"]')
    await emergencyRadio.setValue(true)
    await emergencyRadio.trigger('change')

    await nextTick()
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('Penalty Preview')
    expect(wrapper.text()).toContain('K5,500.00') // withdrawal amount
    expect(wrapper.text()).toContain('K250.00') // penalty amount
    expect(wrapper.text()).toContain('K5,250.00') // net amount
  })

  it('requires OTP verification', () => {
    const wrapper = mount(WithdrawalRequestForm, {
      props: { investment: mockInvestment }
    })

    expect(wrapper.find('input[maxlength="6"]').exists()).toBe(true)
    expect(wrapper.text()).toContain('OTP Verification Code')
    expect(wrapper.find('button').text()).toContain('Send OTP')
  })

  it('validates form before submission', async () => {
    const wrapper = mount(WithdrawalRequestForm, {
      props: { investment: mockInvestment }
    })

    const submitButton = wrapper.find('button[type="submit"]')
    expect(submitButton.attributes('disabled')).toBeDefined()

    // Fill required fields
    const fullRadio = wrapper.find('input[value="full"]')
    await fullRadio.setValue(true)
    await fullRadio.trigger('change')

    const otpInput = wrapper.find('input[maxlength="6"]')
    await otpInput.setValue('123456')

    const bankName = wrapper.find('input[placeholder*="Standard Bank"]')
    await bankName.setValue('Test Bank')

    const accountNumber = wrapper.find('input[placeholder*="Bank account number"]')
    await accountNumber.setValue('1234567890')

    const accountHolder = wrapper.find('input[placeholder*="Full name"]')
    await accountHolder.setValue('John Doe')

    await nextTick()

    // Submit button should be enabled now
    expect(submitButton.attributes('disabled')).toBeFalsy()
  })

  it('calculates maximum withdrawable amount for partial withdrawals', async () => {
    const wrapper = mount(WithdrawalRequestForm, {
      props: { investment: mockInvestment }
    })

    // Wait for eligibility data to load
    await nextTick()
    await wrapper.vm.$nextTick()

    // Select partial withdrawal
    const partialRadio = wrapper.find('input[value="partial"]')
    await partialRadio.setValue(true)
    await partialRadio.trigger('change')

    // Should show maximum withdrawable amount (50% of current value)
    expect(wrapper.text()).toContain('Maximum withdrawable: K2,750.00')
  })

  it('sends OTP when requested', async () => {
    ;(global.fetch as any).mockResolvedValueOnce({
      json: () => Promise.resolve({ success: true })
    })

    const wrapper = mount(WithdrawalRequestForm, {
      props: { investment: mockInvestment }
    })

    const buttons = wrapper.findAll('button')
    const sendOtpButton = buttons.find(btn => btn.text().includes('Send OTP'))
    await sendOtpButton?.trigger('click')

    expect(global.fetch).toHaveBeenCalledWith('/api/send-otp', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': expect.any(String)
      }
    })
  })

  it('shows OTP cooldown after sending', async () => {
    vi.useFakeTimers()
    
    ;(global.fetch as any).mockResolvedValueOnce({
      json: () => Promise.resolve({ success: true })
    })

    const wrapper = mount(WithdrawalRequestForm, {
      props: { investment: mockInvestment }
    })

    const buttons = wrapper.findAll('button')
    const sendOtpButton = buttons.find(btn => btn.text().includes('Send OTP'))
    await sendOtpButton?.trigger('click')

    await nextTick()

    expect(wrapper.text()).toContain('Resend in')
    expect(sendOtpButton?.attributes('disabled')).toBeDefined()

    vi.useRealTimers()
  })

  it('resets form when reset button is clicked', async () => {
    const wrapper = mount(WithdrawalRequestForm, {
      props: { investment: mockInvestment }
    })

    // Fill some form data
    const fullRadio = wrapper.find('input[value="full"]')
    await fullRadio.setValue(true)

    const otpInput = wrapper.find('input[maxlength="6"]')
    await otpInput.setValue('123456')

    // Click reset
    const buttons = wrapper.findAll('button')
    const resetButton = buttons.find(btn => btn.text().includes('Reset'))
    await resetButton?.trigger('click')

    // Form should be reset (check that reset method was called)
    expect(wrapper.vm.form.reset).toHaveBeenCalled()
  })

  it('handles eligibility data loading states', async () => {
    // Mock loading state
    ;(global.fetch as any).mockImplementationOnce(() => new Promise(() => {}))

    const wrapper = mount(WithdrawalRequestForm, {
      props: { investment: mockInvestment }
    })

    // Wait for component to mount and start loading
    await nextTick()
    
    expect(wrapper.text()).toContain('Loading') || expect(wrapper.findComponent({ name: 'LoadingSpinner' }).exists()).toBe(true)
  })

  it('displays ineligibility reasons when not eligible', async () => {
    const ineligibleData = {
      ...mockEligibilityData,
      is_eligible: false,
      reasons: ['Insufficient balance', 'Investment not active']
    }

    ;(global.fetch as any).mockResolvedValueOnce({
      json: () => Promise.resolve(ineligibleData)
    })

    const wrapper = mount(WithdrawalRequestForm, {
      props: { investment: mockInvestment }
    })

    await nextTick()
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('Not Eligible')
    expect(wrapper.text()).toContain('Insufficient balance')
    expect(wrapper.text()).toContain('Investment not active')
  })
})