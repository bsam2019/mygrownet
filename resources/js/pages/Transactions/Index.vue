<script setup lang="ts">
import AppLayout from '@/layouts/MemberLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { formatCurrency } from '@/utils/format';

const props = defineProps<{
    transactions: {
        data: Array<{
            id: number;
            type: string;
            amount: number;
            status: string;
            created_at: string;
            description: string;
        }>;
        links: Array<{ url?: string; label: string; active: boolean }>;
    };
    wallet_balance?: number;
}>();

const getStatusColor = (status: string) => {
    return {
        'completed': 'bg-green-100 text-green-800',
        'pending': 'bg-yellow-100 text-yellow-800',
        'failed': 'bg-red-100 text-red-800',
        'verified': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800',
        'paid': 'bg-green-100 text-green-800',
        'approved': 'bg-green-100 text-green-800',
        'registered': 'bg-blue-100 text-blue-800',
        'attended': 'bg-purple-100 text-purple-800',
        'cancelled': 'bg-red-100 text-red-800'
    }[status] || 'bg-gray-100 text-gray-800';
};

const getTypeColor = (type: string) => {
    return {
        'commission': 'text-green-600',
        'profit_share': 'text-purple-600',
        'withdrawal': 'text-red-600',
        'bonus': 'text-blue-600',
        'wallet_topup': 'text-blue-600',
        'workshop': 'text-orange-600'
    }[type] || 'text-gray-600';
};

const getTypeLabel = (type: string) => {
    return {
        'commission': 'Commission',
        'profit_share': 'Profit Share',
        'withdrawal': 'Withdrawal',
        'bonus': 'Bonus',
        'wallet_topup': 'Wallet Top-up',
        'workshop': 'Workshop'
    }[type] || type;
};

const getStatusLabel = (status: string) => {
    return {
        'verified': 'Completed',
        'paid': 'Completed',
        'approved': 'Approved',
        'pending': 'Pending',
        'rejected': 'Rejected',
        'failed': 'Failed',
        'completed': 'Completed',
        'registered': 'Registered',
        'attended': 'Attended',
        'cancelled': 'Cancelled'
    }[status] || status;
};
</script>

<template>
    <Head title="Transaction History" />

    <AppLayout>
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">Transaction History</h1>
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-6 py-3 rounded-lg shadow-md">
                    <p class="text-xs text-blue-100">Available Balance</p>
                    <p class="text-2xl font-bold">{{ formatCurrency(props.wallet_balance ?? 0) }}</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="transaction in transactions.data" :key="transaction.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ new Date(transaction.created_at).toLocaleDateString() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="['text-sm font-medium', getTypeColor(transaction.type)]">
                                    {{ getTypeLabel(transaction.type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="['text-sm font-medium',
                                    transaction.amount >= 0 ? 'text-green-600' : 'text-red-600']">
                                    {{ transaction.amount >= 0 ? '+' : '' }}{{ formatCurrency(transaction.amount) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ transaction.description }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="['px-2 py-1 text-xs rounded-full', getStatusColor(transaction.status)]">
                                    {{ getStatusLabel(transaction.status) }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="transactions.data.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                No transactions yet
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4 flex justify-between">
                <template v-for="(link, index) in transactions.links" :key="index">
                    <Link v-if="link.url"
                        :href="link.url"
                        class="px-4 py-2 border rounded"
                        :class="{ 'bg-blue-50': link.active }">
                        <span v-html="link.label"></span>
                    </Link>
                    <span v-else
                        class="px-4 py-2 text-gray-400"
                        v-html="link.label"></span>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
