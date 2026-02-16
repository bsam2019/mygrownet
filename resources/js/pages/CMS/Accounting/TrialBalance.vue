<template>
  <CMSLayout>
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Trial Balance</h1>
          <p class="text-sm text-gray-500 mt-1">Verify accounting accuracy</p>
        </div>
        <Link
          :href="route('cms.accounting.index')"
          class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
        >
          Back to Accounts
        </Link>
      </div>

      <!-- Balance Status -->
      <div :class="[
        'p-4 rounded-lg mb-6',
        trialBalance.is_balanced ? 'bg-emerald-50 border border-emerald-200' : 'bg-red-50 border border-red-200'
      ]">
        <div class="flex items-center gap-3">
          <CheckCircleIcon v-if="trialBalance.is_balanced" class="h-6 w-6 text-emerald-600" aria-hidden="true" />
          <ExclamationTriangleIcon v-else class="h-6 w-6 text-red-600" aria-hidden="true" />
          <div>
            <p :class="[
              'font-semibold',
              trialBalance.is_balanced ? 'text-emerald-900' : 'text-red-900'
            ]">
              {{ trialBalance.is_balanced ? 'Books are balanced' : 'Books are out of balance' }}
            </p>
            <p :class="[
              'text-sm',
              trialBalance.is_balanced ? 'text-emerald-700' : 'text-red-700'
            ]">
              Total Debits: {{ formatMoney(trialBalance.total_debits) }} | 
              Total Credits: {{ formatMoney(trialBalance.total_credits) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Trial Balance Table -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Code
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Account Name
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Debit
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Credit
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="balance in trialBalance.balances" :key="balance.account.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ balance.account.code }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ balance.account.name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                {{ balance.debit > 0 ? formatMoney(balance.debit) : '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                {{ balance.credit > 0 ? formatMoney(balance.credit) : '-' }}
              </td>
            </tr>
          </tbody>
          <tfoot class="bg-gray-50 border-t-2 border-gray-300">
            <tr>
              <td colspan="2" class="px-6 py-4 text-sm font-bold text-gray-900">
                TOTALS
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-gray-900">
                {{ formatMoney(trialBalance.total_debits) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-gray-900">
                {{ formatMoney(trialBalance.total_credits) }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </CMSLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import CMSLayout from '@/Layouts/CMSLayout.vue'
import { CheckCircleIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

interface Account {
  id: number
  code: string
  name: string
}

interface Balance {
  account: Account
  debit: number
  credit: number
}

interface Props {
  trialBalance: {
    balances: Balance[]
    total_debits: number
    total_credits: number
    is_balanced: boolean
  }
}

defineProps<Props>()

const formatMoney = (amount: number): string => {
  return 'K' + amount.toLocaleString('en-US', { 
    minimumFractionDigits: 2, 
    maximumFractionDigits: 2 
  })
}
</script>
