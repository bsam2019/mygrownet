<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { CalculatorIcon, ArrowLeftIcon, CalendarIcon } from '@heroicons/vue/24/outline';
import BMSLayout from '@/Layouts/BMSLayout.vue';

defineOptions({
  layout: BMSLayout
})

interface Props {
    trialBalance: any;
    growFinanceEnabled: boolean;
    asOfDate: string;
}

const props = defineProps<Props>();

const asOfDate = ref(props.asOfDate);

const formatCurrency = (amount: number) => {
    return `K${amount.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
};

const applyFilter = () => {
    window.location.href = route('bms.reports.trial-balance', { as_of_date: asOfDate.value });
};
</script>

<template>
    <Head title="Trial Balance - CMS Reports" />

    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6">
            <Link :href="route('bms.reports.index')" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4">
                <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
                Back to Reports
            </Link>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-emerald-100 rounded-lg">
                        <CalculatorIcon class="h-6 w-6 text-emerald-600" aria-hidden="true" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Trial Balance</h1>
                        <p class="mt-1 text-sm text-gray-600">Account Balances Verification</p>
                    </div>
                </div>
                <div v-if="growFinanceEnabled" class="flex items-center gap-2 px-3 py-1.5 bg-emerald-50 border border-emerald-200 rounded-lg">
                    <div class="h-2 w-2 bg-emerald-500 rounded-full animate-pulse"></div>
                    <span class="text-sm font-medium text-emerald-700">GrowFinance Powered</span>
                </div>
            </div>
        </div>

        <!-- Date Filter -->
        <div class="mb-6 rounded-lg bg-white p-4 shadow">
            <div class="flex items-end gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <CalendarIcon class="h-4 w-4 inline mr-1" aria-hidden="true" />
                        As of Date
                    </label>
                    <input
                        v-model="asOfDate"
                        type="date"
                        class="block w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                    />
                </div>
                <button
                    @click="applyFilter"
                    class="rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 transition"
                >
                    Apply
                </button>
            </div>
        </div>

        <!-- GrowFinance Disabled Warning -->
        <div v-if="!growFinanceEnabled" class="mb-6 rounded-lg bg-amber-50 border border-amber-200 p-4">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-amber-800">GrowFinance Module Not Enabled</h3>
                    <p class="mt-1 text-sm text-amber-700">
                        Enable the GrowFinance (Full Accounting) module in Settings → Modules to view trial balance.
                    </p>
                    <Link :href="route('bms.settings.index')" class="mt-2 inline-flex items-center text-sm font-medium text-amber-800 hover:text-amber-900">
                        Go to Settings →
                    </Link>
                </div>
            </div>
        </div>

        <!-- Trial Balance Content -->
        <div v-if="growFinanceEnabled && trialBalance" class="space-y-6">
            <!-- Balance Verification -->
            <div v-if="trialBalance.is_balanced" class="rounded-lg bg-emerald-50 border border-emerald-200 p-4">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm font-medium text-emerald-800">Trial Balance is balanced (Total Debits = Total Credits)</span>
                </div>
            </div>

            <div v-else class="rounded-lg bg-red-50 border border-red-200 p-4">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm font-medium text-red-800">
                        Trial Balance is NOT balanced - Difference: {{ formatCurrency(Math.abs(trialBalance.total_debits - trialBalance.total_credits)) }}
                    </span>
                </div>
            </div>

            <!-- Trial Balance Table -->
            <div class="rounded-lg bg-white shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-emerald-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-emerald-900">Account Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-emerald-900">Account Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-emerald-900">Type</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-emerald-900">Debit</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-emerald-900">Credit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="balance in trialBalance.balances" :key="balance.account.id" class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ balance.account.code }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ balance.account.name }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-800">
                                        {{ balance.account.type }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium text-blue-600">
                                    {{ balance.debit > 0 ? formatCurrency(balance.debit) : '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium text-red-600">
                                    {{ balance.credit > 0 ? formatCurrency(balance.credit) : '-' }}
                                </td>
                            </tr>
                            <tr v-if="trialBalance.balances.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-600">
                                    No account balances found
                                </td>
                            </tr>
                            <!-- Totals Row -->
                            <tr v-if="trialBalance.balances.length > 0" class="bg-emerald-50 font-bold border-t-2 border-emerald-200">
                                <td colspan="3" class="px-6 py-4 text-sm text-emerald-900">TOTAL</td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-blue-600">
                                    {{ formatCurrency(trialBalance.total_debits) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-red-600">
                                    {{ formatCurrency(trialBalance.total_credits) }}
                                </td>
                            </tr>
                            <!-- Difference Row (if not balanced) -->
                            <tr v-if="!trialBalance.is_balanced && trialBalance.balances.length > 0" class="bg-red-50 font-semibold">
                                <td colspan="3" class="px-6 py-4 text-sm text-red-900">DIFFERENCE</td>
                                <td colspan="2" class="whitespace-nowrap px-6 py-4 text-right text-sm text-red-600">
                                    {{ formatCurrency(Math.abs(trialBalance.total_debits - trialBalance.total_credits)) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="rounded-lg bg-white p-6 shadow border-l-4 border-blue-500">
                    <div class="text-sm font-medium text-gray-600">Total Debits</div>
                    <div class="mt-2 text-3xl font-bold text-blue-600">{{ formatCurrency(trialBalance.total_debits) }}</div>
                </div>
                <div class="rounded-lg bg-white p-6 shadow border-l-4 border-red-500">
                    <div class="text-sm font-medium text-gray-600">Total Credits</div>
                    <div class="mt-2 text-3xl font-bold text-red-600">{{ formatCurrency(trialBalance.total_credits) }}</div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!growFinanceEnabled || !trialBalance" class="rounded-lg bg-white p-12 shadow text-center">
            <CalculatorIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
            <h3 class="mt-4 text-lg font-medium text-gray-900">No Trial Balance Data</h3>
            <p class="mt-2 text-sm text-gray-600">
                Enable GrowFinance module to view trial balance.
            </p>
        </div>
    </div>
</template>
