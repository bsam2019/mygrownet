<template>
  <AdminLayout :breadcrumbs="[
    { name: 'Dashboard', href: route('admin.dashboard') },
    { name: 'Reports', href: route('admin.reports.index') }
  ]">
    <Head title="Financial Reports" />
    
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-6">
          <h1 class="text-2xl font-semibold text-gray-900">Financial Reports</h1>
          <p class="mt-1 text-sm text-gray-600">View and analyze financial data across the platform</p>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
          <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                  <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Investments</dt>
                    <dd class="flex items-baseline">
                      <div class="text-2xl font-semibold text-gray-900">{{ formatKwachaDisplay(summary.totalInvestments) }}</div>
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                  <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Active Investments</dt>
                    <dd class="flex items-baseline">
                      <div class="text-2xl font-semibold text-gray-900">{{ formatKwachaDisplay(summary.activeInvestments) }}</div>
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                  <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Monthly Returns</dt>
                    <dd class="flex items-baseline">
                      <div class="text-2xl font-semibold text-gray-900">{{ formatKwachaDisplay(summary.monthlyReturns) }}</div>
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                  <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Pending Withdrawals</dt>
                    <dd class="flex items-baseline">
                      <div class="text-2xl font-semibold text-gray-900">{{ formatKwachaDisplay(summary.pendingWithdrawals) }}</div>
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
          <!-- Investment Trends Chart -->
          <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-5">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Investment Trends</h3>
              <div class="h-80">
                <canvas ref="investmentChart"></canvas>
              </div>
            </div>
          </div>

          <!-- Monthly Performance Chart -->
          <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-5">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Monthly Performance</h3>
              <div class="h-80">
                <canvas ref="performanceChart"></canvas>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Transactions Table -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
          <div class="p-5">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-medium text-gray-900">Recent Transactions</h3>
              <Link :href="route('admin.reports.investments')" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                View All
              </Link>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="transaction in recentTransactions" :key="transaction.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {{ formatTransactionType(transaction.transaction_type) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ transaction.user?.name || 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ formatKwachaDisplay(transaction.amount) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="[
                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                        getStatusColor(transaction.status)
                      ]">
                        {{ formatStatus(transaction.status) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ formatDate(transaction.created_at) }}
                    </td>
                  </tr>
                  <tr v-if="recentTransactions.length === 0">
                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                      No transactions found
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import Chart from 'chart.js/auto'
import { formatCurrency } from '@/utils/format'

const props = defineProps({
  summary: {
    type: Object,
    default: () => ({
      totalInvestments: 0,
      activeInvestments: 0,
      monthlyInvestments: 0,
      yearlyInvestments: 0,
      totalWithdrawals: 0,
      pendingWithdrawals: 0,
      monthlyReturns: 0,
      yearlyReturns: 0
    })
  },
  recentTransactions: {
    type: Array,
    default: () => []
  }
})

const investmentChart = ref(null)
const performanceChart = ref(null)

// Custom formatter to display Kwacha as "K" instead of "ZMW"
const formatKwachaDisplay = (value) => {
  return formatCurrency(value)
}

// Format dates
const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Format transaction types
const formatTransactionType = (type) => {
  const types = {
    'investment': 'Investment',
    'withdrawal': 'Withdrawal',
    'return': 'Return',
    'referral': 'Referral'
  }
  return types[type] || type
}

// Format status
const formatStatus = (status) => {
  const statuses = {
    'completed': 'Completed',
    'pending': 'Pending',
    'failed': 'Failed',
    'active': 'Active',
    'inactive': 'Inactive'
  }
  return statuses[status] || status
}

// Get status color class
const getStatusColor = (status) => {
  return {
    'completed': 'bg-green-100 text-green-800',
    'active': 'bg-green-100 text-green-800',
    'pending': 'bg-yellow-100 text-yellow-800',
    'failed': 'bg-red-100 text-red-800',
    'inactive': 'bg-gray-100 text-gray-800'
  }[status] || 'bg-gray-100 text-gray-800'
}

// Initialize charts
onMounted(() => {
  // Sample data for charts - replace with actual data from your backend
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
  
  // Investment Trends Chart
  if (investmentChart.value) {
    new Chart(investmentChart.value, {
      type: 'line',
      data: {
        labels: months,
        datasets: [
          {
            label: 'Total Investments',
            data: [1200000, 1900000, 1500000, 2500000, 2200000, 3000000, 2800000, 3500000, 3200000, 4000000, 3800000, 4500000],
            borderColor: 'rgb(79, 70, 229)',
            backgroundColor: 'rgba(79, 70, 229, 0.1)',
            tension: 0.3,
            fill: true
          },
          {
            label: 'Active Investments',
            data: [1000000, 1500000, 1200000, 2000000, 1800000, 2500000, 2300000, 3000000, 2700000, 3500000, 3300000, 4000000],
            borderColor: 'rgb(16, 185, 129)',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.3,
            fill: true
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top',
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                return context.dataset.label + ': ' + formatKwachaDisplay(context.raw)
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return formatKwachaDisplay(value)
              }
            }
          }
        }
      }
    })
  }

  // Monthly Performance Chart
  if (performanceChart.value) {
    new Chart(performanceChart.value, {
      type: 'bar',
      data: {
        labels: months,
        datasets: [
          {
            label: 'Monthly Returns',
            data: [120000, 190000, 150000, 250000, 220000, 300000, 280000, 350000, 320000, 400000, 380000, 450000],
            backgroundColor: 'rgba(79, 70, 229, 0.5)',
            borderColor: 'rgb(79, 70, 229)',
            borderWidth: 1
          },
          {
            label: 'Withdrawals',
            data: [80000, 120000, 100000, 150000, 130000, 180000, 160000, 200000, 190000, 220000, 210000, 250000],
            backgroundColor: 'rgba(245, 158, 11, 0.5)',
            borderColor: 'rgb(245, 158, 11)',
            borderWidth: 1
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top',
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                return context.dataset.label + ': ' + formatKwachaDisplay(context.raw)
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return formatKwachaDisplay(value)
              }
            }
          }
        }
      }
    })
  }
})
</script>
