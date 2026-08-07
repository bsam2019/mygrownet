<template>
    <GrowStreamLayout title="Search Results - GrowStream">
        <main class="flex-1 w-full max-w-7xl mx-auto flex flex-col">
            <!-- Search Bar -->
            <div class="px-margin-mobile md:px-margin-desktop py-8 border-b border-surface-container-high">
                <div class="relative max-w-2xl">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant" aria-hidden="true">search</span>
                    <input
                        v-model="query"
                        class="w-full pl-12 pr-4 py-3 rounded-full border border-outline-variant bg-surface-bright focus:border-primary focus:ring-1 focus:ring-primary outline-none text-body-md text-on-surface placeholder-on-surface-variant transition-colors"
                        placeholder="Search creators, series, videos..."
                        @keyup.enter="submitSearch"
                    />
                    <button v-if="query" class="absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-primary transition-colors" aria-label="Clear search" @click="clearSearch">
                        <span class="material-symbols-outlined" aria-hidden="true">close</span>
                    </button>
                </div>
                <!-- Filter Chips -->
                <div class="flex gap-3 mt-6 overflow-x-auto pb-2" style="scrollbar-width:none;">
                    <button class="whitespace-nowrap px-4 py-2 rounded-full bg-primary text-on-primary font-label-md text-label-md transition-colors">All Results</button>
                    <button class="whitespace-nowrap px-4 py-2 rounded-full bg-surface-container-low border border-outline-variant text-on-surface font-label-md text-label-md hover:bg-surface-container transition-colors" @click="goBrowse()">Category</button>
                    <button class="whitespace-nowrap px-4 py-2 rounded-full bg-surface-container-low border border-outline-variant text-on-surface font-label-md text-label-md hover:bg-surface-container transition-colors" @click="goBrowse('created_at')">Recent</button>
                    <button class="whitespace-nowrap px-4 py-2 rounded-full bg-surface-container-low border border-outline-variant text-on-surface font-label-md text-label-md hover:bg-surface-container transition-colors" @click="goBrowse('view_count')">Most Watched</button>
                </div>
            </div>

            <div class="px-margin-mobile md:px-margin-desktop py-8 space-y-12 flex-1">
                <!-- Empty query: Trending -->
                <section v-if="!query && trending.length > 0">
                    <h3 class="font-headline-md text-headline-md mb-6 text-on-surface">Trending Now</h3>
                    <div class="flex gap-6 overflow-x-auto pb-4" style="scrollbar-width:none;">
                        <Link
                            v-for="v in trending"
                            :key="v.id"
                            :href="route('growstream.video.detail', { slug: v.slug })"
                            class="snap-start shrink-0 w-40"
                        >
                            <div class="relative aspect-video rounded-lg overflow-hidden bg-surface-container-highest">
                                <div class="bg-cover bg-center w-full h-full absolute inset-0" :style="{ backgroundImage: `url('${v.thumbnail_url || fallbackThumb}')` }"></div>
                                <span class="absolute bottom-1 right-1 bg-black/80 text-white font-label-sm text-label-sm px-1 py-0.5 rounded">{{ formatDuration(v.duration) }}</span>
                            </div>
                            <p class="font-label-sm text-label-sm text-on-surface mt-2 line-clamp-2">{{ v.title }}</p>
                        </Link>
                    </div>
                </section>

                <template v-else>
                    <!-- Creators Section -->
                    <section v-if="creators.length > 0">
                        <h3 class="font-headline-md text-headline-md mb-6 text-on-surface">Creators</h3>
                        <div class="flex gap-6 overflow-x-auto pb-4 snap-x" style="scrollbar-width:none;">
                            <Link
                                v-for="creator in creators"
                                :key="creator.id"
                                :href="route('growstream.creator.profile', { slug: String(creator.id) })"
                                class="snap-start shrink-0 w-32 flex flex-col items-center group"
                            >
                                <div class="w-24 h-24 rounded-full border-2 border-transparent group-hover:border-primary transition-colors overflow-hidden mb-3 relative bg-surface-container-highest">
                                    <img v-if="creator.avatar_url" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" :src="creator.avatar_url" :alt="creator.display_name" />
                                    <div v-else class="w-full h-full flex items-center justify-center font-headline-md text-headline-md text-primary">{{ (creator.display_name || 'C').charAt(0).toUpperCase() }}</div>
                                </div>
                                <span class="font-label-md text-label-md text-on-surface text-center line-clamp-1">{{ creator.display_name }}</span>
                                <span class="font-label-sm text-label-sm text-on-surface-variant">{{ formatSubs(creator.subscriber_count) }}</span>
                            </Link>
                        </div>
                    </section>

                    <!-- Series Section -->
                    <section v-if="series.length > 0">
                        <h3 class="font-headline-md text-headline-md mb-6 text-on-surface">Series</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <Link
                                v-for="s in series"
                                :key="s.id"
                                :href="route('growstream.series.detail', s.slug)"
                                class="group relative rounded-xl overflow-hidden cursor-pointer bg-surface border border-surface-container-high hover:border-outline-variant transition-all hover:scale-[1.02]"
                            >
                                <div class="aspect-video relative overflow-hidden">
                                    <div class="bg-cover bg-center w-full h-full absolute inset-0" :style="{ backgroundImage: `url('${s.poster_url || s.banner_url || fallbackThumb}')` }"></div>
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                    <div class="absolute bottom-3 left-3 text-white">
                                        <span class="bg-primary/90 px-2 py-1 rounded text-xs font-bold uppercase tracking-wide">{{ s.total_episodes || 0 }} eps</span>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <h4 class="font-label-md text-label-md text-on-surface mb-1 group-hover:text-primary transition-colors">{{ s.title }}</h4>
                                    <p class="font-label-sm text-label-sm text-on-surface-variant line-clamp-2">{{ s.description }}</p>
                                </div>
                            </Link>
                        </div>
                    </section>

                    <!-- Videos Section -->
                    <section v-if="videos.length > 0">
                        <h3 class="font-headline-md text-headline-md mb-6 text-on-surface">Videos</h3>
                        <div class="flex flex-col gap-4">
                            <Link
                                v-for="v in videos"
                                :key="v.id"
                                :href="route('growstream.video.detail', { slug: v.slug })"
                                class="flex flex-col sm:flex-row gap-4 p-3 rounded-lg hover:bg-surface-container-low transition-colors group border border-transparent hover:border-surface-container-high"
                            >
                                <div class="relative w-full sm:w-64 shrink-0 aspect-video rounded-md overflow-hidden">
                                    <div class="bg-cover bg-center w-full h-full absolute inset-0 group-hover:scale-105 transition-transform duration-500" :style="{ backgroundImage: `url('${v.thumbnail_url || fallbackThumb}')` }"></div>
                                    <div class="absolute bottom-2 right-2 bg-black/80 text-white font-label-sm text-label-sm px-1.5 py-0.5 rounded">{{ formatDuration(v.duration) }}</div>
                                </div>
                                <div class="flex flex-col justify-start py-1">
                                    <h4 class="font-label-md text-label-md text-on-surface mb-1 group-hover:text-primary transition-colors line-clamp-2">{{ v.title }}</h4>
                                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                                        <span class="font-label-sm text-label-sm text-on-surface-variant">{{ v.creator?.name }}</span>
                                        <span class="w-1 h-1 rounded-full bg-outline-variant"></span>
                                        <span class="font-label-sm text-label-sm text-on-surface-variant">{{ formatViews(v.view_count) }} views</span>
                                    </div>
                                    <div v-if="v.categories && v.categories.length" class="hidden sm:flex gap-2 mt-auto">
                                        <span v-for="cat in v.categories.slice(0, 3)" :key="cat.id" class="bg-surface-container-high px-2 py-1 rounded-full font-label-sm text-label-sm text-on-surface-variant">{{ cat.name }}</span>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </section>

                    <!-- Categories match -->
                    <section v-if="categories.length > 0">
                        <h3 class="font-headline-md text-headline-md mb-6 text-on-surface">Categories</h3>
                        <div class="flex flex-wrap gap-2">
                            <Link
                                v-for="category in categories"
                                :key="category.id"
                                :href="route('growstream.browse', { category: category.slug })"
                                class="bg-surface-container-low text-on-surface border border-outline-variant px-5 py-2 rounded-full font-label-md text-label-md"
                            >
                                {{ category.name }}
                                <span v-if="category.videos_count" class="opacity-70">· {{ category.videos_count }}</span>
                            </Link>
                        </div>
                    </section>

                    <!-- Empty state -->
                    <div v-if="!videos.length && !creators.length && !series.length && !categories.length" class="flex flex-col items-center gap-4 py-16 text-center">
                        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-primary/10">
                            <span class="material-symbols-outlined text-4xl text-primary" aria-hidden="true">search_off</span>
                        </div>
                        <h3 class="font-headline-md text-headline-md text-on-surface">Nothing found</h3>
                        <p class="font-label-sm text-label-sm text-on-surface-variant max-w-md">We couldn't find anything matching "{{ query }}". Try a different title, creator, or genre.</p>
                    </div>
                </template>
            </div>
        </main>
    </GrowStreamLayout>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import GrowStreamLayout from '@/Layouts/GrowStreamLayout.vue';
import { useGrowStreamMetrics } from '@/composables/useGrowStreamMetrics';
import type { Video, VideoCategory, CreatorProfile, VideoSeries } from '@/types/growstream';

interface Props {
    query?: string;
    videos?: Video[];
    creators?: CreatorProfile[];
    categories?: VideoCategory[];
    series?: VideoSeries[];
    trending?: Video[];
}

const props = withDefaults(defineProps<Props>(), {
    query: '',
    videos: () => [],
    creators: () => [],
    categories: () => [],
    series: () => [],
    trending: () => [],
});

const fallbackThumb = 'https://placehold.co/440x248/e1bfb4/191c1d?text=GrowStream';
const query = ref(props.query);
const metrics = useGrowStreamMetrics();

const submitSearch = () => {
    const q = query.value.trim();
    if (q) metrics.trackSearch(q);
    router.get(route('growstream.search'), { q: q || undefined }, { preserveState: true, replace: true });
};

const clearSearch = () => {
    query.value = '';
    router.get(route('growstream.search'), {}, { preserveState: true, replace: true });
};

const goBrowse = (sortBy?: string) => {
    router.get(route('growstream.browse', sortBy ? { sort_by: sortBy } : {}), {}, { preserveState: true });
};

watch(() => props.query, (val) => { query.value = val ?? ''; });

const formatDuration = (seconds?: number): string => {
    if (!seconds) return '0:00';
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    return `${m}:${s.toString().padStart(2, '0')}`;
};

const formatViews = (count?: number): string => {
    if (!count) return '0';
    if (count >= 1000000) return (count / 1000000).toFixed(1) + 'M';
    if (count >= 1000) return (count / 1000).toFixed(0) + 'K';
    return count.toString();
};

const formatSubs = (count?: number): string => {
    if (!count) return '0 subs';
    if (count >= 1000) return (count / 1000).toFixed(0) + 'K subs';
    return `${count} subs`;
};
</script>
