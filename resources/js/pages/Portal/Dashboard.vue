<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { DocumentTextIcon, CurrencyDollarIcon, ClockIcon, CheckCircleIcon } from '@heroicons/vue/24/outline'
import PortalLayout from '@/Layouts/PortalLayout.vue'

defineOptions({ layout: PortalLayout })

interface Stat {
  total_invoices: number
  paid_invoices: number
  overdue_invoices: number
  open_quotes: number
  outstanding: number
  total_contracts: number
  active_contracts: number
  pending_signatures: number
}

interface Invoice {
  id: number
  invoice_number: string
  invoice_date: string
  total_amount: number
  amount_due: number
  status: string
  company: { name: string }
}

interface Quote {
  id: number
  quotation_number: string
  quotation_date: string
  total_amount: number
  status: string
  company: { name: string }
}

interface Payment {
  id: number
  payment_number: string
  payment_date: string
  amount: number
  payment_method: string
  invoice: { invoice_number: string }
}

interface Contract {
  id: number
  contract_number: string
  title: string
  total_value: number
  currency: string
  status: string
  start_date: string
  signed_by_customer: boolean
  signed_by_company: boolean
  signed_at: string | null
  company: { name: string }
}

interface Props {
  stats: Stat
  invoices: Invoice[]
  quotes: Quote[]
  payments: Payment[]
  contracts: Contract[]
  customers: any[]
}

defineProps<Props>()

const formatMoney = (amount: number) => 'K' + amount.toLocaleString('en-US', { minimumFractionDigits: 2 })
const formatDate = (date: string) => new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })

const statusColors: Record<string, string> = {
  draft: 'bg-gray-100 text-gray-700',
  sent: 'bg-blue-100 text-blue-700',
  partial: 'bg-yellow-100 text-yellow-700',
  paid: 'bg-green-100 text-green-700',
  overdue: 'bg-red-100 text-red-700',
  cancelled: 'bg-gray-100 text-gray-500',
  active: 'bg-green-100 text-green-700',
  expired: 'bg-red-100 text-red-700',
  terminated: 'bg-red-100 text-red-700',
}
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <p class="text-sm text-gray-500 font-medium">Invoices</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.total_invoices }}</p>
        <p class="text-xs text-gray-400 mt-0.5">{{ stats.paid_invoices }} paid · {{ stats.overdue_invoices }} overdue</p>
      </div>
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <p class="text-sm text-gray-500 font-medium">Outstanding</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatMoney(stats.outstanding) }}</p>
      </div>
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <p class="text-sm text-gray-500 font-medium">Active Contracts</p>
        <p class="text-2xl font-bold text-green-600 mt-1">{{ stats.active_contracts }}</p>
        <p class="text-xs text-gray-400 mt-0.5">{{ stats.pending_signatures }} awaiting your signature</p>
      </div>
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <p class="text-sm text-gray-500 font-medium">Open Quotes</p>
        <p class="text-2xl font-bold text-blue-600 mt-1">{{ stats.open_quotes }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Invoices -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h2 class="font-semibold text-gray-900">Recent Invoices</h2>
          <Link :href="route('portal.invoices')" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View All</Link>
        </div>
        <div v-if="invoices.length === 0" class="p-6 text-center text-sm text-gray-500">No invoices yet</div>
        <div v-else class="divide-y divide-gray-50">
          <div v-for="inv in invoices" :key="inv.id" class="px-6 py-4 flex items-center justify-between hover:bg-gray-50">
            <div>
              <Link :href="route('portal.invoice-detail', inv.id)" class="text-sm font-medium text-blue-600 hover:text-blue-700">{{ inv.invoice_number }}</Link>
              <p class="text-xs text-gray-500">{{ inv.company?.name }} · {{ formatDate(inv.invoice_date) }}</p>
            </div>
            <div class="text-right">
              <p class="text-sm font-semibold text-gray-900">{{ formatMoney(inv.total_amount) }}</p>
              <span :class="['px-2 py-0.5 text-xs font-medium rounded-full', statusColors[inv.status] || 'bg-gray-100']">{{ inv.status }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Contracts -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h2 class="font-semibold text-gray-900">Recent Contracts</h2>
          <Link :href="route('portal.contracts')" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View All</Link>
        </div>
        <div v-if="contracts.length === 0" class="p-6 text-center text-sm text-gray-500">No contracts yet</div>
        <div v-else class="divide-y divide-gray-50">
          <div v-for="c in contracts" :key="c.id" class="px-6 py-4 flex items-center justify-between hover:bg-gray-50">
            <div>
              <Link :href="route('portal.contract-detail', c.id)" class="text-sm font-medium text-blue-600 hover:text-blue-700">{{ c.title }}</Link>
              <p class="text-xs text-gray-500">{{ c.company?.name }} · {{ c.contract_number }}</p>
            </div>
            <div class="text-right">
              <p class="text-sm font-semibold text-gray-900">{{ formatMoney(c.total_value) }} {{ c.currency }}</p>
              <span v-if="c.signed_by_customer && c.signed_by_company" class="text-xs text-green-600 font-medium">Signed</span>
              <span v-else-if="!c.signed_by_customer" class="text-xs text-amber-600 font-medium">Sign required</span>
              <span v-else :class="['px-2 py-0.5 text-xs font-medium rounded-full', statusColors[c.status] || 'bg-gray-100']">{{ c.status }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Quotes -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h2 class="font-semibold text-gray-900">Recent Quotes</h2>
          <Link :href="route('portal.quotes')" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View All</Link>
        </div>
        <div v-if="quotes.length === 0" class="p-6 text-center text-sm text-gray-500">No quotes yet</div>
        <div v-else class="divide-y divide-gray-50">
          <div v-for="q in quotes" :key="q.id" class="px-6 py-4 flex items-center justify-between hover:bg-gray-50">
            <div>
              <Link :href="route('portal.quote-detail', q.id)" class="text-sm font-medium text-blue-600 hover:text-blue-700">{{ q.quotation_number }}</Link>
              <p class="text-xs text-gray-500">{{ q.company?.name }} · {{ formatDate(q.quotation_date) }}</p>
            </div>
            <div class="text-right">
              <p class="text-sm font-semibold text-gray-900">{{ formatMoney(q.total_amount) }}</p>
              <span :class="['px-2 py-0.5 text-xs font-medium rounded-full', statusColors[q.status] || 'bg-gray-100']">{{ q.status }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Payments -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h2 class="font-semibold text-gray-900">Recent Payments</h2>
          <Link :href="route('portal.payments')" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View All</Link>
        </div>
        <div v-if="payments.length === 0" class="p-6 text-center text-sm text-gray-500">No payments yet</div>
        <div v-else class="divide-y divide-gray-50">
          <div v-for="p in payments" :key="p.id" class="px-6 py-4 flex items-center justify-between hover:bg-gray-50">
            <div>
              <p class="text-sm font-medium text-gray-900">{{ p.payment_number }}</p>
              <p class="text-xs text-gray-500">{{ p.invoice?.invoice_number }} · {{ formatDate(p.payment_date) }}</p>
            </div>
            <div class="text-right">
              <p class="text-sm font-semibold text-green-600">{{ formatMoney(p.amount) }}</p>
              <p class="text-xs text-gray-500 capitalize">{{ p.payment_method }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
