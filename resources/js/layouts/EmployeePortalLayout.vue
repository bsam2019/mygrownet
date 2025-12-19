<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import NotificationToast from '@/components/Employee/NotificationToast.vue';
import UnifiedLiveChatWidget from '@/components/Support/UnifiedLiveChatWidget.vue';
import { useEmployeeNotifications } from '@/composables/useEmployeeNotifications';
import {
    HomeIcon, ClipboardDocumentListIcon, FlagIcon, CalendarDaysIcon, ClockIcon,
    DocumentTextIcon, UsersIcon, BellIcon, UserCircleIcon, Bars3Icon, XMarkIcon,
    ArrowRightOnRectangleIcon, ChevronDownIcon, ChevronRightIcon, BanknotesIcon,
    MegaphoneIcon, BuildingOffice2Icon, ChartBarIcon, AcademicCapIcon,
    ReceiptPercentIcon, TicketIcon, CalendarIcon, WifiIcon, ChatBubbleLeftRightIcon,
    EnvelopeIcon, CreditCardIcon, FolderIcon, DocumentChartBarIcon, ShieldCheckIcon,
} from '@heroicons/vue/24/outline';
import { BellIcon as BellIconSolid } from '@heroicons/vue/24/solid';

const iconMap: Record<string, any> = {
    ChatBubbleLeftRightIcon, EnvelopeIcon, DocumentTextIcon, CreditCardIcon,
    BanknotesIcon, UsersIcon, ChartBarIcon, DocumentChartBarIcon, FolderIcon, ShieldCheckIcon,
};

interface Props { unreadNotifications?: number; }
const props = withDefaults(defineProps<Props>(), { unreadNotifications: 0 });

const page = usePage();
const user = computed(() => page.props.auth?.user);
const employee = computed(() => page.props.employee);
const delegatedNavigation = computed(() => (page.props.delegatedNavigation as any[]) || []);
const hasDelegatedFunctions = computed(() => (page.props.hasDelegatedFunctions as boolean) || false);
const employeeId = computed(() => employee.value?.id || null);

const { isConnected, recentToasts, markAsRead, dismissToast } = useEmployeeNotifications(employeeId);
const unreadCount = computed(() => props.unreadNotifications || (page.props.unreadNotifications as number) || 0);

const sidebarOpen = ref(false);
const sidebarCollapsed = ref(false); // Desktop sidebar collapse state
const userDropdownOpen = ref(false);
const notificationDropdownOpen = ref(false);

// Collapsible groups state
const expandedGroups = ref<Record<string, boolean>>({
    'work': true,
    'hr': false,
    'company': false,
    'support-agent': false,
    'delegated': false,
});
const toggleGroup = (group: string) => { expandedGroups.value[group] = !expandedGroups.value[group]; };

// Flatten all delegated items into a single list
const allDelegatedItems = computed(() => {
    const items: any[] = [];
    for (const category of delegatedNavigation.value) {
        for (const item of category.items) {
            items.push(item);
        }
    }
    return items;
});
const isDelegatedActive = computed(() => allDelegatedItems.value.some(item => isActive(item.href)));

// Navigation grouped
const navGroups = [
    {
        id: 'work',
        name: 'My Work',
        items: [
            { name: 'Dashboard', href: 'employee.portal.dashboard', icon: HomeIcon },
            { name: 'Tasks', href: 'employee.portal.tasks.index', icon: ClipboardDocumentListIcon },
            { name: 'Goals', href: 'employee.portal.goals.index', icon: FlagIcon },
            { name: 'Calendar', href: 'employee.portal.calendar.index', icon: CalendarIcon },
        ],
    },
    {
        id: 'hr',
        name: 'HR & Benefits',
        items: [
            { name: 'Time Off', href: 'employee.portal.time-off.index', icon: CalendarDaysIcon },
            { name: 'Attendance', href: 'employee.portal.attendance.index', icon: ClockIcon },
            { name: 'Performance', href: 'employee.portal.performance.index', icon: ChartBarIcon },
            { name: 'Training', href: 'employee.portal.training.index', icon: AcademicCapIcon },
            { name: 'Payslips', href: 'employee.portal.payslips.index', icon: BanknotesIcon },
            { name: 'Expenses', href: 'employee.portal.expenses.index', icon: ReceiptPercentIcon },
            { name: 'Documents', href: 'employee.portal.documents', icon: DocumentTextIcon },
        ],
    },
    {
        id: 'company',
        name: 'Company',
        items: [
            { name: 'Announcements', href: 'employee.portal.announcements.index', icon: MegaphoneIcon },
            { name: 'Directory', href: 'employee.portal.directory.index', icon: BuildingOffice2Icon },
            { name: 'My Team', href: 'employee.portal.team', icon: UsersIcon },
            { name: 'Help Desk', href: 'employee.portal.support.index', icon: TicketIcon },
        ],
    },
];

const supportAgentNav = [
    { name: 'Support Agent', href: 'employee.portal.support-agent.dashboard', icon: ChatBubbleLeftRightIcon },
];

const isActive = (routeName: string) => route().current(routeName) || route().current(routeName + '.*');
const isGroupActive = (items: any[]) => items.some(item => isActive(item.href));
const handleLogout = () => router.post(route('logout'));
const getInitials = (name: string) => {
    if (!name) return '?';
    const parts = name.split(' ');
    return parts.length >= 2 ? (parts[0][0] + parts[1][0]).toUpperCase() : name.substring(0, 2).toUpperCase();
};

const closeDropdowns = (e: MouseEvent) => {
    const target = e.target as HTMLElement;
    if (!target.closest('.user-dropdown-container')) userDropdownOpen.value = false;
    if (!target.closest('.notification-dropdown-container')) notificationDropdownOpen.value = false;
};
onMounted(() => document.addEventListener('click', closeDropdowns));
onUnmounted(() => document.removeEventListener('click', closeDropdowns));
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Mobile sidebar backdrop -->
        <div v-if="sidebarOpen" class="fixed inset-0 z-40 bg-gray-900/50 lg:hidden" @click="sidebarOpen = false"></div>

        <!-- Mobile sidebar -->
        <div :class="['fixed inset-y-0 left-0 z-50 w-72 bg-white shadow-xl transform transition-transform duration-300 lg:hidden', sidebarOpen ? 'translate-x-0' : '-translate-x-full']">
            <div class="flex items-center justify-between p-4 border-b">
                <div class="flex items-center gap-2">
                    <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-blue-600">
                        <AppLogoIcon class="size-5 fill-current text-white" />
                    </div>
                    <span class="text-lg font-bold text-gray-900">Workspace</span>
                </div>
                <button @click="sidebarOpen = false" class="p-2 rounded-lg hover:bg-gray-100" aria-label="Close sidebar">
                    <XMarkIcon class="h-6 w-6 text-gray-500" aria-hidden="true" />
                </button>
            </div>
            <nav class="p-3 space-y-1 overflow-y-auto max-h-[calc(100vh-80px)]">
                <!-- Collapsible Groups (Mobile) -->
                <div v-for="group in navGroups" :key="group.id" class="mb-1">
                    <button @click="toggleGroup(group.id)" class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider hover:bg-gray-50 rounded-lg">
                        <span>{{ group.name }}</span>
                        <ChevronDownIcon :class="['h-4 w-4 transition-transform', expandedGroups[group.id] ? 'rotate-180' : '']" aria-hidden="true" />
                    </button>
                    <div v-show="expandedGroups[group.id] || isGroupActive(group.items)" class="mt-1 space-y-0.5">
                        <Link v-for="item in group.items" :key="item.name" :href="route(item.href)" :class="['flex items-center px-3 py-2 rounded-lg text-sm transition-colors', isActive(item.href) ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50']" @click="sidebarOpen = false">
                            <component :is="item.icon" class="h-5 w-5 mr-3 flex-shrink-0" aria-hidden="true" />
                            {{ item.name }}
                        </Link>
                    </div>
                </div>
                <!-- Support Agent -->
                <div class="pt-3 mt-3 border-t border-gray-200">
                    <button @click="toggleGroup('support-agent')" class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-emerald-600 uppercase tracking-wider hover:bg-gray-50 rounded-lg">
                        <span>Support Agent</span>
                        <ChevronDownIcon :class="['h-4 w-4 transition-transform', expandedGroups['support-agent'] ? 'rotate-180' : '']" aria-hidden="true" />
                    </button>
                    <div v-show="expandedGroups['support-agent']" class="mt-1">
                        <Link v-for="item in supportAgentNav" :key="item.name" :href="route(item.href)" :class="['flex items-center px-3 py-2 rounded-lg text-sm transition-colors', isActive(item.href) ? 'bg-emerald-50 text-emerald-700 font-medium' : 'text-gray-600 hover:bg-gray-50']" @click="sidebarOpen = false">
                            <component :is="item.icon" class="h-5 w-5 mr-3" aria-hidden="true" />
                            {{ item.name }}
                        </Link>
                    </div>
                </div>
                <!-- Delegated Functions (Mobile) - Single Collapsible -->
                <div v-if="hasDelegatedFunctions" class="pt-3 mt-3 border-t border-gray-200">
                    <button @click="toggleGroup('delegated')" class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-purple-600 uppercase tracking-wider hover:bg-gray-50 rounded-lg">
                        <span class="flex items-center gap-1"><ShieldCheckIcon class="h-3 w-3" />Delegated</span>
                        <ChevronDownIcon :class="['h-4 w-4 transition-transform', expandedGroups['delegated'] ? 'rotate-180' : '']" aria-hidden="true" />
                    </button>
                    <div v-show="expandedGroups['delegated'] || isDelegatedActive" class="mt-1 space-y-0.5">
                        <Link v-for="item in allDelegatedItems" :key="item.href" :href="route(item.href)" :class="['flex items-center px-3 py-2 rounded-lg text-sm transition-colors', isActive(item.href) ? 'bg-purple-50 text-purple-700 font-medium' : 'text-gray-600 hover:bg-gray-50']" @click="sidebarOpen = false">
                            <component :is="iconMap[item.icon] || ShieldCheckIcon" class="h-5 w-5 mr-3" aria-hidden="true" />
                            {{ item.name }}
                        </Link>
                    </div>
                </div>
            </nav>
        </div>

        <!-- Desktop sidebar -->
        <div :class="['hidden lg:fixed lg:inset-y-0 lg:flex lg:flex-col transition-all duration-300', sidebarCollapsed ? 'lg:w-16' : 'lg:w-64']">
            <div class="flex flex-col h-full bg-white border-r border-gray-200">
                <!-- Logo with collapse toggle -->
                <div :class="['flex-shrink-0 flex items-center h-16 border-b border-gray-200', sidebarCollapsed ? 'justify-center px-2' : 'justify-between px-4']">
                    <Link v-if="!sidebarCollapsed" :href="route('employee.portal.dashboard')" class="flex items-center gap-3">
                        <div class="flex aspect-square size-9 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 shadow-md">
                            <AppLogoIcon class="size-6 fill-current text-white" />
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-gray-900">MyGrowNet</span>
                            <span class="text-xs text-gray-500">Workspace</span>
                        </div>
                    </Link>
                    <button 
                        @click="sidebarCollapsed = !sidebarCollapsed" 
                        :class="['p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors', sidebarCollapsed ? 'bg-gray-50' : '']"
                        :aria-label="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                    >
                        <ChevronRightIcon :class="['h-5 w-5 transition-transform duration-200', sidebarCollapsed ? '' : 'rotate-180']" aria-hidden="true" />
                    </button>
                </div>

                <!-- Navigation - Scrollable with Collapsible Groups -->
                <nav class="flex-1 p-3 space-y-1 overflow-y-auto min-h-0">
                    <!-- Expanded mode: show collapsible groups -->
                    <template v-if="!sidebarCollapsed">
                        <div v-for="group in navGroups" :key="group.id" class="mb-1">
                            <button @click="toggleGroup(group.id)" class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider hover:bg-gray-50 rounded-lg transition-colors">
                                <span>{{ group.name }}</span>
                                <ChevronDownIcon :class="['h-4 w-4 transition-transform duration-200', expandedGroups[group.id] ? 'rotate-180' : '']" aria-hidden="true" />
                            </button>
                            <Transition enter-active-class="transition-all duration-200 ease-out" enter-from-class="opacity-0 max-h-0" enter-to-class="opacity-100 max-h-96" leave-active-class="transition-all duration-150 ease-in" leave-from-class="opacity-100 max-h-96" leave-to-class="opacity-0 max-h-0">
                                <div v-show="expandedGroups[group.id] || isGroupActive(group.items)" class="mt-1 space-y-0.5 overflow-hidden">
                                    <Link v-for="item in group.items" :key="item.name" :href="route(item.href)" :class="['flex items-center px-3 py-2 rounded-lg text-sm transition-colors', isActive(item.href) ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50']">
                                        <component :is="item.icon" class="h-5 w-5 mr-3 flex-shrink-0" aria-hidden="true" />
                                        {{ item.name }}
                                    </Link>
                                </div>
                            </Transition>
                        </div>
                    </template>
                    <!-- Collapsed mode: show icons only -->
                    <template v-else>
                        <div v-for="group in navGroups" :key="group.id" class="space-y-1">
                            <Link 
                                v-for="item in group.items" 
                                :key="item.name" 
                                :href="route(item.href)" 
                                :class="['flex items-center justify-center p-2 rounded-lg transition-colors', isActive(item.href) ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50']"
                                :title="item.name"
                            >
                                <component :is="item.icon" class="h-5 w-5" aria-hidden="true" />
                            </Link>
                        </div>
                    </template>
                    
                    <!-- Support Agent Section -->
                    <template v-if="!sidebarCollapsed">
                        <div class="pt-3 mt-3 border-t border-gray-200">
                            <button @click="toggleGroup('support-agent')" class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-emerald-600 uppercase tracking-wider hover:bg-gray-50 rounded-lg">
                                <span>Support Agent</span>
                                <ChevronDownIcon :class="['h-4 w-4 transition-transform duration-200', expandedGroups['support-agent'] ? 'rotate-180' : '']" aria-hidden="true" />
                            </button>
                            <div v-show="expandedGroups['support-agent']" class="mt-1">
                                <Link v-for="item in supportAgentNav" :key="item.name" :href="route(item.href)" :class="['flex items-center px-3 py-2 rounded-lg text-sm transition-colors', isActive(item.href) ? 'bg-emerald-50 text-emerald-700 font-medium' : 'text-gray-600 hover:bg-gray-50']">
                                    <component :is="item.icon" class="h-5 w-5 mr-3" aria-hidden="true" />
                                    {{ item.name }}
                                </Link>
                            </div>
                        </div>

                        <!-- Delegated Functions Section - Single Collapsible -->
                        <div v-if="hasDelegatedFunctions" class="pt-3 mt-3 border-t border-gray-200">
                            <button @click="toggleGroup('delegated')" class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-purple-600 uppercase tracking-wider hover:bg-gray-50 rounded-lg">
                                <span class="flex items-center gap-1"><ShieldCheckIcon class="h-3 w-3" />Delegated</span>
                                <ChevronDownIcon :class="['h-4 w-4 transition-transform duration-200', expandedGroups['delegated'] ? 'rotate-180' : '']" aria-hidden="true" />
                            </button>
                            <Transition enter-active-class="transition-all duration-200 ease-out" enter-from-class="opacity-0 max-h-0" enter-to-class="opacity-100 max-h-96" leave-active-class="transition-all duration-150 ease-in" leave-from-class="opacity-100 max-h-96" leave-to-class="opacity-0 max-h-0">
                                <div v-show="expandedGroups['delegated'] || isDelegatedActive" class="mt-1 space-y-0.5 overflow-hidden">
                                    <Link v-for="item in allDelegatedItems" :key="item.href" :href="route(item.href)" :class="['flex items-center px-3 py-2 rounded-lg text-sm transition-colors', isActive(item.href) ? 'bg-purple-50 text-purple-700 font-medium' : 'text-gray-600 hover:bg-gray-50']">
                                        <component :is="iconMap[item.icon] || ShieldCheckIcon" class="h-5 w-5 mr-3" aria-hidden="true" />
                                        {{ item.name }}
                                        <span v-if="item.requires_approval" class="ml-auto text-xs text-amber-500" title="Requires approval">•</span>
                                    </Link>
                                </div>
                            </Transition>
                        </div>
                    </template>
                    <!-- Collapsed mode: Support Agent & Delegated icons -->
                    <template v-else>
                        <div class="pt-3 mt-3 border-t border-gray-200 space-y-1">
                            <Link v-for="item in supportAgentNav" :key="item.name" :href="route(item.href)" :class="['flex items-center justify-center p-2 rounded-lg transition-colors', isActive(item.href) ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-50']" :title="item.name">
                                <component :is="item.icon" class="h-5 w-5" aria-hidden="true" />
                            </Link>
                            <template v-if="hasDelegatedFunctions">
                                <Link v-for="item in allDelegatedItems" :key="item.href" :href="route(item.href)" :class="['flex items-center justify-center p-2 rounded-lg transition-colors', isActive(item.href) ? 'bg-purple-50 text-purple-700' : 'text-gray-600 hover:bg-gray-50']" :title="item.name">
                                    <component :is="iconMap[item.icon] || ShieldCheckIcon" class="h-5 w-5" aria-hidden="true" />
                                </Link>
                            </template>
                        </div>
                    </template>
                </nav>

                <!-- Back to Main App -->
                <div class="flex-shrink-0 p-2 border-t border-gray-200">
                    <Link :href="route('dashboard')" :class="['flex items-center rounded-lg transition-colors text-gray-600 hover:text-gray-900 hover:bg-gray-50', sidebarCollapsed ? 'justify-center p-2' : 'px-3 py-2 text-sm']" :title="sidebarCollapsed ? 'Back to MyGrowNet' : undefined">
                        <ArrowRightOnRectangleIcon class="h-5 w-5 rotate-180" :class="{ 'mr-3': !sidebarCollapsed }" aria-hidden="true" />
                        <span v-if="!sidebarCollapsed">Back to MyGrowNet</span>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div :class="['transition-all duration-300', sidebarCollapsed ? 'lg:pl-16' : 'lg:pl-64']">
            <!-- Top navigation bar -->
            <header class="sticky top-0 z-30 bg-white border-b border-gray-200 shadow-sm">
                <div class="flex items-center justify-between h-16 px-4 lg:px-6">
                    <!-- Mobile: Hamburger + Logo -->
                    <div class="flex items-center lg:hidden">
                        <button @click="sidebarOpen = true" class="p-2 -ml-2 rounded-lg hover:bg-gray-100" aria-label="Open sidebar">
                            <Bars3Icon class="h-6 w-6 text-gray-500" aria-hidden="true" />
                        </button>
                        <div class="flex items-center gap-2 ml-2">
                            <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-blue-600">
                                <AppLogoIcon class="size-5 fill-current text-white" />
                            </div>
                            <span class="text-lg font-bold text-gray-900">Workspace</span>
                        </div>
                    </div>
                    <!-- Desktop: Page title -->
                    <div class="hidden lg:flex lg:items-center lg:gap-4">
                        <h1 class="text-lg font-semibold text-gray-900"><slot name="header">Workspace</slot></h1>
                    </div>
                    <!-- Right side: Notifications + User dropdown -->
                    <div class="flex items-center gap-2">
                        <!-- Notification Bell -->
                        <div class="notification-dropdown-container relative">
                            <button @click.stop="notificationDropdownOpen = !notificationDropdownOpen" class="relative p-2 rounded-lg hover:bg-gray-100 transition-colors" aria-label="View notifications">
                                <component :is="unreadCount > 0 ? BellIconSolid : BellIcon" :class="['h-6 w-6', unreadCount > 0 ? 'text-blue-600' : 'text-gray-500']" aria-hidden="true" />
                                <span v-if="unreadCount > 0" class="absolute -top-0.5 -right-0.5 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-xs font-bold text-white bg-red-500 rounded-full">{{ unreadCount > 99 ? '99+' : unreadCount }}</span>
                            </button>
                            <Transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
                                <div v-if="notificationDropdownOpen" class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
                                    <div class="px-4 py-2 border-b border-gray-100">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                                            <span v-if="unreadCount > 0" class="text-xs text-blue-600 font-medium">{{ unreadCount }} new</span>
                                        </div>
                                    </div>
                                    <div class="max-h-64 overflow-y-auto">
                                        <div v-if="unreadCount === 0" class="px-4 py-8 text-center text-gray-500 text-sm">No new notifications</div>
                                        <div v-else class="px-4 py-3 text-sm text-gray-600">You have {{ unreadCount }} unread notification{{ unreadCount > 1 ? 's' : '' }}</div>
                                    </div>
                                    <div class="px-4 py-2 border-t border-gray-100">
                                        <Link :href="route('employee.portal.notifications.index')" class="block text-center text-sm text-blue-600 hover:text-blue-700 font-medium" @click="notificationDropdownOpen = false">View all notifications</Link>
                                    </div>
                                </div>
                            </Transition>
                        </div>

                        <!-- User dropdown -->
                        <div class="user-dropdown-container relative">
                            <button @click.stop="userDropdownOpen = !userDropdownOpen" class="flex items-center gap-2 p-1.5 pr-3 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-sm">
                                    <span class="text-sm font-medium text-white">{{ getInitials(user?.name || '') }}</span>
                                </div>
                                <div class="hidden sm:block text-left">
                                    <p class="text-sm font-medium text-gray-900 truncate max-w-[120px]">{{ employee?.full_name?.split(' ')[0] || user?.name?.split(' ')[0] }}</p>
                                </div>
                                <ChevronDownIcon :class="['h-4 w-4 text-gray-400 transition-transform hidden sm:block', userDropdownOpen ? 'rotate-180' : '']" aria-hidden="true" />
                            </button>
                            <Transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
                                <div v-if="userDropdownOpen" class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
                                    <div class="px-4 py-3 border-b border-gray-100">
                                        <p class="text-sm font-medium text-gray-900">{{ employee?.full_name || user?.name }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ user?.email }}</p>
                                        <p v-if="employee" class="text-xs text-blue-600 mt-1">{{ employee.position?.title || 'Team Member' }}</p>
                                    </div>
                                    <div class="py-1">
                                        <Link :href="route('employee.portal.profile.index')" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" @click="userDropdownOpen = false">
                                            <UserCircleIcon class="h-5 w-5 mr-3 text-gray-400" aria-hidden="true" />My Profile
                                        </Link>
                                        <Link :href="route('employee.portal.notifications.index')" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" @click="userDropdownOpen = false">
                                            <BellIcon class="h-5 w-5 mr-3 text-gray-400" aria-hidden="true" />Notifications
                                            <span v-if="unreadCount > 0" class="ml-auto text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full">{{ unreadCount }}</span>
                                        </Link>
                                        <Link :href="route('dashboard')" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" @click="userDropdownOpen = false">
                                            <HomeIcon class="h-5 w-5 mr-3 text-gray-400" aria-hidden="true" />Back to MyGrowNet
                                        </Link>
                                    </div>
                                    <div class="border-t border-gray-100 pt-1">
                                        <button @click="handleLogout" class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                            <ArrowRightOnRectangleIcon class="h-5 w-5 mr-3" aria-hidden="true" />Sign Out
                                        </button>
                                    </div>
                                </div>
                            </Transition>
                        </div>
                    </div>
                </div>
            </header>
            <!-- Page content -->
            <main class="p-4 lg:p-6"><slot /></main>
        </div>

        <!-- Toasts and widgets -->
        <NotificationToast :notifications="recentToasts" @dismiss="dismissToast" @click="(n) => { markAsRead(n.id); router.visit(n.action_url || route('employee.portal.notifications.index')); }" />
        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="transform translate-y-full opacity-0" enter-to-class="transform translate-y-0 opacity-100" leave-active-class="transition ease-in duration-200" leave-from-class="transform translate-y-0 opacity-100" leave-to-class="transform translate-y-full opacity-0">
            <div v-if="employee && !isConnected" class="fixed bottom-4 left-4 z-40 flex items-center gap-2 px-3 py-2 bg-amber-100 text-amber-800 rounded-lg shadow-md text-sm">
                <WifiIcon class="h-4 w-4" aria-hidden="true" /><span>Connecting to real-time updates...</span>
            </div>
        </Transition>
        <UnifiedLiveChatWidget v-if="employee" user-type="employee" :user-id="employee.id" :user-name="employee.full_name" />
    </div>
</template>
