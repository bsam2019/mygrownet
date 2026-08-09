<script setup lang="ts">
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import GrowStreamHeader from '@/components/GrowStream/GrowStreamHeader.vue';
import GrowStreamFooter from '@/components/GrowStream/GrowStreamFooter.vue';
import { useMiniPlayer } from '@/composables/useMiniPlayer';
import VideoPlayer from '@/Components/GrowStream/VideoPlayer.vue';

interface Props {
    title?: string;
    showPromo?: boolean;
    categories?: any[];
    selectedCategory?: string;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'GrowStream',
    showPromo: false,
    categories: () => [],
    selectedCategory: '',
});

const emit = defineEmits(['selectCategory']);

const handleSelectCategory = (slug: string) => {
    emit('selectCategory', slug);
};

const page = usePage();
const user = computed(() => (page.props as any).auth?.user ?? null);
const isAuthenticated = computed(() => !!user.value);

const navItems = [
    { label: 'Home', href: () => route('growstream.home'), icon: 'home' },
    { label: 'Discovery', href: () => route('growstream.browse'), icon: 'explore' },
    { label: 'Studio', href: () => route('growstream.creator.dashboard'), icon: 'video_settings' },
    { label: 'Subscriptions', href: () => route('growstream.subscription'), icon: 'subscriptions' },
    { label: 'Profile', href: () => route('growstream.my-videos'), icon: 'person' },
];

const isActiveRoute = (href: string): boolean => {
    if (typeof window === 'undefined') return false;
    return window.location.pathname === new URL(href, window.location.origin).pathname;
};

const { activeVideo, isMinimized, expandVideo, closeMiniPlayer } = useMiniPlayer();

// Page preloader progress state
const isPageLoading = ref(false);
let startUnsubscribe: (() => void) | null = null;
let finishUnsubscribe: (() => void) | null = null;

onMounted(() => {
    startUnsubscribe = router.on('start', () => { isPageLoading.value = true; });
    finishUnsubscribe = router.on('finish', () => { isPageLoading.value = false; });
});

onUnmounted(() => {
    if (startUnsubscribe) startUnsubscribe();
    if (finishUnsubscribe) finishUnsubscribe();
});
</script>

<template>
    <Head :title="props.title" />

    <!-- Top Animated Page Preloader Indicator -->
    <div v-if="isPageLoading" class="fixed top-0 left-0 right-0 z-50 h-1 bg-gradient-to-r from-[#e2571f] via-amber-400 to-[#e2571f] animate-pulse shadow-lg shadow-[#e2571f]/50"></div>

    <div class="gs-app bg-background text-on-background min-h-screen flex flex-col font-body-md antialiased">
        <GrowStreamHeader
            :show-promo="props.showPromo"
            :categories="props.categories"
            :selected-category="props.selectedCategory"
            @select-category="handleSelectCategory"
        />

        <!-- Main Content -->
        <main class="flex-1 w-full max-w-6xl mx-auto px-4 md:px-6 pt-6 pb-24 md:pb-12">
            <slot />
        </main>

        <GrowStreamFooter />

        <!-- Floating PiP Mini-Player -->
        <div
            v-if="isMinimized && activeVideo"
            class="fixed bottom-20 right-4 md:bottom-6 md:right-6 z-50 w-72 md:w-80 shadow-2xl rounded-xl overflow-hidden bg-black border border-primary/40 transition-all duration-300"
        >
            <div class="flex items-center justify-between px-3 py-2 bg-surface-container-highest/90 text-on-surface border-b border-outline-variant/40">
                <span class="font-label-sm text-xs truncate max-w-[180px] font-semibold">{{ activeVideo.title }}</span>
                <div class="flex items-center gap-1">
                    <button @click="expandVideo" title="Expand Player" class="p-1 text-on-surface-variant hover:text-primary rounded">
                        <span class="material-symbols-outlined text-base">open_in_full</span>
                    </button>
                    <button @click="closeMiniPlayer" title="Close Player" class="p-1 text-on-surface-variant hover:text-error rounded">
                        <span class="material-symbols-outlined text-base">close</span>
                    </button>
                </div>
            </div>
            <div class="aspect-video w-full">
                <VideoPlayer
                    :video="activeVideo"
                    :autoplay="true"
                />
            </div>
        </div>

        <!-- Bottom Nav (mobile only, authenticated) -->
        <nav v-if="isAuthenticated" class="md:hidden fixed bottom-0 w-full bg-surface-container-lowest border-t border-surface-container-highest flex justify-around py-2 z-50">
            <Link
                v-for="item in navItems"
                :key="item.label"
                :href="item.href()"
                class="flex flex-col items-center px-3 py-1"
                :class="isActiveRoute(item.href()) ? 'text-primary' : 'text-on-surface-variant'"
            >
                <span class="material-symbols-outlined text-xl" :data-weight="isActiveRoute(item.href()) ? 'fill' : 'regular'">{{ item.icon }}</span>
                <span class="font-label-sm text-[9px] uppercase tracking-wide mt-0.5">{{ item.label }}</span>
            </Link>
        </nav>
    </div>
</template>
