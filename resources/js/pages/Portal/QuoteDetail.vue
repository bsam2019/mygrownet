<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import PortalLayout from '@/Layouts/PortalLayout.vue'

defineOptions({ layout: PortalLayout })

interface Props {
  quote: any
}

defineProps<Props>()

const formatMoney = (amount: number) => 'K' + amount.toLocaleString('en-US', { minimumFractionDigits: 2 })
const formatDate = (date: string) => new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
</script>

<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <Link :href="route('portal.quotes')" class="text-sm text-blue-600 hover:text-blue-700 mb-4 inline-block">← Back to Quotes</Link>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">
      <div class="flex items-center justify-between mb-6 pb-6 border-b border-gray-200">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">{{ quote.quotation_number }}</h1>
          <p class="text-gray-500 mt-1">{{ quote.company?.name }}</p>
        </div>
        <span :class="['px-3 py-1 text-sm font-medium rounded-full', quote.status === 'accepted' || quote.status === 'converted' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700']">
          {{ quote.status }}
        </span>
      </div>

      <div class="grid grid-cols-2 gap-6 mb-6">
        <div>
          <p class="text-sm text-gray-500 font-medium">Quote Date</p>
          <p class="text-gray-900">{{ formatDate(quote.quotation_date) }}</p>
        </div>
        <div>
          <p class="text-sm text-gray-500 font-medium">Expiry Date</p>
          <p class="text-gray-900">{{ quote.expiry_date ? formatDate(quote.expiry_date) : '-' }}</p>
        </div>
      </div>

      <div v-if="quote.notes" class="mb-6 p-4 bg-gray-50 rounded-lg">
        <p class="text-sm text-gray-700">{{ quote.notes }}</p>
      </div>

      <h3 class="font-semibold text-gray-900 mb-3">Items</h3>
      <div class="border border-gray-200 rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
              <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
              <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Price</th>
              <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="item in quote.items" :key="item.id" class="text-sm">
              <td class="px-4 py-3 text-gray-900">{{ item.description }}</td>
              <td class="px-4 py-3 text-right text-gray-600">{{ item.quantity }}</td>
              <td class="px-4 py-3 text-right text-gray-600">{{ formatMoney(item.unit_price) }}</td>
              <td class="px-4 py-3 text-right font-medium text-gray-900">{{ formatMoney(item.line_total) }}</td>
            </tr>
          </tbody>
          <tfoot class="bg-gray-50">
            <tr class="text-sm font-semibold">
              <td colspan="3" class="px-4 py-3 text-right text-gray-700">Total</td>
              <td class="px-4 py-3 text-right text-gray-900">{{ formatMoney(quote.total_amount) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</template>
