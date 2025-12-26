<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    ArrowLeftIcon,
    ChartBarIcon,
    EyeIcon,
    UsersIcon,
    DevicePhoneMobileIcon,
    ComputerDesktopIcon,
    GlobeAltIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
} from '@heroicons/vue/24/outline';
import { computed } from 'vue';

interface DailyStats {
    date: string;
    views: number;
}

interface DeviceStats {
    device: string;
    count: number;
    percentage: number;
}

interface PageStats {
    path: string;
    views: number;
}

interface Site {
    id: number;
    name: string;
    subdomain: string;
}

const props = defineProps<{
    site: Site;
    totalViews: number;
    totalVisitors: number;
    viewsChange: number;
    dailyStats: DailyStats[];
    deviceStats: DeviceStats[];
    topPages: PageStats[];
    period: string;
}>();

const formatNumber = (num: number) => num.toLocaleString();

const maxDailyViews = computed(() => Math.max(...props.dailyStats.map(d => d.views), 1));

const getBarHeight = (views: number) => {
    return (views / maxDailyViews.value) * 100;
};

const getDeviceIcon = (device: string) => {
    if (device === 'mobile') return DevicePhoneMobileIcon;
    if (device === 'tablet') return DevicePhoneMobileIcon;
    return ComputerDesktopIcon;
};
</script>

<template>
    <AppLayout>
        <Head :title="`Analytics - ${site.name}`" />

        <div class="py-6">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('growbuilder.index')"
                        class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-4"
                    >
                        <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                        Back to Dashboard
                    </Link>

                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Analytics</h1>
                            <p class="text-sm text-gray-500">{{ site.name }} • {{ site.subdomain }}.mygrownet.com</p>
                        </div>
                        <select class="px-4 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="7d">Last 7 days</option>
                            <option value="30d" selected>Last 30 days</option>
                            <option value="90d">Last 90 days</option>
                        </select>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-blue-100 rounded-xl">
                                <EyeIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                            </div>
                            <div :class="[
                                'flex items-center gap-1 text-sm font-medium',
                                viewsChange >= 0 ? 'text-green-600' : 'text-red-600'
                            ]">
                                <component :is="viewsChange >= 0 ? ArrowTrendingUpIcon : ArrowTrendingDownIcon" class="h-4 w-4" />
                                {{ Math.abs(viewsChange) }}%
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-gray-900">{{ formatNumber(totalViews) }}</p>
                        <p class="text-sm text-gray-500 mt-1">Page Views</p>
                    </div>

                    <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-green-100 rounded-xl">
                                <UsersIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-gray-900">{{ formatNumber(totalVisitors) }}</p>
                        <p class="text-sm text-gray-500 mt-1">Unique Visitors</p>
                    </div>

                    <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-purple-100 rounded-xl">
                                <ChartBarIcon class="h-6 w-6 text-purple-600" aria-hidden="true" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-gray-900">{{ totalVisitors > 0 ? (totalViews / totalVisitors).toFixed(1) : 0 }}</p>
                        <p class="text-sm text-gray-500 mt-1">Pages per Visit</p>
                    </div>
                </div>

                <!-- Views Chart -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm mb-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-6">Page Views Over Time</h2>
                    <div class="h-48 flex items-end gap-1">
                        <div
                            v-for="(day, index) in dailyStats"
                            :key="index"
                            class="flex-1 flex flex-col items-center"
                        >
                            <div
                                class="w-full bg-blue-500 rounded-t transition-all hover:bg-blue-600"
                                :style="{ height: getBarHeight(day.views) + '%', minHeight: day.views > 0 ? '4px' : '0' }"
                                :title="`${day.date}: ${day.views} views`"
                            ></div>
                        </div>
                    </div>
                    <div class="flex justify-between mt-2 text-xs text-gray-400">
                        <span>{{ dailyStats[0]?.date }}</span>
                        <span>{{ dailyStats[dailyStats.length - 1]?.date }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Top Pages -->
                    <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Top Pages</h2>
                        <div class="space-y-3">
                            <div
                                v-for="(page, index) in topPages"
                                :key="index"
                                class="flex items-center justify-between py-2"
                            >
                                <div class="flex items-center gap-3">
                                    <span class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center text-xs font-medium text-gray-600">
                                        {{ index + 1 }}
                                    </span>
                                    <span class="text-sm text-gray-700 truncate max-w-[200px]">{{ page.path || '/' }}</span>
                                </div>
                                <span class="text-sm font-medium text-gray-900">{{ formatNumber(page.views) }}</span>
                            </div>
                            <p v-if="topPages.length === 0" class="text-sm text-gray-500 text-center py-4">
                                No page data yet
                            </p>
                        </div>
                    </div>

                    <!-- Device Breakdown -->
                    <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Devices</h2>
                        <div class="space-y-4">
                            <div
                                v-for="device in deviceStats"
                                :key="device.device"
                                class="flex items-center gap-4"
                            >
                                <div class="p-2 bg-gray-100 rounded-lg">
                                    <component :is="getDeviceIcon(device.device)" class="h-5 w-5 text-gray-600" aria-hidden="true" />
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm font-medium text-gray-700 capitalize">{{ device.device }}</span>
                                        <span class="text-sm text-gray-500">{{ device.percentage }}%</span>
                                    </div>
                                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                        <div
                                            class="h-full bg-blue-500 rounded-full"
                                            :style="{ width: device.percentage + '%' }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                            <p v-if="deviceStats.length === 0" class="text-sm text-gray-500 text-center py-4">
                                No device data yet
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
