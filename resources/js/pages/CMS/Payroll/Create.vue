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
  period_type: 'weekly',
  period_start: '',
  period_end: '',
  notes: '',
})

const periodTypeOptions = [
  { value: 'weekly', label: 'Weekly' },
  { value: 'bi-weekly', label: 'Bi-Weekly' },
  { value: 'monthly', label: 'Monthly' },
]

const submit = () => {
  form.post(route('cms.payroll.store'))
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <button
          @click="$inertia.visit(route('cms.payroll.index'))"
          class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-3 transition-colors"
        >
          <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
          Back to Payroll
        </button>
        <h1 class="text-2xl font-bold text-gray-900">Create Payroll Run</h1>
        <p class="mt-1 text-sm text-gray-500">Generate a new payroll run for approved attendance and commissions</p>
      </div>

      <form @submit.prevent="submit" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <div class="p-6 sm:p-8 space-y-8">
          <!-- Period Settings -->
          <FormSection
            title="Period Settings"
            description="Define the payroll period"
            :divider="false"
          >
            <div class="sm:col-span-2">
              <FormSelect
                v-model="form.period_type"
                label="Period Type"
                :options="periodTypeOptions"
                required
                :error="form.errors.period_type"
                help-text="Select the payroll period frequency"
              />
            </div>

            <FormInput
              v-model="form.period_start"
              label="Period Start Date"
              type="date"
              required
              :error="form.errors.period_start"
            />

            <FormInput
              v-model="form.period_end"
              label="Period End Date"
              type="date"
              required
              :error="form.errors.period_end"
            />
          </FormSection>

          <!-- Additional Notes -->
          <FormSection
            title="Additional Notes"
            description="Optional notes about this payroll run"
          >
            <div class="sm:col-span-2">
              <FormInput
                v-model="form.notes"
                label="Notes"
                type="textarea"
                :rows="3"
                placeholder="Optional notes about this payroll run..."
                :error="form.errors.notes"
              />
            </div>
          </FormSection>

          <!-- Information Box -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h4 class="text-sm font-medium text-blue-900 mb-2">What happens when you create a payroll run?</h4>
            <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
              <li>All approved attendance records in the period will be included</li>
              <li>All approved commissions in the period will be included</li>
              <li>Payroll items will be automatically generated for each worker/staff member</li>
              <li>The payroll will be created in "Draft" status for review</li>
              <li>You can approve and mark as paid after reviewing</li>
            </ul>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="bg-gray-50 px-6 sm:px-8 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
          <button
            type="button"
            @click="$inertia.visit(route('cms.payroll.index'))"
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
            <span v-else>Create Payroll Run</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
