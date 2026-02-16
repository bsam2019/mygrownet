<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
import CMSLayout from '@/Layouts/CMSLayout.vue'
import FormInput from '@/components/CMS/FormInput.vue'
import FormSelect from '@/components/CMS/FormSelect.vue'
import FormSection from '@/components/CMS/FormSection.vue'

defineOptions({
  layout: CMSLayout
})

const form = useForm({
  name: '',
  description: '',
  category: '',
  serial_number: '',
  model: '',
  manufacturer: '',
  purchase_date: '',
  purchase_cost: 0,
  condition: 'good',
  location: '',
  warranty_months: 0,
  notes: '',
})

const conditions = [
  { value: 'excellent', label: 'Excellent' },
  { value: 'good', label: 'Good' },
  { value: 'fair', label: 'Fair' },
  { value: 'poor', label: 'Poor' },
]

const submit = () => {
  form.post(route('cms.assets.store'))
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <button
          @click="$inertia.visit(route('cms.assets.index'))"
          class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-3 transition-colors"
        >
          <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
          Back to Assets
        </button>
        <h1 class="text-2xl font-bold text-gray-900">Add Asset</h1>
        <p class="mt-1 text-sm text-gray-500">Register a new company asset or equipment</p>
      </div>

      <form @submit.prevent="submit" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <div class="p-6 sm:p-8 space-y-8">
          <!-- Basic Information -->
          <FormSection
            title="Basic Information"
            description="Essential details about the asset"
            :divider="false"
          >
            <div class="sm:col-span-2">
              <FormInput
                v-model="form.name"
                label="Asset Name"
                placeholder="e.g., Dell Laptop, Toyota Hilux, Concrete Mixer"
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
                placeholder="Detailed description of the asset..."
                :error="form.errors.description"
              />
            </div>

            <FormInput
              v-model="form.category"
              label="Category"
              placeholder="e.g., Computer, Vehicle, Equipment"
              required
              :error="form.errors.category"
              help-text="Type of asset for organization"
            />

            <FormInput
              v-model="form.location"
              label="Location"
              placeholder="e.g., Office, Warehouse, Workshop"
              :error="form.errors.location"
              help-text="Where the asset is stored"
            />
          </FormSection>

          <!-- Asset Details -->
          <FormSection
            title="Asset Details"
            description="Identification and specifications"
          >
            <FormInput
              v-model="form.serial_number"
              label="Serial Number"
              placeholder="Enter serial number"
              :error="form.errors.serial_number"
            />

            <FormInput
              v-model="form.model"
              label="Model"
              placeholder="e.g., Latitude 5420, Hilux 2.8 GD-6"
              :error="form.errors.model"
            />

            <div class="sm:col-span-2">
              <FormInput
                v-model="form.manufacturer"
                label="Manufacturer"
                placeholder="e.g., Dell, Toyota, Caterpillar"
                :error="form.errors.manufacturer"
              />
            </div>
          </FormSection>

          <!-- Purchase Information -->
          <FormSection
            title="Purchase Information"
            description="Financial and acquisition details"
          >
            <FormInput
              v-model="form.purchase_date"
              label="Purchase Date"
              type="date"
              :error="form.errors.purchase_date"
            />

            <FormInput
              v-model.number="form.purchase_cost"
              label="Purchase Cost (K)"
              type="number"
              step="0.01"
              min="0"
              placeholder="0.00"
              required
              :error="form.errors.purchase_cost"
            />

            <FormSelect
              v-model="form.condition"
              label="Current Condition"
              :options="conditions"
              required
              :error="form.errors.condition"
            />

            <FormInput
              v-model.number="form.warranty_months"
              label="Warranty Period (Months)"
              type="number"
              min="0"
              placeholder="0"
              :error="form.errors.warranty_months"
              help-text="Leave as 0 if no warranty"
            />
          </FormSection>

          <!-- Additional Notes -->
          <FormSection
            title="Additional Notes"
            description="Any other relevant information"
          >
            <div class="sm:col-span-2">
              <FormInput
                v-model="form.notes"
                label="Notes"
                type="textarea"
                :rows="4"
                placeholder="Add any additional notes about this asset..."
                :error="form.errors.notes"
              />
            </div>
          </FormSection>
        </div>

        <!-- Form Actions -->
        <div class="bg-gray-50 px-6 sm:px-8 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
          <button
            type="button"
            @click="$inertia.visit(route('cms.assets.index'))"
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
            <span v-else>Create Asset</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
