<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <!-- Header -->
    <div class="p-6 border-b border-gray-100">
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <div class="p-2 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg mr-3">
            <UsersIcon class="h-6 w-6 text-white" />
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Employee Management</h3>
            <p class="text-sm text-gray-500">Manage your organization's workforce</p>
          </div>
        </div>
        <div class="flex items-center space-x-3">
          <Link 
            v-if="canCreate"
            :href="route('employees.create')" 
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
          >
            <PlusIcon class="h-4 w-4 mr-2" />
            Add Employee
          </Link>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="p-6 border-b border-gray-100 bg-gray-50">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Search -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <div class="relative">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
            <input
              v-model="localFilters.search"
              type="text"
              placeholder="Name, email, or employee number..."
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
              @input="debouncedSearch"
            />
          </div>
        </div>

        <!-- Department Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
          <select
            v-model="localFilters.department"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
            @change="applyFilters"
          >
            <option value="">All Departments</option>
            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
              {{ dept.name }}
            </option>
          </select>
        </div>

        <!-- Position Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
          <select
            v-model="localFilters.position"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
            @change="applyFilters"
          >
            <option value="">All Positions</option>
            <option v-for="pos in positions" :key="pos.id" :value="pos.id">
              {{ pos.title }}
            </option>
          </select>
        </div>

        <!-- Status Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            v-model="localFilters.status"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
            @change="applyFilters"
          >
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="terminated">Terminated</option>
            <option value="suspended">Suspended</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Employee Stats -->
    <div class="p-6 border-b border-gray-100">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm opacity-80">Total Employees</p>
              <p class="text-2xl font-bold mt-1">{{ stats.total_employees || 0 }}</p>
            </div>
            <UsersIcon class="h-8 w-8 opacity-80" />
          </div>
        </div>

        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 text-white">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm opacity-80">Active</p>
              <p class="text-2xl font-bold mt-1">{{ stats.active_employees || 0 }}</p>
            </div>
            <CheckCircleIcon class="h-8 w-8 opacity-80" />
          </div>
        </div>

        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-4 text-white">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm opacity-80">Departments</p>
              <p class="text-2xl font-bold mt-1">{{ stats.total_departments || 0 }}</p>
            </div>
            <BuildingOfficeIcon class="h-8 w-8 opacity-80" />
          </div>
        </div>

        <div class="bg-gradient-to-r from-amber-500 to-amber-600 rounded-lg p-4 text-white">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm opacity-80">New This Month</p>
              <p class="text-2xl font-bold mt-1">{{ stats.new_employees_this_month || 0 }}</p>
            </div>
            <UserPlusIcon class="h-8 w-8 opacity-80" />
          </div>
        </div>
      </div>
    </div>

    <!-- Employee Table -->
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              <button 
                class="flex items-center space-x-1 hover:text-gray-700"
                @click="sort('first_name')"
              >
                <span>Employee</span>
                <ChevronUpDownIcon class="h-4 w-4" />
              </button>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Department
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Position
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              <button 
                class="flex items-center space-x-1 hover:text-gray-700"
                @click="sort('hire_date')"
              >
                <span>Hire Date</span>
                <ChevronUpDownIcon class="h-4 w-4" />
              </button>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Status
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="employee in employees.data" :key="employee.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-10 w-10">
                  <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white font-medium text-sm">
                    {{ getInitials(employee.first_name, employee.last_name) }}
                  </div>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900">
                    {{ employee.first_name }} {{ employee.last_name }}
                  </div>
                  <div class="text-sm text-gray-500">{{ employee.email }}</div>
                  <div class="text-xs text-gray-400">{{ employee.employee_number }}</div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900">{{ employee.department?.name || 'N/A' }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900">{{ employee.position?.title || 'N/A' }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ formatDate(employee.hire_date) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="getStatusBadgeClass(employee.employment_status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                {{ formatStatus(employee.employment_status) }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <div class="flex items-center space-x-2">
                <Link 
                  :href="route('employees.show', employee.id)"
                  class="text-blue-600 hover:text-blue-900 transition-colors"
                  title="View Employee"
                >
                  <EyeIcon class="h-4 w-4" />
                </Link>
                <Link 
                  v-if="canEdit"
                  :href="route('employees.edit', employee.id)"
                  class="text-indigo-600 hover:text-indigo-900 transition-colors"
                  title="Edit Employee"
                >
                  <PencilIcon class="h-4 w-4" />
                </Link>
                <button 
                  v-if="canDelete && employee.employment_status !== 'terminated'"
                  @click="confirmDelete(employee)"
                  class="text-red-600 hover:text-red-900 transition-colors"
                  title="Terminate Employee"
                >
                  <TrashIcon class="h-4 w-4" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Empty State -->
      <div v-if="employees.data.length === 0" class="text-center py-12">
        <UsersIcon class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">No employees found</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ hasFilters ? 'Try adjusting your search criteria.' : 'Get started by adding your first employee.' }}
        </p>
        <div v-if="!hasFilters && canCreate" class="mt-6">
          <Link 
            :href="route('employees.create')"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
          >
            <PlusIcon class="h-4 w-4 mr-2" />
            Add Employee
          </Link>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="employees.data.length > 0" class="px-6 py-4 border-t border-gray-200">
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-700">
          Showing {{ employees.from }} to {{ employees.to }} of {{ employees.total }} employees
        </div>
        <div class="flex items-center space-x-2">
          <Link 
            v-if="employees.prev_page_url"
            :href="employees.prev_page_url"
            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Previous
          </Link>
          <Link 
            v-if="employees.next_page_url"
            :href="employees.next_page_url"
            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Next
          </Link>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <Modal :show="showDeleteModal" @close="showDeleteModal = false">
      <div class="p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <ExclamationTriangleIcon class="h-6 w-6 text-red-600" />
          </div>
          <div class="ml-3">
            <h3 class="text-lg font-medium text-gray-900">Terminate Employee</h3>
            <div class="mt-2">
              <p class="text-sm text-gray-500">
                Are you sure you want to terminate {{ employeeToDelete?.first_name }} {{ employeeToDelete?.last_name }}? 
                This action will change their status to terminated and they will lose system access.
              </p>
            </div>
          </div>
        </div>
        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
          <button
            type="button"
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
            @click="deleteEmployee"
          >
            Terminate
          </button>
          <button
            type="button"
            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm"
            @click="showDeleteModal = false"
          >
            Cancel
          </button>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { debounce } from 'lodash-es'
import {
  UsersIcon,
  PlusIcon,
  MagnifyingGlassIcon,
  CheckCircleIcon,
  BuildingOfficeIcon,
  UserPlusIcon,
  ChevronUpDownIcon,
  EyeIcon,
  PencilIcon,
  TrashIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'
import Modal from '@/components/Modal.vue'

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
  department?: {
    id: number
    name: string
  }
  position?: {
    id: number
    title: string
  }
  manager?: {
    id: number
    first_name: string
    last_name: string
  }
}

interface Department {
  id: number
  name: string
}

interface Position {
  id: number
  title: string
}

interface EmployeeStats {
  total_employees: number
  active_employees: number
  total_departments: number
  new_employees_this_month: number
}

interface PaginatedEmployees {
  data: Employee[]
  current_page: number
  from: number
  to: number
  total: number
  prev_page_url?: string
  next_page_url?: string
}

interface Filters {
  search?: string
  department?: string
  position?: string
  status?: string
  sort?: string
  direction?: string
}

interface Props {
  employees: PaginatedEmployees
  departments: Department[]
  positions: Position[]
  filters: Filters
  stats: EmployeeStats
  canCreate?: boolean
  canEdit?: boolean
  canDelete?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  canCreate: false,
  canEdit: false,
  canDelete: false
})

// Reactive data
const localFilters = ref<Filters>({ ...props.filters })
const showDeleteModal = ref(false)
const employeeToDelete = ref<Employee | null>(null)

// Computed
const hasFilters = computed(() => {
  return !!(localFilters.value.search || localFilters.value.department || 
           localFilters.value.position || localFilters.value.status)
})

// Methods
const getInitials = (firstName: string, lastName: string): string => {
  return `${firstName.charAt(0)}${lastName.charAt(0)}`.toUpperCase()
}

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
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

const applyFilters = () => {
  router.get(route('employees.index'), localFilters.value, {
    preserveState: true,
    replace: true
  })
}

const debouncedSearch = debounce(() => {
  applyFilters()
}, 300)

const sort = (field: string) => {
  if (localFilters.value.sort === field) {
    localFilters.value.direction = localFilters.value.direction === 'asc' ? 'desc' : 'asc'
  } else {
    localFilters.value.sort = field
    localFilters.value.direction = 'asc'
  }
  applyFilters()
}

const confirmDelete = (employee: Employee) => {
  employeeToDelete.value = employee
  showDeleteModal.value = true
}

const deleteEmployee = () => {
  if (employeeToDelete.value) {
    router.delete(route('employees.destroy', employeeToDelete.value.id), {
      onSuccess: () => {
        showDeleteModal.value = false
        employeeToDelete.value = null
      }
    })
  }
}

// Watch for filter changes
watch(() => props.filters, (newFilters) => {
  localFilters.value = { ...newFilters }
}, { deep: true })
</script>