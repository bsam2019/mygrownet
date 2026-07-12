<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import Pagination from '@/components/Pagination.vue';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

interface BillingTransaction {
    id: number;
    user: { id: number; name: string; email: string } | null;
    service_type: string;
    gross_amount_charged: number;
    net_vendor_cost: number;
    pure_platform_profit: number;
    currency: string;
    vendor: string | null;
    delivery_status: string | null;
    reference: string | null;
    created_at: string;
}

interface ServiceStat {
    service_type: string;
    gross: number;
    net: number;
    profit: number;
    count: number;
}

interface Props {
    transactions: {
        data: BillingTransaction[];
        current_page: number;
        last_page: number;
        total: number;
    };
    stats: {
        total_gross: number;
        total_vendor_costs: number;
        total_platform_profit: number;
        total_transactions: number;
        by_service: ServiceStat[];
        recent: BillingTransaction[];
    };
    filters: {
        search?: string;
        service_type?: string;
    };
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters.search || '');
const serviceFilter = ref(props.filters.service_type || '');

watch(searchQuery, (val) => {
    router.get(route('admin.bizboost.billing'), { search: val || undefined, service_type: serviceFilter.value || undefined }, { preserveState: true, preserveScroll: true });
});

watch(serviceFilter, (val) => {
    router.get(route('admin.bizboost.billing'), { search: searchQuery.value || undefined, service_type: val || undefined }, { preserveState: true, preserveScroll: true });
});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount);
};
</script>

<template>
    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">BizBoost Billing</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <p class="text-sm text-gray-500">Total Gross</p>
                        <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(stats.total_gross) }}</p>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <p class="text-sm text-gray-500">Total Vendor Costs</p>
                        <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(stats.total_vendor_costs) }}</p>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <p class="text-sm text-gray-500">Platform Profit</p>
                        <p class="text-2xl font-bold text-green-600">{{ formatCurrency(stats.total_platform_profit) }}</p>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <p class="text-sm text-gray-500">Transactions</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.total_transactions.toLocaleString() }}</p>
                    </div>
                </div>

                <div v-if="stats.by_service.length" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">By Service</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Service</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Gross</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Net</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Profit</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Count</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="svc in stats.by_service" :key="svc.service_type" class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 capitalize">{{ svc.service_type }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900">{{ formatCurrency(svc.gross) }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900">{{ formatCurrency(svc.net) }}</td>
                                <td class="px-4 py-3 text-sm text-right text-green-600 font-medium">{{ formatCurrency(svc.profit) }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-600">{{ svc.count }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b flex flex-wrap gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Search User</label>
                            <input v-model="searchQuery" type="text" placeholder="Name or email..." class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Service Type</label>
                            <select v-model="serviceFilter" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">All</option>
                                <option value="ad_campaign">Ad Campaign</option>
                                <option value="whatsapp_message">WhatsApp</option>
                                <option value="sms_message">SMS</option>
                                <option value="omnichannel">Omnichannel</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Service</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Gross</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Vendor Cost</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Profit</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="txn in transactions.data" :key="txn.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ txn.user?.name || 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm capitalize text-gray-600">{{ txn.service_type }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-900">{{ formatCurrency(txn.gross_amount_charged) }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-900">{{ formatCurrency(txn.net_vendor_cost) }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-green-600 font-medium">{{ formatCurrency(txn.pure_platform_profit) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ txn.vendor || '—' }}</td>
                                    <td class="px-6 py-4">
                                        <span :class="['px-2 py-0.5 text-xs rounded-full', txn.delivery_status === 'delivered' ? 'bg-green-100 text-green-700' : txn.delivery_status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700']">
                                            {{ txn.delivery_status || '—' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 font-mono">{{ txn.reference || '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ txn.created_at }}</td>
                                </tr>
                                <tr v-if="transactions.data.length === 0">
                                    <td colspan="9" class="px-6 py-12 text-center text-gray-500">No billing transactions found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 border-t">
                        <Pagination :links="transactions.links" />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
