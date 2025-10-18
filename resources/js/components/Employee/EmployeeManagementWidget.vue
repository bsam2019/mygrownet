<template>
  <div class="bg-white rounded-lg shadow border p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-medium text-gray-900">Employee Management</h3>
      <div class="flex items-center space-x-2">
        <button
          @click="refreshData"
          class="p-1.5 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100"
          :class="{ 'animate-spin': isRefreshing }"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
        </button>
        <button
          @click="$emit('view-all-employees')"
          class="text-sm text-blue-600 hover:text-blue-800 font-medium"
        >
          View All Employees
        </button>
      </div>
    </div>

    <div v-if="isLoading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
    </div>

    <div v-else class="space-y-6">
      <!-- Employee Statistics with Trends -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="text-center p-3 bg-blue-50 rounded-lg relative">
          <p class="text-2xl font-bold text-blue-600">{{ props.stats?.totalEmployees || 0 }}</p>
          <p class="text-xs text-gray-600">Total Employees</p>
          <div v-if="props.stats?.employeeGrowth" class="absolute top-2 right-2">
            <span :class="getTrendClass(props.stats.employeeGrowth)" class="text-xs font-medium">
              {{ formatTrend(props.stats.employeeGrowth) }}
            </span>
          </div>
        </div>
        <div class="text-center p-3 bg-green-50 rounded-lg relative">
          <p class="text-2xl font-bold text-green-600">{{ props.stats?.activeEmployees || 0 }}</p>
          <p class="text-xs text-gray-600">Active</p>
          <div v-if="props.stats?.activeGrowth" class="absolute top-2 right-2">
            <span :class="getTrendClass(props.stats.activeGrowth)" class="text-xs font-medium">
              {{ formatTrend(props.stats.activeGrowth) }}
            </span>
          </div>
        </div>
        <div class="text-center p-3 bg-yellow-50 rounded-lg relative">
          <p class="text-2xl font-bold text-yellow-600">{{ props.stats?.newHires || 0 }}</p>
          <p class="text-xs text-gray-600">New Hires</p>
          <p class="text-xs text-gray-500 mt-1">This Month</p>
        </div>
        <div class="text-center p-3 bg-purple-50 rounded-lg relative">
          <p class="text-2xl font-bold text-purple-600">{{ props.stats?.departments || 0 }}</p>
          <p class="text-xs text-gray-600">Departments</p>
          <p class="text-xs text-gray-500 mt-1">{{ props.stats?.avgEmployeesPerDept || 0 }} avg/dept</p>
        </div>
      </div>

      <!-- Performance Indicators -->
      <div class="bg-gray-50 rounded-lg p-4">
        <h4 class="text-sm font-medium text-gray-900 mb-3">Key Performance Indicators</h4>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
          <div class="text-center">
            <div class="text-lg font-semibold text-gray-900">{{ props.stats?.turnoverRate || 0 }}%</div>
            <div class="text-xs text-gray-600">Turnover Rate</div>
            <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
              <div 
                class="h-1.5 rounded-full transition-all duration-300"
                :class="getTurnoverColor(props.stats?.turnoverRate || 0)"
                :style="{ width: Math.min((props.stats?.turnoverRate || 0), 100) + '%' }"
              ></div>
            </div>
          </div>
          <div class="text-center">
            <div class="text-lg font-semibold text-gray-900">{{ props.stats?.avgPerformanceScore || 0 }}/5</div>
            <div class="text-xs text-gray-600">Avg Performance</div>
            <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
              <div 
                class="bg-green-500 h-1.5 rounded-full transition-all duration-300"
                :style="{ width: ((props.stats?.avgPerformanceScore || 0) / 5) * 100 + '%' }"
              ></div>
            </div>
          </div>
          <div class="text-center">
            <div class="text-lg font-semibold text-gray-900">{{ formatCurrency(props.stats?.totalCommissions || 0) }}</div>
            <div class="text-xs text-gray-600">Total Commissions</div>
            <div class="text-xs text-gray-500 mt-1">This Quarter</div>
          </div>
        </div>
      </div>

      <!-- Recent Employee Activities -->
      <div>
        <h4 class="text-sm font-medium text-gray-900 mb-3">Recent Activities</h4>
        <div class="space-y-3">
          <div
            v-for="activity in (props.recentActivities || []).slice(0, 5)"
            :key="activity.id"
            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
          >
            <div class="flex items-center space-x-3">
              <div class="flex-shrink-0">
                <div
                  class="w-8 h-8 rounded-full flex items-center justify-center"
                  :class="getActivityIconClass(activity.type)"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                      v-if="activity.type === 'hire'"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"
                    />
                    <path
                      v-else-if="activity.type === 'promotion'"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
                    />
                    <path
                      v-else-if="activity.type === 'performance'"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                    />
                    <path
                      v-else
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                    />
                  </svg>
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ activity.employeeName }}</p>
                <p class="text-xs text-gray-500">{{ activity.description }}</p>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <span
                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                :class="getActivityTypeClass(activity.type)"
              >
                {{ formatActivityType(activity.type) }}
              </span>
              <span class="text-xs text-gray-400">{{ formatDate(activity.date) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Department Overview -->
      <div>
        <h4 class="text-sm font-medium text-gray-900 mb-3">Department Overview</h4>
        <div class="space-y-2">
          <div
            v-for="department in (props.departmentOverview || []).slice(0, 4)"
            :key="department.id"
            class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg"
          >
            <div class="flex items-center space-x-3">
              <div class="flex-shrink-0">
                <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
                  <span class="text-xs font-medium text-blue-600">
                    {{ department.name.charAt(0) }}
                  </span>
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ department.name }}</p>
                <p class="text-xs text-gray-500">{{ department.headName || 'No head assigned' }}</p>
              </div>
            </div>
            <div class="text-right">
              <p class="text-sm font-medium text-gray-900">{{ department.employeeCount }}</p>
              <p class="text-xs text-gray-500">employees</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="pt-4 border-t border-gray-200">
        <div class="grid grid-cols-2 gap-2">
          <button
            @click="$emit('add-employee')"
            class="bg-blue-600 text-white text-sm font-medium py-2 px-3 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Add Employee
          </button>
          <button
            @click="$emit('view-reports')"
            class="bg-gray-100 text-gray-700 text-sm font-medium py-2 px-3 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
          >
            View Reports
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

// Props
const props = defineProps({
  stats: {
    type: Object,
    default: () => ({})
  },
  recentActivities: {
    type: Array,
    default: () => []
  },
  departmentOverview: {
    type: Array,
    default: () => []
  }
})

// Emits
defineEmits(['view-all-employees', 'add-employee', 'view-reports'])

// Reactive data
const isLoading = ref(false)
const isRefreshing = ref(false)

// Methods
const refreshData = async () => {
  isRefreshing.value = true
  try {
    // Emit refresh event to parent component
    emit('refresh-data')
    // Simulate API call delay
    await new Promise(resolve => setTimeout(resolve, 1000))
  } finally {
    isRefreshing.value = false
  }
}

const getTrendClass = (value) => {
  if (value > 0) return 'text-green-600'
  if (value < 0) return 'text-red-600'
  return 'text-gray-500'
}

const formatTrend = (value) => {
  const sign = value > 0 ? '+' : ''
  return `${sign}${value}%`
}

const getTurnoverColor = (rate) => {
  if (rate <= 5) return 'bg-green-500'
  if (rate <= 10) return 'bg-yellow-500'
  return 'bg-red-500'
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount)
}
const getActivityIconClass = (type) => {
  switch (type.toLowerCase()) {
    case 'hire':
      return 'bg-green-100 text-green-600'
    case 'promotion':
      return 'bg-blue-100 text-blue-600'
    case 'performance':
      return 'bg-purple-100 text-purple-600'
    case 'termination':
      return 'bg-red-100 text-red-600'
    default:
      return 'bg-gray-100 text-gray-600'
  }
}

const getActivityTypeClass = (type) => {
  switch (type.toLowerCase()) {
    case 'hire':
      return 'bg-green-100 text-green-800'
    case 'promotion':
      return 'bg-blue-100 text-blue-800'
    case 'performance':
      return 'bg-purple-100 text-purple-800'
    case 'termination':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const formatActivityType = (type) => {
  return type.charAt(0).toUpperCase() + type.slice(1).toLowerCase()
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffTime = Math.abs(now.getTime() - date.getTime())
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  
  if (diffDays === 1) return '1d ago'
  if (diffDays < 7) return `${diffDays}d ago`
  if (diffDays < 30) return `${Math.ceil(diffDays / 7)}w ago`
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

// Lifecycle
onMounted(() => {
  isLoading.value = false
})
</script>