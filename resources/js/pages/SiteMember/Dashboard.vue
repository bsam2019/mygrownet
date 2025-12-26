<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    HomeIcon,
    UserIcon,
    ShoppingBagIcon,
    DocumentTextIcon,
    UsersIcon,
    ChartBarIcon,
    Cog6ToothIcon,
    ArrowRightOnRectangleIcon,
} from '@heroicons/vue/24/outline';

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
    stats: {
        orders_count: number;
        posts_count: number;
    };
    recentOrders: Array<{
        id: number;
        order_number: string;
        total: number;
        status: string;
        created_at: string;
    }>;
    recentPosts: Array<{
        id: number;
        title: string;
        status: string;
        created_at: string;
    }>;
}

const props = defineProps<Props>();

const primaryColor = computed(() => props.site.theme?.primaryColor || '#2563eb');
const subdomain = computed(() => props.site.subdomain);

const hasPermission = (permission: string) => props.user.permissions.includes(permission);

const navigation = computed(() => {
    const items = [
        { name: 'Dashboard', href: route('site.member.dashboard', { subdomain: subdomain.value }), icon: HomeIcon, current: true },
        { name: 'Profile', href: route('site.member.profile', { subdomain: subdomain.value }), icon: UserIcon },
        { name: 'Orders', href: route('site.member.orders', { subdomain: subdomain.value }), icon: ShoppingBagIcon },
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
    });
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-800',
        paid: 'bg-green-100 text-green-800',
        processing: 'bg-blue-100 text-blue-800',
        completed: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
        draft: 'bg-gray-100 text-gray-800',
        published: 'bg-green-100 text-green-800',
        scheduled: 'bg-blue-100 text-blue-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head :title="`Dashboard - ${site.name}`" />

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
                    <!-- Welcome -->
                    <div class="mb-8">
                        <h1 class="text-2xl font-bold text-gray-900">Welcome back, {{ user.name }}!</h1>
                        <p class="text-gray-600">Here's what's happening with your account.</p>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <div class="flex items-center gap-4">
                                <div
                                    class="p-3 rounded-lg"
                                    :style="{ backgroundColor: primaryColor + '20' }"
                                >
                                    <ShoppingBagIcon class="h-6 w-6" :style="{ color: primaryColor }" aria-hidden="true" />
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Total Orders</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ stats.orders_count }}</p>
                                </div>
                            </div>
                        </div>

                        <div v-if="hasPermission('posts.view')" class="bg-white rounded-xl shadow-sm p-6">
                            <div class="flex items-center gap-4">
                                <div
                                    class="p-3 rounded-lg"
                                    :style="{ backgroundColor: primaryColor + '20' }"
                                >
                                    <DocumentTextIcon class="h-6 w-6" :style="{ color: primaryColor }" aria-hidden="true" />
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Your Posts</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ stats.posts_count }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Orders -->
                    <div class="bg-white rounded-xl shadow-sm mb-8">
                        <div class="px-6 py-4 border-b border-gray-100">
                            <h2 class="text-lg font-semibold text-gray-900">Recent Orders</h2>
                        </div>
                        <div v-if="recentOrders.length > 0" class="divide-y divide-gray-100">
                            <div
                                v-for="order in recentOrders"
                                :key="order.id"
                                class="px-6 py-4 flex items-center justify-between"
                            >
                                <div>
                                    <p class="font-medium text-gray-900">{{ order.order_number }}</p>
                                    <p class="text-sm text-gray-500">{{ formatDate(order.created_at) }}</p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span
                                        class="px-2 py-1 text-xs font-medium rounded-full"
                                        :class="getStatusColor(order.status)"
                                    >
                                        {{ order.status }}
                                    </span>
                                    <span class="font-medium text-gray-900">{{ formatCurrency(order.total) }}</span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="px-6 py-8 text-center text-gray-500">
                            No orders yet
                        </div>
                    </div>

                    <!-- Recent Posts (if editor) -->
                    <div v-if="hasPermission('posts.view') && recentPosts.length > 0" class="bg-white rounded-xl shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-100">
                            <h2 class="text-lg font-semibold text-gray-900">Recent Posts</h2>
                        </div>
                        <div class="divide-y divide-gray-100">
                            <div
                                v-for="post in recentPosts"
                                :key="post.id"
                                class="px-6 py-4 flex items-center justify-between"
                            >
                                <div>
                                    <p class="font-medium text-gray-900">{{ post.title }}</p>
                                    <p class="text-sm text-gray-500">{{ formatDate(post.created_at) }}</p>
                                </div>
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full"
                                    :class="getStatusColor(post.status)"
                                >
                                    {{ post.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>
</template>
