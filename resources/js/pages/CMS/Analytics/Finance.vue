<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { 
  CurrencyDollarIcon, 
  ArrowTrendingUpIcon, 
  ArrowTrendingDownIcon,
  BanknotesIcon 
} from '@heroicons/vue/24/outline'
import CMSLayoutNew from '@/Layouts/CMSLayoutNew.vue'

defineOptions({
  layout: CMSLayoutNew
})

interface Props {
  metrics: {
    revenue: number
    expenses: number
    profit: number
    cash_flow: Record<string, { inflow: number; outflow: number; net: number }>
    outstanding_invoices: {
      total_amount: number
      total_outstanding: number
      count: number
      by_customer: Array<{
        customer_name: string
        outstanding: number
        count: number
      }>
    }
    revenue_by_customer: Array<{
      customer_name: string
      total_revenue: number
      payment_count: number
    }>
    expense_breakdown: Record<string, number>
    payment_trends: Record<string, { total: number; count: number }>
    profit_margin_trend: Record<string, number>
  }
  period: string
}

const props = defineProps<Props>()

const selectedPeriod = ref(props.period)

const changePeriod = (period: string) => {
  selectedPeriod.value = period
  router.get(route('cms.analytics.finance'), { period }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const formatNumber = (value: number) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value)
}

const profitMargin = props.metrics.revenue > 0 
  ? ((props.metrics.profit / props.metrics.revenue) * 100).toFixed(2)
  : '0.00'
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Finance Dashboard</h1>
        <p class="mt-1 text-sm text-gray-500">Monitor financial performance and cash flow</p>
      </div>
      <div class="mt-4 sm:mt-0">
        <select
          v-model="selectedPeriod"
          @change="changePeriod(selectedPeriod)"
          class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        >
          <option value="week">Last Week</option>
          <option value="month">Last Month</option>
          <option value="quarter">Last Quarter</option>
          <option value="year">Last Year</option>
        </select>
      </div>
    </div>

    <!-- Key Financial Metrics -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
      <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <ArrowTrendingUpIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Revenue</dt>
                <dd class="flex items-baseline">
                  <div class="text-2xl font-semibold text-gray-900">K{{ formatNumber(metrics.revenue) }}</div>
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                <ArrowTrendingDownIcon class="h-6 w-6 text-red-600" aria-hidden="true" />
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Expenses</dt>
                <dd class="flex items-baseline">
                  <div class="text-2xl font-semibold text-gray-900">K{{ formatNumber(metrics.expenses) }}</div>
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div :class="[
                'w-12 h-12 rounded-lg flex items-center justify-center',
                metrics.profit >= 0 ? 'bg-emerald-100' : 'bg-red-100'
              ]">
                <CurrencyDollarIcon :class="[
                  'h-6 w-6',
                  metrics.profit >= 0 ? 'text-emerald-600' : 'text-red-600'
                ]" aria-hidden="true" />
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Profit</dt>
                <dd class="flex items-baseline">
                  <div :class="[
                    'text-2xl font-semibold',
                    metrics.profit >= 0 ? 'text-emerald-600' : 'text-red-600'
                  ]">
                    K{{ formatNumber(metrics.profit) }}
                  </div>
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <BanknotesIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Profit Margin</dt>
                <dd class="flex items-baseline">
                  <div class="text-2xl font-semibold text-gray-900">{{ profitMargin }}%</div>
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <!-- Outstanding Invoices -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Outstanding Invoices</h3>
        <div class="mb-4">
          <div class="flex justify-between items-center mb-2">
            <span class="text-sm text-gray-600">Total Outstanding</span>
            <span class="text-lg font-bold text-red-600">K{{ formatNumber(metrics.outstanding_invoices.total_outstanding) }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-sm text-gray-600">Number of Invoices</span>
            <span class="text-sm font-medium text-gray-900">{{ metrics.outstanding_invoices.count }}</span>
          </div>
        </div>
        <div class="border-t pt-4">
          <h4 class="text-sm font-medium text-gray-700 mb-3">By Customer</h4>
          <div class="space-y-2">
            <div 
              v-for="customer in metrics.outstanding_invoices.by_customer.slice(0, 5)" 
              :key="customer.customer_name"
              class="flex justify-between items-center text-sm"
            >
              <span class="text-gray-600 truncate">{{ customer.customer_name }}</span>
              <span class="font-medium text-gray-900">K{{ formatNumber(customer.outstanding) }}</span>
            </div>
            <div v-if="metrics.outstanding_invoices.by_customer.length === 0" class="text-center py-2 text-sm text-gray-500">
              No outstanding invoices
            </div>
          </div>
        </div>
      </div>

      <!-- Expense Breakdown -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Expense Breakdown</h3>
        <div class="space-y-3">
          <div v-for="(amount, category) in metrics.expense_breakdown" :key="category" class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-700">{{ category }}</span>
            <span class="text-sm font-semibold text-gray-900">K{{ formatNumber(amount) }}</span>
          </div>
          <div v-if="Object.keys(metrics.expense_breakdown).length === 0" class="text-center py-4 text-sm text-gray-500">
            No expense data available
          </div>
        </div>
      </div>
    </div>

    <!-- Revenue by Customer -->
    <div class="bg-white rounded-lg shadow mb-6">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Top Customers by Revenue</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Revenue</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Payments</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="customer in metrics.revenue_by_customer" :key="customer.customer_name">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ customer.customer_name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 text-right">
                K{{ formatNumber(customer.total_revenue) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                {{ customer.payment_count }}
              </td>
            </tr>
            <tr v-if="metrics.revenue_by_customer.length === 0">
              <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500">
                No revenue data available
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Cash Flow -->
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Cash Flow</h3>
      <div class="space-y-2">
        <div v-for="(flow, date) in metrics.cash_flow" :key="date" class="grid grid-cols-4 gap-4 text-sm">
          <span class="text-gray-600">{{ date }}</span>
          <span class="text-green-600 text-right">+K{{ formatNumber(flow.inflow) }}</span>
          <span class="text-red-600 text-right">-K{{ formatNumber(flow.outflow) }}</span>
          <span :class="[
            'font-medium text-right',
            flow.net >= 0 ? 'text-emerald-600' : 'text-red-600'
          ]">
            {{ flow.net >= 0 ? '+' : '' }}K{{ formatNumber(flow.net) }}
          </span>
        </div>
        <div v-if="Object.keys(metrics.cash_flow).length === 0" class="text-center py-4 text-sm text-gray-500">
          No cash flow data available
        </div>
      </div>
    </div>
  </div>
</template>
