<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';

const props = defineProps<{
    orders: { data: any[]; current_page: number; last_page: number; total: number };
    stats: { pending: number; confirmed: number; processing: number; delivered: number; cancelled: number };
    filters: { status?: string; payment_status?: string; search?: string };
}>();

const searchQuery = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const paymentFilter = ref(props.filters.payment_status || '');

const orderStatusColors: Record<string, string> = {
    pending: 'bg-amber-100 text-amber-800',
    confirmed: 'bg-blue-100 text-blue-800',
    processing: 'bg-indigo-100 text-indigo-800',
    delivered: 'bg-emerald-100 text-emerald-800',
    cancelled: 'bg-red-100 text-red-800',
};

const paymentStatusColors: Record<string, string> = {
    pending: 'bg-amber-100 text-amber-700',
    paid: 'bg-emerald-100 text-emerald-700',
    failed: 'bg-red-100 text-red-700',
    refunded: 'bg-gray-100 text-gray-700',
};

const applyFilters = () => {
    router.get(route('bizboost.shop.orders'), {
        status: statusFilter.value || undefined,
        payment_status: paymentFilter.value || undefined,
        search: searchQuery.value || undefined,
    }, { preserveState: true });
};

const updateStatus = (orderId: number, orderStatus: string, paymentStatus?: string) => {
    router.post(route('bizboost.shop.orders.update-status', orderId), {
        order_status: orderStatus,
        payment_status: paymentStatus || undefined,
    }, { preserveState: true });
};

const formatPrice = (amount: number, currency = 'ZMW') => `${currency} ${Number(amount).toFixed(2)}`;
</script>

<template>
    <BizBoostLayout title="Orders">
        <Head title="Orders - BizBoost" />

        <div class="max-w-5xl mx-auto px-4 py-6">
            <!-- Stats -->
            <div class="grid grid-cols-5 gap-3 mb-6">
                <div v-for="(count, key) in stats" :key="key" class="bg-white rounded-xl border p-4 text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ count }}</p>
                    <p class="text-xs text-gray-500 capitalize">{{ key }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-xl border p-4 mb-6 flex flex-wrap gap-3">
                <input v-model="searchQuery" placeholder="Search by name, phone, or order #" class="flex-1 min-w-[200px] px-3 py-2 border rounded-lg text-sm"
                    @keyup.enter="applyFilters" />
                <select v-model="statusFilter" @change="applyFilters" class="px-3 py-2 border rounded-lg text-sm">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="processing">Processing</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <select v-model="paymentFilter" @change="applyFilters" class="px-3 py-2 border rounded-lg text-sm">
                    <option value="">All Payments</option>
                    <option value="pending">Pending</option>
                    <option value="paid">Paid</option>
                    <option value="failed">Failed</option>
                    <option value="refunded">Refunded</option>
                </select>
                <button @click="applyFilters" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700">Search</button>
            </div>

            <!-- Orders List -->
            <div v-if="orders.data.length === 0" class="bg-white rounded-xl border p-12 text-center text-gray-500">
                No orders found
            </div>
            <div v-else class="space-y-4">
                <div v-for="order in orders.data" :key="order.id" class="bg-white rounded-xl border p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <span class="font-bold text-gray-900">#{{ order.order_number }}</span>
                            <p class="text-sm text-gray-500 mt-0.5">{{ order.customer_name }} · {{ order.customer_phone }}</p>
                        </div>
                        <div class="flex gap-2">
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium capitalize" :class="orderStatusColors[order.order_status] || 'bg-gray-100'">{{ order.order_status }}</span>
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium capitalize" :class="paymentStatusColors[order.payment_status] || 'bg-gray-100'">{{ order.payment_status }}</span>
                        </div>
                    </div>

                    <div class="text-sm text-gray-600 mb-3">
                        <div v-for="item in order.items" :key="item.id" class="flex justify-between py-1">
                            <span>{{ item.product_name }} x{{ item.quantity }}</span>
                            <span>{{ formatPrice(item.subtotal, order.currency) }}</span>
                        </div>
                        <div class="flex justify-between font-bold text-gray-900 border-t pt-2 mt-2">
                            <span>Total</span>
                            <span>{{ formatPrice(order.total, order.currency) }}</span>
                        </div>
                    </div>

                    <div v-if="order.delivery_address" class="text-xs text-gray-500 mb-3">
                        Delivery: {{ order.delivery_address }}
                    </div>

                    <div class="flex flex-wrap gap-2 pt-3 border-t">
                        <select @change="updateStatus(order.id, ($event.target as HTMLSelectElement).value, order.payment_status)"
                            class="px-3 py-1.5 border rounded-lg text-sm">
                            <option value="pending" :selected="order.order_status === 'pending'">Pending</option>
                            <option value="confirmed" :selected="order.order_status === 'confirmed'">Confirmed</option>
                            <option value="processing" :selected="order.order_status === 'processing'">Processing</option>
                            <option value="delivered" :selected="order.order_status === 'delivered'">Delivered</option>
                            <option value="cancelled" :selected="order.order_status === 'cancelled'">Cancelled</option>
                        </select>
                        <select @change="updateStatus(order.id, order.order_status, ($event.target as HTMLSelectElement).value)"
                            class="px-3 py-1.5 border rounded-lg text-sm">
                            <option value="pending" :selected="order.payment_status === 'pending'">Payment: Pending</option>
                            <option value="paid" :selected="order.payment_status === 'paid'">Payment: Paid</option>
                            <option value="failed" :selected="order.payment_status === 'failed'">Payment: Failed</option>
                            <option value="refunded" :selected="order.payment_status === 'refunded'">Payment: Refunded</option>
                        </select>
                        <span class="text-xs text-gray-400 self-center ml-auto">{{ new Date(order.created_at).toLocaleDateString() }}</span>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="orders.last_page > 1" class="mt-6 flex justify-center gap-2">
                <Link v-if="orders.current_page > 1" :href="route('bizboost.shop.orders', { page: orders.current_page - 1 })" class="px-4 py-2 bg-white border rounded-lg text-sm hover:bg-gray-50">Previous</Link>
                <span class="px-3 py-2 text-sm text-gray-500">{{ orders.current_page }} / {{ orders.last_page }}</span>
                <Link v-if="orders.current_page < orders.last_page" :href="route('bizboost.shop.orders', { page: orders.current_page + 1 })" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700">Next</Link>
            </div>
        </div>
    </BizBoostLayout>
</template>
