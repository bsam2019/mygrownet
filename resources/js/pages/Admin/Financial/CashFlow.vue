<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ArrowLeftIcon, DocumentArrowDownIcon } from '@heroicons/vue/24/outline';

interface CashFlowData {
    period: {
        start_date: string;
        end_date: string;
    };
    operating_activities: {
        interest_income_received: number;
        subscription_revenue: number;
        module_revenue: number;
        other_revenue: number;
        operating_expenses: number;
        commission_payments: number;
        net_operating_cash: number;
    };
    investing_activities: {
        loan_disbursements: number;
        principal_repayments: number;
        net_investing_cash: number;
    };
    financing_activities: {
        wallet_deposits: number;
        wallet_withdrawals: number;
        net_financing_cash: number;
    };
    net_change_in_cash: number;
    beginning_cash: number;
    ending_cash: number;
}

interface Props {
    cash_flow: CashFlowData;
}

const props = defineProps<Props>();

const startDate = ref(props.cash_flow.period.start_date);
const endDate = ref(props.cash_flow.period.end_date);

const updatePeriod = () => {
    router.get(route('admin.financial-reports.cash-flow'), {
        start_date: startDate.value,
        end_date: endDate.value,
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
    <Head title="Cash Flow Statement" />

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
                                Cash Flow Statement
                            </h2>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ formatDate(cash_flow.period.start_date) }} to {{ formatDate(cash_flow.period.end_date) }}
                            </p>
                        </div>
                        <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                            <div class="flex items-center space-x-2">
                                <input
                                    v-model="startDate"
                                    type="date"
                                    class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500"
                                    @change="updatePeriod"
                                />
                                <span class="text-gray-500">to</span>
                                <input
                                    v-model="endDate"
                                    type="date"
                                    class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500"
                                    @change="updatePeriod"
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

                <!-- Cash Flow Statement -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <!-- Operating Activities -->
                    <div class="border-b border-gray-200">
                        <div class="bg-blue-50 px-6 py-4">
                            <h3 class="text-lg font-bold text-gray-900">CASH FLOWS FROM OPERATING ACTIVITIES</h3>
                        </div>
                        
                        <div class="px-6 py-4">
                            <div class="space-y-2">
                                <div class="text-sm font-medium text-gray-900 mb-3">Cash Inflows:</div>
                                <div class="ml-4 space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-700">Interest Income Received</span>
                                        <span class="font-medium text-green-600">
                                            {{ formatCurrency(cash_flow.operating_activities.interest_income_received) }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-700">Subscription Revenue</span>
                                        <span class="font-medium text-green-600">
                                            {{ formatCurrency(cash_flow.operating_activities.subscription_revenue) }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-700">Module Revenue</span>
                                        <span class="font-medium text-green-600">
                                            {{ formatCurrency(cash_flow.operating_activities.module_revenue) }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-700">Other Revenue</span>
                                        <span class="font-medium text-green-600">
                                            {{ formatCurrency(cash_flow.operating_activities.other_revenue) }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="text-sm font-medium text-gray-900 mt-4 mb-3">Cash Outflows:</div>
                                <div class="ml-4 space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-700">Operating Expenses</span>
                                        <span class="font-medium text-red-600">
                                            ({{ formatCurrency(cash_flow.operating_activities.operating_expenses) }})
                                        </span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-700">Commission Payments</span>
                                        <span class="font-medium text-red-600">
                                            ({{ formatCurrency(cash_flow.operating_activities.commission_payments) }})
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="flex justify-between text-sm font-bold border-t-2 border-gray-300 pt-3 mt-4">
                                    <span class="text-gray-900">Net Cash from Operating Activities</span>
                                    <span :class="cash_flow.operating_activities.net_operating_cash >= 0 ? 'text-green-600' : 'text-red-600'">
                                        {{ formatCurrency(cash_flow.operating_activities.net_operating_cash) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Investing Activities -->
                    <div class="border-b border-gray-200">
                        <div class="bg-purple-50 px-6 py-4">
                            <h3 class="text-lg font-bold text-gray-900">CASH FLOWS FROM INVESTING ACTIVITIES</h3>
                        </div>
                        
                        <div class="px-6 py-4">
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-700">Loan Disbursements</span>
                                    <span class="font-medium text-red-600">
                                        ({{ formatCurrency(cash_flow.investing_activities.loan_disbursements) }})
                                    </span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-700">Principal Repayments Received</span>
                                    <span class="font-medium text-green-600">
                                        {{ formatCurrency(cash_flow.investing_activities.principal_repayments) }}
                                    </span>
                                </div>
                                
                                <div class="flex justify-between text-sm font-bold border-t-2 border-gray-300 pt-3 mt-4">
                                    <span class="text-gray-900">Net Cash from Investing Activities</span>
                                    <span :class="cash_flow.investing_activities.net_investing_cash >= 0 ? 'text-green-600' : 'text-red-600'">
                                        {{ formatCurrency(cash_flow.investing_activities.net_investing_cash) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Financing Activities -->
                    <div class="border-b border-gray-200">
                        <div class="bg-orange-50 px-6 py-4">
                            <h3 class="text-lg font-bold text-gray-900">CASH FLOWS FROM FINANCING ACTIVITIES</h3>
                        </div>
                        
                        <div class="px-6 py-4">
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-700">Member Wallet Deposits</span>
                                    <span class="font-medium text-green-600">
                                        {{ formatCurrency(cash_flow.financing_activities.wallet_deposits) }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-700">Member Wallet Withdrawals</span>
                                    <span class="font-medium text-red-600">
                                        ({{ formatCurrency(cash_flow.financing_activities.wallet_withdrawals) }})
                                    </span>
                                </div>
                                
                                <div class="flex justify-between text-sm font-bold border-t-2 border-gray-300 pt-3 mt-4">
                                    <span class="text-gray-900">Net Cash from Financing Activities</span>
                                    <span :class="cash_flow.financing_activities.net_financing_cash >= 0 ? 'text-green-600' : 'text-red-600'">
                                        {{ formatCurrency(cash_flow.financing_activities.net_financing_cash) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div>
                        <div class="bg-gray-50 px-6 py-4">
                            <div class="space-y-3">
                                <div class="flex justify-between text-sm font-bold">
                                    <span class="text-gray-900">Net Change in Cash</span>
                                    <span :class="cash_flow.net_change_in_cash >= 0 ? 'text-green-600' : 'text-red-600'">
                                        {{ formatCurrency(cash_flow.net_change_in_cash) }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-700">Cash at Beginning of Period</span>
                                    <span class="text-gray-900">
                                        {{ formatCurrency(cash_flow.beginning_cash) }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-lg font-bold border-t-2 border-gray-400 pt-3">
                                    <span class="text-gray-900">Cash at End of Period</span>
                                    <span class="text-gray-900">
                                        {{ formatCurrency(cash_flow.ending_cash) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cash Flow Summary Cards -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mt-6">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <span class="text-2xl">💼</span>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Operating Cash Flow</dt>
                                        <dd class="text-lg font-semibold" :class="cash_flow.operating_activities.net_operating_cash >= 0 ? 'text-green-600' : 'text-red-600'">
                                            {{ formatCurrency(cash_flow.operating_activities.net_operating_cash) }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <span class="text-2xl">📈</span>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Investing Cash Flow</dt>
                                        <dd class="text-lg font-semibold" :class="cash_flow.investing_activities.net_investing_cash >= 0 ? 'text-green-600' : 'text-red-600'">
                                            {{ formatCurrency(cash_flow.investing_activities.net_investing_cash) }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <span class="text-2xl">💰</span>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Financing Cash Flow</dt>
                                        <dd class="text-lg font-semibold" :class="cash_flow.financing_activities.net_financing_cash >= 0 ? 'text-green-600' : 'text-red-600'">
                                            {{ formatCurrency(cash_flow.financing_activities.net_financing_cash) }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
