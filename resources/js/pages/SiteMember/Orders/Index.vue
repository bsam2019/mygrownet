<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import SiteMemberLayout from '@/layouts/SiteMemberLayout.vue';
import { computed } from 'vue';
import { ShoppingBagIcon, EyeIcon } from '@heroicons/vue/24/outline';

interface Order {
    id: number;
    order_number: string;
    total: number;
    status: string;
    created_at: string;
    items_count?: number;
}

interface Props {
    site: { id: number; name: string; subdomain: string; theme: { primaryColor?: string } | null };
    settings: { navigation?: { logo?: string } } | null;
    user: { id: number; name: string; email: string; role: any; permissions: string[] };
    orders: { data: Order[]; links: any; meta: any };
}

const props = defineProps<Props>();
const primaryColor = computed(() => props.site.theme?.primaryColor || '#2563eb');

const formatCurrency = (amount: number) => new Intl.NumberFormat('en-ZM', { style: 'currency', currency: 'ZMW', minimumFractionDigits: 0 }).format(amount / 100);
const formatDate = (date: string) => new Date(date).toLocaleDateString('en-ZM', { year: 'numeric', month: 'short', day: 'numeric' });

const getStatusConfig = (status: string) => {
    const configs: Record<string, { bg: string; text: string }> = {
        pending: { bg: 'bg-amber-100', text: 'text-amber-700' },
        paid: { bg: 'bg-emerald-100', text: 'text-emerald-700' },
        completed: { bg: 'bg-emerald-100', text: 'text-emerald-700' },
        cancelled: { bg: 'bg-red-100', text: 'text-red-700' },
        refunded: { bg: 'bg-gray-100', text: 'text-gray-700' },
    };
    return configs[status] || { bg: 'bg-gray-100', text: 'text-gray-600' };
};
</script>

<template>
    <SiteMemberLayout :site="site" :settings="settings" :user="user" title="My Orders">
        <Head :title="`Orders - ${site.name}`" />

        <div class="max-w-4xl mx-auto">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">My Orders</h1>
                <p class="text-gray-500">View and track your order history</p>
            </div>

            <!-- Orders List -->
            <div v-if="orders.data.length > 0" class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="divide-y divide-gray-100">
                    <div v-for="order in orders.data" :key="order.id" class="p-5 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center">
                                    <ShoppingBagIcon class="w-6 h-6 text-gray-400" aria-hidden="true" />
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ order.order_number }}</p>
                                    <p class="text-sm text-gray-500">{{ formatDate(order.created_at) }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <p class="font-semibold text-gray-900">{{ formatCurrency(order.total) }}</p>
                                    <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full capitalize" :class="[getStatusConfig(order.status).bg, getStatusConfig(order.status).text]">
                                        {{ order.status }}
                                    </span>
                                </div>
                                <Link :href="`/sites/${site.subdomain}/dashboard/orders/${order.id}`" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg" title="View Details">
                                    <EyeIcon class="w-5 h-5" aria-hidden="true" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white rounded-xl border border-gray-200 p-12 text-center">
                <ShoppingBagIcon class="w-16 h-16 mx-auto text-gray-300 mb-4" aria-hidden="true" />
                <h3 class="text-lg font-medium text-gray-900 mb-2">No orders yet</h3>
                <p class="text-gray-500 mb-4">When you make a purchase, your orders will appear here.</p>
                <a :href="`/sites/${site.subdomain}`" class="inline-flex px-4 py-2 text-white font-medium rounded-lg" :style="{ backgroundColor: primaryColor }">
                    Browse Products
                </a>
            </div>
        </div>
    </SiteMemberLayout>
</template>
