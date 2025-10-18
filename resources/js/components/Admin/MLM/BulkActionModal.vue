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
            <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
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
                    Bulk Process Commissions
                  </DialogTitle>

                  <div class="space-y-6">
                    <!-- Selection Summary -->
                    <div class="bg-gray-50 rounded-lg p-4">
                      <h4 class="text-sm font-medium text-gray-900 mb-2">Selection Summary</h4>
                      <p class="text-sm text-gray-600">
                        You have selected <span class="font-medium">{{ commissionIds.length }}</span> commission(s) for bulk processing.
                      </p>
                    </div>

                    <!-- Action Selection -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-3">
                        Choose Action
                      </label>
                      <div class="space-y-3">
                        <div class="flex items-center">
                          <input
                            id="approve"
                            v-model="selectedAction"
                            type="radio"
                            value="approve"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                          />
                          <label for="approve" class="ml-3 block text-sm font-medium text-gray-700">
                            <span class="text-green-600">Approve</span> - Process and pay selected commissions
                          </label>
                        </div>
                        <div class="flex items-center">
                          <input
                            id="reject"
                            v-model="selectedAction"
                            type="radio"
                            value="reject"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                          />
                          <label for="reject" class="ml-3 block text-sm font-medium text-gray-700">
                            <span class="text-red-600">Reject</span> - Mark selected commissions as rejected
                          </label>
                        </div>
                      </div>
                    </div>

                    <!-- Warning Message -->
                    <div v-if="selectedAction" :class="[
                      'rounded-md p-4',
                      selectedAction === 'approve' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'
                    ]">
                      <div class="flex">
                        <div class="flex-shrink-0">
                          <CheckCircleIcon v-if="selectedAction === 'approve'" class="h-5 w-5 text-green-400" />
                          <ExclamationTriangleIcon v-else class="h-5 w-5 text-red-400" />
                        </div>
                        <div class="ml-3">
                          <h3 :class="[
                            'text-sm font-medium',
                            selectedAction === 'approve' ? 'text-green-800' : 'text-red-800'
                          ]">
                            {{ selectedAction === 'approve' ? 'Approve Commissions' : 'Reject Commissions' }}
                          </h3>
                          <div :class="[
                            'mt-1 text-sm',
                            selectedAction === 'approve' ? 'text-green-700' : 'text-red-700'
                          ]">
                            <p v-if="selectedAction === 'approve'">
                              This will immediately process payments for {{ commissionIds.length }} commission(s). 
                              The referrers will receive their commission amounts in their account balances.
                            </p>
                            <p v-else>
                              This will mark {{ commissionIds.length }} commission(s) as rejected. 
                              This action cannot be undone and no payments will be made.
                            </p>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Processing Results -->
                    <div v-if="processingResults" class="space-y-3">
                      <div v-if="processingResults.summary.processed > 0" class="bg-green-50 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-green-800 mb-2">Successfully Processed</h4>
                        <p class="text-sm text-green-700">
                          {{ processingResults.summary.processed }} commission(s) were successfully {{ selectedAction }}d.
                        </p>
                      </div>

                      <div v-if="processingResults.summary.failed > 0" class="bg-red-50 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-red-800 mb-2">Failed to Process</h4>
                        <p class="text-sm text-red-700 mb-2">
                          {{ processingResults.summary.failed }} commission(s) failed to process:
                        </p>
                        <ul class="text-sm text-red-700 list-disc list-inside space-y-1">
                          <li v-for="failure in processingResults.failed" :key="failure.commission_id">
                            Commission #{{ failure.commission_id }}: {{ failure.error }}
                          </li>
                        </ul>
                      </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                      <button
                        type="button"
                        class="inline-flex justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                        @click="handleClose"
                      >
                        {{ processingResults ? 'Close' : 'Cancel' }}
                      </button>
                      <button
                        v-if="!processingResults"
                        type="button"
                        :disabled="!selectedAction || processing"
                        :class="[
                          'inline-flex justify-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:opacity-50',
                          selectedAction === 'approve' 
                            ? 'bg-green-600 hover:bg-green-500 focus-visible:outline-green-600' 
                            : 'bg-red-600 hover:bg-red-500 focus-visible:outline-red-600'
                        ]"
                        @click="processCommissions"
                      >
                        <span v-if="processing" class="flex items-center">
                          <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                          </svg>
                          Processing...
                        </span>
                        <span v-else>
                          {{ selectedAction === 'approve' ? 'Approve' : 'Reject' }} {{ commissionIds.length }} Commission(s)
                        </span>
                      </button>
                    </div>
                  </div>
                </div>
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
import { 
  CheckCircleIcon, 
  ExclamationTriangleIcon, 
  XMarkIcon 
} from '@heroicons/vue/24/outline'

interface Props {
  show: boolean
  commissionIds: number[]
}

const props = defineProps<Props>()
const emit = defineEmits(['close', 'processed'])

const selectedAction = ref<'approve' | 'reject' | ''>('')
const processing = ref(false)
const processingResults = ref<any>(null)

const resetModal = () => {
  selectedAction.value = ''
  processing.value = false
  processingResults.value = null
}

const handleClose = () => {
  if (processingResults.value) {
    emit('processed')
  }
  emit('close')
  resetModal()
}

const processCommissions = async () => {
  if (!selectedAction.value || processing.value) return

  processing.value = true

  try {
    const response = await fetch(route('admin.mlm.process-pending-commissions'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({
        commission_ids: props.commissionIds,
        action: selectedAction.value,
      }),
    })

    const data = await response.json()

    if (data.success) {
      processingResults.value = data.data
    } else {
      alert('Failed to process commissions: ' + (data.message || 'Unknown error'))
    }
  } catch (error) {
    console.error('Error processing commissions:', error)
    alert('An error occurred while processing commissions')
  } finally {
    processing.value = false
  }
}

watch(() => props.show, (show) => {
  if (show) {
    resetModal()
  }
})
</script>