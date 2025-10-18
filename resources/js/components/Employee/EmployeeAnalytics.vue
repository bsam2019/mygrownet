<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Employee Analytics</h2>
        <p class="text-gray-600">Performance trends and organizational metrics</p>
      </div>
      <div class="flex space-x-3">
        <button
          @click="exportAnalytics"
          :disabled="isExporting"
          class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          {{ isExporting ? 'Exporting...' : 'Export Analytics' }}
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

    <!-- Time Period Selector -->
    <div class="bg-white p-4 rounded-lg shadow border">
      <div class="flex items-center space-x-4">
        <label class="text-sm font-medium text-gray-700">Time Period:</label>
        <select
          v-model="selectedPeriod"
          @change="loadAnalytics"
          class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="last_30_days">Last 30 Days</option>
          <option value="last_90_days">Last 90 Days</option>
          <option value="last_6_months">Last 6 Months</option>
          <option value="last_year">Last Year</option>
          <option value="custom">Custom Range</option>
        </select>
        <div v-if="selectedPeriod === 'custom'" class="flex items-center space-x-2">
          <input
            v-model="customStartDate"
            @change="loadAnalytics"
            type="date"
            class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          />
          <span class="text-gray-500">to</span>
          <input
            v-model="customEndDate"
            @change="loadAnalytics"
            type="date"
            class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          />
        </div>
      </div>
    </div>

    <!-- Key Performance Indicators -->
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
            <p class="text-sm font-medium text-gray-500">Employee Productivity</p>
            <p class="text-2xl font-semibold text-gray-900">{{ formatNumber(kpis.productivity, 1) }}%</p>
            <p class="text-sm" :class="getTrendClass(kpis.productivityTrend)">
              {{ formatTrend(kpis.productivityTrend) }}
            </p>
          </div>
        </div>
      </div>

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
            <p class="text-sm font-medium text-gray-500">Revenue per Employee</p>
            <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(kpis.revenuePerEmployee) }}</p>
            <p class="text-sm" :class="getTrendClass(kpis.revenuePerEmployeeTrend)">
              {{ formatTrend(kpis.revenuePerEmployeeTrend) }}
            </p>
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
            <p class="text-sm font-medium text-gray-500">Employee Retention</p>
            <p class="text-2xl font-semibold text-gray-900">{{ formatPercentage(kpis.retention) }}</p>
            <p class="text-sm" :class="getTrendClass(kpis.retentionTrend)">
              {{ formatTrend(kpis.retentionTrend) }}
            </p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow border">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Avg Performance Score</p>
            <p class="text-2xl font-semibold text-gray-900">{{ formatNumber(kpis.avgPerformance, 1) }}</p>
            <p class="text-sm" :class="getTrendClass(kpis.avgPerformanceTrend)">
              {{ formatTrend(kpis.avgPerformanceTrend) }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Performance Trends Chart -->
    <div class="bg-white p-6 rounded-lg shadow border">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium text-gray-900">Performance Trends</h3>
        <div class="flex space-x-2">
          <button
            v-for="metric in chartMetrics"
            :key="metric.key"
            @click="toggleMetric(metric.key)"
            class="px-3 py-1 text-sm rounded-full border"
            :class="activeMetrics.includes(metric.key) 
              ? 'bg-blue-100 text-blue-800 border-blue-300' 
              : 'bg-gray-100 text-gray-600 border-gray-300'"
          >
            {{ metric.label }}
          </button>
        </div>
      </div>
      <div class="h-80">
        <canvas ref="trendsChart"></canvas>
      </div>
    </div>

    <!-- Department Performance Comparison -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-white p-6 rounded-lg shadow border">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Department Performance</h3>
        <div class="h-64">
          <canvas ref="departmentChart"></canvas>
        </div>
      </div>
      <div class="bg-white p-6 rounded-lg shadow border">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Investment Performance by Field Agents</h3>
        <div class="h-64">
          <canvas ref="investmentChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Employee Performance Distribution -->
    <div class="bg-white p-6 rounded-lg shadow border">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Performance Distribution</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <h4 class="text-sm font-medium text-gray-700 mb-3">Performance Score Distribution</h4>
          <div class="space-y-3">
            <div
              v-for="range in performanceDistribution"
              :key="range.label"
              class="flex items-center justify-between"
            >
              <div class="flex items-center">
                <div
                  class="w-4 h-4 rounded mr-3"
                  :style="{ backgroundColor: range.color }"
                ></div>
                <span class="text-sm text-gray-600">{{ range.label }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-sm font-medium text-gray-900">{{ range.count }}</span>
                <span class="text-xs text-gray-500">({{ formatPercentage(range.percentage) }})</span>
              </div>
            </div>
          </div>
        </div>
        <div>
          <h4 class="text-sm font-medium text-gray-700 mb-3">Goal Achievement Distribution</h4>
          <div class="space-y-3">
            <div
              v-for="range in goalDistribution"
              :key="range.label"
              class="flex items-center justify-between"
            >
              <div class="flex items-center">
                <div
                  class="w-4 h-4 rounded mr-3"
                  :style="{ backgroundColor: range.color }"
                ></div>
                <span class="text-sm text-gray-600">{{ range.label }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-sm font-medium text-gray-900">{{ range.count }}</span>
                <span class="text-xs text-gray-500">({{ formatPercentage(range.percentage) }})</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Top Performers and Improvement Opportunities -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-white p-6 rounded-lg shadow border">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Top Performers</h3>
        <div class="space-y-4">
          <div
            v-for="(performer, index) in topPerformers"
            :key="performer.id"
            class="flex items-center justify-between p-3 bg-green-50 rounded-lg"
          >
            <div class="flex items-center">
              <div class="flex-shrink-0 h-8 w-8 bg-green-100 rounded-full flex items-center justify-center">
                <span class="text-sm font-medium text-green-600">{{ index + 1 }}</span>
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-900">{{ performer.name }}</p>
                <p class="text-xs text-gray-500">{{ performer.department }} • {{ performer.position }}</p>
              </div>
            </div>
            <div class="text-right">
              <p class="text-sm font-medium text-gray-900">{{ formatNumber(performer.score, 1) }}</p>
              <p class="text-xs text-gray-500">{{ formatCurrency(performer.revenue) }} revenue</p>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow border">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Improvement Opportunities</h3>
        <div class="space-y-4">
          <div
            v-for="opportunity in improvementOpportunities"
            :key="opportunity.id"
            class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg"
          >
            <div class="flex items-center">
              <div class="flex-shrink-0 h-8 w-8 bg-yellow-100 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-900">{{ opportunity.name }}</p>
                <p class="text-xs text-gray-500">{{ opportunity.department }} • {{ opportunity.issue }}</p>
              </div>
            </div>
            <div class="text-right">
              <p class="text-sm font-medium text-gray-900">{{ formatNumber(opportunity.score, 1) }}</p>
              <p class="text-xs text-gray-500">{{ opportunity.recommendation }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from 'vue'
import { Chart, registerables } from 'chart.js'
import { formatCurrency, formatNumber, formatPercentage } from '@/utils/formatting'

Chart.register(...registerables)

interface KPIs {
  productivity: number
  productivityTrend: number
  revenuePerEmployee: number
  revenuePerEmployeeTrend: number
  retention: number
  retentionTrend: number
  avgPerformance: number
  avgPerformanceTrend: number
}

interface TopPerformer {
  id: number
  name: string
  department: string
  position: string
  score: number
  revenue: number
}

interface ImprovementOpportunity {
  id: number
  name: string
  department: string
  issue: string
  score: number
  recommendation: string
}

interface DistributionRange {
  label: string
  count: number
  percentage: number
  color: string
}

// Props
const props = defineProps<{
  initialKPIs?: KPIs
  initialTopPerformers?: TopPerformer[]
  initialImprovementOpportunities?: ImprovementOpportunity[]
}>()

// Reactive data
const isLoading = ref(false)
const isExporting = ref(false)
const selectedPeriod = ref('last_30_days')
const customStartDate = ref('')
const customEndDate = ref('')

const kpis = ref<KPIs>(props.initialKPIs || {
  productivity: 0,
  productivityTrend: 0,
  revenuePerEmployee: 0,
  revenuePerEmployeeTrend: 0,
  retention: 0,
  retentionTrend: 0,
  avgPerformance: 0,
  avgPerformanceTrend: 0
})

const topPerformers = ref<TopPerformer[]>(props.initialTopPerformers || [])
const improvementOpportunities = ref<ImprovementOpportunity[]>(props.initialImprovementOpportunities || [])

const performanceDistribution = ref<DistributionRange[]>([
  { label: 'Excellent (9-10)', count: 0, percentage: 0, color: '#10b981' },
  { label: 'Good (7-8)', count: 0, percentage: 0, color: '#3b82f6' },
  { label: 'Average (5-6)', count: 0, percentage: 0, color: '#f59e0b' },
  { label: 'Poor (0-4)', count: 0, percentage: 0, color: '#ef4444' }
])

const goalDistribution = ref<DistributionRange[]>([
  { label: 'Exceeds (>100%)', count: 0, percentage: 0, color: '#10b981' },
  { label: 'Meets (80-100%)', count: 0, percentage: 0, color: '#3b82f6' },
  { label: 'Partial (50-79%)', count: 0, percentage: 0, color: '#f59e0b' },
  { label: 'Below (<50%)', count: 0, percentage: 0, color: '#ef4444' }
])

// Chart references
const trendsChart = ref<HTMLCanvasElement>()
const departmentChart = ref<HTMLCanvasElement>()
const investmentChart = ref<HTMLCanvasElement>()
const trendsChartInstance = ref<Chart>()
const departmentChartInstance = ref<Chart>()
const investmentChartInstance = ref<Chart>()

// Chart configuration
const chartMetrics = [
  { key: 'performance', label: 'Performance Score' },
  { key: 'productivity', label: 'Productivity' },
  { key: 'revenue', label: 'Revenue' },
  { key: 'retention', label: 'Retention' }
]

const activeMetrics = ref(['performance', 'productivity'])

// Methods
const loadAnalytics = async () => {
  isLoading.value = true
  try {
    const response = await fetch('/api/employee/analytics', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({
        period: selectedPeriod.value,
        startDate: customStartDate.value,
        endDate: customEndDate.value,
        metrics: activeMetrics.value
      })
    })
    
    if (response.ok) {
      const data = await response.json()
      kpis.value = data.kpis
      topPerformers.value = data.topPerformers
      improvementOpportunities.value = data.improvementOpportunities
      performanceDistribution.value = data.performanceDistribution
      goalDistribution.value = data.goalDistribution
      
      await nextTick()
      updateCharts(data.chartData)
    }
  } catch (error) {
    console.error('Failed to load analytics:', error)
  } finally {
    isLoading.value = false
  }
}

const updateCharts = (chartData: any) => {
  updateTrendsChart(chartData.trends)
  updateDepartmentChart(chartData.departments)
  updateInvestmentChart(chartData.investments)
}

const updateTrendsChart = (trendsData: any) => {
  if (!trendsChart.value) return
  
  if (trendsChartInstance.value) {
    trendsChartInstance.value.destroy()
  }
  
  const ctx = trendsChart.value.getContext('2d')
  if (!ctx) return
  
  const datasets = []
  const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444']
  
  activeMetrics.value.forEach((metric, index) => {
    if (trendsData[metric]) {
      datasets.push({
        label: chartMetrics.find(m => m.key === metric)?.label || metric,
        data: trendsData[metric],
        borderColor: colors[index % colors.length],
        backgroundColor: colors[index % colors.length] + '20',
        tension: 0.4
      })
    }
  })
  
  trendsChartInstance.value = new Chart(ctx, {
    type: 'line',
    data: {
      labels: trendsData.labels,
      datasets
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

const updateDepartmentChart = (departmentData: any) => {
  if (!departmentChart.value) return
  
  if (departmentChartInstance.value) {
    departmentChartInstance.value.destroy()
  }
  
  const ctx = departmentChart.value.getContext('2d')
  if (!ctx) return
  
  departmentChartInstance.value = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: departmentData.labels,
      datasets: [{
        label: 'Average Performance Score',
        data: departmentData.scores,
        backgroundColor: '#3b82f6',
        borderColor: '#2563eb',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          max: 10
        }
      }
    }
  })
}

const updateInvestmentChart = (investmentData: any) => {
  if (!investmentChart.value) return
  
  if (investmentChartInstance.value) {
    investmentChartInstance.value.destroy()
  }
  
  const ctx = investmentChart.value.getContext('2d')
  if (!ctx) return
  
  investmentChartInstance.value = new Chart(ctx, {
    type: 'scatter',
    data: {
      datasets: [{
        label: 'Investment Performance',
        data: investmentData.points,
        backgroundColor: '#10b981',
        borderColor: '#059669'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        x: {
          title: {
            display: true,
            text: 'Investments Facilitated'
          }
        },
        y: {
          title: {
            display: true,
            text: 'Commission Generated (K)'
          }
        }
      }
    }
  })
}

const toggleMetric = (metric: string) => {
  const index = activeMetrics.value.indexOf(metric)
  if (index > -1) {
    if (activeMetrics.value.length > 1) {
      activeMetrics.value.splice(index, 1)
    }
  } else {
    activeMetrics.value.push(metric)
  }
  loadAnalytics()
}

const refreshData = () => {
  loadAnalytics()
}

const getTrendClass = (trend: number) => {
  if (trend > 0) return 'text-green-600'
  if (trend < 0) return 'text-red-600'
  return 'text-gray-500'
}

const formatTrend = (trend: number) => {
  const sign = trend > 0 ? '+' : ''
  return `${sign}${trend.toFixed(1)}% from last period`
}

const exportAnalytics = async () => {
  isExporting.value = true
  try {
    const response = await fetch('/api/employee/analytics/export', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({
        period: selectedPeriod.value,
        startDate: customStartDate.value,
        endDate: customEndDate.value,
        metrics: activeMetrics.value
      })
    })
    
    if (response.ok) {
      const blob = await response.blob()
      const url = window.URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = `employee-analytics-${new Date().toISOString().split('T')[0]}.xlsx`
      document.body.appendChild(a)
      a.click()
      window.URL.revokeObjectURL(url)
      document.body.removeChild(a)
    }
  } catch (error) {
    console.error('Failed to export analytics:', error)
  } finally {
    isExporting.value = false
  }
}

// Lifecycle
onMounted(() => {
  if (!props.initialKPIs) {
    loadAnalytics()
  } else {
    nextTick(() => {
      // Initialize charts with sample data
      updateCharts({
        trends: {
          labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
          performance: [7.2, 7.5, 7.8, 8.1],
          productivity: [82, 85, 88, 90],
          revenue: [15000, 16500, 17200, 18000],
          retention: [92, 93, 94, 95]
        },
        departments: {
          labels: ['Sales', 'Marketing', 'Operations', 'Support'],
          scores: [8.2, 7.8, 7.5, 8.0]
        },
        investments: {
          points: [
            { x: 5, y: 12000 },
            { x: 8, y: 18000 },
            { x: 12, y: 25000 },
            { x: 15, y: 32000 },
            { x: 20, y: 45000 }
          ]
        }
      })
    })
  }
})
</script>