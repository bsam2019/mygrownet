<template>
    <AppLayout title="Creator Analytics - GrowStream">
        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Creator Analytics</h1>
                <p class="mt-2 text-gray-600">Performance across your content</p>
            </div>

            <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg bg-white p-6 shadow">
                    <p class="text-sm font-medium text-gray-600">Total Videos</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ stats.total_videos }}</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow">
                    <p class="text-sm font-medium text-gray-600">Published</p>
                    <p class="mt-2 text-3xl font-bold text-blue-600">{{ stats.published_videos }}</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow">
                    <p class="text-sm font-medium text-gray-600">Total Views</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ stats.total_views.toLocaleString() }}</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow">
                    <p class="text-sm font-medium text-gray-600">Watch Time (hrs)</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ stats.total_watch_time_hours }}</p>
                </div>
            </div>

            <div class="rounded-lg bg-white shadow">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Top Videos</h2>
                </div>
                <div v-if="topVideos.length === 0" class="px-6 py-12 text-center">
                    <p class="text-sm text-gray-600">No videos yet. Publish content to see analytics.</p>
                </div>
                <ul v-else class="divide-y divide-gray-200">
                    <li v-for="video in topVideos" :key="video.id" class="flex items-center px-6 py-4">
                        <img
                            v-if="video.thumbnail_url"
                            :src="video.thumbnail_url"
                            :alt="video.title"
                            class="h-14 w-24 rounded object-cover"
                        />
                        <div class="ml-4 flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ video.title }}</p>
                            <p class="text-xs text-gray-500">
                                {{ video.view_count }} views · {{ Math.round(video.average_watch_duration || 0) }}s avg watch
                            </p>
                        </div>
                        <span
                            :class="video.is_published ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700'"
                            class="rounded-full px-2 py-0.5 text-xs font-medium"
                        >
                            {{ video.is_published ? 'Published' : 'Unpublished' }}
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';

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
