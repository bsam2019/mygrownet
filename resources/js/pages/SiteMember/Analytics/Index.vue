<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import SiteMemberLayout from '@/layouts/SiteMemberLayout.vue';
import { computed, ref } from 'vue';
import { ChartBarIcon, EyeIcon, UsersIcon, DevicePhoneMobileIcon, ComputerDesktopIcon, GlobeAltIcon, ArrowTrendingUpIcon, ArrowTrendingDownIcon } from '@heroicons/vue/24/outline';

interface Props {
    site: { id: number; name: string; subdomain: string; theme: { primaryColor?: string } | null };
    settings: { navigation?: { logo?: string } } | null;
    user: { id: number; name: string; email: string; role: any; permissions: string[] };
    totalViews: number;
    totalVisitors: number;
    viewsChange: number;
    dailyStats: Array<{ date: string; views: number }>;
    deviceStats: Array<{ device: string; count: number; percentage: number }>;
    topPages: Array<{ path: string; views: number }>;
    period: string;
}

const props = defineProps<Props>();
const primaryColor = computed(() => props.site.theme?.primaryColor || '#2563eb');
const selectedPeriod = ref(props.period);

const maxViews = computed(() => Math.max(...props.dailyStats.map(d => d.views), 1));
const avgViews = computed(() => Math.round(props.dailyStats.reduce((sum, d) => sum + d.views, 0) / props.dailyStats.length));

const getDeviceIcon = (device: string) => {
    if (device === 'mobile') return DevicePhoneMobileIcon;
    if (device === 'tablet') return DevicePhoneMobileIcon;
    return ComputerDesktopIcon;
};

const getDeviceColor = (device: string) => {
    if (device === 'mobile') return '#8b5cf6';
    if (device === 'tablet') return '#06b6d4';
    return '#2563eb';
};

const changePeriod = (period: string) => {
    selectedPeriod.value = period;
    router.get(`/sites/${props.site.subdomain}/dashboard/analytics`, { period }, { preserveState: true });
};

const periodLabel = computed(() => {
    return selectedPeriod.value === '7d' ? '7 days' : selectedPeriod.value === '90d' ? '90 days' : '30 days';
});
</script>

<template>
    <SiteMemberLayout :site="site" :settings="settings" :user="user" title="Analytics">
        <Head :title="`Analytics - ${site.name}`" />

        <div class="max-w-6xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Analytics</h1>
                    <p class="text-gray-500">Track your site's performance over the last {{ periodLabel }}</p>
                </div>
                <div class="flex items-center gap-1 bg-gray-100 rounded-lg p-1">
                    <button v-for="p in ['7d', '30d', '90d']" :key="p" @click="changePeriod(p)"
                        :class="['px-3 py-1.5 text-sm font-medium rounded-md transition', selectedPeriod === p ? 'bg-white shadow text-gray-900' : 'text-gray-600 hover:text-gray-900']">
                        {{ p === '7d' ? '7D' : p === '30d' ? '30D' : '90D' }}
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                            <EyeIcon class="w-5 h-5 text-blue-600" aria-hidden="true" />
                        </div>
                        <span v-if="viewsChange !== 0" :class="viewsChange > 0 ? 'text-emerald-600' : 'text-red-600'" class="inline-flex items-center gap-1 text-sm font-medium">
                            <component :is="viewsChange > 0 ? ArrowTrendingUpIcon : ArrowTrendingDownIcon" class="w-4 h-4" />
                            {{ Math.abs(viewsChange) }}%
                        </span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">{{ totalViews.toLocaleString() }}</p>
                    <p class="text-sm text-gray-500">Total Views</p>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                            <UsersIcon class="w-5 h-5 text-emerald-600" aria-hidden="true" />
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">{{ totalVisitors.toLocaleString() }}</p>
                    <p class="text-sm text-gray-500">Unique Visitors</p>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                            <ChartBarIcon class="w-5 h-5 text-purple-600" aria-hidden="true" />
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">{{ avgViews }}</p>
                    <p class="text-sm text-gray-500">Avg. Daily Views</p>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                            <GlobeAltIcon class="w-5 h-5 text-amber-600" aria-hidden="true" />
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">{{ topPages.length }}</p>
                    <p class="text-sm text-gray-500">Pages Visited</p>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-6 mb-8">
                <!-- Chart -->
                <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Page Views Over Time</h3>
                    <div class="h-64 flex items-end gap-1">
                        <div v-for="(day, idx) in dailyStats" :key="idx" class="flex-1 flex flex-col items-center gap-1 group relative">
                            <div class="absolute bottom-full mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none whitespace-nowrap z-10">
                                {{ day.date }}: {{ day.views }} views
                            </div>
                            <div 
                                class="w-full rounded-t transition-all hover:opacity-80 cursor-pointer"
                                :style="{ height: `${Math.max((day.views / maxViews) * 100, 2)}%`, backgroundColor: primaryColor }"
                            ></div>
                            <span v-if="dailyStats.length <= 14 || idx % Math.ceil(dailyStats.length / 10) === 0" class="text-xs text-gray-400 truncate w-full text-center">
                                {{ day.date }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Device Stats -->
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Devices</h3>
                    <div v-if="deviceStats.length > 0" class="space-y-4">
                        <div v-for="device in deviceStats" :key="device.device" class="space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <component :is="getDeviceIcon(device.device)" class="w-4 h-4 text-gray-400" aria-hidden="true" />
                                    <span class="text-sm text-gray-700 capitalize">{{ device.device || 'Desktop' }}</span>
                                </div>
                                <span class="text-sm font-medium text-gray-900">{{ device.percentage }}%</span>
                            </div>
                            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all" :style="{ width: `${device.percentage}%`, backgroundColor: getDeviceColor(device.device) }"></div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500">
                        No device data yet
                    </div>
                </div>
            </div>

            <!-- Top Pages -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">Top Pages</h3>
                </div>
                <div v-if="topPages.length > 0">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Page</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Views</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">% of Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="page in topPages" :key="page.path" class="hover:bg-gray-50">
                                <td class="px-6 py-3">
                                    <span class="text-sm font-mono text-gray-700">{{ page.path }}</span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <span class="text-sm font-medium text-gray-900">{{ page.views.toLocaleString() }}</span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <span class="text-sm text-gray-500">{{ totalViews > 0 ? Math.round((page.views / totalViews) * 100) : 0 }}%</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="px-6 py-12 text-center text-gray-500">
                    No page view data yet. Views will appear here once visitors start browsing your site.
                </div>
            </div>
        </div>
    </SiteMemberLayout>
</template>