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
    { label: 'Home', href: () => route('growstream.home'), icon: 'home' },
    { label: 'Discovery', href: () => route('growstream.browse'), icon: 'explore' },
    { label: 'Studio', href: () => route('growstream.creator.dashboard'), icon: 'video_settings' },
    { label: 'Subscriptions', href: () => route('growstream.subscription'), icon: 'subscriptions' },
    { label: 'Profile', href: () => route('growstream.my-videos'), icon: 'person' },
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

    <div class="gs-app bg-background text-on-background min-h-screen pb-24 font-body-md antialiased">
        <!-- Top App Bar (auth actions in the right cluster) -->
        <header class="bg-surface-container-lowest border-b border-surface-container-highest sticky top-0 z-40 flex items-center justify-between px-margin-mobile h-16 w-full">
            <button class="text-on-surface-variant p-2 rounded-full flex items-center justify-center" aria-label="Menu">
                <span class="material-symbols-outlined" aria-hidden="true">menu</span>
            </button>
            <div class="font-headline-lg-mobile text-headline-lg-mobile font-bold text-primary">GrowStream</div>
            <div class="flex items-center gap-1">
                <!-- Data Saver -->
                <button
                    class="text-on-surface-variant p-2 rounded-full flex items-center justify-center"
                    :title="dataSaver ? 'Data Saver on' : 'Data Saver off'"
                    :aria-pressed="dataSaver ? 'true' : 'false'"
                    aria-label="Toggle Data Saver"
                    @click="toggleDataSaver"
                >
                    <span class="material-symbols-outlined text-lg" :class="dataSaver ? 'text-primary' : ''" aria-hidden="true">bolt</span>
                </button>
                <!-- Notifications -->
                <Link
                    v-if="isAuthenticated"
                    :href="route('growstream.notifications')"
                    class="relative text-on-surface-variant p-2 rounded-full flex items-center justify-center"
                    aria-label="Notifications"
                >
                    <span class="material-symbols-outlined text-lg" aria-hidden="true">notifications</span>
                    <span
                        v-if="unreadCount > 0"
                        class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-primary px-1 text-[10px] font-bold text-on-primary"
                    >
                        {{ unreadCount > 99 ? '99+' : unreadCount }}
                    </span>
                </Link>
                <!-- Search -->
                <Link :href="route('growstream.search')" class="text-on-surface-variant p-2 rounded-full flex items-center justify-center" aria-label="Search">
                    <span class="material-symbols-outlined text-lg" aria-hidden="true">search</span>
                </Link>
                <!-- Auth / Avatar -->
                <template v-if="isAuthenticated">
                    <div ref="avatarMenuRef" class="relative">
                        <button
                            class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-full bg-primary text-sm font-bold text-on-primary"
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
                                class="absolute right-0 top-full mt-2 w-56 overflow-hidden rounded-lg border border-outline-variant bg-surface-container-lowest shadow-lg"
                                @click.stop
                            >
                                <div class="border-b border-outline-variant px-4 py-3">
                                    <p class="truncate text-sm font-semibold text-on-surface">{{ user?.name }}</p>
                                    <p class="truncate text-xs text-on-surface-variant">{{ user?.email }}</p>
                                </div>
                                <div class="flex flex-col p-1.5">
                                    <Link :href="route('growstream.my-videos')" class="rounded-lg px-3 py-2 text-sm text-on-surface hover:bg-surface-container-low" @click="closeAvatarMenu">My Videos</Link>
                                    <Link :href="route('growstream.subscription')" class="rounded-lg px-3 py-2 text-sm text-primary hover:bg-surface-container-low" @click="closeAvatarMenu">Subscribe</Link>
                                    <Link :href="route('growstream.creator.dashboard')" class="rounded-lg px-3 py-2 text-sm text-on-surface hover:bg-surface-container-low" @click="closeAvatarMenu">Creator Studio</Link>
                                    <Link v-if="isAdmin" :href="route('growstream.admin.videos')" class="rounded-lg px-3 py-2 text-sm text-on-surface hover:bg-surface-container-low" @click="closeAvatarMenu">Admin</Link>
                                    <button class="rounded-lg px-3 py-2 text-left text-sm text-error hover:bg-error-container" @click="closeAvatarMenu; logout()">Sign Out</button>
                                </div>
                            </div>
                        </transition>
                    </div>
                </template>
                <template v-else>
                    <a :href="route('growstream.login')" class="font-label-md text-label-md text-on-surface-variant px-3 py-2">Sign In</a>
                    <a :href="route('growstream.register')" class="bg-primary text-on-primary px-4 py-2 rounded-full font-label-md text-label-md">Get Started</a>
                </template>
            </div>
        </header>

        <!-- Main Content -->
        <main class="px-margin-mobile pt-4">
            <slot />
        </main>

        <!-- Bottom Nav (Stitch) -->
        <nav class="fixed bottom-0 w-full bg-surface-container-lowest border-t border-surface-container-highest flex justify-around py-2 z-50">
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
