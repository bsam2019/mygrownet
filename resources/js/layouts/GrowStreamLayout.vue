<script setup lang="ts">
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useDataSaver } from '@/composables/useDataSaver';

interface Props {
    title?: string;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'GrowStream',
});

const page = usePage();

const { dataSaver, toggle: toggleDataSaver } = useDataSaver();

const user = computed(() => (page.props as any).auth?.user ?? null);

const isAuthenticated = computed(() => !!user.value);

const unreadCount = ref(0);
let unreadTimer: ReturnType<typeof setInterval> | undefined;

const fetchUnreadCount = async () => {
    if (!isAuthenticated.value) return;
    try {
        const res = await fetch(route('growstream.notifications.unread-count'), {
            headers: { Accept: 'application/json' },
        });
        if (res.ok) {
            const data = await res.json();
            unreadCount.value = data.count ?? 0;
        }
    } catch {
        // ignore polling errors
    }
};

onMounted(() => {
    fetchUnreadCount();
    unreadTimer = setInterval(fetchUnreadCount, 60000);
});

onUnmounted(() => {
    if (unreadTimer) clearInterval(unreadTimer);
});

const isAdmin = computed(() => {
    const roles = (user.value?.roles as string[] | undefined) ?? [];
    return roles.some((r) => ['admin', 'administrator', 'superadmin'].includes(r.toLowerCase()));
});

const navItems = [
    { label: 'Home', href: () => route('growstream.home'), icon: 'M3 12l9-9 9 9M5 10v10h14V10' },
    { label: 'Discovery', href: () => route('growstream.browse'), icon: 'M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z' },
    { label: 'Studio', href: () => route('growstream.creator.dashboard'), icon: 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z' },
    { label: 'Alerts', href: () => route('growstream.notifications'), icon: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9' },
    { label: 'Profile', href: () => route('growstream.my-videos'), icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
];

// Current path (pathname + search) so identity login returns the user to the
// page they were on. Only used for guest actions.
const redirectQuery = computed(() => {
    if (typeof window === 'undefined') return '';
    const { pathname, search } = window.location;
    return `${pathname}${search}`;
});

const loginHref = computed(() => {
    const base = route('growstream.login');
    return redirectQuery.value ? `${base}?redirect=${encodeURIComponent(redirectQuery.value)}` : base;
});

const registerHref = computed(() => {
    const base = route('growstream.register');
    return redirectQuery.value ? `${base}?redirect=${encodeURIComponent(redirectQuery.value)}` : base;
});

const logout = () => {
    router.post(route('growstream.logout'));
};

// Determine if a route href matches the current location
const isActiveRoute = (href: string): boolean => {
    if (typeof window === 'undefined') return false;
    return window.location.pathname === new URL(href, window.location.origin).pathname;
};

// Avatar dropdown (works on desktop + mobile)
const avatarMenuOpen = ref(false);
const avatarMenuRef = ref<HTMLElement>();

const toggleAvatarMenu = () => {
    avatarMenuOpen.value = !avatarMenuOpen.value;
};

const closeAvatarMenu = () => {
    avatarMenuOpen.value = false;
};

const handleAvatarClickOutside = (event: MouseEvent) => {
    if (avatarMenuRef.value && !avatarMenuRef.value.contains(event.target as Node)) {
        closeAvatarMenu();
    }
};

onMounted(() => {
    document.addEventListener('click', handleAvatarClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleAvatarClickOutside);
});
</script>

<template>
    <Head :title="props.title" />

    <div class="gs-app">
        <!-- Desktop Header -->
        <header class="sticky top-0 z-40 border-b border-[var(--gs-border)] bg-[var(--gs-bg)]/90 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <!-- Brand -->
                <Link :href="route('growstream.home')" class="flex items-center gap-2">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[var(--gs-primary)]">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-lg font-bold tracking-tight">Grow<span class="text-[var(--gs-accent)]">Stream</span></span>
                </Link>

                <!-- Desktop Nav -->
                <nav aria-label="Primary" class="hidden items-center gap-1 md:flex">
                    <Link
                        v-for="item in navItems"
                        :key="item.label"
                        :href="item.href()"
                        class="rounded-full px-4 py-2 text-sm font-medium transition-colors"
                        :class="isActiveRoute(item.href()) ? 'bg-[var(--gs-primary-soft)] text-[var(--gs-primary)]' : 'text-[var(--gs-muted)] hover:text-[var(--gs-text)]'"
                    >
                        {{ item.label }}
                    </Link>
                    <Link
                        v-if="isAdmin"
                        :href="route('growstream.admin.videos')"
                        class="rounded-full px-4 py-2 text-sm font-medium text-[var(--gs-muted)] transition-colors hover:text-[var(--gs-text)]"
                        :class="isActiveRoute(route('growstream.admin.videos')) ? 'bg-[var(--gs-primary-soft)] text-[var(--gs-primary)]' : ''"
                    >
                        Admin
                    </Link>
                </nav>

                <!-- Desktop Actions -->
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <!-- Data Saver Toggle -->
                    <button
                        class="hidden items-center gap-2 rounded-full border border-[var(--gs-border)] px-3 py-2 text-sm font-medium transition-colors xl:flex"
                        :class="dataSaver ? 'border-[var(--gs-primary)] text-[var(--gs-primary)]' : 'text-[var(--gs-muted)] hover:text-[var(--gs-text)]'"
                        :title="dataSaver ? 'Data Saver on - autoplay off, lower quality' : 'Data Saver off'"
                        :aria-pressed="dataSaver ? 'true' : 'false'"
                        aria-label="Toggle Data Saver"
                        @click="toggleDataSaver"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"
                            />
                        </svg>
                        <span>{{ dataSaver ? 'Data Saver On' : 'Data Saver' }}</span>
                    </button>

                    <!-- Notifications Bell -->
                    <Link
                        v-if="isAuthenticated"
                        :href="route('growstream.notifications')"
                        class="relative flex h-9 w-9 items-center justify-center rounded-full border border-[var(--gs-border)] text-[var(--gs-muted)] transition-colors hover:text-[var(--gs-text)]"
                        aria-label="Notifications"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                            />
                        </svg>
                        <span
                            v-if="unreadCount > 0"
                            class="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-[var(--gs-primary)] px-1 text-[10px] font-bold text-white"
                        >
                            {{ unreadCount > 99 ? '99+' : unreadCount }}
                        </span>
                    </Link>

                    <template v-if="isAuthenticated">
                        <!-- Avatar dropdown -->
                        <div ref="avatarMenuRef" class="relative">
                            <button
                                class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-full bg-[var(--gs-primary-soft)] text-sm font-semibold text-[var(--gs-primary)] transition hover:bg-[var(--gs-primary)]/25"
                                :aria-expanded="avatarMenuOpen"
                                aria-haspopup="true"
                                aria-label="Account menu"
                                @click.stop="toggleAvatarMenu"
                            >
                                {{ (user?.name || 'U').charAt(0).toUpperCase() }}
                            </button>

                            <transition
                                enter-active-class="transition duration-150 ease-out"
                                enter-from-class="opacity-0 translate-y-1"
                                leave-active-class="transition duration-100 ease-in"
                                leave-to-class="opacity-0"
                            >
                                <div
                                    v-if="avatarMenuOpen"
                                    class="absolute right-0 top-full mt-2 w-56 overflow-hidden rounded-xl border border-[var(--gs-border)] bg-[var(--gs-card)] shadow-xl"
                                    @click.stop
                                >
                                    <div class="border-b border-[var(--gs-border)] px-4 py-3">
                                        <p class="truncate text-sm font-semibold text-[var(--gs-text)]">{{ user?.name }}</p>
                                        <p class="truncate text-xs text-[var(--gs-muted)]">{{ user?.email }}</p>
                                    </div>
                                    <div class="flex flex-col p-1.5">
                                        <Link :href="route('growstream.my-videos')" class="rounded-lg px-3 py-2 text-sm text-[var(--gs-muted)] hover:bg-[var(--gs-bg-elevated)] hover:text-[var(--gs-text)]" @click="closeAvatarMenu">
                                            My Videos
                                        </Link>
                                        <Link :href="route('growstream.subscription')" class="rounded-lg px-3 py-2 text-sm text-[var(--gs-accent)] hover:bg-[var(--gs-bg-elevated)]" @click="closeAvatarMenu">
                                            Subscribe
                                        </Link>
                                        <Link :href="route('growstream.creator.dashboard')" class="rounded-lg px-3 py-2 text-sm text-[var(--gs-muted)] hover:bg-[var(--gs-bg-elevated)] hover:text-[var(--gs-text)]" @click="closeAvatarMenu">
                                            Creator Studio
                                        </Link>
                                        <Link v-if="isAdmin" :href="route('growstream.admin.videos')" class="rounded-lg px-3 py-2 text-sm text-[var(--gs-muted)] hover:bg-[var(--gs-bg-elevated)] hover:text-[var(--gs-text)]" @click="closeAvatarMenu">
                                            Admin
                                        </Link>
                                        <button class="rounded-lg px-3 py-2 text-left text-sm text-red-400 hover:bg-red-500/10" @click="closeAvatarMenu; logout()">
                                            Sign Out
                                        </button>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </template>
                    <template v-else>
                        <a :href="route('growstream.login')" class="gs-btn gs-btn-ghost">Sign In</a>
                        <a :href="route('growstream.register')" class="gs-btn gs-btn-accent">Get Started</a>
                    </template>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="mx-auto min-h-[calc(100vh-4rem)] max-w-7xl px-4 pb-24 pt-6 sm:px-6 lg:px-8 md:pb-10">
            <slot />
        </main>

        <!-- Mobile Bottom Nav (5 items, Stitch style) -->
        <nav aria-label="Bottom" class="fixed bottom-0 left-0 right-0 z-40 border-t border-[var(--gs-border)] bg-white/95 backdrop-blur md:hidden">
            <div class="flex justify-around px-2 py-3">
                <Link
                    v-for="item in navItems"
                    :key="item.label"
                    :href="item.href()"
                    :aria-label="item.label"
                    class="flex flex-col items-center px-3 transition-all active:scale-90"
                    :class="isActiveRoute(item.href()) ? 'text-[var(--gs-primary)]' : 'text-[var(--gs-muted)]'"
                >
                    <div v-if="isActiveRoute(item.href())" class="mb-1 rounded-full bg-[var(--gs-primary-soft)] px-5 py-1.5">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                        </svg>
                    </div>
                    <svg v-else class="mb-1 h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                    </svg>
                    <span class="text-[9px] font-bold uppercase tracking-widest">{{ item.label }}</span>
                </Link>
            </div>
        </nav>
    </div>
</template>
