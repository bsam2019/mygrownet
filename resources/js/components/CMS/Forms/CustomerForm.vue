<template>
  <form @submit.prevent="submit" class="space-y-6">
    <!-- Name -->
    <FormInput
      v-model="form.name"
      label="Customer Name"
      placeholder="Full name or business name"
      required
      :error="form.errors.name"
    />

    <!-- Contact Info -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <FormInput
        v-model="form.phone"
        label="Phone"
        type="tel"
        placeholder="+260..."
        required
        :error="form.errors.phone"
      />

      <FormInput
        v-model="form.email"
        label="Email"
        type="email"
        placeholder="email@example.com"
        :error="form.errors.email"
      />
    </div>

    <!-- Address -->
    <FormInput
      v-model="form.address"
      label="Address"
      type="textarea"
      :rows="3"
      placeholder="Physical address..."
    />

    <!-- Credit Limit -->
    <FormInput
      v-model="form.credit_limit"
      label="Credit Limit (K)"
      type="number"
      step="0.01"
      min="0"
      placeholder="0.00"
      help-text="Maximum amount customer can owe before requiring payment"
    />

    <!-- Notes -->
    <FormInput
      v-model="form.notes"
      label="Internal Notes"
      type="textarea"
      :rows="3"
      placeholder="Internal notes about this customer..."
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
        {{ form.processing ? 'Adding...' : 'Add Customer' }}
      </button>
    </div>
  </form>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import FormInput from '@/components/CMS/FormInput.vue'

const emit = defineEmits<{
  (e: 'cancel'): void
  (e: 'success'): void
}>()

const form = useForm({
  name: '',
  phone: '',
  email: '',
  address: '',
  credit_limit: '',
  notes: '',
})

const submit = () => {
  form.post(route('cms.customers.store'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      emit('success')
    },
  })
}
</script>
