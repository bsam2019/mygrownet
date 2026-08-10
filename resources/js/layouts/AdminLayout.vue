<script setup lang="ts">
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';

interface Props {
    title?: string;
}

withDefaults(defineProps<Props>(), {
    title: 'Platform Admin Dashboard',
});

const page = usePage();
const user = computed(() => (page.props as any).auth?.user ?? null);

const currentHost = typeof window !== 'undefined' ? window.location.hostname : '';
const mainDomain = currentHost.endsWith('.mygrownet.com') || currentHost === 'mygrownet.com'
    ? 'https://mygrownet.com'
    : (typeof window !== 'undefined' ? window.location.origin : '');

const platformNav = [
    { label: 'Platform Hub', href: '/admin/dashboard', icon: 'dashboard' },
    { label: 'Users & Identity', href: '/admin/users', icon: 'people' },
    { label: 'App Catalog', href: '/admin/applications', icon: 'apps' },
    { label: 'Subscriptions', href: '/admin/module-subscriptions', icon: 'loyalty' },
    { label: 'Receipts', href: '/admin/receipts', icon: 'receipt_long' },
    { label: 'Withdrawals', href: '/admin/withdrawals', icon: 'account_balance_wallet' },
];

const appDomainHubs = [
    { name: 'StockFlow (POS & Inventory)', url: '/stock-audit/admin', category: 'Business' },
    { name: 'BMS (Construction & HR)', url: '/bms/admin', category: 'Business' },
    { name: 'GrowBuilder (Sites & AI)', url: '/growbuilder/admin', category: 'Business' },
    { name: 'GrowFinance (Ledger & Cash)', url: '/growfinance/admin', category: 'Business' },
    { name: 'BizDocs (Profiles & Docs)', url: '/bizdocs/admin', category: 'Business' },
    { name: 'Employee Portal & HR', url: '/employee/delegated', category: 'Business' },
    { name: 'Venture Builder (Equity & BGF)', url: '/venture/admin', category: 'Capital' },
    { name: 'Investor Portal (Dividends)', url: '/investor/admin', category: 'Capital' },
    { name: 'GrowMusic (Catalog & Royalties)', url: '/growmusic/admin', category: 'Consumer' },
    { name: 'GrowStream (Video & Creators)', url: '/growstream/admin', category: 'Consumer' },
    { name: 'Marketplace (Sellers & Orders)', url: '/admin/marketplace', category: 'Consumer' },
    { name: 'QuickInvoice (Billing)', url: '/admin/quick-invoice', category: 'Business' },
    { name: 'BizBoost Suite (Marketing)', url: '/bizboost/admin', category: 'Legacy' },
];

// Responsive Mobile Sidebar Drawer Toggle
const mobileSidebarOpen = ref(false);
const toggleMobileSidebar = () => { mobileSidebarOpen.value = !mobileSidebarOpen.value; };
const closeMobileSidebar = () => { mobileSidebarOpen.value = false; };

// Interactive Dropdown Menus
const userMenuOpen = ref(false);
const appsMenuOpen = ref(false);
const userMenuRef = ref<HTMLElement>();
const appsMenuRef = ref<HTMLElement>();

const toggleUserMenu = () => { userMenuOpen.value = !userMenuOpen.value; appsMenuOpen.value = false; };
const toggleAppsMenu = () => { appsMenuOpen.value = !appsMenuOpen.value; userMenuOpen.value = false; };

const handleClickOutside = (event: MouseEvent) => {
    if (userMenuRef.value && !userMenuRef.value.contains(event.target as Node)) {
        userMenuOpen.value = false;
    }
    if (appsMenuRef.value && !appsMenuRef.value.contains(event.target as Node)) {
        appsMenuOpen.value = false;
    }
};

onMounted(() => {
    if (typeof document !== 'undefined') {
        document.addEventListener('click', handleClickOutside);
    }
});

onUnmounted(() => {
    if (typeof document !== 'undefined') {
        document.removeEventListener('click', handleClickOutside);
    }
});

const logout = () => {
    userMenuOpen.value = false;
    router.post(route('logout'));
};
</script>

<template>
    <Head :title="title" />

    <div class="min-h-screen bg-gray-100 text-gray-900 font-sans">
        <!-- Top Navigation Header -->
        <header class="sticky top-0 z-40 bg-slate-900 text-white shadow-lg border-b border-slate-800">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                
                <!-- Left: Logo & Core Navigation -->
                <div class="flex items-center gap-6">
                    <button
                        @click="toggleMobileSidebar"
                        class="md:hidden text-gray-300 p-2 rounded-lg hover:bg-slate-800 focus:outline-none"
                    >
                        <span class="material-symbols-outlined text-2xl">{{ mobileSidebarOpen ? 'close' : 'menu' }}</span>
                    </button>

                    <!-- Brand Logo -->
                    <Link href="/admin/dashboard" class="flex items-center gap-2.5">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-500 text-white shadow-md">
                            <span class="material-symbols-outlined text-2xl">admin_panel_settings</span>
                        </div>
                        <div>
                            <span class="text-lg font-bold tracking-tight text-white">
                                MyGrow<span class="text-blue-400">Net</span>
                            </span>
                            <span class="ml-2 rounded-full bg-blue-500/20 border border-blue-400/30 px-2.5 py-0.5 text-xs font-semibold text-blue-300">
                                Command Center
                            </span>
                        </div>
                    </Link>

                    <!-- Desktop Platform Nav Links -->
                    <nav class="hidden md:flex items-center gap-1 border-l border-slate-800 pl-6">
                        <Link
                            v-for="item in platformNav"
                            :key="item.label"
                            :href="item.href"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-slate-300 hover:text-white hover:bg-slate-800 transition-colors"
                        >
                            <span class="material-symbols-outlined text-base">{{ item.icon }}</span>
                            {{ item.label }}
                        </Link>
                    </nav>
                </div>

                <!-- Right Cluster: Domain Apps Launcher + Profile Dropdown -->
                <div class="flex items-center gap-3">
                    
                    <!-- Domain App Portals Dropdown -->
                    <div ref="appsMenuRef" class="relative">
                        <button
                            @click.stop="toggleAppsMenu"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-lg border border-slate-700 bg-slate-800/80 text-xs font-semibold text-slate-200 hover:bg-slate-700 hover:text-white transition-all"
                        >
                            <span class="material-symbols-outlined text-base text-blue-400">apps</span>
                            App Dashboards
                            <span class="material-symbols-outlined text-sm">expand_more</span>
                        </button>

                        <div
                            v-if="appsMenuOpen"
                            class="absolute right-0 mt-2 w-72 rounded-xl bg-white text-gray-900 shadow-2xl border border-gray-200 py-2 z-50 animate-in fade-in slide-in-from-top-2 duration-150"
                        >
                            <div class="px-4 py-2 border-b border-gray-100 font-semibold text-xs text-gray-500 uppercase tracking-wider">
                                Domain Admin Portals
                            </div>
                            <div class="max-h-80 overflow-y-auto py-1">
                                <a
                                    v-for="app in appDomainHubs"
                                    :key="app.name"
                                    :href="app.url"
                                    class="flex items-center justify-between px-4 py-2 text-xs hover:bg-blue-50 text-gray-800 hover:text-blue-700 transition-colors group"
                                >
                                    <span class="font-medium">{{ app.name }}</span>
                                    <span class="material-symbols-outlined text-sm text-gray-400 group-hover:text-blue-600">arrow_forward</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Workspace Link -->
                    <a
                        :href="`${mainDomain}/workspace`"
                        class="hidden sm:inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-slate-300 hover:text-white hover:bg-slate-800 transition-colors"
                    >
                        <span class="material-symbols-outlined text-base">dashboard_customize</span>
                        Workspace
                    </a>

                    <!-- User Profile Menu -->
                    <div ref="userMenuRef" class="relative">
                        <button
                            @click.stop="toggleUserMenu"
                            class="flex items-center gap-2 p-1 rounded-full hover:bg-slate-800 transition-colors"
                        >
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-sm font-bold text-white shadow">
                                {{ (user?.name || 'A').charAt(0).toUpperCase() }}
                            </div>
                        </button>

                        <div
                            v-if="userMenuOpen"
                            class="absolute right-0 mt-2 w-56 rounded-xl bg-white text-gray-900 shadow-2xl border border-gray-200 py-2 z-50"
                        >
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-xs font-bold text-gray-900">{{ user?.name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ user?.email }}</p>
                            </div>
                            <a :href="`${mainDomain}/workspace`" class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-50">
                                Platform Workspace
                            </a>
                            <button
                                @click="logout"
                                class="w-full text-left px-4 py-2 text-xs text-red-600 hover:bg-red-50 font-medium"
                            >
                                Log Out
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="py-6">
            <slot />
        </main>
    </div>
</template>
