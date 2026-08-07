<template>
    <GrowStreamLayout title="Watch History - GrowStream">
        <main class="px-margin-mobile pt-6 pb-24">
            <!-- Today -->
            <section v-if="groupedHistory.today.length > 0" class="mb-8">
                <h2 class="font-headline-md text-headline-md mb-4 text-on-surface">Today</h2>
                <div class="flex flex-col gap-6">
                    <div v-for="item in groupedHistory.today" :key="item.id">
                        <div class="relative w-full aspect-video rounded-lg overflow-hidden bg-surface-container-highest">
                            <Link :href="route('growstream.video.detail', item.video?.slug)">
                                <img class="w-full h-full object-cover" :src="item.video?.thumbnail_url || fallbackThumb" :alt="item.video?.title" />
                                <div v-if="item.video?.content_type" class="absolute top-2 left-2 right-2 text-white font-headline-md leading-tight uppercase drop-shadow-md">
                                    <p class="text-lg">{{ item.video.title }}</p>
                                </div>
                                <span v-if="item.video?.creator?.name" class="absolute bottom-2 left-2 bg-black/70 text-white font-label-sm text-label-sm px-2 py-0.5 rounded">{{ item.video.creator.name }}</span>
                                <span class="absolute bottom-2 right-2 bg-black/80 text-white font-label-sm text-label-sm px-1.5 py-0.5 rounded">{{ formatDuration(item.video?.duration) }}</span>
                            </Link>
                        </div>
                        <div class="flex items-start justify-between mt-3">
                            <div class="min-w-0">
                                <Link :href="route('growstream.video.detail', item.video?.slug)">
                                    <h4 class="font-label-md text-label-md text-on-surface line-clamp-2">{{ item.video?.title }}</h4>
                                    <p class="font-label-sm text-label-sm text-on-surface-variant mt-1">{{ item.video?.creator?.name }}</p>
                                </Link>
                            </div>
                            <button class="text-on-surface-variant p-1 shrink-0" aria-label="Remove from history" @click="removeHistory(item.id)">
                                <span class="material-symbols-outlined text-xl" aria-hidden="true">close</span>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Yesterday -->
            <section v-if="groupedHistory.yesterday.length > 0" class="mb-8">
                <h2 class="font-headline-md text-headline-md mb-4 text-on-surface">Yesterday</h2>
                <div v-for="item in groupedHistory.yesterday" :key="item.id">
                    <div class="relative w-full aspect-video rounded-lg overflow-hidden bg-surface-container-highest">
                        <Link :href="route('growstream.video.detail', item.video?.slug)">
                            <img class="w-full h-full object-cover" :src="item.video?.thumbnail_url || fallbackThumb" :alt="item.video?.title" />
                            <div class="absolute bottom-0 left-0 h-1 bg-primary" :style="{ width: `${Math.min(item.progress_percentage || 0, 100)}%` }"></div>
                            <span class="absolute bottom-2 right-2 bg-black/80 text-white font-label-sm text-label-sm px-1.5 py-0.5 rounded">{{ formatDuration(item.video?.duration) }}</span>
                        </Link>
                    </div>
                    <div class="flex items-start justify-between mt-3">
                        <div class="min-w-0">
                            <Link :href="route('growstream.video.detail', item.video?.slug)">
                                <h4 class="font-label-md text-label-md text-on-surface line-clamp-2">{{ item.video?.title }}</h4>
                                <p class="font-label-sm text-label-sm text-on-surface-variant mt-1">{{ item.video?.creator?.name }}</p>
                            </Link>
                        </div>
                        <button class="text-on-surface-variant p-1 shrink-0" aria-label="Remove from history" @click="removeHistory(item.id)">
                            <span class="material-symbols-outlined text-xl" aria-hidden="true">close</span>
                        </button>
                    </div>
                </div>
            </section>

            <!-- Earlier This Week (compact list rows) -->
            <section v-if="groupedHistory.earlier.length > 0" class="mb-8">
                <h2 class="font-headline-md text-headline-md mb-4 text-on-surface">Earlier This Week</h2>
                <div class="flex flex-col gap-4">
                    <div v-for="item in groupedHistory.earlier" :key="item.id" class="flex gap-3 items-center">
                        <Link :href="route('growstream.video.detail', item.video?.slug)" class="relative w-28 aspect-video rounded-md overflow-hidden shrink-0 bg-surface-container-highest">
                            <img class="w-full h-full object-cover" :src="item.video?.thumbnail_url || fallbackThumb" :alt="item.video?.title" />
                            <span class="absolute bottom-1 right-1 bg-black/80 text-white text-[9px] px-1 rounded">{{ formatDuration(item.video?.duration) }}</span>
                        </Link>
                        <div class="flex-1 min-w-0 flex items-start justify-between">
                            <div class="min-w-0">
                                <Link :href="route('growstream.video.detail', item.video?.slug)">
                                    <h4 class="font-label-md text-label-md text-on-surface truncate">{{ item.video?.title }}</h4>
                                    <p class="font-label-sm text-label-sm text-on-surface-variant mt-0.5">{{ item.video?.creator?.name }}</p>
                                </Link>
                            </div>
                            <button class="text-on-surface-variant p-1 shrink-0" aria-label="Remove from history" @click="removeHistory(item.id)">
                                <span class="material-symbols-outlined text-lg" aria-hidden="true">close</span>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Empty state -->
            <div v-if="watchHistory.length === 0" class="flex flex-col items-center gap-4 py-16 text-center">
                <div class="mx-auto mb-2 flex h-20 w-20 items-center justify-center rounded-full bg-primary/10">
                    <span class="material-symbols-outlined text-4xl text-primary" aria-hidden="true">history</span>
                </div>
                <h2 class="font-headline-md text-headline-md text-on-surface">No watch history</h2>
                <p class="font-label-sm text-label-sm text-on-surface-variant">Videos you watch will appear here</p>
                <Link :href="route('growstream.browse')" class="bg-primary text-on-primary px-6 py-2 rounded-full font-label-md text-label-md">Browse Content</Link>
            </div>

            <!-- End of history -->
            <div v-else-if="history && history.data && history.data.length > 0" class="flex flex-col items-center gap-4 py-4 text-center">
                <p class="font-label-sm text-label-sm text-on-surface-variant">You've reached the end of your recent history.</p>
                <button v-if="history.next_page_url" class="border border-primary text-primary px-6 py-2 rounded-full font-label-md text-label-md" @click="loadOlder">Load Older History</button>
            </div>
        </main>
    </GrowStreamLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import GrowStreamLayout from '@/Layouts/GrowStreamLayout.vue';
import { useGrowStream } from '@/composables/useGrowStream';
import type { WatchHistory } from '@/types/growstream';

interface Props {
    continueWatching?: unknown[];
    watchlist?: unknown[];
    history?: { data: WatchHistory[]; next_page_url?: string | null };
}

const props = withDefaults(defineProps<Props>(), {
    continueWatching: () => [],
    watchlist: () => [],
    history: () => ({ data: [] }),
});

const { formatDuration } = useGrowStream();

const fallbackThumb = 'https://placehold.co/440x248/e1bfb4/191c1d?text=GrowStream';

const watchHistory = computed<WatchHistory[]>(() => props.history?.data ?? []);

const groupedHistory = computed(() => {
    const today: WatchHistory[] = [];
    const yesterday: WatchHistory[] = [];
    const earlier: WatchHistory[] = [];
    const now = new Date();
    const startToday = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const startYesterday = new Date(startToday.getTime() - 86400000);
    const weekAgo = new Date(startToday.getTime() - 7 * 86400000);

    for (const item of watchHistory.value) {
        const date = new Date(item.last_watched_at || item.updated_at || item.created_at || now);
        if (date >= startToday) today.push(item);
        else if (date >= startYesterday) yesterday.push(item);
        else if (date >= weekAgo) earlier.push(item);
        else earlier.push(item);
    }
    return { today, yesterday, earlier };
});

const removeHistory = async (historyId: number) => {
    try {
        await fetch(`/api/v1/growstream/watch/history/${historyId}`, { method: 'DELETE', headers: { 'Accept': 'application/json' } });
        router.reload({ only: ['history'] });
    } catch (e) {
        console.error('Failed to remove history:', e);
    }
};

const loadOlder = () => {
    if (props.history?.next_page_url) {
        router.get(props.history.next_page_url, {}, { preserveState: true, only: ['history'] });
    }
};
</script>
