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
            <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:p-6">
              <form @submit.prevent="submit">
                <div>
                  <DialogTitle as="h3" class="text-lg font-semibold leading-6 text-gray-900 mb-4">
                    Apply Material Template
                  </DialogTitle>

                  <div class="space-y-4">
                    <!-- Template Selection -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">
                        Select Template <span class="text-red-500">*</span>
                      </label>
                      <select
                        v-model="form.template_id"
                        required
                        @change="onTemplateChange"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                      >
                        <option value="">Choose a template</option>
                        <option v-for="template in templates" :key="template.id" :value="template.id">
                          {{ template.name }}
                        </option>
                      </select>
                    </div>

                    <!-- Template Description -->
                    <div v-if="selectedTemplate" class="bg-blue-50 rounded-lg p-3">
                      <p class="text-sm text-gray-700">{{ selectedTemplate.description }}</p>
                      <p class="text-xs text-gray-500 mt-1">{{ selectedTemplate.items.length }} materials in template</p>
                    </div>

                    <!-- Job Size -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">
                        Job Size <span class="text-red-500">*</span>
                      </label>
                      <div class="flex items-center space-x-2">
                        <input
                          v-model="form.job_size"
                          type="number"
                          step="0.01"
                          min="0.01"
                          required
                          placeholder="e.g., 25"
                          class="flex-1 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                        />
                        <span class="text-sm text-gray-500">m² (or relevant unit)</span>
                      </div>
                      <p class="mt-1 text-xs text-gray-500">Material quantities will be calculated based on this size</p>
                    </div>

                    <!-- Template Preview -->
                    <div v-if="selectedTemplate && form.job_size" class="border border-gray-200 rounded-lg overflow-hidden">
                      <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                        <h4 class="text-sm font-medium text-gray-900">Preview: Materials to be Added</h4>
                      </div>
                      <div class="max-h-64 overflow-y-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                          <thead class="bg-gray-50">
                            <tr>
                              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Material</th>
                              <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Quantity</th>
                              <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Est. Cost</th>
                            </tr>
                          </thead>
                          <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="item in previewItems" :key="item.id" class="text-sm">
                              <td class="px-4 py-2 text-gray-900">{{ item.material.name }}</td>
                              <td class="px-4 py-2 text-right text-gray-900">
                                {{ item.calculatedQuantity.toFixed(2) }} {{ item.material.unit }}
                              </td>
                              <td class="px-4 py-2 text-right font-medium text-gray-900">
                                K{{ item.estimatedCost.toFixed(2) }}
                              </td>
                            </tr>
                          </tbody>
                          <tfoot class="bg-gray-50">
                            <tr>
                              <td colspan="2" class="px-4 py-2 text-sm font-medium text-gray-900 text-right">
                                Total Estimated Cost:
                              </td>
                              <td class="px-4 py-2 text-sm font-bold text-gray-900 text-right">
                                K{{ totalEstimatedCost.toFixed(2) }}
                              </td>
                            </tr>
                          </tfoot>
                        </table>
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
                    :disabled="form.processing || !selectedTemplate"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50"
                  >
                    <span v-if="form.processing">Applying...</span>
                    <span v-else>Apply Template</span>
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
  templates: Array,
})

const emit = defineEmits(['close', 'applied'])

const form = useForm({
  template_id: '',
  job_size: '',
})

const selectedTemplate = computed(() => {
  if (!form.template_id) return null
  return props.templates.find(t => t.id === parseInt(form.template_id))
})

const previewItems = computed(() => {
  if (!selectedTemplate.value || !form.job_size) return []
  
  return selectedTemplate.value.items.map(item => {
    const baseQuantity = parseFloat(form.job_size) * parseFloat(item.quantity_per_unit)
    const wastage = baseQuantity * (parseFloat(item.wastage_percentage) / 100)
    const calculatedQuantity = baseQuantity + wastage
    const estimatedCost = calculatedQuantity * parseFloat(item.material.current_price)
    
    return {
      ...item,
      calculatedQuantity,
      estimatedCost,
    }
  })
})

const totalEstimatedCost = computed(() => {
  return previewItems.value.reduce((sum, item) => sum + item.estimatedCost, 0)
})

const onTemplateChange = () => {
  // Reset job size when template changes
  form.job_size = ''
}

const submit = () => {
  form.post(route('cms.jobs.materials.apply-template', props.job.id), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      emit('applied')
    },
  })
}

watch(() => props.show, (newVal) => {
  if (!newVal) {
    form.reset()
    form.clearErrors()
  }
})
</script>
