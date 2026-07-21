<script setup lang="ts">
import { computed } from 'vue'
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue'
import BarChart from '@/components/BMS/Charts/BarChart.vue'
import {
  BanknotesIcon, BuildingLibraryIcon, ArrowPathIcon,
  CheckCircleIcon, ChartBarIcon,
} from '@heroicons/vue/24/outline'

defineOptions({ layout: GrowFinanceLayout })

interface AccountSummary {
  name: string
  bank: string
  balance: number
  type: string
}

interface Statement {
  id: number
  statement_period: string
  start_date: string
  end_date: string
  opening_balance: number
  closing_balance: number
  status: string
  bankAccount?: { account_name: string }
}

interface Props {
  accounts: AccountSummary[]
  summary: {
    total_balance: number
    account_count: number
    average_balance: number
    active_reconciliations: number
    completed_reconciliations: number
  }
  recent_statements: Statement[]
}

const props = defineProps<Props>()

const formatCurrency = (value: number) =>
  new Intl.NumberFormat('en-ZM', { style: 'currency', currency: 'ZMW', minimumFractionDigits: 2 }).format(value)

const accountBalanceChartData = computed(() => ({
  labels: props.accounts.map(a => a.name),
  data: props.accounts.map(a => a.balance),
}))
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Banking Analytics</h1>
      <p class="mt-1 text-sm text-gray-600">Bank account performance, reconciliation status, and statement insights</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
      <div class="rounded-lg bg-white p-4 shadow">
        <div class="flex items-center gap-2 mb-1">
          <BuildingLibraryIcon class="h-5 w-5 text-emerald-600" />
          <span class="text-sm font-medium text-gray-600">Total Balance</span>
        </div>
        <div class="text-2xl font-bold text-gray-900">{{ formatCurrency(summary.total_balance) }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ summary.account_count }} active account(s)</div>
      </div>

      <div class="rounded-lg bg-white p-4 shadow">
        <div class="flex items-center gap-2 mb-1">
          <BanknotesIcon class="h-5 w-5 text-blue-600" />
          <span class="text-sm font-medium text-gray-600">Avg. Balance</span>
        </div>
        <div class="text-2xl font-bold text-gray-900">{{ formatCurrency(summary.average_balance) }}</div>
        <div class="text-xs text-gray-500 mt-1">Across all accounts</div>
      </div>

      <div class="rounded-lg bg-white p-4 shadow">
        <div class="flex items-center gap-2 mb-1">
          <CheckCircleIcon class="h-5 w-5 text-green-600" />
          <span class="text-sm font-medium text-gray-600">Completed Reconciliations</span>
        </div>
        <div class="text-2xl font-bold text-green-600">{{ summary.completed_reconciliations }}</div>
      </div>

      <div class="rounded-lg bg-white p-4 shadow">
        <div class="flex items-center gap-2 mb-1">
          <ArrowPathIcon class="h-5 w-5 text-purple-600" />
          <span class="text-sm font-medium text-gray-600">Active Reconciliations</span>
        </div>
        <div class="text-2xl font-bold text-purple-600">{{ summary.active_reconciliations }}</div>
      </div>
    </div>

    <!-- Account Balances Chart -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="mb-4 flex items-center gap-2">
          <ChartBarIcon class="h-5 w-5 text-emerald-600" />
          <h3 class="text-base font-semibold text-gray-900">Account Balances</h3>
        </div>
        <BarChart
          v-if="accounts.length > 0"
          :labels="accountBalanceChartData.labels"
          :data="accountBalanceChartData.data"
          color="#10b981"
          :height="280"
          x-axis-label="Amount (ZMW)"
          horizontal
        />
        <p v-else class="text-sm text-gray-500 text-center py-8">No bank accounts found</p>
      </div>

      <!-- Account Details Table -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="mb-4 flex items-center gap-2">
          <BuildingLibraryIcon class="h-5 w-5 text-indigo-600" />
          <h3 class="text-base font-semibold text-gray-900">Account Details</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Account</th>
              <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Balance</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="acct in accounts" :key="acct.name" class="hover:bg-gray-50">
              <td class="px-4 py-3 text-sm text-gray-900">
                <div class="font-medium">{{ acct.name }}</div>
                <div class="text-xs text-gray-500">{{ acct.bank }} · {{ acct.type }}</div>
              </td>
              <td class="px-4 py-3 text-sm text-right font-semibold" :class="acct.balance >= 0 ? 'text-green-600' : 'text-red-600'">
                {{ formatCurrency(acct.balance) }}
              </td>
            </tr>
            <tr v-if="accounts.length === 0">
              <td colspan="2" class="px-4 py-8 text-center text-sm text-gray-500">No bank accounts found</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Recent Statements -->
    <div class="mb-6">
      <div class="mb-3 flex items-center gap-2">
        <BuildingLibraryIcon class="h-5 w-5 text-teal-600" />
        <h2 class="text-lg font-semibold text-gray-900">Recent Bank Statements</h2>
      </div>
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Account</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Opening</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Closing</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="s in recent_statements" :key="s.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm text-gray-900">{{ s.bankAccount?.account_name || '—' }}</td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ s.statement_period || (s.start_date + ' — ' + s.end_date) }}</td>
              <td class="px-6 py-4 text-sm text-right text-gray-600">{{ formatCurrency(s.opening_balance) }}</td>
              <td class="px-6 py-4 text-sm text-right font-semibold" :class="s.closing_balance >= 0 ? 'text-green-600' : 'text-red-600'">
                {{ formatCurrency(s.closing_balance) }}
              </td>
              <td class="px-6 py-4 text-center">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="s.status === 'reconciled' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
                  {{ s.status }}
                </span>
              </td>
            </tr>
            <tr v-if="recent_statements.length === 0">
              <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">No statements uploaded yet</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
