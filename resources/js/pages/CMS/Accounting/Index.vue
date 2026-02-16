<template>
  <CMSLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Chart of Accounts</h1>
          <p class="text-sm text-gray-500 mt-1">Manage your accounting structure</p>
        </div>
        <div class="flex gap-3">
          <Link
            :href="route('cms.accounting.trial-balance')"
            class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
          >
            Trial Balance
          </Link>
          <Link
            :href="route('cms.accounting.create')"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2"
          >
            <PlusIcon class="h-5 w-5" aria-hidden="true" />
            Add Account
          </Link>
        </div>
      </div>

      <!-- Account Type Tabs -->
      <div class="flex gap-2 overflow-x-auto pb-2 mb-6 border-b border-gray-200">
        <button
          v-for="type in accountTypes"
          :key="type.value"
          @click="selectedType = type.value"
          :class="[
            'px-4 py-2 rounded-t-lg text-sm font-medium whitespace-nowrap transition-colors',
            selectedType === type.value
              ? `bg-${type.color}-50 text-${type.color}-700 border-b-2 border-${type.color}-600`
              : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
          ]"
        >
          {{ type.label }}
        </button>
      </div>

      <!-- Accounts List -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div v-if="filteredAccounts.length > 0" class="divide-y divide-gray-200">
          <div
            v-for="account in filteredAccounts"
            :key="account.id"
            class="p-4 hover:bg-gray-50 transition cursor-pointer"
            @click="router.visit(route('cms.accounting.show', account.id))"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-4">
                <div :class="[
                  'w-12 h-12 rounded-lg flex items-center justify-center text-sm font-bold',
                  getTypeColor(account.type)
                ]">
                  {{ account.code }}
                </div>
                <div>
                  <p class="font-medium text-gray-900">{{ account.name }}</p>
                  <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs text-gray-500">{{ account.category || account.type }}</span>
                    <span v-if="account.is_system" class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded">
                      System
                    </span>
                    <span v-if="!account.is_active" class="px-2 py-0.5 bg-red-100 text-red-600 text-xs rounded">
                      Inactive
                    </span>
                  </div>
                </div>
              </div>
              <div class="text-right">
                <p :class="[
                  'text-lg font-semibold',
                  account.current_balance >= 0 ? 'text-gray-900' : 'text-red-600'
                ]">
                  {{ formatMoney(account.current_balance) }}
                </p>
                <p class="text-xs text-gray-500 mt-1">Current Balance</p>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="p-12 text-center">
          <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <ChartBarIcon class="h-8 w-8 text-gray-400" aria-hidden="true" />
          </div>
          <p class="text-gray-500 text-sm">No accounts in this category</p>
          <Link
            :href="route('cms.accounting.create')"
            class="inline-block mt-4 text-blue-600 hover:text-blue-700 text-sm font-medium"
          >
            Create your first account
          </Link>
        </div>
      </div>

      <!-- Summary Stats -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
          <p class="text-sm text-gray-500">Total Assets</p>
          <p class="text-2xl font-bold text-blue-600 mt-1">
            {{ formatMoney(getTotalByType('asset')) }}
          </p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
          <p class="text-sm text-gray-500">Total Liabilities</p>
          <p class="text-2xl font-bold text-red-600 mt-1">
            {{ formatMoney(getTotalByType('liability')) }}
          </p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
          <p class="text-sm text-gray-500">Total Equity</p>
          <p class="text-2xl font-bold text-purple-600 mt-1">
            {{ formatMoney(getTotalByType('equity')) }}
          </p>
        </div>
      </div>
    </div>
  </CMSLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import CMSLayout from '@/Layouts/CMSLayout.vue'
import { PlusIcon, ChartBarIcon } from '@heroicons/vue/24/outline'

interface Account {
  id: number
  code: string
  name: string
  type: string
  category: string | null
  current_balance: number
  is_system: boolean
  is_active: boolean
}

interface Props {
  accounts: Record<string, Account[]>
  accountTypes: Array<{ value: string; label: string; color: string }>
}

const props = defineProps<Props>()

const selectedType = ref('asset')

const filteredAccounts = computed(() => {
  return props.accounts[selectedType.value] || []
})

const getTotalByType = (type: string): number => {
  const accounts = props.accounts[type] || []
  return accounts.reduce((sum, account) => sum + parseFloat(account.current_balance.toString()), 0)
}

const getTypeColor = (type: string): string => {
  const colors: Record<string, string> = {
    asset: 'bg-blue-100 text-blue-700',
    liability: 'bg-red-100 text-red-700',
    equity: 'bg-purple-100 text-purple-700',
    income: 'bg-emerald-100 text-emerald-700',
    expense: 'bg-amber-100 text-amber-700',
  }
  return colors[type] || 'bg-gray-100 text-gray-700'
}

const formatMoney = (amount: number): string => {
  return 'K' + Math.abs(amount).toLocaleString('en-US', { 
    minimumFractionDigits: 2, 
    maximumFractionDigits: 2 
  })
}
</script>
