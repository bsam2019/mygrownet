<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import {
    LayoutDashboard,
    Store,
    Package,
    ShoppingBag,
    AlertTriangle,
    MessageSquare,
    FolderTree,
    BarChart3,
    Clock,
    Wallet,
} from 'lucide-vue-next';

interface Props {
    title?: string;
    stats?: {
        pending_sellers?: number;
        pending_products?: number;
        open_disputes?: number;
        total_reviews?: number;
        pending_payouts?: number;
    };
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Marketplace Admin',
    stats: () => ({}),
});

const page = usePage();
const currentUrl = computed(() => page.url);

const navItems = [
    { name: 'Dashboard', href: '/admin/marketplace', icon: LayoutDashboard },
    { name: 'Sellers', href: '/admin/marketplace/sellers', icon: Store, badge: 'pending_sellers' },
    { name: 'Products', href: '/admin/marketplace/products', icon: Package, badge: 'pending_products' },
    { name: 'Orders', href: '/admin/marketplace/orders', icon: ShoppingBag },
    { name: 'Payouts', href: '/admin/marketplace/payouts', icon: Wallet, badge: 'pending_payouts' },
    { name: 'Disputes', href: '/admin/marketplace/disputes', icon: AlertTriangle, badge: 'open_disputes' },
    { name: 'Reviews', href: '/admin/marketplace/reviews', icon: MessageSquare },
    { name: 'Categories', href: '/admin/marketplace/categories', icon: FolderTree },
    { name: 'Analytics', href: '/admin/marketplace/analytics', icon: BarChart3 },
];

const isActive = (href: string) => {
    if (href === '/admin/marketplace') {
        return currentUrl.value === href;
    }
    return currentUrl.value.startsWith(href);
};

const getBadgeCount = (badgeKey?: string) => {
    if (!badgeKey || !props.stats) return 0;
    return props.stats[badgeKey as keyof typeof props.stats] || 0;
};

const breadcrumbs = [
    { title: 'Admin', href: '/admin/dashboard' },
    { title: 'Marketplace', href: '/admin/marketplace' },
];
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Marketplace Header -->
            <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold">{{ title }}</h1>
                        <p class="text-orange-100 mt-1">Manage sellers, products, orders, and disputes</p>
                    </div>
                    
                    <!-- Quick Stats -->
                    <div class="hidden md:flex items-center gap-6">
                        <div v-if="stats.pending_sellers" class="text-center">
                            <div class="flex items-center gap-1">
                                <Clock class="h-4 w-4" />
                                <span class="text-2xl font-bold">{{ stats.pending_sellers }}</span>
                            </div>
                            <p class="text-xs text-orange-100">Pending Sellers</p>
                        </div>
                        <div v-if="stats.pending_products" class="text-center">
                            <div class="flex items-center gap-1">
                                <Package class="h-4 w-4" />
                                <span class="text-2xl font-bold">{{ stats.pending_products }}</span>
                            </div>
                            <p class="text-xs text-orange-100">Pending Products</p>
                        </div>
                        <div v-if="stats.pending_payouts" class="text-center">
                            <div class="flex items-center gap-1">
                                <Wallet class="h-4 w-4" />
                                <span class="text-2xl font-bold">{{ stats.pending_payouts }}</span>
                            </div>
                            <p class="text-xs text-orange-100">Pending Payouts</p>
                        </div>
                        <div v-if="stats.open_disputes" class="text-center">
                            <div class="flex items-center gap-1">
                                <AlertTriangle class="h-4 w-4" />
                                <span class="text-2xl font-bold">{{ stats.open_disputes }}</span>
                            </div>
                            <p class="text-xs text-orange-100">Open Disputes</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Tabs -->
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <nav class="flex overflow-x-auto">
                    <Link
                        v-for="item in navItems"
                        :key="item.name"
                        :href="item.href"
                        :class="[
                            'flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap border-b-2 transition-colors',
                            isActive(item.href)
                                ? 'border-orange-500 text-orange-600 bg-orange-50'
                                : 'border-transparent text-gray-600 hover:text-gray-900 hover:bg-gray-50'
                        ]"
                    >
                        <component :is="item.icon" class="h-4 w-4" />
                        {{ item.name }}
                        <span
                            v-if="item.badge && getBadgeCount(item.badge) > 0"
                            class="ml-1 px-1.5 py-0.5 text-xs rounded-full bg-orange-100 text-orange-700"
                        >
                            {{ getBadgeCount(item.badge) }}
                        </span>
                    </Link>
                </nav>
            </div>

            <!-- Page Content -->
            <div>
                <slot />
            </div>
        </div>
    </AdminLayout>
</template>
