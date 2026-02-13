<template>
  <form @submit.prevent="submit" class="space-y-6">
    <!-- Customer Selection -->
    <FormSelect
      v-model="form.customer_id"
      label="Customer"
      :options="customers.map(c => ({ 
        value: c.id, 
        label: `${c.name} (${formatCurrency(c.outstanding_balance)} outstanding)` 
      }))"
      placeholder="Select customer..."
      required
      :error="form.errors.customer_id"
    />

    <!-- Due Date -->
    <FormInput
      v-model="form.due_date"
      label="Due Date"
      type="date"
      :error="form.errors.due_date"
    />

    <!-- Invoice Items -->
    <div class="border-t pt-4">
      <div class="flex items-center justify-between mb-4">
        <h4 class="text-sm font-medium text-gray-900">Invoice Items</h4>
        <button
          type="button"
          @click="addItem"
          class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700"
        >
          <PlusIcon class="h-4 w-4" aria-hidden="true" />
          Add Item
        </button>
      </div>

      <div class="space-y-4">
        <div v-for="(item, index) in form.items" :key="index" class="border-2 border-gray-300 rounded-lg p-4">
          <div class="flex justify-between items-start mb-3">
            <span class="text-sm font-medium text-gray-700">Item {{ index + 1 }}</span>
            <button
              v-if="form.items.length > 1"
              type="button"
              @click="removeItem(index)"
              class="text-red-600 hover:text-red-800"
            >
              <TrashIcon class="h-5 w-5" aria-hidden="true" />
            </button>
          </div>

          <div class="space-y-3">
            <input
              v-model="item.description"
              type="text"
              placeholder="Description"
              required
              class="block w-full rounded-lg border-2 border-gray-400 px-4 py-2.5 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 text-gray-900"
            />
            
            <div class="grid grid-cols-3 gap-3">
              <input
                v-model.number="item.quantity"
                type="number"
                step="0.01"
                min="0.01"
                placeholder="Qty"
                required
                class="block w-full rounded-lg border-2 border-gray-400 px-4 py-2.5 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 text-gray-900"
              />
              
              <input
                v-model.number="item.unit_price"
                type="number"
                step="0.01"
                min="0"
                placeholder="Price"
                required
                class="block w-full rounded-lg border-2 border-gray-400 px-4 py-2.5 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 text-gray-900"
              />
              
              <div class="flex items-center justify-end">
                <span class="text-sm font-medium text-gray-900">
                  {{ formatCurrency(calculateLineTotal(item)) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-6 border-t pt-4">
        <div class="flex justify-end">
          <div class="w-64">
            <div class="flex justify-between text-lg font-bold">
              <span>Total:</span>
              <span>{{ formatCurrency(calculateSubtotal()) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Notes -->
    <FormInput
      v-model="form.notes"
      label="Notes"
      type="textarea"
      :rows="3"
      placeholder="Add any notes or special instructions..."
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
        {{ form.processing ? 'Creating...' : 'Create Invoice' }}
      </button>
    </div>
  </form>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { PlusIcon, TrashIcon } from '@heroicons/vue/24/outline'
import FormInput from '@/components/CMS/FormInput.vue'
import FormSelect from '@/components/CMS/FormSelect.vue'

interface Customer {
  id: number
  name: string
  email: string
  phone: string
  outstanding_balance: number
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
  customer_id: null as number | null,
  due_date: '',
  notes: '',
  items: [
    { description: '', quantity: 1, unit_price: 0 },
  ],
})

const addItem = () => {
  form.items.push({ description: '', quantity: 1, unit_price: 0 })
}

const removeItem = (index: number) => {
  if (form.items.length > 1) {
    form.items.splice(index, 1)
  }
}

const calculateLineTotal = (item: any) => {
  return item.quantity * item.unit_price
}

const calculateSubtotal = () => {
  return form.items.reduce((sum, item) => sum + calculateLineTotal(item), 0)
}

const formatCurrency = (amount: number) => {
  return `K${amount.toLocaleString('en-US', { minimumFractionDigits: 2 })}`
}

const submit = () => {
  form.post(route('cms.invoices.store'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      emit('success')
    },
  })
}
</script>
