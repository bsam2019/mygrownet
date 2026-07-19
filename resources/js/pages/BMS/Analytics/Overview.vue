<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import BMSLayout from '@/Layouts/BMSLayout.vue'
import {
  CurrencyDollarIcon, BanknotesIcon, ChartBarIcon,
  CheckCircleIcon, ClockIcon, TruckIcon, UsersIcon,
  DocumentTextIcon, ArrowTrendingUpIcon, ExclamationTriangleIcon,
  ArrowRightIcon,
} from '@heroicons/vue/24/outline'

defineOptions({ layout: BMSLayout })

interface Props {
  metrics: {
    revenue: number
    profit: number
    expenses: number
    outstanding: number
    job_completion_rate: number
    active_jobs: number
    total_vendors: number
    pending_pos: number
    total_customers: number
    invoice_paid_rate: number
    period: string
  }
}

const props = defineProps<Props>()

const formatCurrency = (value: number) =>
  new Intl.NumberFormat('en-ZM', { style: 'currency', currency: 'ZMW', minimumFractionDigits: 0 }).format(value)
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">CEO Overview</h1>
          <p class="mt-1 text-sm text-gray-600">Your business at a glance — key metrics across all departments</p>
        </div>
        <div class="flex items-center gap-3">
          <Link
            :href="route('bms.analytics.finance')"
            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition-colors shadow-sm"
          >
            <ArrowTrendingUpIcon class="h-5 w-5" />
            Finance Details
            <ArrowRightIcon class="h-4 w-4" />
          </Link>
          <Link
            :href="route('bms.analytics.operations')"
            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors shadow-sm"
          >
            <ClockIcon class="h-5 w-5" />
            Operations Details
            <ArrowRightIcon class="h-4 w-4" />
          </Link>
        </div>
      </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
      <div class="rounded-lg bg-white p-5 shadow border-l-4 border-emerald-500">
        <div class="flex items-center gap-2 mb-2">
          <CurrencyDollarIcon class="h-5 w-5 text-emerald-600" />
          <span class="text-sm font-medium text-gray-500">Revenue (30d)</span>
        </div>
        <div class="text-2xl font-bold text-gray-900">{{ formatCurrency(metrics.revenue) }}</div>
      </div>

      <div class="rounded-lg bg-white p-5 shadow border-l-4 border-blue-500">
        <div class="flex items-center gap-2 mb-2">
          <BanknotesIcon class="h-5 w-5 text-blue-600" />
          <span class="text-sm font-medium text-gray-500">Profit (30d)</span>
        </div>
        <div class="text-2xl font-bold" :class="metrics.profit >= 0 ? 'text-blue-600' : 'text-red-600'">
          {{ formatCurrency(metrics.profit) }}
        </div>
      </div>

      <div class="rounded-lg bg-white p-5 shadow border-l-4 border-amber-500">
        <div class="flex items-center gap-2 mb-2">
          <ExclamationTriangleIcon class="h-5 w-5 text-amber-600" />
          <span class="text-sm font-medium text-gray-500">Outstanding</span>
        </div>
        <div class="text-2xl font-bold text-amber-600">{{ formatCurrency(metrics.outstanding) }}</div>
      </div>

      <div class="rounded-lg bg-white p-5 shadow border-l-4 border-purple-500">
        <div class="flex items-center gap-2 mb-2">
          <DocumentTextIcon class="h-5 w-5 text-purple-600" />
          <span class="text-sm font-medium text-gray-500">Invoice Paid Rate</span>
        </div>
        <div class="text-2xl font-bold text-purple-600">{{ metrics.invoice_paid_rate }}%</div>
      </div>
    </div>

    <!-- Second Row -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
      <div class="rounded-lg bg-white p-5 shadow border-l-4 border-teal-500">
        <div class="flex items-center gap-2 mb-2">
          <CheckCircleIcon class="h-5 w-5 text-teal-600" />
          <span class="text-sm font-medium text-gray-500">Job Completion</span>
        </div>
        <div class="text-2xl font-bold text-teal-600">{{ metrics.job_completion_rate }}%</div>
      </div>

      <div class="rounded-lg bg-white p-5 shadow border-l-4 border-cyan-500">
        <div class="flex items-center gap-2 mb-2">
          <ClockIcon class="h-5 w-5 text-cyan-600" />
          <span class="text-sm font-medium text-gray-500">Active Jobs</span>
        </div>
        <div class="text-2xl font-bold text-cyan-600">{{ metrics.active_jobs }}</div>
      </div>

      <div class="rounded-lg bg-white p-5 shadow border-l-4 border-indigo-500">
        <div class="flex items-center gap-2 mb-2">
          <UsersIcon class="h-5 w-5 text-indigo-600" />
          <span class="text-sm font-medium text-gray-500">Customers</span>
        </div>
        <div class="text-2xl font-bold text-indigo-600">{{ metrics.total_customers }}</div>
      </div>

      <div class="rounded-lg bg-white p-5 shadow border-l-4 border-rose-500">
        <div class="flex items-center gap-2 mb-2">
          <TruckIcon class="h-5 w-5 text-rose-600" />
          <span class="text-sm font-medium text-gray-500">Vendors / Pending POs</span>
        </div>
        <div class="text-2xl font-bold text-rose-600">{{ metrics.total_vendors }} / {{ metrics.pending_pos }}</div>
      </div>
    </div>

    <!-- Quick Links / Module Navigation -->
    <h2 class="text-lg font-semibold text-gray-900 mb-3">Quick Access</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
      <Link :href="route('bms.analytics.finance')" class="rounded-lg bg-white p-4 shadow hover:shadow-md transition-shadow flex items-center gap-3">
        <div class="p-2 rounded-lg bg-blue-100"><ArrowTrendingUpIcon class="h-6 w-6 text-blue-600" /></div>
        <div><div class="font-semibold text-gray-900">Finance Analytics</div><div class="text-xs text-gray-500">Revenue, expenses, cash flow</div></div>
      </Link>
      <Link :href="route('bms.analytics.operations')" class="rounded-lg bg-white p-4 shadow hover:shadow-md transition-shadow flex items-center gap-3">
        <div class="p-2 rounded-lg bg-emerald-100"><ClockIcon class="h-6 w-6 text-emerald-600" /></div>
        <div><div class="font-semibold text-gray-900">Operations Analytics</div><div class="text-xs text-gray-500">Jobs, productivity, inventory</div></div>
      </Link>
      <Link :href="route('bms.analytics.procurement')" class="rounded-lg bg-white p-4 shadow hover:shadow-md transition-shadow flex items-center gap-3">
        <div class="p-2 rounded-lg bg-purple-100"><TruckIcon class="h-6 w-6 text-purple-600" /></div>
        <div><div class="font-semibold text-gray-900">Procurement Analytics</div><div class="text-xs text-gray-500">Vendors, POs, contracts</div></div>
      </Link>
    </div>
  </div>
</template>
