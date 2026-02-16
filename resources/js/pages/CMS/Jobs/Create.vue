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

interface Props {
  customers: any[]
}

defineProps<Props>()

const form = useForm({
  customer_id: '',
  job_type: '',
  description: '',
  quoted_value: '',
  priority: 'normal',
  deadline: '',
  notes: '',
})

const priorityOptions = [
  { value: 'low', label: 'Low' },
  { value: 'normal', label: 'Normal' },
  { value: 'high', label: 'High' },
  { value: 'urgent', label: 'Urgent' },
]

const submit = () => {
  form.post(route('cms.jobs.store'))
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <button
          @click="$inertia.visit(route('cms.jobs.index'))"
          class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-3 transition-colors"
        >
          <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
          Back to Jobs
        </button>
        <h1 class="text-2xl font-bold text-gray-900">Create New Job</h1>
        <p class="mt-1 text-sm text-gray-500">Create a new job for a customer</p>
      </div>

      <form @submit.prevent="submit" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <div class="p-6 sm:p-8 space-y-8">
          <!-- Customer Selection -->
          <FormSection
            title="Customer"
            description="Select the customer for this job"
            :divider="false"
          >
            <div class="sm:col-span-2">
              <FormSelect
                v-model="form.customer_id"
                label="Customer"
                :options="customers.map(c => ({ value: c.id, label: `${c.customer_number} - ${c.name}` }))"
                placeholder="Select a customer"
                required
                :error="form.errors.customer_id"
              />
            </div>
          </FormSection>

          <!-- Job Details -->
          <FormSection
            title="Job Details"
            description="Type and description of the job"
          >
            <div class="sm:col-span-2">
              <FormInput
                v-model="form.job_type"
                label="Job Type"
                placeholder="e.g., Business Cards, Banner Printing, T-Shirt Printing"
                required
                :error="form.errors.job_type"
              />
            </div>

            <div class="sm:col-span-2">
              <FormInput
                v-model="form.description"
                label="Description"
                type="textarea"
                :rows="4"
                placeholder="Detailed job description, specifications, requirements..."
                :error="form.errors.description"
              />
            </div>
          </FormSection>

          <!-- Pricing & Schedule -->
          <FormSection
            title="Pricing & Schedule"
            description="Quote value, priority, and deadline"
          >
            <FormInput
              v-model.number="form.quoted_value"
              label="Quoted Value (K)"
              type="number"
              step="0.01"
              min="0"
              placeholder="0.00"
              :error="form.errors.quoted_value"
              help-text="Estimated cost for this job"
            />

            <FormSelect
              v-model="form.priority"
              label="Priority"
              :options="priorityOptions"
              :error="form.errors.priority"
            />

            <div class="sm:col-span-2">
              <FormInput
                v-model="form.deadline"
                label="Deadline"
                type="date"
                :error="form.errors.deadline"
                help-text="Expected completion date"
              />
            </div>
          </FormSection>

          <!-- Additional Notes -->
          <FormSection
            title="Additional Notes"
            description="Internal notes and special instructions"
          >
            <div class="sm:col-span-2">
              <FormInput
                v-model="form.notes"
                label="Internal Notes"
                type="textarea"
                :rows="3"
                placeholder="Internal notes, special instructions..."
                :error="form.errors.notes"
              />
            </div>
          </FormSection>
        </div>

        <!-- Form Actions -->
        <div class="bg-gray-50 px-6 sm:px-8 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
          <button
            type="button"
            @click="$inertia.visit(route('cms.jobs.index'))"
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
            <span v-else>Create Job</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
