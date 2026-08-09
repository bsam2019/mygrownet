<template>
    <GrowStreamLayout :title="`${video.title} - GrowStream`">
        <main class="pb-24">
            <!-- Video Player -->
            <div class="relative w-full aspect-video bg-black overflow-hidden">
                <template v-if="userCanAccess">
                    <VideoPlayer
                        :video="video"
                        :start-position="watchHistory?.current_position || 0"
                        :autoplay="false"
                        :throttled="throttled"
                        @progress="handleProgress"
                        @ended="handleEnded"
                        @close="router.visit(route('growstream.browse'))"
                    />

                    <!-- Next Video Auto-Play Overlay -->
                    <div v-if="showNextOverlay && nextVideo" class="absolute inset-0 z-30 flex flex-col items-center justify-center bg-black/85 text-white backdrop-blur-sm p-6 text-center">
                        <span class="font-label-sm text-label-sm uppercase tracking-widest text-amber-400 mb-2">Up Next in {{ nextCountdown }}s</span>
                        <h3 class="font-headline-md text-headline-md text-white max-w-lg mb-4 line-clamp-2">{{ nextVideo.title }}</h3>
                        <div class="flex items-center gap-3">
                            <button @click="playNextVideo" class="bg-primary text-on-primary px-6 py-3 rounded-full font-label-md text-label-md font-bold">Play Now</button>
                            <button @click="cancelNextVideo" class="bg-white/20 text-white px-6 py-3 rounded-full font-label-md text-label-md border border-white/30 hover:bg-white/30">Cancel</button>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <div class="bg-cover bg-center w-full h-full absolute inset-0 opacity-80" :style="{ backgroundImage: `url('${video.thumbnail_url || video.poster_url || fallbackThumb}')` }"></div>
                    <!-- Center transport controls -->
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-white">
                        <div class="absolute inset-0 bg-black/40"></div>
                        <div class="relative flex flex-col items-center px-6 text-center">
                            <span class="material-symbols-outlined text-4xl mb-2" aria-hidden="true">lock</span>
                            <h2 class="font-headline-lg-mobile text-headline-lg-mobile mb-1">{{ video.access_level === 'premium' ? 'Premium Content' : 'Subscriber Content' }}</h2>
                            <p class="font-body-md text-body-md opacity-90 mb-6 max-w-sm">Subscribe to unlock it and stream the whole catalogue on demand.</p>
                            <div class="flex flex-wrap items-center justify-center gap-3">
                                <Link :href="route('growstream.subscription')" class="bg-primary text-on-primary px-6 py-3 rounded-full font-label-md text-label-md uppercase tracking-widest">
                                    Subscribe Now
                                </Link>
                                <button
                                    v-if="video.access_level !== 'free'"
                                    @click="startRental()"
                                    class="bg-white/20 backdrop-blur border border-white/40 px-6 py-3 rounded-full font-label-md text-label-md"
                                >
                                    Rent K{{ rentalPrice }}
                                </button>
                            </div>

                            <!-- Rental Form / states -->
                            <div v-if="rentalStep === 'form'" class="mt-6 w-full max-w-sm bg-surface-container-lowest rounded-lg p-6 text-on-surface">
                                <button @click="cancelRental()" class="float-right text-on-surface-variant"><span class="material-symbols-outlined">close</span></button>
                                <p class="font-label-md text-label-md mb-3">Rent this video for 48 hours</p>
                                <p class="font-headline-lg-mobile text-headline-lg-mobile text-primary mb-4">K{{ rentalPrice }}</p>
                                <input
                                    v-model="rentalPhone"
                                    type="tel" inputmode="tel" placeholder="0970000000"
                                    class="w-full mb-3 px-4 py-3 rounded-lg border border-outline-variant bg-surface-container-lowest text-on-surface text-body-md"
                                />
                                <button @click="rentVideo()" class="w-full bg-primary text-on-primary py-3 rounded-full font-label-md text-label-md">Pay K{{ rentalPrice }}</button>
                                <p v-if="rentalError" class="mt-2 text-sm text-error">{{ rentalError }}</p>
                            </div>
                            <div v-if="rentalStep === 'pending'" class="mt-6 w-full max-w-sm">
                                <p class="text-center text-white">Waiting for payment confirmation...</p>
                                <button @click="cancelRental()" class="mt-3 text-sm underline block mx-auto">Cancel</button>
                            </div>
                            <div v-if="rentalStep === 'failed'" class="mt-6 w-full max-w-sm">
                                <p class="text-center text-error">Payment not completed</p>
                                <button @click="startRental()" class="mt-3 mx-auto block px-4 py-2 rounded-full bg-white/20 border border-white/40 font-label-md text-label-md">Retry</button>
                            </div>
                            <div v-if="rentalStep === 'active'" class="mt-6 w-full max-w-sm bg-primary rounded-lg p-4 text-center text-on-primary">
                                <p class="font-label-md text-label-md">Access Granted!</p>
                                <button @click="router.reload()" class="mt-2 bg-surface-container-lowest text-primary px-4 py-2 rounded-full font-label-md text-label-md">Watch Now</button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Details panel -->
            <div class="px-margin-mobile pt-6">
                <h1 class="font-headline-lg-mobile text-headline-lg-mobile mb-2">{{ video.title }}</h1>
                <div class="flex flex-wrap items-center gap-2 font-label-sm text-label-sm text-on-surface-variant mb-6">
                    <span>{{ formatViews(video.view_count) }} views</span><span>•</span><span>{{ formatDate(video.created_at) }}</span>
                    <span v-if="video.content_type" class="bg-surface-container-low text-on-surface-variant px-3 py-1 rounded-full ml-1">{{ contentTypeLabel(video.content_type) }}</span>
                    <span :class="[accessBadge.color, 'bg-surface-container-low text-on-surface-variant px-3 py-1 rounded-full']">{{ accessBadge.text }}</span>
                </div>

                <!-- Creator row -->
                <div v-if="video.creator" class="flex items-center justify-between mb-6">
                    <Link :href="route('growstream.creator.profile', { slug: String(video.creator.id) })" class="flex items-center gap-3 min-w-0">
                        <img v-if="video.creator.avatar_url" class="w-12 h-12 rounded-full object-cover border border-outline-variant" :src="video.creator.avatar_url" :alt="video.creator.display_name" />
                        <div v-else class="w-12 h-12 rounded-full bg-primary text-on-primary flex items-center justify-center font-headline-md text-headline-md border border-outline-variant">
                            {{ (video.creator.display_name || 'C').charAt(0).toUpperCase() }}
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-label-md text-label-md truncate">{{ video.creator.display_name }}</h3>
                            <p class="font-label-sm text-label-sm text-on-surface-variant">{{ video.creator.subscriber_count ?? 0 }} Subscribers</p>
                        </div>
                    </Link>
                    <div class="flex items-center gap-2">
                        <button @click="toggleMinimize" class="bg-surface-container-low text-on-surface-variant px-4 py-2 rounded-full font-label-sm text-label-sm flex items-center gap-1" title="Minimize to PiP">
                            <span class="material-symbols-outlined text-base" aria-hidden="true">picture_in_picture_alt</span> PiP
                        </button>
                        <button @click="toggleWatchlist" class="bg-primary/10 text-primary px-4 py-2 rounded-full font-label-sm text-label-sm flex items-center gap-1" :disabled="watchlistLoading">
                            <span class="material-symbols-outlined text-base" aria-hidden="true">{{ isInWatchlist ? 'bookmark' : 'bookmark_add' }}</span>
                        </button>
                        <button @click="shareVideo" class="bg-surface-container-low text-on-surface-variant px-4 py-2 rounded-full font-label-sm text-label-sm flex items-center gap-1">
                            <span class="material-symbols-outlined text-base" aria-hidden="true">share</span> Share
                        </button>
                    </div>
                </div>

                <!-- Description -->
                <div class="bg-surface-container-low rounded-lg p-4 mb-8">
                    <p class="font-body-md text-body-md text-on-surface-variant whitespace-pre-line line-clamp-4">
                        {{ video.long_description || video.description }}
                    </p>
                    <button v-if="(video.long_description || video.description || '').length > 200" @click="expanded = !expanded" class="font-label-md text-label-md text-primary mt-2">
                        {{ expanded ? 'Show Less' : 'Show More' }}
                    </button>
                    <div v-if="video.tags && video.tags.length > 0" class="mt-3 flex flex-wrap gap-2">
                        <span v-for="tag in video.tags" :key="tag.id" class="bg-surface-container-lowest text-on-surface-variant px-3 py-1 rounded-full font-label-sm text-label-sm border border-outline-variant">#{{ tag.name }}</span>
                    </div>
                </div>

                <!-- Seasons Filter Tabs -->
                <div v-if="seasons && seasons.length > 0" class="mb-8 p-4 bg-surface-container rounded-xl border border-outline-variant/60">
                    <h3 class="font-headline-sm text-headline-sm mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary" aria-hidden="true">tv</span> Seasons
                    </h3>
                    <div class="flex items-center gap-2 overflow-x-auto pb-1">
                        <button
                            v-for="season in seasons"
                            :key="season.id"
                            @click="activeSeasonId = season.id"
                            :class="[
                                'px-4 py-2 rounded-full font-label-md text-label-md transition-all whitespace-nowrap',
                                activeSeasonId === season.id
                                    ? 'bg-primary text-on-primary font-bold shadow-sm'
                                    : 'bg-surface-container-high text-on-surface hover:bg-surface-container-highest'
                            ]"
                        >
                            Season {{ season.season_number }} — {{ season.title || ('Season ' + season.season_number) }}
                        </button>
                    </div>
                </div>

                <!-- Chapter Markers -->
                <div v-if="video.chapters && video.chapters.length > 0" class="mb-8 p-4 bg-surface-container rounded-xl border border-outline-variant/60">
                    <h3 class="font-headline-sm text-headline-sm mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary" aria-hidden="true">format_list_bulleted</span> Chapters
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="chap in video.chapters"
                            :key="chap.title"
                            @click="jumpToChapter(chap.timestamp)"
                            class="bg-surface-container-high hover:bg-primary/20 hover:text-primary text-on-surface px-3 py-1.5 rounded-lg font-label-sm text-label-sm border border-outline-variant/40 flex items-center gap-1.5 transition-colors"
                        >
                            <span class="font-mono text-xs bg-primary/20 text-primary px-1.5 py-0.5 rounded font-bold">{{ formatSeconds(chap.timestamp) }}</span>
                            <span>{{ chap.title }}</span>
                        </button>
                    </div>
                </div>

                <!-- Up Next -->
                <div v-if="relatedVideos.length > 0">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="font-headline-md text-headline-md">Up Next</h2>
                        <div class="flex items-center gap-2">
                            <span class="font-label-sm text-label-sm text-on-surface-variant">Autoplay</span>
                            <div class="w-10 h-6 bg-primary rounded-full relative" @click="autoplay = !autoplay">
                                <div class="absolute top-0.5 w-5 h-5 bg-white rounded-full transition-all" :class="autoplay ? 'right-0.5' : 'left-0.5'"></div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-4">
                        <Link v-for="relatedVideo in relatedVideos.slice(0, 6)" :key="relatedVideo.id" :href="route('growstream.video.detail', relatedVideo.slug)" class="flex gap-4">
                            <div class="relative w-36 aspect-video rounded-lg overflow-hidden bg-surface-container-highest flex-shrink-0">
                                <div class="bg-cover bg-center w-full h-full absolute inset-0" :style="{ backgroundImage: `url('${relatedVideo.thumbnail_url || fallbackThumb}')` }"></div>
                                <span class="absolute bottom-1 right-1 bg-black/70 text-white text-[9px] px-1 rounded">{{ formatDuration(relatedVideo.duration) }}</span>
                            </div>
                            <div class="flex-1 min-w-0 flex flex-col justify-center">
                                <p class="font-label-md text-label-md line-clamp-2">{{ relatedVideo.title }}</p>
                                <p class="font-label-sm text-label-sm text-on-surface-variant mt-1">{{ relatedVideo.creator?.name }} • {{ formatViews(relatedVideo.view_count) }} views</p>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </main>
    </GrowStreamLayout>
</template>

<script setup lang="ts">
import { ref, computed, onBeforeUnmount } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import GrowStreamLayout from '@/Layouts/GrowStreamLayout.vue';
import VideoPlayer from '@/Components/GrowStream/VideoPlayer.vue';
import { useGrowStream } from '@/composables/useGrowStream';
import { useGrowStreamMetrics } from '@/composables/useGrowStreamMetrics';
import type { Video, WatchHistory, Watchlist } from '@/types/growstream';
import axios from 'axios';

interface Season {
    id: number;
    season_number: number;
    title: string;
    description?: string;
}

interface Props {
    video: Video;
    relatedVideos?: Video[];
    seasons?: Season[];
    watchHistory?: WatchHistory;
    watchlistItem?: Watchlist;
    userCanAccess?: boolean;
    throttled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    relatedVideos: () => [],
    seasons: () => [],
    userCanAccess: true,
    throttled: false,
});

const activeSeasonId = ref<number | null>(props.seasons?.[0]?.id || null);

const { formatDuration, getAccessLevelBadge, addToWatchlist, removeFromWatchlist } = useGrowStream();
const metrics = useGrowStreamMetrics();

const fallbackThumb = 'https://placehold.co/440x248/e1bfb4/191c1d?text=GrowStream';
const expanded = ref(false);
const autoplay = ref(true);

const watchlistLoading = ref(false);
const isInWatchlist = ref(!!props.watchlistItem);
const accessBadge = computed(() => getAccessLevelBadge(props.video.access_level));

import { useMiniPlayer } from '@/composables/useMiniPlayer';

const { minimizeVideo } = useMiniPlayer();

const currentPos = ref(props.watchHistory?.current_position || 0);

const handleProgress = (position: number, duration: number) => {
    currentPos.value = position;
};

const toggleMinimize = () => {
    minimizeVideo(props.video, currentPos.value);
};

const jumpToChapter = (seconds: number) => {
    // Dispatch postMessage event to iframe to seek to chapter timestamp
    const iframe = document.querySelector('iframe');
    if (iframe && iframe.contentWindow) {
        iframe.contentWindow.postMessage(JSON.stringify({
            method: 'setCurrentTime',
            value: seconds
        }), '*');
    }
};

const formatSeconds = (sec: number): string => {
    const mins = Math.floor(sec / 60);
    const remainder = Math.floor(sec % 60);
    return `${mins}:${remainder < 10 ? '0' : ''}${remainder}`;
};

const contentTypeLabel = (key: string): string => {
    const map: Record<string, string> = {
        movie: 'Movie', series: 'Series', episode: 'Episode', short: 'Short',
        comedy: 'Comedy', skit: 'Skits', soap: 'Soap Opera', drama: 'Drama',
        documentary: 'Documentary', reality: 'Reality & Talk', music: 'Music',
        kids: 'Kids & Family', lifestyle: 'Lifestyle', faith: 'Faith-Based',
    };
    return map[key] ?? key;
};

const nextVideo = computed(() => props.relatedVideos?.[0] ?? null);
const showNextOverlay = ref(false);
const nextCountdown = ref(10);
let nextTimer: ReturnType<typeof setInterval> | null = null;

const handleEnded = () => {
    if (nextVideo.value && autoplay.value) {
        showNextOverlay.value = true;
        nextCountdown.value = 10;
        if (nextTimer) clearInterval(nextTimer);
        nextTimer = setInterval(() => {
            nextCountdown.value -= 1;
            if (nextCountdown.value <= 0) {
                playNextVideo();
            }
        }, 1000);
    }
};

const cancelNextVideo = () => {
    showNextOverlay.value = false;
    if (nextTimer) {
        clearInterval(nextTimer);
        nextTimer = null;
    }
};

const playNextVideo = () => {
    cancelNextVideo();
    if (nextVideo.value) {
        router.visit(route('growstream.video.detail', { slug: nextVideo.value.slug }));
    }
};

onBeforeUnmount(() => {
    if (nextTimer) clearInterval(nextTimer);
});

const toggleWatchlist = async () => {
    watchlistLoading.value = true;
    try {
        if (isInWatchlist.value && props.watchlistItem) {
            await removeFromWatchlist(props.watchlistItem.id);
            isInWatchlist.value = false;
        } else {
            await addToWatchlist('video', props.video.id);
            isInWatchlist.value = true;
        }
    } catch (error) {
        console.error('Failed to update watchlist:', error);
    } finally {
        watchlistLoading.value = false;
    }
};

const shareVideo = async () => {
    const url = window.location.href;
    const title = props.video.title;
    try {
        await navigator.share({ title, url });
    } catch {
        try { await navigator.clipboard.writeText(url); } catch { /* noop */ }
    }
};

// PPV / Rental
const rentalStep = ref<'idle' | 'form' | 'pending' | 'active' | 'failed'>('idle');
const rentalPhone = ref('');
const rentalError = ref('');
const rentalRef = ref<string | null>(null);
let rentalPoll: number | null = null;
const rentalPrice = 15;

const startRental = () => { rentalStep.value = 'form'; };
const cancelRental = () => { rentalStep.value = 'idle'; rentalError.value = ''; };

const isValidRentalPhone = computed(() =>
    /^(09[567]\d{7}|07[567]\d{7}|\+?2609[567]\d{7})$/.test(rentalPhone.value.trim())
);

const rentVideo = async () => {
    if (!isValidRentalPhone.value) { rentalError.value = 'Enter a valid Zambian mobile money number'; return; }
    rentalError.value = '';
    rentalStep.value = 'pending';
    try {
        const resp = await axios.post(route('growstream.rent', { video: props.video.id }), { phone_number: rentalPhone.value.trim() });
        if (resp.data.already_rented) { rentalStep.value = 'active'; return; }
        rentalRef.value = resp.data.transaction?.reference ?? null;
        startRentalPoll();
    } catch (e: any) {
        rentalStep.value = 'failed';
        rentalError.value = e.response?.data?.error || 'Rental initiation failed. Try again.';
    }
};

const startRentalPoll = () => {
    stopRentalPoll();
    rentalPoll = window.setInterval(async () => {
        if (!rentalRef.value) return;
        try {
            const resp = await axios.get(route('growstream.rental-status', { reference: rentalRef.value }));
            if (resp.data.status === 'active') { rentalStep.value = 'active'; stopRentalPoll(); }
            else if (resp.data.status === 'failed') { rentalStep.value = 'failed'; rentalError.value = 'Payment was not completed.'; stopRentalPoll(); }
        } catch { /* keep polling */ }
    }, 4000);
};

const stopRentalPoll = () => { if (rentalPoll) { window.clearInterval(rentalPoll); rentalPoll = null; } };
onBeforeUnmount(stopRentalPoll);

const formatViews = (views: number): string => {
    if (views >= 1000000) return `${(views / 1000000).toFixed(1)}M`;
    if (views >= 1000) return `${(views / 1000).toFixed(1)}K`;
    return views.toString();
};

const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    const now = new Date();
    const diffDays = Math.ceil(Math.abs(now.getTime() - date.getTime()) / (1000 * 60 * 60 * 24));
    if (diffDays === 0) return 'Today';
    if (diffDays === 1) return 'Yesterday';
    if (diffDays < 7) return `${diffDays} days ago`;
    if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`;
    if (diffDays < 365) return `${Math.floor(diffDays / 30)} months ago`;
    return `${Math.floor(diffDays / 365)} years ago`;
};
</script>
