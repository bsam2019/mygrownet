<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/Layouts/EmployeePortalLayout.vue';
import { DocumentTextIcon, ArrowDownTrayIcon, BanknotesIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';

interface Payslip {
    id: number;
    payslip_number: string;
    pay_period_start: string;
    pay_period_end: string;
    payment_date: string;
    gross_pay: number;
    net_pay: number;
    total_deductions: number;
    status: string;
}

interface Props {
    payslips: Payslip[];
    ytdTotals: { gross_pay: number; net_pay: number; tax: number; pension: number };
    availableYears: number[];
    selectedYear: number;
}

const props = defineProps<Props>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', { style: 'currency', currency: 'ZMW' }).format(amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-ZM', { year: 'numeric', month: 'short', day: 'numeric' });
};

const changeYear = (year: number) => {
    router.get(route('employee.portal.payslips.index'), { year }, { preserveState: true });
};
</script>

<template>
    <Head title="Payslips" />
    <EmployeePortalLayout>
        <template #header>Payslips</template>
        <div class="space-y-6">
            <!-- YTD Summary -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900">Year-to-Date Summary</h2>
                    <select 
                        :value="selectedYear" 
                        class="px-4 py-2 border border-gray-300 rounded-lg"
                        @change="changeYear(Number(($event.target as HTMLSelectElement).value))"
                    >
                        <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-emerald-50 rounded-lg p-4">
                        <p class="text-sm text-emerald-600">Gross Pay YTD</p>
                        <p class="text-xl font-bold text-emerald-700">{{ formatCurrency(ytdTotals.gross_pay) }}</p>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-4">
                        <p class="text-sm text-blue-600">Net Pay YTD</p>
                        <p class="text-xl font-bold text-blue-700">{{ formatCurrency(ytdTotals.net_pay) }}</p>
                    </div>
                    <div class="bg-amber-50 rounded-lg p-4">
                        <p class="text-sm text-amber-600">Tax Paid YTD</p>
                        <p class="text-xl font-bold text-amber-700">{{ formatCurrency(ytdTotals.tax) }}</p>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-4">
                        <p class="text-sm text-purple-600">Pension YTD</p>
                        <p class="text-xl font-bold text-purple-700">{{ formatCurrency(ytdTotals.pension) }}</p>
                    </div>
                </div>
            </div>

            <!-- Payslips List -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="p-5 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Payslips</h2>
                </div>
                <div v-if="payslips.length === 0" class="p-12 text-center">
                    <DocumentTextIcon class="h-12 w-12 mx-auto text-gray-300 mb-4" aria-hidden="true" />
                    <p class="text-gray-500">No payslips available</p>
                </div>
                <div v-else class="divide-y divide-gray-100">
                    <div v-for="payslip in payslips" :key="payslip.id" class="p-4 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-blue-50 rounded-lg">
                                    <BanknotesIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">
                                        {{ formatDate(payslip.pay_period_start) }} - {{ formatDate(payslip.pay_period_end) }}
                                    </p>
                                    <p class="text-sm text-gray-500">Paid {{ formatDate(payslip.payment_date) }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="text-right hidden sm:block">
                                    <p class="text-sm text-gray-500">Net Pay</p>
                                    <p class="font-semibold text-gray-900">{{ formatCurrency(payslip.net_pay) }}</p>
                                </div>
                                <Link 
                                    :href="route('employee.portal.payslips.show', payslip.id)" 
                                    class="p-2 hover:bg-blue-50 rounded-lg"
                                    :aria-label="`View payslip for ${formatDate(payslip.pay_period_start)}`"
                                >
                                    <ChevronRightIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
