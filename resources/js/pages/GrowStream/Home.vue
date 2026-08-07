<template>
    <GrowStreamLayout title="GrowStream">
        <!-- Search bar -->
        <div class="relative mb-8">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg" aria-hidden="true">search</span>
            <input
                v-model="searchQuery"
                class="w-full bg-surface-container-low border border-outline-variant rounded-full py-3 pl-11 pr-4 text-body-md placeholder:text-on-surface-variant focus:outline-none focus:ring-2 focus:ring-primary"
                placeholder="Discover Zambian creators..."
                @keyup.enter="doSearch"
            />
        </div>

        <!-- Continue Watching -->
        <section v-if="continueWatching && continueWatching.length > 0" class="mb-8">
            <h2 class="font-headline-md text-headline-md mb-4">Continue Watching</h2>
            <div class="flex gap-4 overflow-x-auto pb-2 -mx-margin-mobile px-margin-mobile" style="scrollbar-width:none;">
                <Link
                    v-for="item in continueWatching.slice(0, 6)"
                    :key="item.video?.id ?? item.id"
                    :href="route('growstream.video.detail', { slug: item.video?.slug || item.slug })"
                    class="min-w-[220px] bg-surface-container-lowest rounded-lg overflow-hidden border border-outline-variant"
                >
                    <div class="relative aspect-video bg-surface-container-highest">
                        <div class="bg-cover bg-center w-full h-full absolute inset-0" :style="{ backgroundImage: `url('${item.video?.thumbnail_url || item.thumbnail_url || fallbackThumb}')` }"></div>
                        <div class="absolute bottom-0 left-0 h-1 bg-primary" :style="{ width: (item.progress_percentage || item.watchProgress || 0) + '%' }"></div>
                        <span class="absolute bottom-2 right-2 bg-black/70 text-white text-[10px] px-1.5 py-0.5 rounded">{{ formatDuration(item.video?.duration || item.duration) }}</span>
                    </div>
                    <div class="p-3">
                        <h3 class="font-label-md text-label-md truncate">{{ item.video?.title || item.title }}</h3>
                        <p class="font-label-sm text-label-sm text-on-surface-variant">{{ item.video?.creator?.name || item.creator || '' }}</p>
                    </div>
                </Link>
            </div>
        </section>

        <!-- Category chips -->
        <div class="flex gap-2 overflow-x-auto pb-2 mb-8 -mx-margin-mobile px-margin-mobile" style="scrollbar-width:none;">
            <button class="shrink-0 bg-primary text-on-primary px-5 py-2 rounded-full font-label-md text-label-md">All</button>
            <button
                v-for="cat in categories"
                :key="cat.id"
                @click="goCategory(cat.slug)"
                class="shrink-0 bg-surface-container-low text-on-surface-variant px-5 py-2 rounded-full font-label-md text-label-md border border-outline-variant"
            >
                {{ cat.name }}
            </button>
        </div>

        <!-- Trending Series -->
        <section v-if="featuredVideos && featuredVideos.length > 0" class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-headline-md text-headline-md">Trending Series</h2>
                <Link :href="route('growstream.browse', { sort_by: 'view_count' })" class="text-primary font-label-md text-label-md flex items-center gap-1">
                    View All <span class="material-symbols-outlined text-sm" aria-hidden="true">arrow_forward</span>
                </Link>
            </div>
            <div class="relative rounded-xl overflow-hidden aspect-[3/4]">
                <div class="bg-cover bg-center w-full h-full absolute inset-0" :style="{ backgroundImage: `url('${featuredVideos[0].thumbnail_url || featuredVideos[0].poster_url || fallbackThumb}')` }"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/10 to-black/30 p-6 flex flex-col justify-end text-white">
                    <span class="bg-primary/80 px-3 py-1 rounded-full font-label-sm text-label-sm self-start mb-3 flex items-center gap-1 uppercase tracking-wider">
                        <span class="material-symbols-outlined text-xs" aria-hidden="true">local_fire_department</span> Trending #1
                    </span>
                    <h3 class="text-4xl font-display-lg italic uppercase leading-none mb-1">{{ featuredVideos[0].title }}</h3>
                    <p class="font-label-md text-label-md italic uppercase opacity-90 mb-4">{{ featuredVideos[0].description?.substring(0, 60) || 'Premium Zambian content' }}</p>
                    <Link :href="route('growstream.video.detail', { slug: featuredVideos[0].slug })" class="bg-primary text-on-primary py-3 rounded-full font-label-md text-label-md uppercase tracking-widest text-center">
                        Watch Now
                    </Link>
                </div>
            </div>
            <div class="flex items-center gap-3 mt-3">
                <img class="w-9 h-9 rounded-full object-cover" :src="featuredVideos[0].creator?.avatar_url || fallbackAvatar" :alt="featuredVideos[0].creator?.name || 'Creator'" />
                <div>
                    <p class="font-label-md text-label-md">{{ featuredVideos[0].title }}</p>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">{{ featuredVideos[0].creator?.name || '' }} • {{ formatViews(featuredVideos[0].view_count) }} views</p>
                </div>
            </div>
        </section>

        <!-- Top Creators -->
        <section v-if="trendingVideos && trendingVideos.length > 0" class="mb-8">
            <h2 class="font-headline-md text-headline-md mb-4">Trending Now</h2>
            <div class="flex gap-4 overflow-x-auto pb-2 -mx-margin-mobile px-margin-mobile" style="scrollbar-width:none;">
                <Link
                    v-for="v in trendingVideos.slice(0, 6)"
                    :key="v.id"
                    :href="route('growstream.video.detail', { slug: v.slug })"
                    class="min-w-[160px]"
                >
                    <div class="relative aspect-video rounded-lg overflow-hidden bg-surface-container-highest">
                        <div class="bg-cover bg-center w-full h-full absolute inset-0" :style="{ backgroundImage: `url('${v.thumbnail_url || fallbackThumb}')` }"></div>
                        <span class="absolute bottom-1 right-1 bg-black/70 text-white text-[9px] px-1 rounded">{{ formatDuration(v.duration) }}</span>
                    </div>
                    <p class="font-label-sm text-label-sm mt-2 truncate">{{ v.title }}</p>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">{{ formatViews(v.view_count) }} views</p>
                </Link>
            </div>
        </section>

        <!-- Latest -->
        <section v-if="latestVideos && latestVideos.length > 0" class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-headline-md text-headline-md">Latest</h2>
                <Link :href="route('growstream.browse', { sort_by: 'created_at' })" class="text-primary font-label-md text-label-md flex items-center gap-1">
                    View All <span class="material-symbols-outlined text-sm" aria-hidden="true">arrow_forward</span>
                </Link>
            </div>
            <div class="flex gap-4 overflow-x-auto pb-2 -mx-margin-mobile px-margin-mobile" style="scrollbar-width:none;">
                <Link
                    v-for="v in latestVideos.slice(0, 8)"
                    :key="v.id"
                    :href="route('growstream.video.detail', { slug: v.slug })"
                    class="min-w-[160px]"
                >
                    <div class="relative aspect-video rounded-lg overflow-hidden bg-surface-container-highest">
                        <div class="bg-cover bg-center w-full h-full absolute inset-0" :style="{ backgroundImage: `url('${v.thumbnail_url || fallbackThumb}')` }"></div>
                        <span class="absolute bottom-1 right-1 bg-black/70 text-white text-[9px] px-1 rounded">{{ formatDuration(v.duration) }}</span>
                    </div>
                    <p class="font-label-sm text-label-sm mt-2 truncate">{{ v.title }}</p>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">{{ v.creator?.name || '' }}</p>
                </Link>
            </div>
        </section>

        <!-- Empty state -->
        <div v-if="!featuredVideos?.length && !trendingVideos?.length && !latestVideos?.length" class="text-center py-20">
            <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-primary/10">
                <span class="material-symbols-outlined text-4xl text-primary" aria-hidden="true">play_circle</span>
            </div>
            <h2 class="font-headline-md text-headline-md mb-2 text-on-surface">No videos yet</h2>
            <p class="text-on-surface-variant mb-6">Content is on the way. Check back soon.</p>
            <Link :href="route('growstream.browse')" class="bg-primary text-on-primary px-8 py-3 rounded-full font-label-md text-label-md">Browse Content</Link>
        </div>
    </GrowStreamLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import GrowStreamLayout from '@/Layouts/GrowStreamLayout.vue';
import type { Video, WatchHistory } from '@/types/growstream';

interface Props {
    featuredVideos?: Video[];
    trendingVideos?: Video[];
    latestVideos?: Video[];
    continueWatching?: WatchHistory[];
    categories?: { id: number; name: string; slug: string }[];
}

defineProps<Props>();

const fallbackThumb = 'https://placehold.co/440x248/e1bfb4/191c1d?text=GrowStream';
const fallbackAvatar = 'https://placehold.co/72x72/e1bfb4/191c1d?text=GS';

const searchQuery = ref('');

const doSearch = () => {
    if (searchQuery.value.trim()) {
        router.visit(route('growstream.search', { q: searchQuery.value.trim() }));
    }
};

const goCategory = (slug: string) => {
    router.visit(route('growstream.browse', { category: slug }));
};

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
</script>
