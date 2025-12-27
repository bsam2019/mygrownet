<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import SiteMemberLayout from '@/layouts/SiteMemberLayout.vue';
import {
    ShoppingBagIcon,
    DocumentTextIcon,
    UsersIcon,
    PlusIcon,
    EyeIcon,
    PencilSquareIcon,
    ChevronRightIcon,
    CurrencyDollarIcon,
    ClockIcon,
    ShieldCheckIcon,
    BriefcaseIcon,
    StarIcon,
    InboxIcon,
    UserIcon,
} from '@heroicons/vue/24/outline';

interface Props {
    site: {
        id: number;
        name: string;
        subdomain: string;
        logo: string | null;
        theme: { primaryColor?: string } | null;
    };
    settings: {
        navigation?: {
            logo?: string;
            logoText?: string;
        };
    } | null;
    user: {
        id: number;
        name: string;
        email: string;
        avatar?: string | null;
        role: {
            name: string;
            slug: string;
            level: number;
            type: 'staff' | 'client';
            icon?: string;
            color?: string;
        } | null;
        permissions: string[];
    };
    stats: {
        orders_count: number;
        orders_total: number;
        posts_count: number;
        users_count?: number;
        pending_orders?: number;
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
        views_count: number;
        created_at: string;
    }>;
}

const props = defineProps<Props>();

// Theme
const primaryColor = computed(() => props.site.theme?.primaryColor || '#2563eb');
const subdomain = computed(() => props.site.subdomain);

// Role helpers
const isAdmin = computed(() => props.user.role?.level >= 100);
const isStaff = computed(() => props.user.role?.type === 'staff');
const hasPermission = (permission: string) => isAdmin.value || props.user.permissions.includes(permission);

// Role badge config
const roleBadgeConfig = computed(() => {
    const color = props.user.role?.color || 'gray';
    const colorMap: Record<string, { bg: string; text: string }> = {
        indigo: { bg: 'bg-indigo-100', text: 'text-indigo-700' },
        purple: { bg: 'bg-purple-100', text: 'text-purple-700' },
        blue: { bg: 'bg-blue-100', text: 'text-blue-700' },
        cyan: { bg: 'bg-cyan-100', text: 'text-cyan-700' },
        emerald: { bg: 'bg-emerald-100', text: 'text-emerald-700' },
        yellow: { bg: 'bg-yellow-100', text: 'text-yellow-700' },
        amber: { bg: 'bg-amber-100', text: 'text-amber-700' },
        gray: { bg: 'bg-gray-100', text: 'text-gray-700' },
    };
    return colorMap[color] || colorMap.gray;
});

// Quick actions for header
const quickActions = computed(() => {
    const actions = [];
    if (isStaff.value) {
        if (hasPermission('posts.create')) {
            actions.push({ name: 'New Post', href: `/sites/${subdomain.value}/dashboard/posts/create`, icon: PencilSquareIcon, color: 'bg-blue-600 hover:bg-blue-700' });
        }
        if (hasPermission('orders.view')) {
            actions.push({ name: 'View Orders', href: `/sites/${subdomain.value}/dashboard/orders`, icon: InboxIcon, color: 'bg-purple-600 hover:bg-purple-700' });
        }
    }
    actions.push({ name: 'View Site', href: `/sites/${subdomain.value}`, icon: EyeIcon, color: 'bg-emerald-600 hover:bg-emerald-700', external: true });
    return actions;
});

// Formatters
const formatCurrency = (amount: number) => new Intl.NumberFormat('en-ZM', { style: 'currency', currency: 'ZMW', minimumFractionDigits: 0 }).format(amount / 100);
const formatRelativeTime = (date: string) => {
    const diff = Math.floor((Date.now() - new Date(date).getTime()) / 1000);
    if (diff < 60) return 'Just now';
    if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
    return new Date(date).toLocaleDateString('en-ZM', { month: 'short', day: 'numeric' });
};

const getStatusConfig = (status: string) => {
    const configs: Record<string, { bg: string; text: string; dot: string }> = {
        pending: { bg: 'bg-amber-50', text: 'text-amber-700', dot: 'bg-amber-500' },
        paid: { bg: 'bg-emerald-50', text: 'text-emerald-700', dot: 'bg-emerald-500' },
        completed: { bg: 'bg-emerald-50', text: 'text-emerald-700', dot: 'bg-emerald-500' },
        cancelled: { bg: 'bg-red-50', text: 'text-red-700', dot: 'bg-red-500' },
        draft: { bg: 'bg-gray-50', text: 'text-gray-700', dot: 'bg-gray-400' },
        published: { bg: 'bg-emerald-50', text: 'text-emerald-700', dot: 'bg-emerald-500' },
    };
    return configs[status] || { bg: 'bg-gray-50', text: 'text-gray-600', dot: 'bg-gray-400' };
};
</script>

<template>
    <Head :title="`Dashboard - ${site.name}`" />

    <SiteMemberLayout :site="site" :settings="settings" :user="user" title="Dashboard">
        <!-- Header Actions -->
        <template #header-actions>
            <a v-for="action in quickActions" :key="action.name" :href="action.href" :target="action.external ? '_blank' : undefined"
                class="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-white text-sm font-medium transition-colors"
                :class="action.color">
                <component :is="action.icon" class="w-4 h-4" aria-hidden="true" />
                <span class="hidden md:inline">{{ action.name }}</span>
            </a>
        </template>

        <!-- Welcome Section -->
        <div class="mb-8">
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl font-bold text-gray-900">Welcome back, {{ user.name.split(' ')[0] }}!</h1>
                <span v-if="user.role" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium" :class="[roleBadgeConfig.bg, roleBadgeConfig.text]">
                    <ShieldCheckIcon v-if="isAdmin" class="w-3.5 h-3.5" aria-hidden="true" />
                    <BriefcaseIcon v-else-if="isStaff" class="w-3.5 h-3.5" aria-hidden="true" />
                    <UserIcon v-else class="w-3.5 h-3.5" aria-hidden="true" />
                    {{ user.role.name }}
                </span>
            </div>
            <p class="text-gray-500 mt-1">
                <template v-if="isStaff">Here's your team dashboard overview.</template>
                <template v-else>Here's what's happening with your account.</template>
            </p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Staff Stats -->
            <template v-if="isStaff">
                <div v-if="hasPermission('orders.view')" class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Total Orders</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.orders_count }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center">
                            <InboxIcon class="w-6 h-6 text-purple-600" aria-hidden="true" />
                        </div>
                    </div>
                </div>

                <div v-if="hasPermission('orders.view')" class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Revenue</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatCurrency(stats.orders_total || 0) }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
                            <CurrencyDollarIcon class="w-6 h-6 text-emerald-600" aria-hidden="true" />
                        </div>
                    </div>
                </div>

                <div v-if="hasPermission('posts.view')" class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Posts</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.posts_count }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                            <DocumentTextIcon class="w-6 h-6 text-blue-600" aria-hidden="true" />
                        </div>
                    </div>
                </div>

                <div v-if="hasPermission('users.view')" class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Users</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.users_count || 0 }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center">
                            <UsersIcon class="w-6 h-6 text-amber-600" aria-hidden="true" />
                        </div>
                    </div>
                </div>
            </template>

            <!-- Client Stats -->
            <template v-else>
                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">My Orders</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.orders_count }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center" :style="{ backgroundColor: primaryColor + '15' }">
                            <ShoppingBagIcon class="w-6 h-6" :style="{ color: primaryColor }" aria-hidden="true" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Total Spent</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatCurrency(stats.orders_total || 0) }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
                            <CurrencyDollarIcon class="w-6 h-6 text-emerald-600" aria-hidden="true" />
                        </div>
                    </div>
                </div>

                <div v-if="user.role?.slug === 'vip'" class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm col-span-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">VIP Status</p>
                            <p class="text-lg font-bold text-yellow-600 mt-1 flex items-center gap-2">
                                <StarIcon class="w-5 h-5" aria-hidden="true" />
                                Premium Member
                            </p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-yellow-100 flex items-center justify-center">
                            <StarIcon class="w-6 h-6 text-yellow-600" aria-hidden="true" />
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Staff Alert - Pending Orders -->
        <div v-if="isStaff && stats.pending_orders" class="mb-8 bg-gradient-to-r from-amber-500 to-orange-500 rounded-xl p-5 text-white">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
                        <ClockIcon class="w-6 h-6" aria-hidden="true" />
                    </div>
                    <div>
                        <p class="font-semibold">{{ stats.pending_orders }} Pending Orders</p>
                        <p class="text-white/80 text-sm">Orders waiting for processing</p>
                    </div>
                </div>
                <Link :href="`/sites/${subdomain}/dashboard/orders?status=pending`" class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-colors">
                    Review Now →
                </Link>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="grid lg:grid-cols-2 gap-6">
            <!-- Recent Orders -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Recent Orders</h2>
                    <Link :href="`/sites/${subdomain}/dashboard/orders`" class="text-sm font-medium" :style="{ color: primaryColor }">View all</Link>
                </div>
                <div v-if="recentOrders.length > 0" class="divide-y divide-gray-100">
                    <div v-for="order in recentOrders.slice(0, 5)" :key="order.id" class="px-5 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div>
                            <p class="font-medium text-gray-900">{{ order.order_number }}</p>
                            <p class="text-sm text-gray-500">{{ formatRelativeTime(order.created_at) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-900">{{ formatCurrency(order.total) }}</p>
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 text-xs font-medium rounded-full capitalize" :class="[getStatusConfig(order.status).bg, getStatusConfig(order.status).text]">
                                <span class="w-1.5 h-1.5 rounded-full" :class="getStatusConfig(order.status).dot"></span>
                                {{ order.status }}
                            </span>
                        </div>
                    </div>
                </div>
                <div v-else class="px-5 py-12 text-center">
                    <ShoppingBagIcon class="w-12 h-12 mx-auto text-gray-300 mb-3" aria-hidden="true" />
                    <p class="text-gray-500 mb-2">No orders yet</p>
                    <a :href="`/sites/${subdomain}`" class="text-sm font-medium" :style="{ color: primaryColor }">Browse products →</a>
                </div>
            </div>

            <!-- Recent Posts or Quick Links -->
            <div v-if="hasPermission('posts.view')" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Recent Posts</h2>
                    <Link :href="`/sites/${subdomain}/dashboard/posts`" class="text-sm font-medium" :style="{ color: primaryColor }">View all</Link>
                </div>
                <div v-if="recentPosts.length > 0" class="divide-y divide-gray-100">
                    <div v-for="post in recentPosts.slice(0, 5)" :key="post.id" class="px-5 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div class="min-w-0 flex-1 mr-4">
                            <p class="font-medium text-gray-900 truncate">{{ post.title }}</p>
                            <p class="text-sm text-gray-500">{{ formatRelativeTime(post.created_at) }} · {{ post.views_count || 0 }} views</p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 text-xs font-medium rounded-full capitalize flex-shrink-0" :class="[getStatusConfig(post.status).bg, getStatusConfig(post.status).text]">
                            <span class="w-1.5 h-1.5 rounded-full" :class="getStatusConfig(post.status).dot"></span>
                            {{ post.status }}
                        </span>
                    </div>
                </div>
                <div v-else class="px-5 py-12 text-center">
                    <DocumentTextIcon class="w-12 h-12 mx-auto text-gray-300 mb-3" aria-hidden="true" />
                    <p class="text-gray-500 mb-2">No posts yet</p>
                    <Link v-if="hasPermission('posts.create')" :href="`/sites/${subdomain}/dashboard/posts/create`" class="inline-flex items-center gap-1 text-sm font-medium" :style="{ color: primaryColor }">
                        <PlusIcon class="w-4 h-4" aria-hidden="true" />
                        Create your first post
                    </Link>
                </div>
            </div>

            <!-- Quick Links for non-editors -->
            <div v-else class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Quick Links</h2>
                </div>
                <div class="p-4 space-y-2">
                    <Link :href="`/sites/${subdomain}/dashboard/profile`" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                            <UserIcon class="w-5 h-5 text-blue-600" aria-hidden="true" />
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">Edit Profile</p>
                            <p class="text-sm text-gray-500">Update your information</p>
                        </div>
                        <ChevronRightIcon class="w-5 h-5 text-gray-400" aria-hidden="true" />
                    </Link>
                    <a :href="`/sites/${subdomain}`" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                            <EyeIcon class="w-5 h-5 text-emerald-600" aria-hidden="true" />
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">Visit Site</p>
                            <p class="text-sm text-gray-500">Browse products & content</p>
                        </div>
                        <ChevronRightIcon class="w-5 h-5 text-gray-400" aria-hidden="true" />
                    </a>
                </div>
            </div>
        </div>
    </SiteMemberLayout>
</template>
