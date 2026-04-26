<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { ArrowLeftIcon, BanknotesIcon, DocumentTextIcon } from '@heroicons/vue/24/outline'
import CMSLayout from '@/Layouts/CMSLayout.vue'
import { toast } from '@/utils/bizboost-toast'

defineOptions({ layout: CMSLayout })

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
  customer_id: props.preselectedCustomerId ? String(props.preselectedCustomerId) : '',
  invoice_id: '',
  amount: '' as number | string,
  payment_method: 'cash',
  payment_date: new Date().toISOString().split('T')[0],
  reference_number: '',
  notes: '',
})

const availableInvoices = ref<Invoice[]>(props.customerInvoices)
const loadingInvoices = ref(false)

const selectedCustomer = computed(() =>
  props.customers.find(c => String(c.id) === String(form.customer_id)) ?? null
)

const selectedInvoice = computed(() =>
  availableInvoices.value.find(inv => String(inv.id) === String(form.invoice_id)) ?? null
)

// When customer changes, fetch their unpaid invoices
watch(() => form.customer_id, async (customerId) => {
  availableInvoices.value = []
  form.invoice_id = ''
  form.amount = ''

  if (!customerId) return

  loadingInvoices.value = true
  try {
    const res = await fetch(
      route('cms.payments.customer-invoices', { customer: customerId }),
      { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } }
    )
    if (res.ok) availableInvoices.value = await res.json()
  } catch (e) {
    console.error('Failed to load invoices', e)
  } finally {
    loadingInvoices.value = false
  }
})

// Auto-fill amount from invoice balance
watch(() => form.invoice_id, () => {
  if (selectedInvoice.value) {
    form.amount = selectedInvoice.value.balance_due
  }
})

const formatCurrency = (n: number) =>
  `K${Number(n).toLocaleString('en-US', { minimumFractionDigits: 2 })}`

const submit = () => {
  form.post(route('cms.payments.store'), {
    onSuccess: () => toast.success('Payment recorded', 'Payment has been recorded successfully'),
    onError: () => toast.error('Failed to record payment', 'Please check the form and try again'),
  })
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="max-w-3xl mx-auto">

      <!-- Header -->
      <div class="mb-6">
        <button
          @click="router.visit(route('cms.payments.index'))"
          class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-3 transition-colors"
        >
          <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
          Back to Payments
        </button>
        <h1 class="text-2xl font-bold text-gray-900">Record Payment</h1>
        <p class="mt-1 text-sm text-gray-500">Record a payment received from a customer</p>
      </div>

      <form @submit.prevent="submit" class="space-y-6">

        <!-- ── Customer & Invoice ─────────────────────────────── -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-5">
          <h2 class="text-base font-semibold text-gray-900">Customer & Invoice</h2>

          <!-- Customer select -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Customer <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.customer_id"
              required
              class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
            >
              <option value="">Select a customer…</option>
              <option v-for="c in customers" :key="c.id" :value="String(c.id)">
                {{ c.name }}{{ c.outstanding_balance > 0 ? ` — K${c.outstanding_balance.toFixed(2)} outstanding` : '' }}
              </option>
            </select>
            <p v-if="form.errors.customer_id" class="mt-1 text-xs text-red-600">{{ form.errors.customer_id }}</p>
          </div>

          <!-- Customer summary card -->
          <div v-if="selectedCustomer" class="flex items-center justify-between p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div>
              <p class="text-sm font-semibold text-blue-900">{{ selectedCustomer.name }}</p>
              <p v-if="selectedCustomer.email" class="text-xs text-blue-700 mt-0.5">{{ selectedCustomer.email }}</p>
            </div>
            <div class="text-right">
              <p class="text-xs text-blue-600">Outstanding balance</p>
              <p class="text-sm font-bold text-blue-900">{{ formatCurrency(selectedCustomer.outstanding_balance) }}</p>
            </div>
          </div>

          <!-- Invoice select -->
          <div v-if="selectedCustomer">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Link to Invoice
              <span class="text-gray-400 font-normal">(optional)</span>
            </label>

            <!-- Loading state -->
            <div v-if="loadingInvoices" class="flex items-center gap-2 text-sm text-gray-500 py-2">
              <svg class="animate-spin h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
              </svg>
              Loading invoices…
            </div>

            <!-- No unpaid invoices -->
            <div v-else-if="availableInvoices.length === 0" class="flex items-center gap-2 p-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-500">
              <DocumentTextIcon class="h-4 w-4 flex-shrink-0" aria-hidden="true" />
              No unpaid invoices for this customer
            </div>

            <!-- Invoice dropdown -->
            <select
              v-else
              v-model="form.invoice_id"
              class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
            >
              <option value="">— General payment (no invoice) —</option>
              <option v-for="inv in availableInvoices" :key="inv.id" :value="String(inv.id)">
                {{ inv.invoice_number }} · {{ new Date(inv.invoice_date).toLocaleDateString('en-GB') }} · {{ formatCurrency(inv.balance_due) }} due
              </option>
            </select>
          </div>

          <!-- Selected invoice breakdown -->
          <div v-if="selectedInvoice" class="grid grid-cols-3 gap-4 p-4 bg-gray-50 border border-gray-200 rounded-lg text-sm">
            <div>
              <p class="text-gray-500 text-xs">Invoice Total</p>
              <p class="font-semibold text-gray-900 mt-0.5">{{ formatCurrency(selectedInvoice.total_amount) }}</p>
            </div>
            <div>
              <p class="text-gray-500 text-xs">Already Paid</p>
              <p class="font-semibold text-gray-900 mt-0.5">{{ formatCurrency(selectedInvoice.amount_paid) }}</p>
            </div>
            <div>
              <p class="text-gray-500 text-xs">Balance Due</p>
              <p class="font-semibold text-green-600 mt-0.5">{{ formatCurrency(selectedInvoice.balance_due) }}</p>
            </div>
          </div>
        </div>

        <!-- ── Payment Details ────────────────────────────────── -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-5">
          <h2 class="text-base font-semibold text-gray-900">Payment Details</h2>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <!-- Amount -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Amount <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm font-medium">K</span>
                <input
                  v-model="form.amount"
                  type="number"
                  step="0.01"
                  min="0.01"
                  required
                  placeholder="0.00"
                  class="block w-full pl-7 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                />
              </div>
              <p v-if="form.errors.amount" class="mt-1 text-xs text-red-600">{{ form.errors.amount }}</p>
            </div>

            <!-- Payment Date -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Payment Date <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.payment_date"
                type="date"
                required
                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
              />
              <p v-if="form.errors.payment_date" class="mt-1 text-xs text-red-600">{{ form.errors.payment_date }}</p>
            </div>

            <!-- Payment Method -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Payment Method <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.payment_method"
                required
                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
              >
                <option v-for="m in paymentMethods" :key="m.value" :value="m.value">{{ m.label }}</option>
              </select>
              <p v-if="form.errors.payment_method" class="mt-1 text-xs text-red-600">{{ form.errors.payment_method }}</p>
            </div>

            <!-- Reference Number -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
              <input
                v-model="form.reference_number"
                type="text"
                placeholder="Cheque no., transaction ID…"
                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
              />
              <p v-if="form.errors.reference_number" class="mt-1 text-xs text-red-600">{{ form.errors.reference_number }}</p>
            </div>
          </div>

          <!-- Notes -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea
              v-model="form.notes"
              rows="3"
              placeholder="Any additional notes about this payment…"
              class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
            />
          </div>
        </div>

        <!-- ── Actions ─────────────────────────────────────────── -->
        <div class="flex items-center justify-end gap-3">
          <button
            type="button"
            @click="router.visit(route('cms.payments.index'))"
            class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="form.processing || !form.customer_id"
            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <BanknotesIcon class="h-4 w-4" aria-hidden="true" />
            <span v-if="form.processing">Recording…</span>
            <span v-else>Record Payment</span>
          </button>
        </div>

      </form>
    </div>
  </div>
</template>
