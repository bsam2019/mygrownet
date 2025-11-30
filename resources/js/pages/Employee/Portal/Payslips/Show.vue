<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/Layouts/EmployeePortalLayout.vue';
import { ArrowLeftIcon, ArrowDownTrayIcon, PrinterIcon } from '@heroicons/vue/24/outline';

interface Payslip {
    id: number;
    payslip_number: string;
    pay_period_start: string;
    pay_period_end: string;
    payment_date: string;
    basic_salary: number;
    overtime_pay: number;
    bonus: number;
    commission: number;
    allowances: number;
    gross_pay: number;
    tax: number;
    pension: number;
    health_insurance: number;
    loan_deduction: number;
    other_deductions: number;
    total_deductions: number;
    net_pay: number;
    earnings_breakdown: Record<string, number> | null;
    deductions_breakdown: Record<string, number> | null;
    employee: {
        full_name: string;
        employee_id: string;
        department: { name: string } | null;
        position: { title: string } | null;
    };
}

const props = defineProps<{ payslip: Payslip }>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
    }).format(amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-ZM', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const printPayslip = () => window.print();
</script>

<template>
    <Head :title="`Payslip - ${payslip.payslip_number}`" />
    <EmployeePortalLayout>
        <template #header>Payslip Details</template>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <Link :href="route('employee.portal.payslips.index')"
                    class="flex items-center gap-2 text-gray-600 hover:text-gray-900">
                    <ArrowLeftIcon class="h-5 w-5" />
                    Back to Payslips
                </Link>
                <div class="flex gap-2">
                    <button @click="printPayslip"
                        class="flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        <PrinterIcon class="h-5 w-5" />
                        Print
                    </button>
                    <a :href="route('employee.portal.payslips.download', payslip.id)"
                        class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <ArrowDownTrayIcon class="h-5 w-5" />
                        Download PDF
                    </a>
                </div>
            </div>

            <!-- Payslip Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 print:shadow-none print:border-0">
                <!-- Company Header -->
                <div class="text-center border-b pb-6 mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">MyGrowNet</h1>
                    <p class="text-gray-500">Payslip</p>
                    <p class="text-sm text-gray-400 mt-2">#{{ payslip.payslip_number }}</p>
                </div>

                <!-- Employee & Period Info -->
                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Employee</h3>
                        <p class="font-semibold text-gray-900">{{ payslip.employee.full_name }}</p>
                        <p class="text-sm text-gray-600">{{ payslip.employee.employee_id }}</p>
                        <p class="text-sm text-gray-600">{{ payslip.employee.position?.title }}</p>
                        <p class="text-sm text-gray-600">{{ payslip.employee.department?.name }}</p>
                    </div>
                    <div class="text-right">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Pay Period</h3>
                        <p class="font-semibold text-gray-900">
                            {{ formatDate(payslip.pay_period_start) }} - {{ formatDate(payslip.pay_period_end) }}
                        </p>
                        <p class="text-sm text-gray-600 mt-2">Payment Date</p>
                        <p class="font-medium text-gray-900">{{ formatDate(payslip.payment_date) }}</p>
                    </div>
                </div>

                <!-- Earnings & Deductions -->
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Earnings -->
                    <div class="bg-emerald-50 rounded-lg p-4">
                        <h3 class="font-semibold text-emerald-800 mb-4">Earnings</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Basic Salary</span>
                                <span class="font-medium">{{ formatCurrency(payslip.basic_salary) }}</span>
                            </div>
                            <div v-if="payslip.overtime_pay > 0" class="flex justify-between">
                                <span class="text-gray-600">Overtime</span>
                                <span class="font-medium">{{ formatCurrency(payslip.overtime_pay) }}</span>
                            </div>
                            <div v-if="payslip.bonus > 0" class="flex justify-between">
                                <span class="text-gray-600">Bonus</span>
                                <span class="font-medium">{{ formatCurrency(payslip.bonus) }}</span>
                            </div>
                            <div v-if="payslip.commission > 0" class="flex justify-between">
                                <span class="text-gray-600">Commission</span>
                                <span class="font-medium">{{ formatCurrency(payslip.commission) }}</span>
                            </div>
                            <div v-if="payslip.allowances > 0" class="flex justify-between">
                                <span class="text-gray-600">Allowances</span>
                                <span class="font-medium">{{ formatCurrency(payslip.allowances) }}</span>
                            </div>
                            <div class="border-t border-emerald-200 pt-2 mt-2 flex justify-between">
                                <span class="font-semibold text-emerald-800">Gross Pay</span>
                                <span class="font-bold text-emerald-800">{{ formatCurrency(payslip.gross_pay) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Deductions -->
                    <div class="bg-red-50 rounded-lg p-4">
                        <h3 class="font-semibold text-red-800 mb-4">Deductions</h3>
                        <div class="space-y-2">
                            <div v-if="payslip.tax > 0" class="flex justify-between">
                                <span class="text-gray-600">Tax (PAYE)</span>
                                <span class="font-medium">{{ formatCurrency(payslip.tax) }}</span>
                            </div>
                            <div v-if="payslip.pension > 0" class="flex justify-between">
                                <span class="text-gray-600">Pension (NAPSA)</span>
                                <span class="font-medium">{{ formatCurrency(payslip.pension) }}</span>
                            </div>
                            <div v-if="payslip.health_insurance > 0" class="flex justify-between">
                                <span class="text-gray-600">Health Insurance</span>
                                <span class="font-medium">{{ formatCurrency(payslip.health_insurance) }}</span>
                            </div>
                            <div v-if="payslip.loan_deduction > 0" class="flex justify-between">
                                <span class="text-gray-600">Loan Deduction</span>
                                <span class="font-medium">{{ formatCurrency(payslip.loan_deduction) }}</span>
                            </div>
                            <div v-if="payslip.other_deductions > 0" class="flex justify-between">
                                <span class="text-gray-600">Other Deductions</span>
                                <span class="font-medium">{{ formatCurrency(payslip.other_deductions) }}</span>
                            </div>
                            <div class="border-t border-red-200 pt-2 mt-2 flex justify-between">
                                <span class="font-semibold text-red-800">Total Deductions</span>
                                <span class="font-bold text-red-800">{{ formatCurrency(payslip.total_deductions) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Net Pay -->
                <div class="mt-6 bg-blue-600 rounded-lg p-6 text-center text-white">
                    <p class="text-blue-100 text-sm">Net Pay</p>
                    <p class="text-3xl font-bold">{{ formatCurrency(payslip.net_pay) }}</p>
                </div>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
