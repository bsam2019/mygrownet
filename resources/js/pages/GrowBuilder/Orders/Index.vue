<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ArrowLeftIcon, EyeIcon } from '@heroicons/vue/24/outline';

interface Order {
    id: number;
    order_number: string;
    customer_name: string;
    customer_phone: string;
    total: number;
    status: string;
    status_label: string;
    status_color: string;
    payment_method: string | null;
    item_count: number;
    created_at: string;
    paid_at: string | null;
}

interface Site {
    id: number;
    name: string;
    subdomain: string;
}

interface Stats {
    pending: number;
    processing: number;
    completed: number;
    total_revenue: number;
}

const props = defineProps<{
    site: Site;
    orders: {
        data: Order[];
        links: any[];
    };
    stats: Stats;
    currentStatus: string | null;
}>();

const formatPrice = (priceInNgwee: number): string => {
    return 'K' + (priceInNgwee / 100).toFixed(2);
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('en-ZM', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusColor = (color: string): string => {
    const colors: Record<string, string> = {
        yellow: 'bg-yellow-100 text-yellow-800',
        orange: 'bg-orange-100 text-orange-800',
        blue: 'bg-blue-100 text-blue-800',
        indigo: 'bg-indigo-100 text-indigo-800',
        purple: 'bg-purple-100 text-purple-800',
        green: 'bg-green-100 text-green-800',
        red: 'bg-red-100 text-red-800',
        gray: 'bg-gray-100 text-gray-800',
    };
    return colors[color] || colors.gray;
};

const filterByStatus = (status: string | null) => {
    router.get(route('growbuilder.orders.index', props.site.id), { status }, { preserveState: true });
};
</script>

<template>
    <AppLayout>
        <Head :title="`Orders - ${site.name}`" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('growbuilder.index')"
                        class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-4"
                    >
                        <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                        Back to Sites
                    </Link>

                    <h1 class="text-2xl font-bold text-gray-900">Orders</h1>
                    <p class="text-sm text-gray-500">{{ site.name }}</p>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-xl p-4 border border-gray-200">
                        <p class="text-sm text-gray-500">Pending</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ stats.pending }}</p>
                    </div>
                    <div class="bg-white rounded-xl p-4 border border-gray-200">
                        <p class="text-sm text-gray-500">Processing</p>
                        <p class="text-2xl font-bold text-blue-600">{{ stats.processing }}</p>
                    </div>
                    <div class="bg-white rounded-xl p-4 border border-gray-200">
                        <p class="text-sm text-gray-500">Completed</p>
                        <p class="text-2xl font-bold text-green-600">{{ stats.completed }}</p>
                    </div>
                    <div class="bg-white rounded-xl p-4 border border-gray-200">
                        <p class="text-sm text-gray-500">Total Revenue</p>
                        <p class="text-2xl font-bold text-gray-900">{{ formatPrice(stats.total_revenue) }}</p>
                    </div>
                </div>

                <!-- Filters -->
                <div class="flex gap-2 mb-4 overflow-x-auto pb-2">
                    <button
                        type="button"
                        :class="[
                            'px-3 py-1.5 text-sm font-medium rounded-full whitespace-nowrap',
                            !currentStatus ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                        ]"
                        @click="filterByStatus(null)"
                    >
                        All Orders
                    </button>
                    <button
                        v-for="status in ['pending', 'payment_pending', 'paid', 'processing', 'shipped', 'delivered', 'completed']"
                        :key="status"
                        type="button"
                        :class="[
                            'px-3 py-1.5 text-sm font-medium rounded-full whitespace-nowrap capitalize',
                            currentStatus === status ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                        ]"
                        @click="filterByStatus(status)"
                    >
                        {{ status.replace('_', ' ') }}
                    </button>
                </div>

                <!-- Orders Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div v-if="orders.data.length === 0" class="text-center py-12">
                        <p class="text-gray-500">No orders yet</p>
                    </div>

                    <table v-else class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="order in orders.data" :key="order.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-900">{{ order.order_number }}</p>
                                    <p class="text-sm text-gray-500">{{ order.item_count }} items</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-gray-900">{{ order.customer_name }}</p>
                                    <p class="text-sm text-gray-500">{{ order.customer_phone }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-900">{{ formatPrice(order.total) }}</p>
                                    <p v-if="order.payment_method" class="text-sm text-gray-500 capitalize">
                                        {{ order.payment_method }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusColor(order.status_color)]">
                                        {{ order.status_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ formatDate(order.created_at) }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Link
                                        :href="route('growbuilder.orders.show', { siteId: site.id, orderId: order.id })"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-blue-600 hover:bg-blue-50 rounded-lg"
                                    >
                                        <EyeIcon class="h-4 w-4" aria-hidden="true" />
                                        View
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
