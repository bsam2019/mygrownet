<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import CMSLayout from '@/Layouts/CMSLayout.vue'
import Pagination from '@/Components/Pagination.vue'

defineOptions({ layout: CMSLayout })

interface Props {
  assets: { data: any[]; links: any }
  summary: {
    total_cost: number
    total_current_value: number
    total_accumulated_depreciation: number
  }
}

defineProps<Props>()

const formatMoney = (amount: number) => 'K' + amount.toLocaleString('en-US', { minimumFractionDigits: 2 })
const formatDate = (date: string) => date ? new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '-'

const methodLabels: Record<string, string> = {
  straight_line: 'Straight Line',
  declining_balance: 'Declining Balance',
  sum_of_years_digits: 'Sum of Years Digits',
}
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Depreciation Register</h1>

    <!-- Summary Cards -->
    <div class="grid grid-cols-3 gap-4 mb-8">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <p class="text-sm text-gray-500 font-medium">Total Cost</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatMoney(summary.total_cost) }}</p>
      </div>
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <p class="text-sm text-gray-500 font-medium">Current Value</p>
        <p class="text-2xl font-bold text-blue-600 mt-1">{{ formatMoney(summary.total_current_value) }}</p>
      </div>
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <p class="text-sm text-gray-500 font-medium">Accumulated Depreciation</p>
        <p class="text-2xl font-bold text-red-600 mt-1">{{ formatMoney(summary.total_accumulated_depreciation) }}</p>
      </div>
    </div>

    <!-- Assets Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Asset</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Purchase Cost</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Current Value</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Useful Life</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Annual Depr.</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Start Date</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="a in assets.data" :key="a.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <Link :href="route('cms.assets.show', a.id)" class="text-sm font-medium text-blue-600 hover:text-blue-700">{{ a.name }}</Link>
                <p class="text-xs text-gray-500">{{ a.asset_number }}</p>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ a.category }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">{{ formatMoney(a.purchase_cost) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium" :class="a.current_value < a.purchase_cost ? 'text-red-600' : 'text-green-600'">{{ formatMoney(a.current_value) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ a.depreciation ? methodLabels[a.depreciation.method] || a.depreciation.method : '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">{{ a.depreciation ? a.depreciation.useful_life_years + ' yrs' : '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">{{ a.depreciation ? formatMoney(a.depreciation.annual_depreciation) : '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ a.depreciation ? formatDate(a.depreciation.depreciation_start_date) : '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-center">
                <span v-if="a.depreciation" class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Active</span>
                <span v-else class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-500">Not Set</span>
              </td>
            </tr>
            <tr v-if="assets.data.length === 0">
              <td colspan="9" class="px-6 py-12 text-center text-sm text-gray-500">No assets found</td>
            </tr>
          </tbody>
        </table>
      </div>
      <Pagination :links="assets.links" />
    </div>
  </div>
</template>
