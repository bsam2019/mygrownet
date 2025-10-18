<template>
  <div class="bg-white rounded-lg shadow border p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-medium text-gray-900">Employee Profile</h3>
      <button
        @click="$emit('view-full-profile')"
        class="text-sm text-blue-600 hover:text-blue-800 font-medium"
      >
        View Full Profile
      </button>
    </div>

    <div v-if="isLoading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
    </div>

    <div v-else-if="employee" class="space-y-4">
      <!-- Employee Basic Info -->
      <div class="flex items-center space-x-4">
        <div class="flex-shrink-0">
          <div class="h-12 w-12 bg-blue-100 rounded-full flex items-center justify-center">
            <span class="text-lg font-medium text-blue-600">
              {{ getInitials(employee.firstName, employee.lastName) }}
            </span>
          </div>
        </div>
        <div class="flex-1">
          <h4 class="text-lg font-medium text-gray-900">
            {{ employee.firstName }} {{ employee.lastName }}
          </h4>
          <p class="text-sm text-gray-500">{{ employee.position?.title }}</p>
          <p class="text-xs text-gray-400">{{ employee.department?.name }}</p>
        </div>
        <div class="flex-shrink-0">
          <span
            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
            :class="getStatusClass(employee.employmentStatus)"
          >
            {{ formatStatus(employee.employmentStatus) }}
          </span>
        </div>
      </div>

      <!-- Quick Stats -->
      <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200">
        <div class="text-center">
          <p class="text-2xl font-semibold text-gray-900">{{ employee.yearsOfService || 0 }}</p>
          <p class="text-xs text-gray-500">Years of Service</p>
        </div>
        <div class="text-center">
          <p class="text-2xl font-semibold text-gray-900">{{ formatNumber(employee.performanceScore || 0, 1) }}</p>
          <p class="text-xs text-gray-500">Performance Score</p>
        </div>
      </div>

      <!-- Recent Activity -->
      <div v-if="recentActivity.length > 0" class="pt-4 border-t border-gray-200">
        <h5 class="text-sm font-medium text-gray-900 mb-2">Recent Activity</h5>
        <div class="space-y-2">
          <div
            v-for="activity in recentActivity.slice(0, 3)"
            :key="activity.id"
            class="flex items-center text-sm"
          >
            <div class="flex-shrink-0 w-2 h-2 bg-blue-400 rounded-full mr-3"></div>
            <span class="text-gray-600">{{ activity.description }}</span>
            <span class="ml-auto text-xs text-gray-400">{{ formatDate(activity.date) }}</span>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="pt-4 border-t border-gray-200">
        <div class="flex space-x-2">
          <button
            @click="$emit('update-profile')"
            class="flex-1 bg-blue-600 text-white text-sm font-medium py-2 px-3 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Update Profile
          </button>
          <button
            @click="$emit('view-performance')"
            class="flex-1 bg-gray-100 text-gray-700 text-sm font-medium py-2 px-3 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
          >
            View Performance
          </button>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-8">
      <p class="text-gray-500">No employee profile found</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { formatNumber } from '@/utils/formatting'

interface Employee {
  id: number
  firstName: string
  lastName: string
  employmentStatus: string
  yearsOfService?: number
  performanceScore?: number
  position?: {
    title: string
  }
  department?: {
    name: string
  }
}

interface Activity {
  id: number
  description: string
  date: string
}

// Props
const props = defineProps<{
  employeeId?: number
}>()

// Emits
defineEmits<{
  'view-full-profile': []
  'update-profile': []
  'view-performance': []
}>()

// Reactive data
const isLoading = ref(false)
const employee = ref<Employee | null>(null)
const recentActivity = ref<Activity[]>([])

// Methods
const getInitials = (firstName: string, lastName: string): string => {
  return `${firstName.charAt(0)}${lastName.charAt(0)}`.toUpperCase()
}

const getStatusClass = (status: string): string => {
  switch (status.toLowerCase()) {
    case 'active':
      return 'bg-green-100 text-green-800'
    case 'inactive':
      return 'bg-gray-100 text-gray-800'
    case 'terminated':
      return 'bg-red-100 text-red-800'
    case 'suspended':
      return 'bg-yellow-100 text-yellow-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const formatStatus = (status: string): string => {
  return status.charAt(0).toUpperCase() + status.slice(1).toLowerCase()
}

const formatDate = (dateString: string): string => {
  const date = new Date(dateString)
  const now = new Date()
  const diffTime = Math.abs(now.getTime() - date.getTime())
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  
  if (diffDays === 1) return '1 day ago'
  if (diffDays < 7) return `${diffDays} days ago`
  if (diffDays < 30) return `${Math.ceil(diffDays / 7)} weeks ago`
  return date.toLocaleDateString()
}

const loadEmployeeProfile = async () => {
  if (!props.employeeId) return
  
  isLoading.value = true
  try {
    const response = await fetch(`/api/employee/${props.employeeId}/profile`)
    if (response.ok) {
      const data = await response.json()
      employee.value = data.employee
      recentActivity.value = data.recentActivity || []
    }
  } catch (error) {
    console.error('Failed to load employee profile:', error)
  } finally {
    isLoading.value = false
  }
}

// Lifecycle
onMounted(() => {
  if (props.employeeId) {
    loadEmployeeProfile()
  }
})
</script>