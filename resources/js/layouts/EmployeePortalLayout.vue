<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import NotificationToast from '@/components/Employee/NotificationToast.vue';
import UnifiedLiveChatWidget from '@/components/Support/UnifiedLiveChatWidget.vue';
import { useEmployeeNotifications } from '@/composables/useEmployeeNotifications';
import {
    HomeIcon,
    ClipboardDocumentListIcon,
    FlagIcon,
    CalendarDaysIcon,
    ClockIcon,
    DocumentTextIcon,
    UsersIcon,
    BellIcon,
    UserCircleIcon,
    Bars3Icon,
    XMarkIcon,
    ArrowRightOnRectangleIcon,
    Cog6ToothIcon,
    ChevronDownIcon,
    MagnifyingGlassIcon,
    BanknotesIcon,
    MegaphoneIcon,
    BuildingOffice2Icon,
    ChartBarIcon,
    AcademicCapIcon,
    ReceiptPercentIcon,
    TicketIcon,
    CalendarIcon,
    WifiIcon,
} from '@heroicons/vue/24/outline';
import { BellIcon as BellIconSolid, WifiIcon as WifiIconSolid } from '@heroicons/vue/24/solid';

interface Props {
    unreadNotifications?: number;
}

const props = withDefaults(defineProps<Props>(), {
    unreadNotifications: 0,
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const employee = computed(() => page.props.employee);

// Employee ID as a computed ref for the notifications composable
const employeeId = computed(() => employee.value?.id || null);

// Use the notifications composable with the reactive employeeId ref
const {
    notifications: realtimeNotifications,
    unreadCount: realtimeUnreadCount,
    isConnected,
    recentToasts,
    markAsRead,
    markAllAsRead,
    dismissToast,
} = useEmployeeNotifications(employeeId);

// Get unread notifications from props or real-time count
const unreadCount = computed(() => {
    // Prefer real-time count if connected, otherwise use props
    if (isConnected.value && realtimeUnreadCount.value > 0) {
        return realtimeUnreadCount.value;
    }
    return props.unreadNotifications || (page.props.unreadNotifications as number) || 0;
});

const sidebarOpen = ref(false);
const userDropdownOpen = ref(false);
const notificationDropdownOpen = ref(false);

// Close dropdowns when clicking outside
const closeDropdowns = (e: MouseEvent) => {
    const target = e.target as HTMLElement;
    if (!target.closest('.user-dropdown-container')) {
        userDropdownOpen.value = false;
    }
    if (!target.closest('.notification-dropdown-container')) {
        notificationDropdownOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', closeDropdowns);
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdowns);
});

const navigation = [
    { name: 'Dashboard', href: 'employee.portal.dashboard', icon: HomeIcon },
    { name: 'My Tasks', href: 'employee.portal.tasks.index', icon: ClipboardDocumentListIcon },
    { name: 'My Goals', href: 'employee.portal.goals.index', icon: FlagIcon },
    { name: 'Calendar', href: 'employee.portal.calendar.index', icon: CalendarIcon },
    { name: 'Time Off', href: 'employee.portal.time-off.index', icon: CalendarDaysIcon },
    { name: 'Attendance', href: 'employee.portal.attendance.index', icon: ClockIcon },
    { name: 'Performance', href: 'employee.portal.performance.index', icon: ChartBarIcon },
    { name: 'Training', href: 'employee.portal.training.index', icon: AcademicCapIcon },
    { name: 'Payslips', href: 'employee.portal.payslips.index', icon: BanknotesIcon },
    { name: 'Expenses', href: 'employee.portal.expenses.index', icon: ReceiptPercentIcon },
    { name: 'Documents', href: 'employee.portal.documents', icon: DocumentTextIcon },
    { name: 'Announcements', href: 'employee.portal.announcements.index', icon: MegaphoneIcon },
    { name: 'Directory', href: 'employee.portal.directory.index', icon: BuildingOffice2Icon },
    { name: 'My Team', href: 'employee.portal.team', icon: UsersIcon },
    { name: 'Help Desk', href: 'employee.portal.support.index', icon: TicketIcon },
];

// Support Agent navigation (for employees who handle member/investor support)
import { ChatBubbleLeftRightIcon } from '@heroicons/vue/24/outline';

const supportAgentNavigation = [
    { name: 'Support Agent', href: 'employee.portal.support-agent.dashboard', icon: ChatBubbleLeftRightIcon },
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
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Mobile sidebar backdrop -->
        <div v-if="sidebarOpen" 
            class="fixed inset-0 z-40 bg-gray-900/50 lg:hidden"
            @click="sidebarOpen = false">
        </div>

        <!-- Mobile sidebar -->
        <div :class="[
            'fixed inset-y-0 left-0 z-50 w-72 bg-white shadow-xl transform transition-transform duration-300 lg:hidden',
            sidebarOpen ? 'translate-x-0' : '-translate-x-full'
        ]">
            <div class="flex items-center justify-between p-4 border-b">
                <div class="flex items-center gap-2">
                    <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-blue-600">
                        <AppLogoIcon class="size-5 fill-current text-white" />
                    </div>
                    <span class="text-lg font-bold text-gray-900">Employee Portal</span>
                </div>
                <button @click="sidebarOpen = false" class="p-2 rounded-lg hover:bg-gray-100" aria-label="Close sidebar">
                    <XMarkIcon class="h-6 w-6 text-gray-500" aria-hidden="true" />
                </button>
            </div>
            <nav class="p-4 space-y-1">
                <Link v-for="item in navigation" :key="item.name"
                    :href="route(item.href)"
                    :class="[
                        'flex items-center px-4 py-3 rounded-lg transition-colors',
                        isActive(item.href) 
                            ? 'bg-blue-50 text-blue-700' 
                            : 'text-gray-600 hover:bg-gray-50'
                    ]"
                    @click="sidebarOpen = false">
                    <component :is="item.icon" class="h-5 w-5 mr-3" aria-hidden="true" />
                    {{ item.name }}
                </Link>
                
                <!-- Support Agent Section -->
                <div class="pt-4 mt-4 border-t border-gray-200">
                    <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Support Agent</p>
                    <Link v-for="item in supportAgentNavigation" :key="item.name"
                        :href="route(item.href)"
                        :class="[
                            'flex items-center px-4 py-3 rounded-lg transition-colors',
                            isActive(item.href) 
                                ? 'bg-emerald-50 text-emerald-700' 
                                : 'text-gray-600 hover:bg-gray-50'
                        ]"
                        @click="sidebarOpen = false">
                        <component :is="item.icon" class="h-5 w-5 mr-3" aria-hidden="true" />
                        {{ item.name }}
                    </Link>
                </div>
            </nav>
        </div>

        <!-- Desktop sidebar -->
        <div class="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col">
            <div class="flex flex-col h-full bg-white border-r border-gray-200">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center h-16 px-6 border-b border-gray-200">
                    <Link :href="route('employee.portal.dashboard')" class="flex items-center gap-3">
                        <div class="flex aspect-square size-9 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 shadow-md">
                            <AppLogoIcon class="size-6 fill-current text-white" />
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-gray-900">MyGrowNet</span>
                            <span class="text-xs text-gray-500">Employee Portal</span>
                        </div>
                    </Link>
                </div>

                <!-- Navigation - Scrollable -->
                <nav class="flex-1 p-4 space-y-1 overflow-y-auto min-h-0">
                    <Link v-for="item in navigation" :key="item.name"
                        :href="route(item.href)"
                        :class="[
                            'flex items-center px-4 py-3 rounded-lg transition-colors',
                            isActive(item.href) 
                                ? 'bg-blue-50 text-blue-700 font-medium' 
                                : 'text-gray-600 hover:bg-gray-50'
                        ]">
                        <component :is="item.icon" class="h-5 w-5 mr-3" aria-hidden="true" />
                        {{ item.name }}
                    </Link>
                    
                    <!-- Support Agent Section -->
                    <div class="pt-4 mt-4 border-t border-gray-200">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Support Agent</p>
                        <Link v-for="item in supportAgentNavigation" :key="item.name"
                            :href="route(item.href)"
                            :class="[
                                'flex items-center px-4 py-3 rounded-lg transition-colors',
                                isActive(item.href) 
                                    ? 'bg-emerald-50 text-emerald-700 font-medium' 
                                    : 'text-gray-600 hover:bg-gray-50'
                            ]">
                            <component :is="item.icon" class="h-5 w-5 mr-3" aria-hidden="true" />
                            {{ item.name }}
                        </Link>
                    </div>
                </nav>

                <!-- Back to Main App -->
                <div class="flex-shrink-0 p-4 border-t border-gray-200">
                    <Link :href="route('dashboard')"
                        class="flex items-center px-4 py-3 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg transition-colors">
                        <ArrowRightOnRectangleIcon class="h-5 w-5 mr-3 rotate-180" aria-hidden="true" />
                        Back to MyGrowNet
                    </Link>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="lg:pl-64">
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
                            <span class="text-lg font-bold text-gray-900">Employee Portal</span>
                        </div>
                    </div>

                    <!-- Desktop: Page title / Search -->
                    <div class="hidden lg:flex lg:items-center lg:gap-4">
                        <h1 class="text-lg font-semibold text-gray-900">
                            <slot name="header">Employee Portal</slot>
                        </h1>
                    </div>

                    <!-- Right side: Notifications + User dropdown -->
                    <div class="flex items-center gap-2">
                        <!-- Notification Bell -->
                        <div class="notification-dropdown-container relative">
                            <button 
                                @click.stop="notificationDropdownOpen = !notificationDropdownOpen"
                                class="relative p-2 rounded-lg hover:bg-gray-100 transition-colors"
                                aria-label="View notifications">
                                <component 
                                    :is="unreadCount > 0 ? BellIconSolid : BellIcon" 
                                    :class="[
                                        'h-6 w-6',
                                        unreadCount > 0 ? 'text-blue-600' : 'text-gray-500'
                                    ]" 
                                    aria-hidden="true" 
                                />
                                <!-- Notification badge -->
                                <span 
                                    v-if="unreadCount > 0"
                                    class="absolute -top-0.5 -right-0.5 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-xs font-bold text-white bg-red-500 rounded-full">
                                    {{ unreadCount > 99 ? '99+' : unreadCount }}
                                </span>
                            </button>

                            <!-- Notification dropdown -->
                            <Transition
                                enter-active-class="transition ease-out duration-100"
                                enter-from-class="transform opacity-0 scale-95"
                                enter-to-class="transform opacity-100 scale-100"
                                leave-active-class="transition ease-in duration-75"
                                leave-from-class="transform opacity-100 scale-100"
                                leave-to-class="transform opacity-0 scale-95">
                                <div 
                                    v-if="notificationDropdownOpen"
                                    class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
                                    <div class="px-4 py-2 border-b border-gray-100">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                                            <span v-if="unreadCount > 0" class="text-xs text-blue-600 font-medium">
                                                {{ unreadCount }} new
                                            </span>
                                        </div>
                                    </div>
                                    <div class="max-h-64 overflow-y-auto">
                                        <div v-if="unreadCount === 0" class="px-4 py-8 text-center text-gray-500 text-sm">
                                            No new notifications
                                        </div>
                                        <div v-else class="px-4 py-3 text-sm text-gray-600">
                                            You have {{ unreadCount }} unread notification{{ unreadCount > 1 ? 's' : '' }}
                                        </div>
                                    </div>
                                    <div class="px-4 py-2 border-t border-gray-100">
                                        <Link 
                                            :href="route('employee.portal.notifications.index')"
                                            class="block text-center text-sm text-blue-600 hover:text-blue-700 font-medium"
                                            @click="notificationDropdownOpen = false">
                                            View all notifications
                                        </Link>
                                    </div>
                                </div>
                            </Transition>
                        </div>

                        <!-- User dropdown -->
                        <div class="user-dropdown-container relative">
                            <button 
                                @click.stop="userDropdownOpen = !userDropdownOpen"
                                class="flex items-center gap-2 p-1.5 pr-3 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-sm">
                                    <span class="text-sm font-medium text-white">
                                        {{ getInitials(user?.name || '') }}
                                    </span>
                                </div>
                                <div class="hidden sm:block text-left">
                                    <p class="text-sm font-medium text-gray-900 truncate max-w-[120px]">{{ user?.name }}</p>
                                </div>
                                <ChevronDownIcon 
                                    :class="[
                                        'h-4 w-4 text-gray-400 transition-transform hidden sm:block',
                                        userDropdownOpen ? 'rotate-180' : ''
                                    ]" 
                                    aria-hidden="true" 
                                />
                            </button>

                            <!-- User dropdown menu -->
                            <Transition
                                enter-active-class="transition ease-out duration-100"
                                enter-from-class="transform opacity-0 scale-95"
                                enter-to-class="transform opacity-100 scale-100"
                                leave-active-class="transition ease-in duration-75"
                                leave-from-class="transform opacity-100 scale-100"
                                leave-to-class="transform opacity-0 scale-95">
                                <div 
                                    v-if="userDropdownOpen"
                                    class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
                                    <!-- User info -->
                                    <div class="px-4 py-3 border-b border-gray-100">
                                        <p class="text-sm font-medium text-gray-900">{{ user?.name }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ user?.email }}</p>
                                        <p v-if="employee" class="text-xs text-blue-600 mt-1">
                                            {{ employee.position?.title || 'Employee' }}
                                        </p>
                                    </div>

                                    <!-- Menu items -->
                                    <div class="py-1">
                                        <Link 
                                            :href="route('employee.portal.profile.index')"
                                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                            @click="userDropdownOpen = false">
                                            <UserCircleIcon class="h-5 w-5 mr-3 text-gray-400" aria-hidden="true" />
                                            My Profile
                                        </Link>
                                        <Link 
                                            :href="route('employee.portal.notifications.index')"
                                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                            @click="userDropdownOpen = false">
                                            <BellIcon class="h-5 w-5 mr-3 text-gray-400" aria-hidden="true" />
                                            Notifications
                                            <span v-if="unreadCount > 0" class="ml-auto text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full">
                                                {{ unreadCount }}
                                            </span>
                                        </Link>
                                        <Link 
                                            :href="route('dashboard')"
                                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                            @click="userDropdownOpen = false">
                                            <HomeIcon class="h-5 w-5 mr-3 text-gray-400" aria-hidden="true" />
                                            Back to MyGrowNet
                                        </Link>
                                    </div>

                                    <!-- Logout -->
                                    <div class="border-t border-gray-100 pt-1">
                                        <button 
                                            @click="handleLogout"
                                            class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                            <ArrowRightOnRectangleIcon class="h-5 w-5 mr-3" aria-hidden="true" />
                                            Sign Out
                                        </button>
                                    </div>
                                </div>
                            </Transition>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main class="p-4 lg:p-6">
                <slot />
            </main>
        </div>

        <!-- Real-time notification toasts -->
        <NotificationToast
            :notifications="recentToasts"
            @dismiss="dismissToast"
            @click="(n) => { markAsRead(n.id); router.visit(n.action_url || route('employee.portal.notifications.index')); }"
        />

        <!-- Connection status indicator (only show when disconnected) -->
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="transform translate-y-full opacity-0"
            enter-to-class="transform translate-y-0 opacity-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="transform translate-y-0 opacity-100"
            leave-to-class="transform translate-y-full opacity-0"
        >
            <div 
                v-if="employee && !isConnected"
                class="fixed bottom-4 left-4 z-40 flex items-center gap-2 px-3 py-2 bg-amber-100 text-amber-800 rounded-lg shadow-md text-sm"
            >
                <WifiIcon class="h-4 w-4" aria-hidden="true" />
                <span>Connecting to real-time updates...</span>
            </div>
        </Transition>

        <!-- Live Chat Support Widget -->
        <UnifiedLiveChatWidget
            v-if="employee"
            user-type="employee"
            :user-id="employee.id"
            :user-name="employee.full_name"
        />
    </div>
</template>
