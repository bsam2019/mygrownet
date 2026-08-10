<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
    title?: string;
    subtitle?: string;
    backUrl?: string;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'GrowNet',
    subtitle: '',
    backUrl: '',
});

const page = usePage();
const user = computed(() => (page.props as any).auth?.user ?? null);

const currentHost = typeof window !== 'undefined' ? window.location.hostname : '';
const isSubdomain = currentHost === 'grownet.mygrownet.com' || currentHost.startsWith('grownet.');

const homeUrl = computed(() => {
    if (props.backUrl) return props.backUrl;
    try {
        return isSubdomain ? route('grownet.sub.welcome') : route('grownet.welcome');
    } catch {
        return '/grownet';
    }
});

const dashboardUrl = computed(() => {
    try {
        return isSubdomain ? route('grownet.sub.dashboard') : route('grownet.dashboard');
    } catch {
        return '/workspace';
    }
});

const libraryUrl = computed(() => {
    try {
        return isSubdomain ? route('grownet.sub.library.index') : route('grownet.library.index');
    } catch {
        return '/grownet/library';
    }
});

const rewardsUrl = computed(() => {
    try {
        return isSubdomain ? route('grownet.sub.rewards.index') : route('grownet.rewards.index');
    } catch {
        return '/grownet/rewards';
    }
});

const logoutUrl = computed(() => {
    try {
        return route('grownet.logout');
    } catch {
        return '/logout';
    }
});

const mainDomain = currentHost.endsWith('.mygrownet.com') || currentHost === 'mygrownet.com'
    ? 'https://mygrownet.com'
    : (typeof window !== 'undefined' ? window.location.origin : '');
</script>

<template>
    <Head :title="props.title" />

    <div class="min-h-screen bg-gray-50 font-sans antialiased text-gray-900">
        <!-- GrowNet Header Shell -->
        <header class="sticky top-0 z-40 border-b border-purple-100 bg-white/95 backdrop-blur shadow-sm">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
                
                <!-- Left: Brand + Title -->
                <div class="flex items-center gap-4">
                    <Link :href="homeUrl" class="flex items-center gap-2 group">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-tr from-purple-600 to-indigo-600 text-sm font-black text-white shadow-md group-hover:scale-105 transition-transform">
                            G
                        </div>
                        <div>
                            <span class="text-lg font-extrabold tracking-tight text-gray-900">
                                Grow<span class="text-purple-600">Net</span>
                            </span>
                        </div>
                    </Link>

                    <div v-if="props.title && props.title !== 'GrowNet'" class="hidden md:flex items-center gap-2 border-l border-gray-200 pl-4">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ props.title }}</span>
                    </div>
                </div>

                <!-- Right: Navigation Links + User Profile -->
                <div class="flex items-center gap-4">
                    <nav class="hidden sm:flex items-center gap-2">
                        <Link
                            :href="dashboardUrl"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:text-purple-700 hover:bg-purple-50 transition-colors"
                        >
                            Dashboard
                        </Link>
                        <Link
                            :href="libraryUrl"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:text-purple-700 hover:bg-purple-50 transition-colors"
                        >
                            Library
                        </Link>
                        <Link
                            :href="rewardsUrl"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:text-purple-700 hover:bg-purple-50 transition-colors"
                        >
                            Rewards
                        </Link>
                        <a
                            :href="`${mainDomain}/workspace`"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold text-purple-700 bg-purple-50 hover:bg-purple-100 transition-colors flex items-center gap-1"
                        >
                            Workspace
                        </a>
                    </nav>

                    <div v-if="user" class="flex items-center gap-3 border-l border-gray-200 pl-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-purple-600 text-xs font-bold text-white shadow-sm">
                            {{ (user.name || 'U').charAt(0).toUpperCase() }}
                        </div>
                        <Link
                            :href="logoutUrl"
                            method="post"
                            as="button"
                            class="rounded-lg px-2.5 py-1 text-xs font-semibold text-gray-500 hover:bg-gray-100 hover:text-red-600 transition-colors"
                        >
                            Sign out
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Slot Container -->
        <main class="mx-auto max-w-6xl px-4 py-6 sm:px-6">
            <slot />
        </main>
    </div>
</template>
