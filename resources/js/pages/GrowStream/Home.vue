<template>
    <AppLayout title="GrowStream - Learn and Grow">
        <div class="min-h-screen bg-gray-50">
            <!-- Hero Section -->
            <div class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800">
                <div class="absolute inset-0 bg-black/20"></div>
                <div class="relative mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
                    <div class="text-center">
                        <h1 class="mb-6 text-5xl font-bold text-white md:text-6xl">
                            Learn. Grow. Succeed.
                        </h1>
                        <p class="mb-8 text-xl text-blue-100 md:text-2xl">
                            Access thousands of educational videos to advance your skills
                        </p>
                        <div class="flex justify-center gap-4">
                            <Link
                                :href="route('growstream.browse')"
                                class="rounded-lg bg-white px-8 py-3 text-lg font-semibold text-blue-600 shadow-lg transition hover:bg-blue-50"
                            >
                                Browse Videos
                            </Link>
                            <Link
                                v-if="(continueWatching || []).length > 0"
                                :href="route('growstream.my-videos')"
                                class="rounded-lg border-2 border-white px-8 py-3 text-lg font-semibold text-white transition hover:bg-white/10"
                            >
                                Continue Watching
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
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
                <div class="mb-12">
                    <h2 class="mb-6 text-2xl font-bold text-gray-900">Browse by Category</h2>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                        <Link
                            v-for="category in (categories || [])"
                            :key="category.id"
                            :href="route('growstream.browse', { category: category.slug })"
                            class="group relative overflow-hidden rounded-lg bg-white p-6 shadow transition hover:shadow-lg"
                        >
                            <div class="flex items-center gap-3">
                                <div class="rounded-lg bg-blue-100 p-3 transition group-hover:bg-blue-200">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 group-hover:text-blue-600">
                                        {{ category.name }}
                                    </h3>
                                    <p class="text-sm text-gray-600">{{ category.videos_count || 0 }} videos</p>
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
                <div class="rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 p-12 text-center text-white">
                    <h2 class="mb-4 text-3xl font-bold">Ready to Start Learning?</h2>
                    <p class="mb-8 text-lg text-blue-100">
                        Join thousands of learners advancing their skills with GrowStream
                    </p>
                    <Link
                        :href="route('growstream.browse')"
                        class="inline-block rounded-lg bg-white px-8 py-3 text-lg font-semibold text-blue-600 shadow-lg transition hover:bg-blue-50"
                    >
                        Explore All Videos
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
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
