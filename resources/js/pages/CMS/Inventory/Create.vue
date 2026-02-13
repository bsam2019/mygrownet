<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
import CMSLayoutNew from '@/Layouts/CMSLayoutNew.vue'
import FormInput from '@/components/CMS/FormInput.vue'
import FormSelect from '@/components/CMS/FormSelect.vue'
import FormSection from '@/components/CMS/FormSection.vue'

defineOptions({
  layout: CMSLayoutNew
})

const form = useForm({
  name: '',
  description: '',
  category: '',
  unit: 'pieces',
  unit_cost: 0,
  selling_price: 0,
  current_stock: 0,
  minimum_stock: 0,
  reorder_quantity: 0,
  supplier: '',
  location: '',
})

const unitOptions = [
  { value: 'pieces', label: 'Pieces' },
  { value: 'kg', label: 'Kilograms' },
  { value: 'liters', label: 'Liters' },
  { value: 'meters', label: 'Meters' },
  { value: 'boxes', label: 'Boxes' },
  { value: 'reams', label: 'Reams' },
]

const submit = () => {
  form.post(route('cms.inventory.store'))
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <button
          @click="$inertia.visit(route('cms.inventory.index'))"
          class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-3 transition-colors"
        >
          <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
          Back to Inventory
        </button>
        <h1 class="text-2xl font-bold text-gray-900">Add Inventory Item</h1>
        <p class="mt-1 text-sm text-gray-500">Create a new inventory item for tracking</p>
      </div>

      <form @submit.prevent="submit" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <div class="p-6 sm:p-8 space-y-8">
          <!-- Basic Information -->
          <FormSection
            title="Basic Information"
            description="Item name and description"
            :divider="false"
          >
            <div class="sm:col-span-2">
              <FormInput
                v-model="form.name"
                label="Item Name"
                placeholder="Enter item name"
                required
                :error="form.errors.name"
              />
            </div>

            <div class="sm:col-span-2">
              <FormInput
                v-model="form.description"
                label="Description"
                type="textarea"
                :rows="3"
                placeholder="Describe the item..."
                :error="form.errors.description"
              />
            </div>
          </FormSection>

          <!-- Item Details -->
          <FormSection
            title="Item Details"
            description="Category and unit of measurement"
          >
            <FormInput
              v-model="form.category"
              label="Category"
              placeholder="e.g., Paper, Ink, Equipment"
              required
              :error="form.errors.category"
            />

            <FormSelect
              v-model="form.unit"
              label="Unit"
              :options="unitOptions"
              required
              :error="form.errors.unit"
            />
          </FormSection>

          <!-- Pricing -->
          <FormSection
            title="Pricing"
            description="Cost and selling price"
          >
            <FormInput
              v-model.number="form.unit_cost"
              label="Unit Cost (K)"
              type="number"
              step="0.01"
              min="0"
              placeholder="0.00"
              required
              :error="form.errors.unit_cost"
              help-text="Cost per unit"
            />

            <FormInput
              v-model.number="form.selling_price"
              label="Selling Price (K)"
              type="number"
              step="0.01"
              min="0"
              placeholder="0.00"
              :error="form.errors.selling_price"
              help-text="Optional - for items sold directly"
            />
          </FormSection>

          <!-- Stock Management -->
          <FormSection
            title="Stock Management"
            description="Initial stock and reorder settings"
          >
            <FormInput
              v-model.number="form.current_stock"
              label="Current Stock"
              type="number"
              min="0"
              placeholder="0"
              required
              :error="form.errors.current_stock"
              help-text="Initial stock quantity"
            />

            <FormInput
              v-model.number="form.minimum_stock"
              label="Minimum Stock"
              type="number"
              min="0"
              placeholder="0"
              required
              :error="form.errors.minimum_stock"
              help-text="Alert when stock falls below this level"
            />

            <FormInput
              v-model.number="form.reorder_quantity"
              label="Reorder Quantity"
              type="number"
              min="0"
              placeholder="0"
              :error="form.errors.reorder_quantity"
              help-text="Suggested quantity to reorder"
            />

            <FormInput
              v-model="form.supplier"
              label="Supplier"
              placeholder="Supplier name"
              :error="form.errors.supplier"
              help-text="Primary supplier for this item"
            />

            <div class="sm:col-span-2">
              <FormInput
                v-model="form.location"
                label="Storage Location"
                placeholder="e.g., Warehouse A, Shelf 3"
                :error="form.errors.location"
                help-text="Where this item is stored"
              />
            </div>
          </FormSection>
        </div>

        <!-- Form Actions -->
        <div class="bg-gray-50 px-6 sm:px-8 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
          <button
            type="button"
            @click="$inertia.visit(route('cms.inventory.index'))"
            class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <span v-if="form.processing">Creating...</span>
            <span v-else>Create Item</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
