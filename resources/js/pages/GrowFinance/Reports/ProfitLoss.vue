<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Profit & Loss</h1>
                <p class="text-gray-500 text-sm">{{ startDate }} to {{ endDate }}</p>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 gap-4 mb-6">
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-4 text-white">
                    <p class="text-emerald-100 text-sm">Total Income</p>
                    <p class="text-3xl font-bold">{{ formatMoney(income) }}</p>
                </div>
                
                <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-2xl p-4 text-white">
                    <p class="text-red-100 text-sm">Total Expenses</p>
                    <p class="text-3xl font-bold">{{ formatMoney(expenses) }}</p>
                </div>
                
                <div :class="[
                    'rounded-2xl p-4 text-white',
                    netProfit >= 0 
                        ? 'bg-gradient-to-br from-blue-500 to-blue-600' 
                        : 'bg-gradient-to-br from-gray-500 to-gray-600'
                ]">
                    <p class="opacity-80 text-sm">Net {{ netProfit >= 0 ? 'Profit' : 'Loss' }}</p>
                    <p class="text-3xl font-bold">{{ formatMoney(Math.abs(netProfit)) }}</p>
                </div>
            </div>

            <!-- Expense Breakdown -->
            <div v-if="expensesByCategory.length > 0" class="bg-white rounded-2xl shadow-sm p-4">
                <h3 class="font-semibold text-gray-900 mb-4">Expense Breakdown</h3>
                <div class="space-y-3">
                    <div v-for="cat in expensesByCategory" :key="cat.category" class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full bg-red-400"></div>
                            <span class="text-sm text-gray-600">{{ cat.category || 'Uncategorized' }}</span>
                        </div>
                        <span class="font-medium text-gray-900">{{ formatMoney(cat.total) }}</span>
                    </div>
                </div>
            </div>

            <!-- Report Actions -->
            <div class="mt-6 flex gap-3">
                <Link 
                    :href="route('growfinance.reports.balance-sheet')"
                    class="flex-1 py-3 rounded-xl bg-white text-center font-medium text-gray-700 shadow-sm active:bg-gray-50"
                >
                    Balance Sheet
                </Link>
                <Link 
                    :href="route('growfinance.reports.cash-flow')"
                    class="flex-1 py-3 rounded-xl bg-white text-center font-medium text-gray-700 shadow-sm active:bg-gray-50"
                >
                    Cash Flow
                </Link>
            </div>
        </div>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';

interface Props {
    income: number;
    expenses: number;
    netProfit: number;
    expensesByCategory: Array<{ category: string; total: number }>;
    startDate: string;
    endDate: string;
}

defineProps<Props>();

const formatMoney = (amount: number) => {
    return 'K' + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};
</script>
