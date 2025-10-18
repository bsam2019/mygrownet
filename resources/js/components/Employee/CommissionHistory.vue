<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-xl font-semibold text-gray-900">Commission History</h2>
      <div class="flex space-x-3">
        <select
          v-model="filters.period"
          @change="loadCommissions"
          class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="all">All Time</option>
          <option value="current_year">Current Year</option>
          <option value="last_year">Last Year</option>
          <option value="current_quarter">Current Quarter</option>
          <option value="last_quarter">Last Quarter</option>
          <option value="current_month">Current Month</option>
          <option value="last_month">Last Month</option>
        </select>
        <select
          v-model="filters.status"
          @change="loadCommissions"
          class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="all">All Status</option>
          <option value="pending">Pending</option>
          <option value="paid">Paid</option>
          <option value="cancelled">Cancelled</option>
        </select>
        <button
          @click="exportHistory"
          class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
        >
          Export
        </button>
      </div>
    </div>

    <!-- Search and Filters -->
    <div class="mb-6 flex flex-col sm:flex-row gap-4">
      <div class="flex-1">
        <input
          v-model="filters.search"
          @input="debouncedSearch"
          type="text"
          placeholder="Search by employee name or commission type..."
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>
      <select
        v-model="filters.type"
        @change="loadCommissions"
        class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
        <option value="all">All Types</option>
        <option value="sales">Sales Commission</option>
        <option value="performance">Performance Bonus</option>
        <option value="referral">Referral Bonus</option>
      </select>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-blue-50 p-4 rounded-lg">
        <div class="text-sm font-medium text-blue-600">Total Commissions</div>
        <div class="text-2xl font-bold text-blue-900">{{ commissions.length }}</div>
      </div>
      <div class="bg-green-50 p-4 rounded-lg">
        <div class="text-sm font-medium text-green-600">Total Amount</div>
        <div class="text-2xl font-bold text-green-900">
          {{ formatCurrency(totalAmount) }}
        </div>
      </div>
      <div class="bg-yellow-50 p-4 rounded-lg">
        <div class="text-sm font-medium text-yellow-600">Pending</div>
        <div class="text-2xl font-bold text-yellow-900">
          {{ formatCurrency(pendingAmount) }}
        </div>
      </div>
      <div class="bg-purple-50 p-4 rounded-lg">
        <div class="text-sm font-medium text-purple-600">Paid</div>
        <div class="text-2xl font-bold text-purple-900">
          {{ formatCurrency(paidAmount) }}
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <!-- Commission Table -->
    <div v-else-if="commissions.length > 0" class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              <button @click="sortBy('employee_name')" class="flex items-center space-x-1">
                <span>Employee</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                </svg>
              </button>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Type
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              <button @click="sortBy('amount')" class="flex items-center space-x-1">
                <span>Amount</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                </svg>
              </button>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              <button @click="sortBy('created_at')" class="flex items-center space-x-1">
                <span>Date</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                </svg>
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
          <tr v-for="commission in paginatedCommissions" :key="commission.id">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-10 w-10">
                  <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                    <span class="text-sm font-medium text-gray-700">
                      {{ commission.employeeName.charAt(0).toUpperCase() }}
                    </span>
                  </div>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900">{{ commission.employeeName }}</div>
                  <div class="text-sm text-gray-500">{{ commission.department }}</div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                :class="{
                  'bg-blue-100 text-blue-800': commission.type === 'sales',
                  'bg-green-100 text-green-800': commission.type === 'performance',
                  'bg-purple-100 text-purple-800': commission.type === 'referral'
                }"
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
              >
                {{ commission.type }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
              {{ formatCurrency(commission.amount) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ formatDate(commission.createdAt) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                :class="{
                  'bg-green-100 text-green-800': commission.status === 'paid',
                  'bg-yellow-100 text-yellow-800': commission.status === 'pending',
                  'bg-red-100 text-red-800': commission.status === 'cancelled'
                }"
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
              >
                {{ commission.status }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <div class="flex space-x-2">
                <button
                  @click="viewDetails(commission)"
                  class="text-blue-600 hover:text-blue-900"
                >
                  View
                </button>
                <button
                  v-if="commission.status === 'pending'"
                  @click="markAsPaid(commission)"
                  class="text-green-600 hover:text-green-900"
                >
                  Mark Paid
                </button>
                <button
                  v-if="commission.status === 'pending'"
                  @click="cancelCommission(commission)"
                  class="text-red-600 hover:text-red-900"
                >
                  Cancel
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div class="flex items-center justify-between px-6 py-3 bg-gray-50 border-t">
        <div class="text-sm text-gray-500">
          Showing {{ (currentPage - 1) * itemsPerPage + 1 }} to 
          {{ Math.min(currentPage * itemsPerPage, filteredCommissions.length) }} of 
          {{ filteredCommissions.length }} results
        </div>
        <div class="flex space-x-2">
          <button
            @click="currentPage--"
            :disabled="currentPage === 1"
            class="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50"
          >
            Previous
          </button>
          <span class="px-3 py-1 text-sm">{{ currentPage }} of {{ totalPages }}</span>
          <button
            @click="currentPage++"
            :disabled="currentPage === totalPages"
            class="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="!isLoading" class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No commissions found</h3>
      <p class="mt-1 text-sm text-gray-500">No commission records match your current filters.</p>
    </div>

    <!-- Error State -->
    <div v-if="error" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-md">
      <div class="text-red-800">{{ error }}</div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { formatCurrency } from '@/utils/formatting'

interface Commission {
  id: number
  employeeName: string
  department: string
  type: 'sales' | 'performance' | 'referral'
  amount: number
  status: 'pending' | 'paid' | 'cancelled'
  createdAt: string
  paidAt?: string
  description?: string
}

interface Filters {
  search: string
  period: string
  status: string
  type: string
}

const props = defineProps<{
  employeeId?: number
}>()

const emit = defineEmits<{
  commissionViewed: [commission: Commission]
  commissionPaid: [commission: Commission]
  commissionCancelled: [commission: Commission]
  dataExported: [commissions: Commission[]]
}>()

const commissions = ref<Commission[]>([])
const isLoading = ref(false)
const error = ref('')
const currentPage = ref(1)
const itemsPerPage = ref(10)
const sortField = ref('created_at')
const sortDirection = ref<'asc' | 'desc'>('desc')

const filters = ref<Filters>({
  search: '',
  period: 'all',
  status: 'all',
  type: 'all'
})

let searchTimeout: NodeJS.Timeout

const filteredCommissions = computed(() => {
  let filtered = [...commissions.value]

  // Apply search filter
  if (filters.value.search) {
    const search = filters.value.search.toLowerCase()
    filtered = filtered.filter(commission =>
      commission.employeeName.toLowerCase().includes(search) ||
      commission.type.toLowerCase().includes(search) ||
      commission.department.toLowerCase().includes(search)
    )
  }

  // Apply status filter
  if (filters.value.status !== 'all') {
    filtered = filtered.filter(commission => commission.status === filters.value.status)
  }

  // Apply type filter
  if (filters.value.type !== 'all') {
    filtered = filtered.filter(commission => commission.type === filters.value.type)
  }

  // Apply sorting
  filtered.sort((a, b) => {
    const aValue = a[sortField.value as keyof Commission]
    const bValue = b[sortField.value as keyof Commission]
    
    if (sortDirection.value === 'asc') {
      return aValue < bValue ? -1 : aValue > bValue ? 1 : 0
    } else {
      return aValue > bValue ? -1 : aValue < bValue ? 1 : 0
    }
  })

  return filtered
})

const paginatedCommissions = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filteredCommissions.value.slice(start, end)
})

const totalPages = computed(() => {
  return Math.ceil(filteredCommissions.value.length / itemsPerPage.value)
})

const totalAmount = computed(() => {
  return commissions.value.reduce((sum, commission) => sum + commission.amount, 0)
})

const pendingAmount = computed(() => {
  return commissions.value
    .filter(commission => commission.status === 'pending')
    .reduce((sum, commission) => sum + commission.amount, 0)
})

const paidAmount = computed(() => {
  return commissions.value
    .filter(commission => commission.status === 'paid')
    .reduce((sum, commission) => sum + commission.amount, 0)
})

const loadCommissions = async () => {
  isLoading.value = true
  error.value = ''

  try {
    const params = new URLSearchParams({
      period: filters.value.period,
      status: filters.value.status,
      type: filters.value.type,
      ...(props.employeeId && { employee_id: props.employeeId.toString() })
    })

    const response = await fetch(`/api/employees/commissions?${params}`)
    
    if (!response.ok) {
      throw new Error('Failed to load commission history')
    }

    const result = await response.json()
    commissions.value = result.data
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'An error occurred while loading commissions'
  } finally {
    isLoading.value = false
  }
}

const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    currentPage.value = 1
    loadCommissions()
  }, 300)
}

const sortBy = (field: string) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortField.value = field
    sortDirection.value = 'desc'
  }
}

const viewDetails = (commission: Commission) => {
  emit('commissionViewed', commission)
}

const markAsPaid = async (commission: Commission) => {
  try {
    const response = await fetch(`/api/employees/commissions/${commission.id}/mark-paid`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    })

    if (!response.ok) {
      throw new Error('Failed to mark commission as paid')
    }

    commission.status = 'paid'
    commission.paidAt = new Date().toISOString()
    emit('commissionPaid', commission)
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'An error occurred'
  }
}

const cancelCommission = async (commission: Commission) => {
  if (!confirm('Are you sure you want to cancel this commission?')) {
    return
  }

  try {
    const response = await fetch(`/api/employees/commissions/${commission.id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    })

    if (!response.ok) {
      throw new Error('Failed to cancel commission')
    }

    commission.status = 'cancelled'
    emit('commissionCancelled', commission)
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'An error occurred'
  }
}

const exportHistory = () => {
  emit('dataExported', filteredCommissions.value)
}

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

onMounted(() => {
  loadCommissions()
})
</script>