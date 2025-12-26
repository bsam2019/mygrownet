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
    EyeIcon,
} from '@heroicons/vue/24/outline';

interface Order {
    id: number;
    order_number: string;
    total: number;
    status: string;
    created_at: string;
    items: Array<{ name: string; quantity: number; price: number }>;
}

interface Props {
    site: {
        id: number;
        name: string;
        subdomain: string;
        logo: string | null;
        theme: {
            primaryColor?: string;
        } | null;
    };
    user: {
        id: number;
        name: string;
        email: string;
        role: { name: string; slug: string } | null;
        permissions: string[];
    };
    orders: {
        data: Order[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
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
        month: 'short',
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
    <Head :title="`Orders - ${site.name}`" />

    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center gap-3">
                        <img
                            v-if="site.logo"
                            :src="site.logo"
                            :alt="site.name"
                            class="h-10 w-auto"
                        />
                        <div
                            v-else
                            class="h-10 w-10 rounded-full flex items-center justify-center text-white font-bold"
                            :style="{ backgroundColor: primaryColor }"
                        >
                            {{ site.name.charAt(0) }}
                        </div>
                        <span class="text-xl font-semibold text-gray-900">{{ site.name }}</span>
                    </div>

                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-600">{{ user.name }}</span>
                        <button
                            @click="logout"
                            class="flex items-center gap-1 text-sm text-gray-600 hover:text-gray-900"
                        >
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
                    <div class="mb-8">
                        <h1 class="text-2xl font-bold text-gray-900">My Orders</h1>
                        <p class="text-gray-600">View your order history</p>
                    </div>

                    <!-- Orders Table -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <table v-if="orders.data.length > 0" class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Order
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Total
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="order in orders.data" :key="order.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-medium text-gray-900">{{ order.order_number }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDate(order.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full"
                                            :class="getStatusColor(order.status)"
                                        >
                                            {{ order.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ formatCurrency(order.total) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <Link
                                            :href="route('site.member.orders.show', { subdomain, id: order.id })"
                                            class="inline-flex items-center gap-1 text-sm font-medium hover:underline"
                                            :style="{ color: primaryColor }"
                                        >
                                            <EyeIcon class="h-4 w-4" aria-hidden="true" />
                                            View
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div v-else class="px-6 py-12 text-center">
                            <ShoppingBagIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No orders</h3>
                            <p class="mt-1 text-sm text-gray-500">You haven't placed any orders yet.</p>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="orders.last_page > 1" class="mt-6 flex justify-center">
                        <nav class="flex items-center gap-2">
                            <Link
                                v-for="page in orders.last_page"
                                :key="page"
                                :href="route('site.member.orders', { subdomain, page })"
                                class="px-3 py-2 text-sm font-medium rounded-lg"
                                :class="page === orders.current_page ? 'text-white' : 'text-gray-700 hover:bg-gray-100'"
                                :style="page === orders.current_page ? { backgroundColor: primaryColor } : {}"
                            >
                                {{ page }}
                            </Link>
                        </nav>
                    </div>
                </main>
            </div>
        </div>
    </div>
</template>
