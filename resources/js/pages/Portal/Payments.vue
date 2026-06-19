<script setup lang="ts">
import PortalLayout from '@/Layouts/PortalLayout.vue'
import Pagination from '@/Components/Pagination.vue'

defineOptions({ layout: PortalLayout })

interface Props {
  payments: { data: any[]; links: Array<{ url: string | null; label: string; active: boolean }> }
}

defineProps<Props>()

const formatMoney = (amount: number) => 'K' + amount.toLocaleString('en-US', { minimumFractionDigits: 2 })
const formatDate = (date: string) => new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Payment History</h1>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment #</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="p in payments.data" :key="p.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ p.payment_number }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ formatDate(p.payment_date) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ p.invoice?.invoice_number || '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 capitalize">{{ p.payment_method }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-green-600">{{ formatMoney(p.amount) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ p.reference_number || '-' }}</td>
            </tr>
            <tr v-if="payments.data.length === 0">
              <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">No payments found</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <Pagination :links="payments.links" />
  </div>
</template>
