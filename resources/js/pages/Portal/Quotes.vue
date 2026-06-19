<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import PortalLayout from '@/Layouts/PortalLayout.vue'
import Pagination from '@/Components/Pagination.vue'

defineOptions({ layout: PortalLayout })

interface Props {
  quotes: { data: any[]; links: Array<{ url: string | null; label: string; active: boolean }> }
}

defineProps<Props>()

const formatMoney = (amount: number) => 'K' + amount.toLocaleString('en-US', { minimumFractionDigits: 2 })
const formatDate = (date: string) => new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })

const statusColors: Record<string, string> = {
  draft: 'bg-gray-100 text-gray-700',
  sent: 'bg-blue-100 text-blue-700',
  accepted: 'bg-green-100 text-green-700',
  rejected: 'bg-red-100 text-red-700',
  expired: 'bg-yellow-100 text-yellow-700',
  converted: 'bg-purple-100 text-purple-700',
}
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Quotes</h1>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quote #</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Company</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expiry</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="q in quotes.data" :key="q.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">{{ q.quotation_number }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ formatDate(q.quotation_date) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ q.company?.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">{{ formatMoney(q.total_amount) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ q.expiry_date ? formatDate(q.expiry_date) : '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-center">
                <span :class="['px-2 py-1 text-xs font-medium rounded-full', statusColors[q.status] || 'bg-gray-100']">{{ q.status }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right">
                <Link :href="route('portal.quote-detail', q.id)" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View</Link>
              </td>
            </tr>
            <tr v-if="quotes.data.length === 0">
              <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">No quotes found</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <Pagination :links="quotes.links" />
  </div>
</template>
