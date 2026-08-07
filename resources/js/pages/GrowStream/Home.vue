<template>
    <GrowStreamLayout title="GrowStream">
        <!-- Header with Search -->
        <header class="sticky top-0 z-10 border-b border-[var(--gs-border)] bg-[var(--gs-surface)] px-4 py-3 flex items-center gap-3">
            <h1 class="text-lg font-extrabold text-[var(--gs-primary)] uppercase tracking-tighter italic">GrowStream</h1>
            <div class="relative flex-1">
                <svg class="absolute left-3 top-2.5 h-4 w-4 text-[var(--gs-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Discover Zambian creators..."
                    @keyup.enter="doSearch"
                    class="w-full rounded-full border border-[var(--gs-border)] bg-[var(--gs-card-hover)] py-2 pl-9 pr-4 text-sm text-[var(--gs-text)] placeholder-[var(--gs-muted)] focus:border-[var(--gs-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--gs-primary-soft)]"
                />
            </div>
        </header>

        <main class="px-4 pb-28 space-y-10">
            <!-- Hero: Trending Series -->
            <section v-if="featuredVideos && featuredVideos.length > 0" class="pt-4">
                <div class="relative rounded-2xl overflow-hidden aspect-[9/16] max-h-[480px]">
                    <img :src="featuredVideos[0].thumbnail_url || featuredVideos[0].poster_url" class="w-full h-full object-cover" />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-black/20 p-6 flex flex-col justify-end text-white">
                        <span class="bg-[var(--gs-primary)] bg-opacity-20 backdrop-blur-md border border-white/20 px-3 py-1 rounded-full text-[10px] font-bold self-start mb-2 flex items-center uppercase tracking-wider">
                            <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd"/></svg>
                            Trending #1
                        </span>
                        <h2 class="text-4xl font-black mb-1 leading-tight uppercase italic tracking-tighter">{{ featuredVideos[0].title }}</h2>
                        <p class="text-sm font-medium opacity-90 mb-4">{{ featuredVideos[0].description?.substring(0, 80) || 'Premium Zambian content' }}</p>
                        <Link :href="route('growstream.video.detail', { slug: featuredVideos[0].slug })" class="bg-[var(--gs-primary)] text-white py-4 rounded-full font-bold uppercase tracking-widest text-center shadow-lg">
                            Watch Now
                        </Link>
                    </div>
                </div>
            </section>

            <!-- Continue Watching -->
            <section v-if="continueWatching && continueWatching.length > 0">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-extrabold text-[var(--gs-text)]">Continue Watching</h2>
                    <Link :href="route('growstream.my-videos')" class="text-[var(--gs-primary)] text-xs font-bold flex items-center">
                        View All <svg class="h-3 w-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </Link>
                </div>
                <div class="gs-row">
                    <div v-for="item in continueWatching.slice(0, 6)" :key="item.video?.id ?? item.id" class="min-w-[200px] bg-[var(--gs-card)] rounded-2xl overflow-hidden border border-[var(--gs-border)] shadow-sm">
                        <Link :href="route('growstream.video.detail', { slug: item.video?.slug || item.slug })">
                            <div class="relative aspect-video">
                                <img :src="item.video?.thumbnail_url || item.thumbnail_url" class="w-full h-full object-cover" />
                                <div class="absolute bottom-0 left-0 h-1 bg-[var(--gs-primary)]" :style="{ width: (item.progress_percentage || item.watchProgress || 0) + '%' }"></div>
                                <span class="absolute bottom-2 right-2 bg-black/60 text-white text-[10px] px-1.5 py-0.5 rounded">{{ formatDuration(item.video?.duration || item.duration) }}</span>
                            </div>
                            <div class="p-3">
                                <h3 class="font-bold text-sm line-clamp-1 text-[var(--gs-text)]">{{ item.video?.title || item.title }}</h3>
                                <p class="text-[10px] text-[var(--gs-muted)] font-medium mt-0.5">{{ item.video?.creator?.name || item.creator || '' }}</p>
                            </div>
                        </Link>
                    </div>
                </div>
            </section>

            <!-- Trending Videos -->
            <section v-if="trendingVideos && trendingVideos.length > 0">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-extrabold text-[var(--gs-text)]">Trending Now</h2>
                    <Link :href="route('growstream.browse', { sort_by: 'view_count' })" class="text-[var(--gs-primary)] text-xs font-bold flex items-center">
                        View All <svg class="h-3 w-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </Link>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <Link v-for="v in trendingVideos.slice(0, 6)" :key="v.id" :href="route('growstream.video.detail', { slug: v.slug })" class="bg-[var(--gs-card)] rounded-2xl overflow-hidden border border-[var(--gs-border)] shadow-sm hover:scale-[1.02] transition-transform">
                        <div class="relative aspect-video">
                            <img :src="v.thumbnail_url" class="w-full h-full object-cover" />
                            <span class="absolute bottom-2 right-2 bg-black/60 text-white text-[10px] px-1.5 py-0.5 rounded">{{ formatDuration(v.duration) }}</span>
                        </div>
                        <div class="p-3">
                            <h3 class="font-bold text-xs line-clamp-2 text-[var(--gs-text)] leading-tight">{{ v.title }}</h3>
                            <p class="text-[10px] text-[var(--gs-muted)] font-medium mt-1">{{ formatViews(v.view_count) }} views</p>
                        </div>
                    </Link>
                </div>
            </section>

            <!-- Latest Videos -->
            <section v-if="latestVideos && latestVideos.length > 0">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-extrabold text-[var(--gs-text)]">Latest</h2>
                    <Link :href="route('growstream.browse', { sort_by: 'created_at' })" class="text-[var(--gs-primary)] text-xs font-bold flex items-center">
                        View All <svg class="h-3 w-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </Link>
                </div>
                <div class="gs-row">
                    <Link v-for="v in latestVideos.slice(0, 8)" :key="v.id" :href="route('growstream.video.detail', { slug: v.slug })" class="min-w-[160px] bg-[var(--gs-card)] rounded-2xl overflow-hidden border border-[var(--gs-border)] shadow-sm">
                        <div class="relative aspect-video">
                            <img :src="v.thumbnail_url" class="w-full h-full object-cover" />
                            <span class="absolute bottom-2 right-2 bg-black/60 text-white text-[10px] px-1.5 py-0.5 rounded">{{ formatDuration(v.duration) }}</span>
                        </div>
                        <div class="p-3">
                            <h3 class="font-bold text-xs line-clamp-2 text-[var(--gs-text)] leading-tight">{{ v.title }}</h3>
                            <p class="text-[10px] text-[var(--gs-muted)] font-medium mt-1">{{ v.creator?.name || '' }}</p>
                        </div>
                    </Link>
                </div>
            </section>

            <!-- Empty state -->
            <div v-if="!featuredVideos?.length && !trendingVideos?.length && !latestVideos?.length" class="text-center py-20">
                <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-[var(--gs-primary-soft)]">
                    <svg class="h-10 w-10 text-[var(--gs-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                </div>
                <h2 class="text-xl font-extrabold text-[var(--gs-text)] mb-2">No videos yet</h2>
                <p class="text-[var(--gs-muted)] mb-6">Content is on the way. Check back soon.</p>
                <Link :href="route('growstream.browse')" class="gs-btn gs-btn-primary px-8 py-3">Browse Content</Link>
            </div>
        </main>
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

const searchQuery = ref('');

const doSearch = () => {
    if (searchQuery.value.trim()) {
        router.visit(route('growstream.search', { q: searchQuery.value.trim() }));
    }
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
