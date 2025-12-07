<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import {
    ArrowLeftIcon,
    HeartIcon,
    ChatBubbleLeftIcon,
    ShareIcon,
    EyeIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
    ClockIcon,
} from '@heroicons/vue/24/outline';

interface Props {
    post: {
        id: number;
        caption: string;
        published_at: string;
        platforms: string[];
        media: Array<{ url: string; type: string }>;
    };
    analytics: {
        total_likes: number;
        total_comments: number;
        total_shares: number;
        total_reach: number;
        engagement_rate: number;
        best_performing_platform: string | null;
        peak_engagement_time: string | null;
    };
    engagementHistory: Array<{
        date: string;
        likes: number;
        comments: number;
        shares: number;
        reach: number;
    }>;
    comparisons: {
        likes_vs_avg: number;
        comments_vs_avg: number;
        reach_vs_avg: number;
    };
}

const props = defineProps<Props>();

const metricCards = [
    { name: 'Total Reach', key: 'total_reach', icon: EyeIcon, color: 'bg-blue-500' },
    { name: 'Likes', key: 'total_likes', icon: HeartIcon, color: 'bg-pink-500', comparison: 'likes_vs_avg' },
    { name: 'Comments', key: 'total_comments', icon: ChatBubbleLeftIcon, color: 'bg-green-500', comparison: 'comments_vs_avg' },
    { name: 'Shares', key: 'total_shares', icon: ShareIcon, color: 'bg-violet-500' },
];
</script>

<template>
    <Head :title="`Post Analytics - BizBoost`" />
    <BizBoostLayout title="Post Analytics">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link href="/bizboost/analytics/posts" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                </Link>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Post Performance</h2>
                    <p class="text-sm text-gray-500">Published {{ new Date(post.published_at).toLocaleDateString() }}</p>
                </div>
            </div>

            <!-- Post Preview & Stats -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Post Preview -->
                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-200">
                    <div v-if="post.media?.[0]" class="aspect-square rounded-lg overflow-hidden bg-gray-100 mb-4">
                        <img :src="post.media[0].url" class="h-full w-full object-cover" alt="" />
                    </div>
                    <p class="text-sm text-gray-700 line-clamp-3">{{ post.caption }}</p>
                    <div v-if="post.platforms?.length" class="mt-3 flex gap-2">
                        <span
                            v-for="platform in post.platforms"
                            :key="platform"
                            class="text-xs px-2 py-1 bg-gray-100 text-gray-600 rounded-full capitalize"
                        >
                            {{ platform }}
                        </span>
                    </div>
                </div>

                <!-- Metrics -->
                <div class="lg:col-span-2 grid grid-cols-2 gap-4">
                    <div
                        v-for="metric in metricCards"
                        :key="metric.key"
                        class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-200"
                    >
                        <div class="flex items-center gap-3">
                            <div :class="[metric.color, 'rounded-lg p-2']">
                                <component :is="metric.icon" class="h-5 w-5 text-white" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">{{ metric.name }}</p>
                                <p class="text-xl font-semibold text-gray-900">
                                    {{ (analytics[metric.key as keyof typeof analytics] as number).toLocaleString() }}
                                </p>
                            </div>
                        </div>
                        <div v-if="metric.comparison" class="mt-2 flex items-center gap-1">
                            <component
                                :is="comparisons[metric.comparison as keyof typeof comparisons] >= 0 ? ArrowTrendingUpIcon : ArrowTrendingDownIcon"
                                :class="[
                                    'h-4 w-4',
                                    comparisons[metric.comparison as keyof typeof comparisons] >= 0 ? 'text-green-500' : 'text-red-500'
                                ]"
                                aria-hidden="true"
                            />
                            <span
                                :class="[
                                    'text-xs',
                                    comparisons[metric.comparison as keyof typeof comparisons] >= 0 ? 'text-green-600' : 'text-red-600'
                                ]"
                            >
                                {{ comparisons[metric.comparison as keyof typeof comparisons] >= 0 ? '+' : '' }}{{ comparisons[metric.comparison as keyof typeof comparisons] }}% vs avg
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Insights -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Engagement Rate -->
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Engagement Rate</h3>
                    <div class="flex items-center gap-4">
                        <div class="relative h-24 w-24">
                            <svg class="h-24 w-24 -rotate-90" viewBox="0 0 36 36">
                                <path
                                    class="text-gray-200"
                                    stroke="currentColor"
                                    stroke-width="3"
                                    fill="none"
                                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                />
                                <path
                                    class="text-violet-500"
                                    stroke="currentColor"
                                    stroke-width="3"
                                    fill="none"
                                    stroke-linecap="round"
                                    :stroke-dasharray="`${Math.min(analytics.engagement_rate, 100)}, 100`"
                                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                />
                            </svg>
                            <span class="absolute inset-0 flex items-center justify-center text-lg font-semibold text-gray-900">
                                {{ analytics.engagement_rate }}%
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">
                                {{ analytics.engagement_rate >= 3 ? 'Great engagement!' : analytics.engagement_rate >= 1 ? 'Good engagement' : 'Room for improvement' }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Industry average: 1-3%</p>
                        </div>
                    </div>
                </div>

                <!-- Best Time & Platform -->
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Insights</h3>
                    <div class="space-y-4">
                        <div v-if="analytics.peak_engagement_time" class="flex items-center gap-3">
                            <div class="rounded-lg bg-amber-100 p-2">
                                <ClockIcon class="h-5 w-5 text-amber-600" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Peak Engagement Time</p>
                                <p class="font-medium text-gray-900">{{ analytics.peak_engagement_time }}</p>
                            </div>
                        </div>
                        <div v-if="analytics.best_performing_platform" class="flex items-center gap-3">
                            <div class="rounded-lg bg-blue-100 p-2">
                                <ShareIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Best Platform</p>
                                <p class="font-medium text-gray-900 capitalize">{{ analytics.best_performing_platform }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Engagement History -->
            <div v-if="engagementHistory?.length" class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Engagement Over Time</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="py-2 text-left text-gray-500 font-medium">Date</th>
                                <th class="py-2 text-right text-gray-500 font-medium">Reach</th>
                                <th class="py-2 text-right text-gray-500 font-medium">Likes</th>
                                <th class="py-2 text-right text-gray-500 font-medium">Comments</th>
                                <th class="py-2 text-right text-gray-500 font-medium">Shares</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="day in engagementHistory" :key="day.date" class="border-b border-gray-100">
                                <td class="py-2 text-gray-900">{{ new Date(day.date).toLocaleDateString() }}</td>
                                <td class="py-2 text-right text-gray-700">{{ day.reach }}</td>
                                <td class="py-2 text-right text-gray-700">{{ day.likes }}</td>
                                <td class="py-2 text-right text-gray-700">{{ day.comments }}</td>
                                <td class="py-2 text-right text-gray-700">{{ day.shares }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
