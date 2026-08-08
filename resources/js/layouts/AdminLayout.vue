<script setup lang="ts">
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
    title?: string;
}

withDefaults(defineProps<Props>(), {
    title: 'GrowStream Admin',
});

const page = usePage();
const user = computed(() => (page.props as any).auth?.user ?? null);

// Main-domain base for cross-subdomain admin links (subscription management
// lives on mygrownet.com/admin/module-subscriptions/*). Derive from the current
// host so it works in local/dev too.
const currentHost = window.location.hostname;
const mainDomain = currentHost.endsWith('.mygrownet.com') || currentHost === 'mygrownet.com'
    ? 'https://mygrownet.com'
    : window.location.origin;

const adminNav = [
    { label: 'Videos', href: () => route('growstream.admin.videos'), icon: 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z' },
    { label: 'Moderation', href: () => route('growstream.admin.moderation'), icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' },
    { label: 'Creators', href: () => route('growstream.admin.creators'), icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' },
    { label: 'Categories', href: () => route('growstream.admin.categories'), icon: 'M4 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21L9.09 10.5a7.002 7.002 0 005.744 5.06l.39-2.05a1 1 0 011.22-.78l4.304 1.05a1 1 0 01.763.973V18a2 2 0 01-2 2h-1C9.716 20 4 14.284 4 7.5V5z' },
    { label: 'Analytics', href: () => route('growstream.admin.analytics'), icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
    { label: 'Sponsorship', href: () => route('growstream.admin.sponsorship'), icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    { label: 'Plans & Tiers', href: () => `${mainDomain}/admin/module-subscriptions/growstream`, icon: 'M11 3.055A9 9 0 1020.945 13H11V3.055zM20.488 9H15V3.512A9.025 9.025 0 0120.488 9z' },
    { label: 'Discounts', href: () => `${mainDomain}/admin/module-subscriptions/discounts`, icon: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7a1.994 1.994 0 01-.586-1.414V7a4 4 0 014-4z' },
];

const logout = () => {
    router.post(route('growstream.logout'));
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
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[var(--gs-accent)]">
                        <svg class="h-5 w-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-lg font-bold tracking-tight">
                            Grow<span class="text-[var(--gs-accent)]">Stream</span>
                        </span>
                        <span class="ml-2 hidden rounded-full bg-[var(--gs-primary-soft)] px-2 py-0.5 text-xs font-semibold text-[var(--gs-primary)] sm:inline">
                            Admin
                        </span>
                    </div>
                </div>

                <!-- Right Actions -->
                <div class="flex items-center gap-2">
                    <Link
                        :href="route('growstream.home')"
                        class="gs-btn gs-btn-ghost hidden sm:inline-flex"
                    >
                        View Site
                    </Link>
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[var(--gs-primary-soft)] text-sm font-semibold text-[var(--gs-primary)]">
                            {{ (user?.name || 'A').charAt(0).toUpperCase() }}
                        </div>
                        <button class="gs-btn gs-btn-outline" aria-label="Sign out of your account" @click="logout">
                            Sign Out
                        </button>
                    </div>
                </div>
            </div>

            <!-- Admin Tabs (desktop) -->
            <nav aria-label="Admin" class="mx-auto hidden max-w-7xl px-4 sm:block sm:px-6 lg:px-8">
                <div class="flex gap-1 overflow-x-auto pb-px">
                    <Link
                        v-for="item in adminNav"
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

        <!-- Mobile Admin Nav -->
        <nav aria-label="Bottom" class="fixed bottom-0 left-0 right-0 z-40 border-t border-[var(--gs-border)] bg-[var(--gs-bg)]/95 backdrop-blur sm:hidden">
            <div class="grid grid-cols-7">
                <Link
                    v-for="item in adminNav"
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
