<template>
    <GrowStreamLayout :title="`${creator.display_name} - GrowStream`">
        <main class="flex-1 w-full max-w-7xl mx-auto flex flex-col">
            <!-- Banner -->
            <div class="relative w-full aspect-[16/9] sm:aspect-[3/1] md:aspect-[4/1] overflow-hidden bg-surface-container-high">
                <div class="bg-cover bg-center w-full h-full absolute inset-0" :style="{ backgroundImage: `url('${creator.banner_url || fallbackBanner}')` }"></div>
            </div>

            <!-- Profile header -->
            <div class="px-margin-mobile md:px-margin-desktop">
                <div class="w-24 h-24 md:w-28 md:h-28 rounded-full border-4 border-background overflow-hidden -mt-12 md:-mt-14 relative z-10 bg-surface-container-highest shadow-md">
                    <img v-if="creator.avatar_url" class="w-full h-full object-cover" :src="creator.avatar_url" :alt="creator.display_name" />
                    <div v-else class="w-full h-full flex items-center justify-center font-display-lg text-display-lg text-primary">{{ (creator.display_name || 'C').charAt(0).toUpperCase() }}</div>
                </div>

                <div class="flex flex-col md:flex-row md:items-center md:justify-between mt-4 gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="font-display-lg text-headline-lg-mobile md:text-headline-lg font-bold text-on-surface">{{ creator.display_name }}</h1>
                            <span v-if="creator.is_verified" class="material-symbols-outlined text-primary text-[20px]" style="font-variation-settings: 'FILL' 1;" aria-hidden="true">verified</span>
                        </div>
                        <p class="font-label-sm text-label-sm text-on-surface-variant mt-1">{{ formatFollowers(creator.subscriber_count) }} Followers &bull; {{ creator.user?.name || '' }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button class="px-6 py-3 rounded-full bg-primary text-on-primary font-label-md text-label-md hover:opacity-90 active:scale-95 transition-all" @click="onFollow">
                            Subscribe
                        </button>
                        <button class="w-11 h-11 flex items-center justify-center rounded-full border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors" aria-label="Share" @click="shareProfile">
                            <span class="material-symbols-outlined" aria-hidden="true">share</span>
                        </button>
                    </div>
                </div>

                <p v-if="creator.bio" class="font-body-md text-body-md text-on-surface-variant mt-4 max-w-2xl">{{ creator.bio }}</p>
            </div>

            <!-- Tabs -->
            <div class="px-margin-mobile md:px-margin-desktop mt-6 border-b border-surface-container-high">
                <div class="flex gap-6 overflow-x-auto scrollbar-hide">
                    <button class="whitespace-nowrap pb-3 border-b-2 border-primary text-primary font-label-md text-label-md">Series</button>
                    <button class="whitespace-nowrap pb-3 border-b-2 border-transparent text-on-surface-variant font-label-md text-label-md hover:text-on-surface transition-colors" @click="goBrowse('movie')">Movies</button>
                    <button class="whitespace-nowrap pb-3 border-b-2 border-transparent text-on-surface-variant font-label-md text-label-md hover:text-on-surface transition-colors" @click="goBrowse('comedy')">Comedy</button>
                    <button class="whitespace-nowrap pb-3 border-b-2 border-transparent text-on-surface-variant font-label-md text-label-md hover:text-on-surface transition-colors" @click="goBrowse('music')">Music</button>
                </div>
            </div>

            <!-- Content list -->
            <div class="px-margin-mobile md:px-margin-desktop py-6 flex flex-col gap-6">
                <!-- Series section -->
                <section v-if="series.length > 0" class="mb-2">
                    <h3 class="font-headline-md text-headline-md text-on-surface mb-4">Series</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <Link
                            v-for="s in series.slice(0, 6)"
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
                            </div>
                        </Link>
                    </div>
                </section>

                <!-- Videos list -->
                <section>
                    <h3 class="font-headline-md text-headline-md text-on-surface mb-4">{{ series.length > 0 ? 'Videos' : 'Content' }}</h3>
                    <div class="flex flex-col gap-6">
                        <Link
                            v-for="v in videos"
                            :key="v.id"
                            :href="route('growstream.video.detail', { slug: v.slug })"
                            class="group block"
                        >
                            <div class="relative w-full aspect-video rounded-lg overflow-hidden bg-surface-container-high">
                                <div class="bg-cover bg-center w-full h-full absolute inset-0 group-hover:scale-105 transition-transform duration-300" :style="{ backgroundImage: `url('${v.thumbnail_url || fallbackThumb}')` }"></div>
                                <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
                                <span class="absolute bottom-2 right-2 bg-black/80 text-white font-label-sm text-label-sm px-1.5 py-0.5 rounded">{{ formatDuration(v.duration) }}</span>
                            </div>
                            <h4 class="font-label-md text-label-md text-on-surface mt-2 group-hover:text-primary transition-colors">{{ v.title }}</h4>
                            <p class="font-label-sm text-label-sm text-on-surface-variant mt-1">{{ formatViews(v.view_count) }} views &bull; {{ formatDate(v.created_at) }}</p>
                        </Link>
                    </div>
                </section>

                <!-- Empty state -->
                <div v-if="!videos.length && !series.length" class="flex flex-col items-center gap-4 py-16 text-center">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-primary/10">
                        <span class="material-symbols-outlined text-4xl text-primary" aria-hidden="true">videocam_off</span>
                    </div>
                    <h3 class="font-headline-md text-headline-md text-on-surface">No content yet</h3>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">This creator hasn't published anything yet.</p>
                </div>
            </div>
        </main>
    </GrowStreamLayout>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import GrowStreamLayout from '@/Layouts/GrowStreamLayout.vue';
import { useGrowStream } from '@/composables/useGrowStream';
import { useGrowStreamMetrics } from '@/composables/useGrowStreamMetrics';
import type { Video, VideoSeries, CreatorProfile } from '@/types/growstream';

interface Props {
    creator: CreatorProfile;
    videos?: Video[];
    series?: VideoSeries[];
}

const props = withDefaults(defineProps<Props>(), {
    videos: () => [],
    series: () => [],
});

const { formatDuration } = useGrowStream();
const metrics = useGrowStreamMetrics();

const fallbackBanner = 'https://placehold.co/1600x500/e1bfb4/191c1d?text=GrowStream';
const fallbackThumb = 'https://placehold.co/800x450/e1bfb4/191c1d?text=GrowStream';

const onFollow = () => metrics.trackCreatorSubscribe(props.creator.id);

const shareProfile = async () => {
    const url = window.location.href;
    try { await navigator.share({ title: props.creator.display_name, url }); }
    catch { try { await navigator.clipboard.writeText(url); } catch { /* noop */ } }
};

const goBrowse = (contentType?: string) => {
    router.get(route('growstream.browse', { content_type: contentType, creator: props.creator.id }), {}, { preserveState: true });
};

const formatFollowers = (count?: number): string => {
    if (!count) return '0';
    if (count >= 1000) return (count / 1000).toFixed(1) + 'K';
    return count.toString();
};

const formatViews = (count?: number): string => {
    if (!count) return '0';
    if (count >= 1000000) return (count / 1000000).toFixed(1) + 'M';
    if (count >= 1000) return (count / 1000).toFixed(0) + 'K';
    return count.toString();
};

const formatDate = (dateString?: string): string => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const diffDays = Math.ceil(Math.abs(Date.now() - date.getTime()) / (1000 * 60 * 60 * 24));
    if (diffDays === 0) return 'Today';
    if (diffDays === 1) return 'Yesterday';
    if (diffDays < 7) return `${diffDays} days ago`;
    if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`;
    if (diffDays < 365) return `${Math.floor(diffDays / 30)} months ago`;
    return `${Math.floor(diffDays / 365)} years ago`;
};
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
