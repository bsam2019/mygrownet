<template>
    <AppLayout title="My Videos - GrowStream">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">My Videos</h1>
                <p class="mt-2 text-gray-600">Continue watching and manage your watchlist</p>
            </div>

            <!-- Tabs -->
            <div class="mb-8 border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button
                        @click="activeTab = 'continue'"
                        :class="[
                            activeTab === 'continue'
                                ? 'border-blue-600 text-blue-600'
                                : 'border-transparent text-gray-600 hover:border-gray-300 hover:text-gray-900',
                            'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium',
                        ]"
                    >
                        Continue Watching
                        <span
                            v-if="continueWatching.length > 0"
                            class="ml-2 rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-600"
                        >
                            {{ continueWatching.length }}
                        </span>
                    </button>
                    <button
                        @click="activeTab = 'watchlist'"
                        :class="[
                            activeTab === 'watchlist'
                                ? 'border-blue-600 text-blue-600'
                                : 'border-transparent text-gray-600 hover:border-gray-300 hover:text-gray-900',
                            'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium',
                        ]"
                    >
                        Watchlist
                        <span
                            v-if="watchlist.length > 0"
                            class="ml-2 rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-600"
                        >
                            {{ watchlist.length }}
                        </span>
                    </button>
                    <button
                        @click="activeTab = 'history'"
                        :class="[
                            activeTab === 'history'
                                ? 'border-blue-600 text-blue-600'
                                : 'border-transparent text-gray-600 hover:border-gray-300 hover:text-gray-900',
                            'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium',
                        ]"
                    >
                        Watch History
                    </button>
                </nav>
            </div>

            <!-- Continue Watching Tab -->
            <div v-show="activeTab === 'continue'">
                <VideoGrid
                    v-if="continueWatching.length > 0"
                    :videos="continueWatchingVideos"
                    :watch-progress="continueWatchingProgress"
                    :show-description="true"
                    empty-message="No videos in progress"
                    empty-sub-message="Start watching a video to see it here"
                />
                <div v-else class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 py-12">
                    <svg class="mb-4 h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"
                        />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-lg font-medium text-gray-900">No videos in progress</p>
                    <p class="mt-1 text-sm text-gray-600">Start watching a video to see it here</p>
                    <Link
                        :href="route('growstream.browse')"
                        class="mt-4 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                    >
                        Browse Videos
                    </Link>
                </div>
            </div>

            <!-- Watchlist Tab -->
            <div v-show="activeTab === 'watchlist'">
                <div v-if="watchlist.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <div v-for="item in watchlist" :key="item.id" class="relative">
                        <Link
                            :href="getWatchableRoute(item)"
                            class="block transition-transform hover:scale-[1.02]"
                        >
                            <VideoCard
                                v-if="item.watchable_type.includes('Video')"
                                :video="item.watchable as Video"
                                :show-description="true"
                            />
                            <div v-else class="rounded-lg bg-white shadow-sm">
                                <!-- Series card placeholder -->
                                <div class="aspect-video overflow-hidden rounded-t-lg bg-gray-200">
                                    <img
                                        v-if="(item.watchable as VideoSeries).poster_url"
                                        :src="(item.watchable as VideoSeries).poster_url"
                                        :alt="(item.watchable as VideoSeries).title"
                                        class="h-full w-full object-cover"
                                    />
                                </div>
                                <div class="p-4">
                                    <h3 class="font-semibold text-gray-900">{{ (item.watchable as VideoSeries).title }}</h3>
                                    <p class="text-sm text-gray-600">
                                        {{ (item.watchable as VideoSeries).total_episodes }} episodes
                                    </p>
                                </div>
                            </div>
                        </Link>
                        <button
                            @click="removeFromWatchlistHandler(item.id)"
                            class="absolute right-2 top-2 rounded-full bg-black/60 p-2 text-white hover:bg-black/80"
                            aria-label="Remove from watchlist"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div v-else class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 py-12">
                    <svg class="mb-4 h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"
                        />
                    </svg>
                    <p class="text-lg font-medium text-gray-900">Your watchlist is empty</p>
                    <p class="mt-1 text-sm text-gray-600">Save videos to watch later</p>
                    <Link
                        :href="route('growstream.browse')"
                        class="mt-4 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                    >
                        Browse Videos
                    </Link>
                </div>
            </div>

            <!-- Watch History Tab -->
            <div v-show="activeTab === 'history'">
                <div v-if="watchHistory.length > 0" class="space-y-4">
                    <div
                        v-for="history in watchHistory"
                        :key="history.id"
                        class="flex gap-4 rounded-lg border border-gray-200 p-4 hover:bg-gray-50"
                    >
                        <Link
                            :href="route('growstream.video.detail', history.video?.slug)"
                            class="relative h-32 w-48 flex-shrink-0 overflow-hidden rounded-lg bg-gray-200"
                        >
                            <img
                                v-if="history.video?.thumbnail_url"
                                :src="history.video.thumbnail_url"
                                :alt="history.video.title"
                                class="h-full w-full object-cover"
                            />
                            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gray-700">
                                <div :style="{ width: `${history.progress_percentage}%` }" class="h-full bg-blue-600"></div>
                            </div>
                        </Link>
                        <div class="flex-1">
                            <Link :href="route('growstream.video.detail', history.video?.slug)" class="group">
                                <h3 class="mb-1 text-lg font-semibold text-gray-900 group-hover:text-blue-600">
                                    {{ history.video?.title }}
                                </h3>
                            </Link>
                            <p class="mb-2 text-sm text-gray-600">{{ history.video?.description }}</p>
                            <div class="flex items-center gap-4 text-sm text-gray-600">
                                <span>{{ Math.round(history.progress_percentage) }}% complete</span>
                                <span>{{ formatDuration(history.watch_duration) }} watched</span>
                                <span>{{ formatDate(history.updated_at) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 py-12">
                    <svg class="mb-4 h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    <p class="text-lg font-medium text-gray-900">No watch history</p>
                    <p class="mt-1 text-sm text-gray-600">Videos you watch will appear here</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import VideoCard from '@/Components/GrowStream/VideoCard.vue';
import VideoGrid from '@/Components/GrowStream/VideoGrid.vue';
import { useGrowStream } from '@/composables/useGrowStream';
import type { Video, VideoSeries, WatchHistory, Watchlist } from '@/types/growstream';

interface Props {
    continueWatching: WatchHistory[];
    watchlist: Watchlist[];
    watchHistory: WatchHistory[];
}

const props = defineProps<Props>();

const { formatDuration, removeFromWatchlist } = useGrowStream();

const activeTab = ref<'continue' | 'watchlist' | 'history'>('continue');

const continueWatchingVideos = computed(() => {
    return props.continueWatching.map((h) => h.video).filter((v): v is Video => v !== undefined);
});

const continueWatchingProgress = computed(() => {
    const progress: Record<number, number> = {};
    props.continueWatching.forEach((h) => {
        if (h.video) {
            progress[h.video.id] = h.progress_percentage;
        }
    });
    return progress;
});

const getWatchableRoute = (item: Watchlist) => {
    if (item.watchable_type.includes('Video')) {
        return route('growstream.video.detail', (item.watchable as Video).slug);
    }
    return route('growstream.series.detail', (item.watchable as VideoSeries).slug);
};

const removeFromWatchlistHandler = async (watchlistId: number) => {
    try {
        await removeFromWatchlist(watchlistId);
        router.reload({ only: ['watchlist'] });
    } catch (error) {
        console.error('Failed to remove from watchlist:', error);
    }
};

const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = Math.abs(now.getTime() - date.getTime());
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays === 0) return 'Today';
    if (diffDays === 1) return 'Yesterday';
    if (diffDays < 7) return `${diffDays} days ago`;
    return date.toLocaleDateString();
};
</script>
