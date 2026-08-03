<template>
    <div class="group relative overflow-hidden rounded-[var(--gs-radius)] bg-[var(--gs-card)] transition-all hover:shadow-xl">
        <!-- Thumbnail -->
        <div class="relative aspect-video overflow-hidden bg-[var(--gs-bg-elevated)]">
            <img
                v-if="video.thumbnail_url"
                :src="video.thumbnail_url"
                :alt="video.title"
                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
            />
            <div v-else class="flex h-full w-full items-center justify-center bg-[var(--gs-bg-elevated)]">
                <svg class="h-16 w-16 text-[var(--gs-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 transition-opacity group-hover:opacity-100"
            >
                <div class="rounded-full bg-[var(--gs-primary)]/90 p-4">
                    <svg class="h-8 w-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M8 5v14l11-7z" />
                    </svg>
                </div>
            </div>

            <!-- Progress Bar (if watching) -->
            <div v-if="watchProgress && watchProgress > 0" class="absolute bottom-0 left-0 right-0 h-1 bg-[var(--gs-border)]">
                <div :style="{ width: `${watchProgress}%` }" class="h-full gs-progress-fill"></div>
            </div>
        </div>

        <!-- Content -->
        <div class="p-4">
            <!-- Title -->
            <h3 class="mb-1 line-clamp-2 text-base font-semibold text-[var(--gs-text)]">
                {{ video.title }}
            </h3>

            <!-- Metadata -->
            <div class="mb-2 flex items-center gap-2 text-sm text-[var(--gs-muted)]">
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
            <p v-if="showDescription" class="mb-3 line-clamp-2 text-sm text-[var(--gs-muted)]">
                {{ video.description }}
            </p>

            <!-- Creator -->
            <div v-if="video.creator" class="flex items-center gap-2 text-sm text-[var(--gs-muted)]">
                <div class="h-6 w-6 overflow-hidden rounded-full bg-[var(--gs-bg-elevated)]">
                    <img
                        v-if="video.creator.avatar_url"
                        :src="video.creator.avatar_url"
                        :alt="video.creator.display_name"
                        class="h-full w-full object-cover"
                    />
                    <div v-else class="flex h-full w-full items-center justify-center text-xs font-medium text-[var(--gs-muted)]">
                        {{ (video.creator.display_name || 'C').charAt(0).toUpperCase() }}
                    </div>
                </div>
                <Link
                    :href="route('growstream.creator.profile', { slug: String(video.creator.id) })"
                    class="hover:text-[var(--gs-accent)]"
                >
                    {{ video.creator.display_name }}
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
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
