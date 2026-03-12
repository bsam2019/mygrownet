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
    ClockIcon,
    MapPinIcon,
    LinkIcon,
    DocumentArrowDownIcon,
    CalendarIcon,
    FunnelIcon,
    CursorArrowRaysIcon,
} from '@heroicons/vue/24/outline';
import { computed, ref, onMounted, onUnmounted } from 'vue';

interface DailyStats {
    date: string;
    views: number;
    visitors: number;
}

interface DeviceStats {
    device: string;
    count: number;
    percentage: number;
}

interface PageStats {
    path: string;
    views: number;
    avgTime: number;
    bounceRate: number;
}

interface TrafficSource {
    source: string;
    visitors: number;
    percentage: number;
    type: 'direct' | 'search' | 'social' | 'referral' | 'email';
}

interface GeographicData {
    country: string;
    countryCode: string;
    visitors: number;
    percentage: number;
}

interface ConversionGoal {
    name: string;
    completions: number;
    conversionRate: number;
    value?: number;
}

interface Site {
    id: number;
    name: string;
    subdomain: string;
}

interface Module {
    id: string;
    name: string;
    slug: string;
    color: string | null;
    has_access: boolean;
    primary_route: string;
    description?: string | null;
}

const props = defineProps<{
    site: Site;
    totalViews: number;
    totalVisitors: number;
    viewsChange: number;
    avgSessionDuration: number;
    newVisitors: number;
    returningVisitors: number;
    dailyStats: DailyStats[];
    deviceStats: DeviceStats[];
    topPages: PageStats[];
    trafficSources: TrafficSource[];
    geographicData: GeographicData[];
    conversionGoals: ConversionGoal[];
    period: string;
    modules?: Module[];
}>();

const selectedPeriod = ref(props.period || '30d');
const showComparison = ref(false);
const showExportMenu = ref(false);

// Watch for period changes and reload data
const changePeriod = () => {
    if (selectedPeriod.value !== props.period) {
        // Navigate to the same route with new period parameter
        window.location.href = route('growbuilder.sites.analytics', { 
            id: props.site.id, 
            period: selectedPeriod.value 
        });
    }
};

// Close export menu when clicking outside
const closeExportMenu = (event: Event) => {
    if (!(event.target as Element).closest('.relative')) {
        showExportMenu.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', closeExportMenu);
});

onUnmounted(() => {
    document.removeEventListener('click', closeExportMenu);
});

const exportAnalytics = (format: 'pdf' | 'csv' | 'excel') => {
    showExportMenu.value = false;
    
    const params = new URLSearchParams({
        format,
        period: selectedPeriod.value,
    });
    
    // Create a temporary link to trigger download
    const url = route('growbuilder.sites.analytics.export', { id: props.site.id }) + '?' + params.toString();
    const link = document.createElement('a');
    link.href = url;
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

const formatNumber = (num: number) => num.toLocaleString();
const formatDuration = (seconds: number) => {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
};
const formatCurrency = (amount: number) => `K${(amount / 100).toLocaleString('en-ZM', { minimumFractionDigits: 2 })}`;

const maxDailyViews = computed(() => Math.max(...props.dailyStats.map(d => d.views), 1));

const getBarHeight = (views: number) => {
    return (views / maxDailyViews.value) * 100;
};

const getDeviceIcon = (device: string) => {
    if (device === 'mobile') return DevicePhoneMobileIcon;
    if (device === 'tablet') return DevicePhoneMobileIcon;
    return ComputerDesktopIcon;
};

const getTrafficSourceIcon = (type: string) => {
    switch (type) {
        case 'search': return GlobeAltIcon;
        case 'social': return UsersIcon;
        case 'referral': return LinkIcon;
        case 'email': return '@';
        default: return CursorArrowRaysIcon;
    }
};

const getTrafficSourceColor = (type: string) => {
    switch (type) {
        case 'search': return 'text-green-600 bg-green-100';
        case 'social': return 'text-blue-600 bg-blue-100';
        case 'referral': return 'text-purple-600 bg-purple-100';
        case 'email': return 'text-orange-600 bg-orange-100';
        default: return 'text-gray-600 bg-gray-100';
    }
};

const totalConversions = computed(() => 
    props.conversionGoals.reduce((sum, goal) => sum + goal.completions, 0)
);

const avgConversionRate = computed(() => {
    if (props.conversionGoals.length === 0) return 0;
    const totalRate = props.conversionGoals.reduce((sum, goal) => sum + goal.conversionRate, 0);
    return totalRate / props.conversionGoals.length;
});
</script>

<template>
    <AppLayout :modules="modules">
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
                        <div class="flex items-center gap-3">
                            <!-- Period Selector -->
                            <select 
                                v-model="selectedPeriod"
                                @change="changePeriod"
                                class="px-4 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="7d">Last 7 days</option>
                                <option value="30d">Last 30 days</option>
                                <option value="90d">Last 90 days</option>
                                <option value="custom">Custom Range</option>
                            </select>
                            
                            <!-- Export Button -->
                            <div class="relative">
                                <button 
                                    @click="showExportMenu = !showExportMenu"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-xl hover:bg-gray-200 transition"
                                >
                                    <DocumentArrowDownIcon class="h-4 w-4" aria-hidden="true" />
                                    Export
                                </button>
                                
                                <!-- Export Dropdown Menu -->
                                <div 
                                    v-show="showExportMenu"
                                    class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10"
                                >
                                    <div class="py-1">
                                        <button
                                            @click="exportAnalytics('pdf')"
                                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2"
                                        >
                                            <DocumentArrowDownIcon class="h-4 w-4 text-red-500" aria-hidden="true" />
                                            Export as PDF
                                        </button>
                                        <button
                                            @click="exportAnalytics('csv')"
                                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2"
                                        >
                                            <DocumentArrowDownIcon class="h-4 w-4 text-green-500" aria-hidden="true" />
                                            Export as CSV
                                        </button>
                                        <button
                                            @click="exportAnalytics('excel')"
                                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2"
                                        >
                                            <DocumentArrowDownIcon class="h-4 w-4 text-blue-500" aria-hidden="true" />
                                            Export as Excel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Stats Cards -->
                <div class="grid grid-cols-2 lg:grid-cols-6 gap-4 mb-8">
                    <!-- Page Views -->
                    <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                        <div class="flex items-center justify-between mb-3">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <EyeIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                            </div>
                            <div :class="[
                                'flex items-center gap-1 text-xs font-medium',
                                viewsChange >= 0 ? 'text-green-600' : 'text-red-600'
                            ]">
                                <component :is="viewsChange >= 0 ? ArrowTrendingUpIcon : ArrowTrendingDownIcon" class="h-3 w-3" />
                                {{ Math.abs(viewsChange) }}%
                            </div>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ formatNumber(totalViews) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Page Views</p>
                    </div>

                    <!-- Unique Visitors -->
                    <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                        <div class="flex items-center justify-between mb-3">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <UsersIcon class="h-5 w-5 text-green-600" aria-hidden="true" />
                            </div>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ formatNumber(totalVisitors) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Unique Visitors</p>
                    </div>

                    <!-- Session Duration -->
                    <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                        <div class="flex items-center justify-between mb-3">
                            <div class="p-2 bg-purple-100 rounded-lg">
                                <ClockIcon class="h-5 w-5 text-purple-600" aria-hidden="true" />
                            </div>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ formatDuration(avgSessionDuration || 0) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Avg. Session</p>
                    </div>

                    <!-- Bounce Rate -->
                    <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                        <div class="flex items-center justify-between mb-3">
                            <div class="p-2 bg-amber-100 rounded-lg">
                                <GlobeAltIcon class="h-5 w-5 text-amber-600" aria-hidden="true" />
                            </div>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ totalViews > 0 ? ((totalVisitors / totalViews) * 100).toFixed(1) : 0 }}%</p>
                        <p class="text-xs text-gray-500 mt-1">Bounce Rate</p>
                    </div>

                    <!-- New vs Returning -->
                    <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                        <div class="flex items-center justify-between mb-3">
                            <div class="p-2 bg-indigo-100 rounded-lg">
                                <UsersIcon class="h-5 w-5 text-indigo-600" aria-hidden="true" />
                            </div>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ formatNumber(newVisitors || 0) }}</p>
                        <p class="text-xs text-gray-500 mt-1">New Visitors</p>
                    </div>

                    <!-- Conversions -->
                    <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                        <div class="flex items-center justify-between mb-3">
                            <div class="p-2 bg-emerald-100 rounded-lg">
                                <FunnelIcon class="h-5 w-5 text-emerald-600" aria-hidden="true" />
                            </div>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ formatNumber(totalConversions) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Conversions</p>
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

                <!-- Enhanced Analytics Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Traffic Sources -->
                    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <LinkIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                            Traffic Sources
                        </h2>
                        <div class="space-y-3">
                            <div
                                v-for="(source, index) in trafficSources"
                                :key="index"
                                class="flex items-center justify-between py-2"
                            >
                                <div class="flex items-center gap-3">
                                    <div :class="['p-1.5 rounded-lg', getTrafficSourceColor(source.type)]">
                                        <component :is="getTrafficSourceIcon(source.type)" class="h-4 w-4" aria-hidden="true" />
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-700 capitalize">{{ source.source }}</span>
                                        <p class="text-xs text-gray-500">{{ source.percentage }}% of traffic</p>
                                    </div>
                                </div>
                                <span class="text-sm font-medium text-gray-900">{{ formatNumber(source.visitors) }}</span>
                            </div>
                            <div v-if="trafficSources.length === 0" class="text-center py-8">
                                <LinkIcon class="h-8 w-8 text-gray-300 mx-auto mb-2" aria-hidden="true" />
                                <p class="text-sm text-gray-500 mb-1">No traffic source data yet</p>
                                <p class="text-xs text-gray-400">Data will appear as your site gets more visitors</p>
                            </div>
                        </div>
                    </div>

                    <!-- Geographic Data -->
                    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <MapPinIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                            Top Locations
                        </h2>
                        <div class="space-y-3">
                            <div
                                v-for="(location, index) in geographicData"
                                :key="index"
                                class="flex items-center justify-between py-2"
                            >
                                <div class="flex items-center gap-3">
                                    <span class="text-lg">{{ location.countryCode === 'ZM' ? '🇿🇲' : '🌍' }}</span>
                                    <div>
                                        <span class="text-sm font-medium text-gray-700">{{ location.country }}</span>
                                        <p class="text-xs text-gray-500">{{ location.percentage }}% of visitors</p>
                                    </div>
                                </div>
                                <span class="text-sm font-medium text-gray-900">{{ formatNumber(location.visitors) }}</span>
                            </div>
                            <div v-if="geographicData.length === 0" class="text-center py-8">
                                <MapPinIcon class="h-8 w-8 text-gray-300 mx-auto mb-2" aria-hidden="true" />
                                <p class="text-sm text-gray-500 mb-1">No location data yet</p>
                                <p class="text-xs text-gray-400">Geographic insights will appear with more traffic</p>
                            </div>
                        </div>
                    </div>

                    <!-- Conversion Goals -->
                    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <FunnelIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                            Conversion Goals
                        </h2>
                        <div class="space-y-3">
                            <div
                                v-for="(goal, index) in conversionGoals"
                                :key="index"
                                class="p-3 bg-gray-50 rounded-lg"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-700">{{ goal.name }}</span>
                                    <span class="text-sm font-bold text-gray-900">{{ goal.completions }}</span>
                                </div>
                                <div class="flex items-center justify-between text-xs text-gray-500">
                                    <span>{{ goal.conversionRate.toFixed(1) }}% conversion rate</span>
                                    <span v-if="goal.value">{{ formatCurrency(goal.value) }} value</span>
                                </div>
                            </div>
                            <div v-if="conversionGoals.length === 0" class="text-center py-8">
                                <FunnelIcon class="h-8 w-8 text-gray-300 mx-auto mb-2" aria-hidden="true" />
                                <p class="text-sm text-gray-500 mb-1">No conversion data yet</p>
                                <p class="text-xs text-gray-400">Set up goals to track form submissions and purchases</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Bottom Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Enhanced Top Pages -->
                    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Top Pages</h2>
                        <div class="space-y-3">
                            <div
                                v-for="(page, index) in topPages"
                                :key="index"
                                class="p-3 border border-gray-100 rounded-lg hover:bg-gray-50 transition"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-3">
                                        <span class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-medium">
                                            {{ index + 1 }}
                                        </span>
                                        <span class="text-sm font-medium text-gray-700 truncate max-w-[200px]">{{ page.path || '/' }}</span>
                                    </div>
                                    <span class="text-sm font-bold text-gray-900">{{ formatNumber(page.views) }}</span>
                                </div>
                                <div class="flex items-center justify-between text-xs text-gray-500 ml-9">
                                    <span>Avg. time: {{ formatDuration(page.avgTime || 0) }}</span>
                                    <span>Bounce: {{ page.bounceRate?.toFixed(1) || 0 }}%</span>
                                </div>
                            </div>
                            <p v-if="topPages.length === 0" class="text-sm text-gray-500 text-center py-4">
                                No page data yet
                            </p>
                        </div>
                    </div>

                    <!-- Enhanced Device Breakdown -->
                    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Device & Browser Insights</h2>
                        <div class="space-y-4">
                            <div
                                v-for="device in deviceStats"
                                :key="device.device"
                                class="p-3 border border-gray-100 rounded-lg"
                            >
                                <div class="flex items-center gap-4 mb-3">
                                    <div class="p-2 bg-gray-100 rounded-lg">
                                        <component :is="getDeviceIcon(device.device)" class="h-5 w-5 text-gray-600" aria-hidden="true" />
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-sm font-medium text-gray-700 capitalize">{{ device.device }}</span>
                                            <span class="text-sm font-bold text-gray-900">{{ formatNumber(device.count) }}</span>
                                        </div>
                                        <div class="flex items-center justify-between text-xs text-gray-500">
                                            <span>{{ device.percentage }}% of sessions</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div
                                        class="h-full bg-gradient-to-r from-blue-500 to-blue-600 rounded-full transition-all"
                                        :style="{ width: device.percentage + '%' }"
                                    ></div>
                                </div>
                            </div>
                            <p v-if="deviceStats.length === 0" class="text-sm text-gray-500 text-center py-4">
                                No device data yet
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Performance Insights Section -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200 mb-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <ChartBarIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                        Performance Insights
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-white rounded-lg p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                <span class="text-sm font-medium text-gray-700">Engagement Rate</span>
                            </div>
                            <p class="text-xl font-bold text-gray-900">{{ avgConversionRate.toFixed(1) }}%</p>
                            <p class="text-xs text-gray-500">Average across all goals</p>
                        </div>
                        <div class="bg-white rounded-lg p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                                <span class="text-sm font-medium text-gray-700">Return Visitors</span>
                            </div>
                            <p class="text-xl font-bold text-gray-900">{{ formatNumber(returningVisitors || 0) }}</p>
                            <p class="text-xs text-gray-500">{{ totalVisitors > 0 ? ((returningVisitors || 0) / totalVisitors * 100).toFixed(1) : 0 }}% of total</p>
                        </div>
                        <div class="bg-white rounded-lg p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-3 h-3 bg-purple-500 rounded-full"></div>
                                <span class="text-sm font-medium text-gray-700">Pages per Session</span>
                            </div>
                            <p class="text-xl font-bold text-gray-900">{{ totalVisitors > 0 ? (totalViews / totalVisitors).toFixed(1) : 0 }}</p>
                            <p class="text-xs text-gray-500">Average page depth</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
