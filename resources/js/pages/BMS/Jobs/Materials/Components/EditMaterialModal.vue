<template>
  <TransitionRoot as="template" :show="show">
    <Dialog as="div" class="relative z-50" @close="$emit('close')">
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
              <form v-if="plan" @submit.prevent="submit">
                <div>
                  <DialogTitle as="h3" class="text-lg font-semibold leading-6 text-gray-900 mb-4">
                    Edit Material Plan
                  </DialogTitle>

                  <div class="space-y-4">
                    <!-- Material Info (Read-only) -->
                    <div class="bg-gray-50 rounded-lg p-3">
                      <div class="text-sm font-medium text-gray-900">{{ plan.material.name }}</div>
                      <div class="text-xs text-gray-500 mt-1">{{ plan.material.code }}</div>
                    </div>

                    <!-- Planned Quantity -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">
                        Planned Quantity <span class="text-red-500">*</span>
                      </label>
                      <div class="flex items-center space-x-2">
                        <input
                          v-model="form.planned_quantity"
                          type="number"
                          step="0.01"
                          min="0.01"
                          required
                          class="flex-1 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                        />
                        <span class="text-sm text-gray-500">{{ plan.material.unit }}</span>
                      </div>
                    </div>

                    <!-- Unit Price -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">
                        Unit Price (K) <span class="text-red-500">*</span>
                      </label>
                      <input
                        v-model="form.unit_price"
                        type="number"
                        step="0.01"
                        min="0"
                        required
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                      />
                    </div>

                    <!-- Wastage Percentage -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">
                        Wastage Percentage
                      </label>
                      <div class="flex items-center space-x-2">
                        <input
                          v-model="form.wastage_percentage"
                          type="number"
                          step="0.1"
                          min="0"
                          max="100"
                          class="flex-1 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                        />
                        <span class="text-sm text-gray-500">%</span>
                      </div>
                    </div>

                    <!-- Notes -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                      <textarea
                        v-model="form.notes"
                        rows="2"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                      ></textarea>
                    </div>

                    <!-- Updated Cost -->
                    <div class="bg-blue-50 rounded-lg p-3">
                      <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Updated Cost:</span>
                        <span class="text-lg font-bold text-blue-700">K{{ updatedCost.toFixed(2) }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="mt-6 flex items-center justify-end space-x-3">
                  <button
                    type="button"
                    @click="$emit('close')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50"
                  >
                    <span v-if="form.processing">Saving...</span>
                    <span v-else>Save Changes</span>
                  </button>
                </div>
              </form>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'

const props = defineProps({
  show: Boolean,
  job: Object,
  plan: Object,
})

const emit = defineEmits(['close', 'updated'])

const form = useForm({
  planned_quantity: '',
  unit_price: '',
  wastage_percentage: 0,
  notes: '',
})

const updatedCost = computed(() => {
  if (!form.planned_quantity || !form.unit_price) return 0
  return parseFloat(form.planned_quantity) * parseFloat(form.unit_price)
})

const submit = () => {
  form.put(route('cms.jobs.materials.update', [props.job.id, props.plan.id]), {
    preserveScroll: true,
    onSuccess: () => {
      emit('updated')
    },
  })
}

watch(() => props.plan, (newPlan) => {
  if (newPlan) {
    form.planned_quantity = newPlan.planned_quantity
    form.unit_price = newPlan.unit_price
    form.wastage_percentage = newPlan.wastage_percentage || 0
    form.notes = newPlan.notes || ''
  }
}, { immediate: true })

watch(() => props.show, (newVal) => {
  if (!newVal) {
    form.clearErrors()
  }
})
</script>
