<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CustomAppSidebarLayout from '@/layouts/admin/CustomAppSidebarLayout.vue';
import { TrendingUp, DollarSign, CheckCircle, AlertCircle } from 'lucide-vue-next';

interface Repayment {
    id: number;
    repayment_number: string;
    total_revenue: number;
    total_costs: number;
    net_profit: number;
    member_share: number;
    mygrownet_share: number;
    status: string;
    verified: boolean;
    paid_at: string | null;
    created_at: string;
    project: {
        project_number: string;
        user: {
            name: string;
        };
        application: {
            business_name: string;
        };
    };
}

defineProps<{
    repayments: {
        data: Repayment[];
    };
}>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(amount);
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        pending_report: 'bg-yellow-100 text-yellow-800',
        under_review: 'bg-blue-100 text-blue-800',
        paid: 'bg-green-100 text-green-800',
        disputed: 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getProfitMargin = (revenue: number, profit: number) => {
    if (revenue <= 0) return 0;
    return ((profit / revenue) * 100).toFixed(1);
};
</script>

<template>
    <Head title="BGF Repayments" />

    <CustomAppSidebarLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Repayments</h1>
                <p class="mt-2 text-gray-600">Track project profit sharing and repayments</p>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center gap-3">
                        <TrendingUp class="h-8 w-8 text-blue-600" />
                        <div>
                            <p class="text-sm text-gray-600">Total Revenue</p>
                            <p class="text-lg font-bold text-blue-600">
                                {{ formatCurrency(repayments.data.reduce((sum, r) => sum + r.total_revenue, 0)) }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center gap-3">
                        <DollarSign class="h-8 w-8 text-green-600" />
                        <div>
                            <p class="text-sm text-gray-600">Net Profit</p>
                            <p class="text-lg font-bold text-green-600">
                                {{ formatCurrency(repayments.data.reduce((sum, r) => sum + r.net_profit, 0)) }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center gap-3">
                        <CheckCircle class="h-8 w-8 text-emerald-600" />
                        <div>
                            <p class="text-sm text-gray-600">Member Share</p>
                            <p class="text-lg font-bold text-emerald-600">
                                {{ formatCurrency(repayments.data.reduce((sum, r) => sum + r.member_share, 0)) }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center gap-3">
                        <DollarSign class="h-8 w-8 text-blue-600" />
                        <div>
                            <p class="text-sm text-gray-600">MyGrowNet Share</p>
                            <p class="text-lg font-bold text-blue-600">
                                {{ formatCurrency(repayments.data.reduce((sum, r) => sum + r.mygrownet_share, 0)) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Repayments Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Revenue</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Profit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Margin</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Member Share</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Verified</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="repayments.data.length === 0">
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                No repayments found
                            </td>
                        </tr>
                        <tr
                            v-for="repayment in repayments.data"
                            :key="repayment.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ repayment.repayment_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ repayment.project.project_number }}</div>
                                <div class="text-sm text-gray-500">{{ repayment.project.application.business_name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ formatCurrency(repayment.total_revenue) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                                {{ formatCurrency(repayment.net_profit) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ getProfitMargin(repayment.total_revenue, repayment.net_profit) }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-blue-600">
                                {{ formatCurrency(repayment.member_share) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusColor(repayment.status)]">
                                    {{ repayment.status.replace('_', ' ') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <CheckCircle v-if="repayment.verified" class="h-5 w-5 text-green-600" />
                                <AlertCircle v-else class="h-5 w-5 text-yellow-600" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </CustomAppSidebarLayout>
</template>
