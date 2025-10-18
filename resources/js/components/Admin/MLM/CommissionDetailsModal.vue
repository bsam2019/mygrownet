<template>
  <TransitionRoot as="template" :show="show">
    <Dialog as="div" class="relative z-10" @close="$emit('close')">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl sm:p-6">
              <div class="absolute right-0 top-0 hidden pr-4 pt-4 sm:block">
                <button
                  type="button"
                  class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                  @click="$emit('close')"
                >
                  <span class="sr-only">Close</span>
                  <XMarkIcon class="h-6 w-6" />
                </button>
              </div>

              <div class="sm:flex sm:items-start">
                <div class="w-full">
                  <DialogTitle as="h3" class="text-lg font-semibold leading-6 text-gray-900 mb-6">
                    Commission Details
                  </DialogTitle>

                  <div v-if="loading" class="flex justify-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                  </div>

                  <div v-else-if="details" class="space-y-6">
                    <!-- Commission Information -->
                    <div class="bg-gray-50 rounded-lg p-4">
                      <h4 class="text-sm font-medium text-gray-900 mb-3">Commission Information</h4>
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                          <dt class="text-sm font-medium text-gray-500">Commission ID</dt>
                          <dd class="text-sm text-gray-900">{{ details.commission.id }}</dd>
                        </div>
                        <div>
                          <dt class="text-sm font-medium text-gray-500">Amount</dt>
                          <dd class="text-sm text-gray-900 font-semibold">K{{ formatNumber(details.commission.amount) }}</dd>
                        </div>
                        <div>
                          <dt class="text-sm font-medium text-gray-500">Level</dt>
                          <dd class="text-sm text-gray-900">Level {{ details.commission.level }}</dd>
                        </div>
                        <div>
                          <dt class="text-sm font-medium text-gray-500">Type</dt>
                          <dd class="text-sm text-gray-900">{{ details.commission.commission_type }}</dd>
                        </div>
                        <div>
                          <dt class="text-sm font-medium text-gray-500">Status</dt>
                          <dd>
                            <span :class="[
                              'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                              details.commission.status === 'paid' ? 'bg-green-100 text-green-800' :
                              details.commission.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                              'bg-red-100 text-red-800'
                            ]">
                              {{ details.commission.status }}
                            </span>
                          </dd>
                        </div>
                        <div>
                          <dt class="text-sm font-medium text-gray-500">Created At</dt>
                          <dd class="text-sm text-gray-900">{{ formatDate(details.commission.created_at) }}</dd>
                        </div>
                        <div v-if="details.commission.paid_at">
                          <dt class="text-sm font-medium text-gray-500">Paid At</dt>
                          <dd class="text-sm text-gray-900">{{ formatDate(details.commission.paid_at) }}</dd>
                        </div>
                        <div v-if="details.commission.adjusted_at">
                          <dt class="text-sm font-medium text-gray-500">Adjusted At</dt>
                          <dd class="text-sm text-gray-900">{{ formatDate(details.commission.adjusted_at) }}</dd>
                        </div>
                      </div>
                      
                      <div v-if="details.commission.adjustment_reason" class="mt-4">
                        <dt class="text-sm font-medium text-gray-500">Adjustment Reason</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ details.commission.adjustment_reason }}</dd>
                      </div>
                    </div>

                    <!-- Referrer Information -->
                    <div class="bg-blue-50 rounded-lg p-4">
                      <h4 class="text-sm font-medium text-gray-900 mb-3">Referrer Information</h4>
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                          <dt class="text-sm font-medium text-gray-500">Name</dt>
                          <dd class="text-sm text-gray-900">{{ details.commission.referrer?.name || 'N/A' }}</dd>
                        </div>
                        <div>
                          <dt class="text-sm font-medium text-gray-500">Email</dt>
                          <dd class="text-sm text-gray-900">{{ details.commission.referrer?.email || 'N/A' }}</dd>
                        </div>
                        <div>
                          <dt class="text-sm font-medium text-gray-500">Total Commissions</dt>
                          <dd class="text-sm text-gray-900">K{{ formatNumber(details.referrer_stats.total_commissions || 0) }}</dd>
                        </div>
                        <div>
                          <dt class="text-sm font-medium text-gray-500">Monthly Commissions</dt>
                          <dd class="text-sm text-gray-900">K{{ formatNumber(details.referrer_stats.monthly_commissions || 0) }}</dd>
                        </div>
                        <div>
                          <dt class="text-sm font-medium text-gray-500">Referral Count</dt>
                          <dd class="text-sm text-gray-900">{{ details.referrer_stats.referral_count || 0 }}</dd>
                        </div>
                        <div>
                          <dt class="text-sm font-medium text-gray-500">Current Tier</dt>
                          <dd class="text-sm text-gray-900">{{ details.referrer_stats.current_tier || 'N/A' }}</dd>
                        </div>
                      </div>
                    </div>

                    <!-- Referee Information -->
                    <div class="bg-green-50 rounded-lg p-4">
                      <h4 class="text-sm font-medium text-gray-900 mb-3">Referee Information</h4>
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                          <dt class="text-sm font-medium text-gray-500">Name</dt>
                          <dd class="text-sm text-gray-900">{{ details.commission.referee?.name || 'N/A' }}</dd>
                        </div>
                        <div>
                          <dt class="text-sm font-medium text-gray-500">Email</dt>
                          <dd class="text-sm text-gray-900">{{ details.commission.referee?.email || 'N/A' }}</dd>
                        </div>
                        <div>
                          <dt class="text-sm font-medium text-gray-500">Total Commissions</dt>
                          <dd class="text-sm text-gray-900">K{{ formatNumber(details.referee_stats.total_commissions || 0) }}</dd>
                        </div>
                        <div>
                          <dt class="text-sm font-medium text-gray-500">Monthly Commissions</dt>
                          <dd class="text-sm text-gray-900">K{{ formatNumber(details.referee_stats.monthly_commissions || 0) }}</dd>
                        </div>
                        <div>
                          <dt class="text-sm font-medium text-gray-500">Referral Count</dt>
                          <dd class="text-sm text-gray-900">{{ details.referee_stats.referral_count || 0 }}</dd>
                        </div>
                        <div>
                          <dt class="text-sm font-medium text-gray-500">Current Tier</dt>
                          <dd class="text-sm text-gray-900">{{ details.referee_stats.current_tier || 'N/A' }}</dd>
                        </div>
                      </div>
                    </div>

                    <!-- Related Commissions -->
                    <div v-if="details.related_commissions.length > 0" class="bg-purple-50 rounded-lg p-4">
                      <h4 class="text-sm font-medium text-gray-900 mb-3">Related Commissions</h4>
                      <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                          <thead class="bg-gray-50">
                            <tr>
                              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
                              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Referrer</th>
                              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                          </thead>
                          <tbody class="divide-y divide-gray-200">
                            <tr v-for="commission in details.related_commissions" :key="commission.id">
                              <td class="px-4 py-2 text-sm text-gray-900">Level {{ commission.level }}</td>
                              <td class="px-4 py-2 text-sm text-gray-900">{{ commission.referrer?.name || 'N/A' }}</td>
                              <td class="px-4 py-2 text-sm text-gray-900">K{{ formatNumber(commission.amount) }}</td>
                              <td class="px-4 py-2">
                                <span :class="[
                                  'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                                  commission.status === 'paid' ? 'bg-green-100 text-green-800' :
                                  commission.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                  'bg-red-100 text-red-800'
                                ]">
                                  {{ commission.status }}
                                </span>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>

                  <div v-else-if="error" class="text-center py-8">
                    <p class="text-red-600">{{ error }}</p>
                  </div>
                </div>
              </div>

              <div class="mt-6 flex justify-end">
                <button
                  type="button"
                  class="inline-flex justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                  @click="$emit('close')"
                >
                  Close
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'

interface Props {
  show: boolean
  commissionId: number | null
}

const props = defineProps<Props>()
const emit = defineEmits(['close'])

const loading = ref(false)
const details = ref<any>(null)
const error = ref<string | null>(null)

const formatNumber = (value: number): string => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  }).format(value)
}

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const fetchCommissionDetails = async (commissionId: number) => {
  loading.value = true
  error.value = null
  details.value = null

  try {
    const response = await fetch(route('admin.mlm.commission-details', { commission: commissionId }), {
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
    })

    const data = await response.json()

    if (data.success) {
      details.value = data.data
    } else {
      error.value = data.message || 'Failed to load commission details'
    }
  } catch (err) {
    error.value = 'An error occurred while loading commission details'
    console.error('Error fetching commission details:', err)
  } finally {
    loading.value = false
  }
}

watch(() => props.show, (show) => {
  if (show && props.commissionId) {
    fetchCommissionDetails(props.commissionId)
  }
})
</script>