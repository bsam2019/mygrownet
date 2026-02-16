<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
import CMSLayout from '@/Layouts/CMSLayout.vue'
import FormInput from '@/components/CMS/FormInput.vue'
import FormSection from '@/components/CMS/FormSection.vue'

defineOptions({
  layout: CMSLayout
})

const form = useForm({
  name: '',
  phone: '',
  email: '',
  address: '',
  credit_limit: '',
  notes: '',
})

const submit = () => {
  form.post(route('cms.customers.store'))
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <button
          @click="$inertia.visit(route('cms.customers.index'))"
          class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-3 transition-colors"
        >
          <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
          Back to Customers
        </button>
        <h1 class="text-2xl font-bold text-gray-900">Add New Customer</h1>
        <p class="mt-1 text-sm text-gray-500">Create a new customer record</p>
      </div>

      <form @submit.prevent="submit" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <div class="p-6 sm:p-8 space-y-8">
          <!-- Basic Information -->
          <FormSection
            title="Basic Information"
            description="Customer name and contact details"
            :divider="false"
          >
            <div class="sm:col-span-2">
              <FormInput
                v-model="form.name"
                label="Customer Name"
                placeholder="Full name or business name"
                required
                :error="form.errors.name"
              />
            </div>

            <FormInput
              v-model="form.phone"
              label="Phone"
              type="tel"
              placeholder="+260 XXX XXX XXX"
              required
              :error="form.errors.phone"
            />

            <FormInput
              v-model="form.email"
              label="Email"
              type="email"
              placeholder="email@example.com"
              :error="form.errors.email"
              help-text="Optional - for sending invoices"
            />

            <div class="sm:col-span-2">
              <FormInput
                v-model="form.address"
                label="Address"
                type="textarea"
                :rows="3"
                placeholder="Physical address..."
                :error="form.errors.address"
              />
            </div>
          </FormSection>

          <!-- Credit Settings -->
          <FormSection
            title="Credit Settings"
            description="Credit limit and payment terms"
          >
            <div class="sm:col-span-2">
              <FormInput
                v-model.number="form.credit_limit"
                label="Credit Limit (K)"
                type="number"
                step="0.01"
                min="0"
                placeholder="0.00"
                :error="form.errors.credit_limit"
                help-text="Maximum amount customer can owe before requiring payment"
              />
            </div>
          </FormSection>

          <!-- Additional Notes -->
          <FormSection
            title="Additional Notes"
            description="Internal notes about this customer"
          >
            <div class="sm:col-span-2">
              <FormInput
                v-model="form.notes"
                label="Internal Notes"
                type="textarea"
                :rows="4"
                placeholder="Add any internal notes about this customer..."
                :error="form.errors.notes"
              />
            </div>
          </FormSection>
        </div>

        <!-- Form Actions -->
        <div class="bg-gray-50 px-6 sm:px-8 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
          <button
            type="button"
            @click="$inertia.visit(route('cms.customers.index'))"
            class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <span v-if="form.processing">Adding...</span>
            <span v-else>Add Customer</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
