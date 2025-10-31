<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import CustomAppSidebarLayout from '@/layouts/admin/CustomAppSidebarLayout.vue';
import { DollarSign, CheckCircle, Clock, XCircle } from 'lucide-vue-next';

interface Disbursement {
    id: number;
    disbursement_number: string;
    amount: number;
    type: string;
    purpose: string;
    status: string;
    payment_method: string;
    recipient_name: string;
    disbursed_at: string | null;
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
    disbursements: {
        data: Disbursement[];
        current_page: number;
        last_page: number;
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
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-blue-100 text-blue-800',
        completed: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'completed': return CheckCircle;
        case 'pending': return Clock;
        case 'rejected': return XCircle;
        default: return Clock;
    }
};
</script>

<template>
    <Head title="BGF Disbursements" />

    <CustomAppSidebarLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Disbursements</h1>
                    <p class="mt-2 text-gray-600">Track and manage fund disbursements</p>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center gap-3">
                        <DollarSign class="h-8 w-8 text-blue-600" />
                        <div>
                            <p class="text-sm text-gray-600">Total Disbursements</p>
                            <p class="text-2xl font-bold text-gray-900">{{ disbursements.data.length }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center gap-3">
                        <Clock class="h-8 w-8 text-yellow-600" />
                        <div>
                            <p class="text-sm text-gray-600">Pending</p>
                            <p class="text-2xl font-bold text-yellow-600">
                                {{ disbursements.data.filter(d => d.status === 'pending').length }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center gap-3">
                        <CheckCircle class="h-8 w-8 text-green-600" />
                        <div>
                            <p class="text-sm text-gray-600">Completed</p>
                            <p class="text-2xl font-bold text-green-600">
                                {{ disbursements.data.filter(d => d.status === 'completed').length }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center gap-3">
                        <DollarSign class="h-8 w-8 text-blue-600" />
                        <div>
                            <p class="text-sm text-gray-600">Total Amount</p>
                            <p class="text-lg font-bold text-blue-600">
                                {{ formatCurrency(disbursements.data.reduce((sum, d) => sum + d.amount, 0)) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Disbursements Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recipient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="disbursements.data.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                No disbursements found
                            </td>
                        </tr>
                        <tr
                            v-for="disbursement in disbursements.data"
                            :key="disbursement.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ disbursement.disbursement_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ disbursement.project.project_number }}</div>
                                <div class="text-sm text-gray-500">{{ disbursement.project.application.business_name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ disbursement.recipient_name }}</div>
                                <div class="text-sm text-gray-500">{{ disbursement.payment_method }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                {{ formatCurrency(disbursement.amount) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 capitalize">
                                {{ disbursement.type.replace('_', ' ') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="['inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full', getStatusColor(disbursement.status)]">
                                    <component :is="getStatusIcon(disbursement.status)" class="h-3 w-3" />
                                    {{ disbursement.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ disbursement.disbursed_at ? new Date(disbursement.disbursed_at).toLocaleDateString() : 'Pending' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </CustomAppSidebarLayout>
</template>
