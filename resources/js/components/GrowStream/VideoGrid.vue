<template>
    <div>
        <!-- Header -->
        <div v-if="title" class="mb-6 flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">{{ title }}</h2>
            <Link
                v-if="viewAllLink"
                :href="viewAllLink"
                class="text-sm font-medium text-blue-600 hover:text-blue-700"
            >
                View All →
            </Link>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <div v-for="i in skeletonCount" :key="i" class="animate-pulse">
                <div class="aspect-video rounded-lg bg-gray-200"></div>
                <div class="mt-3 h-4 rounded bg-gray-200"></div>
                <div class="mt-2 h-3 w-2/3 rounded bg-gray-200"></div>
            </div>
        </div>

        <!-- Empty State -->
        <div
            v-else-if="!videos || videos.length === 0"
            class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 py-12"
        >
            <svg class="mb-4 h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
                />
            </svg>
            <p class="text-lg font-medium text-gray-900">{{ emptyMessage }}</p>
            <p class="mt-1 text-sm text-gray-600">{{ emptySubMessage }}</p>
        </div>

        <!-- Video Grid -->
        <div v-else class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <Link
                v-for="video in videos"
                :key="video.id"
                :href="route('growstream.video.detail', video.slug)"
                class="block transition-transform hover:scale-[1.02]"
            >
                <VideoCard :video="video" :watch-progress="getWatchProgress(video.id)" :show-description="showDescription" />
            </Link>
        </div>

        <!-- Pagination -->
        <div v-if="showPagination && meta" class="mt-8 flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Showing {{ (meta.current_page - 1) * meta.per_page + 1 }} to
                {{ Math.min(meta.current_page * meta.per_page, meta.total) }} of {{ meta.total }} videos
            </div>
            <div class="flex gap-2">
                <button
                    :disabled="meta.current_page === 1"
                    @click="$emit('page-change', meta.current_page - 1)"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Previous
                </button>
                <button
                    :disabled="meta.current_page === meta.last_page"
                    @click="$emit('page-change', meta.current_page + 1)"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Next
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { Video } from '@/types/growstream';
import VideoCard from './VideoCard.vue';

interface Props {
    videos?: Video[];
    title?: string;
    viewAllLink?: string;
    loading?: boolean;
    skeletonCount?: number;
    emptyMessage?: string;
    emptySubMessage?: string;
    showDescription?: boolean;
    showPagination?: boolean;
    meta?: {
        current_page: number;
        per_page: number;
        total: number;
        last_page: number;
    };
    watchProgress?: Record<number, number>;
}

const props = withDefaults(defineProps<Props>(), {
    videos: () => [],
    loading: false,
    skeletonCount: 8,
    emptyMessage: 'No videos found',
    emptySubMessage: 'Check back later for new content',
    showDescription: false,
    showPagination: false,
});

defineEmits<{
    (e: 'page-change', page: number): void;
}>();

const getWatchProgress = (videoId: number): number => {
    return props.watchProgress?.[videoId] || 0;
};
</script>
