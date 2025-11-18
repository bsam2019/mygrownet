<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AppLogo from './AppLogo.vue';
import CustomNavUser from './CustomNavUser.vue';
import { type NavItem } from '@/types';
import { 
    HomeIcon, 
    BriefcaseIcon, 
    UsersIcon, 
    BanknoteIcon, 
    BookOpenIcon, 
    ChartBarIcon, 
    CogIcon,
    FolderIcon,
    ShieldIcon,
    GiftIcon,
    TrendingUpIcon,
    StarIcon,
    HistoryIcon,
    GraduationCapIcon,
    UserIcon,
    LockKeyhole as LockKeyholeIcon,
    FileText as FileTextIcon,
    Palette as PaletteIcon,
    ShoppingBagIcon,
    ArrowRightLeftIcon,
    ChevronDownIcon,
    Mail as MailIcon
} from 'lucide-vue-next';

interface Props {
    footerNavItems?: NavItem[];
}

const props = withDefaults(defineProps<Props>(), {
    footerNavItems: () => []
});

const page = usePage();
const isCollapsed = ref(false);
const isMobile = ref(false);
const showSubmenu = ref<Record<string, boolean>>({});

// Tooltip
const showTooltip = ref(false);
const tooltipText = ref('');
const tooltipPosition = { x: 0, y: 0 };

// Check if user has admin role
const isAdmin = computed(() => {
    return page.props.auth?.user?.roles?.some((role: any) => role.name === 'admin') || false;
});

// Check account type
const accountType = computed(() => {
    return page.props.auth?.user?.account_type || 'member';
});

const isInvestor = computed(() => accountType.value === 'investor');
const isMember = computed(() => accountType.value === 'member');

// Menu structure - Investor-only items (minimal)
const investorNavItems: NavItem[] = [
    { title: 'Venture Marketplace', href: route('ventures.index'), icon: BriefcaseIcon },
    { title: 'My Investments', href: route('mygrownet.ventures.my-investments'), icon: TrendingUpIcon },
    { title: 'My Wallet', href: route('mygrownet.wallet.index'), icon: BanknoteIcon },
];

// Full member items
const myBusinessNavItems: NavItem[] = [
    { title: 'My Business Profile', href: route('mygrownet.membership.show'), icon: BriefcaseIcon },
    { title: 'Venture Marketplace', href: route('ventures.index'), icon: BriefcaseIcon },
    { title: 'Business Growth Fund', href: route('mygrownet.bgf.index'), icon: BriefcaseIcon },
    { title: 'MyGrow Shop', href: route('shop.index'), icon: ShoppingBagIcon },
    { title: 'My Starter Kit', href: route('mygrownet.starter-kit.show'), icon: GiftIcon },
    { title: 'Growth Levels', href: route('mygrownet.levels.index'), icon: TrendingUpIcon },
    { title: 'My Points (LP & BP)', href: route('points.index'), icon: ChartBarIcon },
];

// Quick access to information pages
const infoNavItems: NavItem[] = [
    { title: 'Platform Features', href: route('features'), icon: StarIcon },
    { title: 'About BGF', href: route('bgf.about'), icon: TrendingUpIcon },
    { title: 'About Ventures', href: route('ventures.about'), icon: BriefcaseIcon },
    { title: 'About LGR (Loyalty Reward)', href: route('loyalty-reward.policy'), icon: StarIcon },
    { title: 'FAQ & Help', href: route('faq'), icon: BookOpenIcon },
];

const networkNavItems: NavItem[] = [
    { title: 'My Team', href: route('my-team.index'), icon: UsersIcon },
    { title: 'Matrix Structure', href: route('matrix.index'), icon: FolderIcon },
    { title: 'Commission Earnings', href: route('my-team.commissions'), icon: TrendingUpIcon },
];

const financeNavItems: NavItem[] = [
    { title: 'My Wallet', href: route('mygrownet.wallet.index'), icon: BanknoteIcon },
    { title: 'My Earnings', href: route('mygrownet.earnings.hub'), icon: TrendingUpIcon },
    { title: 'My Receipts', href: route('receipts.index'), icon: FileTextIcon },
    { title: 'Withdrawals', href: route('withdrawals.index'), icon: ArrowRightLeftIcon },
    { title: 'Transaction History', href: route('transactions'), icon: HistoryIcon },
];

const reportsNavItems: NavItem[] = [
    { title: 'Commission Earnings', href: route('my-team.commissions'), icon: TrendingUpIcon },
    { title: 'Quarterly Profit Shares', href: route('mygrownet.profit-shares'), icon: TrendingUpIcon },
];

const learningNavItems: NavItem[] = [
    { title: 'Compensation Plan', href: '/compensation-plan', icon: StarIcon },
    { title: 'Resource Library', href: route('mygrownet.library.index'), icon: BookOpenIcon },
    { title: 'Workshops & Training', href: route('mygrownet.workshops.index'), icon: BookOpenIcon },
    { title: 'My Workshops', href: route('mygrownet.workshops.my-workshops'), icon: GraduationCapIcon },
];

const communicationNavItems: NavItem[] = [
    { title: 'Messages', href: route('mygrownet.messages.index'), icon: 'MailIcon' },
    { title: 'Support', href: route('mygrownet.support.index'), icon: 'LifeBuoyIcon' },
];

const accountNavItems: NavItem[] = [
    { title: 'Profile', href: route('profile.edit'), icon: UserIcon },
    { title: 'Password', href: route('password.edit'), icon: LockKeyholeIcon },
    { title: 'Appearance', href: route('appearance'), icon: PaletteIcon },
];

const adminNavItems: NavItem[] = [
    { title: 'Admin Dashboard', href: route('admin.dashboard'), icon: ShieldIcon },
    { title: 'Manage Members', href: route('admin.users.index'), icon: UsersIcon },
    { title: 'Subscription Requests', href: route('admin.investments.index'), icon: ChartBarIcon },
    { title: 'Withdrawal Approvals', href: route('admin.withdrawals.index'), icon: BanknoteIcon },
];

const emit = defineEmits<{
    (e: 'update:collapsed', value: boolean): void;
}>();

const toggleSidebar = () => {
    isCollapsed.value = !isCollapsed.value;
    localStorage.setItem('mygrownet.sidebarCollapsed', isCollapsed.value.toString());
    emit('update:collapsed', isCollapsed.value);
};

const toggleSubmenu = (key: string) => {
    showSubmenu.value[key] = !showSubmenu.value[key];
    localStorage.setItem('mygrownet.sidebarSubmenus', JSON.stringify(showSubmenu.value));
};

const checkMobile = () => {
    const wasMobile = isMobile.value;
    isMobile.value = window.innerWidth < 1024;
    
    // On mobile, sidebar should be collapsed by default
    if (isMobile.value && !wasMobile) {
        isCollapsed.value = true;
    }
    // On desktop, restore saved state
    else if (!isMobile.value && wasMobile) {
        const savedState = localStorage.getItem('mygrownet.sidebarCollapsed');
        isCollapsed.value = savedState === 'true';
    }
};

const isUrlActive = (urlPattern: string | string[]) => {
    const currentUrl = page.url;
    if (Array.isArray(urlPattern)) {
        return urlPattern.some(pattern => currentUrl === pattern || currentUrl.startsWith(pattern + '/'));
    }
    return currentUrl === urlPattern || currentUrl.startsWith(urlPattern + '/');
};

const showItemTooltip = (event: MouseEvent, text: string) => {
    if (!isCollapsed.value || isMobile.value) return;
    tooltipText.value = text;
    const rect = (event.target as HTMLElement).getBoundingClientRect();
    tooltipPosition.x = rect.right + 10;
    tooltipPosition.y = rect.top + (rect.height / 2);
    showTooltip.value = true;
};

const hideTooltip = () => {
    showTooltip.value = false;
};

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
    
    // On desktop, load saved state
    if (!isMobile.value) {
        const savedState = localStorage.getItem('mygrownet.sidebarCollapsed');
        if (savedState !== null) {
            isCollapsed.value = savedState === 'true';
        }
    } else {
        // On mobile, start collapsed
        isCollapsed.value = true;
    }
    
    const savedSubmenus = localStorage.getItem('mygrownet.sidebarSubmenus');
    if (savedSubmenus) {
        try {
            showSubmenu.value = JSON.parse(savedSubmenus);
        } catch (e) {
            console.warn('Failed to parse saved submenu states');
        }
    }
});
</script>

<template>
    <!-- Mobile: Sidebar overlays and can be hidden completely -->
    <!-- Desktop: Sidebar is always visible, can collapse to icons -->
    <aside :class="[
        'fixed inset-y-0 left-0 z-50 flex flex-col transition-all duration-300',
        'bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800',
        // Desktop behavior
        !isMobile && (isCollapsed ? 'w-16' : 'w-64'),
        // Mobile behavior - hide completely when collapsed
        isMobile && (isCollapsed ? '-translate-x-full w-64' : 'translate-x-0 w-64')
    ]">
        <!-- Sidebar Header -->
        <div class="flex h-16 min-h-[64px] items-center justify-between px-4 border-b border-gray-200 dark:border-gray-800">
            <div class="flex items-center justify-between w-full h-full">
                <Link
                    :href="route('mygrownet.dashboard')"
                    class="flex items-center h-full flex-shrink-0 focus:outline-none"
                    v-show="!isCollapsed"
                >
                    <AppLogo class="h-8 w-auto" />
                </Link>

                <button
                    @click="toggleSidebar"
                    :class="[
                        'flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none p-2',
                        isCollapsed ? 'mx-auto' : 'ml-auto'
                    ]"
                    :aria-label="isCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar'"
                >
                    <div class="w-6 h-6 relative">
                        <span :class="[
                            'absolute block w-5 h-0.5 transition-all duration-300 bg-gray-700 dark:bg-gray-300',
                            isCollapsed ? 'top-1 rotate-0' : 'top-3 rotate-45'
                        ]"></span>
                        <span :class="[
                            'absolute block w-5 h-0.5 transition-all duration-300 bg-gray-700 dark:bg-gray-300',
                            isCollapsed ? 'top-3 opacity-100' : 'opacity-0'
                        ]"></span>
                        <span :class="[
                            'absolute block w-5 h-0.5 transition-all duration-300 bg-gray-700 dark:bg-gray-300',
                            isCollapsed ? 'top-5 rotate-0' : 'top-3 -rotate-45'
                        ]"></span>
                    </div>
                </button>
            </div>
        </div>

        <!-- Custom Tooltip -->
        <Transition name="tooltip">
            <div v-if="showTooltip"
                class="fixed z-50 px-2 py-1 text-sm text-white bg-gray-900 rounded shadow-lg pointer-events-none transform -translate-y-1/2"
                :style="{ left: `${tooltipPosition.x}px`, top: `${tooltipPosition.y}px` }"
            >
                {{ tooltipText }}
            </div>
        </Transition>

        <!-- Navigation Links - Scrollable Area -->
        <div class="py-4 overflow-y-auto flex-grow">
            <nav>
                <!-- Dashboard -->
                <Link
                    :href="route('mygrownet.dashboard')"
                    :class="[
                        'flex items-center px-4 py-2 text-sm font-medium transition-colors duration-200 mb-1',
                        isUrlActive(['/dashboard', '/']) ? 'text-blue-600 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-600' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800',
                        isCollapsed && !isMobile ? 'justify-center' : ''
                    ]"
                    @mouseenter="isCollapsed && !isMobile ? showItemTooltip($event, 'Dashboard') : null"
                    @mouseleave="hideTooltip"
                >
                    <HomeIcon class="w-5 h-5" />
                    <span v-show="!isCollapsed || isMobile" class="ml-3">Dashboard</span>
                </Link>

                <!-- Investor Dashboard (Investor-only users) -->
                <div v-if="isInvestor" class="space-y-1 pt-2">
                    <Link v-for="item in investorNavItems" :key="item.title"
                        :href="item.href"
                        :class="[
                            'flex items-center px-4 py-2 transition-colors duration-200',
                            route().current(item.href) 
                                ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400' 
                                : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'
                        ]"
                        @mouseenter="showItemTooltip($event, item.title)"
                        @mouseleave="hideTooltip"
                    >
                        <component :is="item.icon" class="h-5 w-5" />
                        <span v-show="!isCollapsed || isMobile" class="ml-3">{{ item.title }}</span>
                    </Link>
                    
                    <!-- Upgrade to Member CTA -->
                    <div v-show="!isCollapsed" class="mx-4 mt-4 rounded-lg bg-blue-50 p-4 border border-blue-200">
                        <p class="text-sm font-semibold text-blue-900 mb-2">Unlock Full Benefits</p>
                        <p class="text-xs text-blue-700 mb-3">Upgrade to full membership for MLM, shop access, and more!</p>
                        <Link href="/membership/upgrade" class="block w-full rounded-md bg-blue-600 px-3 py-2 text-center text-xs font-semibold text-white hover:bg-blue-500">
                            Upgrade Now
                        </Link>
                    </div>
                </div>

                <!-- My Business Section (Members only) -->
                <div v-if="isMember" class="pt-2">
                    <button @click="isCollapsed ? toggleSidebar() : toggleSubmenu('myBusiness')"
                        :class="[
                            'w-full flex items-center justify-between px-4 py-2 transition-colors duration-200',
                            'hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none',
                            'text-gray-700 dark:text-gray-300'
                        ]"
                        @mouseenter="showItemTooltip($event, 'My Business')"
                        @mouseleave="hideTooltip"
                    >
                        <div class="flex items-center">
                            <BriefcaseIcon class="h-5 w-5" />
                            <span v-show="!isCollapsed || isMobile" class="ml-3">My Business</span>
                        </div>
                        <ChevronDownIcon v-show="!isCollapsed" class="h-5 w-5 transform transition-transform duration-200"
                            :class="{ 'rotate-180': showSubmenu.myBusiness }" />
                    </button>

                    <div v-if="showSubmenu.myBusiness && !isCollapsed" class="mt-2 pl-4 space-y-1">
                        <Link v-for="item in myBusinessNavItems" :key="item.title"
                            :href="item.href"
                            :class="[
                                'flex items-center px-4 py-2 transition-colors duration-200 text-sm',
                                'hover:bg-gray-100 dark:hover:bg-gray-800',
                                isUrlActive(item.href) ? 'text-blue-600 border-l-4 border-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-700 dark:text-gray-300'
                            ]"
                        >
                            <component :is="item.icon" class="h-4 w-4" />
                            <span class="ml-3">{{ item.title }}</span>
                        </Link>
                    </div>
                </div>

                <!-- Network & Team Section (Members only) -->
                <div v-if="isMember" class="pt-2">
                    <button @click="isCollapsed ? toggleSidebar() : toggleSubmenu('network')"
                        :class="[
                            'w-full flex items-center justify-between px-4 py-2 transition-colors duration-200',
                            'hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none',
                            'text-gray-700 dark:text-gray-300'
                        ]"
                        @mouseenter="showItemTooltip($event, 'Network & Team')"
                        @mouseleave="hideTooltip"
                    >
                        <div class="flex items-center">
                            <UsersIcon class="h-5 w-5" />
                            <span v-show="!isCollapsed || isMobile" class="ml-3">Network & Team</span>
                        </div>
                        <ChevronDownIcon v-show="!isCollapsed" class="h-5 w-5 transform transition-transform duration-200"
                            :class="{ 'rotate-180': showSubmenu.network }" />
                    </button>

                    <div v-if="showSubmenu.network && !isCollapsed" class="mt-2 pl-4 space-y-1">
                        <Link v-for="item in networkNavItems" :key="item.title"
                            :href="item.href"
                            :class="[
                                'flex items-center px-4 py-2 transition-colors duration-200 text-sm',
                                'hover:bg-gray-100 dark:hover:bg-gray-800',
                                isUrlActive(item.href) ? 'text-blue-600 border-l-4 border-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-700 dark:text-gray-300'
                            ]"
                        >
                            <component :is="item.icon" class="h-4 w-4" />
                            <span class="ml-3">{{ item.title }}</span>
                        </Link>
                    </div>
                </div>

                <!-- Finance Section (Members only - full finance features) -->
                <div v-if="isMember" class="pt-2">
                    <button @click="isCollapsed ? toggleSidebar() : toggleSubmenu('finance')"
                        :class="[
                            'w-full flex items-center justify-between px-4 py-2 transition-colors duration-200',
                            'hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none',
                            'text-gray-700 dark:text-gray-300'
                        ]"
                        @mouseenter="showItemTooltip($event, 'Finance')"
                        @mouseleave="hideTooltip"
                    >
                        <div class="flex items-center">
                            <BanknoteIcon class="h-5 w-5" />
                            <span v-show="!isCollapsed || isMobile" class="ml-3">Finance</span>
                        </div>
                        <ChevronDownIcon v-show="!isCollapsed" class="h-5 w-5 transform transition-transform duration-200"
                            :class="{ 'rotate-180': showSubmenu.finance }" />
                    </button>

                    <div v-if="showSubmenu.finance && !isCollapsed" class="mt-2 pl-4 space-y-1">
                        <Link v-for="item in financeNavItems" :key="item.title"
                            :href="item.href"
                            :class="[
                                'flex items-center px-4 py-2 transition-colors duration-200 text-sm',
                                'hover:bg-gray-100 dark:hover:bg-gray-800',
                                isUrlActive(item.href) ? 'text-blue-600 border-l-4 border-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-700 dark:text-gray-300'
                            ]"
                        >
                            <component :is="item.icon" class="h-4 w-4" />
                            <span class="ml-3">{{ item.title }}</span>
                        </Link>
                    </div>
                </div>

                <!-- Learning Section -->
                <div class="pt-2">
                    <button @click="isCollapsed ? toggleSidebar() : toggleSubmenu('learning')"
                        :class="[
                            'w-full flex items-center justify-between px-4 py-2 transition-colors duration-200',
                            'hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none',
                            'text-gray-700 dark:text-gray-300'
                        ]"
                        @mouseenter="showItemTooltip($event, 'Learning')"
                        @mouseleave="hideTooltip"
                    >
                        <div class="flex items-center">
                            <BookOpenIcon class="h-5 w-5" />
                            <span v-show="!isCollapsed || isMobile" class="ml-3">Learning</span>
                        </div>
                        <ChevronDownIcon v-show="!isCollapsed" class="h-5 w-5 transform transition-transform duration-200"
                            :class="{ 'rotate-180': showSubmenu.learning }" />
                    </button>

                    <div v-if="showSubmenu.learning && !isCollapsed" class="mt-2 pl-4 space-y-1">
                        <Link v-for="item in learningNavItems" :key="item.title"
                            :href="item.href"
                            :class="[
                                'flex items-center px-4 py-2 transition-colors duration-200 text-sm',
                                'hover:bg-gray-100 dark:hover:bg-gray-800',
                                isUrlActive(item.href) ? 'text-blue-600 border-l-4 border-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-700 dark:text-gray-300'
                            ]"
                        >
                            <component :is="item.icon" class="h-4 w-4" />
                            <span class="ml-3">{{ item.title }}</span>
                        </Link>
                    </div>
                </div>

                <!-- Reports Section -->
                <div class="pt-2">
                    <button @click="isCollapsed ? toggleSidebar() : toggleSubmenu('reports')"
                        :class="[
                            'w-full flex items-center justify-between px-4 py-2 transition-colors duration-200',
                            'hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none',
                            'text-gray-700 dark:text-gray-300'
                        ]"
                        @mouseenter="showItemTooltip($event, 'Reports & Analytics')"
                        @mouseleave="hideTooltip"
                    >
                        <div class="flex items-center">
                            <ChartBarIcon class="h-5 w-5" />
                            <span v-show="!isCollapsed || isMobile" class="ml-3">Reports</span>
                        </div>
                        <ChevronDownIcon v-show="!isCollapsed" class="h-5 w-5 transform transition-transform duration-200"
                            :class="{ 'rotate-180': showSubmenu.reports }" />
                    </button>

                    <div v-if="showSubmenu.reports && !isCollapsed" class="mt-2 pl-4 space-y-1">
                        <Link v-for="item in reportsNavItems" :key="item.title"
                            :href="item.href"
                            :class="[
                                'flex items-center px-4 py-2 transition-colors duration-200 text-sm',
                                'hover:bg-gray-100 dark:hover:bg-gray-800',
                                isUrlActive(item.href) ? 'text-blue-600 border-l-4 border-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-700 dark:text-gray-300'
                            ]"
                        >
                            <component :is="item.icon" class="h-4 w-4" />
                            <span class="ml-3">{{ item.title }}</span>
                        </Link>
                    </div>
                </div>

                <!-- Info & Help Section -->
                <div class="pt-2">
                    <button @click="isCollapsed ? toggleSidebar() : toggleSubmenu('info')"
                        :class="[
                            'w-full flex items-center justify-between px-4 py-2 transition-colors duration-200',
                            'hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none',
                            'text-gray-700 dark:text-gray-300'
                        ]"
                        @mouseenter="showItemTooltip($event, 'Info & Help')"
                        @mouseleave="hideTooltip"
                    >
                        <div class="flex items-center">
                            <FileTextIcon class="h-5 w-5" />
                            <span v-show="!isCollapsed || isMobile" class="ml-3">Info & Help</span>
                        </div>
                        <ChevronDownIcon v-show="!isCollapsed" class="h-5 w-5 transform transition-transform duration-200"
                            :class="{ 'rotate-180': showSubmenu.info }" />
                    </button>

                    <div v-if="showSubmenu.info && !isCollapsed" class="mt-2 pl-4 space-y-1">
                        <Link v-for="item in infoNavItems" :key="item.title"
                            :href="item.href"
                            :class="[
                                'flex items-center px-4 py-2 transition-colors duration-200 text-sm',
                                'hover:bg-gray-100 dark:hover:bg-gray-800',
                                isUrlActive(item.href) ? 'text-blue-600 border-l-4 border-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-700 dark:text-gray-300'
                            ]"
                        >
                            <component :is="item.icon" class="h-4 w-4" />
                            <span class="ml-3">{{ item.title }}</span>
                        </Link>
                    </div>
                </div>

                <!-- Communication Section -->
                <div class="pt-2">
                    <div class="space-y-1">
                        <Link v-for="item in communicationNavItems" :key="item.title"
                            :href="item.href"
                            :class="[
                                'flex items-center justify-between px-4 py-2 transition-colors duration-200',
                                'hover:bg-gray-100 dark:hover:bg-gray-800',
                                isUrlActive(item.href) ? 'text-blue-600 border-l-4 border-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-700 dark:text-gray-300'
                            ]"
                            @mouseenter="showItemTooltip($event, item.title)"
                            @mouseleave="hideTooltip"
                        >
                            <div class="flex items-center">
                                <MailIcon class="h-5 w-5" />
                                <span v-show="!isCollapsed || isMobile" class="ml-3">{{ item.title }}</span>
                            </div>
                            <span v-if="page.props.messagingData?.unread_count > 0 && (!isCollapsed || isMobile)"
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                            >
                                {{ page.props.messagingData.unread_count }}
                            </span>
                        </Link>
                    </div>
                </div>

                <!-- Account Section -->
                <div class="pt-2">
                    <button @click="isCollapsed ? toggleSidebar() : toggleSubmenu('account')"
                        :class="[
                            'w-full flex items-center justify-between px-4 py-2 transition-colors duration-200',
                            'hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none',
                            'text-gray-700 dark:text-gray-300'
                        ]"
                        @mouseenter="showItemTooltip($event, 'Account')"
                        @mouseleave="hideTooltip"
                    >
                        <div class="flex items-center">
                            <CogIcon class="h-5 w-5" />
                            <span v-show="!isCollapsed || isMobile" class="ml-3">Account</span>
                        </div>
                        <ChevronDownIcon v-show="!isCollapsed" class="h-5 w-5 transform transition-transform duration-200"
                            :class="{ 'rotate-180': showSubmenu.account }" />
                    </button>

                    <div v-if="showSubmenu.account && !isCollapsed" class="mt-2 pl-4 space-y-1">
                        <Link v-for="item in accountNavItems" :key="item.title"
                            :href="item.href"
                            :class="[
                                'flex items-center px-4 py-2 transition-colors duration-200 text-sm',
                                'hover:bg-gray-100 dark:hover:bg-gray-800',
                                isUrlActive(item.href) ? 'text-blue-600 border-l-4 border-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-700 dark:text-gray-300'
                            ]"
                        >
                            <component :is="item.icon" class="h-4 w-4" />
                            <span class="ml-3">{{ item.title }}</span>
                        </Link>
                    </div>
                </div>

                <!-- Admin Section (if admin) -->
                <template v-if="isAdmin">
                    <div class="pt-2">
                        <button @click="isCollapsed ? toggleSidebar() : toggleSubmenu('admin')"
                            :class="[
                                'w-full flex items-center justify-between px-4 py-2 transition-colors duration-200',
                                'hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none',
                                'text-gray-700 dark:text-gray-300'
                            ]"
                            @mouseenter="showItemTooltip($event, 'Administration')"
                            @mouseleave="hideTooltip"
                        >
                            <div class="flex items-center">
                                <ShieldIcon class="h-5 w-5" />
                                <span v-show="!isCollapsed || isMobile" class="ml-3">Admin</span>
                            </div>
                            <ChevronDownIcon v-show="!isCollapsed" class="h-5 w-5 transform transition-transform duration-200"
                                :class="{ 'rotate-180': showSubmenu.admin }" />
                        </button>

                        <div v-if="showSubmenu.admin && !isCollapsed" class="mt-2 pl-4 space-y-1">
                            <Link v-for="item in adminNavItems" :key="item.title"
                                :href="item.href"
                                :class="[
                                    'flex items-center px-4 py-2 transition-colors duration-200 text-sm',
                                    'hover:bg-gray-100 dark:hover:bg-gray-800',
                                    isUrlActive(item.href) ? 'text-blue-600 border-l-4 border-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-700 dark:text-gray-300'
                                ]"
                            >
                                <component :is="item.icon" class="h-4 w-4" />
                                <span class="ml-3">{{ item.title }}</span>
                            </Link>
                        </div>
                    </div>
                </template>
            </nav>
        </div>

        <!-- Footer with User -->
        <div class="border-t border-gray-200 dark:border-gray-800 py-2 mt-auto">
            <CustomNavUser />
        </div>
    </aside>

    <!-- Mobile sidebar overlay -->
    <div
        v-if="!isCollapsed && isMobile"
        @click="toggleSidebar"
        class="fixed inset-0 bg-black bg-opacity-50 z-30"
    ></div>

    <!-- Mobile hamburger toggle -->
    <button
        v-if="isMobile && isCollapsed"
        @click="toggleSidebar"
        class="fixed top-4 left-4 z-50 bg-white dark:bg-gray-900 rounded-md shadow-lg p-2 hover:bg-gray-100 dark:hover:bg-gray-800"
        aria-label="Toggle sidebar"
    >
        <svg class="h-6 w-6 text-gray-700 dark:text-gray-300" stroke="currentColor" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>
</template>

<style scoped>
.tooltip-enter-active,
.tooltip-leave-active {
    transition: opacity 0.2s, transform 0.2s;
}

.tooltip-enter-from,
.tooltip-leave-to {
    opacity: 0;
    transform: translateX(5px) translateY(-50%);
}
</style>
