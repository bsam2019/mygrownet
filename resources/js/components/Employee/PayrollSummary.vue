<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-xl font-semibold text-gray-900">Payroll Summary</h2>
      <div class="flex space-x-3">
        <select
          v-model="selectedPeriod"
          @change="loadPayrollData"
          class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="current_month">Current Month</option>
          <option value="last_month">Last Month</option>
          <option value="current_quarter">Current Quarter</option>
          <option value="last_quarter">Last Quarter</option>
          <option value="current_year">Current Year</option>
        </select>
        <button
          @click="exportPayroll"
          class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
        >
          Export
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <!-- Summary Cards -->
    <div v-else-if="payrollData" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-blue-50 p-4 rounded-lg">
          <div class="text-sm font-medium text-blue-600">Total Employees</div>
          <div class="text-2xl font-bold text-blue-900">{{ payrollData.summary.totalEmployees }}</div>
        </div>
        <div class="bg-green-50 p-4 rounded-lg">
          <div class="text-sm font-medium text-green-600">Total Gross Pay</div>
          <div class="text-2xl font-bold text-green-900">
            {{ formatCurrency(payrollData.summary.totalGrossPay) }}
          </div>
        </div>
        <div class="bg-yellow-50 p-4 rounded-lg">
          <div class="text-sm font-medium text-yellow-600">Total Deductions</div>
          <div class="text-2xl font-bold text-yellow-900">
            {{ formatCurrency(payrollData.summary.totalDeductions) }}
          </div>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg">
          <div class="text-sm font-medium text-purple-600">Total Net Pay</div>
          <div class="text-2xl font-bold text-purple-900">
            {{ formatCurrency(payrollData.summary.totalNetPay) }}
          </div>
        </div>
      </div>

      <!-- Department Breakdown -->
      <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Department Breakdown</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div
            v-for="dept in payrollData.departmentBreakdown"
            :key="dept.department"
            class="bg-white p-3 rounded border"
          >
            <div class="text-sm font-medium text-gray-600">{{ dept.department }}</div>
            <div class="text-lg font-semibold text-gray-900">
              {{ dept.employeeCount }} employees
            </div>
            <div class="text-sm text-gray-500">
              {{ formatCurrency(dept.totalPay) }}
            </div>
          </div>
        </div>
      </div>

      <!-- Employee Details Table -->
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Employee
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Department
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Base Salary
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Commission
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Bonuses
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Deductions
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Net Pay
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="employee in payrollData.employees" :key="employee.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                      <span class="text-sm font-medium text-gray-700">
                        {{ employee.name.charAt(0).toUpperCase() }}
                      </span>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ employee.name }}</div>
                    <div class="text-sm text-gray-500">{{ employee.position }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ employee.department }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatCurrency(employee.baseSalary) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                {{ formatCurrency(employee.commission) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600">
                {{ formatCurrency(employee.bonuses) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                {{ formatCurrency(employee.deductions) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ formatCurrency(employee.netPay) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="{
                    'bg-green-100 text-green-800': employee.status === 'paid',
                    'bg-yellow-100 text-yellow-800': employee.status === 'pending',
                    'bg-red-100 text-red-800': employee.status === 'failed'
                  }"
                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                >
                  {{ employee.status }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Payroll Actions -->
      <div class="flex justify-between items-center pt-6 border-t">
        <div class="text-sm text-gray-500">
          Showing {{ payrollData.employees.length }} employees for {{ formatPeriod(selectedPeriod) }}
        </div>
        <div class="flex space-x-3">
          <button
            @click="processPayroll"
            :disabled="isProcessing"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
          >
            <span v-if="isProcessing">Processing...</span>
            <span v-else>Process Payroll</span>
          </button>
          <button
            @click="generateReports"
            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50"
          >
            Generate Reports
          </button>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center py-12">
      <div class="text-red-600 mb-4">{{ error }}</div>
      <button
        @click="loadPayrollData"
        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
      >
        Retry
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { formatCurrency } from '@/utils/formatting'

interface PayrollEmployee {
  id: number
  name: string
  position: string
  department: string
  baseSalary: number
  commission: number
  bonuses: number
  deductions: number
  netPay: number
  status: 'paid' | 'pending' | 'failed'
}

interface DepartmentBreakdown {
  department: string
  employeeCount: number
  totalPay: number
}

interface PayrollSummary {
  totalEmployees: number
  totalGrossPay: number
  totalDeductions: number
  totalNetPay: number
}

interface PayrollData {
  summary: PayrollSummary
  employees: PayrollEmployee[]
  departmentBreakdown: DepartmentBreakdown[]
  period: string
}

const props = defineProps<{
  initialPeriod?: string
}>()

const emit = defineEmits<{
  payrollProcessed: [data: PayrollData]
  reportsGenerated: [data: PayrollData]
  dataExported: [data: PayrollData]
}>()

const selectedPeriod = ref(props.initialPeriod || 'current_month')
const payrollData = ref<PayrollData | null>(null)
const isLoading = ref(false)
const isProcessing = ref(false)
const error = ref('')

const loadPayrollData = async () => {
  isLoading.value = true
  error.value = ''

  try {
    const response = await fetch(`/api/payroll/summary?period=${selectedPeriod.value}`)
    
    if (!response.ok) {
      throw new Error('Failed to load payroll data')
    }

    const result = await response.json()
    payrollData.value = result.data
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'An error occurred while loading payroll data'
  } finally {
    isLoading.value = false
  }
}

const processPayroll = async () => {
  if (!payrollData.value) return

  isProcessing.value = true

  try {
    const response = await fetch('/api/payroll/process', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({
        period: selectedPeriod.value,
        employees: payrollData.value.employees.map(emp => emp.id)
      })
    })

    if (!response.ok) {
      throw new Error('Failed to process payroll')
    }

    const result = await response.json()
    payrollData.value = result.data
    emit('payrollProcessed', result.data)
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'An error occurred while processing payroll'
  } finally {
    isProcessing.value = false
  }
}

const generateReports = () => {
  if (payrollData.value) {
    emit('reportsGenerated', payrollData.value)
  }
}

const exportPayroll = () => {
  if (payrollData.value) {
    emit('dataExported', payrollData.value)
  }
}

const formatPeriod = (period: string): string => {
  const periodMap: Record<string, string> = {
    current_month: 'Current Month',
    last_month: 'Last Month',
    current_quarter: 'Current Quarter',
    last_quarter: 'Last Quarter',
    current_year: 'Current Year'
  }
  return periodMap[period] || period
}

onMounted(() => {
  loadPayrollData()
})
</script>