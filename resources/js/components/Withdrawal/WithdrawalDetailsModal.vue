<template>
  <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div 
        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
        aria-hidden="true"
        @click="$emit('close')"
      ></div>

      <!-- Modal panel -->
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
        <!-- Header -->
        <div class="bg-white px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900" id="modal-title">
              Withdrawal Request Details
            </h3>
            <button
              @click="$emit('close')"
              class="text-gray-400 hover:text-gray-600"
            >
              <Icon name="x" class="h-6 w-6" />
            </button>
          </div>
        </div>

        <!-- Content -->
        <div class="bg-white px-6 py-4 space-y-6">
          <!-- Basic Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h4 class="text-sm font-medium text-gray-900 mb-3">Request Information</h4>
              <dl class="space-y-2">
                <div>
                  <dt class="text-xs text-gray-500">Reference Number</dt>
                  <dd class="text-sm font-medium text-gray-900">
                    #{{ withdrawal.reference_number || withdrawal.id }}
                  </dd>
                </div>
                <div>
                  <dt class="text-xs text-gray-500">Type</dt>
                  <dd class="text-sm text-gray-900 capitalize">
                    {{ withdrawal.type.replace('_', ' ') }}
                  </dd>
                </div>
                <div>
                  <dt class="text-xs text-gray-500">Status</dt>
                  <dd>
                    <StatusBadge :status="withdrawal.status" :type="'withdrawal'" />
                  </dd>
                </div>
              </dl>
            </div>

            <div>
              <h4 class="text-sm font-medium text-gray-900 mb-3">Financial Details</h4>
              <dl class="space-y-2">
                <div>
                  <dt class="text-xs text-gray-500">Requested Amount</dt>
                  <dd class="text-sm font-medium text-gray-900">
                    {{ formatCurrency(withdrawal.amount) }}
                  </dd>
                </div>
                <div v-if="withdrawal.penalty_amount > 0">
                  <dt class="text-xs text-gray-500">Penalty Amount</dt>
                  <dd class="text-sm font-medium text-red-600">
                    -{{ formatCurrency(withdrawal.penalty_amount) }}
                  </dd>
                </div>
                <div v-if="withdrawal.fee > 0">
                  <dt class="text-xs text-gray-500">Processing Fee</dt>
                  <dd class="text-sm font-medium text-gray-600">
                    -{{ formatCurrency(withdrawal.fee) }}
                  </dd>
                </div>
                <div class="border-t border-gray-200 pt-2">
                  <dt class="text-xs text-gray-500">Net Amount</dt>
                  <dd class="text-sm font-bold text-green-600">
                    {{ formatCurrency(withdrawal.net_amount) }}
                  </dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Timeline -->
          <div>
            <h4 class="text-sm font-medium text-gray-900 mb-3">Timeline</h4>
            <div class="flow-root">
              <ul class="-mb-8">
                <!-- Requested -->
                <li>
                  <div class="relative pb-8">
                    <div class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></div>
                    <div class="relative flex space-x-3">
                      <div>
                        <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                          <Icon name="plus" class="h-4 w-4 text-white" />
                        </span>
                      </div>
                      <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                        <div>
                          <p class="text-sm text-gray-500">
                            Withdrawal request submitted
                          </p>
                        </div>
                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                          {{ formatDate(withdrawal.requested_at) }}
                        </div>
                      </div>
                    </div>
                  </div>
                </li>

                <!-- Approved -->
                <li v-if="withdrawal.approved_at">
                  <div class="relative pb-8">
                    <div v-if="withdrawal.processed_at" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></div>
                    <div class="relative flex space-x-3">
                      <div>
                        <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                          <Icon name="check" class="h-4 w-4 text-white" />
                        </span>
                      </div>
                      <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                        <div>
                          <p class="text-sm text-gray-500">
                            Request approved by admin
                          </p>
                          <p v-if="withdrawal.processor" class="text-xs text-gray-400">
                            Approved by: {{ withdrawal.processor.name }}
                          </p>
                        </div>
                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                          {{ formatDate(withdrawal.approved_at) }}
                        </div>
                      </div>
                    </div>
                  </div>
                </li>

                <!-- Processed -->
                <li v-if="withdrawal.processed_at">
                  <div class="relative">
                    <div class="relative flex space-x-3">
                      <div>
                        <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                          <Icon name="credit-card" class="h-4 w-4 text-white" />
                        </span>
                      </div>
                      <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                        <div>
                          <p class="text-sm text-gray-500">
                            Payment processed
                          </p>
                          <p v-if="withdrawal.transaction_id" class="text-xs text-gray-400">
                            Transaction ID: {{ withdrawal.transaction_id }}
                          </p>
                        </div>
                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                          {{ formatDate(withdrawal.processed_at) }}
                        </div>
                      </div>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
          </div>

          <!-- Bank Details -->
          <div v-if="withdrawal.bank_details">
            <h4 class="text-sm font-medium text-gray-900 mb-3">Bank Details</h4>
            <div class="bg-gray-50 rounded-lg p-4">
              <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-if="withdrawal.bank_details.bank_name">
                  <dt class="text-xs text-gray-500">Bank Name</dt>
                  <dd class="text-sm text-gray-900">{{ withdrawal.bank_details.bank_name }}</dd>
                </div>
                <div v-if="withdrawal.bank_details.account_holder_name">
                  <dt class="text-xs text-gray-500">Account Holder</dt>
                  <dd class="text-sm text-gray-900">{{ withdrawal.bank_details.account_holder_name }}</dd>
                </div>
                <div v-if="withdrawal.bank_details.account_number">
                  <dt class="text-xs text-gray-500">Account Number</dt>
                  <dd class="text-sm text-gray-900 font-mono">
                    {{ maskAccountNumber(withdrawal.bank_details.account_number) }}
                  </dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Emergency Reason -->
          <div v-if="withdrawal.type === 'emergency' && withdrawal.reason">
            <h4 class="text-sm font-medium text-gray-900 mb-3">Emergency Reason</h4>
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
              <p class="text-sm text-amber-800">{{ withdrawal.reason }}</p>
            </div>
          </div>

          <!-- Rejection Reason -->
          <div v-if="withdrawal.status === 'rejected' && withdrawal.rejection_reason">
            <h4 class="text-sm font-medium text-gray-900 mb-3">Rejection Reason</h4>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
              <p class="text-sm text-red-800">{{ withdrawal.rejection_reason }}</p>
            </div>
          </div>

          <!-- Admin Notes -->
          <div v-if="withdrawal.admin_notes">
            <h4 class="text-sm font-medium text-gray-900 mb-3">Admin Notes</h4>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <p class="text-sm text-blue-800">{{ withdrawal.admin_notes }}</p>
            </div>
          </div>

          <!-- Commission Clawbacks -->
          <div v-if="withdrawal.commission_clawbacks && withdrawal.commission_clawbacks.length > 0">
            <h4 class="text-sm font-medium text-gray-900 mb-3">Commission Clawbacks</h4>
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="space-y-2">
                <div 
                  v-for="clawback in withdrawal.commission_clawbacks" 
                  :key="clawback.id"
                  class="flex justify-between items-center text-sm"
                >
                  <span class="text-gray-600">{{ clawback.reason }}</span>
                  <span class="font-medium text-red-600">-{{ formatCurrency(clawback.clawback_amount) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
          <button
            v-if="withdrawal.status === 'completed' && withdrawal.transaction_id"
            @click="downloadReceipt"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
          >
            Download Receipt
          </button>
          <button
            @click="$emit('close')"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import Icon from '@/components/Icon.vue'
import StatusBadge from '@/components/StatusBadge.vue'

interface Withdrawal {
  id: number
  reference_number?: string
  amount: number
  penalty_amount: number
  fee: number
  net_amount: number
  type: string
  status: string
  reason?: string
  rejection_reason?: string
  admin_notes?: string
  transaction_id?: string
  requested_at: string
  approved_at?: string
  processed_at?: string
  bank_details?: {
    bank_name?: string
    account_holder_name?: string
    account_number?: string
  }
  processor?: {
    name: string
  }
  commission_clawbacks?: Array<{
    id: number
    reason: string
    clawback_amount: number
  }>
}

interface Props {
  withdrawal: Withdrawal
}

defineProps<Props>()
defineEmits<{
  close: []
}>()

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

const maskAccountNumber = (accountNumber: string) => {
  if (accountNumber.length <= 4) return accountNumber
  return '*'.repeat(accountNumber.length - 4) + accountNumber.slice(-4)
}

const downloadReceipt = () => {
  window.open(`/api/withdrawals/${withdrawal.id}/receipt`, '_blank')
}
</script>