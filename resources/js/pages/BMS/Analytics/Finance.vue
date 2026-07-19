<script setup lang="ts">
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { 
  CurrencyDollarIcon, 
  ArrowTrendingUpIcon, 
  ArrowTrendingDownIcon,
  BanknotesIcon,
  DocumentTextIcon,
  ArrowRightIcon,
  ArrowDownOnSquareIcon,
} from '@heroicons/vue/24/outline'
import CMSLayout from '@/Layouts/CMSLayout.vue'
import DoughnutChart from '@/components/BMS/Charts/DoughnutChart.vue'
import LineChart from '@/components/BMS/Charts/LineChart.vue'
import BarChart from '@/components/BMS/Charts/BarChart.vue'
import StackedBarChart from '@/components/BMS/Charts/StackedBarChart.vue'

defineOptions({
  layout: CMSLayout
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
    revenue_expense_trend: {
      dates: string[]
      revenue: number[]
      expenses: number[]
      profit: number[]
    }
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

// Chart data computations
const expenseChartColors = ['#ef4444', '#f59e0b', '#8b5cf6', '#ec4899', '#3b82f6', '#10b981']

const revenueExpenseChartData = computed(() => ({
  labels: props.metrics.revenue_expense_trend.dates,
  datasets: [
    {
      label: 'Revenue',
      data: props.metrics.revenue_expense_trend.revenue,
      color: '#10b981',
      fill: true
    },
    {
      label: 'Expenses',
      data: props.metrics.revenue_expense_trend.expenses,
      color: '#ef4444',
      fill: true
    }
  ]
}))

const cashFlowChartData = computed(() => {
  const dates = Object.keys(props.metrics.cash_flow)
  const inflows = dates.map(date => props.metrics.cash_flow[date].inflow)
  const outflows = dates.map(date => props.metrics.cash_flow[date].outflow)
  
  return {
    labels: dates,
    datasets: [
      { label: 'Inflow', data: inflows, color: '#10b981' },
      { label: 'Outflow', data: outflows, color: '#ef4444' }
    ]
  }
})

const revenueByCustomerChartData = computed(() => {
  const topCustomers = props.metrics.revenue_by_customer.slice(0, 10)
  return {
    labels: topCustomers.map(c => c.customer_name),
    data: topCustomers.map(c => c.total_revenue)
  }
})

const outstandingInvoicesChartData = computed(() => {
  const topCustomers = props.metrics.outstanding_invoices.by_customer.slice(0, 10)
  return {
    labels: topCustomers.map(c => c.customer_name),
    data: topCustomers.map(c => c.outstanding)
  }
})

const profitMarginTrendChartData = computed(() => {
  const dates = Object.keys(props.metrics.profit_margin_trend)
  const margins = Object.values(props.metrics.profit_margin_trend)
  
  return {
    labels: dates,
    datasets: [{
      label: 'Profit Margin %',
      data: margins,
      color: '#3b82f6',
      fill: true
    }]
  }
})

const paymentTrendsChartData = computed(() => {
  const dates = Object.keys(props.metrics.payment_trends)
  const amounts = dates.map(date => props.metrics.payment_trends[date].total)
  const counts = dates.map(date => props.metrics.payment_trends[date].count)
  
  return {
    labels: dates,
    datasets: [
      { label: 'Payment Amount', data: amounts, color: '#10b981', fill: true },
      { label: 'Payment Count', data: counts, color: '#3b82f6', fill: false }
    ]
  }
})
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Finance Analytics</h1>
          <p class="mt-1 text-sm text-gray-600">Monitor financial performance and cash flow</p>
        </div>
        <div class="flex items-center gap-2">
          <Link
            :href="route('cms.analytics.finance.export-csv', { period: selectedPeriod })"
            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors shadow-sm"
          >
            <ArrowDownOnSquareIcon class="h-5 w-5" aria-hidden="true" />
            Export CSV
          </Link>
          <Link
            :href="route('cms.reports.index')"
            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition-colors shadow-sm"
          >
            <DocumentTextIcon class="h-5 w-5" aria-hidden="true" />
            View Detailed Reports
            <ArrowRightIcon class="h-4 w-4" aria-hidden="true" />
          </Link>
        </div>
      </div>
    </div>

    <!-- Period Filter -->
    <div class="mb-6 rounded-lg bg-white p-4 shadow">
      <div class="flex items-center gap-4">
        <label class="text-sm font-medium text-gray-700">Period:</label>
        <select
          v-model="selectedPeriod"
          @change="changePeriod(selectedPeriod)"
          class="block w-full max-w-xs px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
        >
          <option value="week">Last Week</option>
          <option value="month">Last Month</option>
          <option value="quarter">Last Quarter</option>
          <option value="year">Last Year</option>
        </select>
      </div>
    </div>

    <!-- Key Financial Metrics -->
    <div class="mb-6">
      <div class="mb-3 flex items-center gap-2">
        <CurrencyDollarIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
        <h2 class="text-lg font-semibold text-gray-900">Key Metrics</h2>
      </div>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg bg-white p-4 shadow">
          <div class="text-sm font-medium text-gray-600">Revenue</div>
          <div class="mt-1 text-2xl font-bold text-green-600">K{{ formatNumber(metrics.revenue) }}</div>
        </div>

        <div class="rounded-lg bg-white p-4 shadow">
          <div class="text-sm font-medium text-gray-600">Expenses</div>
          <div class="mt-1 text-2xl font-bold text-red-600">K{{ formatNumber(metrics.expenses) }}</div>
        </div>

        <div class="rounded-lg bg-white p-4 shadow">
          <div class="text-sm font-medium text-gray-600">Profit</div>
          <div class="mt-1 text-2xl font-bold" :class="metrics.profit >= 0 ? 'text-emerald-600' : 'text-red-600'">
            K{{ formatNumber(metrics.profit) }}
          </div>
        </div>

        <div class="rounded-lg bg-white p-4 shadow">
          <div class="text-sm font-medium text-gray-600">Profit Margin</div>
          <div class="mt-1 text-2xl font-bold text-blue-600">{{ profitMargin }}%</div>
        </div>
      </div>
    </div>

    <!-- Revenue vs Expenses Trend -->
    <div class="mb-6">
      <div class="mb-3 flex items-center gap-2">
        <ArrowTrendingUpIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
        <h2 class="text-lg font-semibold text-gray-900">Revenue vs Expenses Trend</h2>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <LineChart 
          :labels="revenueExpenseChartData.labels"
          :datasets="revenueExpenseChartData.datasets"
          :height="350"
          y-axis-label="Amount (K)"
          x-axis-label="Date"
        />
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <!-- Outstanding Invoices -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="mb-4 flex items-center gap-2">
          <BanknotesIcon class="h-5 w-5 text-amber-600" aria-hidden="true" />
          <h3 class="text-base font-semibold text-gray-900">Outstanding Invoices</h3>
        </div>
        <div class="mb-4">
          <div class="flex justify-between items-center mb-2">
            <span class="text-sm text-gray-600">Total Outstanding</span>
            <span class="text-lg font-bold text-red-600">K{{ formatNumber(metrics.outstanding_invoices.total_outstanding) }}</span>
          </div>
          <div class="flex justify-between items-center mb-4">
            <span class="text-sm text-gray-600">Number of Invoices</span>
            <span class="text-sm font-medium text-gray-900">{{ metrics.outstanding_invoices.count }}</span>
          </div>
        </div>
        <div class="border-t pt-4">
          <h4 class="text-sm font-medium text-gray-700 mb-3">Top Customers</h4>
          <BarChart 
            :labels="outstandingInvoicesChartData.labels"
            :data="outstandingInvoicesChartData.data"
            color="#f59e0b"
            :height="250"
            horizontal
          />
        </div>
      </div>

      <!-- Expense Breakdown -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="mb-4 flex items-center gap-2">
          <ArrowTrendingDownIcon class="h-5 w-5 text-red-600" aria-hidden="true" />
          <h3 class="text-base font-semibold text-gray-900">Expense Breakdown</h3>
        </div>
        <DoughnutChart 
          :data="metrics.expense_breakdown"
          :colors="expenseChartColors"
          :height="280"
        />
      </div>
    </div>

    <!-- Revenue by Customer -->
    <div class="mb-6">
      <div class="mb-3 flex items-center gap-2">
        <ArrowTrendingUpIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
        <h2 class="text-lg font-semibold text-gray-900">Top Customers by Revenue</h2>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <BarChart 
          :labels="revenueByCustomerChartData.labels"
          :data="revenueByCustomerChartData.data"
          color="#10b981"
          :height="350"
          horizontal
          x-axis-label="Revenue (K)"
        />
      </div>
    </div>

    <!-- Cash Flow & Profit Margin -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <!-- Cash Flow -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="mb-4 flex items-center gap-2">
          <BanknotesIcon class="h-6 w-6 text-teal-600" aria-hidden="true" />
          <h3 class="text-lg font-semibold text-gray-900">Cash Flow</h3>
        </div>
        <StackedBarChart 
          :labels="cashFlowChartData.labels"
          :datasets="cashFlowChartData.datasets"
          :height="300"
          y-axis-label="Amount (K)"
        />
      </div>

      <!-- Profit Margin Trend -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="mb-4 flex items-center gap-2">
          <ArrowTrendingUpIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
          <h3 class="text-lg font-semibold text-gray-900">Profit Margin Trend</h3>
        </div>
        <LineChart 
          :labels="profitMarginTrendChartData.labels"
          :datasets="profitMarginTrendChartData.datasets"
          :height="300"
          y-axis-label="Margin %"
        />
      </div>
    </div>

    <!-- Payment Trends -->
    <div class="mb-6">
      <div class="mb-3 flex items-center gap-2">
        <CurrencyDollarIcon class="h-6 w-6 text-emerald-600" aria-hidden="true" />
        <h2 class="text-lg font-semibold text-gray-900">Payment Trends</h2>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <LineChart 
          :labels="paymentTrendsChartData.labels"
          :datasets="paymentTrendsChartData.datasets"
          :height="300"
          y-axis-label="Amount / Count"
          x-axis-label="Date"
        />
      </div>
    </div>
  </div>
</template>
