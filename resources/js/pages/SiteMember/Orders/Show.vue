<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import SiteMemberLayout from '@/layouts/SiteMemberLayout.vue';
import { computed } from 'vue';
import { ArrowLeftIcon, ShoppingBagIcon } from '@heroicons/vue/24/outline';

interface OrderItem {
    id: number;
    name: string;
    quantity: number;
    price: number;
    total: number;
}

interface Order {
    id: number;
    order_number: string;
    total: number;
    subtotal: number;
    status: string;
    payment_method: string;
    customer_name: string;
    customer_email: string;
    customer_phone: string;
    notes: string | null;
    items: OrderItem[];
    created_at: string;
    paid_at: string | null;
}

interface Props {
    site: { id: number; name: string; subdomain: string; theme: { primaryColor?: string } | null };
    settings: { navigation?: { logo?: string } } | null;
    user: { id: number; name: string; email: string; role: any; permissions: string[] };
    order: Order;
}

const props = defineProps<Props>();
const primaryColor = computed(() => props.site.theme?.primaryColor || '#2563eb');

const formatCurrency = (amount: number) => new Intl.NumberFormat('en-ZM', { style: 'currency', currency: 'ZMW', minimumFractionDigits: 0 }).format(amount / 100);
const formatDate = (date: string) => new Date(date).toLocaleDateString('en-ZM', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' });

const getStatusConfig = (status: string) => {
    const configs: Record<string, { bg: string; text: string; label: string }> = {
        pending: { bg: 'bg-amber-100', text: 'text-amber-700', label: 'Pending Payment' },
        paid: { bg: 'bg-emerald-100', text: 'text-emerald-700', label: 'Paid' },
        completed: { bg: 'bg-emerald-100', text: 'text-emerald-700', label: 'Completed' },
        cancelled: { bg: 'bg-red-100', text: 'text-red-700', label: 'Cancelled' },
    };
    return configs[status] || { bg: 'bg-gray-100', text: 'text-gray-600', label: status };
};
</script>

<template>
    <SiteMemberLayout :site="site" :settings="settings" :user="user" title="Order Details">
        <Head :title="`Order ${order.order_number} - ${site.name}`" />

        <div class="max-w-3xl mx-auto">
            <!-- Back Link -->
            <Link :href="`/sites/${site.subdomain}/dashboard/orders`" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-4">
                <ArrowLeftIcon class="w-4 h-4" aria-hidden="true" />
                Back to Orders
            </Link>

            <!-- Order Header -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Order {{ order.order_number }}</h1>
                        <p class="text-gray-500">Placed on {{ formatDate(order.created_at) }}</p>
                    </div>
                    <span class="px-3 py-1 text-sm font-medium rounded-full" :class="[getStatusConfig(order.status).bg, getStatusConfig(order.status).text]">
                        {{ getStatusConfig(order.status).label }}
                    </span>
                </div>
            </div>

            <!-- Order Items -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Order Items</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    <div v-for="item in order.items" :key="item.id" class="px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center">
                                <ShoppingBagIcon class="w-6 h-6 text-gray-400" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ item.name }}</p>
                                <p class="text-sm text-gray-500">Qty: {{ item.quantity }} × {{ formatCurrency(item.price) }}</p>
                            </div>
                        </div>
                        <p class="font-semibold text-gray-900">{{ formatCurrency(item.total) }}</p>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    <div class="flex justify-between text-lg font-semibold">
                        <span>Total</span>
                        <span :style="{ color: primaryColor }">{{ formatCurrency(order.total) }}</span>
                    </div>
                </div>
            </div>

            <!-- Payment Info -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="font-semibold text-gray-900 mb-4">Payment Information</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Payment Method</dt>
                        <dd class="font-medium text-gray-900 capitalize">{{ order.payment_method || 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Paid At</dt>
                        <dd class="font-medium text-gray-900">{{ order.paid_at ? formatDate(order.paid_at) : 'Not paid' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </SiteMemberLayout>
</template>
