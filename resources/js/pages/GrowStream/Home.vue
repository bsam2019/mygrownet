<template>
    <GrowStreamLayout title="GrowStream" show-promo>
        <!-- Sticky category chips -->
        <div class="sticky top-16 z-20 -mx-margin-mobile md:-mx-0 px-margin-mobile md:px-0 py-3 bg-background/95 backdrop-blur-md flex gap-2 overflow-x-auto scrollbar-none">
            <button
                class="shrink-0 bg-primary text-on-primary px-5 py-2 rounded-full font-label-md text-label-md"
                @click="goBrowse({})"
            >All</button>
            <button
                v-for="cat in categories"
                :key="cat.id"
                @click="goBrowse({ category: cat.slug })"
                class="shrink-0 bg-surface-container-low text-on-surface-variant px-5 py-2 rounded-full font-label-md text-label-md border border-outline-variant hover:bg-surface-container-high hover:text-on-surface transition-colors"
            >
                {{ cat.name }}
            </button>
        </div>

        <!-- Personalized hero -->
        <section class="mb-8">
            <div class="relative rounded-2xl overflow-hidden aspect-[16/9] md:aspect-[21/9] gs-hero">
                <div class="bg-cover bg-center w-full h-full absolute inset-0" :style="{ backgroundImage: `url('${heroVideo?.banner_url || heroVideo?.thumbnail_url || heroVideo?.poster_url || fallbackThumb}')` }"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-black/20"></div>
                <div class="absolute inset-0 flex flex-col justify-end p-5 md:p-10 text-white">
                    <span class="bg-primary/90 px-3 py-1 rounded-full font-label-sm text-label-sm self-start mb-3 flex items-center gap-1 uppercase tracking-wider">
                        <span class="material-symbols-outlined text-xs" aria-hidden="true">local_fire_department</span>
                        {{ heroLabel }}
                    </span>
                    <h1 class="font-headline-lg text-headline-lg md:font-display-lg md:text-display-lg font-extrabold uppercase leading-none mb-2">{{ heroVideo.title }}</h1>
                    <p class="font-label-md text-label-md italic opacity-90 mb-4 max-w-xl line-clamp-2">{{ heroVideo.description?.substring(0, 120) || 'Premium Zambian content' }}</p>
                    <div class="flex items-center gap-3 flex-wrap">
                        <Link :href="route('growstream.video.detail', { slug: heroVideo.slug })" class="bg-primary text-on-primary px-6 py-3 rounded-full font-label-md text-label-md uppercase tracking-widest flex items-center gap-2 hover:bg-[#c94918] transition-colors">
                            <span class="material-symbols-outlined text-lg" aria-hidden="true">play_arrow</span> {{ heroCta }}
                        </Link>
                        <span class="font-label-sm text-label-sm bg-black/40 backdrop-blur px-3 py-1.5 rounded-full flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm" aria-hidden="true">visibility</span> {{ formatViews(heroVideo.view_count) }} views
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Continue Watching -->
        <section v-if="continueWatching && continueWatching.length > 0" class="mb-8">
            <h2 class="font-headline-md text-headline-md mb-4">Continue Watching</h2>
            <div class="flex gap-4 overflow-x-auto pb-2 -mx-margin-mobile px-margin-mobile gs-row-fade" style="scrollbar-width:none;">
                <Link
                    v-for="item in continueWatching.slice(0, 8)"
                    :key="item.video?.id ?? item.id"
                    :href="route('growstream.video.detail', { slug: item.video?.slug || item.slug })"
                    class="min-w-[220px] max-w-[240px] bg-surface-container-lowest rounded-xl overflow-hidden border border-outline-variant hover:border-primary/50 transition-colors group"
                >
                    <div class="relative aspect-video bg-surface-container-highest">
                        <div class="bg-cover bg-center w-full h-full absolute inset-0 group-hover:scale-105 transition-transform duration-300" :style="{ backgroundImage: `url('${item.video?.thumbnail_url || item.thumbnail_url || fallbackThumb}')` }"></div>
                        <div class="absolute bottom-0 left-0 h-1 bg-primary" :style="{ width: (item.progress_percentage || item.watchProgress || 0) + '%' }"></div>
                        <span class="absolute bottom-2 right-2 bg-black/70 text-white text-[10px] px-1.5 py-0.5 rounded flex items-center gap-0.5">
                            <span class="material-symbols-outlined text-xs" aria-hidden="true">play_arrow</span>{{ formatDuration(item.video?.duration || item.duration) }}
                        </span>
                        <span class="absolute top-2 right-2 bg-primary text-on-primary text-[9px] font-bold px-1.5 py-0.5 rounded-full">{{ Math.round(item.progress_percentage || item.watchProgress || 0) }}%</span>
                    </div>
                    <div class="p-3">
                        <h3 class="font-label-md text-label-md truncate">{{ item.video?.title || item.title }}</h3>
                        <p class="font-label-sm text-label-sm text-on-surface-variant truncate">{{ item.video?.creator?.name || item.creator || '' }}</p>
                    </div>
                </Link>
            </div>
        </section>

        <!-- For You -->
        <section v-if="forYou && forYou.length > 0" class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-headline-md text-headline-md">For You</h2>
                <Link :href="route('growstream.browse', { sort_by: 'view_count' })" class="text-primary font-label-md text-label-md flex items-center gap-1 group">
                    See All <span class="material-symbols-outlined text-sm group-hover:translate-x-0.5 transition-transform" aria-hidden="true">arrow_forward</span>
                </Link>
            </div>
            <div class="flex gap-4 overflow-x-auto pb-2 -mx-margin-mobile px-margin-mobile gs-row-fade" style="scrollbar-width:none;">
                <Link
                    v-for="v in forYou.slice(0, 10)"
                    :key="v.id"
                    :href="route('growstream.video.detail', { slug: v.slug })"
                    class="min-w-[160px] max-w-[180px] group"
                >
                    <div class="relative aspect-video rounded-xl overflow-hidden bg-surface-container-highest">
                        <div class="bg-cover bg-center w-full h-full absolute inset-0 group-hover:scale-105 transition-transform duration-300" :style="{ backgroundImage: `url('${v.thumbnail_url || v.poster_url || fallbackThumb}')` }"></div>
                        <span class="absolute bottom-1 right-1 bg-black/70 text-white text-[9px] px-1 rounded">{{ formatDuration(v.duration) }}</span>
                    </div>
                    <p class="font-label-sm text-label-sm mt-2 truncate">{{ v.title }}</p>
                    <p class="font-label-sm text-label-sm text-on-surface-variant truncate">{{ v.creator?.name || '' }} • {{ formatViews(v.view_count) }}</p>
                </Link>
            </div>
        </section>

        <!-- Trending Now -->
        <section v-if="trendingVideos && trendingVideos.length > 0" class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-headline-md text-headline-md">Trending Now</h2>
                <Link :href="route('growstream.browse', { sort_by: 'view_count' })" class="text-primary font-label-md text-label-md flex items-center gap-1 group">
                    View All <span class="material-symbols-outlined text-sm group-hover:translate-x-0.5 transition-transform" aria-hidden="true">arrow_forward</span>
                </Link>
            </div>
            <div class="flex gap-4 overflow-x-auto pb-2 -mx-margin-mobile px-margin-mobile gs-row-fade" style="scrollbar-width:none;">
                <Link
                    v-for="v in trendingVideos.slice(0, 8)"
                    :key="v.id"
                    :href="route('growstream.video.detail', { slug: v.slug })"
                    class="min-w-[160px] max-w-[180px] group"
                >
                    <div class="relative aspect-video rounded-xl overflow-hidden bg-surface-container-highest">
                        <div class="bg-cover bg-center w-full h-full absolute inset-0 group-hover:scale-105 transition-transform duration-300" :style="{ backgroundImage: `url('${v.thumbnail_url || v.poster_url || fallbackThumb}')` }"></div>
                        <span class="absolute bottom-1 right-1 bg-black/70 text-white text-[9px] px-1 rounded">{{ formatDuration(v.duration) }}</span>
                    </div>
                    <p class="font-label-sm text-label-sm mt-2 truncate">{{ v.title }}</p>
                    <p class="font-label-sm text-label-sm text-on-surface-variant truncate">{{ v.creator?.name || '' }} • {{ formatViews(v.view_count) }}</p>
                </Link>
            </div>
        </section>

        <!-- Top Creators -->
        <section v-if="topCreators && topCreators.length > 0" class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-headline-md text-headline-md">Top Creators</h2>
                <Link :href="route('growstream.browse')" class="text-primary font-label-md text-label-md flex items-center gap-1 group">
                    Explore <span class="material-symbols-outlined text-sm group-hover:translate-x-0.5 transition-transform" aria-hidden="true">arrow_forward</span>
                </Link>
            </div>
            <div class="flex gap-6 overflow-x-auto pb-2 -mx-margin-mobile px-margin-mobile" style="scrollbar-width:none;">
                <Link
                    v-for="c in topCreators"
                    :key="c.id"
                    :href="route('growstream.creator.profile', { slug: c.channel_slug })"
                    class="flex flex-col items-center min-w-[80px] group"
                >
                    <div class="relative">
                        <div class="w-16 h-16 rounded-full bg-surface-container-highest overflow-hidden ring-2 ring-primary/30 group-hover:ring-primary transition-all flex items-center justify-center text-primary font-bold text-xl">
                            <img v-if="c.avatar_url" class="w-full h-full object-cover" :src="c.avatar_url" :alt="c.display_name" />
                            <span v-else>{{ (c.display_name || 'C').charAt(0).toUpperCase() }}</span>
                        </div>
                        <span v-if="c.is_verified" class="absolute -bottom-0.5 -right-0.5 w-5 h-5 rounded-full bg-primary flex items-center justify-center">
                            <span class="material-symbols-outlined text-[11px] text-on-primary" aria-hidden="true">verified</span>
                        </span>
                    </div>
                    <p class="font-label-sm text-label-sm mt-2 truncate text-center max-w-[80px]">{{ c.display_name }}</p>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">{{ formatNumber(c.subscriber_count || 0) }} subs</p>
                </Link>
            </div>
        </section>

        <!-- Series -->
        <section v-if="series && series.length > 0" class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-headline-md text-headline-md">Series</h2>
                <Link :href="route('growstream.browse', { content_type: 'series' })" class="text-primary font-label-md text-label-md flex items-center gap-1 group">
                    View All <span class="material-symbols-outlined text-sm group-hover:translate-x-0.5 transition-transform" aria-hidden="true">arrow_forward</span>
                </Link>
            </div>
            <div class="flex gap-4 overflow-x-auto pb-2 -mx-margin-mobile px-margin-mobile gs-row-fade" style="scrollbar-width:none;">
                <Link
                    v-for="s in series.slice(0, 8)"
                    :key="s.id"
                    :href="route('growstream.series.detail', { slug: s.slug })"
                    class="min-w-[140px] max-w-[150px] group"
                >
                    <div class="relative aspect-[2/3] rounded-xl overflow-hidden bg-surface-container-highest">
                        <div class="bg-cover bg-center w-full h-full absolute inset-0 group-hover:scale-105 transition-transform duration-300" :style="{ backgroundImage: `url('${s.poster_url || fallbackPoster}')` }"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent"></div>
                        <span class="absolute bottom-1.5 left-2 font-label-sm text-label-sm text-white">{{ s.total_episodes || 0 }} eps</span>
                    </div>
                    <p class="font-label-sm text-label-sm mt-2 truncate">{{ s.title }}</p>
                </Link>
            </div>
        </section>

        <!-- My List -->
        <section v-if="watchlist && watchlist.length > 0" class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-headline-md text-headline-md">My List</h2>
                <Link :href="route('growstream.my-videos')" class="text-primary font-label-md text-label-md flex items-center gap-1 group">
                    View All <span class="material-symbols-outlined text-sm group-hover:translate-x-0.5 transition-transform" aria-hidden="true">arrow_forward</span>
                </Link>
            </div>
            <div class="flex gap-4 overflow-x-auto pb-2 -mx-margin-mobile px-margin-mobile gs-row-fade" style="scrollbar-width:none;">
                <Link
                    v-for="w in watchlist.slice(0, 8)"
                    :key="w.id"
                    :href="watchableHref(w)"
                    class="min-w-[140px] max-w-[150px] group"
                >
                    <div class="relative aspect-[2/3] rounded-xl overflow-hidden bg-surface-container-highest">
                        <div class="bg-cover bg-center w-full h-full absolute inset-0 group-hover:scale-105 transition-transform duration-300" :style="{ backgroundImage: `url('${watchablePoster(w)}')` }"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent"></div>
                        <span class="absolute bottom-1.5 left-2 font-label-sm text-label-sm text-white">{{ isSeries(w) ? 'Series' : (w.watchable?.duration ? formatDuration(w.watchable.duration) : '') }}</span>
                    </div>
                    <p class="font-label-sm text-label-sm mt-2 truncate">{{ watchableTitle(w) }}</p>
                </Link>
            </div>
        </section>

        <!-- Latest -->
        <section v-if="latestVideos && latestVideos.length > 0" class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-headline-md text-headline-md">Latest</h2>
                <Link :href="route('growstream.browse', { sort_by: 'created_at' })" class="text-primary font-label-md text-label-md flex items-center gap-1 group">
                    View All <span class="material-symbols-outlined text-sm group-hover:translate-x-0.5 transition-transform" aria-hidden="true">arrow_forward</span>
                </Link>
            </div>
            <div class="flex gap-4 overflow-x-auto pb-2 -mx-margin-mobile px-margin-mobile gs-row-fade" style="scrollbar-width:none;">
                <Link
                    v-for="v in latestVideos.slice(0, 8)"
                    :key="v.id"
                    :href="route('growstream.video.detail', { slug: v.slug })"
                    class="min-w-[160px] max-w-[180px] group"
                >
                    <div class="relative aspect-video rounded-xl overflow-hidden bg-surface-container-highest">
                        <div class="bg-cover bg-center w-full h-full absolute inset-0 group-hover:scale-105 transition-transform duration-300" :style="{ backgroundImage: `url('${v.thumbnail_url || v.poster_url || fallbackThumb}')` }"></div>
                        <span class="absolute bottom-1 right-1 bg-black/70 text-white text-[9px] px-1 rounded">{{ formatDuration(v.duration) }}</span>
                    </div>
                    <p class="font-label-sm text-label-sm mt-2 truncate">{{ v.title }}</p>
                    <p class="font-label-sm text-label-sm text-on-surface-variant truncate">{{ v.creator?.name || '' }}</p>
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
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import GrowStreamLayout from '@/Layouts/GrowStreamLayout.vue';
import type { Video, VideoSeries, WatchHistory, Watchlist, CreatorProfile } from '@/types/growstream';

interface TopCreator {
    id: number;
    channel_slug: string;
    display_name: string;
    avatar_url?: string;
    is_verified: boolean;
    subscriber_count: number;
}

interface Props {
    featuredVideos?: Video[];
    trendingVideos?: Video[];
    latestVideos?: Video[];
    continueWatching?: WatchHistory[];
    categories?: { id: number; name: string; slug: string }[];
    watchlist?: Watchlist[];
    topCreators?: TopCreator[];
    series?: VideoSeries[];
    forYou?: Video[];
}

const props = defineProps<Props>();

const fallbackThumb = 'https://placehold.co/440x248/e1bfb4/191c1d?text=GrowStream';
const fallbackPoster = 'https://placehold.co/300x450/e1bfb4/191c1d?text=GrowStream';
const searchQuery = ref('');

// Hero resolution: use continue-watching (resume) OR the top trending item.
const heroVideo = computed<Video>(() => {
    const cw = (props.continueWatching ?? [])[0]?.video;
    if (cw) return cw;
    return (props.trendingVideos ?? props.featuredVideos ?? [])[0];
});
const heroLabel = computed(() => ((props.continueWatching ?? [])[0]?.video ? 'Pick up where you left off' : 'Trending #1'));
const heroCta = computed(() => ((props.continueWatching ?? [])[0]?.video ? 'Resume' : 'Watch Now'));

const doSearch = () => {
    if (searchQuery.value.trim()) {
        router.visit(route('growstream.search', { q: searchQuery.value.trim() }));
    }
};

const goBrowse = (params: Record<string, string>) => {
    router.visit(route('growstream.browse', params));
};

const formatDuration = (seconds?: number): string => {
    if (!seconds) return '0:00';
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    return `${m}:${s.toString().padStart(2, '0')}`;
};

const formatViews = (count?: number): string => formatNumber(count ?? 0);
const formatNumber = (n: number): string => {
    if (n >= 1000000) return (n / 1000000).toFixed(1) + 'M';
    if (n >= 1000) return (n / 1000).toFixed(0) + 'K';
    return n.toString();
};

// Watchlist helpers
const watchableTitle = (w: Watchlist): string => {
    const it = w.watchable as any;
    return it?.title || it?.name || 'Untitled';
};
const watchablePoster = (w: Watchlist): string => {
    const it = w.watchable as any;
    return it?.poster_url || it?.thumbnail_url || fallbackPoster;
};
const watchableHref = (w: Watchlist): string => {
    const it = w.watchable as any;
    if (!it) return route('growstream.browse');
    if (isSeries(w)) return route('growstream.series.detail', { slug: it.slug });
    return route('growstream.video.detail', { slug: it.slug });
};
const isSeries = (w: Watchlist): boolean => {
    const type = String(w.watchable_type || '');
    return type.includes('Series') || type.includes('series');
};
</script>

<style scoped>
.gs-hero {
    animation: heroRise 0.8s cubic-bezier(0.22, 1, 0.36, 1);
}
@keyframes heroRise {
    from { opacity: 0; transform: translateY(24px); }
    to { opacity: 1; transform: translateY(0); }
}
.gs-row-fade {
    -webkit-mask-image: linear-gradient(90deg, #000 92%, transparent);
    mask-image: linear-gradient(90deg, #000 92%, transparent);
}
.scrollbar-none { scrollbar-width: none; }
.scrollbar-none::-webkit-scrollbar { display: none; }
</style>
