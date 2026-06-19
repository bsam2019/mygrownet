<script setup lang="ts">
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import CMSLayout from '@/Layouts/CMSLayout.vue'
import DoughnutChart from '@/components/CMS/Charts/DoughnutChart.vue'
import BarChart from '@/components/CMS/Charts/BarChart.vue'
import {
  TruckIcon, DocumentTextIcon, CurrencyDollarIcon,
  CheckCircleIcon, ClockIcon, ExclamationCircleIcon,
  BuildingLibraryIcon, DocumentCheckIcon,
} from '@heroicons/vue/24/outline'

defineOptions({ layout: CMSLayout })

interface VendorSpend {
  vendor_name: string
  total_spend: number
  order_count: number
}

interface Props {
  metrics: {
    total_vendors: number
    total_purchase_orders: number
    pending_pos: number
    approved_pos: number
    completed_pos: number
    total_po_value: number
    spend_by_vendor: VendorSpend[]
    po_by_status: Record<string, number>
  }
  contracts: {
    total_contracts: number
    active_contracts: number
    expiring_soon: number
    overdue_renewals: number
  }
  assets: {
    total_assets: number
    total_cost: number
    total_depreciation: number
    current_value: number
    by_status: Record<string, number>
  }
  period: string
}

const props = defineProps<Props>()

const selectedPeriod = ref(props.period)

const changePeriod = (period: string) => {
  selectedPeriod.value = period
  router.get(route('cms.analytics.procurement'), { period }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const formatCurrency = (value: number) =>
  new Intl.NumberFormat('en-ZM', { style: 'currency', currency: 'ZMW', minimumFractionDigits: 0 }).format(value)

const poStatusColors: Record<string, string> = {
  pending: '#f59e0b',
  approved: '#3b82f6',
  completed: '#10b981',
  cancelled: '#ef4444',
  rejected: '#ef4444',
}

const poByStatusChartData = computed(() => {
  const data: Record<string, number> = {}
  for (const [status, count] of Object.entries(props.metrics.po_by_status)) {
    data[status.charAt(0).toUpperCase() + status.slice(1)] = count
  }
  return data
})

const vendorSpendChartData = computed(() => ({
  labels: props.metrics.spend_by_vendor.map(v => v.vendor_name),
  data: props.metrics.spend_by_vendor.map(v => v.total_spend),
}))

const assetStatusChartData = computed(() => {
  const data: Record<string, number> = {}
  for (const [status, count] of Object.entries(props.assets.by_status)) {
    data[status.charAt(0).toUpperCase() + status.slice(1)] = count
  }
  return data
})
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Procurement & Assets Analytics</h1>
          <p class="mt-1 text-sm text-gray-600">Vendor spending, purchase orders, contracts, and fixed assets</p>
        </div>
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
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
      <div class="rounded-lg bg-white p-4 shadow">
        <div class="flex items-center gap-2 mb-1">
          <TruckIcon class="h-5 w-5 text-purple-600" />
          <span class="text-sm font-medium text-gray-600">Total Vendors</span>
        </div>
        <div class="text-2xl font-bold text-gray-900">{{ metrics.total_vendors }}</div>
      </div>

      <div class="rounded-lg bg-white p-4 shadow">
        <div class="flex items-center gap-2 mb-1">
          <DocumentTextIcon class="h-5 w-5 text-blue-600" />
          <span class="text-sm font-medium text-gray-600">Purchase Orders</span>
        </div>
        <div class="text-2xl font-bold text-gray-900">{{ metrics.total_purchase_orders }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ metrics.pending_pos }} pending, {{ metrics.completed_pos }} completed</div>
      </div>

      <div class="rounded-lg bg-white p-4 shadow">
        <div class="flex items-center gap-2 mb-1">
          <CurrencyDollarIcon class="h-5 w-5 text-emerald-600" />
          <span class="text-sm font-medium text-gray-600">Total PO Value</span>
        </div>
        <div class="text-2xl font-bold text-emerald-600">{{ formatCurrency(metrics.total_po_value) }}</div>
      </div>

      <div class="rounded-lg bg-white p-4 shadow">
        <div class="flex items-center gap-2 mb-1">
          <DocumentCheckIcon class="h-5 w-5 text-amber-600" />
          <span class="text-sm font-medium text-gray-600">Contracts</span>
        </div>
        <div class="text-2xl font-bold text-amber-600">{{ contracts.active_contracts }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ contracts.expiring_soon }} expiring soon</div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <!-- PO by Status -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="mb-4 flex items-center gap-2">
          <DocumentTextIcon class="h-5 w-5 text-blue-600" />
          <h3 class="text-base font-semibold text-gray-900">Purchase Orders by Status</h3>
        </div>
        <DoughnutChart
          :data="poByStatusChartData"
          :colors="Object.values(poStatusColors)"
          :height="260"
        />
      </div>

      <!-- Spend by Vendor -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="mb-4 flex items-center gap-2">
          <TruckIcon class="h-5 w-5 text-purple-600" />
          <h3 class="text-base font-semibold text-gray-900">Top Vendors by Spend</h3>
        </div>
        <BarChart
          v-if="metrics.spend_by_vendor.length > 0"
          :labels="vendorSpendChartData.labels"
          :data="vendorSpendChartData.data"
          color="#8b5cf6"
          :height="280"
          horizontal
          x-axis-label="Spend (ZMW)"
        />
        <p v-else class="text-sm text-gray-500 text-center py-8">No vendor spend data for this period</p>
      </div>
    </div>

    <!-- Contracts & Assets Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <!-- Contract Summary -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="mb-4 flex items-center gap-2">
          <DocumentCheckIcon class="h-5 w-5 text-amber-600" />
          <h3 class="text-base font-semibold text-gray-900">Contract Summary</h3>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div class="p-3 bg-gray-50 rounded-lg">
            <div class="text-sm text-gray-500">Total</div>
            <div class="text-xl font-bold text-gray-900">{{ contracts.total_contracts }}</div>
          </div>
          <div class="p-3 bg-green-50 rounded-lg">
            <div class="text-sm text-green-600">Active</div>
            <div class="text-xl font-bold text-green-700">{{ contracts.active_contracts }}</div>
          </div>
          <div class="p-3 bg-amber-50 rounded-lg">
            <div class="text-sm text-amber-600">Expiring Soon</div>
            <div class="text-xl font-bold text-amber-700">{{ contracts.expiring_soon }}</div>
          </div>
          <div class="p-3 bg-red-50 rounded-lg">
            <div class="text-sm text-red-600">Overdue Renewals</div>
            <div class="text-xl font-bold text-red-700">{{ contracts.overdue_renewals }}</div>
          </div>
        </div>
      </div>

      <!-- Asset Summary -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="mb-4 flex items-center gap-2">
          <BuildingLibraryIcon class="h-5 w-5 text-indigo-600" />
          <h3 class="text-base font-semibold text-gray-900">Fixed Asset Summary</h3>
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
          <div class="p-3 bg-gray-50 rounded-lg">
            <div class="text-sm text-gray-500">Total Assets</div>
            <div class="text-xl font-bold text-gray-900">{{ assets.total_assets }}</div>
          </div>
          <div class="p-3 bg-blue-50 rounded-lg">
            <div class="text-sm text-blue-600">Current Value</div>
            <div class="text-xl font-bold text-blue-700">{{ formatCurrency(assets.current_value) }}</div>
          </div>
          <div class="p-3 bg-indigo-50 rounded-lg">
            <div class="text-sm text-indigo-600">Total Cost</div>
            <div class="text-xl font-bold text-indigo-700">{{ formatCurrency(assets.total_cost) }}</div>
          </div>
          <div class="p-3 bg-amber-50 rounded-lg">
            <div class="text-sm text-amber-600">Depreciation</div>
            <div class="text-xl font-bold text-amber-700">{{ formatCurrency(assets.total_depreciation) }}</div>
          </div>
        </div>
        <DoughnutChart
          v-if="Object.keys(assets.by_status).length > 0"
          :data="assetStatusChartData"
          :colors="['#10b981', '#3b82f6', '#f59e0b', '#ef4444']"
          :height="200"
        />
      </div>
    </div>

    <!-- Vendor Spend Table -->
    <div class="mb-6" v-if="metrics.spend_by_vendor.length > 0">
      <div class="mb-3 flex items-center gap-2">
        <TruckIcon class="h-5 w-5 text-purple-600" />
        <h2 class="text-lg font-semibold text-gray-900">Vendor Spend Details</h2>
      </div>
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendor</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Orders</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Spend</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="v in metrics.spend_by_vendor" :key="v.vendor_name" class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ v.vendor_name }}</td>
              <td class="px-6 py-4 text-sm text-right text-gray-600">{{ v.order_count }}</td>
              <td class="px-6 py-4 text-sm text-right font-semibold text-purple-600">{{ formatCurrency(v.total_spend) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
