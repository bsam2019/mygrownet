<template>
    <GrowStreamLayout :title="`${creator?.display_name || 'Creator'} - GrowStream`">
        <div v-if="creator" class="mx-auto max-w-6xl">
            <!-- Banner -->
            <div class="relative mb-6 overflow-hidden rounded-[var(--gs-radius)] border border-[var(--gs-border)]">
                <div
                    v-if="creator.banner_url"
                    class="h-48 w-full bg-cover bg-center sm:h-64"
                    :style="{ backgroundImage: `url(${creator.banner_url})` }"
                ></div>
                <div v-else class="h-48 w-full bg-gradient-to-br from-[var(--gs-primary)]/40 via-[#065f46] to-[#022c22] sm:h-64"></div>

                <!-- Profile Card Overlap -->
                <div class="relative -mt-16 px-6 pb-6 sm:-mt-20">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div class="flex items-end gap-4">
                            <div class="h-28 w-28 shrink-0 overflow-hidden rounded-2xl border-4 border-[var(--gs-bg)] bg-[var(--gs-bg-elevated)] sm:h-32 sm:w-32">
                                <img
                                    v-if="creator.avatar_url"
                                    :src="creator.avatar_url"
                                    :alt="creator.display_name"
                                    class="h-full w-full object-cover"
                                />
                                <div v-else class="flex h-full w-full items-center justify-center text-4xl font-bold text-[var(--gs-primary)]">
                                    {{ (creator.display_name || 'C').charAt(0).toUpperCase() }}
                                </div>
                            </div>
                            <div class="pb-1">
                                <div class="flex items-center gap-2">
                                    <h1 class="text-2xl font-bold text-[var(--gs-text)] sm:text-3xl">{{ creator.display_name }}</h1>
                                    <svg
                                        v-if="creator.is_verified"
                                        class="h-5 w-5 text-[var(--gs-primary)]"
                                        fill="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M8.603 3.799A4.49 4.49 0 0112 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 013.498 1.307 4.491 4.491 0 011.307 3.497A4.49 4.49 0 0121.75 12a4.49 4.49 0 01-1.549 3.397 4.491 4.491 0 01-1.307 3.497 4.491 4.491 0 01-3.497 1.307A4.49 4.49 0 0112 21.75a4.49 4.49 0 01-3.397-1.549 4.49 4.49 0 01-3.498-1.306 4.491 4.491 0 01-1.307-3.498A4.49 4.49 0 012.25 12c0-1.357.6-2.573 1.549-3.397a4.49 4.49 0 011.307-3.497 4.49 4.49 0 013.497-1.307zm7.007 6.387a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                                <p class="mt-1 text-sm text-[var(--gs-muted)]">
                                    {{ creator.subscriber_count ?? 0 }} subscribers · {{ creator.total_videos ?? 0 }} videos · {{ formatViews(creator.total_views ?? 0) }} views
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 pb-1">
                            <span v-if="creator.creator_tier" class="gs-chip gs-chip-accent">
                                {{ tierLabel(creator.creator_tier) }}
                            </span>
                            <Link
                                v-if="creator.website_url"
                                :href="creator.website_url"
                                target="_blank"
                                rel="noopener"
                                class="gs-btn gs-btn-outline"
                            >
                                Website
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- About -->
            <div v-if="creator.bio" class="mb-10 max-w-3xl">
                <h2 class="mb-2 text-lg font-semibold text-[var(--gs-text)]">About</h2>
                <p class="whitespace-pre-line text-[var(--gs-muted)]">{{ creator.bio }}</p>
            </div>

            <!-- Series -->
            <div v-if="(series || []).length > 0" class="mb-10">
                <h2 class="mb-4 text-xl font-semibold text-[var(--gs-text)]">Series</h2>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    <Link
                        v-for="s in (series || [])"
                        :key="s.id"
                        :href="route('growstream.series.detail', { slug: s.slug })"
                        class="gs-card gs-card-hover overflow-hidden"
                    >
                        <div class="relative aspect-video bg-[var(--gs-bg-elevated)]">
                            <img
                                v-if="s.poster_url"
                                :src="s.poster_url"
                                :alt="s.title"
                                class="h-full w-full object-cover"
                            />
                            <div v-else class="flex h-full w-full items-center justify-center">
                                <svg class="h-10 w-10 text-[var(--gs-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h18M3 16h18" />
                                </svg>
                            </div>
                        </div>
                        <div class="p-3">
                            <h3 class="line-clamp-1 font-semibold text-[var(--gs-text)]">{{ s.title }}</h3>
                            <p class="text-sm text-[var(--gs-muted)]">{{ s.total_episodes }} episodes</p>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Videos -->
            <div v-if="(videos || []).length > 0">
                <h2 class="mb-4 text-xl font-semibold text-[var(--gs-text)]">Videos</h2>
                <VideoGrid :videos="videos || []" />
            </div>

            <!-- Empty catalogue -->
            <div v-if="!((videos || []).length > 0 || (series || []).length > 0)" class="gs-card p-12 text-center">
                <h3 class="mb-2 text-lg font-semibold text-[var(--gs-text)]">No content yet</h3>
                <p class="text-sm text-[var(--gs-muted)]">This creator hasn't published any content yet.</p>
            </div>
        </div>
    </GrowStreamLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import GrowStreamLayout from '@/Layouts/GrowStreamLayout.vue';
import VideoGrid from '@/Components/GrowStream/VideoGrid.vue';
import type { Video, VideoSeries, CreatorProfile } from '@/types/growstream';

interface Props {
    creator?: CreatorProfile;
    videos?: Video[];
    series?: VideoSeries[];
}

withDefaults(defineProps<Props>(), {
    creator: undefined,
    videos: () => [],
    series: () => [],
});

const formatViews = (views: number): string => {
    if (views >= 1000000) {
        return `${(views / 1000000).toFixed(1)}M`;
    }
    if (views >= 1000) {
        return `${(views / 1000).toFixed(1)}K`;
    }
    return views.toString();
};

const tierLabel = (tier: string): string => {
    const map: Record<string, string> = {
        bronze: 'Bronze Creator',
        silver: 'Silver Creator',
        gold: 'Gold Creator',
        platinum: 'Platinum Creator',
    };
    return map[tier.toLowerCase()] ?? 'Creator';
};
</script>
