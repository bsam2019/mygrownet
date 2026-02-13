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
  phone: '',
  email: '',
  id_number: '',
  worker_type: 'casual',
  hourly_rate: 0,
  daily_rate: 0,
  commission_rate: 0,
  payment_method: 'mobile_money',
  mobile_money_number: '',
  bank_name: '',
  bank_account_number: '',
  notes: '',
})

const workerTypes = [
  { value: 'casual', label: 'Casual' },
  { value: 'contract', label: 'Contract' },
  { value: 'permanent', label: 'Permanent' },
]

const paymentMethods = [
  { value: 'cash', label: 'Cash' },
  { value: 'mobile_money', label: 'Mobile Money' },
  { value: 'bank_transfer', label: 'Bank Transfer' },
]

const submit = () => {
  form.post(route('cms.payroll.workers.store'))
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <button
          @click="$inertia.visit(route('cms.payroll.workers.index'))"
          class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-3 transition-colors"
        >
          <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
          Back to Workers
        </button>
        <h1 class="text-2xl font-bold text-gray-900">Add Worker</h1>
        <p class="mt-1 text-sm text-gray-500">Register a new worker or contractor to your team</p>
      </div>

      <form @submit.prevent="submit" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <div class="p-6 sm:p-8 space-y-8">
          <!-- Personal Information -->
          <FormSection
            title="Personal Information"
            description="Basic details about the worker"
            :divider="false"
          >
            <div class="sm:col-span-2">
              <FormInput
                v-model="form.name"
                label="Full Name"
                placeholder="Enter worker's full name"
                required
                :error="form.errors.name"
              />
            </div>

            <FormInput
              v-model="form.phone"
              label="Phone Number"
              type="tel"
              placeholder="+260 XXX XXX XXX"
              required
              :error="form.errors.phone"
            />

            <FormInput
              v-model="form.email"
              label="Email Address"
              type="email"
              placeholder="worker@example.com"
              :error="form.errors.email"
              help-text="Optional - for sending notifications"
            />

            <div class="sm:col-span-2">
              <FormInput
                v-model="form.id_number"
                label="ID/NRC Number"
                placeholder="123456/78/9"
                :error="form.errors.id_number"
                help-text="National Registration Card or ID number"
              />
            </div>
          </FormSection>

          <!-- Employment Details -->
          <FormSection
            title="Employment Details"
            description="Worker type and compensation rates"
          >
            <FormSelect
              v-model="form.worker_type"
              label="Worker Type"
              :options="workerTypes"
              required
              :error="form.errors.worker_type"
            />

            <FormInput
              v-model.number="form.hourly_rate"
              label="Hourly Rate (K)"
              type="number"
              step="0.01"
              min="0"
              placeholder="0.00"
              :error="form.errors.hourly_rate"
              help-text="Leave as 0 if not applicable"
            />

            <FormInput
              v-model.number="form.daily_rate"
              label="Daily Rate (K)"
              type="number"
              step="0.01"
              min="0"
              placeholder="0.00"
              :error="form.errors.daily_rate"
              help-text="Leave as 0 if not applicable"
            />

            <FormInput
              v-model.number="form.commission_rate"
              label="Commission Rate (%)"
              type="number"
              step="0.01"
              min="0"
              max="100"
              placeholder="0.00"
              :error="form.errors.commission_rate"
              help-text="Percentage for commission-based work"
            />
          </FormSection>

          <!-- Payment Information -->
          <FormSection
            title="Payment Information"
            description="How the worker will receive payments"
          >
            <div class="sm:col-span-2">
              <FormSelect
                v-model="form.payment_method"
                label="Payment Method"
                :options="paymentMethods"
                required
                :error="form.errors.payment_method"
              />
            </div>

            <div v-if="form.payment_method === 'mobile_money'" class="sm:col-span-2">
              <FormInput
                v-model="form.mobile_money_number"
                label="Mobile Money Number"
                type="tel"
                placeholder="+260 XXX XXX XXX"
                :error="form.errors.mobile_money_number"
              />
            </div>

            <template v-if="form.payment_method === 'bank_transfer'">
              <FormInput
                v-model="form.bank_name"
                label="Bank Name"
                placeholder="e.g., Zanaco, FNB, Stanbic"
                :error="form.errors.bank_name"
              />

              <FormInput
                v-model="form.bank_account_number"
                label="Account Number"
                placeholder="Enter account number"
                :error="form.errors.bank_account_number"
              />
            </template>
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
                placeholder="Add any additional notes about this worker..."
                :error="form.errors.notes"
              />
            </div>
          </FormSection>
        </div>

        <!-- Form Actions -->
        <div class="bg-gray-50 px-6 sm:px-8 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
          <button
            type="button"
            @click="$inertia.visit(route('cms.payroll.workers.index'))"
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
            <span v-else>Create Worker</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
