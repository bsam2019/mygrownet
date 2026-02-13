<template>
  <form @submit.prevent="submit" class="space-y-6">
    <!-- Customer Selection -->
    <FormSelect
      v-model="form.customer_id"
      label="Customer"
      :options="customers.map(c => ({ value: c.id, label: `${c.customer_number} - ${c.name}` }))"
      placeholder="Select a customer"
      required
      :error="form.errors.customer_id"
    />

    <!-- Job Type -->
    <FormInput
      v-model="form.job_type"
      label="Job Type"
      placeholder="e.g., Business Cards, Banner Printing, T-Shirt Printing"
      required
      :error="form.errors.job_type"
    />

    <!-- Description -->
    <FormInput
      v-model="form.description"
      label="Description"
      type="textarea"
      :rows="4"
      placeholder="Detailed job description, specifications, requirements..."
      :error="form.errors.description"
    />

    <!-- Quoted Value -->
    <FormInput
      v-model="form.quoted_value"
      label="Quoted Value (K)"
      type="number"
      step="0.01"
      min="0"
      placeholder="0.00"
      :error="form.errors.quoted_value"
    />

    <!-- Priority & Deadline -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <FormSelect
        v-model="form.priority"
        label="Priority"
        :options="[
          { value: 'low', label: 'Low' },
          { value: 'normal', label: 'Normal' },
          { value: 'high', label: 'High' },
          { value: 'urgent', label: 'Urgent' }
        ]"
      />

      <FormInput
        v-model="form.deadline"
        label="Deadline"
        type="date"
        :error="form.errors.deadline"
      />
    </div>

    <!-- Notes -->
    <FormInput
      v-model="form.notes"
      label="Internal Notes"
      type="textarea"
      :rows="3"
      placeholder="Internal notes, special instructions..."
    />

    <!-- Actions -->
    <div class="flex justify-end gap-3 pt-4 border-t">
      <button
        type="button"
        @click="$emit('cancel')"
        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium"
      >
        Cancel
      </button>
      <button
        type="submit"
        :disabled="form.processing"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 font-medium"
      >
        {{ form.processing ? 'Creating...' : 'Create Job' }}
      </button>
    </div>
  </form>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import FormInput from '@/components/CMS/FormInput.vue'
import FormSelect from '@/components/CMS/FormSelect.vue'

interface Customer {
  id: number
  customer_number: string
  name: string
}

interface Props {
  customers: Customer[]
}

defineProps<Props>()

const emit = defineEmits<{
  (e: 'cancel'): void
  (e: 'success'): void
}>()

const form = useForm({
  customer_id: '',
  job_type: '',
  description: '',
  quoted_value: '',
  priority: 'normal',
  deadline: '',
  notes: '',
})

const submit = () => {
  form.post(route('cms.jobs.store'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      emit('success')
    },
  })
}
</script>
