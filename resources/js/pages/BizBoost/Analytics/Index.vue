<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import InteractiveChart from '@/Components/BizBoost/Dashboard/InteractiveChart.vue';
import {
    EyeIcon,
    HeartIcon,
    ChatBubbleLeftIcon,
    QrCodeIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
} from '@heroicons/vue/24/outline';
import { computed, ref } from 'vue';

interface Props {
    overview: {
        page_views: { current: number; change: number };
        engagements: { current: number };
        whatsapp_clicks: { current: number };
        qr_scans: { current: number };
        posts_published: number;
    };
    dailyMetrics: Array<{
        date: string;
        page_views: number;
        post_engagements: number;
    }>;
    topPosts: Array<{
        id: number;
        title: string | null;
        caption: string;
        published_at: string;
        analytics: { engagements?: number } | null;
    }>;
    trafficSources: Array<{ source: string; count: number }>;
    hasAdvancedAnalytics: boolean;
    period: string;
}

const props = defineProps<Props>();

const selectedPeriod = ref(props.period);

const periods = [
    { value: '7', label: 'Last 7 days' },
    { value: '30', label: 'Last 30 days' },
    { value: '90', label: 'Last 90 days' },
];

const changePeriod = (period: string) => {
    selectedPeriod.value = period;
    router.get('/bizboost/analytics', { period }, { preserveState: true });
};

const statCards = computed(() => [
    {
        name: 'Page Views',
        value: props.overview.page_views.current,
        change: props.overview.page_views.change,
        icon: EyeIcon,
        color: 'bg-blue-500',
    },
    {
        name: 'Engagements',
        value: props.overview.engagements.current,
        change: null,
        icon: HeartIcon,
        color: 'bg-pink-500',
    },
    {
        name: 'WhatsApp Clicks',
        value: props.overview.whatsapp_clicks.current,
        change: null,
        icon: ChatBubbleLeftIcon,
        color: 'bg-green-500',
    },
    {
        name: 'QR Scans',
        value: props.overview.qr_scans.current,
        change: null,
        icon: QrCodeIcon,
        color: 'bg-violet-500',
    },
]);

// Transform daily metrics for InteractiveChart
const pageViewsChartData = computed(() => 
    props.dailyMetrics?.map(m => ({
        label: new Date(m.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }),
        value: m.page_views,
        date: m.date,
        details: { page_views: m.page_views, engagements: m.post_engagements }
    })) || []
);

const engagementsChartData = computed(() => 
    props.dailyMetrics?.map(m => ({
        label: new Date(m.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }),
        value: m.post_engagements,
        date: m.date,
    })) || []
);

const handleChartPeriodChange = (period: string) => {
    const periodMap: Record<string, string> = { '7d': '7', '30d': '30', '90d': '90', '1y': '365' };
    changePeriod(periodMap[period] || '30');
};
</script>

<template>
    <Head title="Analytics - BizBoost" />
    <BizBoostLayout title="Analytics">
        <div class="space-y-6">
            <!-- Period Selector -->
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Overview</h2>
                <div class="flex gap-2">
                    <button
                        v-for="p in periods"
                        :key="p.value"
                        @click="changePeriod(p.value)"
                        :class="[
                            'px-3 py-1.5 text-sm rounded-lg transition-colors',
                            selectedPeriod === p.value
                                ? 'bg-violet-600 text-white'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                        ]"
                    >
                        {{ p.label }}
                    </button>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div
                    v-for="stat in statCards"
                    :key="stat.name"
                    class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-200"
                >
                    <div class="flex items-center gap-3">
                        <div :class="[stat.color, 'rounded-lg p-2']">
                            <component :is="stat.icon" class="h-5 w-5 text-white" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">{{ stat.name }}</p>
                            <p class="text-xl font-semibold text-gray-900">{{ stat.value.toLocaleString() }}</p>
                        </div>
                    </div>
                    <div v-if="stat.change !== null" class="mt-2 flex items-center gap-1">
                        <component
                            :is="stat.change >= 0 ? ArrowTrendingUpIcon : ArrowTrendingDownIcon"
                            :class="[
                                'h-4 w-4',
                                stat.change >= 0 ? 'text-green-500' : 'text-red-500'
                            ]"
                            aria-hidden="true"
                        />
                        <span
                            :class="[
                                'text-xs',
                                stat.change >= 0 ? 'text-green-600' : 'text-red-600'
                            ]"
                        >
                            {{ stat.change >= 0 ? '+' : '' }}{{ stat.change }}% vs previous
                        </span>
                    </div>
                </div>
            </div>

            <!-- Interactive Charts -->
            <div class="grid gap-6 lg:grid-cols-2">
                <InteractiveChart
                    title="Page Views"
                    type="line"
                    :data="pageViewsChartData"
                    color="blue"
                    :show-trend="true"
                    :drill-down-enabled="true"
                    @period-change="handleChartPeriodChange"
                />
                <InteractiveChart
                    title="Engagements"
                    type="bar"
                    :data="engagementsChartData"
                    color="violet"
                    :show-trend="true"
                    @period-change="handleChartPeriodChange"
                />
            </div>

            <!-- Top Posts & Traffic Sources -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Top Posts -->
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Top Performing Posts</h3>
                        <Link href="/bizboost/analytics/posts" class="text-sm text-violet-600 hover:text-violet-700">
                            View all
                        </Link>
                    </div>
                    <div v-if="topPosts?.length" class="space-y-3">
                        <Link
                            v-for="post in topPosts"
                            :key="post.id"
                            :href="`/bizboost/analytics/posts/${post.id}`"
                            class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0 hover:bg-gray-50 -mx-2 px-2 rounded"
                        >
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-gray-700 truncate">{{ post.caption }}</p>
                                <p class="text-xs text-gray-500">{{ new Date(post.published_at).toLocaleDateString() }}</p>
                            </div>
                            <span class="text-sm font-medium text-violet-600 ml-2">
                                {{ post.analytics?.engagements ?? 0 }} engagements
                            </span>
                        </Link>
                    </div>
                    <p v-else class="text-sm text-gray-500 text-center py-4">No published posts yet.</p>
                </div>

                <!-- Traffic Sources -->
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Traffic Sources</h3>
                    <div v-if="trafficSources?.length" class="space-y-3">
                        <div
                            v-for="source in trafficSources"
                            :key="source.source"
                            class="flex items-center justify-between"
                        >
                            <span class="text-sm text-gray-700 capitalize">{{ source.source || 'Direct' }}</span>
                            <div class="flex items-center gap-2">
                                <div class="w-24 h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div
                                        class="h-full bg-violet-500 rounded-full"
                                        :style="{ width: `${Math.min((source.count / (trafficSources[0]?.count || 1)) * 100, 100)}%` }"
                                    ></div>
                                </div>
                                <span class="text-sm font-medium text-gray-900 w-12 text-right">{{ source.count }}</span>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-500 text-center py-4">No traffic data yet.</p>
                </div>
            </div>

            <!-- Advanced Analytics Upsell -->
            <div v-if="!hasAdvancedAnalytics" class="rounded-xl bg-gradient-to-r from-violet-600 to-violet-700 p-6 text-white">
                <h3 class="text-lg font-semibold mb-2">Unlock Advanced Analytics</h3>
                <p class="text-violet-100 text-sm mb-4">
                    Get detailed insights, competitor analysis, and AI-powered recommendations to grow your business faster.
                </p>
                <Link
                    href="/bizboost/upgrade"
                    class="inline-flex items-center px-4 py-2 bg-white text-violet-600 rounded-lg text-sm font-medium hover:bg-violet-50 transition-colors"
                >
                    Upgrade Now
                </Link>
            </div>
        </div>
    </BizBoostLayout>
</template>
