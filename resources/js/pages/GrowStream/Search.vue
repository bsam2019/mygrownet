<template>
    <GrowStreamLayout title="Search - GrowStream">
        <div class="mx-auto max-w-5xl">
            <!-- Search Header -->
            <div class="mb-8">
                <h1 class="mb-4 text-3xl font-bold text-[var(--gs-text)]">Search</h1>

                <!-- Search Input -->
                <div class="relative">
                    <svg
                        class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-[var(--gs-muted)]"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"
                        />
                    </svg>
                    <input
                        v-model="query"
                        type="search"
                        class="gs-input py-4 pl-12 pr-16 text-base"
                        placeholder="Search titles, creators, genres..."
                        @keyup.enter="submitSearch"
                    />
                    <button
                        v-if="query"
                        class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full p-2 text-[var(--gs-muted)] hover:bg-[var(--gs-card)] hover:text-[var(--gs-text)]"
                        aria-label="Clear search"
                        @click="clearSearch"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Trending (empty query) -->
            <div v-if="!props.query && (trending || []).length > 0">
                <h2 class="mb-4 text-xl font-semibold text-[var(--gs-text)]">Trending Now</h2>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    <Link
                        v-for="v in (trending || [])"
                        :key="v.id"
                        :href="route('growstream.video.detail', { slug: v.slug })"
                    >
                        <VideoCard :video="v" />
                    </Link>
                </div>
            </div>

            <!-- Results -->
            <template v-else>
                <!-- Categories match -->
                <div v-if="(categories || []).length > 0" class="mb-8">
                    <h2 class="mb-4 text-xl font-semibold text-[var(--gs-text)]">Categories</h2>
                    <div class="flex flex-wrap gap-2">
                        <Link
                            v-for="category in (categories || [])"
                            :key="category.id"
                            :href="route('growstream.browse', { category: category.slug })"
                            class="gs-chip gs-chip-primary"
                        >
                            {{ category.name }}
                            <span v-if="category.videos_count" class="ml-1 opacity-70">· {{ category.videos_count }}</span>
                        </Link>
                    </div>
                </div>

                <!-- Creators match -->
                <div v-if="(creators || []).length > 0" class="mb-8">
                    <h2 class="mb-4 text-xl font-semibold text-[var(--gs-text)]">Creators</h2>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <Link
                            v-for="creator in (creators || [])"
                            :key="creator.id"
                            :href="route('growstream.creator.profile', { slug: String(creator.id) })"
                            class="gs-card gs-card-hover flex items-center gap-3 p-4"
                        >
                            <div class="h-12 w-12 shrink-0 overflow-hidden rounded-full bg-[var(--gs-bg-elevated)]">
                                <img
                                    v-if="creator.avatar_url"
                                    :src="creator.avatar_url"
                                    :alt="creator.display_name"
                                    class="h-full w-full object-cover"
                                />
                                <div v-else class="flex h-full w-full items-center justify-center text-lg font-semibold text-[var(--gs-primary)]">
                                    {{ (creator.display_name || 'C').charAt(0).toUpperCase() }}
                                </div>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <p class="truncate font-semibold text-[var(--gs-text)]">{{ creator.display_name }}</p>
                                    <svg
                                        v-if="creator.is_verified"
                                        class="h-4 w-4 shrink-0 text-[var(--gs-primary)]"
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
                                <p v-if="creator.bio" class="line-clamp-1 text-sm text-[var(--gs-muted)]">{{ creator.bio }}</p>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Videos match -->
                <div v-if="(videos || []).length > 0" class="mb-8">
                    <h2 class="mb-4 text-xl font-semibold text-[var(--gs-text)]">
                        Videos <span class="text-[var(--gs-muted)]">({{ videos.length }})</span>
                    </h2>
                    <VideoGrid :videos="videos || []" />
                </div>

                <!-- Empty state -->
                <div v-if="!((videos || []).length > 0 || (creators || []).length > 0 || (categories || []).length > 0)" class="gs-card p-12 text-center">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[var(--gs-bg-elevated)]">
                        <svg class="h-8 w-8 text-[var(--gs-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"
                            />
                        </svg>
                    </div>
                    <h3 class="mb-2 text-lg font-semibold text-[var(--gs-text)]">Nothing found</h3>
                    <p class="mx-auto max-w-md text-sm text-[var(--gs-muted)]">
                        We couldn't find anything matching “{{ props.query }}”. Try a different title, creator, or genre.
                    </p>
                </div>
            </template>
        </div>
    </GrowStreamLayout>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import GrowStreamLayout from '@/Layouts/GrowStreamLayout.vue';
import VideoCard from '@/Components/GrowStream/VideoCard.vue';
import VideoGrid from '@/Components/GrowStream/VideoGrid.vue';
import { useGrowStreamMetrics } from '@/composables/useGrowStreamMetrics';
import type { Video, VideoCategory, CreatorProfile } from '@/types/growstream';

interface Props {
    query?: string;
    videos?: Video[];
    creators?: CreatorProfile[];
    categories?: VideoCategory[];
    trending?: Video[];
}

const props = withDefaults(defineProps<Props>(), {
    query: '',
    videos: () => [],
    creators: () => [],
    categories: () => [],
    trending: () => [],
});

const query = ref(props.query);

const metrics = useGrowStreamMetrics();

const submitSearch = () => {
    const q = query.value.trim();
    if (q) {
        metrics.trackSearch(q);
    }
    router.get(route('growstream.search'), { q: q || undefined }, { preserveState: true, replace: true });
};

const clearSearch = () => {
    query.value = '';
    router.get(route('growstream.search'), {}, { preserveState: true, replace: true });
};

watch(
    () => props.query,
    (val) => {
        query.value = val ?? '';
    }
);
</script>
