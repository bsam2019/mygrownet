<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import CommandPalette from '@/Components/BizBoost/CommandPalette.vue';
import ToastContainer from '@/Components/BizBoost/ToastContainer.vue';
import NotificationDropdown from '@/Components/BizBoost/NotificationDropdown.vue';
import ThemeToggle from '@/Components/BizBoost/ThemeToggle.vue';
import OnboardingTour from '@/Components/BizBoost/OnboardingTour.vue';
// Mobile components
import { BottomNavigation, MoreMenu, InstallPrompt } from '@/Components/BizBoost/Mobile';
import { useMobileDetect } from '@/composables/useMobileDetect';
import { usePWA } from '@/composables/usePWA';
import {
    HomeIcon,
    ShoppingBagIcon,
    UsersIcon,
    DocumentTextIcon,
    PhotoIcon,
    SparklesIcon,
    ChartBarIcon,
    CurrencyDollarIcon,
    Cog6ToothIcon,
    ArrowUpCircleIcon,
    Bars3Icon,
    XMarkIcon,
    BellIcon,
    BuildingStorefrontIcon,
    QrCodeIcon,
    CalendarIcon,
    LinkIcon,
    RectangleStackIcon,
    RocketLaunchIcon,
    ChatBubbleLeftRightIcon,
    LightBulbIcon,
    ClockIcon,
    ChevronLeftIcon,
    ChevronDownIcon,
    AcademicCapIcon,
    UserGroupIcon,
    MapPinIcon,
    CodeBracketIcon,
    ShoppingCartIcon,
    PaintBrushIcon,
    MagnifyingGlassIcon,
    CommandLineIcon,
} from '@heroicons/vue/24/outline';

interface Props {
    title?: string;
}

defineProps<Props>();

const page = usePage();
const user = computed(() => page.props.auth?.user);
const business = computed(() => page.props.business);
const subscriptionTier = computed(() => page.props.subscriptionTier || 'free');

const sidebarOpen = ref(false);
const sidebarCollapsed = ref(false);
const toolsExpanded = ref(false);
const advancedExpanded = ref(false);

// Mobile detection
const { isMobile, isMobileOrTablet } = useMobileDetect();
const { isOnline } = usePWA();

// More menu state for mobile
const moreMenuOpen = ref(false);

// Check if any business nav item is active to auto-expand
const isBusinessNavActive = computed(() => 
    route().current('bizboost.products.*') ||
    route().current('bizboost.customers.*') ||
    route().current('bizboost.sales.*') ||
    route().current('bizboost.posts.*') ||
    route().current('bizboost.calendar.*') ||
    route().current('bizboost.analytics.*')
);

// Auto-expand business section if active
const businessExpanded = ref(isBusinessNavActive.value);

// Check if any tools nav item is active
const isToolsNavActive = computed(() =>
    route().current('bizboost.ai.*') ||
    route().current('bizboost.templates.*') ||
    route().current('bizboost.campaigns.*') ||
    route().current('bizboost.whatsapp.*') ||
    route().current('bizboost.advisor.*') ||
    route().current('bizboost.reminders.*')
);

// Auto-expand tools if active
if (isToolsNavActive.value) {
    toolsExpanded.value = true;
}

// Check if any advanced nav item is active
const isAdvancedNavActive = computed(() =>
    route().current('bizboost.business.*') ||
    route().current('bizboost.integrations.*') ||
    route().current('bizboost.industry-kits.*') ||
    route().current('bizboost.learning.*') ||
    route().current('bizboost.team.*') ||
    route().current('bizboost.messages.*') ||
    route().current('bizboost.locations.*') ||
    route().current('bizboost.api.*') ||
    route().current('bizboost.marketplace.*') ||
    route().current('bizboost.white-label.*')
);

// Auto-expand advanced if active
if (isAdvancedNavActive.value) {
    advancedExpanded.value = true;
}

// Dashboard only (always visible)
const dashboardNav = { name: 'Dashboard', href: '/bizboost', icon: HomeIcon, current: route().current('bizboost.dashboard'), tourId: 'dashboard' };

// Business operations (collapsible)
const businessNavigation = [
    { name: 'Products', href: '/bizboost/products', icon: ShoppingBagIcon, current: route().current('bizboost.products.*'), tourId: 'products' },
    { name: 'Customers', href: '/bizboost/customers', icon: UsersIcon, current: route().current('bizboost.customers.*'), tourId: 'customers' },
    { name: 'Sales', href: '/bizboost/sales', icon: CurrencyDollarIcon, current: route().current('bizboost.sales.*'), tourId: 'sales' },
    { name: 'Posts', href: '/bizboost/posts', icon: DocumentTextIcon, current: route().current('bizboost.posts.*'), tourId: 'posts' },
    { name: 'Calendar', href: '/bizboost/calendar', icon: CalendarIcon, current: route().current('bizboost.calendar.*'), tourId: 'calendar' },
    { name: 'Analytics', href: '/bizboost/analytics', icon: ChartBarIcon, current: route().current('bizboost.analytics.*'), tourId: 'analytics' },
];

// Keep old navigation for mobile sidebar
const navigation = [
    dashboardNav,
    ...businessNavigation,
];

const toolsNavigation = [
    { name: 'AI Content', href: '/bizboost/ai', icon: SparklesIcon, current: route().current('bizboost.ai.*') },
    { name: 'Templates', href: '/bizboost/templates', icon: PhotoIcon, current: route().current('bizboost.templates.*') },
    { name: 'Campaigns', href: '/bizboost/campaigns', icon: RocketLaunchIcon, current: route().current('bizboost.campaigns.*') },
    { name: 'WhatsApp', href: '/bizboost/whatsapp/broadcasts', icon: ChatBubbleLeftRightIcon },
    { name: 'AI Advisor', href: '/bizboost/advisor', icon: LightBulbIcon },
    { name: 'Reminders', href: '/bizboost/reminders', icon: ClockIcon },
];

const advancedNavigation = [
    { name: 'Business Profile', href: '/bizboost/business/profile', icon: BuildingStorefrontIcon },
    { name: 'Mini-Website', href: '/bizboost/business/mini-website', icon: QrCodeIcon },
    { name: 'Integrations', href: '/bizboost/integrations', icon: LinkIcon },
    { name: 'Industry Kits', href: '/bizboost/industry-kits', icon: RectangleStackIcon },
    { name: 'Learning Hub', href: '/bizboost/learning', icon: AcademicCapIcon },
    { name: 'Team', href: '/bizboost/team', icon: UserGroupIcon },
    { name: 'Messages', href: '/bizboost/messages', icon: ChatBubbleLeftRightIcon },
    { name: 'Locations', href: '/bizboost/locations', icon: MapPinIcon },
    { name: 'API Access', href: '/bizboost/api', icon: CodeBracketIcon },
    { name: 'Marketplace', href: '/bizboost/marketplace', icon: ShoppingCartIcon },
    { name: 'White Label', href: '/bizboost/white-label', icon: PaintBrushIcon },
];

const tierColors: Record<string, string> = {
    free: 'bg-gray-100 text-gray-800',
    basic: 'bg-blue-100 text-blue-800',
    professional: 'bg-violet-100 text-violet-800',
    business: 'bg-amber-100 text-amber-800',
};

// Command palette trigger
const showCommandPaletteHint = ref(false);

// Function to open command palette
const openCommandPalette = () => {
    document.dispatchEvent(new KeyboardEvent('keydown', { key: 'k', metaKey: true, bubbles: true }));
};

// Notification state is now handled by NotificationDropdown component
</script>

<template>
    <div class="h-screen overflow-hidden bg-gradient-to-b from-slate-50 to-white dark:from-slate-900 dark:to-slate-950">
        <!-- Command Palette -->
        <CommandPalette />
        
        <!-- Toast Notifications -->
        <ToastContainer />
        
        <!-- Onboarding Tour -->
        <OnboardingTour @complete="console.log('Tour completed')" />
        <!-- Mobile sidebar -->
        <div v-if="sidebarOpen" class="relative z-50 lg:hidden">
            <div class="fixed inset-0 bg-gray-900/80" @click="sidebarOpen = false"></div>
            <div class="fixed inset-0 flex">
                <div class="relative mr-16 flex w-full max-w-xs flex-1">
                    <div class="absolute left-full top-0 flex w-16 justify-center pt-5">
                        <button type="button" class="-m-2.5 p-2.5" @click="sidebarOpen = false">
                            <span class="sr-only">Close sidebar</span>
                            <XMarkIcon class="h-6 w-6 text-white" aria-hidden="true" />
                        </button>
                    </div>
                    <div class="flex grow flex-col gap-y-5 overflow-y-auto mobile-sidebar-scroll bg-white px-6 pb-4">
                        <div class="flex h-16 shrink-0 items-center">
                            <Link href="/bizboost" class="flex items-center gap-2">
                                <div class="h-8 w-8 rounded-lg bg-violet-600 flex items-center justify-center">
                                    <SparklesIcon class="h-5 w-5 text-white" />
                                </div>
                                <span class="text-xl font-bold text-gray-900">BizBoost</span>
                            </Link>
                        </div>
                        <nav class="flex flex-1 flex-col">
                            <ul role="list" class="flex flex-1 flex-col gap-y-7">
                                <li>
                                    <ul role="list" class="-mx-2 space-y-1">
                                        <li v-for="item in navigation" :key="item.name">
                                            <Link
                                                :href="item.href"
                                                :class="[
                                                    item.current
                                                        ? 'bg-violet-50 text-violet-600'
                                                        : 'text-gray-700 hover:text-violet-600 hover:bg-gray-50',
                                                    'group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold',
                                                ]"
                                            >
                                                <component :is="item.icon" class="h-6 w-6 shrink-0" aria-hidden="true" />
                                                {{ item.name }}
                                            </Link>
                                        </li>
                                    </ul>
                                </li>
                                <li>
                                    <div class="text-xs font-semibold leading-6 text-gray-400">Settings</div>
                                    <ul role="list" class="-mx-2 mt-2 space-y-1">
                                        <li>
                                            <Link
                                                href="/bizboost/business/settings"
                                                class="text-gray-700 hover:text-violet-600 hover:bg-gray-50 group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold"
                                            >
                                                <Cog6ToothIcon class="h-6 w-6 shrink-0 text-gray-400 group-hover:text-violet-600" aria-hidden="true" />
                                                Settings
                                            </Link>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Static sidebar for desktop -->
        <div 
            :class="[
                'hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:flex-col transition-all duration-300',
                sidebarCollapsed ? 'lg:w-20' : 'lg:w-72'
            ]"
        >
            <div class="flex grow flex-col gap-y-5 overflow-y-auto sidebar-scroll border-r border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 pb-4 shadow-sm">
                <!-- Header with collapse toggle -->
                <div :class="['flex h-16 shrink-0 items-center border-b border-slate-200 dark:border-slate-700', sidebarCollapsed ? 'justify-center px-4' : 'justify-between px-6']">
                    <Link href="/bizboost" :class="['flex items-center gap-3', sidebarCollapsed && 'justify-center']">
                        <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-violet-600 via-violet-600 to-pink-500 flex items-center justify-center shadow-lg shadow-violet-500/30 ring-2 ring-white">
                            <SparklesIcon class="h-5 w-5 text-white" />
                        </div>
                        <div v-if="!sidebarCollapsed">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">BizBoost</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Marketing Console</p>
                        </div>
                    </Link>
                    <button 
                        v-if="!sidebarCollapsed"
                        @click="sidebarCollapsed = true"
                        class="p-1.5 rounded-lg hover:bg-slate-200 text-slate-400 hover:text-slate-600 transition-colors"
                        aria-label="Collapse sidebar"
                    >
                        <ChevronLeftIcon class="h-5 w-5" aria-hidden="true" />
                    </button>
                    <button 
                        v-else
                        @click="sidebarCollapsed = false"
                        class="absolute -right-3 top-6 p-1 rounded-full bg-white border border-slate-200 shadow-md hover:shadow-lg text-slate-400 hover:text-violet-600 transition-all"
                        aria-label="Expand sidebar"
                    >
                        <ChevronLeftIcon class="h-4 w-4 rotate-180" aria-hidden="true" />
                    </button>
                </div>

                <nav :class="['flex flex-1 flex-col', sidebarCollapsed ? 'px-3' : 'px-4']">
                    <ul role="list" class="flex flex-1 flex-col gap-y-4">
                        <!-- Dashboard (always visible) -->
                        <li>
                            <Link
                                :href="dashboardNav.href"
                                :data-tour="dashboardNav.tourId"
                                :class="[
                                    dashboardNav.current
                                        ? 'bg-gradient-to-r from-violet-50 to-violet-100 text-violet-700 shadow-sm border border-violet-200 dark:from-violet-900/30 dark:to-violet-800/30 dark:text-violet-300 dark:border-violet-700'
                                        : 'text-slate-700 hover:text-violet-600 hover:bg-slate-100 border border-transparent dark:text-slate-300 dark:hover:text-violet-400 dark:hover:bg-slate-800',
                                    'group flex items-center gap-x-3 rounded-xl p-2.5 text-sm font-semibold transition-all',
                                    sidebarCollapsed && 'justify-center'
                                ]"
                                :title="sidebarCollapsed ? dashboardNav.name : ''"
                            >
                                <component :is="dashboardNav.icon" class="h-5 w-5 shrink-0" aria-hidden="true" />
                                <span v-if="!sidebarCollapsed">{{ dashboardNav.name }}</span>
                            </Link>
                        </li>

                        <!-- Business Operations (collapsible) -->
                        <li>
                            <div v-if="!sidebarCollapsed">
                                <button
                                    @click="businessExpanded = !businessExpanded"
                                    :class="[
                                        'w-full flex items-center justify-between text-xs font-bold uppercase tracking-wider px-2 py-2 rounded-lg transition-colors',
                                        isBusinessNavActive ? 'text-violet-600 bg-violet-50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-100'
                                    ]"
                                >
                                    <span>Business</span>
                                    <ChevronDownIcon 
                                        :class="['h-4 w-4 transition-transform', businessExpanded && 'rotate-180']" 
                                        aria-hidden="true" 
                                    />
                                </button>
                                <ul v-show="businessExpanded" role="list" class="mt-2 space-y-1">
                                    <li v-for="item in businessNavigation" :key="item.name">
                                        <Link
                                            :href="item.href"
                                            :data-tour="item.tourId"
                                            :class="[
                                                item.current
                                                    ? 'bg-gradient-to-r from-violet-50 to-violet-100 text-violet-700 border border-violet-200'
                                                    : 'text-slate-600 hover:text-violet-600 hover:bg-slate-100 border border-transparent',
                                                'group flex gap-x-3 rounded-xl p-2 text-sm font-medium transition-all'
                                            ]"
                                        >
                                            <component :is="item.icon" class="h-5 w-5 shrink-0" aria-hidden="true" />
                                            {{ item.name }}
                                        </Link>
                                    </li>
                                </ul>
                            </div>
                            <div v-else class="space-y-1">
                                <ul role="list" class="space-y-1">
                                    <li v-for="item in businessNavigation" :key="item.name">
                                        <Link
                                            :href="item.href"
                                            :class="[
                                                item.current
                                                    ? 'bg-violet-50 text-violet-600 border border-violet-200'
                                                    : 'text-slate-600 hover:text-violet-600 hover:bg-slate-100 border border-transparent',
                                                'group flex justify-center rounded-xl p-2.5 transition-all'
                                            ]"
                                            :title="item.name"
                                        >
                                            <component :is="item.icon" class="h-5 w-5 shrink-0" aria-hidden="true" />
                                        </Link>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        <!-- Tools Section -->
                        <li>
                            <div v-if="!sidebarCollapsed">
                                <button
                                    @click="toolsExpanded = !toolsExpanded"
                                    :class="[
                                        'w-full flex items-center justify-between text-xs font-bold uppercase tracking-wider px-2 py-2 rounded-lg transition-colors',
                                        isToolsNavActive ? 'text-violet-600 bg-violet-50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-100'
                                    ]"
                                >
                                    <span>Tools</span>
                                    <ChevronDownIcon 
                                        :class="['h-4 w-4 transition-transform', toolsExpanded && 'rotate-180']" 
                                        aria-hidden="true" 
                                    />
                                </button>
                                <ul v-show="toolsExpanded" role="list" class="mt-2 space-y-1">
                                    <li v-for="item in toolsNavigation" :key="item.name">
                                        <Link
                                            :href="item.href"
                                            :class="[
                                                item.current
                                                    ? 'bg-gradient-to-r from-violet-50 to-violet-100 text-violet-700 border border-violet-200'
                                                    : 'text-slate-600 hover:text-violet-600 hover:bg-slate-100 border border-transparent',
                                                'group flex gap-x-3 rounded-xl p-2 text-sm font-medium transition-all'
                                            ]"
                                        >
                                            <component :is="item.icon" class="h-5 w-5 shrink-0" aria-hidden="true" />
                                            {{ item.name }}
                                        </Link>
                                    </li>
                                </ul>
                            </div>
                            <div v-else class="border-t border-slate-200 pt-4">
                                <ul role="list" class="space-y-1">
                                    <li v-for="item in toolsNavigation" :key="item.name">
                                        <Link
                                            :href="item.href"
                                            :class="[
                                                item.current
                                                    ? 'bg-violet-50 text-violet-600 border border-violet-200'
                                                    : 'text-slate-600 hover:text-violet-600 hover:bg-slate-100 border border-transparent',
                                                'group flex justify-center rounded-xl p-2.5 transition-all'
                                            ]"
                                            :title="item.name"
                                        >
                                            <component :is="item.icon" class="h-5 w-5 shrink-0" aria-hidden="true" />
                                        </Link>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        <!-- Advanced Section -->
                        <li>
                            <div v-if="!sidebarCollapsed">
                                <button
                                    @click="advancedExpanded = !advancedExpanded"
                                    :class="[
                                        'w-full flex items-center justify-between text-xs font-bold uppercase tracking-wider px-2 py-2 rounded-lg transition-colors',
                                        isAdvancedNavActive ? 'text-violet-600 bg-violet-50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-100'
                                    ]"
                                >
                                    <span>Advanced</span>
                                    <ChevronDownIcon 
                                        :class="['h-4 w-4 transition-transform', advancedExpanded && 'rotate-180']" 
                                        aria-hidden="true" 
                                    />
                                </button>
                                <ul v-show="advancedExpanded" role="list" class="mt-2 space-y-1">
                                    <li v-for="item in advancedNavigation" :key="item.name">
                                        <Link
                                            :href="item.href"
                                            :class="[
                                                route().current(item.href.replace('/bizboost/', 'bizboost.').replace('/', '.') + '*')
                                                    ? 'bg-gradient-to-r from-violet-50 to-violet-100 text-violet-700 border border-violet-200'
                                                    : 'text-slate-600 hover:text-violet-600 hover:bg-slate-100 border border-transparent',
                                                'group flex gap-x-3 rounded-xl p-2 text-sm font-medium transition-all'
                                            ]"
                                        >
                                            <component :is="item.icon" class="h-5 w-5 shrink-0" aria-hidden="true" />
                                            {{ item.name }}
                                        </Link>
                                    </li>
                                </ul>
                            </div>
                            <div v-else class="border-t border-slate-200 pt-4">
                                <ul role="list" class="space-y-1">
                                    <li v-for="item in advancedNavigation.slice(0, 4)" :key="item.name">
                                        <Link
                                            :href="item.href"
                                            class="text-slate-600 hover:text-violet-600 hover:bg-slate-100 border border-transparent group flex justify-center rounded-xl p-2.5 transition-all"
                                            :title="item.name"
                                        >
                                            <component :is="item.icon" class="h-5 w-5 shrink-0" aria-hidden="true" />
                                        </Link>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        <!-- All Apps & Settings & Upgrade -->
                        <li class="mt-auto space-y-2">
                            <!-- All Apps Link -->
                            <Link
                                v-if="!sidebarCollapsed"
                                href="/apps"
                                class="group flex gap-x-3 rounded-lg p-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 hover:text-violet-600 transition-all border border-gray-200 hover:border-violet-200"
                            >
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                </svg>
                                All Apps
                            </Link>
                            <Link
                                v-else
                                href="/apps"
                                class="group flex justify-center rounded-lg p-2.5 text-gray-700 hover:bg-gray-50 hover:text-violet-600 transition-all border border-gray-200 hover:border-violet-200"
                                title="All Apps"
                            >
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                </svg>
                            </Link>
                            
                            <Link
                                v-if="!sidebarCollapsed"
                                href="/bizboost/business/settings"
                                class="group flex gap-x-3 rounded-lg p-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 hover:text-violet-600 transition-all"
                            >
                                <Cog6ToothIcon class="h-5 w-5 shrink-0" aria-hidden="true" />
                                Settings
                            </Link>
                            <Link
                                v-else
                                href="/bizboost/business/settings"
                                class="group flex justify-center rounded-lg p-2.5 text-gray-700 hover:bg-gray-50 hover:text-violet-600 transition-all"
                                title="Settings"
                            >
                                <Cog6ToothIcon class="h-5 w-5 shrink-0" aria-hidden="true" />
                            </Link>
                            
                            <Link
                                href="/bizboost/upgrade"
                                :class="[
                                    'group flex items-center gap-x-3 rounded-lg p-2.5 text-sm font-semibold text-white bg-gradient-to-r from-violet-600 to-violet-700 hover:from-violet-700 hover:to-violet-800 shadow-lg shadow-violet-500/30 hover:shadow-violet-500/40 transition-all',
                                    sidebarCollapsed && 'justify-center'
                                ]"
                                :title="sidebarCollapsed ? 'Upgrade Plan' : ''"
                            >
                                <ArrowUpCircleIcon class="h-5 w-5 shrink-0" aria-hidden="true" />
                                <span v-if="!sidebarCollapsed" class="flex-1">Upgrade</span>
                                <span v-if="!sidebarCollapsed" :class="[tierColors[subscriptionTier], 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium capitalize']">
                                    {{ subscriptionTier }}
                                </span>
                            </Link>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <div :class="['transition-all duration-300 flex flex-col h-screen overflow-hidden', sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-72']">
            <!-- Top bar -->
            <div class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
                <button type="button" class="-m-2.5 p-2.5 text-gray-700 lg:hidden" @click="sidebarOpen = true">
                    <span class="sr-only">Open sidebar</span>
                    <Bars3Icon class="h-6 w-6" aria-hidden="true" />
                </button>

                <div class="h-6 w-px bg-gray-200 lg:hidden" aria-hidden="true"></div>

                <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                    <div class="flex flex-1 items-center gap-4">
                        <h1 v-if="title" class="text-lg font-semibold text-gray-900 dark:text-white">{{ title }}</h1>
                        
                        <!-- Search / Command Palette Trigger -->
                        <button
                            @click="openCommandPalette"
                            class="hidden lg:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors text-sm"
                            aria-label="Open search"
                        >
                            <MagnifyingGlassIcon class="h-4 w-4" aria-hidden="true" />
                            <span>Search...</span>
                            <kbd class="ml-2 px-1.5 py-0.5 bg-white dark:bg-slate-700 rounded border border-slate-200 dark:border-slate-600 text-xs font-mono text-slate-400">⌘K</kbd>
                        </button>
                    </div>
                    <div class="flex items-center gap-x-4 lg:gap-x-6">
                        <!-- Theme Toggle -->
                        <ThemeToggle />
                        
                        <!-- Notification Dropdown -->
                        <NotificationDropdown :business-id="business?.id" />

                        <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-gray-200 dark:bg-slate-700" aria-hidden="true"></div>

                        <div class="flex items-center gap-x-3">
                            <div v-if="business" class="hidden lg:flex lg:flex-col lg:items-end">
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ business.name }}</span>
                                <span class="text-xs text-gray-500 dark:text-slate-400 capitalize">{{ business.industry }}</span>
                            </div>
                            <div class="h-9 w-9 rounded-full bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center ring-2 ring-white shadow-md">
                                <span class="text-sm font-semibold text-white">{{ user?.name?.charAt(0) || 'U' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <main :class="['flex-1 overflow-y-auto main-scroll py-6 dark:bg-slate-950', isMobileOrTablet && 'pb-24']">
                <div class="px-4 sm:px-6 lg:px-8">
                    <slot />
                </div>
            </main>
        </div>

        <!-- Mobile Bottom Navigation -->
        <BottomNavigation
            v-if="isMobileOrTablet"
            :more-menu-open="moreMenuOpen"
            @open-more="moreMenuOpen = true"
        />

        <!-- Mobile More Menu -->
        <MoreMenu v-model="moreMenuOpen" />

        <!-- PWA Install Prompt -->
        <InstallPrompt v-if="isMobileOrTablet" />

        <!-- Offline Indicator -->
        <Transition name="slide-down">
            <div
                v-if="!isOnline"
                class="fixed top-0 left-0 right-0 z-[100] bg-amber-500 text-white text-center py-2 text-sm font-medium lg:hidden"
            >
                You're offline. Some features may be limited.
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.slide-down-enter-active,
.slide-down-leave-active {
    transition: transform 0.3s ease;
}

.slide-down-enter-from,
.slide-down-leave-to {
    transform: translateY(-100%);
}

/* Custom Scrollbar Styles */
.sidebar-scroll {
    scrollbar-width: thin;
    scrollbar-color: rgba(139, 92, 246, 0.3) transparent;
    scroll-behavior: smooth;
    overscroll-behavior: contain;
}

.sidebar-scroll::-webkit-scrollbar {
    width: 5px;
}

.sidebar-scroll::-webkit-scrollbar-track {
    background: transparent;
    margin: 12px 0;
}

.sidebar-scroll::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, rgba(139, 92, 246, 0.35) 0%, rgba(168, 85, 247, 0.35) 100%);
    border-radius: 10px;
    transition: all 0.2s ease;
}

.sidebar-scroll::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(180deg, rgba(139, 92, 246, 0.6) 0%, rgba(168, 85, 247, 0.6) 100%);
    width: 6px;
}

/* Main content scrollbar */
.main-scroll {
    scrollbar-width: thin;
    scrollbar-color: rgba(148, 163, 184, 0.3) transparent;
    scroll-behavior: smooth;
    overscroll-behavior: contain;
}

.main-scroll::-webkit-scrollbar {
    width: 10px;
    height: 10px;
}

.main-scroll::-webkit-scrollbar-track {
    background: transparent;
    border-radius: 5px;
    margin: 4px;
}

.main-scroll::-webkit-scrollbar-thumb {
    background: rgba(148, 163, 184, 0.35);
    border-radius: 10px;
    border: 3px solid transparent;
    background-clip: padding-box;
    transition: all 0.2s ease;
}

.main-scroll::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(180deg, rgba(139, 92, 246, 0.5) 0%, rgba(168, 85, 247, 0.5) 100%);
    border: 2px solid transparent;
    background-clip: padding-box;
}

.main-scroll::-webkit-scrollbar-thumb:active {
    background: linear-gradient(180deg, rgba(139, 92, 246, 0.7) 0%, rgba(168, 85, 247, 0.7) 100%);
    background-clip: padding-box;
}

.main-scroll::-webkit-scrollbar-corner {
    background: transparent;
}

/* Dark mode scrollbar adjustments */
.dark .sidebar-scroll {
    scrollbar-color: rgba(139, 92, 246, 0.35) transparent;
}

.dark .sidebar-scroll::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, rgba(139, 92, 246, 0.4) 0%, rgba(168, 85, 247, 0.4) 100%);
}

.dark .sidebar-scroll::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(180deg, rgba(139, 92, 246, 0.6) 0%, rgba(168, 85, 247, 0.6) 100%);
}

.dark .main-scroll {
    scrollbar-color: rgba(100, 116, 139, 0.3) transparent;
}

.dark .main-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.dark .main-scroll::-webkit-scrollbar-thumb {
    background: rgba(100, 116, 139, 0.4);
}

.dark .main-scroll::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(180deg, rgba(139, 92, 246, 0.5) 0%, rgba(168, 85, 247, 0.5) 100%);
}

.dark .main-scroll::-webkit-scrollbar-thumb:active {
    background: linear-gradient(180deg, rgba(139, 92, 246, 0.7) 0%, rgba(168, 85, 247, 0.7) 100%);
}

/* Mobile sidebar scrollbar */
.mobile-sidebar-scroll {
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.mobile-sidebar-scroll::-webkit-scrollbar {
    display: none;
}
</style>
