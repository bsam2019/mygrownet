<template>
    <CreatorStudioLayout title="Creator Analytics - GrowStream">
        <div>
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-[var(--gs-text)]">Creator Analytics</h1>
                <p class="mt-2 text-[var(--gs-muted)]">Performance across your content</p>
            </div>

            <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="gs-card p-6">
                    <p class="text-sm font-medium text-[var(--gs-muted)]">Total Videos</p>
                    <p class="mt-2 text-3xl font-bold text-[var(--gs-text)]">{{ stats.total_videos }}</p>
                </div>
                <div class="gs-card p-6">
                    <p class="text-sm font-medium text-[var(--gs-muted)]">Published</p>
                    <p class="mt-2 text-3xl font-bold text-[var(--gs-primary)]">{{ stats.published_videos }}</p>
                </div>
                <div class="gs-card p-6">
                    <p class="text-sm font-medium text-[var(--gs-muted)]">Total Views</p>
                    <p class="mt-2 text-3xl font-bold text-[var(--gs-text)]">{{ stats.total_views.toLocaleString() }}</p>
                </div>
                <div class="gs-card p-6">
                    <p class="text-sm font-medium text-[var(--gs-muted)]">Watch Time (hrs)</p>
                    <p class="mt-2 text-3xl font-bold text-[var(--gs-text)]">{{ stats.total_watch_time_hours }}</p>
                </div>
            </div>

            <div class="gs-surface overflow-hidden">
                <div class="border-b border-[var(--gs-border)] px-6 py-4">
                    <h2 class="text-lg font-semibold text-[var(--gs-text)]">Top Videos</h2>
                </div>
                <div v-if="topVideos.length === 0" class="px-6 py-12 text-center">
                    <p class="text-sm text-[var(--gs-muted)]">No videos yet. Publish content to see analytics.</p>
                </div>
                <ul v-else class="divide-y divide-[var(--gs-border)]">
                    <li v-for="video in topVideos" :key="video.id" class="flex items-center px-6 py-4">
                        <img
                            v-if="video.thumbnail_url"
                            :src="video.thumbnail_url"
                            :alt="video.title"
                            class="h-14 w-24 rounded object-cover"
                        />
                        <div v-else class="flex h-14 w-24 items-center justify-center rounded bg-[var(--gs-bg-elevated)]">
                            <svg class="h-6 w-6 text-[var(--gs-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-sm font-medium text-[var(--gs-text)]">{{ video.title }}</p>
                            <p class="text-xs text-[var(--gs-muted)]">
                                {{ video.view_count }} views · {{ Math.round(video.average_watch_duration || 0) }}s avg watch
                            </p>
                        </div>
                        <span
                            :class="video.is_published ? 'gs-chip gs-chip-primary' : 'gs-chip bg-[var(--gs-bg-elevated)] text-[var(--gs-muted)]'"
                        >
                            {{ video.is_published ? 'Published' : 'Unpublished' }}
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </CreatorStudioLayout>
</template>

<script setup lang="ts">
import CreatorStudioLayout from '@/Layouts/CreatorStudioLayout.vue';

interface TopVideo {
    id: number;
    title: string;
    thumbnail_url: string | null;
    view_count: number;
    average_watch_duration: number;
    is_published: boolean;
}

interface Props {
    stats: {
        total_videos: number;
        published_videos: number;
        total_views: number;
        total_watch_time_hours: number;
        avg_watch_time_seconds: number;
    };
    topVideos: TopVideo[];
}

defineProps<Props>();
</script>
