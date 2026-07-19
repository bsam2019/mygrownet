<template>
  <CMSLayout title="Add Material">
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
        <h1 class="text-2xl font-bold text-gray-900">Add New Material</h1>
        <p class="mt-1 text-sm text-gray-500">Add a new material to your catalog</p>
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
                  placeholder="e.g., ALU-FRAME-001"
                  class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                  :class="{ 'border-red-500': form.errors.code }"
                />
                <p v-if="form.errors.code" class="mt-1 text-sm text-red-600">{{ form.errors.code }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Category
                </label>
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
                  placeholder="e.g., Aluminium Frame Profile 50x50mm"
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
                  placeholder="Optional description or specifications"
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
                  <option value="">Select Unit</option>
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
                <p v-if="form.errors.unit" class="mt-1 text-sm text-red-600">{{ form.errors.unit }}</p>
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
                  placeholder="0.00"
                  class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                  :class="{ 'border-red-500': form.errors.current_price }"
                />
                <p v-if="form.errors.current_price" class="mt-1 text-sm text-red-600">{{ form.errors.current_price }}</p>
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
                  placeholder="e.g., ABC Suppliers"
                  class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Supplier Code</label>
                <input
                  v-model="form.supplier_code"
                  type="text"
                  placeholder="Supplier's product code"
                  class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lead Time (Days)</label>
                <input
                  v-model="form.lead_time_days"
                  type="number"
                  min="0"
                  placeholder="e.g., 7"
                  class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                />
              </div>
            </div>
          </div>

          <!-- Stock Management -->
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Stock Management (Optional)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Stock Level</label>
                <input
                  v-model="form.minimum_stock"
                  type="number"
                  step="0.01"
                  min="0"
                  placeholder="0.00"
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
                  placeholder="0.00"
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
            <span v-if="form.processing">Creating...</span>
            <span v-else>Create Material</span>
          </button>
        </div>
      </form>
    </div>
  </CMSLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import CMSLayout from '@/Layouts/CMSLayout.vue'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  categories: Array,
})

const form = useForm({
  code: '',
  name: '',
  description: '',
  category_id: '',
  unit: '',
  current_price: '',
  supplier: '',
  supplier_code: '',
  lead_time_days: '',
  minimum_stock: '',
  reorder_level: '',
  is_active: true,
})

const submit = () => {
  form.post(route('cms.materials.store'), {
    onSuccess: () => {
      // Redirect handled by controller
    },
  })
}
</script>
