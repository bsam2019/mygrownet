<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
import CMSLayout from '@/Layouts/CMSLayout.vue'
import FormInput from '@/components/CMS/FormInput.vue'
import FormSelect from '@/components/CMS/FormSelect.vue'
import FormSection from '@/components/CMS/FormSection.vue'

defineOptions({
  layout: CMSLayout
})

interface Customer {
  id: number
  name: string
  email: string
  phone: string
  outstanding_balance: number
}

interface Invoice {
  id: number
  invoice_number: string
  invoice_date: string
  total_amount: number
  amount_paid: number
  balance_due: number
}

interface Props {
  customers: Customer[]
  customerInvoices: Invoice[]
  paymentMethods: Array<{ value: string; label: string }>
  preselectedCustomerId: number | null
}

const props = defineProps<Props>()

const form = useForm({
  customer_id: props.preselectedCustomerId || '',
  invoice_id: '',
  amount: 0,
  payment_method: 'cash',
  payment_date: new Date().toISOString().split('T')[0],
  reference_number: '',
  notes: '',
})

const selectedCustomer = computed(() => {
  if (!form.customer_id) return null
  return props.customers.find(c => c.id === Number(form.customer_id))
})

const availableInvoices = ref<Invoice[]>(props.customerInvoices)

// Watch for customer changes to load their invoices
watch(() => form.customer_id, async (newCustomerId) => {
  if (!newCustomerId) {
    availableInvoices.value = []
    form.invoice_id = ''
    return
  }

  // Fetch invoices for selected customer
  try {
    const response = await fetch(route('cms.payments.create', { customer_id: newCustomerId }))
    const data = await response.json()
    availableInvoices.value = data.props.customerInvoices || []
  } catch (error) {
    console.error('Failed to load customer invoices:', error)
  }
})

const selectedInvoice = computed(() => {
  if (!form.invoice_id) return null
  return availableInvoices.value.find(inv => inv.id === Number(form.invoice_id))
})

// Auto-fill amount when invoice is selected
watch(() => form.invoice_id, (newInvoiceId) => {
  if (newInvoiceId && selectedInvoice.value) {
    form.amount = selectedInvoice.value.balance_due
  }
})

const customerOptions = computed(() => {
  return props.customers.map(c => ({
    value: c.id,
    label: `${c.name} ${c.outstanding_balance > 0 ? `(K${c.outstanding_balance.toFixed(2)} due)` : ''}`
  }))
})

const invoiceOptions = computed(() => {
  return availableInvoices.value.map(inv => ({
    value: inv.id,
    label: `${inv.invoice_number} - K${inv.balance_due.toFixed(2)} due`
  }))
})

const submit = () => {
  form.post(route('cms.payments.store'))
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <button
          @click="$inertia.visit(route('cms.payments.index'))"
          class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-3 transition-colors"
        >
          <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
          Back to Payments
        </button>
        <h1 class="text-2xl font-bold text-gray-900">Record Payment</h1>
        <p class="mt-1 text-sm text-gray-500">Record a payment received from a customer</p>
      </div>

      <form @submit.prevent="submit" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <div class="p-6 sm:p-8 space-y-8">
          <!-- Customer Selection -->
          <FormSection
            title="Customer & Invoice"
            description="Select the customer and optionally link to an invoice"
            :divider="false"
          >
            <div class="sm:col-span-2">
              <FormSelect
                v-model="form.customer_id"
                label="Customer"
                :options="customerOptions"
                placeholder="Select a customer"
                required
                :error="form.errors.customer_id"
              />
            </div>

            <div v-if="selectedCustomer" class="sm:col-span-2">
              <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm font-medium text-blue-900">{{ selectedCustomer.name }}</p>
                    <p class="text-xs text-blue-700 mt-1">
                      Outstanding Balance: K{{ selectedCustomer.outstanding_balance.toFixed(2) }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="availableInvoices.length > 0" class="sm:col-span-2">
              <FormSelect
                v-model="form.invoice_id"
                label="Invoice (Optional)"
                :options="invoiceOptions"
                placeholder="Select an invoice or leave blank for general payment"
                :error="form.errors.invoice_id"
                help-text="Link this payment to a specific invoice"
              />
            </div>

            <div v-if="selectedInvoice" class="sm:col-span-2">
              <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                <div class="grid grid-cols-3 gap-4 text-sm">
                  <div>
                    <p class="text-gray-500">Invoice Total</p>
                    <p class="font-medium text-gray-900">K{{ selectedInvoice.total_amount.toFixed(2) }}</p>
                  </div>
                  <div>
                    <p class="text-gray-500">Paid</p>
                    <p class="font-medium text-gray-900">K{{ selectedInvoice.amount_paid.toFixed(2) }}</p>
                  </div>
                  <div>
                    <p class="text-gray-500">Balance Due</p>
                    <p class="font-medium text-green-600">K{{ selectedInvoice.balance_due.toFixed(2) }}</p>
                  </div>
                </div>
              </div>
            </div>
          </FormSection>

          <!-- Payment Details -->
          <FormSection
            title="Payment Details"
            description="Enter the payment information"
          >
            <FormInput
              v-model.number="form.amount"
              label="Amount"
              type="number"
              step="0.01"
              min="0"
              placeholder="0.00"
              required
              :error="form.errors.amount"
              help-text="Amount received from customer"
            />

            <FormInput
              v-model="form.payment_date"
              label="Payment Date"
              type="date"
              required
              :error="form.errors.payment_date"
            />

            <FormSelect
              v-model="form.payment_method"
              label="Payment Method"
              :options="paymentMethods"
              required
              :error="form.errors.payment_method"
            />

            <FormInput
              v-model="form.reference_number"
              label="Reference Number"
              placeholder="e.g., Cheque number, transaction ID"
              :error="form.errors.reference_number"
              help-text="Optional reference for tracking"
            />
          </FormSection>

          <!-- Additional Notes -->
          <FormSection
            title="Additional Notes"
            description="Any additional information about this payment"
          >
            <div class="sm:col-span-2">
              <FormInput
                v-model="form.notes"
                label="Notes"
                type="textarea"
                :rows="4"
                placeholder="Add any additional notes about this payment..."
                :error="form.errors.notes"
              />
            </div>
          </FormSection>
        </div>

        <!-- Form Actions -->
        <div class="bg-gray-50 px-6 sm:px-8 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
          <button
            type="button"
            @click="$inertia.visit(route('cms.payments.index'))"
            class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <span v-if="form.processing">Recording...</span>
            <span v-else>Record Payment</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
