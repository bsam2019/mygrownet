<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ArrowLeftIcon, PrinterIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';

interface OrderItem {
    product_id: number;
    name: string;
    price: number;
    quantity: number;
    total: number;
}

interface Order {
    id: number;
    order_number: string;
    customer_name: string;
    customer_phone: string;
    customer_email: string | null;
    customer_address: string | null;
    customer_city: string | null;
    items: OrderItem[];
    subtotal: number;
    shipping_cost: number;
    discount_amount: number;
    total: number;
    status: string;
    payment_method: string | null;
    payment_reference: string | null;
    notes: string | null;
    created_at: string;
    paid_at: string | null;
}

interface Site {
    id: number;
    name: string;
}

const props = defineProps<{
    site: Site;
    order: Order;
}>();

const statusForm = useForm({ status: props.order.status });

const statusOptions = [
    { value: 'pending', label: 'Pending' },
    { value: 'payment_pending', label: 'Payment Pending' },
    { value: 'paid', label: 'Paid' },
    { value: 'processing', label: 'Processing' },
    { value: 'shipped', label: 'Shipped' },
    { value: 'delivered', label: 'Delivered' },
    { value: 'completed', label: 'Completed' },
    { value: 'cancelled', label: 'Cancelled' },
];

const formatCurrency = (amount: number) => {
    return `K${(amount / 100).toFixed(2)}`;
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-ZM', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-800',
        payment_pending: 'bg-orange-100 text-orange-800',
        paid: 'bg-green-100 text-green-800',
        processing: 'bg-blue-100 text-blue-800',
        shipped: 'bg-purple-100 text-purple-800',
        delivered: 'bg-emerald-100 text-emerald-800',
        completed: 'bg-gray-100 text-gray-800',
        cancelled: 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const updateStatus = () => {
    statusForm.put(route('growbuilder.orders.status', [props.site.id, props.order.id]));
};

const markAsPaid = () => {
    if (confirm('Mark this order as paid?')) {
        useForm({}).post(route('growbuilder.orders.mark-paid', [props.site.id, props.order.id]));
    }
};
</script>

<template>
    <AppLayout>
        <Head :title="`Order ${order.order_number} - ${site.name}`" />

        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('growbuilder.orders.index', site.id)"
                        class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-4"
                    >
                        <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                        Back to Orders
                    </Link>

                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Order {{ order.order_number }}</h1>
                            <p class="text-sm text-gray-500">{{ formatDate(order.created_at) }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span :class="['px-3 py-1 text-sm font-medium rounded-full', getStatusColor(order.status)]">
                                {{ order.status.replace('_', ' ') }}
                            </span>
                            <button
                                @click="window.print()"
                                class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg"
                                aria-label="Print order"
                            >
                                <PrinterIcon class="h-5 w-5" aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Order Items -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Items</h2>
                            <div class="divide-y divide-gray-200">
                                <div
                                    v-for="item in order.items"
                                    :key="item.product_id"
                                    class="py-3 flex justify-between"
                                >
                                    <div>
                                        <p class="font-medium text-gray-900">{{ item.name }}</p>
                                        <p class="text-sm text-gray-500">
                                            {{ formatCurrency(item.price) }} × {{ item.quantity }}
                                        </p>
                                    </div>
                                    <p class="font-medium text-gray-900">{{ formatCurrency(item.total) }}</p>
                                </div>
                            </div>

                            <div class="mt-4 pt-4 border-t border-gray-200 space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Subtotal</span>
                                    <span>{{ formatCurrency(order.subtotal) }}</span>
                                </div>
                                <div v-if="order.shipping_cost > 0" class="flex justify-between text-sm">
                                    <span class="text-gray-500">Shipping</span>
                                    <span>{{ formatCurrency(order.shipping_cost) }}</span>
                                </div>
                                <div v-if="order.discount_amount > 0" class="flex justify-between text-sm text-green-600">
                                    <span>Discount</span>
                                    <span>-{{ formatCurrency(order.discount_amount) }}</span>
                                </div>
                                <div class="flex justify-between text-lg font-semibold pt-2 border-t">
                                    <span>Total</span>
                                    <span>{{ formatCurrency(order.total) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div v-if="order.notes" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-2">Customer Notes</h2>
                            <p class="text-gray-600">{{ order.notes }}</p>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Customer Info -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Customer</h2>
                            <div class="space-y-3 text-sm">
                                <div>
                                    <p class="text-gray-500">Name</p>
                                    <p class="font-medium">{{ order.customer_name }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Phone</p>
                                    <p class="font-medium">{{ order.customer_phone }}</p>
                                </div>
                                <div v-if="order.customer_email">
                                    <p class="text-gray-500">Email</p>
                                    <p class="font-medium">{{ order.customer_email }}</p>
                                </div>
                                <div v-if="order.customer_address">
                                    <p class="text-gray-500">Address</p>
                                    <p class="font-medium">{{ order.customer_address }}</p>
                                    <p v-if="order.customer_city" class="text-gray-600">{{ order.customer_city }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Info -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment</h2>
                            <div class="space-y-3 text-sm">
                                <div>
                                    <p class="text-gray-500">Method</p>
                                    <p class="font-medium capitalize">{{ order.payment_method || 'Not specified' }}</p>
                                </div>
                                <div v-if="order.payment_reference">
                                    <p class="text-gray-500">Reference</p>
                                    <p class="font-medium font-mono text-xs">{{ order.payment_reference }}</p>
                                </div>
                                <div v-if="order.paid_at">
                                    <p class="text-gray-500">Paid At</p>
                                    <p class="font-medium">{{ formatDate(order.paid_at) }}</p>
                                </div>
                            </div>

                            <button
                                v-if="!order.paid_at && order.status !== 'cancelled'"
                                @click="markAsPaid"
                                class="mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700"
                            >
                                <CheckCircleIcon class="h-4 w-4" aria-hidden="true" />
                                Mark as Paid
                            </button>
                        </div>

                        <!-- Update Status -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Update Status</h2>
                            <select
                                v-model="statusForm.status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 mb-3"
                            >
                                <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
                                    {{ opt.label }}
                                </option>
                            </select>
                            <button
                                @click="updateStatus"
                                :disabled="statusForm.processing || statusForm.status === order.status"
                                class="w-full px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50"
                            >
                                {{ statusForm.processing ? 'Updating...' : 'Update Status' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
