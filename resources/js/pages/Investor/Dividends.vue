<template>
  <InvestorLayout 
    :investor="investor" 
    page-title="Dividends" 
    :active-page="activePage || 'dividends'"
    :unread-messages="unreadMessages"
    :unread-announcements="unreadAnnouncements"
  >
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Dividend Management</h1>
        <p class="mt-1 text-sm text-gray-500">
          Track your dividend payments and distributions
        </p>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <StatCard
          title="Total Dividends Earned"
          :value="formatCurrency(totalEarned)"
          icon="BanknotesIcon"
          color="green"
        />
        <StatCard
          title="This Year"
          :value="formatCurrency(yearToDate)"
          icon="CalendarIcon"
          color="blue"
        />
        <StatCard
          title="Next Payment"
          :value="nextPaymentDate"
          icon="ClockIcon"
          color="indigo"
        />
      </div>

      <!-- Upcoming Distributions -->
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Upcoming Distributions</h2>
        </div>
        <div class="p-6">
          <div v-if="upcomingDistributions.length === 0" class="text-center py-8">
            <CalendarIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
            <p class="mt-2 text-sm text-gray-500">No upcoming distributions scheduled</p>
          </div>
          <div v-else class="space-y-4">
            <div
              v-for="distribution in upcomingDistributions"
              :key="distribution.id"
              class="flex items-center justify-between p-4 bg-blue-50 rounded-lg"
            >
              <div>
                <h3 class="text-sm font-medium text-gray-900">
                  {{ distribution.quarter }} Distribution
                </h3>
                <p class="text-sm text-gray-500">
                  Payment Date: {{ formatDate(distribution.payment_date) }}
                </p>
              </div>
              <div class="text-right">
                <p class="text-lg font-semibold text-blue-600">
                  {{ formatCurrency(distribution.estimated_amount) }}
                </p>
                <p class="text-xs text-gray-500">Estimated</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Dividend History -->
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
          <h2 class="text-lg font-semibold text-gray-900">Payment History</h2>
          <select
            v-model="selectedYear"
            class="text-sm border-gray-300 rounded-md"
          >
            <option v-for="year in availableYears" :key="year" :value="year">
              {{ year }}
            </option>
          </select>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  Date
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  Quarter
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  Gross Amount
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  Tax Withheld
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  Net Amount
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  Status
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="dividend in dividends" :key="dividend.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(dividend.payment_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ dividend.quarter }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatCurrency(dividend.gross_amount) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatCurrency(dividend.tax_withheld) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                  {{ formatCurrency(dividend.net_amount) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="inline-flex px-2 py-1 text-xs font-medium rounded-full"
                    :class="getStatusClass(dividend.status)"
                  >
                    {{ dividend.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </InvestorLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { BanknotesIcon, CalendarIcon, ClockIcon } from '@heroicons/vue/24/outline'
import InvestorLayout from '@/Layouts/InvestorLayout.vue'
import StatCard from '@/components/Investor/StatCard.vue'

interface Dividend {
  id: number
  payment_date: string
  quarter: string
  gross_amount: number
  tax_withheld: number
  net_amount: number
  status: string
}

interface Distribution {
  id: number
  quarter: string
  payment_date: string
  estimated_amount: number
}

interface Investor {
  id: number
  name: string
  email: string
}

interface Props {
  investor: Investor
  dividends: Dividend[]
  upcomingDistributions: Distribution[]
  totalEarned: number
  activePage?: string
  unreadMessages?: number
  unreadAnnouncements?: number
}

const props = defineProps<Props>()

const selectedYear = ref(new Date().getFullYear())

const availableYears = computed(() => {
  const years = new Set(
    props.dividends.map(d => new Date(d.payment_date).getFullYear())
  )
  return Array.from(years).sort((a, b) => b - a)
})

const yearToDate = computed(() => {
  return props.dividends
    .filter(d => new Date(d.payment_date).getFullYear() === selectedYear.value)
    .reduce((sum, d) => sum + d.net_amount, 0)
})

const nextPaymentDate = computed(() => {
  if (props.upcomingDistributions.length === 0) return 'TBD'
  return formatDate(props.upcomingDistributions[0].payment_date)
})

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-ZM', {
    style: 'currency',
    currency: 'ZMW',
  }).format(amount)
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const getStatusClass = (status: string) => {
  const classes: Record<string, string> = {
    paid: 'bg-green-100 text-green-800',
    pending: 'bg-amber-100 text-amber-800',
    processing: 'bg-blue-100 text-blue-800',
  }
  return classes[status.toLowerCase()] || 'bg-gray-100 text-gray-800'
}
</script>
