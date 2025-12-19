<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import OfflineIndicator from '@/Components/LifePlus/OfflineIndicator.vue';
import {
    HomeIcon,
    BanknotesIcon,
    ClipboardDocumentCheckIcon,
    UserGroupIcon,
    UserCircleIcon,
    XMarkIcon,
    ArrowRightOnRectangleIcon,
    Cog6ToothIcon,
    BellIcon,
    BookOpenIcon,
    DocumentTextIcon,
    SparklesIcon,
    BriefcaseIcon,
    ChartBarIcon,
} from '@heroicons/vue/24/outline';
import {
    HomeIcon as HomeIconSolid,
    BanknotesIcon as BanknotesIconSolid,
    ClipboardDocumentCheckIcon as ClipboardIconSolid,
    UserGroupIcon as UserGroupIconSolid,
    UserCircleIcon as UserCircleIconSolid,
} from '@heroicons/vue/24/solid';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const pageKey = computed(() => page.url);

const moreMenuOpen = ref(false);

onMounted(() => {
    document.addEventListener('click', closeMenus);
    document.body.style.overscrollBehavior = 'none';
});

onUnmounted(() => {
    document.removeEventListener('click', closeMenus);
});

const closeMenus = (e: MouseEvent) => {
    const target = e.target as HTMLElement;
    if (!target.closest('.more-menu-container')) {
        moreMenuOpen.value = false;
    }
};

const navigation = [
    { name: 'Home', href: 'lifeplus.home', icon: HomeIcon, iconSolid: HomeIconSolid, color: 'emerald' },
    { name: 'Money', href: 'lifeplus.money.index', icon: BanknotesIcon, iconSolid: BanknotesIconSolid, color: 'green' },
    { name: 'Tasks', href: 'lifeplus.tasks.index', icon: ClipboardDocumentCheckIcon, iconSolid: ClipboardIconSolid, color: 'blue' },
    { name: 'Community', href: 'lifeplus.community.index', icon: UserGroupIcon, iconSolid: UserGroupIconSolid, color: 'purple' },
    { name: 'Profile', href: 'lifeplus.profile.index', icon: UserCircleIcon, iconSolid: UserCircleIconSolid, color: 'pink' },
];

const isActive = (routeName: string) => {
    return route().current(routeName) || route().current(routeName + '.*');
};

const handleLogout = () => {
    router.post(route('logout'));
};

const getInitials = (name: string) => {
    if (!name) return '?';
    const parts = name.split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
};

const getGreeting = () => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good Morning';
    if (hour < 17) return 'Good Afternoon';
    return 'Good Evening';
};
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-emerald-50 via-teal-50 to-cyan-50 flex flex-col">
        <!-- Offline Indicator -->
        <OfflineIndicator />
        
        <!-- App Header with Gradient -->
        <header class="sticky top-0 z-40 bg-gradient-to-r from-emerald-500 via-teal-500 to-cyan-500 shadow-lg shadow-emerald-200/50 safe-area-top">
            <div class="flex items-center justify-between h-16 px-4 max-w-lg mx-auto">
                <!-- Left: Logo/Brand -->
                <Link :href="route('lifeplus.home')" class="flex items-center gap-2.5">
                    <div class="flex aspect-square size-10 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-sm shadow-lg border border-white/30">
                        <SparklesIcon class="size-6 text-white drop-shadow-md" aria-hidden="true" />
                    </div>
                    <div class="flex flex-col">
                        <span class="text-lg font-bold text-white leading-tight drop-shadow-md">Life+</span>
                        <span class="text-[10px] text-emerald-100 leading-tight">Your Daily Companion</span>
                    </div>
                </Link>

                <!-- Right: Actions -->
                <div class="flex items-center gap-1">
                    <Link 
                        :href="route('lifeplus.knowledge.index')"
                        class="p-2.5 rounded-xl text-white/90 hover:bg-white/20 backdrop-blur-sm transition-all"
                        aria-label="Knowledge Center"
                    >
                        <BookOpenIcon class="h-5 w-5" aria-hidden="true" />
                    </Link>
                    
                    <button 
                        class="p-2.5 rounded-xl text-white/90 hover:bg-white/20 backdrop-blur-sm transition-all relative"
                        aria-label="Notifications"
                    >
                        <BellIcon class="h-5 w-5" aria-hidden="true" />
                        <span class="absolute top-2 right-2 w-2 h-2 bg-pink-500 rounded-full border-2 border-white"></span>
                    </button>

                    <button 
                        @click.stop="moreMenuOpen = !moreMenuOpen"
                        class="ml-1 p-0.5 rounded-full ring-2 ring-white/30 hover:ring-white/50 transition-all more-menu-container"
                        aria-label="Open menu"
                    >
                        <div class="h-9 w-9 rounded-full bg-white shadow-lg flex items-center justify-center">
                            <span class="text-sm font-bold bg-gradient-to-br from-emerald-600 to-teal-600 bg-clip-text text-transparent">
                                {{ getInitials(user?.name || '') }}
                            </span>
                        </div>
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 pb-20">
            <div class="max-w-lg mx-auto">
                <slot />
            </div>
        </main>

        <!-- Bottom Navigation with Glassmorphism -->
        <nav class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-xl border-t border-gray-200/50 shadow-2xl shadow-gray-900/10 safe-area-bottom">
            <div class="max-w-lg mx-auto px-2">
                <div class="flex items-center justify-around h-16">
                    <Link
                        v-for="item in navigation"
                        :key="item.name"
                        :href="route(item.href)"
                        preserve-scroll
                        :class="[
                            'flex flex-col items-center justify-center w-16 h-14 rounded-2xl transition-all active:scale-95',
                            isActive(item.href)
                                ? 'text-emerald-600 bg-emerald-50'
                                : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'
                        ]"
                        :aria-label="`Navigate to ${item.name}`"
                        :aria-current="isActive(item.href) ? 'page' : undefined"
                    >
                        <component 
                            :is="isActive(item.href) ? item.iconSolid : item.icon" 
                            class="h-6 w-6" 
                            aria-hidden="true"
                        />
                        <span class="text-[10px] font-medium mt-0.5">{{ item.name }}</span>
                    </Link>
                </div>
            </div>
        </nav>

        <!-- More Menu Slide-in -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div 
                v-if="moreMenuOpen" 
                class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm"
                @click="moreMenuOpen = false"
            />
        </Transition>

        <Transition
            enter-active-class="transition-transform duration-300 ease-out"
            enter-from-class="translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition-transform duration-200 ease-in"
            leave-from-class="translate-x-0"
            leave-to-class="translate-x-full"
        >
            <div 
                v-if="moreMenuOpen"
                class="fixed inset-y-0 right-0 w-full max-w-xs z-50 bg-white shadow-2xl flex flex-col safe-area-top safe-area-bottom"
            >
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Menu</h2>
                    <button 
                        @click="moreMenuOpen = false"
                        class="p-2 rounded-full hover:bg-gray-100 transition-colors"
                        aria-label="Close menu"
                    >
                        <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                    </button>
                </div>
                
                <div class="px-4 py-4 border-b border-gray-100 bg-gradient-to-br from-emerald-50 to-teal-50">
                    <div class="flex items-center gap-3">
                        <div class="h-12 w-12 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg">
                            <span class="text-lg font-semibold text-white">{{ getInitials(user?.name || '') }}</span>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ user?.name }}</p>
                            <p class="text-sm text-gray-500">{{ user?.email }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto px-4 py-3 space-y-1">
                    <Link 
                        :href="route('lifeplus.habits.index')"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 hover:bg-purple-50 transition-colors"
                        @click="moreMenuOpen = false"
                    >
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center shadow-md">
                            <SparklesIcon class="h-5 w-5 text-white" aria-hidden="true" />
                        </div>
                        <span class="font-medium text-sm">Habit Tracker</span>
                    </Link>
                    
                    <Link 
                        :href="route('lifeplus.notes.index')"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 hover:bg-amber-50 transition-colors"
                        @click="moreMenuOpen = false"
                    >
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center shadow-md">
                            <DocumentTextIcon class="h-5 w-5 text-white" aria-hidden="true" />
                        </div>
                        <span class="font-medium text-sm">Notes</span>
                    </Link>
                    
                    <Link 
                        :href="route('lifeplus.gigs.index')"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 hover:bg-blue-50 transition-colors"
                        @click="moreMenuOpen = false"
                    >
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-indigo-500 flex items-center justify-center shadow-md">
                            <BriefcaseIcon class="h-5 w-5 text-white" aria-hidden="true" />
                        </div>
                        <span class="font-medium text-sm">Gig Finder</span>
                    </Link>
                    
                    <Link 
                        :href="route('lifeplus.knowledge.index')"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 hover:bg-teal-50 transition-colors"
                        @click="moreMenuOpen = false"
                    >
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-teal-500 to-cyan-500 flex items-center justify-center shadow-md">
                            <BookOpenIcon class="h-5 w-5 text-white" aria-hidden="true" />
                        </div>
                        <span class="font-medium text-sm">Knowledge Center</span>
                    </Link>
                    
                    <Link 
                        :href="route('lifeplus.analytics.index')"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 hover:bg-indigo-50 transition-colors"
                        @click="moreMenuOpen = false"
                    >
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center shadow-md">
                            <ChartBarIcon class="h-5 w-5 text-white" aria-hidden="true" />
                        </div>
                        <span class="font-medium text-sm">Analytics & Reports</span>
                    </Link>
                    
                    <div class="border-t border-gray-100 my-2"></div>
                    
                    <Link 
                        href="/apps"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 hover:bg-emerald-50 transition-colors border border-emerald-200"
                        @click="moreMenuOpen = false"
                    >
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center shadow-md">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                            </svg>
                        </div>
                        <span class="font-medium text-sm">All Apps</span>
                    </Link>
                    
                    <Link 
                        :href="route('lifeplus.profile.settings')"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 hover:bg-gray-50 transition-colors"
                        @click="moreMenuOpen = false"
                    >
                        <div class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center">
                            <Cog6ToothIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                        </div>
                        <span class="font-medium text-sm">Settings</span>
                    </Link>
                    
                    <button 
                        @click="handleLogout"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-red-600 hover:bg-red-50 transition-colors w-full"
                    >
                        <div class="w-9 h-9 rounded-full bg-red-50 flex items-center justify-center">
                            <ArrowRightOnRectangleIcon class="h-5 w-5 text-red-500" aria-hidden="true" />
                        </div>
                        <span class="font-medium text-sm">Sign Out</span>
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>
