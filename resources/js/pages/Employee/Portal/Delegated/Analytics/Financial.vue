<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/layouts/EmployeePortalLayout.vue';
import { ref } from 'vue';
import { CreditCardIcon, BanknotesIcon, ArrowTrendingUpIcon, ArrowTrendingDownIcon } from '@heroicons/vue/24/outline';

interface Props {
    employee: any;
    stats: {
        total_payments: number;
        total_withdrawals: number;
        payment_count: number;
        withdrawal_count: number;
    };
    dailyData: { date: string; payments: number; withdrawals: number }[];
    period: string;
}

const props = defineProps<Props>();
const selectedPeriod = ref(props.period);

const changePeriod = () => {
    router.get(route('employee.portal.delegated.analytics.financial'), { period: selectedPeriod.value }, { preserveState: true });
};

const netFlow = props.stats.total_payments - props.stats.total_withdrawals;
</script>

<template>
    <Head title="Delegated - Financial Reports" />
    <EmployeePortalLayout>
        <template #header>Financial Reports</template>
        
        <div class="space-y-6">
            <!-- Period Selector -->
            <div class="flex justify-end">
                <select v-model="selectedPeriod" @change="changePeriod" class="border border-gray-300 rounded-lg px-3 py-2">
                    <option value="week">Last 7 Days</option>
                    <option value="month">Last 30 Days</option>
                    <option value="quarter">Last 90 Days</option>
                </select>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-100 rounded-lg"><CreditCardIcon class="h-5 w-5 text-green-600" /></div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">K{{ Number(stats.total_payments).toLocaleString() }}</p>
                            <p class="text-sm text-gray-500">Total Payments</p>
                            <p class="text-xs text-gray-400">{{ stats.payment_count }} transactions</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-red-100 rounded-lg"><BanknotesIcon class="h-5 w-5 text-red-600" /></div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">K{{ Number(stats.total_withdrawals).toLocaleString() }}</p>
                            <p class="text-sm text-gray-500">Total Withdrawals</p>
                            <p class="text-xs text-gray-400">{{ stats.withdrawal_count }} transactions</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div :class="['p-2 rounded-lg', netFlow >= 0 ? 'bg-green-100' : 'bg-red-100']">
                            <component :is="netFlow >= 0 ? ArrowTrendingUpIcon : ArrowTrendingDownIcon" :class="['h-5 w-5', netFlow >= 0 ? 'text-green-600' : 'text-red-600']" />
                        </div>
                        <div>
                            <p :class="['text-2xl font-bold', netFlow >= 0 ? 'text-green-600' : 'text-red-600']">
                                {{ netFlow >= 0 ? '+' : '' }}K{{ Number(netFlow).toLocaleString() }}
                            </p>
                            <p class="text-sm text-gray-500">Net Flow</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 rounded-lg"><CreditCardIcon class="h-5 w-5 text-blue-600" /></div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.payment_count + stats.withdrawal_count }}</p>
                            <p class="text-sm text-gray-500">Total Transactions</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daily Breakdown Table -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="p-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Daily Breakdown</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Payments</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Withdrawals</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Net</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="day in dailyData" :key="day.date" class="hover:bg-gray-50">
                                <td class="px-6 py-3 text-sm text-gray-900">{{ day.date }}</td>
                                <td class="px-6 py-3 text-sm text-right text-green-600 font-medium">
                                    K{{ Number(day.payments).toLocaleString() }}
                                </td>
                                <td class="px-6 py-3 text-sm text-right text-red-600 font-medium">
                                    K{{ Number(day.withdrawals).toLocaleString() }}
                                </td>
                                <td :class="['px-6 py-3 text-sm text-right font-medium', (day.payments - day.withdrawals) >= 0 ? 'text-green-600' : 'text-red-600']">
                                    {{ (day.payments - day.withdrawals) >= 0 ? '+' : '' }}K{{ Number(day.payments - day.withdrawals).toLocaleString() }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
