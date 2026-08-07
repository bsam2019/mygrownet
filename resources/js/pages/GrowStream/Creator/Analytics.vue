<template>
    <CreatorStudioLayout title="Creator Analytics - GrowStream">
        <main class="flex-1 px-margin-mobile pt-6 pb-40 flex flex-col gap-8 max-w-[600px] mx-auto w-full">
            <!-- Page Header -->
            <div class="flex justify-between items-end">
                <div>
                    <h2 class="font-headline-lg-mobile text-headline-lg-mobile text-on-surface">Attribution</h2>
                    <p class="font-body-md text-body-md text-on-surface-variant mt-1">Track your link performance</p>
                </div>
                <button class="flex items-center gap-1 bg-surface-container px-3 py-1.5 rounded-full text-on-surface-variant hover:bg-surface-container-high transition-colors">
                    <span class="font-label-sm text-label-sm">Last 30 Days</span>
                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">expand_more</span>
                </button>
            </div>

            <!-- Summary Card (Bento) -->
            <section class="bg-surface border border-outline-variant rounded-xl p-4 flex flex-col gap-4 shadow-sm">
                <div class="grid grid-cols-2 gap-4">
                    <!-- Total Clicks -->
                    <div class="col-span-2 bg-surface-container-low rounded-lg p-4">
                        <div class="flex justify-between items-start">
                            <div class="font-label-md text-label-md text-on-surface-variant flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">ads_click</span>
                                Total Clicks
                            </div>
                        </div>
                        <div class="font-display-lg text-[40px] leading-tight font-extrabold text-on-surface mt-2">{{ formatNumber(attribution.total_clicks) }}</div>
                    </div>
                    <!-- Conversions -->
                    <div class="bg-surface-container-low rounded-lg p-4 flex flex-col justify-between">
                        <div class="font-label-sm text-label-sm text-on-surface-variant flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">check_circle</span>
                            Conversions
                        </div>
                        <div class="font-headline-md text-headline-md text-on-surface mt-2">{{ formatNumber(attribution.total_conversions) }}</div>
                    </div>
                    <!-- Conv. Rate -->
                    <div class="bg-surface-container-low rounded-lg p-4 flex flex-col justify-between">
                        <div class="font-label-sm text-label-sm text-on-surface-variant flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">percent</span>
                            Conv. Rate
                        </div>
                        <div class="font-headline-md text-headline-md text-on-surface mt-2">{{ attribution.conversion_rate }}%</div>
                    </div>
                </div>
            </section>

            <!-- Traffic Sources -->
            <section class="flex flex-col gap-4">
                <h3 class="font-headline-md text-headline-md text-on-surface">Traffic Sources</h3>
                <div class="flex flex-col gap-3">
                    <div
                        v-for="s in attribution.sources"
                        :key="s.source"
                        class="flex items-center justify-between p-4 bg-surface border border-outline-variant rounded-xl hover:scale-[1.02] hover:border-outline transition-all"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center font-bold font-label-md">{{ sourceBadge(s.source) }}</div>
                            <div>
                                <div class="font-label-md text-label-md text-on-surface capitalize">{{ sourceLabel(s.source) }}</div>
                                <div class="font-label-sm text-label-sm text-on-surface-variant">{{ sourceSub(s.source) }}</div>
                            </div>
                        </div>
                        <div class="text-right flex flex-col gap-1">
                            <div class="font-label-md text-label-md text-on-surface">{{ formatNumber(s.clicks) }} <span class="text-on-surface-variant text-[11px] font-normal">clicks</span></div>
                            <div class="font-label-sm text-label-sm text-tertiary">{{ s.conversions }} <span class="opacity-70">conv.</span></div>
                        </div>
                    </div>

                    <div v-if="attribution.sources.length === 0" class="bg-surface border border-outline-variant rounded-xl p-8 text-center">
                        <span class="material-symbols-outlined text-4xl text-on-surface-variant" aria-hidden="true">link</span>
                        <p class="font-label-md text-label-md text-on-surface mt-3">No attribution data yet</p>
                        <p class="font-label-sm text-label-sm text-on-surface-variant mt-1">Share your creator links to start tracking clicks.</p>
                    </div>
                </div>
            </section>

            <!-- Top Videos -->
            <section v-if="topVideos.length > 0" class="flex flex-col gap-4">
                <h3 class="font-headline-md text-headline-md text-on-surface">Top Videos</h3>
                <div class="flex flex-col gap-3">
                    <div
                        v-for="v in topVideos"
                        :key="v.id"
                        class="flex items-center gap-4 p-4 bg-surface border border-outline-variant rounded-xl"
                    >
                        <div class="relative w-24 h-16 shrink-0 rounded-md overflow-hidden bg-surface-container-high">
                            <div class="bg-cover bg-center w-full h-full absolute inset-0" :style="{ backgroundImage: `url('${v.thumbnail_url || fallbackThumb}')` }"></div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-label-md text-label-md text-on-surface truncate">{{ v.title }}</h4>
                            <p class="font-label-sm text-label-sm text-on-surface-variant mt-1">{{ formatNumber(v.view_count) }} views &bull; {{ formatViews(v.view_count) }}</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </CreatorStudioLayout>
</template>

<script setup lang="ts">
import CreatorStudioLayout from '@/Layouts/CreatorStudioLayout.vue';
import type { Video } from '@/types/growstream';

interface SourceRow { source: string; clicks: number; conversions: number; }
interface AttributionData { total_clicks: number; total_conversions: number; conversion_rate: number; sources: SourceRow[]; }

interface Props {
    stats?: { total_videos: number; published_videos: number; total_views: number; total_watch_time_hours: number; avg_watch_time_seconds: number };
    topVideos?: Video[];
    attribution?: AttributionData;
}

const props = withDefaults(defineProps<Props>(), {
    stats: () => ({ total_videos: 0, published_videos: 0, total_views: 0, total_watch_time_hours: 0, avg_watch_time_seconds: 0 }),
    topVideos: () => [],
    attribution: () => ({ total_clicks: 0, total_conversions: 0, conversion_rate: 0, sources: [] }),
});

const fallbackThumb = 'https://placehold.co/240x160/e1bfb4/191c1d?text=GrowStream';

const sourceLabel = (source: string): string => {
    const map: Record<string, string> = { facebook: 'Facebook', instagram: 'Instagram', whatsapp: 'WhatsApp', tiktok: 'TikTok', direct: 'Direct' };
    return map[source] ?? source.charAt(0).toUpperCase() + source.slice(1);
};

const sourceSub = (source: string): string => {
    const map: Record<string, string> = { facebook: 'Group Posts', instagram: 'Link in Bio', whatsapp: 'Direct Shares', tiktok: 'Video Links', direct: 'Direct Visit' };
    return map[source] ?? 'Share Link';
};

const sourceBadge = (source: string): string => {
    const initials = sourceLabel(source).slice(0, 2).toUpperCase();
    return initials === 'DI' ? 'DR' : initials;
};

const formatNumber = (n: number): string => n.toLocaleString();
const formatViews = (count?: number): string => {
    if (!count) return '0';
    if (count >= 1000000) return (count / 1000000).toFixed(1) + 'M';
    if (count >= 1000) return (count / 1000).toFixed(0) + 'K';
    return count.toString();
};
</script>
