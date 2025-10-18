<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Performance Reports</h2>
        <p class="text-gray-600">Employee performance analytics and trends</p>
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
          <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
          <select
            v-model="filters.positionId"
            @change="applyFilters"
            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">All Positions</option>
            <option
              v-for="position in positions"
              :key="position.id"
              :value="position.id"
            >
              {{ position.title }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Performance Rating</label>
          <select
            v-model="filters.performanceRating"
            @change="applyFilters"
            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">All Ratings</option>
            <option value="excellent">Excellent (9-10)</option>
            <option value="good">Good (7-8)</option>
            <option value="average">Average (5-6)</option>
            <option value="poor">Poor (0-4)</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Period</label>
          <select
            v-model="filters.period"
            @change="applyFilters"
            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="current_quarter">Current Quarter</option>
            <option value="last_quarter">Last Quarter</option>
            <option value="current_year">Current Year</option>
            <option value="last_year">Last Year</option>
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

    <!-- Performance Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="bg-white p-6 rounded-lg shadow border">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Avg Performance Score</p>
            <p class="text-2xl font-semibold text-gray-900">{{ formatNumber(overview.avgScore, 1) }}</p>
            <p class="text-sm text-gray-500">out of 10</p>
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
            <p class="text-sm font-medium text-gray-500">Goal Achievement</p>
            <p class="text-2xl font-semibold text-gray-900">{{ formatPercentage(overview.goalAchievement) }}</p>
            <p class="text-sm text-gray-500">average rate</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow border">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Top Performers</p>
            <p class="text-2xl font-semibold text-gray-900">{{ overview.topPerformers }}</p>
            <p class="text-sm text-gray-500">employees (9+ score)</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow border">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-red-100 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Needs Improvement</p>
            <p class="text-2xl font-semibold text-gray-900">{{ overview.needsImprovement }}</p>
            <p class="text-sm text-gray-500">employees (&lt;5 score)</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Performance Chart -->
    <div class="bg-white p-6 rounded-lg shadow border">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Performance Trends</h3>
      <div class="h-64">
        <canvas ref="performanceChart"></canvas>
      </div>
    </div>

    <!-- Employee Performance Table -->
    <div class="bg-white rounded-lg shadow border">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Employee Performance Details</h3>
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
              v-for="employee in sortedEmployeeData"
              :key="employee.id"
              class="hover:bg-gray-50"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                      <span class="text-sm font-medium text-gray-700">
                        {{ employee.firstName.charAt(0) }}{{ employee.lastName.charAt(0) }}
                      </span>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">
                      {{ employee.firstName }} {{ employee.lastName }}
                    </div>
                    <div class="text-sm text-gray-500">{{ employee.position }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ employee.department }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="text-sm font-medium text-gray-900">{{ formatNumber(employee.overallScore, 1) }}</div>
                  <div class="ml-2 w-16 bg-gray-200 rounded-full h-2">
                    <div
                      class="h-2 rounded-full"
                      :class="getScoreColor(employee.overallScore)"
                      :style="{ width: `${(employee.overallScore / 10) * 100}%` }"
                    ></div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatPercentage(employee.goalAchievement) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ employee.investmentsFacilitated }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatCurrency(employee.commissionGenerated) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatPercentage(employee.clientRetention) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                  :class="getPerformanceClass(employee.overallScore)"
                >
                  {{ getPerformanceLabel(employee.overallScore) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button
                  @click="viewEmployeeDetails(employee)"
                  class="text-blue-600 hover:text-blue-900 mr-3"
                >
                  View
                </button>
                <button
                  @click="createPerformancePlan(employee)"
                  class="text-green-600 hover:text-green-900"
                >
                  Plan
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
    <div v-if="!isLoading && employeeData.length === 0" class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No performance data</h3>
      <p class="mt-1 text-sm text-gray-500">No employee performance data found for the selected criteria.</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'
import { Chart, registerables } from 'chart.js'
import { formatCurrency, formatNumber, formatPercentage } from '@/utils/formatting'

Chart.register(...registerables)

interface Department {
  id: number
  name: string
}

interface Position {
  id: number
  title: string
}

interface EmployeePerformance {
  id: number
  firstName: string
  lastName: string
  department: string
  position: string
  overallScore: number
  goalAchievement: number
  investmentsFacilitated: number
  commissionGenerated: number
  clientRetention: number
}

interface PerformanceOverview {
  avgScore: number
  goalAchievement: number
  topPerformers: number
  needsImprovement: number
}

interface Filters {
  departmentId: string
  positionId: string
  performanceRating: string
  period: string
  startDate: string
  endDate: string
}

// Props
const props = defineProps<{
  departments: Department[]
  positions: Position[]
  initialData?: EmployeePerformance[]
  initialOverview?: PerformanceOverview
}>()

// Reactive data
const isLoading = ref(false)
const isExporting = ref(false)
const employeeData = ref<EmployeePerformance[]>(props.initialData || [])
const overview = ref<PerformanceOverview>(props.initialOverview || {
  avgScore: 0,
  goalAchievement: 0,
  topPerformers: 0,
  needsImprovement: 0
})

const filters = ref<Filters>({
  departmentId: '',
  positionId: '',
  performanceRating: '',
  period: 'current_quarter',
  startDate: '',
  endDate: ''
})

const sortColumn = ref('overallScore')
const sortDirection = ref<'asc' | 'desc'>('desc')
const performanceChart = ref<HTMLCanvasElement>()
const chartInstance = ref<Chart>()

// Table configuration
const tableColumns = [
  { key: 'name', label: 'Employee' },
  { key: 'department', label: 'Department' },
  { key: 'overallScore', label: 'Performance Score' },
  { key: 'goalAchievement', label: 'Goal Achievement' },
  { key: 'investmentsFacilitated', label: 'Investments' },
  { key: 'commissionGenerated', label: 'Commission' },
  { key: 'clientRetention', label: 'Client Retention' },
  { key: 'status', label: 'Status' },
  { key: 'actions', label: 'Actions' }
]

// Computed
const sortedEmployeeData = computed(() => {
  const data = [...employeeData.value]
  
  return data.sort((a, b) => {
    let aValue: any = a[sortColumn.value as keyof EmployeePerformance]
    let bValue: any = b[sortColumn.value as keyof EmployeePerformance]
    
    if (sortColumn.value === 'name') {
      aValue = `${a.firstName} ${a.lastName}`
      bValue = `${b.firstName} ${b.lastName}`
    }
    
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
    const response = await fetch('/api/employee/reports/performance', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify(filters.value)
    })
    
    if (response.ok) {
      const data = await response.json()
      employeeData.value = data.employees
      overview.value = data.overview
      await nextTick()
      updateChart(data.chartData)
    }
  } catch (error) {
    console.error('Failed to load performance reports:', error)
  } finally {
    isLoading.value = false
  }
}

const updateChart = (chartData: any) => {
  if (!performanceChart.value) return
  
  if (chartInstance.value) {
    chartInstance.value.destroy()
  }
  
  const ctx = performanceChart.value.getContext('2d')
  if (!ctx) return
  
  chartInstance.value = new Chart(ctx, {
    type: 'line',
    data: {
      labels: chartData.labels,
      datasets: [
        {
          label: 'Average Performance Score',
          data: chartData.performanceScores,
          borderColor: '#3b82f6',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          tension: 0.4
        },
        {
          label: 'Goal Achievement Rate',
          data: chartData.goalAchievement,
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
          beginAtZero: true,
          max: 100
        }
      }
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

const getScoreColor = (score: number) => {
  if (score >= 9) return 'bg-green-500'
  if (score >= 7) return 'bg-blue-500'
  if (score >= 5) return 'bg-yellow-500'
  return 'bg-red-500'
}

const getPerformanceClass = (score: number) => {
  if (score >= 9) return 'bg-green-100 text-green-800'
  if (score >= 7) return 'bg-blue-100 text-blue-800'
  if (score >= 5) return 'bg-yellow-100 text-yellow-800'
  return 'bg-red-100 text-red-800'
}

const getPerformanceLabel = (score: number) => {
  if (score >= 9) return 'Excellent'
  if (score >= 7) return 'Good'
  if (score >= 5) return 'Average'
  return 'Needs Improvement'
}

const viewEmployeeDetails = (employee: EmployeePerformance) => {
  router.visit(`/employee/employees/${employee.id}`)
}

const createPerformancePlan = (employee: EmployeePerformance) => {
  router.visit(`/employee/performance/plan/${employee.id}`)
}

const exportReport = async () => {
  isExporting.value = true
  try {
    const response = await fetch('/api/employee/reports/performance/export', {
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
      a.download = `performance-report-${new Date().toISOString().split('T')[0]}.xlsx`
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
      // Initialize chart with sample data if no chart data provided
      updateChart({
        labels: ['Q1', 'Q2', 'Q3', 'Q4'],
        performanceScores: [7.2, 7.8, 8.1, 8.3],
        goalAchievement: [65, 72, 78, 82]
      })
    })
  }
})
</script>