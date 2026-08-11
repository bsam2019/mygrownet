<template>
    <AdminLayout title="GrowBuilder Admin Control Center">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-200 pb-5">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-gray-900 flex items-center gap-3">
                        GrowBuilder Platform Control Center
                        <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 border border-indigo-200">
                            <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                            Sites & AI Builder
                        </span>
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">Aggregate administrative control across all GrowBuilder tenant sites, SSG deployments, AI usage, and agencies.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="/admin/dashboard"
                        class="px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold shadow-xs transition-colors flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-base">arrow_back</span>
                        Back to Command Center
                    </a>
                </div>
            </div>

            <!-- Top Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Tenant Sites</p>
                        <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ formatNumber(stats?.total_sites || 0) }}</p>
                        <p class="text-xs text-indigo-600 font-medium mt-1">+{{ stats?.new_sites_this_month || 0 }} new this month</p>
                    </div>
                    <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl">
                        <span class="material-symbols-outlined text-2xl">web</span>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Active Published Sites</p>
                        <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ formatNumber(stats?.active_sites || 0) }}</p>
                        <p class="text-xs text-emerald-600 font-medium mt-1">{{ stats?.custom_domains || 0 }} custom domains</p>
                    </div>
                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                        <span class="material-symbols-outlined text-2xl">language</span>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">SSG Deployments</p>
                        <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ formatNumber(stats?.ssg_deployments_month || 0) }}</p>
                        <p class="text-xs text-purple-600 font-medium mt-1">{{ stats?.ssg_enabled_sites || 0 }} SSG enabled</p>
                    </div>
                    <div class="p-3 bg-purple-50 text-purple-600 rounded-xl">
                        <span class="material-symbols-outlined text-2xl">rocket_launch</span>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">AI Generation Tokens</p>
                        <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ formatNumber(stats?.ai_usage_this_month || 0) }}</p>
                        <p class="text-xs text-amber-600 font-medium mt-1">Tokens this month</p>
                    </div>
                    <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
                        <span class="material-symbols-outlined text-2xl">auto_awesome</span>
                    </div>
                </div>
            </div>

            <!-- GrowBuilder Quick Management Hub Navigation -->
            <div class="bg-gradient-to-r from-slate-900 to-indigo-950 rounded-2xl p-5 text-white shadow-md flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold flex items-center gap-2">
                        <span class="material-symbols-outlined text-indigo-400">admin_panel_settings</span>
                        GrowBuilder Administrative Quick Hub
                    </h2>
                    <p class="text-xs text-slate-300 mt-0.5">Manage agency accounts, client portfolios, billing services, and subscription plans directly.</p>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="/growbuilder/agency/dashboard" class="px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold transition-colors flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">business</span>
                        Agency Hub
                    </a>
                    <a href="/growbuilder/clients" class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold transition-colors flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">group</span>
                        Clients
                    </a>
                    <a href="/growbuilder/services" class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold transition-colors flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">design_services</span>
                        Services
                    </a>
                    <a href="/growbuilder/invoices" class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold transition-colors flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">receipt_long</span>
                        Invoices
                    </a>
                    <a href="/admin/module-subscriptions" class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold transition-colors flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">loyalty</span>
                        Pricing Tiers
                    </a>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Interactive Sites Management Directory (2 cols) -->
                <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-3">
                        <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                            <span class="material-symbols-outlined text-indigo-600">web</span>
                            Tenant Sites Management Directory
                        </h3>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none">search</span>
                            <input
                                v-model="siteSearchQuery"
                                type="text"
                                placeholder="Filter sites…"
                                class="pl-8 pr-3 py-1.5 text-xs border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 w-48 bg-white"
                            />
                        </div>
                    </div>

                    <div class="divide-y divide-gray-50">
                        <template v-if="filteredSites && filteredSites.length > 0">
                            <div v-for="site in filteredSites" :key="site.id" class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-bold text-sm text-gray-900 truncate">{{ site.name }}</span>
                                        <span :class="[
                                            'px-2 py-0.5 rounded text-[10px] font-semibold capitalize',
                                            site.status === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'
                                        ]">
                                            {{ site.status }}
                                        </span>
                                        <span v-if="site.ssg_enabled" class="px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-50 text-purple-700">
                                            SSG
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 truncate mt-0.5">
                                        {{ site.subdomain }}.mygrownet.com
                                        <span v-if="site.custom_domain" class="text-indigo-600 font-medium">({{ site.custom_domain }})</span>
                                        <span class="text-gray-400 ml-2">• {{ site.user_email }}</span>
                                    </p>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <button
                                        @click="triggerSSG(site.id)"
                                        title="Trigger manual SSG rebuild"
                                        class="px-2.5 py-1 rounded-lg border border-purple-200 bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-medium transition-colors flex items-center gap-1"
                                    >
                                        <span class="material-symbols-outlined text-xs">rocket</span>
                                        Build SSG
                                    </button>
                                    <button
                                        @click="toggleStatus(site.id)"
                                        :class="[
                                            'px-2.5 py-1 rounded-lg border text-xs font-medium transition-colors flex items-center gap-1',
                                            site.status === 'published'
                                                ? 'border-amber-200 bg-amber-50 hover:bg-amber-100 text-amber-800'
                                                : 'border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-emerald-800'
                                        ]"
                                    >
                                        <span class="material-symbols-outlined text-xs">{{ site.status === 'published' ? 'block' : 'check_circle' }}</span>
                                        {{ site.status === 'published' ? 'Suspend' : 'Publish' }}
                                    </button>
                                </div>
                            </div>
                        </template>

                        <div v-else class="py-10 text-center text-gray-400 text-xs">
                            No sites match "{{ siteSearchQuery }}".
                        </div>
                    </div>
                </div>

                <!-- Right Column: Compliance & Deployments -->
                <div class="space-y-6">

                    <!-- Compliance & Business Profile Summary -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                                <span class="material-symbols-outlined text-emerald-600">verified</span>
                                Compliance & Tax Records
                            </h3>
                        </div>
                        <div class="space-y-3 text-xs">
                            <div class="flex justify-between py-1 border-b border-gray-50">
                                <span class="text-gray-500">Business Profiles Registered:</span>
                                <span class="font-bold text-gray-900">{{ stats?.business_profiles || 0 }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-gray-50">
                                <span class="text-gray-500">TPIN Registered Businesses:</span>
                                <span class="font-bold text-emerald-600">{{ stats?.profiles_with_tpin || 0 }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-gray-50">
                                <span class="text-gray-500">QR Codes Generated:</span>
                                <span class="font-bold text-gray-900">{{ stats?.qr_codes_total || 0 }}</span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span class="text-gray-500">QR Code Scan Count:</span>
                                <span class="font-bold text-indigo-600">{{ stats?.qr_scans_total || 0 }} scans</span>
                            </div>
                        </div>
                    </div>

                    <!-- SSG Deployments -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                                <span class="material-symbols-outlined text-purple-600">cloud_upload</span>
                                Recent SSG Deployments
                            </h3>
                        </div>
                        <div class="space-y-3">
                            <template v-if="recentDeployments && recentDeployments.length > 0">
                                <div v-for="dep in recentDeployments" :key="dep.id" class="flex items-center justify-between text-xs">
                                    <div>
                                        <div class="font-semibold text-gray-900">{{ dep.site_name || dep.subdomain }}</div>
                                        <div class="text-[10px] text-gray-400">{{ dep.deployed_at }}</div>
                                    </div>
                                    <span :class="[
                                        'px-2 py-0.5 rounded text-[10px] font-bold capitalize',
                                        dep.status === 'deployed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'
                                    ]">
                                        {{ dep.status }}
                                    </span>
                                </div>
                            </template>
                            <div v-else class="text-xs text-gray-400 text-center py-4">
                                No recent deployments.
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps<{
    stats?: any;
    topSites?: any[];
    recentDeployments?: any[];
    recentActivity?: any[];
}>();

const siteSearchQuery = ref('');

const filteredSites = computed(() => {
    let sites = props.topSites ?? [];
    if (siteSearchQuery.value.trim()) {
        const q = siteSearchQuery.value.toLowerCase();
        sites = sites.filter(s =>
            s.name?.toLowerCase().includes(q) ||
            s.subdomain?.toLowerCase().includes(q) ||
            s.user_email?.toLowerCase().includes(q) ||
            s.business_name?.toLowerCase().includes(q)
        );
    }
    return sites;
});

const toggleStatus = (id: number) => {
    router.post(route('growbuilder.admin.sites.toggle-status', id), {}, {
        preserveScroll: true
    });
};

const triggerSSG = (id: number) => {
    router.post(route('growbuilder.admin.sites.trigger-ssg', id), {}, {
        preserveScroll: true
    });
};

const formatNumber = (num: number) => new Intl.NumberFormat().format(Math.round(num || 0));
</script>
