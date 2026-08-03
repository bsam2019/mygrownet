<template>
    <GrowStreamLayout title="GrowStream - Watch Zambian Entertainment">
        <!-- Hero Section -->
        <div class="relative overflow-hidden rounded-[var(--gs-radius)] border border-[var(--gs-border)]">
            <div class="relative overflow-hidden bg-gradient-to-br from-[var(--gs-primary)] via-[#065f46] to-[#022c22]">
                <div class="absolute inset-0 bg-black/30"></div>
                <div class="relative mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
                    <div class="max-w-2xl">
                        <p class="mb-4 inline-flex items-center gap-2 rounded-full bg-black/30 px-3 py-1 text-sm font-medium text-[var(--gs-accent)]">
                            Made in Zambia
                        </p>
                        <h1 class="mb-6 text-4xl font-bold text-white md:text-6xl">
                            Watch. Laugh. Binge.
                        </h1>
                        <p class="mb-8 text-lg text-white/80 md:text-2xl">
                            Zambian movies, comedy, skits, dramas, soaps, and series — premium local entertainment in one place.
                        </p>
                        <div class="flex flex-wrap gap-4">
                            <a
                                :href="route('growstream.browse')"
                                class="gs-btn gs-btn-accent px-8 py-3 text-lg"
                            >
                                Browse Videos
                            </a>
                            <a
                                v-if="(continueWatching || []).length > 0"
                                :href="route('growstream.my-videos')"
                                class="gs-btn gs-btn-outline border-white/50 px-8 py-3 text-lg text-white hover:bg-white/10"
                            >
                                Continue Watching
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-12">
            <!-- Continue Watching -->
            <div v-if="(continueWatching || []).length > 0" class="mb-12">
                <VideoGrid
                    :videos="continueWatchingVideos"
                    title="Continue Watching"
                    :view-all-link="route('growstream.my-videos')"
                    :watch-progress="continueWatchingProgress"
                />
            </div>

            <!-- Featured Videos -->
            <div v-if="(featuredVideos || []).length > 0" class="mb-12">
                <VideoGrid
                    :videos="featuredVideos || []"
                    title="Featured Content"
                    :view-all-link="route('growstream.browse', { featured: true })"
                    :show-description="true"
                />
            </div>

            <!-- Trending Videos -->
            <div v-if="(trendingVideos || []).length > 0" class="mb-12">
                <VideoGrid
                    :videos="trendingVideos || []"
                    title="Trending Now"
                    :view-all-link="route('growstream.browse', { sort_by: 'view_count' })"
                />
            </div>

            <!-- Categories -->
            <div v-if="(categories || []).length > 0" class="mb-12">
                <h2 class="mb-6 text-2xl font-bold text-[var(--gs-text)]">Browse by Category</h2>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    <Link
                        v-for="category in (categories || [])"
                        :key="category.id"
                        :href="route('growstream.browse', { category: category.slug })"
                        class="group gs-card gs-card-hover p-6"
                    >
                        <div class="flex items-center gap-3">
                            <div class="rounded-[var(--gs-radius)] bg-[var(--gs-primary-soft)] p-3 transition group-hover:bg-[var(--gs-primary)]/25">
                                <svg class="h-6 w-6 text-[var(--gs-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-[var(--gs-text)] group-hover:text-[var(--gs-primary)]">
                                    {{ category.name }}
                                </h3>
                                <p class="text-sm text-[var(--gs-muted)]">{{ category.videos_count || 0 }} videos</p>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Recent Uploads -->
            <div v-if="(recentVideos || []).length > 0" class="mb-12">
                <VideoGrid
                    :videos="recentVideos || []"
                    title="Recently Added"
                    :view-all-link="route('growstream.browse', { sort_by: 'created_at' })"
                />
            </div>

            <!-- CTA Section -->
            <div class="rounded-[var(--gs-radius)] border border-[var(--gs-border)] bg-gradient-to-r from-[var(--gs-primary)] to-[#065f46] p-12 text-center text-white">
                <h2 class="mb-4 text-3xl font-bold">Ready to Start Watching?</h2>
                <p class="mb-8 text-lg text-white/80">
                    Stream premium content on demand — wherever you are.
                </p>
                <a
                    :href="route('growstream.browse')"
                    class="inline-block gs-btn gs-btn-accent px-8 py-3 text-lg"
                >
                    Explore All Videos
                </a>
            </div>
        </div>
    </GrowStreamLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import GrowStreamLayout from '@/Layouts/GrowStreamLayout.vue';
import VideoGrid from '@/Components/GrowStream/VideoGrid.vue';
import type { Video, VideoCategory, WatchHistory } from '@/types/growstream';

interface Props {
    featuredVideos?: Video[];
    trendingVideos?: Video[];
    recentVideos?: Video[];
    categories?: VideoCategory[];
    continueWatching?: WatchHistory[];
}

const props = withDefaults(defineProps<Props>(), {
    featuredVideos: () => [],
    trendingVideos: () => [],
    recentVideos: () => [],
    categories: () => [],
    continueWatching: () => [],
});

const continueWatchingVideos = computed(() => {
    return (props.continueWatching || []).map((h) => h.video).filter((v): v is Video => v !== undefined);
});

const continueWatchingProgress = computed(() => {
    const progress: Record<number, number> = {};
    (props.continueWatching || []).forEach((h) => {
        if (h.video) {
            progress[h.video.id] = h.progress_percentage;
        }
    });
    return progress;
});
</script>
