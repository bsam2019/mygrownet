<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-xl font-semibold text-gray-900">Commission Calculator</h2>
      <button
        @click="calculateCommission"
        :disabled="isCalculating || !canCalculate"
        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <span v-if="isCalculating" class="flex items-center">
          <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Calculating...
        </span>
        <span v-else>Calculate Commission</span>
      </button>
    </div>

    <form @submit.prevent="calculateCommission" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label for="employee" class="block text-sm font-medium text-gray-700 mb-2">
            Employee
          </label>
          <select
            id="employee"
            v-model="form.employeeId"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          >
            <option value="">Select Employee</option>
            <option
              v-for="employee in employees"
              :key="employee.id"
              :value="employee.id"
            >
              {{ employee.name }} - {{ employee.position }}
            </option>
          </select>
        </div>

        <div>
          <label for="period" class="block text-sm font-medium text-gray-700 mb-2">
            Period
          </label>
          <select
            id="period"
            v-model="form.period"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          >
            <option value="">Select Period</option>
            <option value="current_month">Current Month</option>
            <option value="last_month">Last Month</option>
            <option value="current_quarter">Current Quarter</option>
            <option value="last_quarter">Last Quarter</option>
            <option value="custom">Custom Range</option>
          </select>
        </div>

        <div v-if="form.period === 'custom'" class="md:col-span-2">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="startDate" class="block text-sm font-medium text-gray-700 mb-2">
                Start Date
              </label>
              <input
                id="startDate"
                type="date"
                v-model="form.startDate"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
            </div>
            <div>
              <label for="endDate" class="block text-sm font-medium text-gray-700 mb-2">
                End Date
              </label>
              <input
                id="endDate"
                type="date"
                v-model="form.endDate"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
            </div>
          </div>
        </div>

        <div>
          <label for="commissionType" class="block text-sm font-medium text-gray-700 mb-2">
            Commission Type
          </label>
          <select
            id="commissionType"
            v-model="form.commissionType"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          >
            <option value="">Select Type</option>
            <option value="sales">Sales Commission</option>
            <option value="performance">Performance Bonus</option>
            <option value="referral">Referral Bonus</option>
            <option value="all">All Types</option>
          </select>
        </div>

        <div>
          <label for="baseSalary" class="block text-sm font-medium text-gray-700 mb-2">
            Base Salary (Optional)
          </label>
          <input
            id="baseSalary"
            type="number"
            step="0.01"
            min="0"
            v-model="form.baseSalary"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Enter base salary"
          />
        </div>
      </div>
    </form>

    <!-- Results Section -->
    <div v-if="calculationResult" class="mt-8 border-t pt-6">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Commission Calculation Results</h3>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-blue-50 p-4 rounded-lg">
          <div class="text-sm font-medium text-blue-600">Total Commission</div>
          <div class="text-2xl font-bold text-blue-900">
            {{ formatCurrency(calculationResult.totalCommission) }}
          </div>
        </div>
        <div class="bg-green-50 p-4 rounded-lg">
          <div class="text-sm font-medium text-green-600">Base Amount</div>
          <div class="text-2xl font-bold text-green-900">
            {{ formatCurrency(calculationResult.baseAmount) }}
          </div>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg">
          <div class="text-sm font-medium text-purple-600">Commission Rate</div>
          <div class="text-2xl font-bold text-purple-900">
            {{ calculationResult.commissionRate }}%
          </div>
        </div>
      </div>

      <!-- Breakdown Table -->
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Type
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Base Amount
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Rate
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Commission
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="item in calculationResult.breakdown" :key="item.type">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ item.type }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatCurrency(item.baseAmount) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ item.rate }}%
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                {{ formatCurrency(item.commission) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-end space-x-3 mt-6">
        <button
          @click="exportResults"
          class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50"
        >
          Export Results
        </button>
        <button
          @click="saveCommission"
          class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
        >
          Save Commission
        </button>
      </div>
    </div>

    <!-- Error Display -->
    <div v-if="error" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-md">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800">Error</h3>
          <div class="mt-2 text-sm text-red-700">{{ error }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { formatCurrency } from '@/utils/formatting'

interface Employee {
  id: number
  name: string
  position: string
  department: string
}

interface CommissionForm {
  employeeId: string
  period: string
  startDate: string
  endDate: string
  commissionType: string
  baseSalary: number | null
}

interface CommissionBreakdown {
  type: string
  baseAmount: number
  rate: number
  commission: number
}

interface CommissionResult {
  totalCommission: number
  baseAmount: number
  commissionRate: number
  breakdown: CommissionBreakdown[]
  period: string
  employeeName: string
}

const props = defineProps<{
  employees?: Employee[]
  initialEmployeeId?: number
}>()

const emit = defineEmits<{
  commissionCalculated: [result: CommissionResult]
  commissionSaved: [result: CommissionResult]
  exportRequested: [result: CommissionResult]
}>()

const form = ref<CommissionForm>({
  employeeId: props.initialEmployeeId?.toString() || '',
  period: '',
  startDate: '',
  endDate: '',
  commissionType: '',
  baseSalary: null
})

const employees = ref<Employee[]>(props.employees || [])
const calculationResult = ref<CommissionResult | null>(null)
const isCalculating = ref(false)
const error = ref('')

const canCalculate = computed(() => {
  const hasRequiredFields = form.value.employeeId && form.value.period && form.value.commissionType
  const hasCustomDates = form.value.period !== 'custom' || (form.value.startDate && form.value.endDate)
  return hasRequiredFields && hasCustomDates
})

const calculateCommission = async () => {
  if (!canCalculate.value) return

  isCalculating.value = true
  error.value = ''

  try {
    const response = await fetch('/api/employees/commissions/calculate', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify(form.value)
    })

    if (!response.ok) {
      throw new Error('Failed to calculate commission')
    }

    const result = await response.json()
    calculationResult.value = result.data
    emit('commissionCalculated', result.data)
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'An error occurred while calculating commission'
  } finally {
    isCalculating.value = false
  }
}

const saveCommission = async () => {
  if (!calculationResult.value) return

  try {
    const response = await fetch('/api/employees/commissions', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({
        ...form.value,
        ...calculationResult.value
      })
    })

    if (!response.ok) {
      throw new Error('Failed to save commission')
    }

    emit('commissionSaved', calculationResult.value)
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'An error occurred while saving commission'
  }
}

const exportResults = () => {
  if (calculationResult.value) {
    emit('exportRequested', calculationResult.value)
  }
}

onMounted(async () => {
  if (!employees.value.length) {
    try {
      const response = await fetch('/api/employees')
      if (response.ok) {
        const data = await response.json()
        employees.value = data.data
      }
    } catch (err) {
      console.error('Failed to load employees:', err)
    }
  }
})
</script>