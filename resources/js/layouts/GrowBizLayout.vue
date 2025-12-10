<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import PwaInstallPrompt from '@/Components/GrowBiz/PwaInstallPrompt.vue';
import AppTransition from '@/Components/GrowBiz/AppTransition.vue';
import GlobalLoadingBar from '@/Components/GrowBiz/GlobalLoadingBar.vue';
import ToastContainer from '@/Components/GrowBiz/ToastContainer.vue';
import NotificationDropdown from '@/Components/GrowBiz/NotificationDropdown.vue';
import { initNavigationListeners } from '@/composables/useInertiaEnhancements';
import { useToast } from '@/composables/useToast';
import {
    HomeIcon,
    ClipboardDocumentListIcon,
    UsersIcon,
    EllipsisHorizontalIcon,
    XMarkIcon,
    ArrowRightOnRectangleIcon,
    ChartBarIcon,
    ArrowLeftIcon,
    Cog6ToothIcon,
    BellIcon,
    MagnifyingGlassIcon,
    PresentationChartLineIcon,
    DocumentChartBarIcon,
    ChatBubbleLeftRightIcon,
} from '@heroicons/vue/24/outline';
import {
    HomeIcon as HomeIconSolid,
    ClipboardDocumentListIcon as ClipboardIconSolid,
    UsersIcon as UsersIconSolid,
} from '@heroicons/vue/24/solid';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isPwa = computed(() => (page.props as any).growbiz?.isPwa ?? false);
// Get user role from growbiz shared data or page props (for dashboard pages)
const userRole = computed(() => (page.props as any).growbiz?.userRole ?? (page.props as any).userRole ?? 'owner');
const isEmployee = computed(() => userRole.value === 'employee');

// Notification and message counts from shared data
const unreadNotificationCount = computed(() => (page.props as any).growbiz?.unreadNotificationCount ?? 0);
const unreadMessageCount = computed(() => (page.props as any).growbiz?.unreadMessageCount ?? 0);

// Get current page URL for keying transitions
const pageKey = computed(() => page.url);

// Toast notifications for flash messages
const { toast } = useToast();
const flash = computed(() => (page.props as any).flash);

// Watch for flash messages and show toasts
watch(flash, (newFlash) => {
    if (newFlash?.success) {
        toast.success(newFlash.success);
    }
    if (newFlash?.error) {
        toast.error(newFlash.error);
    }
    if (newFlash?.warning) {
        toast.warning(newFlash.warning);
    }
    if (newFlash?.info) {
        toast.info(newFlash.info);
    }
}, { immediate: true, deep: true });

const moreMenuOpen = ref(false);
const profileMenuOpen = ref(false);
const isStandalone = ref(false);
const navigationInitialized = ref(false);

// Detect standalone PWA mode
onMounted(() => {
    isStandalone.value = window.matchMedia('(display-mode: standalone)').matches
        || (window.navigator as any).standalone === true;
    
    document.addEventListener('click', closeMenus);
    
    // Prevent pull-to-refresh on mobile
    document.body.style.overscrollBehavior = 'none';
    
    // Initialize navigation listeners once
    if (!navigationInitialized.value) {
        initNavigationListeners();
        navigationInitialized.value = true;
    }
});

onUnmounted(() => {
    document.removeEventListener('click', closeMenus);
});

const closeMenus = (e: MouseEvent) => {
    const target = e.target as HTMLElement;
    if (!target.closest('.more-menu-container')) {
        moreMenuOpen.value = false;
    }
    if (!target.closest('.profile-menu-container')) {
        profileMenuOpen.value = false;
    }
};

const closeProfileMenu = () => {
    profileMenuOpen.value = false;
};

// Navigation items - Team tab only visible to owners/supervisors
const navigation = computed(() => {
    const items = [
        { name: 'Home', href: 'growbiz.dashboard', icon: HomeIcon, iconSolid: HomeIconSolid },
        { name: 'Tasks', href: 'growbiz.tasks.index', icon: ClipboardDocumentListIcon, iconSolid: ClipboardIconSolid },
    ];
    
    // Only show Team tab for owners (not regular employees)
    if (!isEmployee.value) {
        items.push({ name: 'Team', href: 'growbiz.employees.index', icon: UsersIcon, iconSolid: UsersIconSolid });
    }
    
    return items;
});

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

const closeMoreMenu = () => {
    moreMenuOpen.value = false;
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <!-- Global Loading Bar -->
        <GlobalLoadingBar />
        
        <!-- App Header - Clean & Modern -->
        <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-gray-100 safe-area-top">
            <div class="flex items-center justify-between h-14 px-4 max-w-4xl mx-auto">
                <!-- Left: Logo/Brand -->
                <Link :href="route('growbiz.dashboard')" class="flex items-center gap-2.5">
                    <div class="flex aspect-square size-9 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 shadow-md shadow-emerald-200">
                        <ChartBarIcon class="size-5 text-white" aria-hidden="true" />
                    </div>
                    <div class="flex flex-col">
                        <span class="text-base font-bold text-gray-900 leading-tight">GrowBiz</span>
                        <span class="text-[10px] text-gray-500 leading-tight">SME Management</span>
                    </div>
                </Link>

                <!-- Right: Actions -->
                <div class="flex items-center gap-1">
                    <!-- Search Button -->
                    <button 
                        class="p-2.5 rounded-xl text-gray-500 hover:bg-gray-100 active:bg-gray-200 transition-colors touch-manipulation"
                        aria-label="Search"
                    >
                        <MagnifyingGlassIcon class="h-5 w-5" aria-hidden="true" />
                    </button>
                    
                    <!-- Notifications Dropdown -->
                    <NotificationDropdown :unread-count="unreadNotificationCount" />
                    
                    <!-- Messages -->
                    <Link 
                        :href="route('growbiz.messages.index')"
                        class="p-2.5 rounded-xl text-gray-500 hover:bg-gray-100 active:bg-gray-200 transition-colors touch-manipulation relative"
                        aria-label="View messages"
                    >
                        <ChatBubbleLeftRightIcon class="h-5 w-5" aria-hidden="true" />
                        <!-- Unread message badge -->
                        <span 
                            v-if="unreadMessageCount > 0" 
                            class="absolute top-1.5 right-1.5 min-w-[18px] h-[18px] px-1 bg-emerald-500 rounded-full flex items-center justify-center"
                        >
                            <span class="text-[10px] font-bold text-white">
                                {{ unreadMessageCount > 99 ? '99+' : unreadMessageCount }}
                            </span>
                        </span>
                    </Link>

                    <!-- User Avatar - Opens Profile Menu -->
                    <button 
                        @click.stop="profileMenuOpen = !profileMenuOpen"
                        class="ml-1 p-0.5 rounded-full ring-2 ring-emerald-100 hover:ring-emerald-200 transition-all touch-manipulation profile-menu-container"
                        aria-label="Open profile menu"
                    >
                        <div class="h-8 w-8 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center">
                            <span class="text-xs font-semibold text-white">
                                {{ getInitials(user?.name || '') }}
                            </span>
                        </div>
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Content Area - Full Width -->
        <main class="flex-1 pb-20">
            <div class="max-w-4xl mx-auto">
                <AppTransition>
                    <div :key="pageKey">
                        <slot />
                    </div>
                </AppTransition>
            </div>
        </main>

        <!-- PWA Install Prompt -->
        <PwaInstallPrompt />
        
        <!-- Toast Notifications -->
        <ToastContainer />

        <!-- More Menu Bottom Sheet -->
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
                @click="closeMoreMenu"
            />
        </Transition>

        <Transition
            enter-active-class="transition-transform duration-300 ease-out"
            enter-from-class="translate-y-full"
            enter-to-class="translate-y-0"
            leave-active-class="transition-transform duration-200 ease-in"
            leave-from-class="translate-y-0"
            leave-to-class="translate-y-full"
        >
            <div 
                v-if="moreMenuOpen"
                class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md z-50 bg-white rounded-t-3xl shadow-2xl safe-area-bottom"
            >
                <!-- Handle bar -->
                <div class="flex justify-center pt-3 pb-2">
                    <div class="w-10 h-1 bg-gray-300 rounded-full"></div>
                </div>
                
                <!-- User Info -->
                <div class="px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="h-12 w-12 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center shadow-lg">
                            <span class="text-lg font-semibold text-white">{{ getInitials(user?.name || '') }}</span>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ user?.name }}</p>
                            <p class="text-sm text-gray-500">{{ user?.email }}</p>
                        </div>
                    </div>
                </div>

                <!-- Menu Items -->
                <div class="px-4 pt-2 pb-3">
                    <!-- Notifications -->
                    <Link 
                        :href="route('growbiz.notifications.index')"
                        class="flex items-center gap-3 px-3 py-2 rounded-xl text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors"
                        @click="closeMoreMenu"
                    >
                        <div class="w-9 h-9 rounded-full bg-red-50 flex items-center justify-center relative">
                            <BellIcon class="h-5 w-5 text-red-600" aria-hidden="true" />
                            <span 
                                v-if="unreadNotificationCount > 0" 
                                class="absolute -top-1 -right-1 min-w-[16px] h-[16px] px-1 bg-red-500 rounded-full flex items-center justify-center"
                            >
                                <span class="text-[9px] font-bold text-white">{{ unreadNotificationCount > 9 ? '9+' : unreadNotificationCount }}</span>
                            </span>
                        </div>
                        <span class="font-medium text-sm">Notifications</span>
                    </Link>
                    
                    <!-- Messages -->
                    <Link 
                        :href="route('growbiz.messages.index')"
                        class="flex items-center gap-3 px-3 py-2 rounded-xl text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors"
                        @click="closeMoreMenu"
                    >
                        <div class="w-9 h-9 rounded-full bg-blue-50 flex items-center justify-center relative">
                            <ChatBubbleLeftRightIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                            <span 
                                v-if="unreadMessageCount > 0" 
                                class="absolute -top-1 -right-1 min-w-[16px] h-[16px] px-1 bg-emerald-500 rounded-full flex items-center justify-center"
                            >
                                <span class="text-[9px] font-bold text-white">{{ unreadMessageCount > 9 ? '9+' : unreadMessageCount }}</span>
                            </span>
                        </div>
                        <span class="font-medium text-sm">Messages</span>
                    </Link>
                    
                    <Link 
                        :href="route('growbiz.reports.analytics')"
                        class="flex items-center gap-3 px-3 py-2 rounded-xl text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors"
                        @click="closeMoreMenu"
                    >
                        <div class="w-9 h-9 rounded-full bg-emerald-50 flex items-center justify-center">
                            <ChartBarIcon class="h-5 w-5 text-emerald-600" aria-hidden="true" />
                        </div>
                        <span class="font-medium text-sm">Analytics</span>
                    </Link>
                    
                    <Link 
                        :href="route('growbiz.reports.performance')"
                        class="flex items-center gap-3 px-3 py-2 rounded-xl text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors"
                        @click="closeMoreMenu"
                    >
                        <div class="w-9 h-9 rounded-full bg-purple-50 flex items-center justify-center">
                            <PresentationChartLineIcon class="h-5 w-5 text-purple-600" aria-hidden="true" />
                        </div>
                        <span class="font-medium text-sm">Performance</span>
                    </Link>
                    
                    <Link 
                        :href="route('growbiz.reports.summaries')"
                        class="flex items-center gap-3 px-3 py-2 rounded-xl text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors"
                        @click="closeMoreMenu"
                    >
                        <div class="w-9 h-9 rounded-full bg-amber-50 flex items-center justify-center">
                            <DocumentChartBarIcon class="h-5 w-5 text-amber-600" aria-hidden="true" />
                        </div>
                        <span class="font-medium text-sm">Summaries & Export</span>
                    </Link>
                    
                    <Link 
                        href="/apps"
                        class="flex items-center gap-3 px-3 py-2 rounded-xl text-gray-700 hover:bg-emerald-50 active:bg-emerald-100 transition-colors border border-gray-200"
                        @click="closeMoreMenu"
                    >
                        <div class="w-9 h-9 rounded-full bg-emerald-50 flex items-center justify-center">
                            <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                            </svg>
                        </div>
                        <span class="font-medium text-sm">All Apps</span>
                    </Link>
                    
                    <Link 
                        :href="route('growbiz.settings.index')"
                        class="flex items-center gap-3 px-3 py-2 rounded-xl text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors w-full"
                        @click="closeMoreMenu"
                    >
                        <div class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center">
                            <Cog6ToothIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                        </div>
                        <span class="font-medium text-sm">Settings</span>
                    </Link>
                    
                    <button 
                        @click="handleLogout"
                        class="flex items-center gap-3 px-3 py-2 rounded-xl text-red-600 hover:bg-red-50 active:bg-red-100 transition-colors w-full"
                    >
                        <div class="w-9 h-9 rounded-full bg-red-50 flex items-center justify-center">
                            <ArrowRightOnRectangleIcon class="h-5 w-5 text-red-500" aria-hidden="true" />
                        </div>
                        <span class="font-medium text-sm">Sign Out</span>
                    </button>
                </div>

                <!-- Cancel Button -->
                <div class="px-4 pb-4">
                    <button 
                        @click="closeMoreMenu"
                        class="w-full py-3.5 rounded-xl bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200 active:bg-gray-300 transition-colors"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </Transition>

        <!-- Profile Menu Bottom Sheet -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div 
                v-if="profileMenuOpen" 
                class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm"
                @click="closeProfileMenu"
            />
        </Transition>

        <Transition
            enter-active-class="transition-transform duration-300 ease-out"
            enter-from-class="translate-y-full"
            enter-to-class="translate-y-0"
            leave-active-class="transition-transform duration-200 ease-in"
            leave-from-class="translate-y-0"
            leave-to-class="translate-y-full"
        >
            <div 
                v-if="profileMenuOpen"
                class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md z-50 bg-white rounded-t-3xl shadow-2xl safe-area-bottom profile-menu-container"
            >
                <!-- Handle bar -->
                <div class="flex justify-center pt-3 pb-2">
                    <div class="w-10 h-1 bg-gray-300 rounded-full"></div>
                </div>
                
                <!-- User Profile Card -->
                <div class="px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center gap-4">
                        <div class="h-16 w-16 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center shadow-lg shadow-emerald-200">
                            <span class="text-xl font-bold text-white">{{ getInitials(user?.name || '') }}</span>
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-lg text-gray-900">{{ user?.name }}</p>
                            <p class="text-sm text-gray-500">{{ user?.email }}</p>
                            <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                {{ isEmployee ? 'Employee' : 'Business Owner' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Profile Menu Items -->
                <div class="px-4 pt-2 pb-3">
                    <!-- GrowBiz Settings (includes profile access) -->
                    <Link 
                        :href="route('growbiz.settings.index')"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors"
                        @click="closeProfileMenu"
                    >
                        <div class="w-9 h-9 rounded-full bg-blue-50 flex items-center justify-center">
                            <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        </div>
                        <div>
                            <span class="font-medium text-sm">My Profile & Settings</span>
                            <p class="text-xs text-gray-500">Account and app preferences</p>
                        </div>
                    </Link>
                    
                    <!-- All Apps - opens in new tab if in PWA mode -->
                    <a 
                        v-if="isStandalone"
                        href="/apps"
                        target="_blank"
                        rel="noopener"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 hover:bg-emerald-50 active:bg-emerald-100 transition-colors border border-gray-200"
                        @click="closeProfileMenu"
                    >
                        <div class="w-9 h-9 rounded-full bg-emerald-50 flex items-center justify-center">
                            <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                            </svg>
                        </div>
                        <div>
                            <span class="font-medium text-sm">All Apps</span>
                            <p class="text-xs text-gray-500">Opens in browser</p>
                        </div>
                    </a>
                    <Link 
                        v-else
                        href="/apps"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 hover:bg-emerald-50 active:bg-emerald-100 transition-colors border border-gray-200"
                        @click="closeProfileMenu"
                    >
                        <div class="w-9 h-9 rounded-full bg-emerald-50 flex items-center justify-center">
                            <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                            </svg>
                        </div>
                        <div>
                            <span class="font-medium text-sm">All Apps</span>
                            <p class="text-xs text-gray-500">Switch to other apps</p>
                        </div>
                    </Link>
                    
                    <button 
                        @click="handleLogout"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-red-600 hover:bg-red-50 active:bg-red-100 transition-colors w-full"
                    >
                        <div class="w-9 h-9 rounded-full bg-red-50 flex items-center justify-center">
                            <ArrowRightOnRectangleIcon class="h-5 w-5 text-red-500" />
                        </div>
                        <div class="text-left">
                            <span class="font-medium text-sm">Sign Out</span>
                            <p class="text-xs text-red-400">Log out of your account</p>
                        </div>
                    </button>
                </div>

                <!-- Cancel Button -->
                <div class="px-4 pb-4">
                    <button 
                        @click="closeProfileMenu"
                        class="w-full py-3.5 rounded-xl bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200 active:bg-gray-300 transition-colors"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </Transition>

        <!-- Bottom Navigation - App Style -->
        <nav class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-gray-100 z-40 safe-area-bottom">
            <div class="flex items-center justify-around h-16 max-w-md mx-auto px-2">
                <Link 
                    v-for="item in navigation" 
                    :key="item.name"
                    :href="route(item.href)"
                    :class="[
                        'relative flex flex-col items-center justify-center flex-1 h-full transition-all touch-manipulation group',
                        isActive(item.href) 
                            ? 'text-emerald-600' 
                            : 'text-gray-400 active:text-gray-600'
                    ]"
                >
                    <!-- Active background pill -->
                    <div 
                        v-if="isActive(item.href)"
                        class="absolute inset-x-2 top-1.5 h-8 bg-emerald-50 rounded-full"
                    />
                    <component 
                        :is="isActive(item.href) ? item.iconSolid : item.icon" 
                        :class="[
                            'relative h-6 w-6 transition-transform',
                            isActive(item.href) ? 'scale-105' : 'group-active:scale-95'
                        ]" 
                        aria-hidden="true" 
                    />
                    <span :class="[
                        'relative text-[10px] mt-0.5 font-semibold tracking-wide',
                        isActive(item.href) ? 'text-emerald-600' : 'text-gray-500'
                    ]">
                        {{ item.name }}
                    </span>
                </Link>

                <!-- More Button -->
                <button 
                    @click.stop="moreMenuOpen = !moreMenuOpen"
                    :class="[
                        'more-menu-container relative flex flex-col items-center justify-center flex-1 h-full transition-all touch-manipulation group',
                        moreMenuOpen ? 'text-emerald-600' : 'text-gray-400 active:text-gray-600'
                    ]"
                >
                    <EllipsisHorizontalIcon 
                        :class="[
                            'h-6 w-6 transition-transform',
                            moreMenuOpen ? 'scale-105' : 'group-active:scale-95'
                        ]" 
                        aria-hidden="true" 
                    />
                    <span :class="[
                        'text-[10px] mt-0.5 font-semibold tracking-wide',
                        moreMenuOpen ? 'text-emerald-600' : 'text-gray-500'
                    ]">
                        More
                    </span>
                </button>
            </div>
        </nav>
    </div>
</template>

<style scoped>
.safe-area-top {
    padding-top: env(safe-area-inset-top);
}
.safe-area-bottom {
    padding-bottom: env(safe-area-inset-bottom);
}
.safe-area-left {
    padding-left: env(safe-area-inset-left);
}
.touch-manipulation {
    touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
}
</style>
