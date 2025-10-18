<template>
  <div class="bg-white rounded-lg border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-lg font-semibold text-gray-900">Withdrawal Eligibility</h3>
      <button
        @click="refreshEligibility"
        :disabled="loading"
        class="text-blue-600 hover:text-blue-700 disabled:text-gray-400"
      >
        <Icon name="refresh" :class="loading ? 'animate-spin' : ''" class="h-5 w-5" />
      </button>
    </div>

    <div v-if="loading" class="flex items-center justify-center py-8">
      <LoadingSpinner />
    </div>

    <div v-else-if="eligibilityData" class="space-y-6">
      <!-- Overall Status -->
      <div class="flex items-center justify-between p-4 rounded-lg"
           :class="eligibilityData.is_eligible 
             ? 'bg-green-50 border border-green-200' 
             : 'bg-red-50 border border-red-200'">
        <div class="flex items-center space-x-3">
          <Icon 
            :name="eligibilityData.is_eligible ? 'check-circle' : 'x-circle'"
            :class="eligibilityData.is_eligible ? 'text-green-600' : 'text-red-600'"
            class="h-6 w-6"
          />
          <div>
            <h4 class="font-medium"
                :class="eligibilityData.is_eligible ? 'text-green-900' : 'text-red-900'">
              {{ eligibilityData.is_eligible ? 'Withdrawal Eligible' : 'Withdrawal Restricted' }}
            </h4>
            <p class="text-sm"
               :class="eligibilityData.is_eligible ? 'text-green-700' : 'text-red-700'">
              {{ eligibilityData.is_eligible 
                ? 'You can proceed with withdrawal requests' 
                : 'Some restrictions apply to your withdrawals' }}
            </p>
          </div>
        </div>
      </div>

      <!-- Lock-in Period Information -->
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-center space-x-2 mb-3">
          <Icon name="clock" class="h-5 w-5 text-blue-600" />
          <h4 class="font-medium text-blue-900">Lock-in Period Status</h4>
        </div>
        
        <div v-if="eligibilityData.lock_in_validation.is_within_lock_in" class="space-y-3">
          <!-- Progress Bar -->
          <div class="w-full bg-blue-200 rounded-full h-2">
            <div 
              class="bg-blue-600 h-2 rounded-full transition-all duration-300"
              :style="{ width: `${lockInProgress}%` }"
            ></div>
          </div>
          
          <div class="flex justify-between text-sm text-blue-700">
            <span>{{ eligibilityData.lock_in_validation.days_remaining }} days remaining</span>
            <span>{{ lockInProgress.toFixed(1) }}% complete</span>
          </div>
          
          <div class="text-sm text-blue-700">
            <p><strong>Lock-in ends:</strong> {{ formatDate(eligibilityData.lock_in_validation.lock_in_end_date) }}</p>
            <p><strong>Investment date:</strong> {{ formatDate(eligibilityData.lock_in_validation.investment_date) }}</p>
          </div>
        </div>
        
        <div v-else class="text-sm text-green-700">
          <div class="flex items-center space-x-2">
            <Icon name="check-circle" class="h-4 w-4" />
            <span class="font-medium">Lock-in period completed</span>
          </div>
          <p class="mt-1">Full withdrawal available without early withdrawal penalties.</p>
        </div>
      </div>

      <!-- Investment Summary -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gray-50 rounded-lg p-4">
          <h5 class="text-sm font-medium text-gray-700 mb-1">Original Investment</h5>
          <p class="text-xl font-bold text-gray-900">{{ formatCurrency(investment.amount) }}</p>
          <p class="text-xs text-gray-600">{{ investment.tier.name }} Tier</p>
        </div>
        
        <div class="bg-gray-50 rounded-lg p-4">
          <h5 class="text-sm font-medium text-gray-700 mb-1">Current Value</h5>
          <p class="text-xl font-bold text-gray-900">{{ formatCurrency(eligibilityData.current_value) }}</p>
          <p class="text-xs" :class="profitAmount >= 0 ? 'text-green-600' : 'text-red-600'">
            {{ profitAmount >= 0 ? '+' : '' }}{{ formatCurrency(profitAmount) }} profit
          </p>
        </div>
        
        <div class="bg-gray-50 rounded-lg p-4">
          <h5 class="text-sm font-medium text-gray-700 mb-1">Growth Rate</h5>
          <p class="text-xl font-bold" :class="growthRate >= 0 ? 'text-green-600' : 'text-red-600'">
            {{ growthRate >= 0 ? '+' : '' }}{{ growthRate.toFixed(2) }}%
          </p>
          <p class="text-xs text-gray-600">Since investment</p>
        </div>
      </div>

      <!-- Withdrawal Options -->
      <div class="space-y-4">
        <h4 class="font-medium text-gray-900">Available Withdrawal Options</h4>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Full Withdrawal -->
          <div class="border rounded-lg p-4"
               :class="canWithdraw('full') ? 'border-green-200 bg-green-50' : 'border-gray-200 bg-gray-50'">
            <div class="flex items-center justify-between mb-2">
              <h5 class="font-medium text-gray-900">Full Withdrawal</h5>
              <Icon 
                :name="canWithdraw('full') ? 'check-circle' : 'x-circle'"
                :class="canWithdraw('full') ? 'text-green-600' : 'text-gray-400'"
                class="h-5 w-5"
              />
            </div>
            <p class="text-sm text-gray-600 mb-2">Withdraw entire investment amount</p>
            <p class="text-lg font-bold text-gray-900">{{ formatCurrency(eligibilityData.current_value) }}</p>
            <p v-if="!canWithdraw('full')" class="text-xs text-red-600 mt-1">
              {{ getWithdrawalRestriction('full') }}
            </p>
          </div>

          <!-- Partial Withdrawal -->
          <div class="border rounded-lg p-4"
               :class="canWithdraw('partial') ? 'border-green-200 bg-green-50' : 'border-gray-200 bg-gray-50'">
            <div class="flex items-center justify-between mb-2">
              <h5 class="font-medium text-gray-900">Partial Withdrawal</h5>
              <Icon 
                :name="canWithdraw('partial') ? 'check-circle' : 'x-circle'"
                :class="canWithdraw('partial') ? 'text-green-600' : 'text-gray-400'"
                class="h-5 w-5"
              />
            </div>
            <p class="text-sm text-gray-600 mb-2">Withdraw up to 50% of profits</p>
            <p class="text-lg font-bold text-gray-900">
              Up to {{ formatCurrency(Math.max(0, profitAmount * 0.5)) }}
            </p>
            <p v-if="!canWithdraw('partial')" class="text-xs text-red-600 mt-1">
              {{ getWithdrawalRestriction('partial') }}
            </p>
          </div>

          <!-- Profits Only -->
          <div class="border rounded-lg p-4"
               :class="canWithdraw('profits_only') ? 'border-green-200 bg-green-50' : 'border-gray-200 bg-gray-50'">
            <div class="flex items-center justify-between mb-2">
              <h5 class="font-medium text-gray-900">Profits Only</h5>
              <Icon 
                :name="canWithdraw('profits_only') ? 'check-circle' : 'x-circle'"
                :class="canWithdraw('profits_only') ? 'text-green-600' : 'text-gray-400'"
                class="h-5 w-5"
              />
            </div>
            <p class="text-sm text-gray-600 mb-2">Withdraw earned profits only</p>
            <p class="text-lg font-bold text-gray-900">{{ formatCurrency(Math.max(0, profitAmount)) }}</p>
            <p v-if="!canWithdraw('profits_only')" class="text-xs text-red-600 mt-1">
              {{ getWithdrawalRestriction('profits_only') }}
            </p>
          </div>

          <!-- Emergency Withdrawal -->
          <div class="border border-amber-200 bg-amber-50 rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
              <h5 class="font-medium text-gray-900">Emergency Withdrawal</h5>
              <Icon name="exclamation-triangle" class="h-5 w-5 text-amber-600" />
            </div>
            <p class="text-sm text-gray-600 mb-2">Immediate withdrawal with penalties</p>
            <p class="text-lg font-bold text-gray-900">Available with approval</p>
            <p class="text-xs text-amber-700 mt-1">Requires admin approval and incurs penalties</p>
          </div>
        </div>
      </div>

      <!-- Penalty Information -->
      <div v-if="eligibilityData.lock_in_validation.is_within_lock_in" 
           class="bg-amber-50 border border-amber-200 rounded-lg p-4">
        <div class="flex items-center space-x-2 mb-3">
          <Icon name="exclamation-triangle" class="h-5 w-5 text-amber-600" />
          <h4 class="font-medium text-amber-900">Early Withdrawal Penalties</h4>
        </div>
        
        <div class="text-sm text-amber-800 space-y-2">
          <p>Since you're still within the lock-in period, early withdrawals will incur penalties:</p>
          <ul class="list-disc list-inside space-y-1 ml-4">
            <li>Profit penalty: Varies based on time remaining</li>
            <li>Capital penalty: May apply for very early withdrawals</li>
            <li>Commission clawback: Referral commissions may be reversed</li>
          </ul>
          <p class="font-medium">
            Emergency withdrawals are available but require admin approval and full penalties apply.
          </p>
        </div>
      </div>

      <!-- Ineligibility Reasons -->
      <div v-if="eligibilityData.reasons.length > 0" 
           class="bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex items-center space-x-2 mb-3">
          <Icon name="x-circle" class="h-5 w-5 text-red-600" />
          <h4 class="font-medium text-red-900">Withdrawal Restrictions</h4>
        </div>
        
        <ul class="text-sm text-red-800 space-y-1">
          <li v-for="reason in eligibilityData.reasons" :key="reason" class="flex items-start space-x-2">
            <span class="text-red-600 mt-0.5">•</span>
            <span>{{ reason }}</span>
          </li>
        </ul>
      </div>
    </div>

    <div v-else class="text-center py-8">
      <Icon name="exclamation-triangle" class="mx-auto h-12 w-12 text-gray-400" />
      <h3 class="mt-2 text-sm font-medium text-gray-900">Unable to load eligibility data</h3>
      <p class="mt-1 text-sm text-gray-500">Please try refreshing or contact support if the problem persists.</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import Icon from '@/components/Icon.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'

interface Props {
  investment: {
    id: number
    amount: number
    tier: {
      name: string
    }
  }
}

const props = defineProps<Props>()

// Reactive data
const eligibilityData = ref(null)
const loading = ref(false)

// Computed properties
const profitAmount = computed(() => {
  if (!eligibilityData.value) return 0
  return eligibilityData.value.current_value - props.investment.amount
})

const growthRate = computed(() => {
  if (!eligibilityData.value || props.investment.amount === 0) return 0
  return ((eligibilityData.value.current_value - props.investment.amount) / props.investment.amount) * 100
})

const lockInProgress = computed(() => {
  if (!eligibilityData.value?.lock_in_validation?.is_within_lock_in) return 100
  
  const totalDays = eligibilityData.value.lock_in_validation.total_lock_in_days || 365
  const remainingDays = eligibilityData.value.lock_in_validation.days_remaining || 0
  const completedDays = totalDays - remainingDays
  
  return Math.max(0, Math.min(100, (completedDays / totalDays) * 100))
})

// Methods
const refreshEligibility = async () => {
  loading.value = true
  try {
    const response = await fetch(`/dashboard/withdrawal-eligibility?investment_id=${props.investment.id}`)
    eligibilityData.value = await response.json()
  } catch (error) {
    console.error('Failed to fetch eligibility data:', error)
  } finally {
    loading.value = false
  }
}

const canWithdraw = (type: string) => {
  if (!eligibilityData.value) return false
  
  const checks = eligibilityData.value.eligibility_checks
  
  switch (type) {
    case 'full':
      return checks.investment_active && checks.sufficient_balance
    case 'partial':
      return checks.investment_active && checks.sufficient_balance && checks.has_profits
    case 'profits_only':
      return checks.investment_active && checks.has_profits
    default:
      return false
  }
}

const getWithdrawalRestriction = (type: string) => {
  if (!eligibilityData.value) return 'Unable to determine restrictions'
  
  const reasons = eligibilityData.value.reasons
  if (reasons.length === 0) return 'No restrictions'
  
  // Return the most relevant reason for this withdrawal type
  return reasons[0]
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

// Initialize
onMounted(() => {
  refreshEligibility()
})
</script>