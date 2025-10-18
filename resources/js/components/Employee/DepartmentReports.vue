<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Department Reports</h2>
        <p class="text-gray-600">Comprehensive department performance and analytics</p>
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
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
          <select
            v-model="filters.departmentId"
            @change="applyFilters"
            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">All Departments</option>
            <option
              v-for="department in departments"
              :key="department.id"
              :value="department.id"
            >
              {{ department.name }}
            </option>
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
          <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
          <input
            v-model="filters.startDate"
            @change="applyFilters"
            type="date"
            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          />
        </div>
        <div v-if="filters.period === 'custom'">
          <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
          <input
            v-model="filters.endDate"
            @change="applyFilters"
            type="date"
            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          />
        </div>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="bg-white p-6 rounded-lg shadow border">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Total Employees</p>
            <p class="text-2xl font-semibold text-gray-900">{{ summary.totalEmployees }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow border">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Avg Performance</p>
            <p class="text-2xl font-semibold text-gray-900">{{ formatNumber(summary.avgPerformance, 1) }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow border">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Growth Rate</p>
            <p class="text-2xl font-semibold text-gray-900">{{ formatPercentage(summary.growthRate) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Department Performance Table -->
    <div class="bg-white rounded-lg shadow border">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Department Performance</h3>
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
              v-for="department in sortedDepartmentData"
              :key="department.id"
              class="hover:bg-gray-50"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="text-sm font-medium text-gray-900">{{ department.name }}</div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ department.employeeCount }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatNumber(department.avgPerformance, 1) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatCurrency(department.totalCommissions) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatCurrency(department.avgSalary) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                  :class="getStatusClass(department.status)"
                >
                  {{ department.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button
                  @click="viewDepartmentDetails(department)"
                  class="text-blue-600 hover:text-blue-900"
                >
                  View Details
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
    <div v-if="!isLoading && departmentData.length === 0" class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No department data</h3>
      <p class="mt-1 text-sm text-gray-500">No departments found for the selected criteria.</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { formatCurrency, formatNumber, formatPercentage } from '@/utils/formatting'

interface Department {
  id: number
  name: string
  description: string
  is_active: boolean
}

interface DepartmentData {
  id: number
  name: string
  employeeCount: number
  avgPerformance: number
  totalCommissions: number
  avgSalary: number
  status: string
}

interface Summary {
  totalEmployees: number
  avgPerformance: number
  totalCommissions: number
  growthRate: number
}

interface Filters {
  departmentId: string
  period: string
  startDate: string
  endDate: string
}

// Props
const props = defineProps<{
  departments: Department[]
  initialData?: DepartmentData[]
  initialSummary?: Summary
}>()

// Reactive data
const isLoading = ref(false)
const isExporting = ref(false)
const departmentData = ref<DepartmentData[]>(props.initialData || [])
const summary = ref<Summary>(props.initialSummary || {
  totalEmployees: 0,
  avgPerformance: 0,
  totalCommissions: 0,
  growthRate: 0
})

const filters = ref<Filters>({
  departmentId: '',
  period: 'current_month',
  startDate: '',
  endDate: ''
})

const sortColumn = ref('name')
const sortDirection = ref<'asc' | 'desc'>('asc')

// Table configuration
const tableColumns = [
  { key: 'name', label: 'Department' },
  { key: 'employeeCount', label: 'Employees' },
  { key: 'avgPerformance', label: 'Avg Performance' },
  { key: 'totalCommissions', label: 'Total Commissions' },
  { key: 'avgSalary', label: 'Avg Salary' },
  { key: 'status', label: 'Status' },
  { key: 'actions', label: 'Actions' }
]

// Computed
const sortedDepartmentData = computed(() => {
  const data = [...departmentData.value]
  
  return data.sort((a, b) => {
    const aValue = a[sortColumn.value as keyof DepartmentData]
    const bValue = b[sortColumn.value as keyof DepartmentData]
    
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
    const response = await fetch('/api/employee/reports/departments', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify(filters.value)
    })
    
    if (response.ok) {
      const data = await response.json()
      departmentData.value = data.departments
      summary.value = data.summary
    }
  } catch (error) {
    console.error('Failed to load department reports:', error)
  } finally {
    isLoading.value = false
  }
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

const getStatusClass = (status: string) => {
  switch (status.toLowerCase()) {
    case 'excellent':
      return 'bg-green-100 text-green-800'
    case 'good':
      return 'bg-blue-100 text-blue-800'
    case 'average':
      return 'bg-yellow-100 text-yellow-800'
    case 'poor':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const viewDepartmentDetails = (department: DepartmentData) => {
  router.visit(`/employee/departments/${department.id}`)
}

const exportReport = async () => {
  isExporting.value = true
  try {
    const response = await fetch('/api/employee/reports/departments/export', {
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
      a.download = `department-report-${new Date().toISOString().split('T')[0]}.xlsx`
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
  }
})
</script>