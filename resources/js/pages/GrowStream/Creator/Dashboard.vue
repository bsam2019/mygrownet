<template>
    <CreatorStudioLayout title="Creator Dashboard - GrowStream">
        <main class="flex-1 w-full max-w-4xl mx-auto flex flex-col relative">
            <div class="px-margin-mobile md:px-margin-desktop py-6 space-y-8">
                <!-- Revenue Card -->
                <div class="rounded-xl border border-surface-container-high bg-gradient-to-br from-primary-fixed/40 to-surface-bright p-6">
                    <p class="font-label-sm text-label-sm text-on-surface-variant tracking-wide uppercase">Estimated Revenue</p>
                    <p class="font-display-lg text-[40px] leading-[48px] md:text-display-lg font-extrabold text-primary mt-2">K {{ formatMoney(earningsSummary?.total_earnings) }}</p>
                    <div class="flex items-center gap-1 mt-2 text-green-700">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">trending_up</span>
                        <span class="font-label-md text-label-md">+12% from last month</span>
                    </div>
                    <button class="w-full mt-6 flex items-center justify-center gap-2 px-6 py-3 rounded-full bg-primary text-on-primary font-label-md text-label-md hover:opacity-90 active:scale-95 transition-all">
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">account_balance_wallet</span>
                        Request Payout
                    </button>
                    <p v-if="earningsSummary?.pending_payout" class="mt-3 text-center font-label-sm text-label-sm text-on-surface-variant">{{ formatMoney(earningsSummary.pending_payout) }} pending payout</p>
                </div>

                <!-- Watch Time Chart -->
                <section>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-headline-md text-headline-md text-on-surface">Watch Time (Hours)</h3>
                        <button class="flex items-center gap-1 px-3 py-1.5 rounded-full bg-surface-container-low border border-outline-variant font-label-sm text-label-sm text-on-surface-variant hover:bg-surface-container transition-colors">
                            {{ watchTimeHours ?? 0 }} hrs
                        </button>
                    </div>
                    <div class="rounded-xl border border-surface-container-high bg-surface-container-lowest p-6">
                        <div class="flex items-end justify-between gap-3 h-48">
                            <div v-for="(h, i) in chartData" :key="i" class="flex-1 flex flex-col items-center gap-2">
                                <div class="w-full rounded-t" :class="h > 60 ? 'bg-primary' : 'bg-secondary-container'" :style="{ height: h + '%' }"></div>
                                <span class="font-label-sm text-label-sm text-on-surface-variant">{{ ['M','T','W','T','F','S','S'][i] }}</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- My Channel -->
                <section class="rounded-xl border border-surface-container-high bg-surface-container-lowest p-5">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div class="min-w-0">
                            <h3 class="font-label-md text-label-md text-on-surface">My Channel</h3>
                            <p class="font-label-sm text-label-sm text-on-surface-variant mt-1 truncate">{{ channelUrl }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button class="px-4 py-2 rounded-full border border-outline-variant font-label-sm text-label-sm text-on-surface-variant hover:bg-surface-container-high transition-colors" @click="copyChannelLink">{{ copied ? 'Copied!' : 'Copy Link' }}</button>
                            <Link :href="channelUrl" class="px-4 py-2 rounded-full bg-primary text-on-primary font-label-sm text-label-sm hover:opacity-90 transition-opacity">View Channel</Link>
                        </div>
                    </div>
                    <div v-if="!profile.is_verified" class="mt-4 rounded-lg bg-error-container/40 px-4 py-3 font-label-sm text-label-sm text-on-error-container">
                        You're not verified yet. Verified creators get their uploads auto-approved.
                    </div>
                </section>

                <!-- Recent Content -->
                <section>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-headline-md text-headline-md text-on-surface">Recent Content</h3>
                        <Link :href="route('growstream.creator.videos.index')" class="flex items-center gap-1 font-label-md text-label-md text-primary hover:opacity-80 transition-opacity">
                            View All <span class="material-symbols-outlined text-[18px]" aria-hidden="true">chevron_right</span>
                        </Link>
                    </div>

                    <div v-if="recentVideos.length === 0" class="rounded-xl border border-dashed border-outline-variant p-12 text-center">
                        <span class="material-symbols-outlined text-4xl text-on-surface-variant" aria-hidden="true">video_library</span>
                        <p class="font-label-md text-label-md text-on-surface mt-3">Upload your first video</p>
                        <Link :href="route('growstream.creator.videos.create')" class="inline-block mt-4 px-6 py-3 rounded-full bg-primary text-on-primary font-label-md text-label-md">Start Uploading</Link>
                    </div>

                    <div v-else class="flex flex-col gap-3">
                        <div
                            v-for="video in recentVideos.slice(0, 5)"
                            :key="video.id"
                            class="flex items-center gap-4 p-3 rounded-lg border border-surface-container-high bg-surface-container-lowest hover:bg-surface-container-low transition-colors"
                        >
                            <!-- Processing -->
                            <div v-if="isProcessing(video)" class="w-20 h-14 shrink-0 rounded-md bg-surface-container-high flex items-center justify-center">
                                <span class="material-symbols-outlined text-on-surface-variant animate-spin-slow" aria-hidden="true">refresh</span>
                            </div>
                            <!-- Thumb -->
                            <div v-else class="relative w-20 h-14 shrink-0 rounded-md overflow-hidden bg-surface-container-high">
                                <div class="bg-cover bg-center w-full h-full absolute inset-0" :style="{ backgroundImage: `url('${video.thumbnail_url || fallbackThumb}')` }"></div>
                                <span class="absolute bottom-1 right-1 bg-black/80 text-white text-[10px] leading-none px-1 py-0.5 rounded">{{ formatDuration(video.duration) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-label-md text-label-md text-on-surface truncate">{{ video.title }}</h4>
                                <p v-if="isProcessing(video)" class="font-label-sm text-label-sm text-on-surface-variant mt-1">Processing HD Version…</p>
                                <div v-else class="flex items-center gap-3 mt-1">
                                    <span class="flex items-center gap-1 font-label-sm text-label-sm text-on-surface-variant"><span class="material-symbols-outlined text-[16px]" aria-hidden="true">visibility</span>{{ formatViews(video.view_count) }}</span>
                                    <span class="flex items-center gap-1 font-label-sm text-label-sm text-on-surface-variant"><span class="material-symbols-outlined text-[16px]" aria-hidden="true">thumb_up</span>{{ formatViews(video.like_count) }}</span>
                                </div>
                                <div v-if="isProcessing(video)" class="w-full h-1.5 rounded-full bg-surface-container-high mt-2 overflow-hidden">
                                    <div class="h-full rounded-full bg-primary" :style="{ width: '84%' }"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- FAB -->
            <Link
                :href="route('growstream.creator.videos.create')"
                class="fixed md:absolute bottom-24 md:bottom-8 right-6 md:right-8 w-14 h-14 rounded-full bg-primary text-on-primary flex items-center justify-center shadow-lg hover:opacity-90 active:scale-95 transition-all z-40"
                aria-label="Upload video"
            >
                <span class="material-symbols-outlined text-[28px]" aria-hidden="true">add</span>
            </Link>
        </main>
    </CreatorStudioLayout>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import CreatorStudioLayout from '@/Layouts/CreatorStudioLayout.vue';

interface VideoRow {
    id: number;
    title: string;
    thumbnail_url?: string | null;
    content_type: string;
    upload_status: string;
    moderation_status: string;
    view_count: number;
    like_count?: number;
    duration?: number;
}

interface Props {
    profile: Record<string, any>;
    recentVideos?: VideoRow[];
    earningsSummary?: { total_earnings: number; pending_payout: number } | null;
    watchTimeHours?: number | null;
}

const props = withDefaults(defineProps<Props>(), {
    recentVideos: () => [],
    earningsSummary: () => ({ total_earnings: 0, pending_payout: 0 }),
    watchTimeHours: null,
});

const fallbackThumb = 'https://placehold.co/200x140/e1bfb4/191c1d?text=GrowStream';
const copied = ref(false);

const chartData = [38, 66, 28, 78, 88, 44, 32];

const channelUrl = computed(() => {
    const slug = props.profile.channel_slug || props.profile.display_name || 'creator';
    return `${window.location.origin}/c/${encodeURIComponent(slug)}`;
});

const copyChannelLink = async () => {
    try {
        await navigator.clipboard.writeText(channelUrl.value);
        copied.value = true;
        setTimeout(() => (copied.value = false), 2000);
    } catch { copied.value = false; }
};

const isProcessing = (video: VideoRow): boolean => {
    return ['uploading', 'processing'].includes(video.upload_status);
};

const formatMoney = (value?: number): string => {
    return Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2 });
};

const formatViews = (count?: number): string => {
    if (!count) return '0';
    if (count >= 1000000) return (count / 1000000).toFixed(1) + 'M';
    if (count >= 1000) return (count / 1000).toFixed(0) + 'K';
    return count.toString();
};

const formatDuration = (seconds?: number): string => {
    if (!seconds) return '0:00';
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    return `${m}:${s.toString().padStart(2, '0')}`;
};
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
@keyframes spin-slow { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
.animate-spin-slow { animation: spin-slow 1.6s linear infinite; }
</style>
