<template>
    <AppLayout :title="`${video.title} - GrowStream`">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Video Player -->
            <div class="mb-8">
                <VideoPlayer
                    :video="video"
                    :start-position="watchHistory?.current_position || 0"
                    :autoplay="false"
                    @progress="handleProgress"
                    @ended="handleEnded"
                    @close="router.visit(route('growstream.browse'))"
                />
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <!-- Main Content -->
                <div class="lg:col-span-2">
                    <!-- Video Info -->
                    <div class="mb-6">
                        <h1 class="mb-2 text-3xl font-bold text-gray-900">{{ video.title }}</h1>
                        
                        <!-- Metadata -->
                        <div class="mb-4 flex flex-wrap items-center gap-4 text-sm text-gray-600">
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
                                class="flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
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

                            <button class="flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
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
                    <div class="mb-6 rounded-lg bg-gray-50 p-6">
                        <h2 class="mb-3 text-lg font-semibold text-gray-900">About</h2>
                        <p class="whitespace-pre-line text-gray-700">
                            {{ video.long_description || video.description }}
                        </p>

                        <!-- Tags -->
                        <div v-if="video.tags && video.tags.length > 0" class="mt-4 flex flex-wrap gap-2">
                            <span
                                v-for="tag in video.tags"
                                :key="tag.id"
                                class="rounded-full bg-blue-100 px-3 py-1 text-sm text-blue-800"
                            >
                                #{{ tag.name }}
                            </span>
                        </div>
                    </div>

                    <!-- Creator Info -->
                    <div v-if="video.creator" class="mb-6 flex items-center gap-4 rounded-lg border border-gray-200 p-4">
                        <div class="h-16 w-16 overflow-hidden rounded-full bg-gray-200">
                            <img
                                v-if="video.creator.avatar"
                                :src="video.creator.avatar"
                                :alt="video.creator.name"
                                class="h-full w-full object-cover"
                            />
                            <div v-else class="flex h-full w-full items-center justify-center text-2xl font-medium text-gray-600">
                                {{ video.creator.name.charAt(0).toUpperCase() }}
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900">{{ video.creator.name }}</h3>
                            <p class="text-sm text-gray-600">Content Creator</p>
                        </div>
                        <button class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                            Follow
                        </button>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <!-- Related Videos -->
                    <div v-if="relatedVideos.length > 0">
                        <h2 class="mb-4 text-lg font-semibold text-gray-900">Related Videos</h2>
                        <div class="space-y-4">
                            <Link
                                v-for="relatedVideo in relatedVideos"
                                :key="relatedVideo.id"
                                :href="route('growstream.video.detail', relatedVideo.slug)"
                                class="flex gap-3 rounded-lg transition-colors hover:bg-gray-50"
                            >
                                <div class="relative h-24 w-40 flex-shrink-0 overflow-hidden rounded-lg bg-gray-200">
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
                                    <h3 class="mb-1 line-clamp-2 text-sm font-medium text-gray-900">
                                        {{ relatedVideo.title }}
                                    </h3>
                                    <p class="text-xs text-gray-600">
                                        {{ relatedVideo.creator?.name }}
                                    </p>
                                    <p class="text-xs text-gray-600">
                                        {{ formatViews(relatedVideo.view_count) }} views
                                    </p>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import VideoPlayer from '@/Components/GrowStream/VideoPlayer.vue';
import { useGrowStream } from '@/composables/useGrowStream';
import type { Video, WatchHistory, Watchlist } from '@/types/growstream';

interface Props {
    video: Video;
    relatedVideos: Video[];
    watchHistory?: WatchHistory;
    watchlistItem?: Watchlist;
}

const props = defineProps<Props>();

const { formatDuration, getAccessLevelBadge, addToWatchlist, removeFromWatchlist } = useGrowStream();

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
            await addToWatchlist('App\\Domain\\GrowStream\\Infrastructure\\Persistence\\Eloquent\\Video', props.video.id);
            isInWatchlist.value = true;
        }
    } catch (error) {
        console.error('Failed to update watchlist:', error);
    } finally {
        watchlistLoading.value = false;
    }
};

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
