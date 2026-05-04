<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ScaleIcon, ArrowLeftIcon, CalendarIcon } from '@heroicons/vue/24/outline';
import CMSLayout from '@/Layouts/CMSLayout.vue';

defineOptions({
  layout: CMSLayout
})

interface Props {
    balanceSheet: any;
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
    window.location.href = route('cms.reports.balance-sheet', { as_of_date: asOfDate.value });
};
</script>

<template>
    <Head title="Balance Sheet - CMS Reports" />

    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6">
            <Link :href="route('cms.reports.index')" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4">
                <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
                Back to Reports
            </Link>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-emerald-100 rounded-lg">
                        <ScaleIcon class="h-6 w-6 text-emerald-600" aria-hidden="true" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Balance Sheet</h1>
                        <p class="mt-1 text-sm text-gray-600">Assets, Liabilities, and Equity</p>
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
                        Enable the GrowFinance (Full Accounting) module in Settings → Modules to view complete balance sheet with double-entry accounting.
                    </p>
                    <Link :href="route('cms.settings.index')" class="mt-2 inline-flex items-center text-sm font-medium text-amber-800 hover:text-amber-900">
                        Go to Settings →
                    </Link>
                </div>
            </div>
        </div>

        <!-- Balance Sheet Content -->
        <div v-if="growFinanceEnabled && balanceSheet" class="space-y-6">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-lg bg-white p-6 shadow border-l-4 border-blue-500">
                    <div class="text-sm font-medium text-gray-600">Total Assets</div>
                    <div class="mt-2 text-3xl font-bold text-blue-600">{{ formatCurrency(balanceSheet.assets.total) }}</div>
                </div>
                <div class="rounded-lg bg-white p-6 shadow border-l-4 border-red-500">
                    <div class="text-sm font-medium text-gray-600">Total Liabilities</div>
                    <div class="mt-2 text-3xl font-bold text-red-600">{{ formatCurrency(balanceSheet.liabilities.total) }}</div>
                </div>
                <div class="rounded-lg bg-white p-6 shadow border-l-4 border-emerald-500">
                    <div class="text-sm font-medium text-gray-600">Total Equity</div>
                    <div class="mt-2 text-3xl font-bold text-emerald-600">{{ formatCurrency(balanceSheet.equity.total) }}</div>
                </div>
            </div>

            <!-- Balance Verification -->
            <div v-if="balanceSheet.is_balanced" class="rounded-lg bg-emerald-50 border border-emerald-200 p-4">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm font-medium text-emerald-800">Balance Sheet is balanced (Assets = Liabilities + Equity)</span>
                </div>
            </div>

            <!-- Assets Section -->
            <div class="rounded-lg bg-white shadow overflow-hidden">
                <div class="bg-blue-50 px-6 py-4 border-b border-blue-100">
                    <h2 class="text-lg font-semibold text-blue-900">ASSETS</h2>
                </div>
                <div class="p-6">
                    <!-- Current Assets -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Current Assets</h3>
                        <div class="space-y-2 pl-4">
                            <div v-for="account in balanceSheet.assets.current.accounts" :key="account.id" class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ account.code }} - {{ account.name }}</span>
                                <span class="font-medium text-gray-900">{{ formatCurrency(account.balance) }}</span>
                            </div>
                            <div v-if="balanceSheet.assets.current.accounts.length === 0" class="text-sm text-gray-500 italic">
                                No current assets
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-t flex justify-between text-sm font-semibold">
                            <span class="text-gray-700">Total Current Assets</span>
                            <span class="text-gray-900">{{ formatCurrency(balanceSheet.assets.current.total) }}</span>
                        </div>
                    </div>

                    <!-- Fixed Assets -->
                    <div class="mb-4">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Fixed Assets</h3>
                        <div class="space-y-2 pl-4">
                            <div v-for="account in balanceSheet.assets.fixed.accounts" :key="account.id" class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ account.code }} - {{ account.name }}</span>
                                <span class="font-medium text-gray-900">{{ formatCurrency(account.balance) }}</span>
                            </div>
                            <div v-if="balanceSheet.assets.fixed.accounts.length === 0" class="text-sm text-gray-500 italic">
                                No fixed assets
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-t flex justify-between text-sm font-semibold">
                            <span class="text-gray-700">Total Fixed Assets</span>
                            <span class="text-gray-900">{{ formatCurrency(balanceSheet.assets.fixed.total) }}</span>
                        </div>
                    </div>

                    <!-- Total Assets -->
                    <div class="pt-4 border-t-2 border-blue-200">
                        <div class="flex justify-between text-lg font-bold">
                            <span class="text-blue-900">TOTAL ASSETS</span>
                            <span class="text-blue-600">{{ formatCurrency(balanceSheet.assets.total) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liabilities Section -->
            <div class="rounded-lg bg-white shadow overflow-hidden">
                <div class="bg-red-50 px-6 py-4 border-b border-red-100">
                    <h2 class="text-lg font-semibold text-red-900">LIABILITIES</h2>
                </div>
                <div class="p-6">
                    <!-- Current Liabilities -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Current Liabilities</h3>
                        <div class="space-y-2 pl-4">
                            <div v-for="account in balanceSheet.liabilities.current.accounts" :key="account.id" class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ account.code }} - {{ account.name }}</span>
                                <span class="font-medium text-gray-900">{{ formatCurrency(account.balance) }}</span>
                            </div>
                            <div v-if="balanceSheet.liabilities.current.accounts.length === 0" class="text-sm text-gray-500 italic">
                                No current liabilities
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-t flex justify-between text-sm font-semibold">
                            <span class="text-gray-700">Total Current Liabilities</span>
                            <span class="text-gray-900">{{ formatCurrency(balanceSheet.liabilities.current.total) }}</span>
                        </div>
                    </div>

                    <!-- Long-term Liabilities -->
                    <div class="mb-4">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Long-term Liabilities</h3>
                        <div class="space-y-2 pl-4">
                            <div v-for="account in balanceSheet.liabilities.long_term.accounts" :key="account.id" class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ account.code }} - {{ account.name }}</span>
                                <span class="font-medium text-gray-900">{{ formatCurrency(account.balance) }}</span>
                            </div>
                            <div v-if="balanceSheet.liabilities.long_term.accounts.length === 0" class="text-sm text-gray-500 italic">
                                No long-term liabilities
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-t flex justify-between text-sm font-semibold">
                            <span class="text-gray-700">Total Long-term Liabilities</span>
                            <span class="text-gray-900">{{ formatCurrency(balanceSheet.liabilities.long_term.total) }}</span>
                        </div>
                    </div>

                    <!-- Total Liabilities -->
                    <div class="pt-4 border-t-2 border-red-200">
                        <div class="flex justify-between text-lg font-bold">
                            <span class="text-red-900">TOTAL LIABILITIES</span>
                            <span class="text-red-600">{{ formatCurrency(balanceSheet.liabilities.total) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Equity Section -->
            <div class="rounded-lg bg-white shadow overflow-hidden">
                <div class="bg-emerald-50 px-6 py-4 border-b border-emerald-100">
                    <h2 class="text-lg font-semibold text-emerald-900">EQUITY</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-2 pl-4 mb-4">
                        <div v-for="account in balanceSheet.equity.accounts" :key="account.id" class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ account.code }} - {{ account.name }}</span>
                            <span class="font-medium text-gray-900">{{ formatCurrency(account.balance) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Retained Earnings</span>
                            <span class="font-medium text-gray-900">{{ formatCurrency(balanceSheet.equity.retained_earnings) }}</span>
                        </div>
                        <div v-if="balanceSheet.equity.accounts.length === 0 && balanceSheet.equity.retained_earnings === 0" class="text-sm text-gray-500 italic">
                            No equity accounts
                        </div>
                    </div>

                    <!-- Total Equity -->
                    <div class="pt-4 border-t-2 border-emerald-200">
                        <div class="flex justify-between text-lg font-bold">
                            <span class="text-emerald-900">TOTAL EQUITY</span>
                            <span class="text-emerald-600">{{ formatCurrency(balanceSheet.equity.total) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Liabilities + Equity -->
            <div class="rounded-lg bg-gray-50 border-2 border-gray-300 p-6">
                <div class="flex justify-between text-xl font-bold">
                    <span class="text-gray-900">TOTAL LIABILITIES + EQUITY</span>
                    <span class="text-gray-900">{{ formatCurrency(balanceSheet.liabilities.total + balanceSheet.equity.total) }}</span>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!growFinanceEnabled || !balanceSheet" class="rounded-lg bg-white p-12 shadow text-center">
            <ScaleIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
            <h3 class="mt-4 text-lg font-medium text-gray-900">No Balance Sheet Data</h3>
            <p class="mt-2 text-sm text-gray-600">
                Enable GrowFinance module to view complete balance sheet.
            </p>
        </div>
    </div>
</template>
