<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ArrowTrendingUpIcon, ArrowLeftIcon, CalendarIcon } from '@heroicons/vue/24/outline';
import BMSLayout from '@/Layouts/BMSLayout.vue';

defineOptions({
  layout: BMSLayout
})

interface Props {
    cashFlow: any;
    growFinanceEnabled: boolean;
    startDate: string;
    endDate: string;
}

const props = defineProps<Props>();

const startDate = ref(props.startDate);
const endDate = ref(props.endDate);

const formatCurrency = (amount: number) => {
    return `K${amount.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
};

const applyFilter = () => {
    window.location.href = route('bms.reports.cash-flow-statement', { 
        start_date: startDate.value,
        end_date: endDate.value 
    });
};
</script>

<template>
    <Head title="Cash Flow Statement - CMS Reports" />

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
                        <ArrowTrendingUpIcon class="h-6 w-6 text-emerald-600" aria-hidden="true" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Cash Flow Statement</h1>
                        <p class="mt-1 text-sm text-gray-600">Operating, Investing, and Financing Activities</p>
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
                        Start Date
                    </label>
                    <input
                        v-model="startDate"
                        type="date"
                        class="block w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                    />
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <CalendarIcon class="h-4 w-4 inline mr-1" aria-hidden="true" />
                        End Date
                    </label>
                    <input
                        v-model="endDate"
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
                        Enable the GrowFinance (Full Accounting) module in Settings → Modules to view complete cash flow statement.
                    </p>
                    <Link :href="route('bms.settings.index')" class="mt-2 inline-flex items-center text-sm font-medium text-amber-800 hover:text-amber-900">
                        Go to Settings →
                    </Link>
                </div>
            </div>
        </div>

        <!-- Cash Flow Statement Content -->
        <div v-if="growFinanceEnabled && cashFlow" class="space-y-6">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-lg bg-white p-6 shadow border-l-4 border-blue-500">
                    <div class="text-sm font-medium text-gray-600">Opening Balance</div>
                    <div class="mt-2 text-3xl font-bold text-blue-600">{{ formatCurrency(cashFlow.opening_balance) }}</div>
                </div>
                <div class="rounded-lg bg-white p-6 shadow border-l-4 border-emerald-500">
                    <div class="text-sm font-medium text-gray-600">Net Change</div>
                    <div class="mt-2 text-3xl font-bold" :class="cashFlow.net_change >= 0 ? 'text-emerald-600' : 'text-red-600'">
                        {{ formatCurrency(cashFlow.net_change) }}
                    </div>
                </div>
                <div class="rounded-lg bg-white p-6 shadow border-l-4 border-purple-500">
                    <div class="text-sm font-medium text-gray-600">Closing Balance</div>
                    <div class="mt-2 text-3xl font-bold text-purple-600">{{ formatCurrency(cashFlow.closing_balance) }}</div>
                </div>
            </div>

            <!-- Operating Activities -->
            <div class="rounded-lg bg-white shadow overflow-hidden">
                <div class="bg-blue-50 px-6 py-4 border-b border-blue-100">
                    <h2 class="text-lg font-semibold text-blue-900">Cash Flow from Operating Activities</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-2 pl-4 mb-4">
                        <div v-for="(item, index) in cashFlow.operating_activities.items" :key="index" class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ item.name }}</span>
                            <span class="font-medium" :class="item.amount >= 0 ? 'text-green-600' : 'text-red-600'">
                                {{ formatCurrency(item.amount) }}
                            </span>
                        </div>
                        <div v-if="cashFlow.operating_activities.items.length === 0" class="text-sm text-gray-500 italic">
                            No operating activities
                        </div>
                    </div>
                    <div class="pt-4 border-t-2 border-blue-200">
                        <div class="flex justify-between text-lg font-bold">
                            <span class="text-blue-900">Net Cash from Operating Activities</span>
                            <span :class="cashFlow.operating_activities.total >= 0 ? 'text-green-600' : 'text-red-600'">
                                {{ formatCurrency(cashFlow.operating_activities.total) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Investing Activities -->
            <div class="rounded-lg bg-white shadow overflow-hidden">
                <div class="bg-purple-50 px-6 py-4 border-b border-purple-100">
                    <h2 class="text-lg font-semibold text-purple-900">Cash Flow from Investing Activities</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-2 pl-4 mb-4">
                        <div v-for="(item, index) in cashFlow.investing_activities.items" :key="index" class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ item.name }}</span>
                            <span class="font-medium" :class="item.amount >= 0 ? 'text-green-600' : 'text-red-600'">
                                {{ formatCurrency(item.amount) }}
                            </span>
                        </div>
                        <div v-if="cashFlow.investing_activities.items.length === 0" class="text-sm text-gray-500 italic">
                            No investing activities
                        </div>
                    </div>
                    <div class="pt-4 border-t-2 border-purple-200">
                        <div class="flex justify-between text-lg font-bold">
                            <span class="text-purple-900">Net Cash from Investing Activities</span>
                            <span :class="cashFlow.investing_activities.total >= 0 ? 'text-green-600' : 'text-red-600'">
                                {{ formatCurrency(cashFlow.investing_activities.total) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financing Activities -->
            <div class="rounded-lg bg-white shadow overflow-hidden">
                <div class="bg-indigo-50 px-6 py-4 border-b border-indigo-100">
                    <h2 class="text-lg font-semibold text-indigo-900">Cash Flow from Financing Activities</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-2 pl-4 mb-4">
                        <div v-for="(item, index) in cashFlow.financing_activities.items" :key="index" class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ item.name }}</span>
                            <span class="font-medium" :class="item.amount >= 0 ? 'text-green-600' : 'text-red-600'">
                                {{ formatCurrency(item.amount) }}
                            </span>
                        </div>
                        <div v-if="cashFlow.financing_activities.items.length === 0" class="text-sm text-gray-500 italic">
                            No financing activities
                        </div>
                    </div>
                    <div class="pt-4 border-t-2 border-indigo-200">
                        <div class="flex justify-between text-lg font-bold">
                            <span class="text-indigo-900">Net Cash from Financing Activities</span>
                            <span :class="cashFlow.financing_activities.total >= 0 ? 'text-green-600' : 'text-red-600'">
                                {{ formatCurrency(cashFlow.financing_activities.total) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Net Change Summary -->
            <div class="rounded-lg bg-gray-50 border-2 border-gray-300 p-6">
                <div class="space-y-3">
                    <div class="flex justify-between text-base">
                        <span class="text-gray-700">Opening Cash Balance</span>
                        <span class="font-semibold text-gray-900">{{ formatCurrency(cashFlow.opening_balance) }}</span>
                    </div>
                    <div class="flex justify-between text-base">
                        <span class="text-gray-700">Net Change in Cash</span>
                        <span class="font-semibold" :class="cashFlow.net_change >= 0 ? 'text-green-600' : 'text-red-600'">
                            {{ formatCurrency(cashFlow.net_change) }}
                        </span>
                    </div>
                    <div class="pt-3 border-t-2 border-gray-400">
                        <div class="flex justify-between text-xl font-bold">
                            <span class="text-gray-900">Closing Cash Balance</span>
                            <span class="text-gray-900">{{ formatCurrency(cashFlow.closing_balance) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!growFinanceEnabled || !cashFlow" class="rounded-lg bg-white p-12 shadow text-center">
            <ArrowTrendingUpIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
            <h3 class="mt-4 text-lg font-medium text-gray-900">No Cash Flow Data</h3>
            <p class="mt-2 text-sm text-gray-600">
                Enable GrowFinance module to view complete cash flow statement.
            </p>
        </div>
    </div>
</template>
