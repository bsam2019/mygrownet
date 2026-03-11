<template>
    <AppLayout title="Analytics - GrowStream Admin">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Analytics Dashboard</h1>
                    <p class="mt-2 text-gray-600">Platform performance and insights</p>
                </div>
                <select
                    v-model="selectedPeriod"
                    @change="loadAnalytics"
                    class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                    <option :value="7">Last 7 days</option>
                    <option :value="30">Last 30 days</option>
                    <option :value="90">Last 90 days</option>
                </select>
            </div>

            <!-- Stats Grid -->
            <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Total Videos -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Videos</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">
                                {{ analytics.overview.total_videos.toLocaleString() }}
                            </p>
                            <p class="mt-1 text-sm text-green-600">
                                +{{ analytics.overview.new_videos_this_period }} this period
                            </p>
                        </div>
                        <div class="rounded-full bg-blue-100 p-3">
                            <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
                                />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Views -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Views</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">
                                {{ analytics.overview.total_views.toLocaleString() }}
                            </p>
                            <p class="mt-1 text-sm text-green-600">
                                +{{ analytics.overview.views_this_period.toLocaleString() }} this period
                            </p>
                        </div>
                        <div class="rounded-full bg-green-100 p-3">
                            <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Watch Time -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Watch Time</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">
                                {{ analytics.overview.total_watch_time_hours.toLocaleString() }}h
                            </p>
                            <p class="mt-1 text-sm text-green-600">
                                +{{ analytics.overview.watch_time_this_period_hours.toLocaleString() }}h this period
                            </p>
                        </div>
                        <div class="rounded-full bg-purple-100 p-3">
                            <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Unique Viewers -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Unique Viewers</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">
                                {{ analytics.overview.unique_viewers.toLocaleString() }}
                            </p>
                            <p class="mt-1 text-sm text-green-600">
                                +{{ analytics.overview.unique_viewers_this_period.toLocaleString() }} this period
                            </p>
                        </div>
                        <div class="rounded-full bg-indigo-100 p-3">
                            <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Daily Views Chart -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Daily Views</h2>
                    <div class="h-64">
                        <!-- Chart placeholder -->
                        <div class="flex h-full items-end justify-around gap-2">
                            <div
                                v-for="(day, index) in analytics.daily_views.slice(-7)"
                                :key="index"
                                class="flex flex-1 flex-col items-center"
                            >
                                <div
                                    :style="{ height: `${(day.views / maxDailyViews) * 100}%` }"
                                    class="w-full rounded-t bg-blue-500"
                                ></div>
                                <span class="mt-2 text-xs text-gray-600">
                                    {{ new Date(day.date).toLocaleDateString('en-US', { weekday: 'short' }) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Categories -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Top Categories</h2>
                    <div class="space-y-4">
                        <div v-for="category in analytics.top_categories" :key="category.name" class="flex items-center">
                            <div class="flex-1">
                                <div class="mb-1 flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-900">{{ category.name }}</span>
                                    <span class="text-sm text-gray-600">{{ category.view_count.toLocaleString() }} views</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-gray-200">
                                    <div
                                        :style="{ width: `${(category.view_count / maxCategoryViews) * 100}%` }"
                                        class="h-full rounded-full bg-blue-600"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Metrics -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Completion Rate -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-2 text-sm font-medium text-gray-600">Completion Rate</h3>
                    <p class="text-3xl font-bold text-gray-900">{{ analytics.overview.completion_rate }}%</p>
                    <p class="mt-1 text-sm text-gray-600">Average video completion</p>
                </div>

                <!-- Avg Watch Duration -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-2 text-sm font-medium text-gray-600">Avg Watch Duration</h3>
                    <p class="text-3xl font-bold text-gray-900">
                        {{ Math.round(analytics.overview.avg_watch_duration_seconds / 60) }}m
                    </p>
                    <p class="mt-1 text-sm text-gray-600">Per viewing session</p>
                </div>

                <!-- Published Videos -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-2 text-sm font-medium text-gray-600">Published Videos</h3>
                    <p class="text-3xl font-bold text-gray-900">{{ analytics.overview.published_videos }}</p>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ Math.round((analytics.overview.published_videos / analytics.overview.total_videos) * 100) }}% of total
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { AnalyticsOverview } from '@/types/growstream';

interface Props {
    analytics: AnalyticsOverview;
}

const props = defineProps<Props>();

const selectedPeriod = ref(30);

const maxDailyViews = computed(() => {
    return Math.max(...props.analytics.daily_views.map((d) => d.views), 1);
});

const maxCategoryViews = computed(() => {
    return Math.max(...props.analytics.top_categories.map((c) => c.view_count), 1);
});

const loadAnalytics = () => {
    router.get(
        route('growstream.admin.analytics'),
        { period: selectedPeriod.value },
        { preserveState: true }
    );
};
</script>
