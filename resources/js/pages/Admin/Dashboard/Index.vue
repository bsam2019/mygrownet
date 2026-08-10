<template>
    <AdminLayout title="Platform Command Center">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
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
                    <p class="text-sm text-gray-500 mt-1">
                        Global administrative control, application catalog, and domain module status.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-medium text-gray-500">{{ currentDate }}</span>
                    <a
                        href="/admin/applications"
                        class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm transition-colors flex items-center gap-1.5"
                    >
                        <span class="material-symbols-outlined text-base">apps</span>
                        Manage Catalog
                    </a>
                </div>
            </div>

            <!-- Top Level Platform Overview Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Platform Users</p>
                        <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ formatNumber(platformOverview?.total_users || 0) }}</p>
                        <p class="text-xs text-blue-600 font-medium mt-1">
                            +{{ memberMetrics?.new_this_month || 0 }} new this month
                        </p>
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

            <!-- Application Ecosystem Grid -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600">apps</span>
                        Modular Application Directory
                    </h2>
                    <span class="text-xs font-medium text-gray-500">
                        {{ appEcosystem?.length || 0 }} Applications Registered
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div
                        v-for="app in appEcosystem"
                        :key="app.slug"
                        class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-shadow flex flex-col justify-between"
                    >
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <div>
                                    <h3 class="text-base font-bold text-gray-900">{{ app.name }}</h3>
                                    <span class="text-xs text-gray-400 capitalize">{{ app.category }} Module</span>
                                </div>
                                <span
                                    :class="[
                                        'px-2.5 py-0.5 rounded-full text-xs font-semibold border',
                                        app.operational_status === 'online' || app.is_active
                                            ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                            : 'bg-amber-50 text-amber-700 border-amber-200'
                                    ]"
                                >
                                    {{ app.operational_status || 'Online' }}
                                </span>
                            </div>

                            <p class="text-xs text-gray-600 mb-4 line-clamp-2">
                                {{ app.description }}
                            </p>

                            <div class="flex items-center gap-4 text-xs text-gray-500 mb-5 border-t border-gray-50 pt-3">
                                <div>
                                    <span class="font-bold text-gray-900">{{ app.installed_orgs || 0 }}</span> Org Installs
                                </div>
                                <div>
                                    Lifecycle: <span class="capitalize font-medium text-gray-700">{{ app.lifecycle || 'active' }}</span>
                                </div>
                            </div>
                        </div>

                        <a
                            :href="app.admin_url"
                            class="block w-full py-2.5 px-4 text-center rounded-xl bg-gray-900 hover:bg-blue-600 text-white text-xs font-semibold shadow-sm transition-colors flex items-center justify-center gap-1.5 group"
                        >
                            Open {{ app.name }} Admin
                            <span class="material-symbols-outlined text-base group-hover:translate-x-0.5 transition-transform">arrow_forward</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Deep Dive Performance & Operations Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- GrowNet MLM Performance -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                            <span class="material-symbols-outlined text-purple-600">stars</span>
                            GrowNet MLM & Points
                        </h3>
                        <a href="/admin/points" class="text-xs font-medium text-blue-600 hover:underline">Details</a>
                    </div>
                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500">Points Awarded This Month:</span>
                            <span class="font-bold text-gray-900">{{ formatNumber((pointsMetrics?.this_month_lp || 0) + (pointsMetrics?.this_month_map || 0)) }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500">MAP Qualification Rate:</span>
                            <span class="font-bold text-purple-600">{{ pointsMetrics?.qualification_rate || 0 }}%</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-gray-500">Matrix Network Fill Rate:</span>
                            <span class="font-bold text-gray-900">{{ matrixMetrics?.fill_rate || 0 }}% ({{ matrixMetrics?.filled_positions || 0 }}/{{ matrixMetrics?.total_positions || 0 }})</span>
                        </div>
                    </div>
                </div>

                <!-- Support & Operations -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-600">support_agent</span>
                            Support & Operations
                        </h3>
                        <a href="/admin/support-tickets" class="text-xs font-medium text-blue-600 hover:underline">Manage</a>
                    </div>
                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500">Total Support Tickets:</span>
                            <span class="font-bold text-gray-900">{{ supportData?.total_tickets || 0 }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500">Open / Pending Tickets:</span>
                            <span class="font-bold text-amber-600">{{ (supportData?.open_tickets || 0) + (supportData?.pending_tickets || 0) }}</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-gray-500">Telegram Bot Linked:</span>
                            <span class="font-bold text-emerald-600">{{ telegramMetrics?.total_linked || 0 }} members</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Platform Actions -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-4">
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
                    </div>
                </div>

            </div>

        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
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

const currentDate = computed(() => {
    return new Date().toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
});

const formatNumber = (num: number) => {
    return new Intl.NumberFormat().format(Math.round(num || 0));
};
</script>
