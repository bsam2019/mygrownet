<script setup lang="ts">
import { formatCurrency, formatDate } from '@/utils';

defineProps<{
  transactions: Array<{
    id: number;
    reference_number: string;
    investment_amount: number;
    transaction_type: string;
    status: string;
    created_at: string;
  }>;
}>();
</script>

<template>
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <tr v-for="transaction in transactions" :key="transaction.id">
          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
            {{ transaction.reference_number }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            {{ formatCurrency(transaction.investment_amount) }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            <span :class="{
              'px-2 py-1 rounded-full text-xs font-medium': true,
              'bg-green-100 text-green-800': transaction.transaction_type === 'return',
              'bg-blue-100 text-blue-800': transaction.transaction_type === 'deposit',
              'bg-red-100 text-red-800': transaction.transaction_type === 'withdrawal'
            }">
              {{ transaction.transaction_type }}
            </span>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            <span :class="{
              'px-2 py-1 rounded-full text-xs font-medium': true,
              'bg-yellow-100 text-yellow-800': transaction.status === 'pending',
              'bg-green-100 text-green-800': transaction.status === 'completed',
              'bg-red-100 text-red-800': transaction.status === 'failed'
            }">
              {{ transaction.status }}
            </span>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            {{ formatDate(transaction.created_at) }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
