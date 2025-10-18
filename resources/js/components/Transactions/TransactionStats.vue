<script setup lang="ts">
import { formatCurrency } from '@/utils';

defineProps<{
  totalInvested: number;
  totalEarnings: number;
  pendingTransactions: number;
}>();
</script>

<template>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="text-sm font-medium text-gray-500">Total Invested</h3>
      <p class="mt-2 text-3xl font-semibold text-gray-900">{{ formatCurrency(totalInvested) }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="text-sm font-medium text-gray-500">Total Earnings</h3>
      <p class="mt-2 text-3xl font-semibold text-green-600">{{ formatCurrency(totalEarnings) }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="text-sm font-medium text-gray-500">Pending Transactions</h3>
      <p class="mt-2 text-3xl font-semibold text-yellow-600">{{ pendingTransactions }}</p>
    </div>
  </div>
</template>
