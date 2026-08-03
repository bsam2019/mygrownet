<template>
    <AdminLayout title="Analytics - GrowStream Admin">
        <div class="mx-auto max-w-7xl">
            <!-- Header -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-[var(--gs-text)]">Analytics Dashboard</h1>
                    <p class="mt-2 text-[var(--gs-muted)]">Platform performance and insights</p>
                </div>
                <select
                    v-model="selectedPeriod"
                    @change="loadAnalytics"
                    class="gs-input w-auto"
                >
                    <option :value="7">Last 7 days</option>
                    <option :value="30">Last 30 days</option>
                    <option :value="90">Last 90 days</option>
                </select>
            </div>

            <!-- Stats Grid -->
            <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Total Videos -->
                <div class="gs-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-[var(--gs-muted)]">Total Videos</p>
                            <p class="mt-2 text-3xl font-bold text-[var(--gs-text)]">
                                {{ analytics.overview.total_videos.toLocaleString() }}
                            </p>
                            <p class="mt-1 text-sm text-[var(--gs-primary)]">
                                +{{ analytics.overview.new_videos_this_period }} this period
                            </p>
                        </div>
                        <div class="rounded-full bg-[var(--gs-primary-soft)] p-3">
                            <svg class="h-8 w-8 text-[var(--gs-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <div class="gs-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-[var(--gs-muted)]">Total Views</p>
                            <p class="mt-2 text-3xl font-bold text-[var(--gs-text)]">
                                {{ analytics.overview.total_views.toLocaleString() }}
                            </p>
                            <p class="mt-1 text-sm text-[var(--gs-primary)]">
                                +{{ analytics.overview.views_this_period.toLocaleString() }} this period
                            </p>
                        </div>
                        <div class="rounded-full bg-[var(--gs-primary-soft)] p-3">
                            <svg class="h-8 w-8 text-[var(--gs-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <div class="gs-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-[var(--gs-muted)]">Total Watch Time</p>
                            <p class="mt-2 text-3xl font-bold text-[var(--gs-text)]">
                                {{ analytics.overview.total_watch_time_hours.toLocaleString() }}h
                            </p>
                            <p class="mt-1 text-sm text-[var(--gs-primary)]">
                                +{{ analytics.overview.watch_time_this_period_hours.toLocaleString() }}h this period
                            </p>
                        </div>
                        <div class="rounded-full bg-[var(--gs-accent-soft)] p-3">
                            <svg class="h-8 w-8 text-[var(--gs-accent)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <div class="gs-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-[var(--gs-muted)]">Unique Viewers</p>
                            <p class="mt-2 text-3xl font-bold text-[var(--gs-text)]">
                                {{ analytics.overview.unique_viewers.toLocaleString() }}
                            </p>
                            <p class="mt-1 text-sm text-[var(--gs-primary)]">
                                +{{ analytics.overview.unique_viewers_this_period.toLocaleString() }} this period
                            </p>
                        </div>
                        <div class="rounded-full bg-blue-500/15 p-3">
                            <svg class="h-8 w-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <div class="mb-8 grid grid-cols-1 gap-4 lg:grid-cols-2">
                <!-- Daily Views Chart -->
                <div class="gs-card p-6">
                    <h2 class="mb-4 text-lg font-semibold text-[var(--gs-text)]">Daily Views</h2>
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
                                    class="w-full rounded-t bg-[var(--gs-primary)]"
                                ></div>
                                <span class="mt-2 text-xs text-[var(--gs-muted)]">
                                    {{ new Date(day.date).toLocaleDateString('en-US', { weekday: 'short' }) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Categories -->
                <div class="gs-card p-6">
                    <h2 class="mb-4 text-lg font-semibold text-[var(--gs-text)]">Top Categories</h2>
                    <div class="space-y-4">
                        <div v-for="category in analytics.top_categories" :key="category.name" class="flex items-center">
                            <div class="flex-1">
                                <div class="mb-1 flex items-center justify-between">
                                    <span class="text-sm font-medium text-[var(--gs-text)]">{{ category.name }}</span>
                                    <span class="text-sm text-[var(--gs-muted)]">{{ category.view_count.toLocaleString() }} views</span>
                                </div>
                                <div class="gs-progress-track h-2">
                                    <div
                                        :style="{ width: `${(category.view_count / maxCategoryViews) * 100}%` }"
                                        class="gs-progress-fill h-full rounded-full"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Metrics -->
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <!-- Completion Rate -->
                <div class="gs-card p-6">
                    <h3 class="mb-2 text-sm font-medium text-[var(--gs-muted)]">Completion Rate</h3>
                    <p class="text-3xl font-bold text-[var(--gs-text)]">{{ analytics.overview.completion_rate }}%</p>
                    <p class="mt-1 text-sm text-[var(--gs-muted)]">Average video completion</p>
                </div>

                <!-- Avg Watch Duration -->
                <div class="gs-card p-6">
                    <h3 class="mb-2 text-sm font-medium text-[var(--gs-muted)]">Avg Watch Duration</h3>
                    <p class="text-3xl font-bold text-[var(--gs-text)]">
                        {{ Math.round(analytics.overview.avg_watch_duration_seconds / 60) }}m
                    </p>
                    <p class="mt-1 text-sm text-[var(--gs-muted)]">Per viewing session</p>
                </div>

                <!-- Published Videos -->
                <div class="gs-card p-6">
                    <h3 class="mb-2 text-sm font-medium text-[var(--gs-muted)]">Published Videos</h3>
                    <p class="text-3xl font-bold text-[var(--gs-text)]">{{ analytics.overview.published_videos }}</p>
                    <p class="mt-1 text-sm text-[var(--gs-muted)]">
                        {{ Math.round((analytics.overview.published_videos / analytics.overview.total_videos) * 100) }}% of total
                    </p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
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
