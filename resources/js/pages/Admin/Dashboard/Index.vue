<template>
    <AdminLayout title="Platform Command Center">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-200 pb-5">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-gray-900 flex items-center gap-3">
                        Ecosystem Command Center
                        <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            System {{ platformOverview?.system_status || 'Operational' }}
                        </span>
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">Platform-wide control, application provisioning, and domain module status.</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-medium text-gray-500">{{ currentDate }}</span>
                    <a href="/admin/module-subscriptions"
                        class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-sm transition-colors flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-base">apps</span>
                        Manage Catalog
                    </a>
                </div>
            </div>

            <!-- Top Level Platform KPI Tiles -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Platform Users</p>
                        <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ formatNumber(platformOverview?.total_users || 0) }}</p>
                        <p class="text-xs text-blue-600 font-medium mt-1">+{{ memberMetrics?.new_this_month || 0 }} new this month</p>
                    </div>
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                        <span class="material-symbols-outlined text-2xl">groups</span>
                    </div>
                </div>
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Active Organizations</p>
                        <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ formatNumber(platformOverview?.total_organizations || 0) }}</p>
                        <p class="text-xs text-emerald-600 font-medium mt-1">Org Tenants</p>
                    </div>
                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                        <span class="material-symbols-outlined text-2xl">corporate_fare</span>
                    </div>
                </div>
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Connected Applications</p>
                        <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ platformOverview?.active_applications || 16 }}</p>
                        <p class="text-xs text-purple-600 font-medium mt-1">Active Modules</p>
                    </div>
                    <div class="p-3 bg-purple-50 text-purple-600 rounded-xl">
                        <span class="material-symbols-outlined text-2xl">widgets</span>
                    </div>
                </div>
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Monthly Revenue</p>
                        <p class="text-2xl font-extrabold text-gray-900 mt-1">K{{ formatNumber(platformOverview?.monthly_revenue || 0) }}</p>
                        <p class="text-xs text-amber-600 font-medium mt-1">Subscribed Packages</p>
                    </div>
                    <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
                        <span class="material-symbols-outlined text-2xl">payments</span>
                    </div>
                </div>
            </div>

            <!-- Application Directory -->
            <div class="space-y-4">
                <!-- Header + Search + Category Tabs -->
                <div class="flex flex-col gap-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                <span class="material-symbols-outlined text-blue-600">apps</span>
                                Modular Application Directory
                            </h2>
                            <p class="text-xs text-gray-500 mt-0.5">Single launching authority for platform application admin contexts.</p>
                        </div>
                        <!-- Search -->
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-base pointer-events-none">search</span>
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search modules…"
                                class="pl-9 pr-4 py-2 text-xs border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 w-64 bg-white shadow-sm"
                            />
                        </div>
                    </div>

                    <!-- Legend & Category Filter Tabs -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-200">
                        <div class="flex items-center gap-1 -mb-px">
                            <button
                                v-for="cat in categories"
                                :key="cat.key"
                                @click="activeCategory = cat.key"
                                :class="[
                                    'px-4 py-2.5 text-xs font-semibold border-b-2 transition-colors flex items-center gap-1.5',
                                    activeCategory === cat.key
                                        ? 'border-blue-600 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                ]"
                            >
                                <span class="material-symbols-outlined text-sm">{{ cat.icon }}</span>
                                {{ cat.label }}
                                <span :class="[
                                    'px-1.5 py-0.5 rounded-full text-[10px] font-bold',
                                    activeCategory === cat.key ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-400'
                                ]">{{ getCategoryCount(cat.key) }}</span>
                            </button>
                        </div>
                        <div class="text-[11px] text-gray-400 pb-2 sm:pb-0 flex items-center gap-3">
                            <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Healthy</span>
                            <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-400"></span> Action Required</span>
                            <span class="inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Own Auth = Independent Login</span>
                        </div>
                    </div>
                </div>

                <!-- App List — Active panels -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <template v-if="activeApps.length > 0">
                        <div
                            v-for="(app, index) in activeApps"
                            :key="app.slug"
                            :class="[
                                'flex items-center gap-4 px-5 py-3.5 transition-colors hover:bg-slate-50/80',
                                index < activeApps.length - 1 ? 'border-b border-gray-100' : ''
                            ]"
                        >
                            <!-- App Icon -->
                            <div :class="['flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center shadow-xs', getAppMeta(app.slug).bgClass]">
                                <span :class="['material-symbols-outlined text-xl', getAppMeta(app.slug).textClass]">{{ getAppMeta(app.slug).icon }}</span>
                            </div>

                            <!-- Name + Description -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-sm font-bold text-gray-900">{{ app.name }}</span>
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded capitalize"
                                        :class="getCategoryBadgeClass(app.category)">
                                        {{ app.category }}
                                    </span>
                                    <!-- Tooltip / Legend for StockFlow Own Auth -->
                                    <span v-if="app.separate_auth"
                                        title="Authenticates independently via StockFlow domain credentials"
                                        class="text-[10px] font-semibold px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 cursor-help flex items-center gap-1">
                                        <span class="material-symbols-outlined text-xs">key</span>
                                        Own Auth
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 truncate mt-0.5">{{ app.description }}</p>
                            </div>

                            <!-- Combined Health & Adoption Status (Replaces 3 repetitive columns) -->
                            <div class="hidden sm:flex items-center gap-3 flex-shrink-0">
                                <div class="text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <span :class="['w-2 h-2 rounded-full flex-shrink-0', getStatusDotClass(app)]"></span>
                                        <span :class="['text-xs font-semibold capitalize', getStatusTextClass(app)]">
                                            {{ getStatusLabel(app) }}
                                        </span>
                                    </div>
                                    <div class="text-[10px] text-gray-400 mt-0.5">
                                        {{ app.installed_orgs > 0 ? `${app.installed_orgs} org installs` : '0 org installs (ramp-up)' }}
                                    </div>
                                </div>
                            </div>

                            <!-- Quieter Subdued Launch Button by Default -->
                            <div class="flex-shrink-0">
                                <a
                                    :href="app.admin_url"
                                    :class="[
                                        'inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all border shadow-xs',
                                        app.operational_status === 'maintenance'
                                            ? 'bg-amber-600 text-white border-amber-600 hover:bg-amber-700'
                                            : 'bg-white hover:bg-slate-100 text-slate-700 hover:text-blue-600 border-slate-200 hover:border-slate-300'
                                    ]"
                                >
                                    Open Admin
                                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                                </a>
                            </div>
                        </div>
                    </template>

                    <!-- Empty state -->
                    <div v-if="activeApps.length === 0" class="py-14 text-center">
                        <span class="material-symbols-outlined text-4xl text-gray-300">search_off</span>
                        <p class="text-sm text-gray-400 mt-2">No modules match "{{ searchQuery }}"</p>
                    </div>

                    <!-- Inactive / No Admin Panel section -->
                    <div v-if="inactiveApps.length > 0 && !searchQuery" class="border-t border-dashed border-gray-200">
                        <button
                            @click="showInactive = !showInactive"
                            class="w-full flex items-center justify-between px-5 py-3 text-xs text-gray-400 hover:bg-gray-50 transition-colors"
                        >
                            <span class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-sm">expand_{{ showInactive ? 'less' : 'more' }}</span>
                                {{ inactiveApps.length }} modules without a dedicated admin panel (unadopted / core only)
                            </span>
                            <span class="material-symbols-outlined text-sm">{{ showInactive ? 'keyboard_arrow_up' : 'keyboard_arrow_down' }}</span>
                        </button>
                        <template v-if="showInactive">
                            <div
                                v-for="(app, index) in inactiveApps"
                                :key="app.slug"
                                :class="[
                                    'flex items-center gap-4 px-5 py-3 bg-gray-50/60 opacity-70',
                                    index < inactiveApps.length - 1 ? 'border-b border-gray-100' : ''
                                ]"
                            >
                                <div :class="['flex-shrink-0 w-9 h-9 rounded-xl flex items-center justify-center', getAppMeta(app.slug).bgClass]">
                                    <span :class="['material-symbols-outlined text-lg', getAppMeta(app.slug).textClass]">{{ getAppMeta(app.slug).icon }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <span class="text-sm font-medium text-gray-600">{{ app.name }}</span>
                                    <p class="text-xs text-gray-400 truncate">{{ app.description }}</p>
                                </div>
                                <span class="text-xs text-gray-400 italic flex-shrink-0">No admin panel yet</span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Bottom Operations Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <!-- GrowNet MLM & Points -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                            <span class="material-symbols-outlined text-purple-600">stars</span>
                            GrowNet MLM & Points
                        </h3>
                        <a href="/admin/points" class="text-xs font-medium text-blue-600 hover:underline">Details</a>
                    </div>
                    <div class="space-y-2.5 text-xs">
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500">Points Awarded This Month:</span>
                            <span class="font-bold text-gray-900">{{ formatNumber((pointsMetrics?.this_month_lp || 0) + (pointsMetrics?.this_month_map || 0)) }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500">MAP Qualification Rate:</span>
                            <span class="font-bold text-purple-600">{{ pointsMetrics?.qualification_rate || 0 }}%</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-gray-500">Matrix Network Fill:</span>
                            <span class="font-bold text-gray-900">{{ matrixMetrics?.fill_rate || 0 }}%</span>
                        </div>
                    </div>
                </div>

                <!-- Support & Operations -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-600">support_agent</span>
                            Support & Operations
                        </h3>
                        <a href="/admin/support-tickets" class="text-xs font-medium text-blue-600 hover:underline">Manage</a>
                    </div>
                    <div class="space-y-2.5 text-xs">
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500">Total Support Tickets:</span>
                            <span class="font-bold text-gray-900">{{ supportData?.total_tickets || 0 }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500">Open / Pending:</span>
                            <span class="font-bold text-amber-600">{{ (supportData?.open_tickets || 0) + (supportData?.pending_tickets || 0) }}</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-gray-500">Telegram Bot Linked:</span>
                            <span class="font-bold text-emerald-600">{{ telegramMetrics?.total_linked || 0 }} members</span>
                        </div>
                    </div>
                </div>

                <!-- Platform Quick Actions -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-4">
                    <div class="border-b border-gray-100 pb-3">
                        <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                            <span class="material-symbols-outlined text-indigo-600">flash_on</span>
                            Platform Quick Actions
                        </h3>
                    </div>
                    <div class="space-y-2">
                        <a href="/admin/users" class="block w-full py-2 px-3 text-center rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 text-xs font-semibold transition-colors">
                            Manage Platform Users
                        </a>
                        <a href="/admin/receipts" class="block w-full py-2 px-3 text-center rounded-xl bg-slate-50 text-slate-700 hover:bg-slate-100 text-xs font-semibold transition-colors">
                            View Payment Receipts
                        </a>
                        <a href="/admin/withdrawals" class="block w-full py-2 px-3 text-center rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-semibold transition-colors">
                            Process Withdrawals
                        </a>
                        <a href="/admin/role-management/roles" class="block w-full py-2 px-3 text-center rounded-xl bg-purple-50 text-purple-700 hover:bg-purple-100 text-xs font-semibold transition-colors">
                            Manage Roles & Permissions
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps<{
    platformOverview?: any;
    appEcosystem?: any[];
    memberMetrics?: any;
    subscriptionMetrics?: any;
    starterKitMetrics?: any;
    pointsMetrics?: any;
    matrixMetrics?: any;
    financialMetrics?: any;
    workshopMetrics?: any;
    supportData?: any;
    emailMarketingMetrics?: any;
    telegramMetrics?: any;
    alerts?: any[];
}>();

// ── Directory State ──────────────────────────────────────────────
const searchQuery = ref('');
const activeCategory = ref('all');
const showInactive = ref(false);

const categories = [
    { key: 'all',           label: 'All Modules',    icon: 'grid_view' },
    { key: 'business',      label: 'Business',        icon: 'business_center' },
    { key: 'consumer',      label: 'Consumer',        icon: 'person' },
    { key: 'shared',        label: 'Infrastructure',  icon: 'hub' },
];

// ── App Icon / Color Meta ────────────────────────────────────────
const appMetaMap: Record<string, { icon: string; bgClass: string; textClass: string }> = {
    'bms':           { icon: 'construction',        bgClass: 'bg-orange-50',  textClass: 'text-orange-500' },
    'stockflow':     { icon: 'inventory_2',          bgClass: 'bg-violet-50',  textClass: 'text-violet-500' },
    'growfinance':   { icon: 'account_balance',      bgClass: 'bg-green-50',   textClass: 'text-green-600' },
    'grownet':       { icon: 'hub',                  bgClass: 'bg-blue-50',    textClass: 'text-blue-600' },
    'growbuilder':   { icon: 'web',                  bgClass: 'bg-indigo-50',  textClass: 'text-indigo-500' },
    'bizdocs':       { icon: 'description',          bgClass: 'bg-sky-50',     textClass: 'text-sky-500' },
    'bizboost':      { icon: 'rocket_launch',        bgClass: 'bg-pink-50',    textClass: 'text-pink-500' },
    'employee':      { icon: 'badge',                bgClass: 'bg-amber-50',   textClass: 'text-amber-600' },
    'venture':       { icon: 'trending_up',          bgClass: 'bg-emerald-50', textClass: 'text-emerald-600' },
    'investor':      { icon: 'payments',             bgClass: 'bg-teal-50',    textClass: 'text-teal-600' },
    'growstream':    { icon: 'play_circle',          bgClass: 'bg-red-50',     textClass: 'text-red-500' },
    'growmusic':     { icon: 'music_note',           bgClass: 'bg-purple-50',  textClass: 'text-purple-500' },
    'marketplace':   { icon: 'storefront',           bgClass: 'bg-orange-50',  textClass: 'text-orange-400' },
    'growmart':      { icon: 'shopping_bag',         bgClass: 'bg-orange-50',  textClass: 'text-orange-500' },
    'quick-invoice': { icon: 'receipt_long',         bgClass: 'bg-cyan-50',    textClass: 'text-cyan-600' },
    'lifeplus':      { icon: 'favorite',             bgClass: 'bg-rose-50',    textClass: 'text-rose-500' },
    'zamstay':       { icon: 'hotel',                bgClass: 'bg-blue-50',    textClass: 'text-blue-400' },
    'primeedge':     { icon: 'workspace_premium',    bgClass: 'bg-slate-50',   textClass: 'text-slate-500' },
    'growstorage':   { icon: 'cloud',                bgClass: 'bg-gray-100',   textClass: 'text-gray-500' },
};

const getAppMeta = (slug: string) =>
    appMetaMap[slug] ?? { icon: 'apps', bgClass: 'bg-gray-50', textClass: 'text-gray-400' };

// ── Filtering / Grouping ─────────────────────────────────────────
const baseApps = computed(() => {
    let apps = props.appEcosystem ?? [];
    if (activeCategory.value !== 'all') {
        apps = apps.filter(a => a.category === activeCategory.value);
    }
    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase();
        apps = apps.filter(a =>
            a.name?.toLowerCase().includes(q) ||
            a.description?.toLowerCase().includes(q) ||
            a.slug?.toLowerCase().includes(q)
        );
    }
    return apps;
});

const activeApps = computed(() => baseApps.value.filter(a => a.has_admin_panel));
const inactiveApps = computed(() => (props.appEcosystem ?? []).filter(a => !a.has_admin_panel));

const getCategoryCount = (key: string) => {
    const all = props.appEcosystem ?? [];
    if (key === 'all') return all.filter(a => a.has_admin_panel).length;
    return all.filter(a => a.category === key && a.has_admin_panel).length;
};

// ── Status Helpers ────────────────────────────────────────────────
const getStatusDotClass = (app: any) => {
    if (!app.is_active) return 'bg-gray-300';
    const s = app.operational_status;
    if (s === 'maintenance') return 'bg-amber-400';
    if (s === 'offline') return 'bg-red-400';
    return 'bg-emerald-500 animate-pulse';
};
const getStatusTextClass = (app: any) => {
    if (!app.is_active) return 'text-gray-400';
    const s = app.operational_status;
    if (s === 'maintenance') return 'text-amber-600';
    if (s === 'offline') return 'text-red-500';
    return 'text-emerald-600';
};
const getStatusLabel = (app: any) => {
    if (!app.is_active) return 'Inactive';
    const s = app.operational_status;
    if (s === 'maintenance') return 'Needs Setup';
    if (s === 'offline') return 'Offline';
    return 'Healthy';
};

// ── Category Badge ────────────────────────────────────────────────
const getCategoryBadgeClass = (cat: string) => {
    const map: Record<string, string> = {
        business:      'bg-blue-50 text-blue-600 border border-blue-100',
        consumer:      'bg-purple-50 text-purple-600 border border-purple-100',
        shared:        'bg-teal-50 text-teal-600 border border-teal-100',
        infrastructure:'bg-slate-100 text-slate-600 border border-slate-200',
    };
    return map[cat] ?? 'bg-gray-100 text-gray-500';
};

// ── Formatting ────────────────────────────────────────────────────
const currentDate = computed(() =>
    new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
);
const formatNumber = (num: number) => new Intl.NumberFormat().format(Math.round(num || 0));
</script>
