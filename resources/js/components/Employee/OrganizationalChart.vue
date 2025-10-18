<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <!-- Header -->
    <div class="p-6 border-b border-gray-100">
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <div class="p-2 bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg mr-3">
            <ChartBarIcon class="h-6 w-6 text-white" />
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Organizational Chart</h3>
            <p class="text-sm text-gray-500">Visual representation of company hierarchy</p>
          </div>
        </div>
        <div class="flex items-center space-x-3">
          <button
            @click="toggleView"
            class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            {{ viewMode === 'tree' ? 'Grid View' : 'Tree View' }}
          </button>
          <button
            @click="exportChart"
            class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            Export
          </button>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="p-6 border-b border-gray-100 bg-gray-50">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Search -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <div class="relative">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
            <input
              v-model="searchTerm"
              type="text"
              placeholder="Search employees..."
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
            />
          </div>
        </div>

        <!-- Department Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
          <select
            v-model="selectedDepartment"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
          >
            <option value="">All Departments</option>
            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
              {{ dept.name }}
            </option>
          </select>
        </div>

        <!-- Level Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Level</label>
          <select
            v-model="selectedLevel"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
          >
            <option value="">All Levels</option>
            <option v-for="level in levels" :key="level" :value="level">
              {{ level }}
            </option>
          </select>
        </div>

        <!-- Show Vacant Positions -->
        <div class="flex items-center">
          <label class="flex items-center">
            <input
              v-model="showVacantPositions"
              type="checkbox"
              class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
            />
            <span class="ml-2 text-sm text-gray-700">Show vacant positions</span>
          </label>
        </div>
      </div>
    </div>

    <!-- Chart Content -->
    <div class="p-6">
      <div v-if="filteredEmployees.length === 0" class="text-center py-12">
        <ChartBarIcon class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">No employees found</h3>
        <p class="mt-1 text-sm text-gray-500">Try adjusting your search criteria.</p>
      </div>

      <!-- Tree View -->
      <div v-else-if="viewMode === 'tree'" class="org-chart-container">
        <div class="flex justify-center">
          <OrgNode
            v-for="rootEmployee in rootEmployees"
            :key="rootEmployee.id"
            :employee="rootEmployee"
            :show-vacant="showVacantPositions"
            @employee-click="onEmployeeClick"
          />
        </div>
      </div>

      <!-- Grid View -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <EmployeeCard
          v-for="employee in filteredEmployees"
          :key="employee.id"
          :employee="employee"
          @click="onEmployeeClick(employee)"
        />
      </div>
    </div>

    <!-- Employee Details Modal -->
    <Modal :show="showEmployeeModal" @close="showEmployeeModal = false">
      <div v-if="selectedEmployee" class="p-6">
        <div class="flex items-center mb-6">
          <div class="flex-shrink-0">
            <div class="h-16 w-16 rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-xl">
              {{ getInitials(selectedEmployee.first_name, selectedEmployee.last_name) }}
            </div>
          </div>
          <div class="ml-4">
            <h3 class="text-xl font-semibold text-gray-900">
              {{ selectedEmployee.first_name }} {{ selectedEmployee.last_name }}
            </h3>
            <p class="text-sm text-gray-600">{{ selectedEmployee.position?.title }}</p>
            <p class="text-sm text-gray-500">{{ selectedEmployee.department?.name }}</p>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-6">
          <!-- Contact Information -->
          <div>
            <h4 class="text-sm font-medium text-gray-900 mb-3">Contact Information</h4>
            <div class="space-y-2">
              <div class="flex items-center text-sm">
                <EnvelopeIcon class="h-4 w-4 text-gray-400 mr-2" />
                <span class="text-gray-600">{{ selectedEmployee.email }}</span>
              </div>
              <div v-if="selectedEmployee.phone" class="flex items-center text-sm">
                <PhoneIcon class="h-4 w-4 text-gray-400 mr-2" />
                <span class="text-gray-600">{{ selectedEmployee.phone }}</span>
              </div>
            </div>
          </div>

          <!-- Employment Details -->
          <div>
            <h4 class="text-sm font-medium text-gray-900 mb-3">Employment Details</h4>
            <div class="space-y-2">
              <div class="text-sm">
                <span class="text-gray-500">Employee ID:</span>
                <span class="ml-2 text-gray-900">{{ selectedEmployee.employee_number }}</span>
              </div>
              <div class="text-sm">
                <span class="text-gray-500">Hire Date:</span>
                <span class="ml-2 text-gray-900">{{ formatDate(selectedEmployee.hire_date) }}</span>
              </div>
              <div class="text-sm">
                <span class="text-gray-500">Status:</span>
                <span
                  class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                  :class="getStatusBadgeClass(selectedEmployee.employment_status)"
                >
                  {{ formatStatus(selectedEmployee.employment_status) }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Reporting Structure -->
        <div class="mt-6">
          <h4 class="text-sm font-medium text-gray-900 mb-3">Reporting Structure</h4>
          
          <!-- Manager -->
          <div v-if="selectedEmployee.manager" class="mb-4">
            <h5 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Reports To</h5>
            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
              <div class="flex-shrink-0">
                <div class="h-8 w-8 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white font-medium text-xs">
                  {{ getInitials(selectedEmployee.manager.first_name, selectedEmployee.manager.last_name) }}
                </div>
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-900">
                  {{ selectedEmployee.manager.first_name }} {{ selectedEmployee.manager.last_name }}
                </p>
                <p class="text-xs text-gray-500">{{ selectedEmployee.manager.position?.title }}</p>
              </div>
            </div>
          </div>

          <!-- Direct Reports -->
          <div v-if="selectedEmployee.direct_reports?.length > 0">
            <h5 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Direct Reports</h5>
            <div class="space-y-2">
              <div
                v-for="report in selectedEmployee.direct_reports"
                :key="report.id"
                class="flex items-center p-3 bg-gray-50 rounded-lg"
              >
                <div class="flex-shrink-0">
                  <div class="h-8 w-8 rounded-full bg-gradient-to-r from-green-500 to-blue-600 flex items-center justify-center text-white font-medium text-xs">
                    {{ getInitials(report.first_name, report.last_name) }}
                  </div>
                </div>
                <div class="ml-3">
                  <p class="text-sm font-medium text-gray-900">
                    {{ report.first_name }} {{ report.last_name }}
                  </p>
                  <p class="text-xs text-gray-500">{{ report.position?.title }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="flex justify-end mt-6 pt-6 border-t border-gray-200">
          <Link
            :href="route('employees.show', selectedEmployee.id)"
            class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors"
          >
            View Full Profile
          </Link>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import {
  ChartBarIcon,
  MagnifyingGlassIcon,
  EnvelopeIcon,
  PhoneIcon
} from '@heroicons/vue/24/outline'
import Modal from '@/components/Modal.vue'
import OrgNode from './OrgNode.vue'
import EmployeeCard from './EmployeeCard.vue'

// Types
interface Employee {
  id: number
  employee_number: string
  first_name: string
  last_name: string
  email: string
  phone?: string
  hire_date: string
  employment_status: 'active' | 'inactive' | 'terminated' | 'suspended'
  manager_id?: number
  department?: {
    id: number
    name: string
  }
  position?: {
    id: number
    title: string
    level?: string
  }
  manager?: Employee
  direct_reports?: Employee[]
}

interface Department {
  id: number
  name: string
}

interface Props {
  employees: Employee[]
  departments: Department[]
}

const props = defineProps<Props>()

// Reactive data
const viewMode = ref<'tree' | 'grid'>('tree')
const searchTerm = ref('')
const selectedDepartment = ref('')
const selectedLevel = ref('')
const showVacantPositions = ref(false)
const showEmployeeModal = ref(false)
const selectedEmployee = ref<Employee | null>(null)

// Computed
const levels = computed(() => {
  const levelSet = new Set<string>()
  props.employees.forEach(emp => {
    if (emp.position?.level) {
      levelSet.add(emp.position.level)
    }
  })
  return Array.from(levelSet).sort()
})

const filteredEmployees = computed(() => {
  let filtered = [...props.employees]
  
  if (searchTerm.value) {
    const search = searchTerm.value.toLowerCase()
    filtered = filtered.filter(emp =>
      emp.first_name.toLowerCase().includes(search) ||
      emp.last_name.toLowerCase().includes(search) ||
      emp.employee_number.toLowerCase().includes(search) ||
      emp.position?.title.toLowerCase().includes(search)
    )
  }
  
  if (selectedDepartment.value) {
    filtered = filtered.filter(emp => emp.department?.id.toString() === selectedDepartment.value)
  }
  
  if (selectedLevel.value) {
    filtered = filtered.filter(emp => emp.position?.level === selectedLevel.value)
  }
  
  return filtered
})

const rootEmployees = computed(() => {
  return buildEmployeeTree(filteredEmployees.value)
})

// Methods
const buildEmployeeTree = (employees: Employee[]): Employee[] => {
  const employeeMap = new Map<number, Employee>()
  const rootEmployees: Employee[] = []
  
  // Create a map of all employees with direct_reports array
  employees.forEach(emp => {
    employeeMap.set(emp.id, { ...emp, direct_reports: [] })
  })
  
  // Build the tree structure
  employees.forEach(emp => {
    const employee = employeeMap.get(emp.id)!
    
    if (emp.manager_id && employeeMap.has(emp.manager_id)) {
      const manager = employeeMap.get(emp.manager_id)!
      manager.direct_reports!.push(employee)
    } else {
      rootEmployees.push(employee)
    }
  })
  
  return rootEmployees
}

const toggleView = () => {
  viewMode.value = viewMode.value === 'tree' ? 'grid' : 'tree'
}

const exportChart = () => {
  // Implementation for exporting the chart
  // This could export as PDF, PNG, or other formats
  console.log('Export chart functionality')
}

const onEmployeeClick = (employee: Employee) => {
  selectedEmployee.value = employee
  showEmployeeModal.value = true
}

const getInitials = (firstName: string, lastName: string): string => {
  return `${firstName.charAt(0)}${lastName.charAt(0)}`.toUpperCase()
}

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
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
</script>

<style scoped>
.org-chart-container {
  overflow-x: auto;
  min-height: 400px;
}
</style>