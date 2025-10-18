<template>
  <div class="org-node">
    <!-- Employee Node -->
    <div class="flex flex-col items-center">
      <!-- Employee Card -->
      <div
        class="bg-white border-2 border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow cursor-pointer min-w-[200px]"
        :class="{
          'border-blue-500 bg-blue-50': isHighlighted,
          'border-gray-300': !isHighlighted
        }"
        @click="$emit('employee-click', employee)"
      >
        <!-- Avatar -->
        <div class="flex justify-center mb-3">
          <div class="h-12 w-12 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm">
            {{ getInitials(employee.first_name, employee.last_name) }}
          </div>
        </div>

        <!-- Employee Info -->
        <div class="text-center">
          <h4 class="text-sm font-semibold text-gray-900 mb-1">
            {{ employee.first_name }} {{ employee.last_name }}
          </h4>
          <p class="text-xs text-gray-600 mb-1">{{ employee.position?.title || 'No Position' }}</p>
          <p class="text-xs text-gray-500">{{ employee.department?.name || 'No Department' }}</p>
          
          <!-- Status Badge -->
          <div class="mt-2">
            <span
              class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
              :class="getStatusBadgeClass(employee.employment_status)"
            >
              {{ formatStatus(employee.employment_status) }}
            </span>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="flex justify-center mt-3 space-x-2">
          <button
            class="p-1 text-gray-400 hover:text-blue-600 transition-colors"
            title="View Profile"
            @click.stop="$emit('employee-click', employee)"
          >
            <EyeIcon class="h-4 w-4" />
          </button>
          <button
            class="p-1 text-gray-400 hover:text-green-600 transition-colors"
            title="Send Message"
            @click.stop="sendMessage(employee)"
          >
            <ChatBubbleLeftIcon class="h-4 w-4" />
          </button>
        </div>
      </div>

      <!-- Connection Line -->
      <div v-if="employee.direct_reports && employee.direct_reports.length > 0" class="relative">
        <!-- Vertical line down -->
        <div class="w-px h-8 bg-gray-300 mx-auto"></div>
        
        <!-- Horizontal line -->
        <div v-if="employee.direct_reports.length > 1" class="relative">
          <div class="h-px bg-gray-300" :style="{ width: getHorizontalLineWidth() + 'px' }"></div>
          <!-- Vertical lines to children -->
          <div
            v-for="(report, index) in employee.direct_reports"
            :key="report.id"
            class="absolute top-0 w-px h-8 bg-gray-300"
            :style="{ left: getChildLinePosition(index) + 'px' }"
          ></div>
        </div>
        <div v-else class="w-px h-8 bg-gray-300 mx-auto"></div>
      </div>
    </div>

    <!-- Direct Reports -->
    <div v-if="employee.direct_reports && employee.direct_reports.length > 0" class="flex justify-center mt-0">
      <div class="flex space-x-8">
        <OrgNode
          v-for="report in employee.direct_reports"
          :key="report.id"
          :employee="report"
          :show-vacant="showVacant"
          @employee-click="$emit('employee-click', $event)"
        />
      </div>
    </div>

    <!-- Vacant Position Placeholder -->
    <div v-if="showVacant && hasVacantPositions" class="flex justify-center mt-8">
      <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-4 min-w-[200px]">
        <div class="flex justify-center mb-3">
          <div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center">
            <PlusIcon class="h-6 w-6 text-gray-400" />
          </div>
        </div>
        <div class="text-center">
          <h4 class="text-sm font-medium text-gray-500 mb-1">Vacant Position</h4>
          <p class="text-xs text-gray-400">Available for hire</p>
          <button class="mt-2 text-xs text-blue-600 hover:text-blue-800 transition-colors">
            Post Job
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import {
  EyeIcon,
  ChatBubbleLeftIcon,
  PlusIcon
} from '@heroicons/vue/24/outline'

// Types
interface Employee {
  id: number
  employee_number: string
  first_name: string
  last_name: string
  email: string
  employment_status: 'active' | 'inactive' | 'terminated' | 'suspended'
  department?: {
    id: number
    name: string
  }
  position?: {
    id: number
    title: string
  }
  direct_reports?: Employee[]
}

interface Props {
  employee: Employee
  showVacant: boolean
  isHighlighted?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  isHighlighted: false
})

// Emits
const emit = defineEmits<{
  'employee-click': [employee: Employee]
}>()

// Computed
const hasVacantPositions = computed(() => {
  // This would be determined by business logic
  // For now, we'll show vacant positions for managers with less than 3 direct reports
  return props.employee.direct_reports && props.employee.direct_reports.length < 3
})

// Methods
const getInitials = (firstName: string, lastName: string): string => {
  return `${firstName.charAt(0)}${lastName.charAt(0)}`.toUpperCase()
}

const formatStatus = (status: string): string => {
  return status.charAt(0).toUpperCase() + status.slice(1)
}

const getStatusBadgeClass = (status: string): string => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    inactive: 'bg-gray-100 text-gray-800',
    terminated: 'bg-red-100 text-red-800',
    suspended: 'bg-yellow-100 text-yellow-800'
  }
  return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800'
}

const getHorizontalLineWidth = (): number => {
  const reports = props.employee.direct_reports || []
  if (reports.length <= 1) return 0
  return (reports.length - 1) * 232 // 200px card width + 32px spacing
}

const getChildLinePosition = (index: number): number => {
  const reports = props.employee.direct_reports || []
  if (reports.length <= 1) return 0
  const totalWidth = getHorizontalLineWidth()
  const spacing = totalWidth / (reports.length - 1)
  return spacing * index
}

const sendMessage = (employee: Employee) => {
  // Implementation for sending message to employee
  console.log('Send message to:', employee.first_name, employee.last_name)
}
</script>

<style scoped>
.org-node {
  position: relative;
}
</style>