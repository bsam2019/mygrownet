<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, ShoppingBag, Package, Folder, HardDrive, Building2, DollarSign, Ticket, Star, ChevronDown } from 'lucide-vue-next';

const page = usePage();
const isCollapsed = ref(false);

const navItems = [
    { title: 'Dashboard', href: '/admin/growmart', icon: LayoutGrid },
    { title: 'Orders', href: '/admin/growmart/orders', icon: ShoppingBag },
    { title: 'Payments', href: '/admin/growmart/orders?payment_status=pending_verification', icon: DollarSign },
    { title: 'Products', href: '/admin/growmart/products', icon: Package },
    { title: 'Categories', href: '/admin/growmart/categories', icon: Folder },
    { title: 'Reviews', href: '/admin/growmart/reviews', icon: Star },
    { title: 'Coupons', href: '/admin/growmart/coupons', icon: Ticket },
    { title: 'Inventory', href: '/admin/growmart/inventory', icon: HardDrive },
    { title: 'Warehouses', href: '/admin/growmart/warehouses', icon: Building2 },
];

const toggleSidebar = () => {
    isCollapsed.value = !isCollapsed.value;
    localStorage.setItem('growmart.sidebarCollapsed', isCollapsed.value.toString());
};

const isUrlActive = (url: string) => {
    const current = page.url.split('?')[0];
    const target = url.split('?')[0];
    if (current === target) return true;
    if (target !== '/' && current.startsWith(target)) return true;
    return false;
};

onMounted(() => {
    const saved = localStorage.getItem('growmart.sidebarCollapsed');
    if (saved !== null) isCollapsed.value = saved === 'true';
});
</script>

<template>
    <aside :class="[
        'fixed inset-y-0 left-0 z-50 flex flex-col transition-all duration-300 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800',
        isCollapsed ? 'w-16' : 'w-64'
    ]">
        <div class="flex h-16 items-center justify-between px-4 border-b border-gray-200 dark:border-gray-800">
            <Link href="/admin/growmart" class="flex items-center gap-2" v-show="!isCollapsed">
                <div class="p-1.5 bg-emerald-600 rounded-lg">
                    <ShoppingBag class="h-5 w-5 text-white" />
                </div>
                <span class="text-lg font-bold text-gray-900">GrowMart</span>
            </Link>
            <button @click="toggleSidebar" :class="['p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800', isCollapsed ? 'mx-auto' : '']">
                <ChevronDown :class="['h-5 w-5 text-gray-500 transition-transform', isCollapsed ? 'rotate-90' : '-rotate-90']" />
            </button>
        </div>

        <nav class="flex-1 py-4 overflow-y-auto">
            <Link
                v-for="item in navItems"
                :key="item.title"
                :href="item.href"
                :class="[
                    'flex items-center px-4 py-3 mx-2 rounded-lg transition-colors text-sm',
                    isUrlActive(item.href)
                        ? 'bg-emerald-50 text-emerald-700 font-medium'
                        : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'
                ]"
            >
                <component :is="item.icon" class="h-5 w-5 shrink-0" />
                <span v-show="!isCollapsed" class="ml-3">{{ item.title }}</span>
            </Link>
        </nav>

        <div v-show="!isCollapsed" class="p-4 border-t border-gray-200 dark:border-gray-800">
            <Link href="/growmart" class="flex items-center gap-2 text-sm text-gray-500 hover:text-emerald-600 transition-colors">
                <ShoppingBag class="h-4 w-4" />
                <span>Visit Storefront</span>
            </Link>
        </div>
    </aside>
</template>
