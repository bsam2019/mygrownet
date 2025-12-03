<script setup lang="ts">
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    ArrowLeftIcon,
    DocumentArrowDownIcon,
    CalendarIcon,
} from '@heroicons/vue/24/outline';

interface CashFlowData {
    operating: {
        items: { name: string; amount: number }[];
        total: number;
    };
    investing: {
        items: { name: string; amount: number }[];
        total: number;
    };
    financing: {
        items: { name: string; amount: number }[];
        total: number;
    };
    netChange: number;
    openingBalance: number;
    closingBalance: number;
}

defineProps<{
    data: CashFlowData;
    startDate: string;
    endDate: string;
}>();

const dateRange = ref({
    start: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
    end: new Date().toISOString().split('T')[0],
});

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
        <Head title="Cash Flow Statement" />

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
                        <h1 class="text-2xl font-bold text-gray-900">Cash Flow Statement</h1>
                        <p class="text-sm text-gray-500">{{ formatDate(startDate) }} - {{ formatDate(endDate) }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <CalendarIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                        <input
                            v-model="dateRange.start"
                            type="date"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                        <span class="text-gray-400">to</span>
                        <input
                            v-model="dateRange.end"
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

            <!-- Cash Flow Statement -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200 text-center">
                    <h2 class="text-xl font-bold text-gray-900">Statement of Cash Flows</h2>
                    <p class="text-sm text-gray-500">For the period {{ formatDate(startDate) }} to {{ formatDate(endDate) }}</p>
                </div>

                <div class="p-6 space-y-8">
                    <!-- Operating Activities -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">
                            Cash Flows from Operating Activities
                        </h3>
                        <div class="space-y-2 pl-4">
                            <div
                                v-for="(item, index) in data.operating.items"
                                :key="index"
                                class="flex justify-between text-sm"
                            >
                                <span class="text-gray-600">{{ item.name }}</span>
                                <span :class="item.amount >= 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ formatCurrency(item.amount) }}
                                </span>
                            </div>
                        </div>
                        <div class="flex justify-between font-semibold text-gray-900 pt-2 mt-2 border-t">
                            <span>Net Cash from Operating Activities</span>
                            <span :class="data.operating.total >= 0 ? 'text-green-600' : 'text-red-600'">
                                {{ formatCurrency(data.operating.total) }}
                            </span>
                        </div>
                    </div>

                    <!-- Investing Activities -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">
                            Cash Flows from Investing Activities
                        </h3>
                        <div class="space-y-2 pl-4">
                            <div
                                v-for="(item, index) in data.investing.items"
                                :key="index"
                                class="flex justify-between text-sm"
                            >
                                <span class="text-gray-600">{{ item.name }}</span>
                                <span :class="item.amount >= 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ formatCurrency(item.amount) }}
                                </span>
                            </div>
                            <div v-if="!data.investing.items.length" class="text-sm text-gray-400 italic">
                                No investing activities
                            </div>
                        </div>
                        <div class="flex justify-between font-semibold text-gray-900 pt-2 mt-2 border-t">
                            <span>Net Cash from Investing Activities</span>
                            <span :class="data.investing.total >= 0 ? 'text-green-600' : 'text-red-600'">
                                {{ formatCurrency(data.investing.total) }}
                            </span>
                        </div>
                    </div>

                    <!-- Financing Activities -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">
                            Cash Flows from Financing Activities
                        </h3>
                        <div class="space-y-2 pl-4">
                            <div
                                v-for="(item, index) in data.financing.items"
                                :key="index"
                                class="flex justify-between text-sm"
                            >
                                <span class="text-gray-600">{{ item.name }}</span>
                                <span :class="item.amount >= 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ formatCurrency(item.amount) }}
                                </span>
                            </div>
                            <div v-if="!data.financing.items.length" class="text-sm text-gray-400 italic">
                                No financing activities
                            </div>
                        </div>
                        <div class="flex justify-between font-semibold text-gray-900 pt-2 mt-2 border-t">
                            <span>Net Cash from Financing Activities</span>
                            <span :class="data.financing.total >= 0 ? 'text-green-600' : 'text-red-600'">
                                {{ formatCurrency(data.financing.total) }}
                            </span>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="pt-4 border-t-2 border-gray-300 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Net Increase (Decrease) in Cash</span>
                            <span :class="data.netChange >= 0 ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'">
                                {{ formatCurrency(data.netChange) }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Cash at Beginning of Period</span>
                            <span class="text-gray-900">{{ formatCurrency(data.openingBalance) }}</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold text-gray-900 pt-2 border-t">
                            <span>Cash at End of Period</span>
                            <span>{{ formatCurrency(data.closingBalance) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>
