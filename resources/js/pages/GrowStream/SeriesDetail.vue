<template>
    <AppLayout :title="`${series.title} - GrowStream`">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Series Header -->
            <div class="mb-8 grid grid-cols-1 gap-8 lg:grid-cols-3">
                <!-- Poster -->
                <div class="lg:col-span-1">
                    <div class="sticky top-8">
                        <img
                            v-if="series.poster_url"
                            :src="series.poster_url"
                            :alt="series.title"
                            class="w-full rounded-lg shadow-lg"
                        />
                        <div v-else class="aspect-[2/3] w-full rounded-lg bg-gray-200"></div>
                        
                        <!-- Actions -->
                        <div class="mt-4 space-y-3">
                            <button
                                @click="toggleWatchlist"
                                :disabled="watchlistLoading"
                                class="flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
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
                                {{ isInWatchlist ? 'In Watchlist' : 'Add to Watchlist' }}
                            </button>
                            
                            <button class="flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50">
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
                </div>

                <!-- Info -->
                <div class="lg:col-span-2">
                    <h1 class="mb-4 text-4xl font-bold text-gray-900">{{ series.title }}</h1>
                    
                    <!-- Metadata -->
                    <div class="mb-6 flex flex-wrap items-center gap-4 text-sm text-gray-600">
                        <span>{{ series.total_episodes }} episodes</span>
                        <span v-if="series.total_seasons > 1">{{ series.total_seasons }} seasons</span>
                        <span v-if="series.release_year">{{ series.release_year }}</span>
                        <span :class="[accessBadge.color, 'rounded px-2 py-1 text-xs font-medium text-white']">
                            {{ accessBadge.text }}
                        </span>
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <p class="whitespace-pre-line text-gray-700">
                            {{ series.long_description || series.description }}
                        </p>
                    </div>

                    <!-- Categories -->
                    <div v-if="series.categories && series.categories.length > 0" class="mb-6">
                        <h3 class="mb-2 text-sm font-medium text-gray-900">Categories</h3>
                        <div class="flex flex-wrap gap-2">
                            <Link
                                v-for="category in series.categories"
                                :key="category.id"
                                :href="route('growstream.browse', { category: category.slug })"
                                class="rounded-full bg-blue-100 px-3 py-1 text-sm text-blue-800 hover:bg-blue-200"
                            >
                                {{ category.name }}
                            </Link>
                        </div>
                    </div>

                    <!-- Creator -->
                    <div v-if="series.creator" class="flex items-center gap-4 rounded-lg border border-gray-200 p-4">
                        <div class="h-12 w-12 overflow-hidden rounded-full bg-gray-200">
                            <img
                                v-if="series.creator.avatar"
                                :src="series.creator.avatar"
                                :alt="series.creator.name"
                                class="h-full w-full object-cover"
                            />
                            <div v-else class="flex h-full w-full items-center justify-center text-lg font-medium text-gray-600">
                                {{ series.creator.name.charAt(0).toUpperCase() }}
                            </div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ series.creator.name }}</h3>
                            <p class="text-sm text-gray-600">Content Creator</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Episodes -->
            <div class="mb-8">
                <h2 class="mb-6 text-2xl font-bold text-gray-900">Episodes</h2>
                
                <!-- Season Selector (if multiple seasons) -->
                <div v-if="series.total_seasons > 1" class="mb-6">
                    <select
                        v-model="selectedSeason"
                        class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option v-for="season in series.total_seasons" :key="season" :value="season">
                            Season {{ season }}
                        </option>
                    </select>
                </div>

                <!-- Episode List -->
                <div class="space-y-4">
                    <Link
                        v-for="episode in filteredEpisodes"
                        :key="episode.id"
                        :href="route('growstream.video.detail', episode.slug)"
                        class="flex gap-4 rounded-lg border border-gray-200 p-4 transition hover:bg-gray-50"
                    >
                        <div class="relative h-24 w-40 flex-shrink-0 overflow-hidden rounded-lg bg-gray-200">
                            <img
                                v-if="episode.thumbnail_url"
                                :src="episode.thumbnail_url"
                                :alt="episode.title"
                                class="h-full w-full object-cover"
                            />
                            <div class="absolute bottom-1 right-1 rounded bg-black/80 px-1 text-xs text-white">
                                {{ formatDuration(episode.duration) }}
                            </div>
                            <!-- Progress bar -->
                            <div v-if="getWatchProgress(episode.id) > 0" class="absolute bottom-0 left-0 right-0 h-1 bg-gray-700">
                                <div :style="{ width: `${getWatchProgress(episode.id)}%` }" class="h-full bg-blue-600"></div>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="mb-1 flex items-start justify-between">
                                <h3 class="font-semibold text-gray-900">
                                    {{ episode.episode_number }}. {{ episode.title }}
                                </h3>
                                <span v-if="getWatchProgress(episode.id) === 100" class="text-green-600">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </span>
                            </div>
                            <p class="line-clamp-2 text-sm text-gray-600">{{ episode.description }}</p>
                            <div class="mt-2 flex items-center gap-4 text-xs text-gray-500">
                                <span>{{ formatViews(episode.view_count) }} views</span>
                                <span v-if="getWatchProgress(episode.id) > 0 && getWatchProgress(episode.id) < 100">
                                    {{ Math.round(getWatchProgress(episode.id)) }}% watched
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useGrowStream } from '@/composables/useGrowStream';
import type { VideoSeries, Watchlist } from '@/types/growstream';

interface Props {
    series: VideoSeries;
    watchlistItem?: Watchlist;
    watchProgress?: Record<number, number>;
}

const props = defineProps<Props>();

const { formatDuration, getAccessLevelBadge, addToWatchlist, removeFromWatchlist } = useGrowStream();

const selectedSeason = ref(1);
const watchlistLoading = ref(false);
const isInWatchlist = ref(!!props.watchlistItem);

const accessBadge = computed(() => getAccessLevelBadge(props.series.access_level));

const filteredEpisodes = computed(() => {
    if (!props.series.videos) return [];
    return props.series.videos
        .filter((v) => v.season_number === selectedSeason.value)
        .sort((a, b) => (a.episode_number || 0) - (b.episode_number || 0));
});

const getWatchProgress = (videoId: number): number => {
    return props.watchProgress?.[videoId] || 0;
};

const toggleWatchlist = async () => {
    watchlistLoading.value = true;
    try {
        if (isInWatchlist.value && props.watchlistItem) {
            await removeFromWatchlist(props.watchlistItem.id);
            isInWatchlist.value = false;
        } else {
            await addToWatchlist('App\\Domain\\GrowStream\\Infrastructure\\Persistence\\Eloquent\\VideoSeries', props.series.id);
            isInWatchlist.value = true;
        }
    } catch (error) {
        console.error('Failed to update watchlist:', error);
    } finally {
        watchlistLoading.value = false;
    }
};

const formatViews = (views: number): string => {
    if (views >= 1000000) return `${(views / 1000000).toFixed(1)}M`;
    if (views >= 1000) return `${(views / 1000).toFixed(1)}K`;
    return views.toString();
};
</script>
