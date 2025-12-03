<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <!-- Header -->
            <div class="flex items-center gap-3 mb-5">
                <Link :href="route('growfinance.reports.profit-loss')" class="p-2 -ml-2 rounded-lg hover:bg-gray-100">
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                </Link>
                <div class="flex-1">
                    <h1 class="text-xl font-bold text-gray-900">Trial Balance</h1>
                    <p class="text-sm text-gray-500">As of {{ formatDate(asOfDate) }}</p>
                </div>
                <button 
                    @click="exportReport"
                    class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200"
                    aria-label="Export report"
                >
                    <ArrowDownTrayIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                </button>
            </div>

            <!-- Balance Status -->
            <div :class="['rounded-xl p-4 mb-6', isBalanced ? 'bg-emerald-50' : 'bg-red-50']">
                <div class="flex items-center gap-2">
                    <CheckCircleIcon v-if="isBalanced" class="h-5 w-5 text-emerald-600" aria-hidden="true" />
                    <ExclamationCircleIcon v-else class="h-5 w-5 text-red-600" aria-hidden="true" />
                    <span :class="['font-medium', isBalanced ? 'text-emerald-700' : 'text-red-700']">
                        {{ isBalanced ? 'Trial balance is in balance' : 'Trial balance is out of balance' }}
                    </span>
                </div>
            </div>

            <!-- Trial Balance Table -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <!-- Header -->
                <div class="grid grid-cols-3 gap-2 px-4 py-3 bg-gray-50 border-b border-gray-100">
                    <div class="text-xs font-semibold text-gray-600 uppercase">Account</div>
                    <div class="text-xs font-semibold text-gray-600 uppercase text-right">Debit</div>
                    <div class="text-xs font-semibold text-gray-600 uppercase text-right">Credit</div>
                </div>

                <!-- Rows -->
                <div v-if="balances.length > 0" class="divide-y divide-gray-50">
                    <div 
                        v-for="item in balances" 
                        :key="item.account.id"
                        class="grid grid-cols-3 gap-2 px-4 py-3"
                    >
                        <div>
                            <p class="font-medium text-gray-900 text-sm">{{ item.account.name }}</p>
                            <p class="text-xs text-gray-500">{{ item.account.code }}</p>
                        </div>
                        <div class="text-right">
                            <span v-if="item.debit > 0" class="font-medium text-gray-900">
                                {{ formatMoney(item.debit) }}
                            </span>
                            <span v-else class="text-gray-400">-</span>
                        </div>
                        <div class="text-right">
                            <span v-if="item.credit > 0" class="font-medium text-gray-900">
                                {{ formatMoney(item.credit) }}
                            </span>
                            <span v-else class="text-gray-400">-</span>
                        </div>
                    </div>
                </div>
                <div v-else class="p-8 text-center">
                    <p class="text-gray-500 text-sm">No accounts found</p>
                </div>

                <!-- Totals -->
                <div class="grid grid-cols-3 gap-2 px-4 py-3 bg-gray-50 border-t border-gray-200">
                    <div class="font-bold text-gray-900">TOTALS</div>
                    <div class="text-right font-bold text-gray-900">{{ formatMoney(totalDebits) }}</div>
                    <div class="text-right font-bold text-gray-900">{{ formatMoney(totalCredits) }}</div>
                </div>
            </div>

            <!-- Report Navigation -->
            <div class="mt-6 grid grid-cols-2 gap-3">
                <Link 
                    :href="route('growfinance.reports.profit-loss')"
                    class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow"
                >
                    <p class="font-medium text-gray-900 text-sm">Profit & Loss</p>
                    <p class="text-xs text-gray-500">Income statement</p>
                </Link>
                <Link 
                    :href="route('growfinance.reports.general-ledger')"
                    class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow"
                >
                    <p class="font-medium text-gray-900 text-sm">General Ledger</p>
                    <p class="text-xs text-gray-500">Account details</p>
                </Link>
            </div>
        </div>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import {
    ArrowLeftIcon,
    ArrowDownTrayIcon,
    CheckCircleIcon,
    ExclamationCircleIcon,
} from '@heroicons/vue/24/outline';

interface Account {
    id: number;
    code: string;
    name: string;
    type: string;
}

interface BalanceItem {
    account: Account;
    debit: number;
    credit: number;
}

const props = defineProps<{
    balances: BalanceItem[];
    totalDebits: number;
    totalCredits: number;
    isBalanced: boolean;
    asOfDate: string;
}>();

const formatMoney = (amount: number) => {
    return 'K' + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
};

const exportReport = () => {
    window.location.href = route('growfinance.reports.export', { type: 'trial-balance', format: 'csv' });
};
</script>
