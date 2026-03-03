<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ArrowLeftIcon, DocumentArrowDownIcon } from '@heroicons/vue/24/outline';

interface BalanceSheetData {
    as_of_date: string;
    assets: {
        current_assets: {
            cash: number;
            wallet_balances: number;
            loans_receivable: {
                current: number;
                '30_days': number;
                '60_days': number;
                '90_days': number;
                default: number;
                total: number;
            };
            allowance_for_doubtful: number;
            net_loans_receivable: number;
            total_current_assets: number;
        };
        total_assets: number;
    };
    liabilities: {
        current_liabilities: {
            wallet_liabilities: number;
            pending_withdrawals: number;
            pending_commissions: number;
            total_current_liabilities: number;
        };
        total_liabilities: number;
    };
    equity: {
        retained_earnings: number;
        current_period_profit: number;
        total_equity: number;
    };
}

interface Props {
    balance_sheet: BalanceSheetData;
}

const props = defineProps<Props>();

const asOfDate = ref(props.balance_sheet.as_of_date);

const updateDate = () => {
    router.get(route('admin.financial-reports.balance-sheet'), {
        as_of_date: asOfDate.value,
    }, {
        preserveState: true,
    });
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 2,
    }).format(amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Balance Sheet" />

    <AdminLayout>
        <div class="py-6">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('admin.financial-reports.dashboard')"
                        class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4"
                    >
                        <ArrowLeftIcon class="h-4 w-4 mr-2" aria-hidden="true" />
                        Back to Financial Dashboard
                    </Link>
                    <div class="md:flex md:items-center md:justify-between">
                        <div class="flex-1 min-w-0">
                            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl">
                                Balance Sheet
                            </h2>
                            <p class="mt-1 text-sm text-gray-500">
                                As of {{ formatDate(balance_sheet.as_of_date) }}
                            </p>
                        </div>
                        <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                            <div class="flex items-center space-x-2">
                                <label for="as_of_date" class="text-sm text-gray-700">As of:</label>
                                <input
                                    v-model="asOfDate"
                                    type="date"
                                    id="as_of_date"
                                    class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500"
                                    @change="updateDate"
                                />
                            </div>
                            <button
                                type="button"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                            >
                                <DocumentArrowDownIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                                Export PDF
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Balance Sheet -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <!-- Assets Section -->
                    <div class="border-b border-gray-200">
                        <div class="bg-blue-50 px-6 py-4">
                            <h3 class="text-lg font-bold text-gray-900">ASSETS</h3>
                        </div>
                        
                        <!-- Current Assets -->
                        <div class="px-6 py-4">
                            <h4 class="text-md font-semibold text-gray-900 mb-3">Current Assets</h4>
                            
                            <div class="space-y-2 ml-4">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-700">Cash</span>
                                    <span class="font-medium text-gray-900">
                                        {{ formatCurrency(balance_sheet.assets.current_assets.cash) }}
                                    </span>
                                </div>
                                
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-700">Member Wallet Balances</span>
                                    <span class="font-medium text-gray-900">
                                        {{ formatCurrency(balance_sheet.assets.current_assets.wallet_balances) }}
                                    </span>
                                </div>
                                
                                <!-- Loans Receivable Breakdown -->
                                <div class="mt-3">
                                    <div class="flex justify-between text-sm font-medium text-gray-900 mb-2">
                                        <span>Loans Receivable:</span>
                                    </div>
                                    <div class="ml-4 space-y-1">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Current (0 days)</span>
                                            <span class="text-gray-900">
                                                {{ formatCurrency(balance_sheet.assets.current_assets.loans_receivable.current) }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">1-30 days overdue</span>
                                            <span class="text-gray-900">
                                                {{ formatCurrency(balance_sheet.assets.current_assets.loans_receivable['30_days']) }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">31-60 days overdue</span>
                                            <span class="text-gray-900">
                                                {{ formatCurrency(balance_sheet.assets.current_assets.loans_receivable['60_days']) }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">61-90 days overdue</span>
                                            <span class="text-gray-900">
                                                {{ formatCurrency(balance_sheet.assets.current_assets.loans_receivable['90_days']) }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">90+ days (default)</span>
                                            <span class="text-gray-900">
                                                {{ formatCurrency(balance_sheet.assets.current_assets.loans_receivable.default) }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between text-sm font-medium border-t border-gray-200 pt-1 mt-1">
                                            <span class="text-gray-900">Total Loans Receivable</span>
                                            <span class="text-gray-900">
                                                {{ formatCurrency(balance_sheet.assets.current_assets.loans_receivable.total) }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between text-sm text-red-600">
                                            <span>Less: Allowance for Doubtful Accounts</span>
                                            <span>
                                                ({{ formatCurrency(balance_sheet.assets.current_assets.allowance_for_doubtful) }})
                                            </span>
                                        </div>
                                        <div class="flex justify-between text-sm font-semibold border-t border-gray-200 pt-1 mt-1">
                                            <span class="text-gray-900">Net Loans Receivable</span>
                                            <span class="text-gray-900">
                                                {{ formatCurrency(balance_sheet.assets.current_assets.net_loans_receivable) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex justify-between text-sm font-bold border-t-2 border-gray-300 pt-2 mt-3">
                                    <span class="text-gray-900">Total Current Assets</span>
                                    <span class="text-gray-900">
                                        {{ formatCurrency(balance_sheet.assets.current_assets.total_current_assets) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-blue-100 px-6 py-3 border-t-2 border-blue-300">
                            <div class="flex justify-between">
                                <span class="text-lg font-bold text-gray-900">TOTAL ASSETS</span>
                                <span class="text-lg font-bold text-gray-900">
                                    {{ formatCurrency(balance_sheet.assets.total_assets) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Liabilities Section -->
                    <div class="border-b border-gray-200">
                        <div class="bg-orange-50 px-6 py-4">
                            <h3 class="text-lg font-bold text-gray-900">LIABILITIES</h3>
                        </div>
                        
                        <!-- Current Liabilities -->
                        <div class="px-6 py-4">
                            <h4 class="text-md font-semibold text-gray-900 mb-3">Current Liabilities</h4>
                            
                            <div class="space-y-2 ml-4">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-700">Member Wallet Liabilities</span>
                                    <span class="font-medium text-gray-900">
                                        {{ formatCurrency(balance_sheet.liabilities.current_liabilities.wallet_liabilities) }}
                                    </span>
                                </div>
                                
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-700">Pending Withdrawals</span>
                                    <span class="font-medium text-gray-900">
                                        {{ formatCurrency(balance_sheet.liabilities.current_liabilities.pending_withdrawals) }}
                                    </span>
                                </div>
                                
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-700">Pending Commissions</span>
                                    <span class="font-medium text-gray-900">
                                        {{ formatCurrency(balance_sheet.liabilities.current_liabilities.pending_commissions) }}
                                    </span>
                                </div>
                                
                                <div class="flex justify-between text-sm font-bold border-t-2 border-gray-300 pt-2 mt-3">
                                    <span class="text-gray-900">Total Current Liabilities</span>
                                    <span class="text-gray-900">
                                        {{ formatCurrency(balance_sheet.liabilities.current_liabilities.total_current_liabilities) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-orange-100 px-6 py-3 border-t-2 border-orange-300">
                            <div class="flex justify-between">
                                <span class="text-lg font-bold text-gray-900">TOTAL LIABILITIES</span>
                                <span class="text-lg font-bold text-gray-900">
                                    {{ formatCurrency(balance_sheet.liabilities.total_liabilities) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Equity Section -->
                    <div>
                        <div class="bg-green-50 px-6 py-4">
                            <h3 class="text-lg font-bold text-gray-900">EQUITY</h3>
                        </div>
                        
                        <div class="px-6 py-4">
                            <div class="space-y-2 ml-4">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-700">Retained Earnings</span>
                                    <span class="font-medium text-gray-900">
                                        {{ formatCurrency(balance_sheet.equity.retained_earnings) }}
                                    </span>
                                </div>
                                
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-700">Current Period Profit/Loss</span>
                                    <span class="font-medium" :class="balance_sheet.equity.current_period_profit >= 0 ? 'text-green-600' : 'text-red-600'">
                                        {{ formatCurrency(balance_sheet.equity.current_period_profit) }}
                                    </span>
                                </div>
                                
                                <div class="flex justify-between text-sm font-bold border-t-2 border-gray-300 pt-2 mt-3">
                                    <span class="text-gray-900">Total Equity</span>
                                    <span class="text-gray-900">
                                        {{ formatCurrency(balance_sheet.equity.total_equity) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-green-100 px-6 py-3 border-t-2 border-green-300">
                            <div class="flex justify-between">
                                <span class="text-lg font-bold text-gray-900">TOTAL LIABILITIES & EQUITY</span>
                                <span class="text-lg font-bold text-gray-900">
                                    {{ formatCurrency(balance_sheet.liabilities.total_liabilities + balance_sheet.equity.total_equity) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Balance Check -->
                <div class="mt-4 p-4 rounded-lg" :class="Math.abs(balance_sheet.assets.total_assets - (balance_sheet.liabilities.total_liabilities + balance_sheet.equity.total_equity)) < 0.01 ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium" :class="Math.abs(balance_sheet.assets.total_assets - (balance_sheet.liabilities.total_liabilities + balance_sheet.equity.total_equity)) < 0.01 ? 'text-green-800' : 'text-red-800'">
                            Balance Check: {{ Math.abs(balance_sheet.assets.total_assets - (balance_sheet.liabilities.total_liabilities + balance_sheet.equity.total_equity)) < 0.01 ? '✓ Balanced' : '✗ Not Balanced' }}
                        </span>
                        <span class="text-sm" :class="Math.abs(balance_sheet.assets.total_assets - (balance_sheet.liabilities.total_liabilities + balance_sheet.equity.total_equity)) < 0.01 ? 'text-green-600' : 'text-red-600'">
                            Difference: {{ formatCurrency(balance_sheet.assets.total_assets - (balance_sheet.liabilities.total_liabilities + balance_sheet.equity.total_equity)) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
