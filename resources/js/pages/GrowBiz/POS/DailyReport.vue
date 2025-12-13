<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import GrowBizLayout from '@/layouts/GrowBizLayout.vue';
import {
    CalendarDaysIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    BanknotesIcon,
    DevicePhoneMobileIcon,
    CreditCardIcon,
    ReceiptPercentIcon,
    ArrowTrendingUpIcon,
    ClockIcon,
} from '@heroicons/vue/24/outline';

interface Sale {
    id: number;
    sale_number: string;
    customer_name: string | null;
    total_amount: number;
    payment_method: string;
    status: string;
    created_at: string;
}

interface Stats {
    total_sales: number;
    total_revenue: number;
    total_transactions: number;
    cash_sales: number;
    mobile_money_sales: number;
    card_sales: number;
    credit_sales: number;
    voided_count: number;
    voided_amount: number;
    average_sale: number;
}

interface Props {
    date: string;
    stats: Stats;
    sales: Sale[];
}

const props = defineProps<Props>();

const selectedDate = ref(props.date);

const changeDate = (direction: number) => {
    const date = new Date(selectedDate.value);
    date.setDate(date.getDate() + direction);
    selectedDate.value = date.toISOString().split('T')[0];
    router.get(route('growbiz.pos.daily-report'), { date: selectedDate.value }, { preserveState: true });
};

const goToDate = () => {
    router.get(route('growbiz.pos.daily-report'), { date: selectedDate.value }, { preserveState: true });
};

const isToday = computed(() => {
    return selectedDate.value === new Date().toISOString().split('T')[0];
});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', { style: 'currency', currency: 'ZMW' }).format(amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-ZM', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatTime = (date: string) => {
    return new Date(date).toLocaleTimeString('en-ZM', {
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getPaymentIcon = (method: string) => {
    switch (method) {
        case 'cash': return BanknotesIcon;
        case 'mobile_money': return DevicePhoneMobileIcon;
        case 'card': return CreditCardIcon;
        default: return BanknotesIcon;
    }
};

const paymentBreakdown = computed(() => [
    { label: 'Cash', amount: props.stats.cash_sales, icon: BanknotesIcon, color: 'text-green-600 bg-green-100' },
    { label: 'Mobile Money', amount: props.stats.mobile_money_sales, icon: DevicePhoneMobileIcon, color: 'text-blue-600 bg-blue-100' },
    { label: 'Card', amount: props.stats.card_sales, icon: CreditCardIcon, color: 'text-purple-600 bg-purple-100' },
    { label: 'Credit', amount: props.stats.credit_sales, icon: ClockIcon, color: 'text-amber-600 bg-amber-100' },
]);
</script>

<template>
    <GrowBizLayout>
        <Head title="Daily Report - POS" />

        <div class="max-w-4xl mx-auto px-4 sm:px-6 py-4 sm:py-6">
            <!-- Header with Date Navigation -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Daily Report</h1>
                    <p class="text-sm text-gray-500 mt-1">{{ formatDate(selectedDate) }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        @click="changeDate(-1)"
                        class="p-2 border border-gray-200 rounded-lg hover:bg-gray-50"
                        aria-label="Previous day"
                    >
                        <ChevronLeftIcon class="w-5 h-5" />
                    </button>
                    <input
                        v-model="selectedDate"
                        type="date"
                        class="px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500"
                        @change="goToDate"
                    />
                    <button
                        @click="changeDate(1)"
                        :disabled="isToday"
                        class="p-2 border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-50"
                        aria-label="Next day"
                    >
                        <ChevronRightIcon class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-6">
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
                            <BanknotesIcon class="w-4 h-4 text-green-600" />
                        </div>
                    </div>
                    <p class="text-xs text-gray-500">Total Revenue</p>
                    <p class="text-lg sm:text-xl font-bold text-green-600">{{ formatCurrency(stats.total_revenue) }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                            <ReceiptPercentIcon class="w-4 h-4 text-blue-600" />
                        </div>
                    </div>
                    <p class="text-xs text-gray-500">Transactions</p>
                    <p class="text-lg sm:text-xl font-bold text-gray-900">{{ stats.total_transactions }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                            <ArrowTrendingUpIcon class="w-4 h-4 text-purple-600" />
                        </div>
                    </div>
                    <p class="text-xs text-gray-500">Average Sale</p>
                    <p class="text-lg sm:text-xl font-bold text-gray-900">{{ formatCurrency(stats.average_sale || 0) }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center">
                            <ClockIcon class="w-4 h-4 text-red-600" />
                        </div>
                    </div>
                    <p class="text-xs text-gray-500">Voided</p>
                    <p class="text-lg sm:text-xl font-bold text-red-600">{{ stats.voided_count }}</p>
                </div>
            </div>

            <!-- Payment Breakdown -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                <h2 class="font-semibold text-gray-900 mb-4">Payment Breakdown</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div
                        v-for="payment in paymentBreakdown"
                        :key="payment.label"
                        class="text-center"
                    >
                        <div :class="['w-12 h-12 rounded-full mx-auto mb-2 flex items-center justify-center', payment.color]">
                            <component :is="payment.icon" class="w-6 h-6" />
                        </div>
                        <p class="text-sm text-gray-500">{{ payment.label }}</p>
                        <p class="font-semibold">{{ formatCurrency(payment.amount) }}</p>
                    </div>
                </div>
            </div>

            <!-- Sales List -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Sales Transactions</h2>
                </div>

                <div v-if="sales.length === 0" class="p-8 text-center">
                    <CalendarDaysIcon class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                    <p class="text-gray-500">No sales on this day</p>
                </div>

                <div v-else class="divide-y divide-gray-100">
                    <div
                        v-for="sale in sales"
                        :key="sale.id"
                        class="p-4 flex items-center justify-between"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                                <component :is="getPaymentIcon(sale.payment_method)" class="w-5 h-5 text-gray-600" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ sale.sale_number }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ sale.customer_name || 'Walk-in' }} • {{ formatTime(sale.created_at) }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p :class="[
                                'font-semibold',
                                sale.status === 'voided' ? 'text-red-600 line-through' : 'text-gray-900'
                            ]">
                                {{ formatCurrency(sale.total_amount) }}
                            </p>
                            <span v-if="sale.status === 'voided'" class="text-xs text-red-600">Voided</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GrowBizLayout>
</template>
