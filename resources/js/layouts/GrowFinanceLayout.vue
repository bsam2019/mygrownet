<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import ToastContainer from '@/Components/GrowBiz/ToastContainer.vue';
import GlobalLoadingBar from '@/Components/GrowBiz/GlobalLoadingBar.vue';
import PwaInstallPrompt from '@/Components/GrowFinance/PwaInstallPrompt.vue';
import RecordSaleModal from '@/Components/GrowFinance/RecordSaleModal.vue';
import RecordExpenseModal from '@/Components/GrowFinance/RecordExpenseModal.vue';
import QuickInvoiceModal from '@/Components/GrowFinance/QuickInvoiceModal.vue';
import NotificationBell from '@/Components/GrowFinance/NotificationBell.vue';
import { useToast } from '@/composables/useToast';
import {
    HomeIcon,
    BanknotesIcon,
    DocumentTextIcon,
    ChartBarIcon,
    ArrowRightOnRectangleIcon,
    ArrowLeftIcon,
    UsersIcon,
    BuildingStorefrontIcon,
    PlusIcon,
    ClipboardDocumentListIcon,
    BuildingLibraryIcon,
    Cog6ToothIcon,
    InformationCircleIcon,
    Bars3Icon,
    PhoneIcon,
    EnvelopeIcon,
    UserIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    ClockIcon,
    ExclamationCircleIcon,
    MagnifyingGlassIcon,
    ChatBubbleLeftRightIcon,
    QuestionMarkCircleIcon,
    XMarkIcon,
    BellIcon,
} from '@heroicons/vue/24/outline';
import {
    HomeIcon as HomeIconSolid,
    BanknotesIcon as BanknotesIconSolid,
    DocumentTextIcon as DocumentTextIconSolid,
    ChartBarIcon as ChartBarIconSolid,
} from '@heroicons/vue/24/solid';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const pageKey = computed(() => page.url);

const { toast } = useToast();
const flash = computed(() => (page.props as any).flash);

watch(flash, (newFlash) => {
    if (newFlash?.success) toast.success(newFlash.success);
    if (newFlash?.error) toast.error(newFlash.error);
    if (newFlash?.warning) toast.warning(newFlash.warning);
}, { immediate: true, deep: true });

const moreMenuOpen = ref(false);
const quickAddOpen = ref(false);
const isStandalone = ref(false);

// Collapsible sidebar state with localStorage persistence
const SIDEBAR_STORAGE_KEY = 'growfinance-sidebar-collapsed';
const sidebarCollapsed = ref(false);
const rightSidebarCollapsed = ref(false);
const hoveredNavItem = ref<string | null>(null);

// Quick entry modals
const showSaleModal = ref(false);
const showExpenseModal = ref(false);
const showInvoiceModal = ref(false);

// Quick entry data from page props (if available)
const quickEntryData = computed(() => (page.props as any).quickEntryData || {});
const customers = computed(() => quickEntryData.value.customers || []);
const vendors = computed(() => quickEntryData.value.vendors || []);
const expenseAccounts = computed(() => quickEntryData.value.expenseAccounts || []);

// Dashboard data for right sidebar
const dashboardData = computed(() => (page.props as any).overdueInvoices || []);
const invoiceStats = computed(() => (page.props as any).invoiceStats || {});

const openSaleModal = () => {
    quickAddOpen.value = false;
    showSaleModal.value = true;
};

const openExpenseModal = () => {
    quickAddOpen.value = false;
    showExpenseModal.value = true;
};

const openInvoiceModal = () => {
    quickAddOpen.value = false;
    showInvoiceModal.value = true;
};

const handleModalSuccess = () => {
    router.reload({ only: ['financialSummary', 'recentTransactions', 'invoiceStats'] });
};

// Toggle sidebar and persist to localStorage
const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
    localStorage.setItem(SIDEBAR_STORAGE_KEY, String(sidebarCollapsed.value));
};

const toggleRightSidebar = () => {
    rightSidebarCollapsed.value = !rightSidebarCollapsed.value;
    localStorage.setItem('growfinance-right-sidebar-collapsed', String(rightSidebarCollapsed.value));
};

onMounted(() => {
    isStandalone.value = window.matchMedia('(display-mode: standalone)').matches;
    document.addEventListener('click', closeMenus);
    document.body.style.overscrollBehavior = 'none';
    
    // Load sidebar state from localStorage
    const savedState = localStorage.getItem(SIDEBAR_STORAGE_KEY);
    if (savedState !== null) {
        sidebarCollapsed.value = savedState === 'true';
    }
    const savedRightState = localStorage.getItem('growfinance-right-sidebar-collapsed');
    if (savedRightState !== null) {
        rightSidebarCollapsed.value = savedRightState === 'true';
    }
    
    // Auto-collapse on medium screens
    const mediaQuery = window.matchMedia('(max-width: 1280px)');
    if (mediaQuery.matches && savedState === null) {
        sidebarCollapsed.value = true;
    }
});

onUnmounted(() => {
    document.removeEventListener('click', closeMenus);
});

const closeMenus = (e: MouseEvent) => {
    const target = e.target as HTMLElement;
    if (!target.closest('.more-menu-container')) moreMenuOpen.value = false;
    if (!target.closest('.quick-add-container')) quickAddOpen.value = false;
};

// Mobile bottom navigation (4 items)
const navigation = [
    { name: 'Dashboard', href: 'growfinance.dashboard', icon: HomeIcon, iconSolid: HomeIconSolid },
    { name: 'Sales', href: 'growfinance.sales.index', icon: BanknotesIcon, iconSolid: BanknotesIconSolid },
    { name: 'Invoices', href: 'growfinance.invoices.index', icon: DocumentTextIcon, iconSolid: DocumentTextIconSolid },
    { name: 'Reports', href: 'growfinance.reports.profit-loss', icon: ChartBarIcon, iconSolid: ChartBarIconSolid },
];

// Desktop sidebar navigation with badge counts
const desktopNavigation = computed(() => [
    { name: 'Dashboard', href: 'growfinance.dashboard', icon: HomeIcon, badge: null },
    { name: 'Accounts', href: 'growfinance.accounts.index', icon: ChartBarIcon, badge: null },
    { name: 'Sales', href: 'growfinance.sales.index', icon: BanknotesIcon, badge: null },
    { name: 'Invoices', href: 'growfinance.invoices.index', icon: DocumentTextIcon, badge: invoiceStats.value.overdue || null },
    { name: 'Expenses', href: 'growfinance.expenses.index', icon: ClipboardDocumentListIcon, badge: null },
    { name: 'Customers', href: 'growfinance.customers.index', icon: UsersIcon, badge: null },
    { name: 'Vendors', href: 'growfinance.vendors.index', icon: BuildingStorefrontIcon, badge: null },
    { name: 'Banking', href: 'growfinance.banking.index', icon: BuildingLibraryIcon, badge: null },
    { name: 'Reports', href: 'growfinance.reports.profit-loss', icon: ChartBarIcon, badge: null },
    { name: 'Messages', href: 'growfinance.messages.index', icon: ChatBubbleLeftRightIcon, badge: null },
    { name: 'Support', href: 'growfinance.support.index', icon: QuestionMarkCircleIcon, badge: null },
]);

const isActive = (routeName: string) => {
    return route().current(routeName) || route().current(routeName + '.*');
};

const handleLogout = () => router.post(route('logout'));

const getInitials = (name: string) => {
    if (!name) return '?';
    const parts = name.split(' ');
    return parts.length >= 2 ? (parts[0][0] + parts[1][0]).toUpperCase() : name.substring(0, 2).toUpperCase();
};

const closeMoreMenu = () => { moreMenuOpen.value = false; };
const toggleQuickAdd = () => { quickAddOpen.value = !quickAddOpen.value; };
const closeQuickAdd = () => { quickAddOpen.value = false; };
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <GlobalLoadingBar />
        
        <!-- Desktop Header (lg and up) -->
        <header class="hidden lg:block sticky top-0 z-40 bg-white border-b border-gray-200">
            <div class="flex items-center h-14 px-6">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <Link :href="route('growfinance.dashboard')" class="flex items-center gap-2">
                        <svg class="w-7 h-7 text-emerald-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                        </svg>
                        <span class="text-lg font-semibold text-emerald-600">Grow<span class="text-gray-800">Finance</span></span>
                    </Link>
                </div>

                <!-- Quick Search - Centered with flex-1 -->
                <div class="flex-1 flex justify-center px-8">
                    <div class="relative w-full max-w-md hidden xl:block">
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" aria-hidden="true" />
                        <input 
                            type="text" 
                            placeholder="Search invoices, customers..."
                            class="w-full pl-9 pr-12 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent placeholder-gray-400"
                        />
                        <kbd class="absolute right-3 top-1/2 -translate-y-1/2 hidden sm:inline-flex items-center px-1.5 py-0.5 text-[10px] font-medium text-gray-400 bg-gray-100 rounded border border-gray-200">
                            ⌘K
                        </kbd>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Notifications -->
                    <NotificationBell />
                    
                    <!-- User Info -->
                    <div class="flex items-center gap-2 text-gray-600 px-3 py-1.5 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                        <div class="h-7 w-7 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center">
                            <span class="text-[10px] font-semibold text-white">{{ getInitials(user?.name || '') }}</span>
                        </div>
                        <span class="text-sm font-medium">{{ user?.name }}</span>
                    </div>
                    
                    <!-- Help Button -->
                    <button 
                        class="p-2 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors"
                        aria-label="Help"
                    >
                        <InformationCircleIcon class="h-5 w-5" aria-hidden="true" />
                    </button>
                    
                    <!-- Menu Button -->
                    <button 
                        @click.stop="moreMenuOpen = !moreMenuOpen"
                        class="p-2 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors more-menu-container"
                        aria-label="Open menu"
                    >
                        <Bars3Icon class="h-5 w-5" aria-hidden="true" />
                    </button>
                </div>
            </div>
        </header>

        <!-- Mobile Header (below lg) -->
        <header class="lg:hidden sticky top-0 z-40 bg-white border-b border-gray-100 safe-area-top">
            <div class="flex items-center justify-between h-14 px-4 max-w-4xl mx-auto">
                <Link :href="route('growfinance.dashboard')" class="flex items-center gap-2">
                    <svg class="w-7 h-7 text-emerald-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                    </svg>
                    <span class="text-lg font-semibold text-emerald-600">Grow<span class="text-gray-800">Finance</span></span>
                </Link>

                <div class="flex items-center gap-2">
                    <!-- Mobile Notifications -->
                    <NotificationBell />
                    
                    <button 
                        @click.stop="moreMenuOpen = !moreMenuOpen"
                        class="p-0.5 rounded-full ring-2 ring-emerald-100 hover:ring-emerald-200 transition-all touch-manipulation more-menu-container"
                        aria-label="Open menu"
                    >
                        <div class="h-8 w-8 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center">
                            <span class="text-xs font-semibold text-white">{{ getInitials(user?.name || '') }}</span>
                        </div>
                    </button>
                </div>
            </div>
        </header>

        <!-- Desktop Layout with Sidebar -->
        <div class="hidden lg:flex flex-1">
            <!-- Left Sidebar Navigation - Collapsible -->
            <aside 
                :class="[
                    'bg-white border-r border-gray-200 flex-shrink-0 flex flex-col transition-all duration-300 ease-in-out relative',
                    sidebarCollapsed ? 'w-16' : 'w-52'
                ]"
            >
                <!-- Sidebar Toggle Button - Floating at edge -->
                <button 
                    @click="toggleSidebar"
                    class="absolute -right-3 top-6 z-10 w-6 h-6 bg-white border border-gray-200 rounded-full shadow-sm flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-colors"
                    :aria-label="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                >
                    <ChevronLeftIcon v-if="!sidebarCollapsed" class="h-3.5 w-3.5" aria-hidden="true" />
                    <ChevronRightIcon v-else class="h-3.5 w-3.5" aria-hidden="true" />
                </button>

                <!-- Navigation Items -->
                <nav class="flex-1 pt-3 pb-1 px-2 space-y-0.5 overflow-y-auto">
                    <div 
                        v-for="item in desktopNavigation" 
                        :key="item.name"
                        class="relative"
                        @mouseenter="hoveredNavItem = item.name"
                        @mouseleave="hoveredNavItem = null"
                    >
                        <Link 
                            :href="route(item.href)"
                            :class="[
                                'flex items-center gap-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 relative group',
                                sidebarCollapsed ? 'px-2 justify-center' : 'px-3',
                                isActive(item.href) 
                                    ? 'bg-emerald-50 text-emerald-700' 
                                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                            ]"
                        >
                            <!-- Active indicator bar -->
                            <div 
                                v-if="isActive(item.href)"
                                class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-emerald-500 rounded-r-full"
                            />
                            
                            <component :is="item.icon" class="h-5 w-5 flex-shrink-0" aria-hidden="true" />
                            
                            <!-- Label (hidden when collapsed) -->
                            <span 
                                v-if="!sidebarCollapsed" 
                                class="truncate transition-opacity duration-200"
                            >
                                {{ item.name }}
                            </span>
                            
                            <!-- Badge -->
                            <span 
                                v-if="item.badge && !sidebarCollapsed"
                                class="ml-auto bg-red-100 text-red-600 text-xs font-medium px-1.5 py-0.5 rounded-full"
                            >
                                {{ item.badge }}
                            </span>
                            
                            <!-- Badge for collapsed state -->
                            <span 
                                v-if="item.badge && sidebarCollapsed"
                                class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center"
                            >
                                {{ item.badge > 9 ? '9+' : item.badge }}
                            </span>
                        </Link>
                        
                        <!-- Tooltip for collapsed state -->
                        <Transition
                            enter-active-class="transition-opacity duration-150"
                            enter-from-class="opacity-0"
                            enter-to-class="opacity-100"
                            leave-active-class="transition-opacity duration-100"
                            leave-from-class="opacity-100"
                            leave-to-class="opacity-0"
                        >
                            <div 
                                v-if="sidebarCollapsed && hoveredNavItem === item.name"
                                class="absolute left-full ml-2 top-1/2 -translate-y-1/2 z-50 px-2 py-1 bg-gray-900 text-white text-xs font-medium rounded shadow-lg whitespace-nowrap"
                            >
                                {{ item.name }}
                                <span v-if="item.badge" class="ml-1 text-red-300">({{ item.badge }})</span>
                            </div>
                        </Transition>
                    </div>
                </nav>

                <!-- Sidebar Footer -->
                <div class="p-2 border-t border-gray-100">
                    <!-- Back to MyGrowNet -->
                    <div 
                        v-if="!isStandalone"
                        class="relative"
                        @mouseenter="hoveredNavItem = 'back'"
                        @mouseleave="hoveredNavItem = null"
                    >
                        <button 
                            @click="router.visit(route('home'))"
                            :class="[
                                'flex items-center gap-3 w-full py-2 rounded-lg text-sm text-gray-500 hover:bg-gray-50 hover:text-gray-700 transition-colors',
                                sidebarCollapsed ? 'px-2 justify-center' : 'px-3'
                            ]"
                        >
                            <ArrowLeftIcon class="h-4 w-4 flex-shrink-0" aria-hidden="true" />
                            <span v-if="!sidebarCollapsed" class="truncate">Back to MyGrowNet</span>
                        </button>
                        <!-- Tooltip for collapsed state -->
                        <Transition
                            enter-active-class="transition-opacity duration-150"
                            enter-from-class="opacity-0"
                            enter-to-class="opacity-100"
                            leave-active-class="transition-opacity duration-100"
                            leave-from-class="opacity-100"
                            leave-to-class="opacity-0"
                        >
                            <div 
                                v-if="sidebarCollapsed && hoveredNavItem === 'back'"
                                class="absolute left-full ml-2 top-1/2 -translate-y-1/2 z-50 px-2 py-1 bg-gray-900 text-white text-xs font-medium rounded shadow-lg whitespace-nowrap"
                            >
                                Back to MyGrowNet
                            </div>
                        </Transition>
                    </div>
                </div>
            </aside>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-auto">
                <slot />
            </main>

            <!-- Right Sidebar - Collapsible -->
            <aside 
                :class="[
                    'bg-white border-l border-gray-200 flex-shrink-0 flex flex-col transition-all duration-300 ease-in-out',
                    rightSidebarCollapsed ? 'w-0 overflow-hidden' : 'w-64'
                ]"
            >
                <div v-if="!rightSidebarCollapsed" class="p-4 space-y-6 overflow-y-auto flex-1">
                    <!-- Quick Links Section -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                            <PlusIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                            Quick Actions
                        </h3>
                        <div class="space-y-1">
                            <button 
                                @click="openSaleModal"
                                class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm text-gray-600 hover:bg-emerald-50 hover:text-emerald-700 transition-colors text-left group"
                            >
                                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center group-hover:bg-emerald-200 transition-colors">
                                    <BanknotesIcon class="h-4 w-4 text-emerald-600" aria-hidden="true" />
                                </div>
                                <span>Record Sale</span>
                            </button>
                            <button 
                                @click="openExpenseModal"
                                class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm text-gray-600 hover:bg-red-50 hover:text-red-700 transition-colors text-left group"
                            >
                                <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center group-hover:bg-red-200 transition-colors">
                                    <ClipboardDocumentListIcon class="h-4 w-4 text-red-600" aria-hidden="true" />
                                </div>
                                <span>Add Expense</span>
                            </button>
                            <button 
                                @click="openInvoiceModal"
                                class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm text-gray-600 hover:bg-blue-50 hover:text-blue-700 transition-colors text-left group"
                            >
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                                    <DocumentTextIcon class="h-4 w-4 text-blue-600" aria-hidden="true" />
                                </div>
                                <span>New Invoice</span>
                            </button>
                        </div>
                    </div>

                    <!-- Overdue Invoices Section -->
                    <div v-if="dashboardData.length > 0">
                        <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                            <ExclamationCircleIcon class="h-4 w-4 text-amber-500" aria-hidden="true" />
                            Overdue Invoices
                            <span class="ml-auto bg-amber-100 text-amber-700 text-xs font-medium px-1.5 py-0.5 rounded-full">
                                {{ dashboardData.length }}
                            </span>
                        </h3>
                        <div class="space-y-2">
                            <Link 
                                v-for="invoice in dashboardData.slice(0, 3)"
                                :key="invoice.id"
                                :href="route('growfinance.invoices.show', invoice.id)"
                                class="block px-3 py-2 rounded-lg text-sm hover:bg-amber-50 transition-colors border border-amber-100"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-900 truncate">{{ invoice.customer_name }}</span>
                                    <span class="text-amber-600 font-semibold text-xs">{{ invoice.days_overdue }}d</span>
                                </div>
                                <div class="text-xs text-gray-500 mt-0.5">
                                    K{{ invoice.balance_due?.toLocaleString() || '0' }} due
                                </div>
                            </Link>
                        </div>
                    </div>

                    <!-- Bills to Pay Section -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                            <ClockIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                            Bills to Pay
                        </h3>
                        <div class="space-y-2">
                            <Link 
                                :href="route('growfinance.expenses.index')"
                                class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors"
                            >
                                <div class="flex items-center gap-2">
                                    <UsersIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                                    <span>View All Expenses</span>
                                </div>
                                <ChevronRightIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                            </Link>
                            <Link 
                                :href="route('growfinance.vendors.index')"
                                class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors"
                            >
                                <div class="flex items-center gap-2">
                                    <BuildingStorefrontIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                                    <span>Manage Vendors</span>
                                </div>
                                <ChevronRightIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar Toggle (visible when collapsed) -->
                <button 
                    v-if="rightSidebarCollapsed"
                    @click="toggleRightSidebar"
                    class="absolute right-0 top-1/2 -translate-y-1/2 -translate-x-full bg-white border border-gray-200 rounded-l-lg p-1.5 shadow-sm hover:bg-gray-50 transition-colors"
                    aria-label="Expand right sidebar"
                >
                    <ChevronLeftIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                </button>
            </aside>

            <!-- Right Sidebar Toggle Button (when expanded) -->
            <button 
                v-if="!rightSidebarCollapsed"
                @click="toggleRightSidebar"
                class="absolute right-64 top-20 bg-white border border-gray-200 rounded-l-lg p-1.5 shadow-sm hover:bg-gray-50 transition-colors z-10"
                aria-label="Collapse right sidebar"
            >
                <ChevronRightIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
            </button>
        </div>

        <!-- Mobile Main Content -->
        <main class="lg:hidden flex-1 pb-20">
            <div class="max-w-4xl mx-auto">
                <slot />
            </div>
        </main>

        <ToastContainer />
        
        <!-- PWA Install Prompt -->
        <PwaInstallPrompt />

        <!-- More Menu - Right Slide-in Drawer -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="moreMenuOpen" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm" @click="closeMoreMenu" />
        </Transition>

        <Transition
            enter-active-class="transition-transform duration-300 ease-out"
            enter-from-class="translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition-transform duration-200 ease-in"
            leave-from-class="translate-x-0"
            leave-to-class="translate-x-full"
        >
            <div v-if="moreMenuOpen" class="fixed inset-y-0 right-0 w-full max-w-xs z-50 bg-white shadow-2xl flex flex-col more-menu-container">
                <!-- Header -->
                <div class="px-4 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center shadow-md">
                            <span class="text-sm font-semibold text-white">{{ getInitials(user?.name || '') }}</span>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">{{ user?.name }}</p>
                            <p class="text-xs text-gray-500">{{ user?.email }}</p>
                        </div>
                    </div>
                    <button 
                        @click="closeMoreMenu" 
                        class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                        aria-label="Close menu"
                    >
                        <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                    </button>
                </div>

                <!-- Scrollable Content -->
                <div class="flex-1 overflow-y-auto px-4 py-4">
                    <!-- Business Section -->
                    <div class="mb-5">
                        <p class="px-2 py-1.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Business</p>
                        <div class="space-y-1">
                            <Link :href="route('growfinance.customers.index')" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors" @click="closeMoreMenu">
                                <div class="w-9 h-9 rounded-full bg-blue-50 flex items-center justify-center">
                                    <UsersIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                                </div>
                                <span class="text-sm font-medium">Customers</span>
                            </Link>
                            <Link :href="route('growfinance.vendors.index')" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors" @click="closeMoreMenu">
                                <div class="w-9 h-9 rounded-full bg-purple-50 flex items-center justify-center">
                                    <BuildingStorefrontIcon class="h-5 w-5 text-purple-600" aria-hidden="true" />
                                </div>
                                <span class="text-sm font-medium">Vendors</span>
                            </Link>
                            <Link :href="route('growfinance.invoices.index')" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors" @click="closeMoreMenu">
                                <div class="w-9 h-9 rounded-full bg-emerald-50 flex items-center justify-center">
                                    <DocumentTextIcon class="h-5 w-5 text-emerald-600" aria-hidden="true" />
                                </div>
                                <span class="text-sm font-medium">Invoices</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Finance Section -->
                    <div class="mb-5">
                        <p class="px-2 py-1.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Finance</p>
                        <div class="space-y-1">
                            <Link :href="route('growfinance.banking.index')" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors" @click="closeMoreMenu">
                                <div class="w-9 h-9 rounded-full bg-teal-50 flex items-center justify-center">
                                    <BuildingLibraryIcon class="h-5 w-5 text-teal-600" aria-hidden="true" />
                                </div>
                                <span class="text-sm font-medium">Banking</span>
                            </Link>
                            <Link :href="route('growfinance.accounts.index')" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors" @click="closeMoreMenu">
                                <div class="w-9 h-9 rounded-full bg-amber-50 flex items-center justify-center">
                                    <ChartBarIcon class="h-5 w-5 text-amber-600" aria-hidden="true" />
                                </div>
                                <span class="text-sm font-medium">Chart of Accounts</span>
                            </Link>
                            <Link :href="route('growfinance.reports.profit-loss')" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors" @click="closeMoreMenu">
                                <div class="w-9 h-9 rounded-full bg-indigo-50 flex items-center justify-center">
                                    <ChartBarIcon class="h-5 w-5 text-indigo-600" aria-hidden="true" />
                                </div>
                                <span class="text-sm font-medium">Reports</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Help & Communication Section -->
                    <div class="mb-5">
                        <p class="px-2 py-1.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Help & Communication</p>
                        <div class="space-y-1">
                            <Link :href="route('growfinance.messages.index')" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors" @click="closeMoreMenu">
                                <div class="w-9 h-9 rounded-full bg-cyan-50 flex items-center justify-center">
                                    <ChatBubbleLeftRightIcon class="h-5 w-5 text-cyan-600" aria-hidden="true" />
                                </div>
                                <span class="text-sm font-medium">Messages</span>
                            </Link>
                            <Link :href="route('growfinance.support.index')" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors" @click="closeMoreMenu">
                                <div class="w-9 h-9 rounded-full bg-rose-50 flex items-center justify-center">
                                    <QuestionMarkCircleIcon class="h-5 w-5 text-rose-600" aria-hidden="true" />
                                </div>
                                <span class="text-sm font-medium">Support</span>
                            </Link>
                            <Link :href="route('growfinance.notifications.index')" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors" @click="closeMoreMenu">
                                <div class="w-9 h-9 rounded-full bg-orange-50 flex items-center justify-center">
                                    <BellIcon class="h-5 w-5 text-orange-600" aria-hidden="true" />
                                </div>
                                <span class="text-sm font-medium">Notifications</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="px-4 py-4 border-t border-gray-100 space-y-1">
                    <Link v-if="!isStandalone" :href="route('home')" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-600 hover:bg-gray-50 active:bg-gray-100 transition-colors" @click="closeMoreMenu">
                        <ArrowLeftIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                        <span class="text-sm font-medium">Back to MyGrowNet</span>
                    </Link>
                    <button @click="handleLogout" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-red-600 hover:bg-red-50 active:bg-red-100 w-full transition-colors">
                        <ArrowRightOnRectangleIcon class="h-5 w-5 text-red-400" aria-hidden="true" />
                        <span class="text-sm font-medium">Sign Out</span>
                    </button>
                </div>
            </div>
        </Transition>

        <!-- Quick Add FAB Menu -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="quickAddOpen" class="fixed inset-0 z-40 bg-black/30 backdrop-blur-sm" @click="closeQuickAdd" />
        </Transition>

        <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 translate-y-4"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition-all duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-4"
        >
            <div v-if="quickAddOpen" class="fixed bottom-24 left-1/2 -translate-x-1/2 z-50 quick-add-container">
                <div class="bg-white rounded-2xl shadow-xl p-2 min-w-[180px]">
                    <button 
                        @click="openSaleModal"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-50 active:bg-gray-100 transition-colors w-full text-left"
                    >
                        <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center">
                            <BanknotesIcon class="h-4 w-4 text-emerald-600" aria-hidden="true" />
                        </div>
                        <span class="font-medium text-gray-900 text-sm">Record Sale</span>
                    </button>
                    <button 
                        @click="openExpenseModal"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-50 active:bg-gray-100 transition-colors w-full text-left"
                    >
                        <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                            <ClipboardDocumentListIcon class="h-4 w-4 text-red-600" aria-hidden="true" />
                        </div>
                        <span class="font-medium text-gray-900 text-sm">Add Expense</span>
                    </button>
                    <button 
                        @click="openInvoiceModal"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-50 active:bg-gray-100 transition-colors w-full text-left"
                    >
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                            <DocumentTextIcon class="h-4 w-4 text-blue-600" aria-hidden="true" />
                        </div>
                        <span class="font-medium text-gray-900 text-sm">Create Invoice</span>
                    </button>
                </div>
            </div>
        </Transition>

        <!-- Quick Entry Modals -->
        <RecordSaleModal 
            :show="showSaleModal" 
            :customers="customers"
            @close="showSaleModal = false"
            @success="handleModalSuccess"
        />
        <RecordExpenseModal 
            :show="showExpenseModal" 
            :vendors="vendors"
            :accounts="expenseAccounts"
            @close="showExpenseModal = false"
            @success="handleModalSuccess"
        />
        <QuickInvoiceModal 
            :show="showInvoiceModal" 
            :customers="customers"
            @close="showInvoiceModal = false"
            @success="handleModalSuccess"
        />

        <!-- Bottom Navigation (Mobile Only) -->
        <nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 z-40 safe-area-bottom">
            <div class="flex items-center justify-around h-16 max-w-md mx-auto relative">
                <!-- Dashboard -->
                <Link 
                    :href="route(navigation[0].href)"
                    :class="[
                        'flex flex-col items-center justify-center flex-1 h-full transition-all touch-manipulation',
                        isActive(navigation[0].href) ? 'text-blue-600' : 'text-gray-400'
                    ]"
                >
                    <component 
                        :is="isActive(navigation[0].href) ? navigation[0].iconSolid : navigation[0].icon" 
                        class="h-5 w-5" 
                        aria-hidden="true" 
                    />
                    <span :class="['text-[10px] mt-1 font-medium', isActive(navigation[0].href) ? 'text-blue-600' : 'text-gray-500']">
                        {{ navigation[0].name }}
                    </span>
                </Link>

                <!-- Sales -->
                <Link 
                    :href="route(navigation[1].href)"
                    :class="[
                        'flex flex-col items-center justify-center flex-1 h-full transition-all touch-manipulation',
                        isActive(navigation[1].href) ? 'text-blue-600' : 'text-gray-400'
                    ]"
                >
                    <component 
                        :is="isActive(navigation[1].href) ? navigation[1].iconSolid : navigation[1].icon" 
                        class="h-5 w-5" 
                        aria-hidden="true" 
                    />
                    <span :class="['text-[10px] mt-1 font-medium', isActive(navigation[1].href) ? 'text-blue-600' : 'text-gray-500']">
                        {{ navigation[1].name }}
                    </span>
                </Link>

                <!-- Center FAB Button -->
                <div class="flex flex-col items-center justify-center flex-1 h-full quick-add-container">
                    <button 
                        @click.stop="toggleQuickAdd"
                        :class="[
                            'w-12 h-12 rounded-full flex items-center justify-center shadow-lg transition-all transform -mt-5',
                            quickAddOpen 
                                ? 'bg-gray-600 rotate-45' 
                                : 'bg-emerald-500 hover:bg-emerald-600 active:scale-95'
                        ]"
                        aria-label="Quick add menu"
                    >
                        <PlusIcon class="h-6 w-6 text-white" aria-hidden="true" />
                    </button>
                </div>

                <!-- Invoices -->
                <Link 
                    :href="route(navigation[2].href)"
                    :class="[
                        'flex flex-col items-center justify-center flex-1 h-full transition-all touch-manipulation',
                        isActive(navigation[2].href) ? 'text-blue-600' : 'text-gray-400'
                    ]"
                >
                    <component 
                        :is="isActive(navigation[2].href) ? navigation[2].iconSolid : navigation[2].icon" 
                        class="h-5 w-5" 
                        aria-hidden="true" 
                    />
                    <span :class="['text-[10px] mt-1 font-medium', isActive(navigation[2].href) ? 'text-blue-600' : 'text-gray-500']">
                        {{ navigation[2].name }}
                    </span>
                </Link>

                <!-- Reports -->
                <Link 
                    :href="route(navigation[3].href)"
                    :class="[
                        'flex flex-col items-center justify-center flex-1 h-full transition-all touch-manipulation',
                        isActive(navigation[3].href) ? 'text-blue-600' : 'text-gray-400'
                    ]"
                >
                    <component 
                        :is="isActive(navigation[3].href) ? navigation[3].iconSolid : navigation[3].icon" 
                        class="h-5 w-5" 
                        aria-hidden="true" 
                    />
                    <span :class="['text-[10px] mt-1 font-medium', isActive(navigation[3].href) ? 'text-blue-600' : 'text-gray-500']">
                        {{ navigation[3].name }}
                    </span>
                </Link>
            </div>
        </nav>
    </div>
</template>

<style scoped>
.safe-area-top { padding-top: env(safe-area-inset-top); }
.safe-area-bottom { padding-bottom: env(safe-area-inset-bottom); }
.touch-manipulation { touch-action: manipulation; -webkit-tap-highlight-color: transparent; }

/* Smooth sidebar transitions */
aside {
    will-change: width;
}

/* Navigation item hover effect */
nav a:hover {
    transform: translateX(2px);
}

/* Active indicator animation */
nav a .absolute {
    transition: all 0.2s ease-out;
}

/* Tooltip arrow */
.tooltip-arrow::before {
    content: '';
    position: absolute;
    left: -4px;
    top: 50%;
    transform: translateY(-50%);
    border-width: 4px;
    border-style: solid;
    border-color: transparent #111827 transparent transparent;
}
</style>
