<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Commission Reports</h2>
        <p class="text-gray-600">Employee commission tracking and analytics</p>
      </div>
      <div class="flex space-x-3">
        <button
          @click="exportReport"
          :disabled="isExporting"
          class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          {{ isExporting ? 'Exporting...' : 'Export Report' }}
        </button>
        <button
          @click="refreshData"
          :disabled="isLoading"
          class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          {{ isLoading ? 'Loading...' : 'Refresh' }}
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-lg shadow border">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
          <select
            v-model="filters.employeeId"
            @change="applyFilters"
            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">All Employees</option>
            <option
              v-for="employee in employees"
              :key="employee.id"
              :value="employee.id"
            >
              {{ employee.firstName }} {{ employee.lastName }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Commission Type</label>
          <select
            v-model="filters.commissionType"
            @change="applyFilters"
            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">All Types</option>
            <option value="investment_facilitation">Investment Facilitation</option>
            <option value="referral">Referral</option>
            <option value="performance_bonus">Performance Bonus</option>
            <option value="retention_bonus">Retention Bonus</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            v-model="filters.status"
            @change="applyFilters"
            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="paid">Paid</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Period</label>
          <select
            v-model="filters.period"
            @change="applyFilters"
            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="current_month">Current Month</option>
            <option value="last_month">Last Month</option>
            <option value="current_quarter">Current Quarter</option>
            <option value="last_quarter">Last Quarter</option>
            <option value="current_year">Current Year</option>
            <option value="custom">Custom Range</option>
          </select>
        </div>
        <div v-if="filters.period === 'custom'">
          <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
          <div class="flex space-x-2">
            <input
              v-model="filters.startDate"
              @change="applyFilters"
              type="date"
              class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            />
            <input
              v-model="filters.endDate"
              @change="applyFilters"
              type="date"
              class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Commission Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="bg-white p-6 rounded-lg shadow border">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Total Commissions</p>
            <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(summary.totalCommissions) }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow border">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Paid Commissions</p>
            <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(summary.paidCommissions) }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow border">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Pending Commissions</p>
            <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(summary.pendingCommissions) }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow border">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Avg Commission</p>
            <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(summary.avgCommission) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Commission Chart -->
    <div class="bg-white p-6 rounded-lg shadow border">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Commission Trends</h3>
      <div class="h-64">
        <canvas ref="commissionChart"></canvas>
      </div>
    </div>

    <!-- Commission Breakdown by Type -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-white p-6 rounded-lg shadow border">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Commission by Type</h3>
        <div class="h-48">
          <canvas ref="typeChart"></canvas>
        </div>
      </div>
      <div class="bg-white p-6 rounded-lg shadow border">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Top Earners</h3>
        <div class="space-y-3">
          <div
            v-for="(earner, index) in topEarners"
            :key="earner.id"
            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
          >
            <div class="flex items-center">
              <div class="flex-shrink-0 h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                <span class="text-sm font-medium text-blue-600">{{ index + 1 }}</span>
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-900">{{ earner.name }}</p>
                <p class="text-xs text-gray-500">{{ earner.position }}</p>
              </div>
            </div>
            <div class="text-right">
              <p class="text-sm font-medium text-gray-900">{{ formatCurrency(earner.totalCommission) }}</p>
              <p class="text-xs text-gray-500">{{ earner.commissionCount }} commissions</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Commission Details Table -->
    <div class="bg-white rounded-lg shadow border">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Commission Details</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th
                v-for="column in tableColumns"
                :key="column.key"
                @click="sortBy(column.key)"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                :class="{ 'bg-gray-100': sortColumn === column.key }"
              >
                <div class="flex items-center space-x-1">
                  <span>{{ column.label }}</span>
                  <svg
                    v-if="sortColumn === column.key"
                    class="w-4 h-4"
                    :class="sortDirection === 'asc' ? 'transform rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="commission in sortedCommissionData"
              :key="commission.id"
              class="hover:bg-gray-50"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                      <span class="text-sm font-medium text-gray-700">
                        {{ commission.employeeName.split(' ').map(n => n.charAt(0)).join('') }}
                      </span>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ commission.employeeName }}</div>
                    <div class="text-sm text-gray-500">{{ commission.employeePosition }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                  :class="getTypeClass(commission.commissionType)"
                >
                  {{ formatCommissionType(commission.commissionType) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatCurrency(commission.baseAmount) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatPercentage(commission.commissionRate) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ formatCurrency(commission.commissionAmount) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(commission.calculationDate) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ commission.paymentDate ? formatDate(commission.paymentDate) : '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                  :class="getStatusClass(commission.status)"
                >
                  {{ commission.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button
                  v-if="commission.status === 'pending'"
                  @click="approveCommission(commission)"
                  class="text-green-600 hover:text-green-900 mr-3"
                >
                  Approve
                </button>
                <button
                  @click="viewCommissionDetails(commission)"
                  class="text-blue-600 hover:text-blue-900"
                >
                  View
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <!-- Empty State -->
    <div v-if="!isLoading && commissionData.length === 0" class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No commission data</h3>
      <p class="mt-1 text-sm text-gray-500">No commission records found for the selected criteria.</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'
import { Chart, registerables } from 'chart.js'
import { formatCurrency, formatNumber, formatPercentage } from '@/utils/formatting'

Chart.register(...registerables)

interface Employee {
  id: number
  firstName: string
  lastName: string
}

interface Commission {
  id: number
  employeeName: string
  employeePosition: string
  commissionType: string
  baseAmount: number
  commissionRate: number
  commissionAmount: number
  calculationDate: string
  paymentDate: string | null
  status: string
}

interface CommissionSummary {
  totalCommissions: number
  paidCommissions: number
  pendingCommissions: number
  avgCommission: number
}

interface TopEarner {
  id: number
  name: string
  position: string
  totalCommission: number
  commissionCount: number
}

interface Filters {
  employeeId: string
  commissionType: string
  status: string
  period: string
  startDate: string
  endDate: string
}

// Props
const props = defineProps<{
  employees: Employee[]
  initialData?: Commission[]
  initialSummary?: CommissionSummary
  initialTopEarners?: TopEarner[]
}>()

// Reactive data
const isLoading = ref(false)
const isExporting = ref(false)
const commissionData = ref<Commission[]>(props.initialData || [])
const summary = ref<CommissionSummary>(props.initialSummary || {
  totalCommissions: 0,
  paidCommissions: 0,
  pendingCommissions: 0,
  avgCommission: 0
})
const topEarners = ref<TopEarner[]>(props.initialTopEarners || [])

const filters = ref<Filters>({
  employeeId: '',
  commissionType: '',
  status: '',
  period: 'current_month',
  startDate: '',
  endDate: ''
})

const sortColumn = ref('calculationDate')
const sortDirection = ref<'asc' | 'desc'>('desc')
const commissionChart = ref<HTMLCanvasElement>()
const typeChart = ref<HTMLCanvasElement>()
const chartInstance = ref<Chart>()
const typeChartInstance = ref<Chart>()

// Table configuration
const tableColumns = [
  { key: 'employeeName', label: 'Employee' },
  { key: 'commissionType', label: 'Type' },
  { key: 'baseAmount', label: 'Base Amount' },
  { key: 'commissionRate', label: 'Rate' },
  { key: 'commissionAmount', label: 'Commission' },
  { key: 'calculationDate', label: 'Calculated' },
  { key: 'paymentDate', label: 'Paid' },
  { key: 'status', label: 'Status' },
  { key: 'actions', label: 'Actions' }
]

// Computed
const sortedCommissionData = computed(() => {
  const data = [...commissionData.value]
  
  return data.sort((a, b) => {
    const aValue = a[sortColumn.value as keyof Commission]
    const bValue = b[sortColumn.value as keyof Commission]
    
    if (typeof aValue === 'string' && typeof bValue === 'string') {
      return sortDirection.value === 'asc' 
        ? aValue.localeCompare(bValue)
        : bValue.localeCompare(aValue)
    }
    
    if (typeof aValue === 'number' && typeof bValue === 'number') {
      return sortDirection.value === 'asc' 
        ? aValue - bValue
        : bValue - aValue
    }
    
    return 0
  })
})

// Methods
const loadData = async () => {
  isLoading.value = true
  try {
    const response = await fetch('/api/employee/reports/commissions', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify(filters.value)
    })
    
    if (response.ok) {
      const data = await response.json()
      commissionData.value = data.commissions
      summary.value = data.summary
      topEarners.value = data.topEarners
      await nextTick()
      updateCharts(data.chartData)
    }
  } catch (error) {
    console.error('Failed to load commission reports:', error)
  } finally {
    isLoading.value = false
  }
}

const updateCharts = (chartData: any) => {
  updateCommissionChart(chartData.trends)
  updateTypeChart(chartData.byType)
}

const updateCommissionChart = (trendData: any) => {
  if (!commissionChart.value) return
  
  if (chartInstance.value) {
    chartInstance.value.destroy()
  }
  
  const ctx = commissionChart.value.getContext('2d')
  if (!ctx) return
  
  chartInstance.value = new Chart(ctx, {
    type: 'line',
    data: {
      labels: trendData.labels,
      datasets: [
        {
          label: 'Total Commissions',
          data: trendData.totalCommissions,
          borderColor: '#3b82f6',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          tension: 0.4
        },
        {
          label: 'Paid Commissions',
          data: trendData.paidCommissions,
          borderColor: '#10b981',
          backgroundColor: 'rgba(16, 185, 129, 0.1)',
          tension: 0.4
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  })
}

const updateTypeChart = (typeData: any) => {
  if (!typeChart.value) return
  
  if (typeChartInstance.value) {
    typeChartInstance.value.destroy()
  }
  
  const ctx = typeChart.value.getContext('2d')
  if (!ctx) return
  
  typeChartInstance.value = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: typeData.labels,
      datasets: [{
        data: typeData.values,
        backgroundColor: [
          '#3b82f6',
          '#10b981',
          '#f59e0b',
          '#ef4444'
        ]
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false
    }
  })
}

const applyFilters = () => {
  loadData()
}

const refreshData = () => {
  loadData()
}

const sortBy = (column: string) => {
  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortColumn.value = column
    sortDirection.value = 'asc'
  }
}

const getTypeClass = (type: string) => {
  switch (type) {
    case 'investment_facilitation':
      return 'bg-blue-100 text-blue-800'
    case 'referral':
      return 'bg-green-100 text-green-800'
    case 'performance_bonus':
      return 'bg-yellow-100 text-yellow-800'
    case 'retention_bonus':
      return 'bg-purple-100 text-purple-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const getStatusClass = (status: string) => {
  switch (status.toLowerCase()) {
    case 'paid':
      return 'bg-green-100 text-green-800'
    case 'approved':
      return 'bg-blue-100 text-blue-800'
    case 'pending':
      return 'bg-yellow-100 text-yellow-800'
    case 'cancelled':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const formatCommissionType = (type: string) => {
  return type.split('_').map(word => 
    word.charAt(0).toUpperCase() + word.slice(1)
  ).join(' ')
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString()
}

const approveCommission = async (commission: Commission) => {
  try {
    const response = await fetch(`/api/employee/commissions/${commission.id}/approve`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    })
    
    if (response.ok) {
      await loadData()
    }
  } catch (error) {
    console.error('Failed to approve commission:', error)
  }
}

const viewCommissionDetails = (commission: Commission) => {
  router.visit(`/employee/commissions/${commission.id}`)
}

const exportReport = async () => {
  isExporting.value = true
  try {
    const response = await fetch('/api/employee/reports/commissions/export', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify(filters.value)
    })
    
    if (response.ok) {
      const blob = await response.blob()
      const url = window.URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = `commission-report-${new Date().toISOString().split('T')[0]}.xlsx`
      document.body.appendChild(a)
      a.click()
      window.URL.revokeObjectURL(url)
      document.body.removeChild(a)
    }
  } catch (error) {
    console.error('Failed to export report:', error)
  } finally {
    isExporting.value = false
  }
}

// Lifecycle
onMounted(() => {
  if (!props.initialData) {
    loadData()
  } else {
    nextTick(() => {
      // Initialize charts with sample data if no chart data provided
      updateCharts({
        trends: {
          labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
          totalCommissions: [12000, 15000, 18000, 16000, 20000, 22000],
          paidCommissions: [10000, 13000, 16000, 14000, 18000, 20000]
        },
        byType: {
          labels: ['Investment Facilitation', 'Referral', 'Performance Bonus', 'Retention Bonus'],
          values: [45, 25, 20, 10]
        }
      })
    })
  }
})
</script>