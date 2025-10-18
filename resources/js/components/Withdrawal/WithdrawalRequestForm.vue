<template>
  <div class="space-y-6">
    <!-- Withdrawal Eligibility Checker -->
    <div class="bg-white rounded-lg border border-gray-200 p-6" data-testid="eligibility-checker">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Withdrawal Eligibility</h3>
      
      <div v-if="eligibilityLoading" class="flex items-center justify-center py-8">
        <LoadingSpinner data-testid="loading-spinner" />
      </div>
      
      <div v-else-if="eligibilityData" class="space-y-4">
        <!-- Lock-in Period Display -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div class="flex items-center space-x-2">
            <Icon name="clock" class="h-5 w-5 text-blue-600" />
            <h4 class="font-medium text-blue-900">Lock-in Period Status</h4>
          </div>
          <div class="mt-2 text-sm text-blue-700">
            <p v-if="eligibilityData.lock_in_validation?.is_within_lock_in">
              <strong>{{ eligibilityData.lock_in_validation.days_remaining }} days remaining</strong> 
              in lock-in period (ends {{ formatDate(eligibilityData.lock_in_validation.lock_in_end_date) }})
            </p>
            <p v-else class="text-green-700">
              Lock-in period completed. Full withdrawal available without penalties.
            </p>
          </div>
        </div>

        <!-- Eligibility Status -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="bg-gray-50 rounded-lg p-4">
            <h5 class="font-medium text-gray-900 mb-2">Current Investment Value</h5>
            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(eligibilityData?.current_value || 0) }}</p>
            <p class="text-sm text-gray-600">Profit: {{ formatCurrency(eligibilityData?.profit_amount || 0) }}</p>
          </div>
          
          <div class="bg-gray-50 rounded-lg p-4">
            <h5 class="font-medium text-gray-900 mb-2">Withdrawal Status</h5>
            <div class="flex items-center space-x-2">
              <Icon 
                :name="eligibilityData.is_eligible ? 'check-circle' : 'x-circle'" 
                :class="eligibilityData.is_eligible ? 'text-green-600' : 'text-red-600'"
                class="h-5 w-5"
              />
              <span :class="eligibilityData.is_eligible ? 'text-green-700' : 'text-red-700'" class="font-medium">
                {{ eligibilityData.is_eligible ? 'Eligible' : 'Not Eligible' }}
              </span>
            </div>
            <ul v-if="eligibilityData?.reasons?.length > 0" class="mt-2 text-sm text-red-600">
              <li v-for="reason in eligibilityData.reasons" :key="reason">• {{ reason }}</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Withdrawal Request Form -->
    <form @submit.prevent="submitWithdrawal" class="bg-white rounded-lg border border-gray-200 p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-6">Request Withdrawal</h3>
      
      <div class="space-y-6">
        <!-- Withdrawal Type Selection -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Withdrawal Type</label>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
            <label 
              v-for="type in withdrawalTypes" 
              :key="type.value"
              class="relative flex cursor-pointer rounded-lg border p-4 focus:outline-none"
              :class="form.withdrawal_type === type.value 
                ? 'border-blue-600 bg-blue-50' 
                : 'border-gray-300 bg-white hover:bg-gray-50'"
            >
              <input
                type="radio"
                :value="type.value"
                v-model="form.withdrawal_type"
                class="sr-only"
                @change="onWithdrawalTypeChange"
              />
              <div class="flex flex-col">
                <span class="block text-sm font-medium text-gray-900">{{ type.label }}</span>
                <span class="block text-xs text-gray-500 mt-1">{{ type.description }}</span>
              </div>
            </label>
          </div>
          <InputError :message="form.errors.withdrawal_type" class="mt-2" />
        </div>

        <!-- Amount Input (for partial withdrawals) -->
        <div v-if="form.withdrawal_type === 'partial'">
          <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
            Withdrawal Amount
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <span class="text-gray-500 sm:text-sm">K</span>
            </div>
            <input
              id="amount"
              type="number"
              step="0.01"
              min="1"
              v-model="form.amount"
              @input="calculatePenaltyPreview"
              class="block w-full pl-7 pr-12 border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              placeholder="0.00"
            />
          </div>
          <InputError :message="form.errors.amount" class="mt-2" />
          <p v-if="maxWithdrawable > 0" class="mt-1 text-sm text-gray-600">
            Maximum withdrawable: {{ formatCurrency(maxWithdrawable) }}
          </p>
        </div>

        <!-- Emergency Withdrawal Reason -->
        <div v-if="form.withdrawal_type === 'emergency'">
          <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
            Emergency Reason <span class="text-red-500">*</span>
          </label>
          <textarea
            id="reason"
            v-model="form.reason"
            rows="4"
            class="block w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
            placeholder="Please explain the emergency situation requiring immediate withdrawal..."
          ></textarea>
          <InputError :message="form.errors.reason" class="mt-2" />
          <p class="mt-1 text-sm text-gray-600">Emergency withdrawals require admin approval and incur penalties.</p>
        </div>

        <!-- Penalty Preview -->
        <div v-if="penaltyPreview && (form.withdrawal_type === 'emergency' || eligibilityData?.lock_in_validation?.is_within_lock_in)" 
             class="bg-amber-50 border border-amber-200 rounded-lg p-4" data-testid="penalty-preview">
          <div class="flex items-center space-x-2 mb-3">
            <Icon name="exclamation-triangle" class="h-5 w-5 text-amber-600" />
            <h4 class="font-medium text-amber-900">Penalty Preview</h4>
          </div>
          
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-amber-700">Withdrawal Amount:</span>
              <span class="font-medium">{{ formatCurrency(penaltyPreview?.withdrawal_amount || 0) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-amber-700">Profit Penalty ({{ penaltyPreview?.profit_penalty_rate || 0 }}%):</span>
              <span class="font-medium text-red-600">-{{ formatCurrency(penaltyPreview?.profit_penalty_amount || 0) }}</span>
            </div>
            <div v-if="(penaltyPreview?.capital_penalty_amount || 0) > 0" class="flex justify-between">
              <span class="text-amber-700">Capital Penalty ({{ penaltyPreview?.capital_penalty_rate || 0 }}%):</span>
              <span class="font-medium text-red-600">-{{ formatCurrency(penaltyPreview?.capital_penalty_amount || 0) }}</span>
            </div>
            <div class="border-t border-amber-200 pt-2 flex justify-between font-semibold">
              <span class="text-amber-900">Net Amount:</span>
              <span class="text-green-600">{{ formatCurrency(penaltyPreview?.net_amount || 0) }}</span>
            </div>
          </div>
        </div>

        <!-- Bank Details (not for emergency) -->
        <div v-if="form.withdrawal_type !== 'emergency'" class="space-y-4">
          <h4 class="font-medium text-gray-900">Bank Details</h4>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-2">
                Bank Name <span class="text-red-500">*</span>
              </label>
              <input
                id="bank_name"
                type="text"
                v-model="form.bank_name"
                class="block w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                placeholder="e.g., Standard Bank"
              />
              <InputError :message="form.errors.bank_name" class="mt-2" />
            </div>
            
            <div>
              <label for="account_holder_name" class="block text-sm font-medium text-gray-700 mb-2">
                Account Holder Name <span class="text-red-500">*</span>
              </label>
              <input
                id="account_holder_name"
                type="text"
                v-model="form.account_holder_name"
                class="block w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                placeholder="Full name as on bank account"
              />
              <InputError :message="form.errors.account_holder_name" class="mt-2" />
            </div>
          </div>
          
          <div>
            <label for="bank_account" class="block text-sm font-medium text-gray-700 mb-2">
              Account Number <span class="text-red-500">*</span>
            </label>
            <input
              id="bank_account"
              type="text"
              v-model="form.bank_account"
              class="block w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              placeholder="Bank account number"
            />
            <InputError :message="form.errors.bank_account" class="mt-2" />
          </div>
        </div>

        <!-- OTP Verification -->
        <div>
          <label for="otp_code" class="block text-sm font-medium text-gray-700 mb-2">
            OTP Verification Code <span class="text-red-500">*</span>
          </label>
          <div class="flex space-x-3">
            <input
              id="otp_code"
              type="text"
              v-model="form.otp_code"
              maxlength="6"
              class="block w-32 border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-center text-lg font-mono"
              placeholder="000000"
            />
            <button
              type="button"
              @click="sendOTP"
              :disabled="otpSending || otpCooldown > 0"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed"
            >
              <span v-if="otpSending">Sending...</span>
              <span v-else-if="otpCooldown > 0">Resend in {{ otpCooldown }}s</span>
              <span v-else>{{ otpSent ? 'Resend OTP' : 'Send OTP' }}</span>
            </button>
          </div>
          <InputError :message="form.errors.otp_code" class="mt-2" />
          <p v-if="otpSent" class="mt-1 text-sm text-green-600">OTP sent to your registered phone number</p>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end space-x-3">
          <button
            type="button"
            @click="resetForm"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
          >
            Reset
          </button>
          <button
            type="submit"
            :disabled="form.processing || !canSubmit"
            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed"
          >
            <span v-if="form.processing">Processing...</span>
            <span v-else>Submit Withdrawal Request</span>
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import Icon from '@/components/Icon.vue'
import InputError from '@/components/InputError.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'

interface Props {
  investment: {
    id: number
    amount: number
    current_value: number
    tier: {
      name: string
      minimum_amount: number
    }
  }
}

const props = defineProps<Props>()

// Form data
const form = useForm({
  withdrawal_type: '',
  amount: '',
  reason: '',
  bank_name: '',
  bank_account: '',
  account_holder_name: '',
  otp_code: ''
})

// Reactive data
const eligibilityData = ref(null)
const eligibilityLoading = ref(false)
const penaltyPreview = ref(null)
const otpSent = ref(false)
const otpSending = ref(false)
const otpCooldown = ref(0)

// Withdrawal types
const withdrawalTypes = [
  {
    value: 'full',
    label: 'Full Withdrawal',
    description: 'Withdraw entire investment'
  },
  {
    value: 'partial',
    label: 'Partial Withdrawal',
    description: 'Withdraw specific amount'
  },
  {
    value: 'profits_only',
    label: 'Profits Only',
    description: 'Withdraw profits only'
  },
  {
    value: 'emergency',
    label: 'Emergency',
    description: 'Urgent withdrawal with penalties'
  }
]

// Computed properties
const maxWithdrawable = computed(() => {
  if (!eligibilityData.value) return 0
  
  switch (form.withdrawal_type) {
    case 'partial':
      return eligibilityData.value.current_value * 0.5 // 50% max for partial
    case 'profits_only':
      return Math.max(0, eligibilityData.value.profit_amount)
    default:
      return eligibilityData.value.current_value
  }
})

const canSubmit = computed(() => {
  if (!form.withdrawal_type || !form.otp_code) return false
  
  if (form.withdrawal_type === 'partial' && (!form.amount || parseFloat(form.amount) <= 0)) {
    return false
  }
  
  if (form.withdrawal_type === 'emergency' && !form.reason) {
    return false
  }
  
  if (form.withdrawal_type !== 'emergency') {
    if (!form.bank_name || !form.bank_account || !form.account_holder_name) {
      return false
    }
  }
  
  return true
})

// Methods
const fetchEligibilityData = async () => {
  eligibilityLoading.value = true
  try {
    const response = await fetch(`/dashboard/withdrawal-eligibility?investment_id=${props.investment.id}`)
    eligibilityData.value = await response.json()
  } catch (error) {
    console.error('Failed to fetch eligibility data:', error)
  } finally {
    eligibilityLoading.value = false
  }
}

const onWithdrawalTypeChange = () => {
  form.amount = ''
  penaltyPreview.value = null
  if (form.withdrawal_type) {
    calculatePenaltyPreview()
  }
}

const calculatePenaltyPreview = async () => {
  if (!form.withdrawal_type) return
  
  const amount = form.withdrawal_type === 'partial' ? parseFloat(form.amount) || 0 : props.investment.current_value
  
  try {
    const response = await fetch('/dashboard/penalty-preview', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({
        investment_id: props.investment.id,
        withdrawal_type: form.withdrawal_type,
        amount: amount
      })
    })
    
    penaltyPreview.value = await response.json()
  } catch (error) {
    console.error('Failed to calculate penalty preview:', error)
  }
}

const sendOTP = async () => {
  otpSending.value = true
  try {
    await fetch('/api/send-otp', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    })
    
    otpSent.value = true
    startOTPCooldown()
  } catch (error) {
    console.error('Failed to send OTP:', error)
  } finally {
    otpSending.value = false
  }
}

const startOTPCooldown = () => {
  otpCooldown.value = 60
  const interval = setInterval(() => {
    otpCooldown.value--
    if (otpCooldown.value <= 0) {
      clearInterval(interval)
    }
  }, 1000)
}

const submitWithdrawal = () => {
  form.post(`/investments/${props.investment.id}/withdrawal`, {
    onSuccess: () => {
      resetForm()
    }
  })
}

const resetForm = () => {
  form.reset()
  penaltyPreview.value = null
  otpSent.value = false
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount)
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

// Watch for amount changes to recalculate penalties
watch(() => form.amount, () => {
  if (form.withdrawal_type === 'partial' && form.amount) {
    calculatePenaltyPreview()
  }
})

// Initialize
onMounted(() => {
  fetchEligibilityData()
})
</script>