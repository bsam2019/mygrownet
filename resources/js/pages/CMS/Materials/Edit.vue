<template>
  <CMSLayout title="Edit Material">
    <div class="max-w-3xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <Link
          :href="route('cms.materials.index')"
          class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4"
        >
          <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
          Back to Materials
        </Link>
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Material</h1>
            <p class="mt-1 text-sm text-gray-500">{{ material.name }}</p>
          </div>
          <button
            @click="showDeleteConfirm = true"
            class="px-4 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100"
          >
            Delete Material
          </button>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="bg-white rounded-lg shadow">
        <div class="p-6 space-y-6">
          <!-- Basic Information -->
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Material Code <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.code"
                  type="text"
                  required
                  class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                  :class="{ 'border-red-500': form.errors.code }"
                />
                <p v-if="form.errors.code" class="mt-1 text-sm text-red-600">{{ form.errors.code }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <select
                  v-model="form.category_id"
                  class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                >
                  <option value="">Select Category</option>
                  <option v-for="category in categories" :key="category.id" :value="category.id">
                    {{ category.name }}
                  </option>
                </select>
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Material Name <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.name"
                  type="text"
                  required
                  class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                  :class="{ 'border-red-500': form.errors.name }"
                />
                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea
                  v-model="form.description"
                  rows="3"
                  class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                ></textarea>
              </div>
            </div>
          </div>

          <!-- Pricing & Units -->
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Pricing & Units</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Unit of Measurement <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.unit"
                  required
                  class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                >
                  <option value="meters">Meters</option>
                  <option value="m²">Square Meters (m²)</option>
                  <option value="m³">Cubic Meters (m³)</option>
                  <option value="kg">Kilograms (kg)</option>
                  <option value="pcs">Pieces (pcs)</option>
                  <option value="box">Box</option>
                  <option value="bag">Bag</option>
                  <option value="sheet">Sheet</option>
                  <option value="roll">Roll</option>
                  <option value="tube">Tube</option>
                  <option value="liters">Liters</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Current Price (K) <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.current_price"
                  type="number"
                  step="0.01"
                  min="0"
                  required
                  class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                  :class="{ 'border-red-500': form.errors.current_price }"
                />
                <p v-if="form.errors.current_price" class="mt-1 text-sm text-red-600">{{ form.errors.current_price }}</p>
              </div>

              <div v-if="priceChanged" class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Reason for Price Change <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.price_change_reason"
                  type="text"
                  required
                  placeholder="e.g., Supplier price increase"
                  class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                />
                <p class="mt-1 text-sm text-amber-600">
                  Price change detected: K{{ material.current_price }} → K{{ form.current_price }}
                </p>
              </div>
            </div>
          </div>

          <!-- Supplier Information -->
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Supplier Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Supplier Name</label>
                <input
                  v-model="form.supplier"
                  type="text"
                  class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Supplier Code</label>
                <input
                  v-model="form.supplier_code"
                  type="text"
                  class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lead Time (Days)</label>
                <input
                  v-model="form.lead_time_days"
                  type="number"
                  min="0"
                  class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                />
              </div>
            </div>
          </div>

          <!-- Stock Management -->
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Stock Management</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Stock Level</label>
                <input
                  v-model="form.minimum_stock"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reorder Level</label>
                <input
                  v-model="form.reorder_level"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                />
              </div>
            </div>
          </div>

          <!-- Status -->
          <div>
            <label class="flex items-center">
              <input
                v-model="form.is_active"
                type="checkbox"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              />
              <span class="ml-2 text-sm text-gray-700">Active (available for use)</span>
            </label>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-3 rounded-b-lg">
          <Link
            :href="route('cms.materials.index')"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
          >
            Cancel
          </Link>
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

      <!-- Delete Confirmation Modal -->
      <TransitionRoot as="template" :show="showDeleteConfirm">
        <Dialog as="div" class="relative z-50" @close="showDeleteConfirm = false">
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
                  <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                      <ExclamationTriangleIcon class="h-6 w-6 text-red-600" aria-hidden="true" />
                    </div>
                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                      <DialogTitle as="h3" class="text-base font-semibold leading-6 text-gray-900">
                        Delete Material
                      </DialogTitle>
                      <div class="mt-2">
                        <p class="text-sm text-gray-500">
                          Are you sure you want to delete this material? This action cannot be undone.
                        </p>
                      </div>
                    </div>
                  </div>
                  <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button
                      type="button"
                      @click="deleteMaterial"
                      :disabled="deleteForm.processing"
                      class="inline-flex w-full justify-center rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto disabled:opacity-50"
                    >
                      Delete
                    </button>
                    <button
                      type="button"
                      @click="showDeleteConfirm = false"
                      class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                    >
                      Cancel
                    </button>
                  </div>
                </DialogPanel>
              </TransitionChild>
            </div>
          </div>
        </Dialog>
      </TransitionRoot>
    </div>
  </CMSLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, Link, router } from '@inertiajs/vue3'
import CMSLayout from '@/Layouts/CMSLayout.vue'
import { ArrowLeftIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'

const props = defineProps({
  material: Object,
  categories: Array,
})

const showDeleteConfirm = ref(false)

const form = useForm({
  code: props.material.code,
  name: props.material.name,
  description: props.material.description,
  category_id: props.material.category_id || '',
  unit: props.material.unit,
  current_price: props.material.current_price,
  supplier: props.material.supplier,
  supplier_code: props.material.supplier_code,
  lead_time_days: props.material.lead_time_days,
  minimum_stock: props.material.minimum_stock,
  reorder_level: props.material.reorder_level,
  is_active: props.material.is_active,
  price_change_reason: '',
})

const deleteForm = useForm({})

const priceChanged = computed(() => {
  return parseFloat(form.current_price) !== parseFloat(props.material.current_price)
})

const submit = () => {
  form.put(route('cms.materials.update', props.material.id), {
    onSuccess: () => {
      // Redirect handled by controller
    },
  })
}

const deleteMaterial = () => {
  deleteForm.delete(route('cms.materials.destroy', props.material.id), {
    onSuccess: () => {
      showDeleteConfirm.value = false
    },
  })
}
</script>
