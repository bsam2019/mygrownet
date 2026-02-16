<script setup lang="ts">
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { PlusIcon, FunnelIcon } from '@heroicons/vue/24/outline'
import CMSLayout from '@/Layouts/CMSLayout.vue'

defineOptions({
  layout: CMSLayout
})

interface Props {
  payrollRuns: any
  filters: any
}

const props = defineProps<Props>()

const selectedStatus = ref(props.filters.status || '')
const selectedPeriodType = ref(props.filters.period_type || '')

const applyFilters = () => {
  router.get(route('cms.payroll.index'), {
    status: selectedStatus.value,
    period_type: selectedPeriodType.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const clearFilters = () => {
  selectedStatus.value = ''
  selectedPeriodType.value = ''
  applyFilters()
}

const getStatusClass = (status: string) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800',
    approved: 'bg-green-100 text-green-800',
    paid: 'bg-blue-100 text-blue-800',
  }
  return classes[status as keyof typeof classes] || classes.draft
}

const formatNumber = (value: number) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value)
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Header -->
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Payroll Runs</h1>
        <p class="mt-1 text-sm text-gray-500">Manage payroll processing and payments</p>
      </div>
      <div class="mt-4 sm:mt-0">
        <Link
          :href="route('cms.payroll.create')"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium"
        >
          <PlusIcon class="h-5 w-5" aria-hidden="true" />
          Create Payroll Run
        </Link>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            v-model="selectedStatus"
            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="">All Statuses</option>
            <option value="draft">Draft</option>
            <option value="approved">Approved</option>
            <option value="paid">Paid</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Period Type</label>
          <select
            v-model="selectedPeriodType"
            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="">All Types</option>
            <option value="weekly">Weekly</option>
            <option value="bi-weekly">Bi-Weekly</option>
            <option value="monthly">Monthly</option>
          </select>
        </div>
      </div>

      <div class="flex gap-3 mt-4">
        <button
          @click="applyFilters"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium"
        >
          <FunnelIcon class="h-4 w-4" aria-hidden="true" />
          Apply Filters
        </button>
        <button
          @click="clearFilters"
          class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
        >
          Clear
        </button>
      </div>
    </div>

    <!-- Payroll Runs Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Payroll Number
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Period
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Type
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Total Amount
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Status
            </th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="payroll in payrollRuns.data" :key="payroll.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">{{ payroll.payroll_number }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900">
                {{ formatDate(payroll.period_start) }} - {{ formatDate(payroll.period_end) }}
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900 capitalize">{{ payroll.period_type.replace('-', ' ') }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">K{{ formatNumber(payroll.total_net_pay) }}</div>
              <div class="text-xs text-gray-500">
                Wages: K{{ formatNumber(payroll.total_wages) }} | 
                Commissions: K{{ formatNumber(payroll.total_commissions) }}
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusClass(payroll.status)]"
              >
                {{ payroll.status.toUpperCase() }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <Link
                :href="route('cms.payroll.show', payroll.id)"
                class="text-blue-600 hover:text-blue-900"
              >
                View
              </Link>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Empty State -->
      <div v-if="payrollRuns.data.length === 0" class="text-center py-12">
        <p class="text-gray-500">No payroll runs found</p>
      </div>

      <!-- Pagination -->
      <div v-if="payrollRuns.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Showing {{ payrollRuns.from }} to {{ payrollRuns.to }} of {{ payrollRuns.total }} results
          </div>
          <div class="flex gap-2">
            <Link
              v-if="payrollRuns.prev_page_url"
              :href="payrollRuns.prev_page_url"
              class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50"
            >
              Previous
            </Link>
            <Link
              v-if="payrollRuns.next_page_url"
              :href="payrollRuns.next_page_url"
              class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50"
            >
              Next
            </Link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
