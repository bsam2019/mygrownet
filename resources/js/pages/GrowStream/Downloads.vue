<template>
    <GrowStreamLayout title="Downloads - GrowStream">
        <main class="flex-1 w-full max-w-4xl mx-auto flex flex-col">
            <div class="px-margin-mobile md:px-margin-desktop py-6 space-y-6">
                <!-- Page header -->
                <div>
                    <h1 class="font-display-lg text-headline-lg-mobile md:text-headline-lg font-bold text-on-surface">Downloads</h1>
                    <p class="font-body-md text-body-md text-on-surface-variant mt-1">Watch offline anytime, anywhere.</p>
                </div>

                <!-- Storage card -->
                <div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-6">
                    <div class="flex items-center justify-between">
                        <span class="font-label-md text-label-md text-on-surface">Storage Used</span>
                        <span class="font-label-md text-label-md text-on-surface-variant">{{ downloadable.length }} of 10 downloads</span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-surface-container-high mt-4 overflow-hidden">
                        <div class="h-full rounded-full bg-primary transition-all" :style="{ width: pctWidth + '%' }"></div>
                    </div>
                    <p class="font-label-sm text-label-sm text-on-surface-variant mt-3">Free up space by deleting watched videos</p>
                </div>

                <!-- Download list -->
                <div class="flex flex-col gap-3">
                    <div
                        v-for="v in downloadable"
                        :key="v.id"
                        class="flex items-center gap-4 p-3 rounded-lg border border-outline-variant bg-surface-container-lowest hover:bg-surface-container-low transition-colors cursor-pointer"
                        @click="router.visit(route('growstream.video.detail', { slug: v.slug }))"
                    >
                        <div class="relative w-24 h-16 shrink-0 rounded-md overflow-hidden bg-surface-container-high">
                            <div class="bg-cover bg-center w-full h-full absolute inset-0" :style="{ backgroundImage: `url('${v.thumbnail_url || fallbackThumb}')` }"></div>
                            <span class="absolute bottom-1 right-1 bg-black/80 text-white text-[10px] leading-none px-1 py-0.5 rounded">{{ formatDuration(v.duration) }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-label-md text-label-md text-on-surface truncate">{{ v.title }}</h4>
                            <p class="font-label-sm text-label-sm text-on-surface-variant mt-1">{{ v.creator?.name }} &bull; {{ formatViews(v.view_count) }} views</p>
                        </div>
                        <button class="text-on-surface-variant hover:text-error transition-colors shrink-0" aria-label="Download" @click.stop="downloadVideo(v)">
                            <span class="material-symbols-outlined" aria-hidden="true">download</span>
                        </button>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-if="downloadable.length === 0" class="flex flex-col items-center gap-4 py-16 text-center">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-primary/10">
                        <span class="material-symbols-outlined text-4xl text-primary" aria-hidden="true">download</span>
                    </div>
                    <h3 class="font-headline-md text-headline-md text-on-surface">No downloads yet</h3>
                    <p class="font-label-sm text-label-sm text-on-surface-variant max-w-sm">Videos marked for offline viewing will appear here.</p>
                    <Link :href="route('growstream.browse')" class="bg-primary text-on-primary px-6 py-2 rounded-full font-label-md text-label-md">Browse Content</Link>
                </div>
            </div>
        </main>
    </GrowStreamLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import GrowStreamLayout from '@/Layouts/GrowStreamLayout.vue';
import { useGrowStream } from '@/composables/useGrowStream';
import type { Video } from '@/types/growstream';

interface Props {
    downloadable?: Video[];
}

const props = withDefaults(defineProps<Props>(), {
    downloadable: () => [],
});

const { formatDuration } = useGrowStream();

const fallbackThumb = 'https://placehold.co/240x160/e1bfb4/191c1d?text=GrowStream';

const pctWidth = computed(() => Math.min((props.downloadable.length / 10) * 100, 100));

const downloadVideo = (v: Video) => {
    if (v.playback_url) {
        window.open(v.playback_url, '_blank');
    } else {
        router.visit(route('growstream.video.detail', { slug: v.slug }));
    }
};

const formatViews = (count?: number): string => {
    if (!count) return '0';
    if (count >= 1000000) return (count / 1000000).toFixed(1) + 'M';
    if (count >= 1000) return (count / 1000).toFixed(0) + 'K';
    return count.toString();
};
</script>
