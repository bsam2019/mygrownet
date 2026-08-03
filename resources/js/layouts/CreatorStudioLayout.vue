<script setup lang="ts">
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
    title?: string;
}

withDefaults(defineProps<Props>(), {
    title: 'Creator Studio - GrowStream',
});

const page = usePage();
const user = computed(() => (page.props as any).auth?.user ?? null);

const studioNav = [
    { label: 'Dashboard', href: () => route('growstream.creator.dashboard'), icon: 'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10' },
    { label: 'Videos', href: () => route('growstream.creator.videos.index'), icon: 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z' },
    { label: 'Upload', href: () => route('growstream.creator.videos.create'), icon: 'M12 4v12m0-12l-4 4m4-4l4 4M4 20h16' },
    { label: 'Analytics', href: () => route('growstream.creator.analytics'), icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
    { label: 'Sponsorship', href: () => route('growstream.creator.sponsorship.index'), icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
];

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <Head :title="title" />

    <div class="gs-app">
        <!-- Header -->
        <header class="sticky top-0 z-40 border-b border-[var(--gs-border)] bg-[var(--gs-bg)]/90 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <!-- Brand -->
                <div class="flex items-center gap-2">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[var(--gs-primary)]">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-lg font-bold tracking-tight">
                            Grow<span class="text-[var(--gs-accent)]">Stream</span>
                        </span>
                        <span class="ml-2 hidden rounded-full bg-[var(--gs-accent-soft)] px-2 py-0.5 text-xs font-semibold text-[var(--gs-accent)] sm:inline">
                            Studio
                        </span>
                    </div>
                </div>

                <!-- Right Actions -->
                <div class="flex items-center gap-2">
                    <Link
                        :href="route('growstream.home')"
                        class="gs-btn gs-btn-ghost hidden sm:inline-flex"
                    >
                        Back to Browse
                    </Link>
                    <button class="gs-btn gs-btn-outline" aria-label="Sign out of your account" @click="logout">
                        Sign Out
                    </button>
                </div>
            </div>

            <!-- Studio Tabs (desktop) -->
            <nav aria-label="Creator Studio" class="mx-auto hidden max-w-7xl px-4 sm:block sm:px-6 lg:px-8">
                <div class="flex gap-1 overflow-x-auto pb-px">
                    <Link
                        v-for="item in studioNav"
                        :key="item.label"
                        :href="item.href()"
                        class="flex items-center gap-2 border-b-2 border-transparent px-4 py-2.5 text-sm font-medium text-[var(--gs-muted)] transition-colors hover:text-[var(--gs-text)]"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                        </svg>
                        {{ item.label }}
                    </Link>
                </div>
            </nav>
        </header>

        <!-- Main -->
        <main class="mx-auto min-h-[calc(100vh-8rem)] max-w-7xl px-4 pb-24 pt-6 sm:px-6 sm:pb-10 lg:px-8">
            <slot />
        </main>

        <!-- Mobile Studio Nav -->
        <nav aria-label="Bottom" class="fixed bottom-0 left-0 right-0 z-40 border-t border-[var(--gs-border)] bg-[var(--gs-bg)]/95 backdrop-blur sm:hidden">
            <div class="grid grid-cols-5">
                <Link
                    v-for="item in studioNav"
                    :key="item.label"
                    :href="item.href()"
                    :aria-label="item.label"
                    class="flex min-h-[56px] flex-col items-center justify-center gap-1 py-2 text-[11px] font-medium text-[var(--gs-muted)] active:text-[var(--gs-primary)]"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                    </svg>
                    {{ item.label }}
                </Link>
            </div>
        </nav>
    </div>
</template>
