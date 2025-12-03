<script setup lang="ts">
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    ArrowLeftIcon,
    DocumentArrowDownIcon,
    CalendarIcon,
} from '@heroicons/vue/24/outline';

interface Account {
    id: number;
    code: string;
    name: string;
    current_balance: number;
}

interface BalanceSheetData {
    assets: {
        current: Account[];
        fixed: Account[];
        total: number;
    };
    liabilities: {
        current: Account[];
        longTerm: Account[];
        total: number;
    };
    equity: {
        accounts: Account[];
        retainedEarnings: number;
        total: number;
    };
}

defineProps<{
    data: BalanceSheetData;
    asOfDate: string;
}>();

const selectedDate = ref(new Date().toISOString().split('T')[0]);

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 2,
    }).format(amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-ZM', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>

<template>
    <GrowFinanceLayout>
        <Head title="Balance Sheet" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('growfinance.dashboard')"
                        class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Balance Sheet</h1>
                        <p class="text-sm text-gray-500">As of {{ formatDate(asOfDate) }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <CalendarIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                        <input
                            v-model="selectedDate"
                            type="date"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        <DocumentArrowDownIcon class="h-4 w-4" aria-hidden="true" />
                        Export PDF
                    </button>
                </div>
            </div>

            <!-- Balance Sheet -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200 text-center">
                    <h2 class="text-xl font-bold text-gray-900">Statement of Financial Position</h2>
                    <p class="text-sm text-gray-500">As of {{ formatDate(asOfDate) }}</p>
                </div>

                <div class="p-6 space-y-8">
                    <!-- Assets -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">ASSETS</h3>
                        
                        <!-- Current Assets -->
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Current Assets</h4>
                            <div class="space-y-2 pl-4">
                                <div
                                    v-for="account in data.assets.current"
                                    :key="account.id"
                                    class="flex justify-between text-sm"
                                >
                                    <span class="text-gray-600">{{ account.name }}</span>
                                    <span class="text-gray-900">{{ formatCurrency(account.current_balance) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Fixed Assets -->
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Fixed Assets</h4>
                            <div class="space-y-2 pl-4">
                                <div
                                    v-for="account in data.assets.fixed"
                                    :key="account.id"
                                    class="flex justify-between text-sm"
                                >
                                    <span class="text-gray-600">{{ account.name }}</span>
                                    <span class="text-gray-900">{{ formatCurrency(account.current_balance) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between font-semibold text-gray-900 pt-2 border-t">
                            <span>Total Assets</span>
                            <span>{{ formatCurrency(data.assets.total) }}</span>
                        </div>
                    </div>

                    <!-- Liabilities -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">LIABILITIES</h3>
                        
                        <!-- Current Liabilities -->
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Current Liabilities</h4>
                            <div class="space-y-2 pl-4">
                                <div
                                    v-for="account in data.liabilities.current"
                                    :key="account.id"
                                    class="flex justify-between text-sm"
                                >
                                    <span class="text-gray-600">{{ account.name }}</span>
                                    <span class="text-gray-900">{{ formatCurrency(account.current_balance) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Long-term Liabilities -->
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Long-term Liabilities</h4>
                            <div class="space-y-2 pl-4">
                                <div
                                    v-for="account in data.liabilities.longTerm"
                                    :key="account.id"
                                    class="flex justify-between text-sm"
                                >
                                    <span class="text-gray-600">{{ account.name }}</span>
                                    <span class="text-gray-900">{{ formatCurrency(account.current_balance) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between font-semibold text-gray-900 pt-2 border-t">
                            <span>Total Liabilities</span>
                            <span>{{ formatCurrency(data.liabilities.total) }}</span>
                        </div>
                    </div>

                    <!-- Equity -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">EQUITY</h3>
                        
                        <div class="space-y-2 pl-4 mb-4">
                            <div
                                v-for="account in data.equity.accounts"
                                :key="account.id"
                                class="flex justify-between text-sm"
                            >
                                <span class="text-gray-600">{{ account.name }}</span>
                                <span class="text-gray-900">{{ formatCurrency(account.current_balance) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Retained Earnings</span>
                                <span class="text-gray-900">{{ formatCurrency(data.equity.retainedEarnings) }}</span>
                            </div>
                        </div>

                        <div class="flex justify-between font-semibold text-gray-900 pt-2 border-t">
                            <span>Total Equity</span>
                            <span>{{ formatCurrency(data.equity.total) }}</span>
                        </div>
                    </div>

                    <!-- Total Liabilities + Equity -->
                    <div class="pt-4 border-t-2 border-gray-300">
                        <div class="flex justify-between text-lg font-bold text-gray-900">
                            <span>Total Liabilities + Equity</span>
                            <span>{{ formatCurrency(data.liabilities.total + data.equity.total) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>
