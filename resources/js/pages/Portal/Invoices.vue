<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import PortalLayout from '@/Layouts/PortalLayout.vue'
import Pagination from '@/Components/Pagination.vue'

defineOptions({ layout: PortalLayout })

interface Props {
  invoices: { data: any[]; links: Array<{ url: string | null; label: string; active: boolean }> }
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
}
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Invoices</h1>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Company</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Paid</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Due</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="inv in invoices.data" :key="inv.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">{{ inv.invoice_number }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ formatDate(inv.invoice_date) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ inv.company?.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">{{ formatMoney(inv.total_amount) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">{{ formatMoney(inv.amount_paid) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium" :class="inv.amount_due > 0 ? 'text-red-600' : 'text-green-600'">{{ formatMoney(inv.amount_due) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-center">
                <span :class="['px-2 py-1 text-xs font-medium rounded-full', statusColors[inv.status] || 'bg-gray-100']">{{ inv.status }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right">
                <Link :href="route('portal.invoice-detail', inv.id)" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View</Link>
              </td>
            </tr>
            <tr v-if="invoices.data.length === 0">
              <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500">No invoices found</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <Pagination :links="invoices.links" />
  </div>
</template>
