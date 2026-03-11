<template>
    <div class="group relative overflow-hidden rounded-lg bg-white shadow-sm transition-all hover:shadow-lg">
        <!-- Thumbnail -->
        <div class="relative aspect-video overflow-hidden bg-gray-900">
            <img
                v-if="video.thumbnail_url"
                :src="video.thumbnail_url"
                :alt="video.title"
                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
            />
            <div v-else class="flex h-full w-full items-center justify-center bg-gray-800">
                <svg class="h-16 w-16 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"
                    />
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </svg>
            </div>

            <!-- Duration Badge -->
            <div
                v-if="video.duration"
                class="absolute bottom-2 right-2 rounded bg-black/80 px-2 py-1 text-xs font-medium text-white"
            >
                {{ formatDuration(video.duration) }}
            </div>

            <!-- Access Level Badge -->
            <div
                :class="[accessBadge.color, 'absolute left-2 top-2 rounded px-2 py-1 text-xs font-medium text-white']"
            >
                {{ accessBadge.text }}
            </div>

            <!-- Play Overlay -->
            <div
                class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition-opacity group-hover:opacity-100"
            >
                <div class="rounded-full bg-white/90 p-4">
                    <svg class="h-8 w-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M8 5v14l11-7z" />
                    </svg>
                </div>
            </div>

            <!-- Progress Bar (if watching) -->
            <div v-if="watchProgress && watchProgress > 0" class="absolute bottom-0 left-0 right-0 h-1 bg-gray-700">
                <div :style="{ width: `${watchProgress}%` }" class="h-full bg-blue-600"></div>
            </div>
        </div>

        <!-- Content -->
        <div class="p-4">
            <!-- Title -->
            <h3 class="mb-1 line-clamp-2 text-base font-semibold text-gray-900">
                {{ video.title }}
            </h3>

            <!-- Metadata -->
            <div class="mb-2 flex items-center gap-2 text-sm text-gray-600">
                <span>{{ contentTypeLabel }}</span>
                <span v-if="video.view_count" class="flex items-center gap-1">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    {{ formatViews(video.view_count) }}
                </span>
            </div>

            <!-- Description -->
            <p v-if="showDescription" class="mb-3 line-clamp-2 text-sm text-gray-600">
                {{ video.description }}
            </p>

            <!-- Creator -->
            <div v-if="video.creator" class="flex items-center gap-2 text-sm text-gray-600">
                <div class="h-6 w-6 overflow-hidden rounded-full bg-gray-200">
                    <img
                        v-if="video.creator.avatar"
                        :src="video.creator.avatar"
                        :alt="video.creator.name"
                        class="h-full w-full object-cover"
                    />
                    <div v-else class="flex h-full w-full items-center justify-center text-xs font-medium text-gray-600">
                        {{ video.creator.name.charAt(0).toUpperCase() }}
                    </div>
                </div>
                <span>{{ video.creator.name }}</span>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { Video } from '@/types/growstream';
import { useGrowStream } from '@/composables/useGrowStream';

interface Props {
    video: Video;
    watchProgress?: number;
    showDescription?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    watchProgress: 0,
    showDescription: false,
});

const { formatDuration, getAccessLevelBadge, getContentTypeLabel } = useGrowStream();

const accessBadge = computed(() => getAccessLevelBadge(props.video.access_level));
const contentTypeLabel = computed(() => getContentTypeLabel(props.video.content_type));

const formatViews = (views: number): string => {
    if (views >= 1000000) {
        return `${(views / 1000000).toFixed(1)}M`;
    }
    if (views >= 1000) {
        return `${(views / 1000).toFixed(1)}K`;
    }
    return views.toString();
};
</script>
