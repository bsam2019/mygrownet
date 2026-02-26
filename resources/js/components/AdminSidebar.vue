<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AppLogo from './AppLogo.vue';
import CustomNavUser from './CustomNavUser.vue';
import { type NavItem } from '@/types';
import { 
    BookOpen, 
    Folder, 
    LayoutGrid, 
    Users, 
    FileText, 
    Activity, 
    BarChart3 as ChartBarIcon, 
    UserCheck, 
    Building2, 
    Briefcase, 
    Target, 
    DollarSign, 
    Shield, 
    ShieldCheck as ShieldCheckIcon,
    Key, 
    CreditCard,
    ChevronDown,
    TrendingUp,
    Star,
    Settings,
    Calendar,
    BarChart3,
    Bell,
    Mail as MailIcon,
    Send as SendIcon,
    Ticket as TicketIcon,
    MessageCircle as LiveChatIcon,
    Globe,
    Trash2,
    HardDrive as HardDriveIcon,
    ShoppingBag,
    Store,
    Package,
    AlertTriangle,
    MessageSquare,
    Cloud
} from 'lucide-vue-next';
import { useModules } from '@/composables/useModules';

const page = usePage();
const { isModuleEnabled } = useModules();
const isCollapsed = ref(false);
const isMobile = ref(false);
const showSubmenu = ref<Record<string, boolean>>({});

// Tooltip
const showTooltip = ref(false);
const tooltipText = ref('');
const tooltipPosition = { x: 0, y: 0 };

// Helper function to safely get route
const safeRoute = (routeName: string, fallback: string = '#') => {
    try {
        return route(routeName);
    } catch (error) {
        console.warn(`Route '${routeName}' not found, using fallback`);
        return fallback;
    }
};

// Menu structure

const userManagementNavItems: NavItem[] = [
    { title: 'Users', href: safeRoute('admin.users.index'), icon: Users },
    { title: 'Analytics', href: safeRoute('admin.analytics.index'), icon: Activity },
    { title: 'Subscriptions', href: safeRoute('admin.subscriptions.index'), icon: CreditCard },
    { title: 'Packages', href: safeRoute('admin.packages.index'), icon: BookOpen },
    { title: 'Library Resources', href: safeRoute('admin.library.resources.index'), icon: BookOpen },
];

const growNetNavItems: NavItem[] = [
    { title: 'Starter Kits', href: safeRoute('admin.starter-kit.dashboard'), icon: BookOpen },
    { title: 'Starter Kit Tiers', href: safeRoute('admin.starter-kit-tiers.index'), icon: Settings },
    { title: 'Referral System', href: safeRoute('admin.referrals.index'), icon: Users },
    { title: 'Matrix Management', href: safeRoute('admin.matrix.index'), icon: LayoutGrid },
    { title: 'Network Management', href: safeRoute('admin.network.index'), icon: Activity },
    { title: 'Points Management', href: '/admin/points', icon: Target },
];

const lgrNavItems: NavItem[] = [
    { title: 'Overview', href: safeRoute('admin.lgr.index'), icon: LayoutGrid },
    { title: 'Manual Awards', href: safeRoute('admin.lgr.awards.index'), icon: Star },
    { title: 'Activity Report', href: safeRoute('admin.lgr.activity-report'), icon: Activity },
    { title: 'Cycles', href: safeRoute('admin.lgr.cycles'), icon: Activity },
    { title: 'Qualifications', href: safeRoute('admin.lgr.qualifications'), icon: Target },
    { title: 'Pool Management', href: safeRoute('admin.lgr.pool'), icon: DollarSign },
    { title: 'Promotional Cards', href: safeRoute('admin.promotional-cards.index'), icon: MessageSquare },
    { title: 'Settings', href: safeRoute('admin.lgr.settings'), icon: Shield }, // Temporarily using Shield icon
];

const financeNavItems: NavItem[] = [
    { title: 'Payment Approvals', href: safeRoute('admin.payments.index'), icon: DollarSign },
    { title: 'Receipts', href: safeRoute('admin.receipts.index'), icon: FileText },
    { title: 'Commission Settings', href: safeRoute('admin.commission-settings.index'), icon: Settings },
    { title: 'Community Profit Sharing', href: safeRoute('admin.profit-sharing.index'), icon: Activity },
    { title: 'Investment Profit Distribution', href: safeRoute('admin.profit-distribution.index'), icon: Activity },
    { title: 'Withdrawals', href: safeRoute('admin.withdrawals.index'), icon: Activity },
    { title: 'Loan Management', href: safeRoute('admin.loans.index'), icon: CreditCard },
];

const investorRelationsNavItems: NavItem[] = [
    { title: 'Investment Rounds', href: safeRoute('admin.investment-rounds.index'), icon: TrendingUp },
    { title: 'Investor Accounts', href: safeRoute('admin.investor-accounts.index'), icon: UserCheck },
    { title: 'Investor Messages', href: safeRoute('admin.investor-messages.index'), icon: MailIcon },
    { title: 'Investor Documents', href: safeRoute('admin.investor-documents.index'), icon: FileText },
    { title: 'Financial Reports', href: safeRoute('admin.financial-reports.index'), icon: ChartBarIcon },
    // Note: Investor Announcements feature not yet implemented - routes/controller/migration needed
    { title: 'Investor Analytics', href: safeRoute('admin.investor-analytics.index'), icon: ChartBarIcon },
];

const ventureBuilderNavItems: NavItem[] = [
    { title: 'Dashboard', href: safeRoute('admin.ventures.dashboard'), icon: LayoutGrid },
    { title: 'All Ventures', href: safeRoute('admin.ventures.index'), icon: Briefcase },
    { title: 'Create Venture', href: safeRoute('admin.ventures.create'), icon: FileText },
    { title: 'Investments', href: safeRoute('admin.ventures.investments.index'), icon: DollarSign },
    { title: 'Categories', href: safeRoute('admin.ventures.categories'), icon: Folder },
    { title: 'Analytics', href: safeRoute('admin.ventures.analytics'), icon: ChartBarIcon },
];

const bgfNavItems: NavItem[] = [
    { title: 'Dashboard', href: safeRoute('admin.bgf.dashboard'), icon: LayoutGrid },
    { title: 'Applications', href: safeRoute('admin.bgf.applications'), icon: FileText },
    { title: 'Projects', href: safeRoute('admin.bgf.projects.index'), icon: Briefcase },
    { title: 'Disbursements', href: safeRoute('admin.bgf.disbursements.index'), icon: DollarSign },
    { title: 'Repayments', href: safeRoute('admin.bgf.repayments.index'), icon: Activity },
    { title: 'Evaluations', href: safeRoute('admin.bgf.evaluations.index'), icon: Target },
    { title: 'Contracts', href: safeRoute('admin.bgf.contracts.index'), icon: FileText },
    { title: 'Analytics', href: safeRoute('admin.bgf.analytics'), icon: ChartBarIcon },
];

const growBuilderNavItems: NavItem[] = [
    { title: 'All Sites', href: safeRoute('admin.growbuilder.index'), icon: Globe },
    { title: 'Deleted Sites', href: safeRoute('admin.growbuilder.deleted'), icon: Trash2 },
    { title: 'Storage', href: safeRoute('admin.growbuilder.storage'), icon: HardDriveIcon },
    { title: 'Analytics', href: safeRoute('admin.growbuilder.analytics'), icon: ChartBarIcon },
    { title: 'Premium Access', href: safeRoute('admin.premium-access.index'), icon: ShieldCheckIcon },
];

const marketplaceNavItems: NavItem[] = [
    { title: 'Dashboard', href: safeRoute('admin.marketplace.dashboard'), icon: LayoutGrid },
    { title: 'Sellers', href: safeRoute('admin.marketplace.sellers.index'), icon: Store },
    { title: 'Products', href: safeRoute('admin.marketplace.products.index'), icon: Package },
    { title: 'Orders', href: safeRoute('admin.marketplace.orders.index'), icon: ShoppingBag },
    { title: 'Disputes', href: safeRoute('admin.marketplace.disputes.index'), icon: AlertTriangle },
    { title: 'Reviews', href: safeRoute('admin.marketplace.reviews.index'), icon: MessageSquare },
    { title: 'Categories', href: safeRoute('admin.marketplace.categories.index'), icon: Folder },
    { title: 'Analytics', href: safeRoute('admin.marketplace.analytics'), icon: ChartBarIcon },
];

const growBackupNavItems: NavItem[] = [
    { title: 'Dashboard', href: safeRoute('admin.growbackup.index'), icon: LayoutGrid },
    { title: 'Storage Plans', href: safeRoute('admin.growbackup.plans'), icon: Package },
    { title: 'User Management', href: safeRoute('admin.growbackup.users'), icon: Users },
];

const reportsNavItems: NavItem[] = [
    { title: 'Reward Analytics', href: safeRoute('admin.reward-analytics.index'), icon: ChartBarIcon },
    { title: 'Points Analytics', href: safeRoute('admin.analytics.points'), icon: Target },
    { title: 'Matrix Analytics', href: safeRoute('admin.analytics.matrix'), icon: LayoutGrid },
    { title: 'Member Analytics', href: safeRoute('admin.analytics.members'), icon: Users },
    { title: 'Financial Reports', href: safeRoute('admin.analytics.financial'), icon: DollarSign },
    { title: 'System Analytics', href: safeRoute('admin.analytics.system'), icon: Activity },
];

const employeeNavItems: NavItem[] = [
    { title: 'All Employees', href: safeRoute('admin.employees.index'), icon: UserCheck },
    { title: 'Delegations', href: safeRoute('admin.delegations.index'), icon: Shield },
    { title: 'Task Management', href: safeRoute('admin.tasks.index'), icon: Target },
    { title: 'Departments', href: safeRoute('admin.departments.index'), icon: Building2 },
    { title: 'Positions', href: safeRoute('admin.positions.index'), icon: Briefcase },
    { title: 'Organizational Chart', href: safeRoute('admin.organization.index'), icon: Users },
    { title: 'KPI Management', href: safeRoute('admin.organization.kpis.index'), icon: BarChart3 },
    { title: 'Hiring Roadmap', href: safeRoute('admin.organization.hiring.index'), icon: Calendar },
    { title: 'Performance', href: safeRoute('admin.performance.index'), icon: Target },
    { title: 'Commissions', href: safeRoute('admin.commissions.index'), icon: DollarSign },
];

const communicationNavItems: NavItem[] = [
    { title: 'Messages', href: safeRoute('admin.messages.index'), icon: 'MailIcon' },
    { title: 'Compose Message', href: safeRoute('admin.messages.compose'), icon: 'SendIcon' },
    { title: 'Support Center', href: safeRoute('admin.unified-support.index'), icon: 'LiveChatIcon' },
    { title: 'Email Campaigns', href: safeRoute('admin.email-campaigns.index'), icon: 'SendIcon' },
    { title: 'Telegram Bot (FREE)', href: '#telegram', icon: 'BellIcon', badge: 'FREE' },
];

const systemNavItems: NavItem[] = [
    { title: 'Announcements', href: safeRoute('admin.announcements.index'), icon: Bell },
    { title: 'Module Management', href: safeRoute('admin.modules.index'), icon: Settings },
    { title: 'Module Subscriptions', href: safeRoute('admin.module-subscriptions.index'), icon: CreditCard },
    { title: 'GrowSuite Companies', href: safeRoute('admin.cms-companies.index'), icon: Building2 },
    { title: 'Bonus Points Settings', href: safeRoute('admin.settings.bp.index'), icon: Target },
    { title: 'Roles', href: safeRoute('admin.role-management.roles.index'), icon: Shield },
    { title: 'Permissions', href: safeRoute('admin.role-management.permissions.index'), icon: Key },
    { title: 'User Roles', href: safeRoute('admin.role-management.users.index'), icon: Users },
];

const emit = defineEmits<{
    (e: 'update:collapsed', value: boolean): void;
}>();

const toggleSidebar = () => {
    isCollapsed.value = !isCollapsed.value;
    localStorage.setItem('admin.sidebarCollapsed', isCollapsed.value.toString());
    emit('update:collapsed', isCollapsed.value);
};

const toggleSubmenu = (key: string) => {
    showSubmenu.value[key] = !showSubmenu.value[key];
    localStorage.setItem('admin.sidebarSubmenus', JSON.stringify(showSubmenu.value));
};

const checkMobile = () => {
    const wasMobile = isMobile.value;
    isMobile.value = window.innerWidth < 1024;
    
    if (isMobile.value && !wasMobile) {
        isCollapsed.value = true;
    } else if (!isMobile.value && wasMobile) {
        const savedState = localStorage.getItem('admin.sidebarCollapsed');
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
    
    if (!isMobile.value) {
        const savedState = localStorage.getItem('admin.sidebarCollapsed');
        if (savedState !== null) {
            isCollapsed.value = savedState === 'true';
        }
    } else {
        isCollapsed.value = true;
    }
    
    const savedSubmenus = localStorage.getItem('admin.sidebarSubmenus');
    if (savedSubmenus) {
        try {
            showSubmenu.value = JSON.parse(savedSubmenus);
        } catch (e) {
            console.warn('Failed to parse saved submenu states');
        }
    }
    
    // Auto-expand Employees submenu if on organizational structure pages
    const currentUrl = page.url;
    if (currentUrl.includes('/admin/organization') || 
        currentUrl.includes('/admin/employees') || 
        currentUrl.includes('/admin/departments') || 
        currentUrl.includes('/admin/positions') ||
        currentUrl.includes('/admin/performance') ||
        currentUrl.includes('/admin/commissions') ||
        currentUrl.includes('/admin/tasks')) {
        showSubmenu.value.employees = true;
    }
});
</script>

<template>
    <aside :class="[
        'fixed inset-y-0 left-0 z-50 flex flex-col transition-all duration-300',
        'bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800',
        !isMobile && (isCollapsed ? 'w-16' : 'w-64'),
        isMobile && (isCollapsed ? '-translate-x-full w-64' : 'translate-x-0 w-64')
    ]">
        <!-- Sidebar Header -->
        <div class="flex h-16 min-h-[64px] items-center justify-between px-4 border-b border-gray-200 dark:border-gray-800">
            <div class="flex items-center justify-between w-full h-full">
                <Link
                    :href="safeRoute('admin.dashboard')"
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

        <!-- Tooltip -->
        <Transition name="tooltip">
            <div v-if="showTooltip"
                class="fixed z-50 px-2 py-1 text-sm text-white bg-gray-900 rounded shadow-lg pointer-events-none transform -translate-y-1/2"
                :style="{ left: `${tooltipPosition.x}px`, top: `${tooltipPosition.y}px` }"
            >
                {{ tooltipText }}
            </div>
        </Transition>

        <!-- Navigation Links -->
        <div class="py-4 overflow-y-auto flex-grow">
            <nav>
                <!-- User Management Section -->
                <div class="pt-2">
                    <button @click="toggleSubmenu('userManagement')"
                        :class="[
                            'w-full flex items-center justify-between px-4 py-2 transition-colors duration-200',
                            'hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none',
                            'text-gray-700 dark:text-gray-300'
                        ]"
                        @mouseenter="showItemTooltip($event, 'User Management')"
                        @mouseleave="hideTooltip"
                    >
                        <div class="flex items-center">
                            <Users class="h-5 w-5" />
                            <span v-show="!isCollapsed || isMobile" class="ml-3">User Management</span>
                        </div>
                        <ChevronDown v-show="!isCollapsed || isMobile" class="h-5 w-5 transform transition-transform duration-200"
                            :class="{ 'rotate-180': showSubmenu.userManagement }" />
                    </button>

                    <div v-if="showSubmenu.userManagement" v-show="!isCollapsed || isMobile" class="mt-2 pl-4 space-y-1">
                        <Link v-for="item in userManagementNavItems" :key="item.title"
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

                <!-- GrowNet Section -->
                <div class="pt-2">
                    <button @click="toggleSubmenu('growNet')"
                        :class="[
                            'w-full flex items-center justify-between px-4 py-2 transition-colors duration-200',
                            'hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none',
                            'text-gray-700 dark:text-gray-300'
                        ]"
                        @mouseenter="showItemTooltip($event, 'GrowNet')"
                        @mouseleave="hideTooltip"
                    >
                        <div class="flex items-center">
                            <LayoutGrid class="h-5 w-5" />
                            <span v-show="!isCollapsed || isMobile" class="ml-3">GrowNet</span>
                        </div>
                        <ChevronDown v-show="!isCollapsed || isMobile" class="h-5 w-5 transform transition-transform duration-200"
                            :class="{ 'rotate-180': showSubmenu.growNet }" />
                    </button>

                    <div v-if="showSubmenu.growNet" v-show="!isCollapsed || isMobile" class="mt-2 pl-4 space-y-1">
                        <Link v-for="item in growNetNavItems" :key="item.title"
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

                <!-- LGR Management Section -->
                <div class="pt-2">
                    <button @click="toggleSubmenu('lgr')"
                        :class="[
                            'w-full flex items-center justify-between px-4 py-2 transition-colors duration-200',
                            'hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none',
                            'text-gray-700 dark:text-gray-300'
                        ]"
                        @mouseenter="showItemTooltip($event, 'LGR Management')"
                        @mouseleave="hideTooltip"
                    >
                        <div class="flex items-center">
                            <Star class="h-5 w-5" />
                            <span v-show="!isCollapsed || isMobile" class="ml-3">LGR Management</span>
                        </div>
                        <ChevronDown v-show="!isCollapsed || isMobile" class="h-5 w-5 transform transition-transform duration-200"
                            :class="{ 'rotate-180': showSubmenu.lgr }" />
                    </button>

                    <div v-if="showSubmenu.lgr" v-show="!isCollapsed || isMobile" class="mt-2 pl-4 space-y-1">
                        <Link v-for="item in lgrNavItems" :key="item.title"
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

                <!-- Finance Section -->
                <div class="pt-2">
                    <button @click="toggleSubmenu('finance')"
                        :class="[
                            'w-full flex items-center justify-between px-4 py-2 transition-colors duration-200',
                            'hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none',
                            'text-gray-700 dark:text-gray-300'
                        ]"
                        @mouseenter="showItemTooltip($event, 'Finance')"
                        @mouseleave="hideTooltip"
                    >
                        <div class="flex items-center">
                            <DollarSign class="h-5 w-5" />
                            <span v-show="!isCollapsed || isMobile" class="ml-3">Finance</span>
                        </div>
                        <ChevronDown v-show="!isCollapsed || isMobile" class="h-5 w-5 transform transition-transform duration-200"
                            :class="{ 'rotate-180': showSubmenu.finance }" />
                    </button>

                    <div v-if="showSubmenu.finance" v-show="!isCollapsed || isMobile" class="mt-2 pl-4 space-y-1">
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

                <!-- Investor Relations Section -->
                <div class="pt-2">
                    <button @click="toggleSubmenu('investorRelations')"
                        :class="[
                            'w-full flex items-center justify-between px-4 py-2 transition-colors duration-200',
                            'hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none',
                            'text-gray-700 dark:text-gray-300'
                        ]"
                        @mouseenter="showItemTooltip($event, 'Investor Relations')"
                        @mouseleave="hideTooltip"
                    >
                        <div class="flex items-center">
                            <TrendingUp class="h-5 w-5" />
                            <span v-show="!isCollapsed || isMobile" class="ml-3">Investor Relations</span>
                        </div>
                        <ChevronDown v-show="!isCollapsed || isMobile" class="h-5 w-5 transform transition-transform duration-200"
                            :class="{ 'rotate-180': showSubmenu.investorRelations }" />
                    </button>

                    <div v-if="showSubmenu.investorRelations" v-show="!isCollapsed || isMobile" class="mt-2 pl-4 space-y-1">
                        <Link v-for="item in investorRelationsNavItems" :key="item.title"
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

                <!-- Venture Builder Section (only if module enabled) -->
                <div v-if="isModuleEnabled('venture_builder')" class="pt-2">
                    <button @click="toggleSubmenu('ventureBuilder')"
                        :class="[
                            'w-full flex items-center justify-between px-4 py-2 transition-colors duration-200',
                            'hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none',
                            'text-gray-700 dark:text-gray-300'
                        ]"
                        @mouseenter="showItemTooltip($event, 'Venture Builder')"
                        @mouseleave="hideTooltip"
                    >
                        <div class="flex items-center">
                            <Briefcase class="h-5 w-5" />
                            <span v-show="!isCollapsed || isMobile" class="ml-3">Venture Builder</span>
                        </div>
                        <ChevronDown v-show="!isCollapsed || isMobile" class="h-5 w-5 transform transition-transform duration-200"
                            :class="{ 'rotate-180': showSubmenu.ventureBuilder }" />
                    </button>

                    <div v-if="showSubmenu.ventureBuilder" v-show="!isCollapsed || isMobile" class="mt-2 pl-4 space-y-1">
                        <Link v-for="item in ventureBuilderNavItems" :key="item.title"
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

                <!-- Business Growth Fund Section -->
                <div class="pt-2">
                    <button @click="toggleSubmenu('bgf')"
                        :class="[
                            'w-full flex items-center justify-between px-4 py-2 transition-colors duration-200',
                            'hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none',
                            'text-gray-700 dark:text-gray-300'
                        ]"
                        @mouseenter="showItemTooltip($event, 'Growth Fund')"
                        @mouseleave="hideTooltip"
                    >
                        <div class="flex items-center">
                            <TrendingUp class="h-5 w-5" />
                            <span v-show="!isCollapsed || isMobile" class="ml-3">Growth Fund</span>
                        </div>
                        <ChevronDown v-show="!isCollapsed || isMobile" class="h-5 w-5 transform transition-transform duration-200"
                            :class="{ 'rotate-180': showSubmenu.bgf }" />
                    </button>

                    <div v-if="showSubmenu.bgf" v-show="!isCollapsed || isMobile" class="mt-2 pl-4 space-y-1">
                        <Link v-for="item in bgfNavItems" :key="item.title"
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

                <!-- GrowBuilder Section -->
                <div class="pt-2">
                    <button @click="toggleSubmenu('growBuilder')"
                        :class="[
                            'w-full flex items-center justify-between px-4 py-2 transition-colors duration-200',
                            'hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none',
                            'text-gray-700 dark:text-gray-300'
                        ]"
                        @mouseenter="showItemTooltip($event, 'GrowBuilder')"
                        @mouseleave="hideTooltip"
                    >
                        <div class="flex items-center">
                            <Globe class="h-5 w-5" />
                            <span v-show="!isCollapsed || isMobile" class="ml-3">GrowBuilder</span>
                        </div>
                        <ChevronDown v-show="!isCollapsed || isMobile" class="h-5 w-5 transform transition-transform duration-200"
                            :class="{ 'rotate-180': showSubmenu.growBuilder }" />
                    </button>

                    <div v-if="showSubmenu.growBuilder" v-show="!isCollapsed || isMobile" class="mt-2 pl-4 space-y-1">
                        <Link v-for="item in growBuilderNavItems" :key="item.title"
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

                <!-- GrowBackup Section -->
                <div class="pt-2">
                    <button @click="toggleSubmenu('growBackup')"
                        :class="[
                            'w-full flex items-center justify-between px-4 py-2 transition-colors duration-200',
                            'hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none',
                            'text-gray-700 dark:text-gray-300'
                        ]"
                        @mouseenter="showItemTooltip($event, 'GrowBackup')"
                        @mouseleave="hideTooltip"
                    >
                        <div class="flex items-center">
                            <Cloud class="h-5 w-5" />
                            <span v-show="!isCollapsed || isMobile" class="ml-3">GrowBackup</span>
                        </div>
                        <ChevronDown v-show="!isCollapsed || isMobile" class="h-5 w-5 transform transition-transform duration-200"
                            :class="{ 'rotate-180': showSubmenu.growBackup }" />
                    </button>

                    <div v-if="showSubmenu.growBackup" v-show="!isCollapsed || isMobile" class="mt-2 pl-4 space-y-1">
                        <Link v-for="item in growBackupNavItems" :key="item.title"
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

                <!-- Marketplace Section -->
                <div class="pt-2">
                    <Link
                        :href="safeRoute('admin.marketplace.dashboard')"
                        :class="[
                            'w-full flex items-center px-4 py-2 transition-colors duration-200',
                            'hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none',
                            isUrlActive('/admin/marketplace') ? 'text-blue-600 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-600' : 'text-gray-700 dark:text-gray-300'
                        ]"
                        @mouseenter="showItemTooltip($event, 'Marketplace')"
                        @mouseleave="hideTooltip"
                    >
                        <ShoppingBag class="h-5 w-5" />
                        <span v-show="!isCollapsed || isMobile" class="ml-3">Marketplace</span>
                    </Link>
                </div>

                <!-- Reports Section -->
                <div class="pt-2">
                    <button @click="toggleSubmenu('reports')"
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
                        <ChevronDown v-show="!isCollapsed || isMobile" class="h-5 w-5 transform transition-transform duration-200"
                            :class="{ 'rotate-180': showSubmenu.reports }" />
                    </button>

                    <div v-if="showSubmenu.reports" v-show="!isCollapsed || isMobile" class="mt-2 pl-4 space-y-1">
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

                <!-- Employees Section -->
                <div class="pt-2">
                    <button @click="isCollapsed ? toggleSidebar() : toggleSubmenu('employees')"
                        :class="[
                            'w-full flex items-center justify-between px-4 py-2 transition-colors duration-200',
                            'hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none',
                            'text-gray-700 dark:text-gray-300'
                        ]"
                        @mouseenter="showItemTooltip($event, 'Employees')"
                        @mouseleave="hideTooltip"
                    >
                        <div class="flex items-center">
                            <UserCheck class="h-5 w-5" />
                            <span v-show="!isCollapsed || isMobile" class="ml-3">Employees</span>
                        </div>
                        <ChevronDown v-show="!isCollapsed" class="h-5 w-5 transform transition-transform duration-200"
                            :class="{ 'rotate-180': showSubmenu.employees }" />
                    </button>

                    <div v-if="showSubmenu.employees && !isCollapsed" class="mt-2 pl-4 space-y-1">
                        <Link v-for="item in employeeNavItems" :key="item.title"
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
                    <button @click="isCollapsed ? toggleSidebar() : toggleSubmenu('communication')"
                        :class="[
                            'w-full flex items-center justify-between px-4 py-2 transition-colors duration-200',
                            'hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none',
                            'text-gray-700 dark:text-gray-300'
                        ]"
                        @mouseenter="showItemTooltip($event, 'Communication')"
                        @mouseleave="hideTooltip"
                    >
                        <div class="flex items-center">
                            <MailIcon class="h-5 w-5" />
                            <span v-show="!isCollapsed || isMobile" class="ml-3">Communication</span>
                            <span v-if="page.props.messagingData?.unread_count > 0 && (!isCollapsed || isMobile)"
                                class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                            >
                                {{ page.props.messagingData.unread_count }}
                            </span>
                        </div>
                        <ChevronDown v-show="!isCollapsed" class="h-5 w-5 transform transition-transform duration-200"
                            :class="{ 'rotate-180': showSubmenu.communication }" />
                    </button>

                    <div v-if="showSubmenu.communication && !isCollapsed" class="mt-2 pl-4 space-y-1">
                        <Link v-for="item in communicationNavItems" :key="item.title"
                            :href="item.href"
                            :class="[
                                'flex items-center px-4 py-2 transition-colors duration-200 text-sm',
                                'hover:bg-gray-100 dark:hover:bg-gray-800',
                                isUrlActive(item.href) ? 'text-blue-600 border-l-4 border-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-700 dark:text-gray-300'
                            ]"
                        >
                            <component :is="item.icon === 'MailIcon' ? MailIcon : item.icon === 'SendIcon' ? SendIcon : item.icon === 'LiveChatIcon' ? LiveChatIcon : TicketIcon" class="h-4 w-4" />
                            <span class="ml-3">{{ item.title }}</span>
                            <span v-if="item.title === 'Live Support' && page.props.supportStats?.open > 0"
                                class="ml-auto inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ page.props.supportStats.open }}
                            </span>
                            <span v-if="item.badge" class="ml-auto text-xs bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded">{{ item.badge }}</span>
                        </Link>
                    </div>
                </div>

                <!-- System Section -->
                <div class="pt-2">
                    <button @click="isCollapsed ? toggleSidebar() : toggleSubmenu('system')"
                        :class="[
                            'w-full flex items-center justify-between px-4 py-2 transition-colors duration-200',
                            'hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none',
                            'text-gray-700 dark:text-gray-300'
                        ]"
                        @mouseenter="showItemTooltip($event, 'System')"
                        @mouseleave="hideTooltip"
                    >
                        <div class="flex items-center">
                            <Shield class="h-5 w-5" />
                            <span v-show="!isCollapsed || isMobile" class="ml-3">System</span>
                        </div>
                        <ChevronDown v-show="!isCollapsed" class="h-5 w-5 transform transition-transform duration-200"
                            :class="{ 'rotate-180': showSubmenu.system }" />
                    </button>

                    <div v-if="showSubmenu.system && !isCollapsed" class="mt-2 pl-4 space-y-1">
                        <Link v-for="item in systemNavItems" :key="item.title"
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
            </nav>
        </div>

        <!-- Footer with User -->
        <div class="border-t border-gray-200 dark:border-gray-800 py-2 mt-auto">
            <CustomNavUser />
        </div>
    </aside>

    <!-- Mobile overlay -->
    <div
        v-if="!isCollapsed && isMobile"
        @click="toggleSidebar"
        class="fixed inset-0 bg-black bg-opacity-50 z-30"
    ></div>

    <!-- Mobile hamburger -->
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
