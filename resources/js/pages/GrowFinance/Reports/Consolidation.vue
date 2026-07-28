<template>
  <AuthenticatedLayout>
    <Head title="Consolidated Statements" />
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header with period selector -->
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-bold">Consolidated Statements</h1>
          <div class="flex gap-4 items-center">
            <input type="month" v-model="period" class="rounded border-gray-300" />
            <input type="text" v-model="reportingCurrency" placeholder="Currency" class="w-20 rounded border-gray-300" />
            <button @click="runConsolidation" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
              Run Consolidation
            </button>
          </div>
        </div>

        <!-- Consolidated Trial Balance -->
        <div v-if="consolidation" class="bg-white rounded-lg shadow p-6 mb-6">
          <h2 class="text-lg font-semibold mb-4">Consolidated Trial Balance</h2>
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b">
                <th class="text-left py-2">Account Code</th>
                <th class="text-left py-2">Account Name</th>
                <th class="text-right py-2">Debit</th>
                <th class="text-right py-2">Credit</th>
                <th class="text-right py-2">Balance</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in trialBalance" :key="row.account_code || row.code" class="border-b hover:bg-gray-50">
                <td class="py-2">{{ row.account_code || row.code }}</td>
                <td class="py-2">{{ row.account_name || row.name }}</td>
                <td class="text-right py-2">{{ formatAmount(row.debit) }}</td>
                <td class="text-right py-2">{{ formatAmount(row.credit) }}</td>
                <td class="text-right py-2 font-medium">{{ formatAmount(row.balance) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Consolidated P&L -->
        <div v-if="consolidation" class="bg-white rounded-lg shadow p-6 mb-6">
          <h2 class="text-lg font-semibold mb-4">Consolidated Profit & Loss</h2>
          <div v-for="item in pnlIncome" :key="item.account_code" class="flex justify-between py-1">
            <span>{{ item.account_name }} ({{ item.account_code }})</span>
            <span class="font-medium">{{ formatAmount(item.amount) }}</span>
          </div>
          <div class="flex justify-between py-2 border-t font-bold text-green-700">
            <span>Total Income</span>
            <span>{{ formatAmount(pnlTotalIncome) }}</span>
          </div>
          <div v-for="item in pnlExpenses" :key="item.account_code" class="flex justify-between py-1">
            <span>{{ item.account_name }} ({{ item.account_code }})</span>
            <span class="font-medium">{{ formatAmount(item.amount) }}</span>
          </div>
          <div class="flex justify-between py-2 border-t font-bold text-red-700">
            <span>Total Expenses</span>
            <span>{{ formatAmount(pnlTotalExpenses) }}</span>
          </div>
          <div class="flex justify-between py-2 border-t-2 font-bold text-lg">
            <span>Net {{ pnlTotalIncome >= pnlTotalExpenses ? 'Profit' : 'Loss' }}</span>
            <span :class="pnlTotalIncome >= pnlTotalExpenses ? 'text-green-700' : 'text-red-700'">
              {{ formatAmount(Math.abs(pnlTotalIncome - pnlTotalExpenses)) }}
            </span>
          </div>
        </div>

        <!-- Consolidated Balance Sheet -->
        <div v-if="consolidation" class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold mb-4">Consolidated Balance Sheet</h2>

          <h3 class="font-medium text-gray-600 mb-2">Assets</h3>
          <div v-for="item in bsAssets" :key="item.account_code" class="flex justify-between py-1">
            <span>{{ item.account_name }} ({{ item.account_code }})</span>
            <span class="font-medium">{{ formatAmount(item.amount) }}</span>
          </div>
          <div class="flex justify-between py-2 border-t font-bold">
            <span>Total Assets</span>
            <span>{{ formatAmount(bsTotalAssets) }}</span>
          </div>

          <h3 class="font-medium text-gray-600 mt-4 mb-2">Liabilities</h3>
          <div v-for="item in bsLiabilities" :key="item.account_code" class="flex justify-between py-1">
            <span>{{ item.account_name }} ({{ item.account_code }})</span>
            <span class="font-medium">{{ formatAmount(item.amount) }}</span>
          </div>
          <div class="flex justify-between py-2 border-t font-bold">
            <span>Total Liabilities</span>
            <span>{{ formatAmount(bsTotalLiabilities) }}</span>
          </div>

          <h3 class="font-medium text-gray-600 mt-4 mb-2">Equity</h3>
          <div v-for="item in bsEquity" :key="item.account_code" class="flex justify-between py-1">
            <span>{{ item.account_name }} ({{ item.account_code }})</span>
            <span class="font-medium">{{ formatAmount(item.amount) }}</span>
          </div>
          <div class="flex justify-between py-2 border-t font-bold">
            <span>Total Equity</span>
            <span>{{ formatAmount(bsTotalEquity) }}</span>
          </div>

          <div class="flex justify-between py-2 border-t-2 mt-2 font-bold text-lg">
            <span>Liabilities + Equity</span>
            <span>{{ formatAmount(bsTotalLiabilities + bsTotalEquity) }}</span>
          </div>
        </div>

        <!-- Empty state -->
        <div v-else class="bg-white rounded-lg shadow p-12 text-center text-gray-500">
          <p class="text-lg">No consolidation data for {{ period }}</p>
          <p class="mt-2">Select a period and click "Run Consolidation" to generate consolidated statements.</p>
        </div>

        <!-- History -->
        <div v-if="history.length" class="mt-6 bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold mb-4">Consolidation History</h2>
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b">
                <th class="text-left py-2">Period</th>
                <th class="text-left py-2">Status</th>
                <th class="text-right py-2">Consolidated At</th>
                <th class="text-right py-2">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="entry in history" :key="entry.id" class="border-b hover:bg-gray-50">
                <td class="py-2">{{ entry.period }}</td>
                <td class="py-2">
                  <span :class="entry.status === 'completed' ? 'text-green-600' : 'text-yellow-600'">
                    {{ entry.status }}
                  </span>
                </td>
                <td class="text-right py-2">{{ entry.consolidated_at || '-' }}</td>
                <td class="text-right py-2">
                  <Link :href="route('growfinance.consolidation.show', entry.id)" class="text-indigo-600 hover:underline">
                    View
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/GrowFinanceLayout.vue'

const props = defineProps({
  consolidation: { type: Object, default: null },
  history: { type: Array, default: () => [] },
  currentPeriod: { type: String, default: '' },
})

const period = ref(props.currentPeriod)
const reportingCurrency = ref('ZMW')

const consolidationData = computed(() => props.consolidation?.consolidated_data || {})

const trialBalance = computed(() => consolidationData.value?.trial_balance || [])

const pnlIncome = computed(() => consolidationData.value?.profit_and_loss?.income || [])
const pnlExpenses = computed(() => consolidationData.value?.profit_and_loss?.expenses || [])
const pnlTotalIncome = computed(() => consolidationData.value?.profit_and_loss?.total_income || 0)
const pnlTotalExpenses = computed(() => consolidationData.value?.profit_and_loss?.total_expenses || 0)

const bsAssets = computed(() => consolidationData.value?.balance_sheet?.assets || [])
const bsLiabilities = computed(() => consolidationData.value?.balance_sheet?.liabilities || [])
const bsEquity = computed(() => consolidationData.value?.balance_sheet?.equity || [])
const bsTotalAssets = computed(() => consolidationData.value?.balance_sheet?.total_assets || 0)
const bsTotalLiabilities = computed(() => consolidationData.value?.balance_sheet?.total_liabilities || 0)
const bsTotalEquity = computed(() => consolidationData.value?.balance_sheet?.total_equity || 0)

function formatAmount(val) {
  return new Intl.NumberFormat('en-ZM', { style: 'currency', currency: reportingCurrency.value }).format(val || 0)
}

function runConsolidation() {
  router.post(route('growfinance.consolidation.run'), {
    period: period.value,
    reporting_currency: reportingCurrency.value,
  })
}
</script>
