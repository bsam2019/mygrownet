<script setup lang="ts">
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';

interface Props {
    title?: string;
}

withDefaults(defineProps<Props>(), {
    title: 'GrowStream Admin',
});

const page = usePage();
const user = computed(() => (page.props as any).auth?.user ?? null);

const currentHost = typeof window !== 'undefined' ? window.location.hostname : '';
const mainDomain = currentHost.endsWith('.mygrownet.com') || currentHost === 'mygrownet.com'
    ? 'https://mygrownet.com'
    : (typeof window !== 'undefined' ? window.location.origin : '');

const adminNav = [
    { label: 'Videos', href: () => route('growstream.admin.videos'), icon: 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z' },
    { label: 'Moderation', href: () => route('growstream.admin.moderation'), icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' },
    { label: 'Creator Hubs', href: () => route('growstream.admin.hubs'), icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5' },
    { label: 'Hub Pricing', href: () => route('growstream.admin.hub_pricing.show'), icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    { label: 'Creators', href: () => route('growstream.admin.creators'), icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' },
    { label: 'Categories', href: () => route('growstream.admin.categories'), icon: 'M4 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21L9.09 10.5a7.002 7.002 0 005.744 5.06l.39-2.05a1 1 0 011.22-.78l4.304 1.05a1 1 0 01.763.973V18a2 2 0 01-2 2h-1C9.716 20 4 14.284 4 7.5V5z' },
    { label: 'Analytics', href: () => route('growstream.admin.analytics'), icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
    { label: 'Sponsorship', href: () => route('growstream.admin.sponsorship'), icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    { label: 'Plans & Tiers', href: () => `${mainDomain}/admin/module-subscriptions/growstream`, icon: 'M11 3.055A9 9 0 1020.945 13H11V3.055zM20.488 9H15V3.512A9.025 9.025 0 0120.488 9z' },
];

// Responsive Mobile Sidebar Drawer Toggle
const mobileSidebarOpen = ref(false);
const toggleMobileSidebar = () => { mobileSidebarOpen.value = !mobileSidebarOpen.value; };
const closeMobileSidebar = () => { mobileSidebarOpen.value = false; };

// Interactive Top-Right User Dropdown Menu
const userMenuOpen = ref(false);
const userMenuRef = ref<HTMLElement>();
const toggleUserMenu = () => { userMenuOpen.value = !userMenuOpen.value; };
const closeUserMenu = () => { userMenuOpen.value = false; };

const handleClickOutside = (event: MouseEvent) => {
    if (userMenuRef.value && !userMenuRef.value.contains(event.target as Node)) {
        closeUserMenu();
    }
};

onMounted(() => {
    if (typeof document !== 'undefined') {
        document.addEventListener('click', handleClickOutside);
    }
});

onUnmounted(() => {
    if (typeof document !== 'undefined') {
        document.removeEventListener('click', handleClickOutside);
    }
});

const logout = () => {
    closeUserMenu();
    closeMobileSidebar();
    router.post(route('growstream.logout'));
};
</script>

<template>
    <Head :title="title" />

    <div class="gs-app min-h-screen bg-[#0e0b09] text-[#f5f0eb]">
        <!-- Top Navigation Header -->
        <header class="sticky top-0 z-40 border-b border-neutral-800 bg-[#0e0b09]/95 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <!-- Left: Hamburger Toggle (Mobile) + Brand & Exit Links -->
                <div class="flex items-center gap-3">
                    <!-- Mobile Hamburger Menu Button -->
                    <button
                        @click="toggleMobileSidebar"
                        class="md:hidden text-neutral-300 p-2 rounded-lg hover:bg-neutral-800 focus:outline-none"
                        aria-label="Toggle Admin Navigation Drawer"
                    >
                        <span class="material-symbols-outlined text-2xl">{{ mobileSidebarOpen ? 'close' : 'menu' }}</span>
                    </button>

                    <!-- Brand -->
                    <Link :href="route('growstream.admin.videos')" class="flex items-center gap-2">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#e2571f] text-white shadow-md">
                            <span class="material-symbols-outlined text-xl">play_circle</span>
                        </div>
                        <div>
                            <span class="text-lg font-bold tracking-tight text-white">
                                Grow<span class="text-[#e2571f]">Stream</span>
                            </span>
                            <span class="ml-2 rounded-full bg-[#e2571f]/20 px-2 py-0.5 text-xs font-semibold text-[#e2571f]">
                                Admin
                            </span>
                        </div>
                    </Link>

                    <!-- Exit Links (Desktop) -->
                    <div class="hidden lg:flex items-center gap-2 border-l border-neutral-800 pl-4">
                        <Link :href="route('growstream.home')" class="text-xs text-neutral-400 hover:text-white flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">arrow_back</span> Main Site
                        </Link>
                        <span class="text-neutral-700">•</span>
                        <a :href="`${mainDomain}/workspace`" class="text-xs text-neutral-400 hover:text-white flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">apps</span> Workspace
                        </a>
                    </div>
                </div>

                <!-- Right Cluster: View Site + User Profile Dropdown -->
                <div class="flex items-center gap-3">
                    <Link :href="route('growstream.home')" class="hidden sm:inline-flex px-3.5 py-1.5 rounded-full border border-neutral-700 text-xs font-semibold text-neutral-300 hover:bg-neutral-800 transition-colors">
                        View Site
                    </Link>

                    <!-- Top-Right User Profile Dropdown -->
                    <div ref="userMenuRef" class="relative">
                        <button
                            @click.stop="toggleUserMenu"
                            class="flex items-center gap-2 p-1.5 rounded-full hover:bg-neutral-800/60 transition-colors cursor-pointer"
                            aria-label="User Account Menu"
                        >
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[#e2571f] text-sm font-bold text-white shadow-md">
                                {{ (user?.name || 'A').charAt(0).toUpperCase() }}
                            </div>
                            <span class="hidden md:inline font-medium text-xs text-neutral-200 max-w-[120px] truncate">{{ user?.name }}</span>
                            <span class="material-symbols-outlined text-sm text-neutral-400">expand_more</span>
                        </button>

                        <!-- Dropdown Menu -->
                        <transition
                            enter-active-class="transition duration-150 ease-out"
                            enter-from-class="opacity-0 translate-y-1"
                            leave-active-class="transition duration-100 ease-in"
                            leave-to-class="opacity-0"
                        >
                            <div
                                v-if="userMenuOpen"
                                class="absolute right-0 top-full mt-2 w-64 overflow-hidden rounded-2xl border border-neutral-800 bg-[#16120f] shadow-2xl z-50 text-xs"
                                @click.stop
                            >
                                <div class="border-b border-neutral-800 px-4 py-3 bg-neutral-900/50">
                                    <p class="truncate font-bold text-neutral-100">{{ user?.name }}</p>
                                    <p class="truncate text-[11px] text-neutral-400">{{ user?.email }}</p>
                                    <span class="mt-1 inline-block px-2 py-0.5 rounded bg-[#e2571f]/20 text-[#e2571f] text-[10px] font-semibold">Platform Administrator</span>
                                </div>

                                <div class="p-1.5 space-y-0.5">
                                    <a :href="`${mainDomain}/workspace`" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-neutral-200 hover:bg-neutral-800/80 transition-colors">
                                        <span class="material-symbols-outlined text-sm text-[#e2571f]">apps</span>
                                        <span>MyGrowNet Workspace</span>
                                    </a>
                                    <Link :href="route('growstream.home')" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-neutral-200 hover:bg-neutral-800/80 transition-colors" @click="closeUserMenu">
                                        <span class="material-symbols-outlined text-sm text-neutral-400">home</span>
                                        <span>GrowStream Main Site</span>
                                    </Link>
                                    <Link :href="route('growstream.creator.dashboard')" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-neutral-200 hover:bg-neutral-800/80 transition-colors" @click="closeUserMenu">
                                        <span class="material-symbols-outlined text-sm text-neutral-400">video_settings</span>
                                        <span>Creator Studio</span>
                                    </Link>

                                    <div class="my-1 border-t border-neutral-800/80"></div>

                                    <Link :href="route('growstream.admin.hubs')" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-neutral-200 hover:bg-neutral-800/80 transition-colors" @click="closeUserMenu">
                                        <span class="material-symbols-outlined text-sm text-amber-500">domain</span>
                                        <span>Manage Creator Hubs</span>
                                    </Link>
                                    <Link :href="route('growstream.admin.hub_pricing.show')" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-neutral-200 hover:bg-neutral-800/80 transition-colors" @click="closeUserMenu">
                                        <span class="material-symbols-outlined text-sm text-amber-500">payments</span>
                                        <span>Hub Subscription Pricing</span>
                                    </Link>

                                    <div class="my-1 border-t border-neutral-800/80"></div>

                                    <button @click="logout" class="w-full text-left flex items-center gap-2.5 px-3 py-2 rounded-xl text-red-400 hover:bg-red-500/10 transition-colors">
                                        <span class="material-symbols-outlined text-sm">logout</span>
                                        <span>Sign Out</span>
                                    </button>
                                </div>
                            </div>
                        </transition>
                    </div>
                </div>
            </div>

            <!-- Admin Nav Tabs (Desktop) -->
            <nav aria-label="Admin" class="mx-auto hidden md:block max-w-7xl px-4 sm:px-6 lg:px-8 border-t border-neutral-800/60">
                <div class="flex gap-1 overflow-x-auto py-1 scrollbar-none">
                    <Link
                        v-for="item in adminNav"
                        :key="item.label"
                        :href="item.href()"
                        class="flex items-center gap-2 px-3.5 py-2 text-xs font-medium text-neutral-400 hover:text-white rounded-lg hover:bg-neutral-800/50 transition-colors whitespace-nowrap"
                    >
                        <svg class="h-4 w-4 text-[#e2571f]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                        </svg>
                        {{ item.label }}
                    </Link>
                </div>
            </nav>
        </header>

        <!-- Slide-Over Responsive Mobile Admin Sidebar Drawer -->
        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 -translate-x-full"
            leave-active-class="transition duration-150 ease-in"
            leave-to-class="opacity-0 -translate-x-full"
        >
            <div
                v-if="mobileSidebarOpen"
                class="fixed inset-0 z-50 bg-[#0e0b09] flex flex-col justify-between overflow-y-auto md:hidden p-6 space-y-6"
            >
                <div class="space-y-6">
                    <!-- Drawer Header -->
                    <div class="flex items-center justify-between border-b border-neutral-800 pb-4">
                        <div class="flex items-center gap-2">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#e2571f] text-white">
                                <span class="material-symbols-outlined text-xl">play_circle</span>
                            </div>
                            <span class="text-lg font-bold text-white">Grow<span class="text-[#e2571f]">Stream</span> Admin</span>
                        </div>
                        <button @click="closeMobileSidebar" class="p-2 text-neutral-400 hover:text-white rounded-lg bg-neutral-900">
                            <span class="material-symbols-outlined text-xl">close</span>
                        </button>
                    </div>

                    <!-- All 9 Admin Navigation Links -->
                    <div class="space-y-1">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-[#e2571f] mb-3">Admin Operations</p>
                        <Link
                            v-for="item in adminNav"
                            :key="item.label"
                            :href="item.href()"
                            @click="closeMobileSidebar"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold text-neutral-200 hover:bg-neutral-800/80 transition-colors"
                        >
                            <svg class="h-5 w-5 text-[#e2571f]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                            </svg>
                            <span>{{ item.label }}</span>
                        </Link>
                    </div>

                    <!-- Platform Exit Shortcuts -->
                    <div class="pt-4 border-t border-neutral-800 space-y-2">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-neutral-500 mb-2">Platform Shortcuts</p>
                        <Link :href="route('growstream.home')" @click="closeMobileSidebar" class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl text-xs font-medium text-neutral-300 hover:bg-neutral-800">
                            <span class="material-symbols-outlined text-base text-neutral-400">home</span> GrowStream Main Site
                        </Link>
                        <a :href="`${mainDomain}/workspace`" class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl text-xs font-medium text-neutral-300 hover:bg-neutral-800">
                            <span class="material-symbols-outlined text-base text-[#e2571f]">apps</span> MyGrowNet Workspace
                        </a>
                        <Link :href="route('growstream.creator.dashboard')" @click="closeMobileSidebar" class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl text-xs font-medium text-neutral-300 hover:bg-neutral-800">
                            <span class="material-symbols-outlined text-base text-neutral-400">video_settings</span> Creator Studio
                        </Link>
                    </div>
                </div>

                <!-- Drawer User & Logout Footer -->
                <div class="border-t border-neutral-800 pt-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[#e2571f] text-sm font-bold text-white">
                            {{ (user?.name || 'A').charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <p class="text-xs font-bold text-white truncate max-w-[140px]">{{ user?.name }}</p>
                            <p class="text-[10px] text-neutral-400 truncate max-w-[140px]">{{ user?.email }}</p>
                        </div>
                    </div>
                    <button @click="logout" class="p-2 rounded-xl bg-red-500/10 text-red-400 hover:bg-red-500/20 text-xs font-bold flex items-center gap-1">
                        <span class="material-symbols-outlined text-base">logout</span> Exit
                    </button>
                </div>
            </div>
        </transition>

        <!-- Main Content Slot -->
        <main class="mx-auto min-h-[calc(100vh-8rem)] max-w-7xl px-4 pb-24 pt-6 sm:px-6 sm:pb-10 lg:px-8">
            <slot />
        </main>
    </div>
</template>
