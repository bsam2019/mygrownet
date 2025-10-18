<template>
  <div class="bg-white rounded-lg border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900">Withdrawal History</h3>
        
        <!-- Filters -->
        <div class="flex items-center space-x-3">
          <select
            v-model="filters.status"
            @change="applyFilters"
            class="border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="processing">Processing</option>
            <option value="completed">Completed</option>
            <option value="rejected">Rejected</option>
            <option value="cancelled">Cancelled</option>
          </select>
          
          <select
            v-model="filters.type"
            @change="applyFilters"
            class="border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">All Types</option>
            <option value="full">Full Withdrawal</option>
            <option value="partial">Partial Withdrawal</option>
            <option value="profits_only">Profits Only</option>
            <option value="emergency">Emergency</option>
          </select>
          
          <input
            type="date"
            v-model="filters.date_from"
            @change="applyFilters"
            class="border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"
            placeholder="From Date"
          />
          
          <input
            type="date"
            v-model="filters.date_to"
            @change="applyFilters"
            class="border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"
            placeholder="To Date"
          />
        </div>
      </div>
    </div>

    <div v-if="loading" class="flex items-center justify-center py-12">
      <LoadingSpinner />
    </div>

    <div v-else-if="withdrawals?.length === 0" class="text-center py-12">
      <Icon name="document-text" class="mx-auto h-12 w-12 text-gray-400" />
      <h3 class="mt-2 text-sm font-medium text-gray-900">No withdrawal requests</h3>
      <p class="mt-1 text-sm text-gray-500">
        {{ hasFilters ? 'No withdrawals match your current filters.' : 'You haven\'t made any withdrawal requests yet.' }}
      </p>
      <button
        v-if="hasFilters"
        @click="clearFilters"
        class="mt-3 text-sm text-blue-600 hover:text-blue-500"
      >
        Clear filters
      </button>
    </div>

    <div v-else class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Request Details
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Amount & Type
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Status
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Timeline
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="withdrawal in withdrawals" :key="withdrawal.id" class="hover:bg-gray-50">
            <!-- Request Details -->
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-10 w-10">
                  <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <Icon 
                      :name="getWithdrawalIcon(withdrawal.type)" 
                      class="h-5 w-5 text-blue-600" 
                    />
                  </div>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900">
                    #{{ withdrawal.reference_number || withdrawal.id }}
                  </div>
                  <div class="text-sm text-gray-500">
                    {{ withdrawal.investment?.tier?.name || 'N/A' }} Investment
                  </div>
                </div>
              </div>
            </td>

            <!-- Amount & Type -->
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900">
                <div class="font-medium">{{ formatCurrency(withdrawal.amount) }}</div>
                <div class="text-gray-500 capitalize">{{ withdrawal.type.replace('_', ' ') }}</div>
                <div v-if="withdrawal.penalty_amount > 0" class="text-red-600 text-xs">
                  Penalty: {{ formatCurrency(withdrawal.penalty_amount) }}
                </div>
                <div v-if="withdrawal.net_amount !== withdrawal.amount" class="text-green-600 text-xs font-medium">
                  Net: {{ formatCurrency(withdrawal.net_amount) }}
                </div>
              </div>
            </td>

            <!-- Status -->
            <td class="px-6 py-4 whitespace-nowrap">
              <StatusBadge :status="withdrawal.status" :type="'withdrawal'" />
              <div v-if="withdrawal.rejection_reason" class="mt-1 text-xs text-red-600">
                {{ withdrawal.rejection_reason }}
              </div>
            </td>

            <!-- Timeline -->
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              <div class="space-y-1">
                <div class="flex items-center space-x-2">
                  <Icon name="calendar" class="h-3 w-3" />
                  <span>Requested: {{ formatDate(withdrawal.requested_at) }}</span>
                </div>
                <div v-if="withdrawal.approved_at" class="flex items-center space-x-2">
                  <Icon name="check" class="h-3 w-3 text-green-500" />
                  <span>Approved: {{ formatDate(withdrawal.approved_at) }}</span>
                </div>
                <div v-if="withdrawal.processed_at" class="flex items-center space-x-2">
                  <Icon name="credit-card" class="h-3 w-3 text-blue-500" />
                  <span>Processed: {{ formatDate(withdrawal.processed_at) }}</span>
                </div>
              </div>
            </td>

            <!-- Actions -->
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <div class="flex items-center space-x-2">
                <button
                  @click="viewDetails(withdrawal)"
                  class="text-blue-600 hover:text-blue-900"
                >
                  View
                </button>
                
                <button
                  v-if="canCancel(withdrawal)"
                  @click="cancelWithdrawal(withdrawal)"
                  class="text-red-600 hover:text-red-900"
                >
                  Cancel
                </button>
                
                <button
                  v-if="withdrawal.status === 'completed' && withdrawal.transaction_id"
                  @click="downloadReceipt(withdrawal)"
                  class="text-green-600 hover:text-green-900"
                >
                  Receipt
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="pagination && pagination.last_page > 1" class="px-6 py-4 border-t border-gray-200">
      <Pagination :links="pagination.links || []" @page-changed="loadWithdrawals" />
    </div>

    <!-- Withdrawal Details Modal -->
    <WithdrawalDetailsModal
      v-if="selectedWithdrawal"
      :withdrawal="selectedWithdrawal"
      @close="selectedWithdrawal = null"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import Icon from '@/components/Icon.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import Pagination from '@/components/Pagination.vue'
import WithdrawalDetailsModal from './WithdrawalDetailsModal.vue'

interface Withdrawal {
  id: number
  reference_number?: string
  amount: number
  penalty_amount: number
  net_amount: number
  type: string
  status: string
  rejection_reason?: string
  transaction_id?: string
  requested_at: string
  approved_at?: string
  processed_at?: string
  investment?: {
    tier?: {
      name: string
    }
  }
}

interface Props {
  initialWithdrawals?: Withdrawal[]
  initialPagination?: any
}

const props = defineProps<Props>()

// Reactive data
const withdrawals = ref<Withdrawal[]>(props.initialWithdrawals || [])
const pagination = ref(props.initialPagination || null)
const loading = ref(false)
const selectedWithdrawal = ref<Withdrawal | null>(null)

const filters = reactive({
  status: '',
  type: '',
  date_from: '',
  date_to: ''
})

// Computed properties
const hasFilters = computed(() => {
  return filters.status || filters.type || filters.date_from || filters.date_to
})

// Methods
const loadWithdrawals = async (page = 1) => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: page.toString(),
      ...filters
    })
    
    const response = await fetch(`/api/withdrawals?${params}`)
    const data = await response.json()
    
    withdrawals.value = data.data
    pagination.value = data.pagination
  } catch (error) {
    console.error('Failed to load withdrawals:', error)
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  loadWithdrawals(1)
}

const clearFilters = () => {
  Object.keys(filters).forEach(key => {
    filters[key] = ''
  })
  loadWithdrawals(1)
}

const getWithdrawalIcon = (type: string) => {
  switch (type) {
    case 'full':
      return 'arrow-up-circle'
    case 'partial':
      return 'arrow-up'
    case 'profits_only':
      return 'trending-up'
    case 'emergency':
      return 'exclamation-triangle'
    default:
      return 'arrow-up'
  }
}

const canCancel = (withdrawal: Withdrawal) => {
  return ['pending', 'approved'].includes(withdrawal.status)
}

const viewDetails = (withdrawal: Withdrawal) => {
  selectedWithdrawal.value = withdrawal
}

const cancelWithdrawal = async (withdrawal: Withdrawal) => {
  if (!confirm('Are you sure you want to cancel this withdrawal request?')) {
    return
  }
  
  try {
    await fetch(`/api/withdrawals/${withdrawal.id}/cancel`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    })
    
    // Reload withdrawals
    loadWithdrawals()
  } catch (error) {
    console.error('Failed to cancel withdrawal:', error)
    alert('Failed to cancel withdrawal. Please try again.')
  }
}

const downloadReceipt = (withdrawal: Withdrawal) => {
  window.open(`/api/withdrawals/${withdrawal.id}/receipt`, '_blank')
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
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Initialize
onMounted(() => {
  if (!props.initialWithdrawals) {
    loadWithdrawals()
  }
})
</script>