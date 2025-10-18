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
                    Adjust Commission
                  </DialogTitle>

                  <div v-if="commission" class="space-y-6">
                    <!-- Current Commission Info -->
                    <div class="bg-gray-50 rounded-lg p-4">
                      <h4 class="text-sm font-medium text-gray-900 mb-3">Current Commission</h4>
                      <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                          <dt class="font-medium text-gray-500">Referrer</dt>
                          <dd class="text-gray-900">{{ commission.referrer?.name || 'N/A' }}</dd>
                        </div>
                        <div>
                          <dt class="font-medium text-gray-500">Current Amount</dt>
                          <dd class="text-gray-900 font-semibold">K{{ formatNumber(commission.amount) }}</dd>
                        </div>
                        <div>
                          <dt class="font-medium text-gray-500">Level</dt>
                          <dd class="text-gray-900">Level {{ commission.level }}</dd>
                        </div>
                        <div>
                          <dt class="font-medium text-gray-500">Type</dt>
                          <dd class="text-gray-900">{{ commission.commission_type }}</dd>
                        </div>
                      </div>
                    </div>

                    <!-- Adjustment Form -->
                    <form @submit.prevent="submitAdjustment" class="space-y-4">
                      <div>
                        <label for="new_amount" class="block text-sm font-medium text-gray-700">
                          New Amount (K)
                        </label>
                        <input
                          id="new_amount"
                          v-model="form.new_amount"
                          type="number"
                          step="0.01"
                          min="0"
                          required
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                          :class="{ 'border-red-300': errors.new_amount }"
                        />
                        <p v-if="errors.new_amount" class="mt-1 text-sm text-red-600">
                          {{ errors.new_amount }}
                        </p>
                      </div>

                      <div>
                        <label for="reason" class="block text-sm font-medium text-gray-700">
                          Reason for Adjustment
                        </label>
                        <textarea
                          id="reason"
                          v-model="form.reason"
                          rows="3"
                          required
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                          :class="{ 'border-red-300': errors.reason }"
                          placeholder="Explain why this commission is being adjusted..."
                        />
                        <p v-if="errors.reason" class="mt-1 text-sm text-red-600">
                          {{ errors.reason }}
                        </p>
                      </div>

                      <!-- Adjustment Preview -->
                      <div v-if="form.new_amount && form.new_amount !== commission.amount" class="bg-blue-50 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Adjustment Preview</h4>
                        <div class="text-sm space-y-1">
                          <div class="flex justify-between">
                            <span class="text-gray-600">Original Amount:</span>
                            <span class="font-medium">K{{ formatNumber(commission.amount) }}</span>
                          </div>
                          <div class="flex justify-between">
                            <span class="text-gray-600">New Amount:</span>
                            <span class="font-medium">K{{ formatNumber(parseFloat(form.new_amount) || 0) }}</span>
                          </div>
                          <div class="flex justify-between border-t pt-1">
                            <span class="text-gray-600">Difference:</span>
                            <span :class="[
                              'font-medium',
                              adjustmentDifference >= 0 ? 'text-green-600' : 'text-red-600'
                            ]">
                              {{ adjustmentDifference >= 0 ? '+' : '' }}K{{ formatNumber(adjustmentDifference) }}
                            </span>
                          </div>
                        </div>
                      </div>

                      <div class="flex justify-end space-x-3 pt-4">
                        <button
                          type="button"
                          class="inline-flex justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                          @click="$emit('close')"
                        >
                          Cancel
                        </button>
                        <button
                          type="submit"
                          :disabled="processing || !form.new_amount || !form.reason"
                          class="inline-flex justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:opacity-50"
                        >
                          <span v-if="processing" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                          </span>
                          <span v-else>Adjust Commission</span>
                        </button>
                      </div>
                    </form>
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
import { ref, computed, watch } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'

interface Props {
  show: boolean
  commission: any
}

const props = defineProps<Props>()
const emit = defineEmits(['close', 'adjusted'])

const processing = ref(false)
const errors = ref<Record<string, string>>({})

const form = ref({
  new_amount: '',
  reason: '',
})

const adjustmentDifference = computed(() => {
  if (!form.value.new_amount || !props.commission) return 0
  return parseFloat(form.value.new_amount) - props.commission.amount
})

const formatNumber = (value: number): string => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  }).format(value)
}

const resetForm = () => {
  form.value = {
    new_amount: props.commission?.amount?.toString() || '',
    reason: '',
  }
  errors.value = {}
}

const submitAdjustment = async () => {
  if (processing.value) return

  processing.value = true
  errors.value = {}

  try {
    const response = await fetch(route('admin.mlm.adjust-commission'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({
        commission_id: props.commission.id,
        new_amount: parseFloat(form.value.new_amount),
        reason: form.value.reason,
      }),
    })

    const data = await response.json()

    if (data.success) {
      emit('adjusted')
      resetForm()
    } else {
      if (data.errors) {
        errors.value = data.errors
      } else {
        alert('Failed to adjust commission: ' + (data.message || 'Unknown error'))
      }
    }
  } catch (error) {
    console.error('Error adjusting commission:', error)
    alert('An error occurred while adjusting the commission')
  } finally {
    processing.value = false
  }
}

watch(() => props.show, (show) => {
  if (show && props.commission) {
    resetForm()
  }
})
</script>