<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    HomeIcon,
    UserIcon,
    ShoppingBagIcon,
    DocumentTextIcon,
    UsersIcon,
    ArrowRightOnRectangleIcon,
    ArrowLeftIcon,
} from '@heroicons/vue/24/outline';

interface Props {
    site: {
        id: number;
        name: string;
        subdomain: string;
        logo: string | null;
        theme: { primaryColor?: string } | null;
    };
    user: {
        id: number;
        name: string;
        email: string;
        role: { name: string; slug: string } | null;
        permissions: string[];
    };
    order: {
        id: number;
        order_number: string;
        customer_name: string;
        customer_phone: string;
        customer_email: string | null;
        items: Array<{ name: string; quantity: number; price: number }>;
        subtotal: number;
        shipping_cost: number;
        total: number;
        status: string;
        payment_method: string;
        notes: string | null;
        created_at: string;
    };
}

const props = defineProps<Props>();

const primaryColor = computed(() => props.site.theme?.primaryColor || '#2563eb');
const subdomain = computed(() => props.site.subdomain);

const hasPermission = (permission: string) => props.user.permissions.includes(permission);

const navigation = computed(() => {
    const items = [
        { name: 'Dashboard', href: route('site.member.dashboard', { subdomain: subdomain.value }), icon: HomeIcon },
        { name: 'Profile', href: route('site.member.profile', { subdomain: subdomain.value }), icon: UserIcon },
        { name: 'Orders', href: route('site.member.orders', { subdomain: subdomain.value }), icon: ShoppingBagIcon, current: true },
    ];

    if (hasPermission('posts.view')) {
        items.push({ name: 'Posts', href: route('site.member.posts.index', { subdomain: subdomain.value }), icon: DocumentTextIcon });
    }

    if (hasPermission('users.view')) {
        items.push({ name: 'Users', href: route('site.member.users.index', { subdomain: subdomain.value }), icon: UsersIcon });
    }

    return items;
});

const logout = () => {
    router.post(route('site.logout', { subdomain: subdomain.value }));
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
    }).format(amount / 100);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-ZM', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-800',
        paid: 'bg-green-100 text-green-800',
        processing: 'bg-blue-100 text-blue-800',
        completed: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head :title="`Order ${order.order_number} - ${site.name}`" />

    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center gap-3">
                        <img v-if="site.logo" :src="site.logo" :alt="site.name" class="h-10 w-auto" />
                        <div v-else class="h-10 w-10 rounded-full flex items-center justify-center text-white font-bold" :style="{ backgroundColor: primaryColor }">
                            {{ site.name.charAt(0) }}
                        </div>
                        <span class="text-xl font-semibold text-gray-900">{{ site.name }}</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-600">{{ user.name }}</span>
                        <button @click="logout" class="flex items-center gap-1 text-sm text-gray-600 hover:text-gray-900">
                            <ArrowRightOnRectangleIcon class="h-5 w-5" aria-hidden="true" />
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex gap-8">
                <!-- Sidebar -->
                <aside class="w-64 flex-shrink-0">
                    <nav class="space-y-1">
                        <Link
                            v-for="item in navigation"
                            :key="item.name"
                            :href="item.href"
                            class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-colors"
                            :class="item.current ? 'text-white' : 'text-gray-700 hover:bg-gray-100'"
                            :style="item.current ? { backgroundColor: primaryColor } : {}"
                        >
                            <component :is="item.icon" class="h-5 w-5" aria-hidden="true" />
                            {{ item.name }}
                        </Link>
                    </nav>
                </aside>

                <!-- Main Content -->
                <main class="flex-1">
                    <!-- Back Link -->
                    <Link
                        :href="route('site.member.orders', { subdomain })"
                        class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 mb-6"
                    >
                        <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                        Back to Orders
                    </Link>

                    <!-- Order Header -->
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Order {{ order.order_number }}</h1>
                            <p class="text-gray-600">{{ formatDate(order.created_at) }}</p>
                        </div>
                        <span class="px-3 py-1 text-sm font-medium rounded-full" :class="getStatusColor(order.status)">
                            {{ order.status }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Order Items -->
                        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm">
                            <div class="px-6 py-4 border-b border-gray-100">
                                <h2 class="text-lg font-semibold text-gray-900">Order Items</h2>
                            </div>
                            <div class="divide-y divide-gray-100">
                                <div v-for="(item, index) in order.items" :key="index" class="px-6 py-4 flex justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ item.name }}</p>
                                        <p class="text-sm text-gray-500">Qty: {{ item.quantity }}</p>
                                    </div>
                                    <p class="font-medium text-gray-900">{{ formatCurrency(item.price * item.quantity) }}</p>
                                </div>
                            </div>
                            <div class="px-6 py-4 border-t border-gray-100 space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Subtotal</span>
                                    <span class="text-gray-900">{{ formatCurrency(order.subtotal) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Shipping</span>
                                    <span class="text-gray-900">{{ formatCurrency(order.shipping_cost) }}</span>
                                </div>
                                <div class="flex justify-between text-lg font-semibold pt-2 border-t border-gray-100">
                                    <span class="text-gray-900">Total</span>
                                    <span :style="{ color: primaryColor }">{{ formatCurrency(order.total) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Order Details -->
                        <div class="space-y-6">
                            <div class="bg-white rounded-xl shadow-sm p-6">
                                <h3 class="text-sm font-semibold text-gray-900 mb-4">Customer Details</h3>
                                <dl class="space-y-3 text-sm">
                                    <div>
                                        <dt class="text-gray-500">Name</dt>
                                        <dd class="text-gray-900">{{ order.customer_name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500">Phone</dt>
                                        <dd class="text-gray-900">{{ order.customer_phone }}</dd>
                                    </div>
                                    <div v-if="order.customer_email">
                                        <dt class="text-gray-500">Email</dt>
                                        <dd class="text-gray-900">{{ order.customer_email }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div class="bg-white rounded-xl shadow-sm p-6">
                                <h3 class="text-sm font-semibold text-gray-900 mb-4">Payment</h3>
                                <p class="text-sm text-gray-900 capitalize">{{ order.payment_method }}</p>
                            </div>

                            <div v-if="order.notes" class="bg-white rounded-xl shadow-sm p-6">
                                <h3 class="text-sm font-semibold text-gray-900 mb-4">Notes</h3>
                                <p class="text-sm text-gray-600">{{ order.notes }}</p>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>
</template>
