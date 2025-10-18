<template>
  <div class="bg-white rounded-lg shadow border p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-medium text-gray-900">Department Overview</h3>
      <button
        @click="$emit('view-all-departments')"
        class="text-sm text-blue-600 hover:text-blue-800 font-medium"
      >
        View All Departments
      </button>
    </div>

    <div v-if="isLoading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
    </div>

    <div v-else class="space-y-6">
      <!-- Department Statistics -->
      <div class="grid grid-cols-2 gap-4">
        <div class="text-center p-4 bg-blue-50 rounded-lg">
          <p class="text-3xl font-bold text-blue-600">{{ departmentStats.totalDepartments }}</p>
          <p class="text-sm text-gray-600">Total Departments</p>
        </div>
        <div class="text-center p-4 bg-green-50 rounded-lg">
          <p class="text-3xl font-bold text-green-600">{{ departmentStats.averageEmployeesPerDept }}</p>
          <p class="text-sm text-gray-600">Avg Employees/Dept</p>
        </div>
      </div>

      <!-- Department Performance Chart -->
      <div>
        <h4 class="text-sm font-medium text-gray-900 mb-3">Department Performance</h4>
        <div class="space-y-3">
          <div
            v-for="department in departmentPerformance"
            :key="department.id"
            class="flex items-center justify-between"
          >
            <div class="flex items-center space-x-3 flex-1">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                  <span class="text-xs font-medium text-blue-600">
                    {{ department.name.charAt(0) }}
                  </span>
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ department.name }}</p>
                <p class="text-xs text-gray-500">{{ department.employeeCount }} employees</p>
              </div>
            </div>
            <div class="flex items-center space-x-3">
              <div class="flex-1 w-20">
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div
                    class="h-2 rounded-full transition-all duration-300"
                    :class="getPerformanceColor(department.performanceScore)"
                    :style="{ width: `${Math.min(department.performanceScore * 10, 100)}%` }"
                  ></div>
                </div>
              </div>
              <span class="text-sm font-medium text-gray-900 w-8 text-right">
                {{ formatNumber(department.performanceScore, 1) }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Department Heads -->
      <div>
        <h4 class="text-sm font-medium text-gray-900 mb-3">Department Heads</h4>
        <div class="space-y-2">
          <div
            v-for="head in departmentHeads"
            :key="head.id"
            class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg"
          >
            <div class="flex items-center space-x-3">
              <div class="flex-shrink-0">
                <div class="h-8 w-8 bg-gray-200 rounded-full flex items-center justify-center">
                  <span class="text-xs font-medium text-gray-600">
                    {{ getInitials(head.name) }}
                  </span>
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ head.name }}</p>
                <p class="text-xs text-gray-500">{{ head.departmentName }}</p>
              </div>
            </div>
            <div class="text-right">
              <p class="text-sm font-medium text-gray-900">{{ head.teamSize }}</p>
              <p class="text-xs text-gray-500">team members</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Department Changes -->
      <div>
        <h4 class="text-sm font-medium text-gray-900 mb-3">Recent Changes</h4>
        <div class="space-y-2">
          <div
            v-for="change in recentChanges.slice(0, 3)"
            :key="change.id"
            class="flex items-center space-x-3 p-2 bg-gray-50 rounded-lg"
          >
            <div class="flex-shrink-0">
              <div
                class="w-6 h-6 rounded-full flex items-center justify-center"
                :class="getChangeTypeClass(change.type)"
              >
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    v-if="change.type === 'new_department'"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                  />
                  <path
                    v-else-if="change.type === 'head_change'"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"
                  />
                  <path
                    v-else
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                  />
                </svg>
              </div>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm text-gray-900">{{ change.description }}</p>
              <p class="text-xs text-gray-500">{{ formatDate(change.date) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="pt-4 border-t border-gray-200">
        <div class="grid grid-cols-2 gap-2">
          <button
            @click="$emit('create-department')"
            class="bg-blue-600 text-white text-sm font-medium py-2 px-3 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Create Department
          </button>
          <button
            @click="$emit('manage-hierarchy')"
            class="bg-gray-100 text-gray-700 text-sm font-medium py-2 px-3 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
          >
            Manage Hierarchy
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { formatNumber } from '@/utils/formatting'

interface DepartmentStats {
  totalDepartments: number
  averageEmployeesPerDept: number
}

interface DepartmentPerformance {
  id: number
  name: string
  employeeCount: number
  performanceScore: number
}

interface DepartmentHead {
  id: number
  name: string
  departmentName: string
  teamSize: number
}

interface RecentChange {
  id: number
  type: string
  description: string
  date: string
}

// Emits
defineEmits<{
  'view-all-departments': []
  'create-department': []
  'manage-hierarchy': []
}>()

// Reactive data
const isLoading = ref(false)
const departmentStats = ref<DepartmentStats>({
  totalDepartments: 0,
  averageEmployeesPerDept: 0
})
const departmentPerformance = ref<DepartmentPerformance[]>([])
const departmentHeads = ref<DepartmentHead[]>([])
const recentChanges = ref<RecentChange[]>([])

// Methods
const getPerformanceColor = (score: number): string => {
  if (score >= 8) return 'bg-green-500'
  if (score >= 6) return 'bg-yellow-500'
  if (score >= 4) return 'bg-orange-500'
  return 'bg-red-500'
}

const getInitials = (name: string): string => {
  return name.split(' ').map(n => n.charAt(0)).join('').toUpperCase().slice(0, 2)
}

const getChangeTypeClass = (type: string): string => {
  switch (type.toLowerCase()) {
    case 'new_department':
      return 'bg-green-100 text-green-600'
    case 'head_change':
      return 'bg-blue-100 text-blue-600'
    case 'restructure':
      return 'bg-yellow-100 text-yellow-600'
    default:
      return 'bg-gray-100 text-gray-600'
  }
}

const formatDate = (dateString: string): string => {
  const date = new Date(dateString)
  const now = new Date()
  const diffTime = Math.abs(now.getTime() - date.getTime())
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  
  if (diffDays === 1) return '1 day ago'
  if (diffDays < 7) return `${diffDays} days ago`
  if (diffDays < 30) return `${Math.ceil(diffDays / 7)} weeks ago`
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

const loadDepartmentOverview = async () => {
  isLoading.value = true
  try {
    const response = await fetch('/api/admin/department-overview')
    if (response.ok) {
      const data = await response.json()
      departmentStats.value = data.stats
      departmentPerformance.value = data.performance || []
      departmentHeads.value = data.heads || []
      recentChanges.value = data.recentChanges || []
    }
  } catch (error) {
    console.error('Failed to load department overview:', error)
  } finally {
    isLoading.value = false
  }
}

// Lifecycle
onMounted(() => {
  loadDepartmentOverview()
})
</script>