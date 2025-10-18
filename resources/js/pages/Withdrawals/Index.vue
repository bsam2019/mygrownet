<template>
  <AppShell>
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Withdrawals</h1>
          <p class="text-gray-600">Manage your withdrawal requests and view transaction history</p>
        </div>
        
        <button
          @click="showNewWithdrawal = true"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 flex items-center space-x-2"
        >
          <Icon name="plus" class="h-4 w-4" />
          <span>New Withdrawal</span>
        </button>
      </div>

      <!-- Investment Selection (if multiple investments) -->
      <div v-if="investments.length > 1" class="bg-white rounded-lg border border-gray-200 p-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Select Investment</label>
        <select
          v-model="selectedInvestmentId"
          @change="onInvestmentChange"
          class="block w-full max-w-md border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="">All Investments</option>
          <option v-for="investment in investments" :key="investment.id" :value="investment.id">
            {{ investment.tier.name }} - {{ formatCurrency(investment.amount) }}
            ({{ formatDate(investment.investment_date) }})
          </option>
        </select>
      </div>

      <!-- Withdrawal Eligibility Checker -->
      <WithdrawalEligibilityChecker
        v-if="selectedInvestment"
        :investment="selectedInvestment"
      />

      <!-- Withdrawal History -->
      <WithdrawalHistoryTable
        :initial-withdrawals="withdrawals.data"
        :initial-pagination="withdrawals"
      />

      <!-- New Withdrawal Modal -->
      <div v-if="showNewWithdrawal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
          <!-- Background overlay -->
          <div 
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
            @click="showNewWithdrawal = false"
          ></div>

          <!-- Modal panel -->
          <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <!-- Header -->
            <div class="bg-white px-6 py-4 border-b border-gray-200">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">New Withdrawal Request</h3>
                <button
                  @click="showNewWithdrawal = false"
                  class="text-gray-400 hover:text-gray-600"
                >
                  <Icon name="x" class="h-6 w-6" />
                </button>
              </div>
            </div>

            <!-- Content -->
            <div class="bg-white px-6 py-4 max-h-[80vh] overflow-y-auto">
              <div v-if="!selectedInvestment" class="text-center py-8">
                <Icon name="exclamation-triangle" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900">No Investment Selected</h3>
                <p class="mt-1 text-sm text-gray-500">Please select an investment to create a withdrawal request.</p>
              </div>
              
              <WithdrawalRequestForm
                v-else
                :investment="selectedInvestment"
                @success="onWithdrawalSuccess"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppShell>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppShell from '@/components/AppShell.vue'
import Icon from '@/components/Icon.vue'
import WithdrawalEligibilityChecker from '@/components/Withdrawal/WithdrawalEligibilityChecker.vue'
import WithdrawalHistoryTable from '@/components/Withdrawal/WithdrawalHistoryTable.vue'
import WithdrawalRequestForm from '@/components/Withdrawal/WithdrawalRequestForm.vue'

interface Investment {
  id: number
  amount: number
  current_value: number
  investment_date: string
  tier: {
    name: string
    minimum_amount: number
  }
}

interface Props {
  investments: Investment[]
  withdrawals: {
    data: any[]
    [key: string]: any
  }
}

const props = defineProps<Props>()

// Reactive data
const selectedInvestmentId = ref(props.investments.length === 1 ? props.investments[0].id : '')
const showNewWithdrawal = ref(false)

// Computed properties
const selectedInvestment = computed(() => {
  if (!selectedInvestmentId.value) return null
  return props.investments.find(inv => inv.id === parseInt(selectedInvestmentId.value))
})

// Methods
const onInvestmentChange = () => {
  // Could trigger data refresh here if needed
}

const onWithdrawalSuccess = () => {
  showNewWithdrawal.value = false
  // Refresh data or show success message
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
    month: 'short',
    day: 'numeric'
  })
}

// Initialize
onMounted(() => {
  // Auto-select first investment if only one exists
  if (props.investments.length === 1) {
    selectedInvestmentId.value = props.investments[0].id.toString()
  }
})
</script>