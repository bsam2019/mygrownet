<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';
import { useDataSaver } from '@/composables/useDataSaver';

interface Props {
    showPromo?: boolean;
    categories?: any[];
    selectedCategory?: string;
}

const props = withDefaults(defineProps<Props>(), {
    showPromo: false,
    categories: () => [],
    selectedCategory: '',
});

const emit = defineEmits(['selectCategory']);

const onCategoryClick = (categorySlug: string) => {
    emit('selectCategory', categorySlug);
};

const page = usePage();
const { dataSaver, toggle: toggleDataSaver } = useDataSaver();

const user = computed(() => (page.props as any).auth?.user ?? null);
const isAuthenticated = computed(() => !!user.value);

const isAdmin = computed(() => {
    const roles = (user.value?.roles as string[] | undefined) ?? [];
    return roles.some((r) => ['admin', 'administrator', 'superadmin'].includes(r.toLowerCase()));
});

const isCreator = computed(() => {
    if (!user.value) return false;
    const roles = (user.value?.roles as string[] | undefined) ?? [];
    return roles.some((r) => ['creator', 'admin', 'administrator', 'superadmin'].includes(r.toLowerCase())) || !!(user.value as any)?.is_creator;
});

const isHubContext = computed(() => {
    if (typeof window === 'undefined') return false;
    const path = window.location.pathname;
    const host = window.location.hostname;
    return path.startsWith('/hub') || host.includes('hub') || host.endsWith('.growstream.app') || !!(page.props as any).isHubContext;
});

// Mobile menu
const mobileMenuOpen = ref(false);
const toggleMobileMenu = () => { mobileMenuOpen.value = !mobileMenuOpen.value; };
const closeMobileMenu = () => { mobileMenuOpen.value = false; };

// Unread notifications
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

// Avatar dropdown
const avatarMenuOpen = ref(false);
const avatarMenuRef = ref<HTMLElement>();
const toggleAvatarMenu = () => { avatarMenuOpen.value = !avatarMenuOpen.value; };
const closeAvatarMenu = () => { avatarMenuOpen.value = false; };

const handleClickOutside = (event: MouseEvent) => {
    if (avatarMenuRef.value && !avatarMenuRef.value.contains(event.target as Node)) {
        closeAvatarMenu();
    }
};

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));

// Inline search
const searchOpen = ref(false);
const searchQuery = ref('');
const searchInputRef = ref<HTMLInputElement>();
const openSearch = () => {
    searchOpen.value = true;
    nextTick(() => searchInputRef.value?.focus());
};
const closeSearch = () => {
    searchOpen.value = false;
    searchQuery.value = '';
};
const submitSearch = () => {
    const q = searchQuery.value.trim();
    closeSearch();
    if (q) {
        router.visit(route('growstream.search', { q }));
    } else {
        router.visit(route('growstream.search'));
    }
};

// Current path so identity login returns to the same page
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
    closeAvatarMenu();
    closeMobileMenu();
    router.post(route('growstream.logout'));
};

const navLinks = computed(() => [
    { label: 'Home', href: () => route('growstream.home') },
    { label: 'Browse', href: () => route('growstream.browse') },
    { label: 'Plans', href: () => route('growstream.subscription') },
    { label: 'Platform Hub', href: () => route('growstream.hub.landing') },
]);
</script>

<template>
    <div>
        <!-- Promo banner - REMOVED -->

        <!-- Header -->
        <header class="border-b border-outline-variant/60 sticky top-0 z-40 bg-[#0e0b09] shadow-lg">
            <div class="max-w-6xl mx-auto flex items-center justify-between px-margin-mobile md:px-margin-desktop h-16 gap-4">
                <!-- Mobile menu trigger (LEFT side on mobile) -->
                <button
                    class="md:hidden text-on-surface-variant p-2 rounded-full flex items-center justify-center"
                    aria-label="Menu"
                    :aria-expanded="mobileMenuOpen"
                    @click="toggleMobileMenu"
                >
                    <span class="material-symbols-outlined text-2xl" aria-hidden="true">{{ mobileMenuOpen ? 'close' : 'menu' }}</span>
                </button>

                <!-- Brand (CENTER on mobile, LEFT on desktop) -->
                <Link :href="route('growstream.home')" class="font-headline-lg-mobile text-2xl md:text-3xl font-extrabold text-primary tracking-tight shrink-0" @click="closeMobileMenu">GrowStream</Link>

                <!-- Nav links (desktop) -->
                <nav class="hidden md:flex items-center gap-1 flex-1 px-6">
                    <Link
                        v-for="link in navLinks"
                        :key="link.label"
                        :href="link.href()"
                        class="font-label-md text-label-md text-on-surface-variant hover:text-on-surface px-3 py-2 rounded-full transition-colors"
                    >{{ link.label }}</Link>
                </nav>

                <!-- Right cluster -->
                <div class="flex items-center gap-1 sm:gap-2">
                    <!-- Data Saver (authenticated) -->
                    <button
                        v-if="isAuthenticated"
                        class="hidden sm:flex text-on-surface-variant p-2 rounded-full items-center justify-center"
                        :title="dataSaver ? 'Data Saver on' : 'Data Saver off'"
                        :aria-pressed="dataSaver ? 'true' : 'false'"
                        aria-label="Toggle Data Saver"
                        @click="toggleDataSaver"
                    >
                        <span class="material-symbols-outlined text-lg" :class="dataSaver ? 'text-primary' : ''" aria-hidden="true">bolt</span>
                    </button>

                    <!-- Notifications (authenticated) -->
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

                    <!-- Search (expandable inline) -->
                    <button
                        v-if="!searchOpen"
                        class="text-on-surface-variant p-2 rounded-full flex items-center justify-center"
                        aria-label="Search"
                        @click="openSearch"
                    >
                        <span class="material-symbols-outlined text-xl" aria-hidden="true">search</span>
                    </button>
                    <div v-else class="flex items-center gap-1 absolute right-14 left-14 top-0 h-16 px-2 bg-background">
                        <span class="material-symbols-outlined text-on-surface-variant" aria-hidden="true">search</span>
                        <input
                            v-model="searchQuery"
                            type="text"
                            ref="searchInputRef"
                            class="flex-1 bg-transparent outline-none text-body-md text-on-surface placeholder-on-surface-variant"
                            placeholder="Search creators, series, videos..."
                            @keyup.enter="submitSearch"
                        />
                        <button class="text-on-surface-variant p-1 rounded-full" aria-label="Close search" @click="closeSearch">
                            <span class="material-symbols-outlined text-lg" aria-hidden="true">close</span>
                        </button>
                    </div>

                    <!-- Auth / Avatar -->
                    <template v-if="isAuthenticated">
                        <div ref="avatarMenuRef" class="relative hidden sm:block">
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
                                    <div class="flex flex-col p-1.5 space-y-0.5">
                                        <a href="/workspace" class="rounded-lg px-3 py-2 text-xs font-semibold text-primary hover:bg-surface-container-low flex items-center gap-2">
                                            <span class="material-symbols-outlined text-sm">apps</span> MyGrowNet Workspace
                                        </a>
                                        <Link :href="route('growstream.my-videos')" class="rounded-lg px-3 py-2 text-xs text-on-surface hover:bg-surface-container-low" @click="closeAvatarMenu">My Saved Videos</Link>
                                        <Link v-if="isHubContext" :href="route('growstream.hub.client.dashboard')" class="rounded-lg px-3 py-2 text-xs font-semibold text-emerald-400 hover:bg-surface-container-low flex items-center gap-2" @click="closeAvatarMenu">
                                            <span class="material-symbols-outlined text-sm">school</span> Student / Client Portal
                                        </Link>
                                        <Link :href="route('growstream.subscription')" class="rounded-lg px-3 py-2 text-xs text-primary hover:bg-surface-container-low" @click="closeAvatarMenu">Subscribe / Plans</Link>
                                        <template v-if="isCreator">
                                            <Link :href="route('growstream.creator.dashboard')" class="rounded-lg px-3 py-2 text-xs text-on-surface hover:bg-surface-container-low flex items-center gap-2" @click="closeAvatarMenu">
                                                <span class="material-symbols-outlined text-sm">video_settings</span> Studio Dashboard
                                            </Link>
                                            <Link :href="route('growstream.creator.platform.show')" class="rounded-lg px-3 py-2 text-xs text-on-surface hover:bg-surface-container-low flex items-center gap-2" @click="closeAvatarMenu">
                                                <span class="material-symbols-outlined text-sm">domain</span> Platform Settings
                                            </Link>
                                        </template>
                                        <template v-else>
                                            <Link :href="route('growstream.creator.register')" class="rounded-lg px-3 py-2 text-xs font-semibold text-primary hover:bg-primary/10" @click="closeAvatarMenu">🚀 Become a Creator</Link>
                                        </template>

                                        <template v-if="isAdmin">
                                            <div class="my-1 border-t border-outline-variant/60"></div>
                                            <Link :href="route('growstream.admin.videos')" class="rounded-lg px-3 py-2 text-xs font-bold text-amber-400 hover:bg-surface-container-low flex items-center gap-2" @click="closeAvatarMenu">
                                                <span class="material-symbols-outlined text-sm">admin_panel_settings</span> Admin Panel
                                            </Link>
                                            <Link :href="route('growstream.admin.hubs')" class="rounded-lg px-3 py-2 text-xs text-amber-300 hover:bg-surface-container-low flex items-center gap-2" @click="closeAvatarMenu">
                                                <span class="material-symbols-outlined text-sm">domain</span> Manage Creator Hubs
                                            </Link>
                                        </template>

                                        <div class="my-1 border-t border-outline-variant/60"></div>
                                        <button class="rounded-lg px-3 py-2 text-left text-xs font-medium text-error hover:bg-error-container flex items-center gap-2" @click="logout">
                                            <span class="material-symbols-outlined text-sm">logout</span> Sign Out
                                        </button>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </template>
                    <template v-else>
                        <a :href="loginHref" class="hidden sm:block font-label-md text-label-md text-on-surface-variant px-2 sm:px-3 py-2">Sign In</a>
                        <a :href="registerHref" class="bg-primary text-on-primary px-4 py-2 rounded-full font-label-md text-label-md hover:bg-[#c94918] transition-colors">Sign Up</a>
                    </template>
                </div>
            </div>

            <!-- Row 2: Category Pill Bar (YouTube-Style Opaque Sticky Header Component) -->
            <div v-if="categories && categories.length > 0" class="border-t border-outline-variant/40 bg-[#0e0b09] py-2 px-margin-mobile md:px-margin-desktop">
                <div class="max-w-6xl mx-auto flex items-center gap-2 overflow-x-auto scrollbar-none">
                    <button
                        :class="[
                            'shrink-0 px-4 py-1.5 rounded-full text-xs transition-all',
                            !selectedCategory ? 'bg-primary text-on-primary font-bold shadow' : 'bg-surface-container-low text-on-surface-variant border border-outline-variant/60 hover:bg-surface-container-high hover:text-on-surface'
                        ]"
                        @click="onCategoryClick('')"
                    >All</button>
                    <button
                        v-for="cat in categories"
                        :key="cat.id"
                        @click="onCategoryClick(cat.slug)"
                        :class="[
                            'shrink-0 px-4 py-1.5 rounded-full text-xs transition-all',
                            selectedCategory === cat.slug ? 'bg-primary text-on-primary font-bold shadow' : 'bg-surface-container-low text-on-surface-variant border border-outline-variant/60 hover:bg-surface-container-high hover:text-on-surface'
                        ]"
                    >
                        {{ cat.name }}
                    </button>
                </div>
            </div>
        </header>

        <!-- Mobile menu panel -->
        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-2"
            leave-active-class="transition duration-150 ease-in"
            leave-to-class="opacity-0 -translate-y-2"
        >
            <div v-if="mobileMenuOpen" class="md:hidden bg-surface-container-lowest border-b border-outline-variant/60 px-4 py-4 space-y-1">
                <Link
                    v-for="link in navLinks"
                    :key="link.label"
                    :href="link.href()"
                    class="block px-3 py-2.5 rounded-lg font-label-md text-label-md text-on-surface-variant hover:bg-surface-container-low"
                    @click="closeMobileMenu"
                >{{ link.label }}</Link>
                <template v-if="isAuthenticated">
                    <Link :href="route('growstream.my-videos')" class="block px-3 py-2.5 rounded-lg font-label-md text-label-md text-on-surface-variant hover:bg-surface-container-low" @click="closeMobileMenu">My Videos</Link>
                    <Link :href="route('growstream.downloads')" class="block px-3 py-2.5 rounded-lg font-label-md text-label-md text-on-surface-variant hover:bg-surface-container-low" @click="closeMobileMenu">Downloads</Link>
                    <Link :href="route('growstream.creator.dashboard')" class="block px-3 py-2.5 rounded-lg font-label-md text-label-md text-on-surface-variant hover:bg-surface-container-low" @click="closeMobileMenu">Creator Studio</Link>
                    <button class="block w-full text-left px-3 py-2.5 rounded-lg font-label-md text-label-md text-error hover:bg-error-container" @click="logout">Sign Out</button>
                </template>
                <template v-else>
                    <a :href="loginHref" class="block px-3 py-2.5 rounded-lg font-label-md text-label-md text-on-surface-variant">Sign In</a>
                </template>
            </div>
        </transition>
    </div>
</template>
