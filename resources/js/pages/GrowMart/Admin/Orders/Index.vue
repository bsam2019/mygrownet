<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/GrowMartAdminLayout.vue';
import { ClipboardDocumentListIcon, FunnelIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import Pagination from '@/components/Pagination.vue';

interface Order {
    id: number;
    order_number: string;
    customer: string;
    status: string;
    payment_status: string;
    delivery_method: string;
    item_count: number;
    total_formatted: string;
    created_at: string;
    created_at_diff: string;
}

interface Props {
    orders: { data: Order[]; meta: any };
    filters: { status?: string; payment_status?: string; q?: string; sort?: string };
}

const props = defineProps<Props>();

const statusColor: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-blue-100 text-blue-800', processing: 'bg-indigo-100 text-indigo-800',
    out_for_delivery: 'bg-purple-100 text-purple-800', delivered: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
};

const paymentColor: Record<string, string> = {
    pending: 'bg-gray-100 text-gray-600', pending_verification: 'bg-blue-100 text-blue-800',
    paid: 'bg-green-100 text-green-800', failed: 'bg-red-100 text-red-800',
    refunded: 'bg-orange-100 text-orange-800',
};

const applyFilter = (key: string, value: string) => {
    router.get(route('admin.growmart.orders.index'), { ...props.filters, [key]: value || undefined }, { preserveState: true });
};

const clearFilters = () => {
    router.get(route('admin.growmart.orders.index'), {}, { preserveState: true });
};

const hasFilters = props.filters?.status || props.filters?.payment_status || props.filters?.q;
</script>

<template>
    <Head title="Orders - GrowMart Admin" />

    <AdminLayout title="Orders">
        <!-- Filters -->
        <div class="bg-white rounded-lg border border-gray-200 p-4 mb-4">
            <div class="flex flex-wrap items-center gap-3">
                <input v-model="props.filters.q" @keydown.enter="applyFilter('q', ($event.target as HTMLInputElement).value)" placeholder="Search order # or customer..." class="px-3 py-2 border border-gray-300 rounded-lg text-sm min-w-[200px]" />
                <select :value="filters.status || ''" @change="applyFilter('status', ($event.target as HTMLSelectElement).value)" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="processing">Processing</option>
                    <option value="out_for_delivery">Out for Delivery</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <select :value="filters.payment_status || ''" @change="applyFilter('payment_status', ($event.target as HTMLSelectElement).value)" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">All Payments</option>
                    <option value="pending">Pending</option>
                    <option value="pending_verification">Pending Verification</option>
                    <option value="paid">Paid</option>
                    <option value="failed">Failed</option>
                    <option value="refunded">Refunded</option>
                </select>
                <select :value="filters.sort || 'latest'" @change="applyFilter('sort', ($event.target as HTMLSelectElement).value)" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="latest">Latest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="total">Highest Total</option>
                </select>
                <button v-if="hasFilters" @click="clearFilters" class="text-sm text-red-600 hover:text-red-700 font-medium">Clear</button>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="order in orders.data" :key="order.id" class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-mono text-sm font-medium text-gray-900">{{ order.order_number }}</td>
                            <td class="px-6 py-3 text-gray-700">{{ order.customer }}</td>
                            <td class="px-6 py-3"><span :class="statusColor[order.status] || ''" class="px-2.5 py-0.5 rounded-full text-xs font-medium capitalize">{{ order.status.replace('_', ' ') }}</span></td>
                            <td class="px-6 py-3"><span :class="paymentColor[order.payment_status] || ''" class="px-2.5 py-0.5 rounded-full text-xs font-medium">{{ order.payment_status }}</span></td>
                            <td class="px-6 py-3 text-gray-600">{{ order.item_count }}</td>
                            <td class="px-6 py-3 font-medium text-gray-900">{{ order.total_formatted }}</td>
                            <td class="px-6 py-3 text-sm text-gray-500" :title="order.created_at">{{ order.created_at_diff }}</td>
                            <td class="px-6 py-3 text-right"><Link :href="route('admin.growmart.orders.show', order.id)" class="text-emerald-600 hover:text-emerald-700 text-sm font-medium">View</Link></td>
                        </tr>
                        <tr v-if="orders.data.length === 0">
                            <td colspan="8" class="px-6 py-12 text-center">
                                <ClipboardDocumentListIcon class="mx-auto h-12 w-12 text-gray-400" />
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No orders found</h3>
                                <p class="text-sm text-gray-500">Try adjusting your filters</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Pagination :links="orders.meta.links" />
    </AdminLayout>
</template>
