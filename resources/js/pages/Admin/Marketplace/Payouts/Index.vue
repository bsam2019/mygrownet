<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import MarketplaceAdminLayout from '@/layouts/MarketplaceAdminLayout.vue';
import { 
    BanknotesIcon,
    FunnelIcon,
    CheckCircleIcon,
    XCircleIcon,
    ClockIcon,
    ArrowPathIcon,
} from '@heroicons/vue/24/outline';

interface Payout {
    id: number;
    reference: string;
    amount: number;
    net_amount: number;
    payout_method: string;
    account_number: string;
    status: string;
    status_label: string;
    status_color: string;
    formatted_amount: string;
    formatted_net_amount: string;
    created_at: string;
    seller: {
        id: number;
        business_name: string;
        user: {
            name: string;
        };
    };
}

interface Props {
    payouts: {
        data: Payout[];
        links: any;
        meta: any;
    };
    stats: {
        pending_count: number;
        pending_amount: number;
        approved_count: number;
        approved_amount: number;
        processing_count: number;
        processing_amount: number;
        completed_today: number;
        completed_today_amount: number;
        completed_this_month: number;
        completed_this_month_amount: number;
    };
    filters: {
        status: string;
        payout_method: string;
    };
}

const props = defineProps<Props>();

const showFilters = ref(false);
const filterForm = ref({
    status: props.filters.status,
    payout_method: props.filters.payout_method,
});

const applyFilters = () => {
    router.get(route('admin.marketplace.payouts.index'), filterForm.value, {
        preserveState: true,
    });
};

const clearFilters = () => {
    filterForm.value = { status: '', payout_method: '' };
    applyFilters();
};

const formatAmount = (amount: number) => {
    return 'K' + (amount / 100).toFixed(2);
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'completed': return CheckCircleIcon;
        case 'rejected':
        case 'failed': return XCircleIcon;
        case 'processing': return ArrowPathIcon;
        default: return ClockIcon;
    }
};

const getMethodLabel = (method: string) => {
    const labels: Record<string, string> = {
        momo: 'MTN MoMo',
        airtel: 'Airtel',
        bank: 'Bank',
    };
    return labels[method] || method;
};
</script>

<template>
    <MarketplaceAdminLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Payout Management</h1>
                    <p class="text-gray-500">Review and process seller payouts</p>
                </div>
                <button 
                    @click="showFilters = !showFilters"
                    class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                    <FunnelIcon class="h-5 w-5" aria-hidden="true" />
                    Filters
                </button>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-sm text-yellow-700 mb-1">Pending Review</p>
                    <p class="text-2xl font-bold text-yellow-900">{{ stats.pending_count }}</p>
                    <p class="text-sm text-yellow-600 mt-1">{{ formatAmount(stats.pending_amount) }}</p>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-700 mb-1">Approved</p>
                    <p class="text-2xl font-bold text-blue-900">{{ stats.approved_count }}</p>
                    <p class="text-sm text-blue-600 mt-1">{{ formatAmount(stats.approved_amount) }}</p>
                </div>
                <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                    <p class="text-sm text-indigo-700 mb-1">Processing</p>
                    <p class="text-2xl font-bold text-indigo-900">{{ stats.processing_count }}</p>
                    <p class="text-sm text-indigo-600 mt-1">{{ formatAmount(stats.processing_amount) }}</p>
                </div>
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <p class="text-sm text-green-700 mb-1">Completed Today</p>
                    <p class="text-2xl font-bold text-green-900">{{ stats.completed_today }}</p>
                    <p class="text-sm text-green-600 mt-1">{{ formatAmount(stats.completed_today_amount) }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div v-if="showFilters" class="bg-white rounded-lg border p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select v-model="filterForm.status" class="w-full border-gray-300 rounded-lg">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="processing">Processing</option>
                            <option value="completed">Completed</option>
                            <option value="rejected">Rejected</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Method</label>
                        <select v-model="filterForm.payout_method" class="w-full border-gray-300 rounded-lg">
                            <option value="">All Methods</option>
                            <option value="momo">MTN MoMo</option>
                            <option value="airtel">Airtel Money</option>
                            <option value="bank">Bank Transfer</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button @click="applyFilters" class="flex-1 px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600">
                            Apply
                        </button>
                        <button @click="clearFilters" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                            Clear
                        </button>
                    </div>
                </div>
            </div>

            <!-- Payouts Table -->
            <div class="bg-white rounded-lg border overflow-hidden">
                <div v-if="payouts.data.length === 0" class="p-12 text-center">
                    <BanknotesIcon class="h-12 w-12 text-gray-300 mx-auto mb-4" aria-hidden="true" />
                    <p class="text-gray-500">No payouts found</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seller</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="payout in payouts.data" :key="payout.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ payout.seller.business_name }}</p>
                                        <p class="text-sm text-gray-500">{{ payout.seller.user.name }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-mono text-sm text-gray-900">{{ payout.reference }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-gray-900">{{ payout.formatted_net_amount }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-600">{{ getMethodLabel(payout.payout_method) }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="[
                                        'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium',
                                        payout.status_color === 'green' ? 'bg-green-100 text-green-700' :
                                        payout.status_color === 'red' ? 'bg-red-100 text-red-700' :
                                        payout.status_color === 'blue' ? 'bg-blue-100 text-blue-700' :
                                        payout.status_color === 'yellow' ? 'bg-yellow-100 text-yellow-700' :
                                        'bg-gray-100 text-gray-700'
                                    ]">
                                        <component :is="getStatusIcon(payout.status)" class="h-3.5 w-3.5" aria-hidden="true" />
                                        {{ payout.status_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ new Date(payout.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Link 
                                        :href="route('admin.marketplace.payouts.show', payout.id)"
                                        class="text-orange-600 hover:text-orange-700 font-medium text-sm"
                                    >
                                        View
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="payouts.data.length > 0" class="p-4 border-t bg-gray-50">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-600">
                            Showing {{ payouts.meta.from }} to {{ payouts.meta.to }} of {{ payouts.meta.total }} payouts
                        </p>
                        <div class="flex gap-2">
                            <Link 
                                v-for="link in payouts.links" 
                                :key="link.label"
                                :href="link.url"
                                :class="[
                                    'px-3 py-1 text-sm rounded',
                                    link.active ? 'bg-orange-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100',
                                    !link.url ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MarketplaceAdminLayout>
</template>
