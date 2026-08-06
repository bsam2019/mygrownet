<template>
    <GrowStreamLayout :title="`${video.title} - GrowStream`">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Video Player / Paywall Gate -->
            <div class="mb-8">
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
                </template>

                <!-- Subscribe prompt for gated content -->
                <div v-else class="gs-card relative flex flex-col items-center justify-center overflow-hidden p-10 text-center sm:p-16">
                    <div class="absolute inset-0 bg-gradient-to-br from-[var(--gs-primary)]/20 via-[#065f46]/30 to-[#022c22]/40"></div>
                    <div class="relative flex flex-col items-center">
                        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[var(--gs-accent)]">
                            <svg class="h-8 w-8 text-[#1a1608]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <h2 class="mb-2 text-2xl font-bold text-[var(--gs-text)] sm:text-3xl">
                            {{ video.access_level === 'premium' ? 'Premium Content' : 'Subscriber Content' }}
                        </h2>
                        <p class="mb-6 max-w-md text-[var(--gs-muted)]">
                            {{ video.access_level === 'premium' ? 'This video is part of the premium catalogue.' : 'This video is available to subscribers.' }}
                            Subscribe to unlock it and stream the whole catalogue on demand.
                        </p>
                        <div class="flex flex-wrap items-center justify-center gap-3">
                            <Link
                                :href="route('growstream.subscription')"
                                class="gs-btn gs-btn-accent px-8 py-3 text-lg"
                            >
                                Subscribe Now
                            </Link>
                            <button
                                v-if="video.access_level !== 'free'"
                                @click="startRental()"
                                class="gs-btn gs-btn-outline px-6 py-3 text-lg"
                            >
                                Rent K{{ rentalPrice }}
                            </button>
                            <Link
                                :href="route('growstream.browse')"
                                class="gs-btn gs-btn-outline px-6 py-3 text-lg"
                            >
                                Browse Free Content
                            </Link>
                        </div>

                        <!-- Rental Form -->
                        <div v-if="rentalStep === 'form'" class="relative mt-6 w-full max-w-sm mx-auto bg-[var(--gs-bg)] rounded-2xl p-6 border border-[var(--gs-border)]">
                            <button @click="cancelRental()" class="absolute top-3 right-3 text-[var(--gs-muted)] hover:text-[var(--gs-text)]">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                            <p class="text-sm font-semibold text-[var(--gs-text)] mb-3">Rent this video for 48 hours</p>
                            <p class="text-lg font-bold text-[var(--gs-accent)] mb-4">K{{ rentalPrice }}</p>
                            <input
                                v-model="rentalPhone"
                                type="tel" inputmode="tel" placeholder="0970000000"
                                class="w-full mb-3 px-4 py-2.5 rounded-xl border border-[var(--gs-border)] bg-[var(--gs-bg)] text-sm text-[var(--gs-text)]"
                            />
                            <button
                                @click="rentVideo()"
                                class="gs-btn gs-btn-accent w-full py-2.5 text-sm font-semibold"
                            >
                                Pay K{{ rentalPrice }}
                            </button>
                            <p v-if="rentalError" class="mt-2 text-xs text-red-500">{{ rentalError }}</p>
                        </div>

                        <!-- Rental Pending -->
                        <div v-if="rentalStep === 'pending'" class="relative mt-6 w-full max-w-sm mx-auto">
                            <p class="text-sm text-[var(--gs-muted)] text-center">Waiting for payment confirmation...</p>
                            <p class="text-xs text-[var(--gs-muted)] text-center mt-1">Approve the prompt on your phone</p>
                            <button @click="cancelRental()" class="mt-3 text-xs text-[var(--gs-muted)] underline block mx-auto">Cancel</button>
                        </div>

                        <!-- Rental Failed -->
                        <div v-if="rentalStep === 'failed'" class="relative mt-6 w-full max-w-sm mx-auto">
                            <p class="text-sm text-red-500 text-center">Payment not completed</p>
                            <button @click="startRental()" class="gs-btn gs-btn-outline mt-3 mx-auto block px-4 py-2 text-sm">Retry</button>
                        </div>

                        <!-- Rental Active -->
                        <div v-if="rentalStep === 'active'" class="relative mt-6 w-full max-w-sm mx-auto bg-emerald-50 rounded-2xl p-4 text-center border border-emerald-200">
                            <p class="text-sm font-semibold text-emerald-700">Access Granted!</p>
                            <p class="text-xs text-emerald-600 mt-1">Refresh the page to start watching.</p>
                            <button
                                @click="router.reload()"
                                class="gs-btn gs-btn-accent mt-3 px-4 py-2 text-sm"
                            >
                                Watch Now
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <!-- Main Content -->
                <div class="lg:col-span-2">
                    <!-- Video Info -->
                    <div class="mb-6">
                        <h1 class="mb-2 text-3xl font-bold text-[var(--gs-text)]">{{ video.title }}</h1>
                        
                        <!-- Metadata -->
                        <div class="mb-4 flex flex-wrap items-center gap-4 text-sm text-[var(--gs-muted)]">
                            <span class="flex items-center gap-1">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                    />
                                </svg>
                                {{ formatViews(video.view_count) }} views
                            </span>
                            <span>{{ formatDate(video.created_at) }}</span>
                            <span :class="[accessBadge.color, 'rounded px-2 py-1 text-xs font-medium text-white']">
                                {{ accessBadge.text }}
                            </span>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-wrap gap-3">
                            <button
                                @click="toggleWatchlist"
                                :disabled="watchlistLoading"
                                class="flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-[var(--gs-text)] hover:bg-[var(--gs-bg-elevated)] disabled:opacity-50"
                            >
                                <svg
                                    :class="[isInWatchlist ? 'fill-current' : 'fill-none']"
                                    class="h-5 w-5"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"
                                    />
                                </svg>
                                {{ isInWatchlist ? 'Remove from Watchlist' : 'Add to Watchlist' }}
                            </button>

                            <button
                                @click="shareVideo"
                                class="flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-[var(--gs-text)] hover:bg-[var(--gs-bg-elevated)]"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"
                                    />
                                </svg>
                                Share
                            </button>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-6 gs-surface p-6">
                        <h2 class="mb-3 text-lg font-semibold text-[var(--gs-text)]">About</h2>
                        <p class="whitespace-pre-line text-[var(--gs-text)]">
                            {{ video.long_description || video.description }}
                        </p>

                        <!-- Tags -->
                        <div v-if="video.tags && video.tags.length > 0" class="mt-4 flex flex-wrap gap-2">
                            <span
                                v-for="tag in video.tags"
                                :key="tag.id"
                                class="gs-chip gs-chip-primary"
                            >
                                #{{ tag.name }}
                            </span>
                        </div>
                    </div>

                    <!-- Creator Info -->
                    <div v-if="video.creator" class="mb-6 gs-surface p-4">
                        <Link
                            :href="route('growstream.creator.profile', { slug: String(video.creator.id) })"
                            class="flex items-center gap-4"
                        >
                            <div class="h-16 w-16 shrink-0 overflow-hidden rounded-full bg-[var(--gs-bg-elevated)]">
                                <img
                                    v-if="video.creator.avatar_url"
                                    :src="video.creator.avatar_url"
                                    :alt="video.creator.display_name"
                                    class="h-full w-full object-cover"
                                />
                                <div v-else class="flex h-full w-full items-center justify-center text-2xl font-medium text-[var(--gs-muted)]">
                                    {{ (video.creator.display_name || 'C').charAt(0).toUpperCase() }}
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-[var(--gs-text)] hover:text-[var(--gs-accent)]">{{ video.creator.display_name }}</h3>
                                <p class="text-sm text-[var(--gs-muted)]">
                                    {{ video.creator.subscriber_count ?? 0 }} subscribers · {{ video.creator.total_videos ?? 0 }} videos
                                </p>
                            </div>
                        </Link>
                        <div class="mt-4 flex gap-2">
                            <Link
                                :href="route('growstream.creator.profile', { slug: String(video.creator.id) })"
                                class="gs-btn gs-btn-outline flex-1"
                            >
                                View Profile
                            </Link>
                            <button class="gs-btn gs-btn-primary flex-1" @click="onFollow">
                                Follow
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <!-- Related Videos -->
                    <div v-if="relatedVideos.length > 0">
                        <h2 class="mb-4 text-lg font-semibold text-[var(--gs-text)]">Related Videos</h2>
                        <div class="space-y-4">
                            <Link
                                v-for="relatedVideo in relatedVideos"
                                :key="relatedVideo.id"
                                :href="route('growstream.video.detail', relatedVideo.slug)"
                                class="flex gap-3 rounded-lg transition-colors hover:bg-[var(--gs-bg-elevated)]"
                            >
                                <div class="relative h-24 w-40 flex-shrink-0 overflow-hidden rounded-lg bg-[var(--gs-bg-elevated)]">
                                    <img
                                        v-if="relatedVideo.thumbnail_url"
                                        :src="relatedVideo.thumbnail_url"
                                        :alt="relatedVideo.title"
                                        class="h-full w-full object-cover"
                                    />
                                    <div class="absolute bottom-1 right-1 rounded bg-black/80 px-1 text-xs text-white">
                                        {{ formatDuration(relatedVideo.duration) }}
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="mb-1 line-clamp-2 text-sm font-medium text-[var(--gs-text)]">
                                        {{ relatedVideo.title }}
                                    </h3>
                                    <p class="text-xs text-[var(--gs-muted)]">
                                        {{ relatedVideo.creator?.name }}
                                    </p>
                                    <p class="text-xs text-[var(--gs-muted)]">
                                        {{ formatViews(relatedVideo.view_count) }} views
                                    </p>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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

interface Props {
    video: Video;
    relatedVideos?: Video[];
    watchHistory?: WatchHistory;
    watchlistItem?: Watchlist;
    userCanAccess?: boolean;
    throttled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    relatedVideos: () => [],
    userCanAccess: true,
    throttled: false,
});

const { formatDuration, getAccessLevelBadge, addToWatchlist, removeFromWatchlist } = useGrowStream();

const metrics = useGrowStreamMetrics();

const onFollow = () => {
    if (props.video.creator) {
        metrics.trackCreatorSubscribe(props.video.creator.id);
    }
};

const watchlistLoading = ref(false);
const isInWatchlist = ref(!!props.watchlistItem);

const accessBadge = computed(() => getAccessLevelBadge(props.video.access_level));

const handleProgress = (position: number, duration: number) => {
    // Progress is automatically saved by the player component
    console.log(`Progress: ${position}/${duration}`);
};

const handleEnded = () => {
    console.log('Video ended');
    // Could show related videos or next episode
};

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

const rentalPrice = 15; // matches config('growstream.ppv.price')

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
        const resp = await axios.post(route('growstream.rent', { video: props.video.id }), {
            phone_number: rentalPhone.value.trim(),
        });

        if (resp.data.already_rented) {
            rentalStep.value = 'active';
            return;
        }

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
            if (resp.data.status === 'active') {
                rentalStep.value = 'active';
                stopRentalPoll();
            } else if (resp.data.status === 'failed') {
                rentalStep.value = 'failed';
                rentalError.value = 'Payment was not completed.';
                stopRentalPoll();
            }
        } catch { /* keep polling */ }
    }, 4000);
};

const stopRentalPoll = () => {
    if (rentalPoll) { window.clearInterval(rentalPoll); rentalPoll = null; }
};

onBeforeUnmount(stopRentalPoll);

const formatViews = (views: number): string => {
    if (views >= 1000000) {
        return `${(views / 1000000).toFixed(1)}M`;
    }
    if (views >= 1000) {
        return `${(views / 1000).toFixed(1)}K`;
    }
    return views.toString();
};

const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = Math.abs(now.getTime() - date.getTime());
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays === 0) return 'Today';
    if (diffDays === 1) return 'Yesterday';
    if (diffDays < 7) return `${diffDays} days ago`;
    if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`;
    if (diffDays < 365) return `${Math.floor(diffDays / 30)} months ago`;
    return `${Math.floor(diffDays / 365)} years ago`;
};
</script>

