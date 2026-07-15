<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import {
    HomeIcon,
    ArchiveBoxIcon,
    ShoppingCartIcon,
    CreditCardIcon,
    CurrencyDollarIcon,
    ClipboardDocumentListIcon,
    DocumentTextIcon,
    ArrowTrendingUpIcon,
    BuildingStorefrontIcon,
    CubeIcon,
    Bars3Icon,
    XMarkIcon,
    ChevronLeftIcon,
    BuildingOfficeIcon,
    ArrowsRightLeftIcon,
    UserCircleIcon,
    ArrowRightOnRectangleIcon,
    Cog6ToothIcon,
    UsersIcon,
    ShieldCheckIcon,
    DocumentChartBarIcon,
    EnvelopeIcon,
    DocumentArrowUpIcon,
    DocumentCheckIcon,
} from '@heroicons/vue/24/outline';
import NotificationToast from '@/components/StockAudit/NotificationToast.vue';
import NotificationBell from '@/components/StockAudit/NotificationBell.vue';
import ConfirmDialog from '@/components/StockAudit/ConfirmDialog.vue';

interface Props {
    title?: string;
}

defineProps<Props>();

const page = usePage();
const user = computed(() => page.props.auth?.user);
const sidebarOpen = ref(false);
const sidebarCollapsed = ref(false);
const showUserMenu = ref(false);

const isSubdomain = computed(() => {
    const routeName = page.props.routeName ?? '';
    return routeName.startsWith('stockflow.sub.');
});

const companyFeatures = computed(() => (page.props as any).companyFeatures ?? {});
const messageUnreadCount = ref(0);

const fetchMessageUnreadCount = async () => {
    try {
        const url = isSubdomain.value ? '/messages/unread-count' : '/stock-audit/messages/unread-count';
        const res = await fetch(url);
        const data = await res.json();
        messageUnreadCount.value = data.count ?? 0;
    } catch {
        messageUnreadCount.value = 0;
    }
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape') {
        sidebarOpen.value = false;
        showUserMenu.value = false;
    }
};

const navigation = computed(() => {
    const base = isSubdomain.value ? 'stockflow.sub' : 'stock-audit';
    const features = companyFeatures.value;
    const f = (key: string) => features[key] !== false;

    const items = [
        { name: 'Dashboard', href: isSubdomain.value ? '/' : '/stock-audit', icon: HomeIcon, routeName: `${base}.dashboard`, feature: true },
        { name: 'Items', href: isSubdomain.value ? '/items' : '/stock-audit/items', icon: ArchiveBoxIcon, routeName: `${base}.items.index`, feature: f('items') },
        { name: 'Quotations', href: isSubdomain.value ? '/quotations' : '/stock-audit/quotations', icon: DocumentTextIcon, routeName: `${base}.quotations.index`, feature: f('quotations') },
        { name: 'Sales', href: isSubdomain.value ? '/sales' : '/stock-audit/sales', icon: CreditCardIcon, routeName: `${base}.sales.index`, feature: f('sales') },
        { name: 'Invoices', href: isSubdomain.value ? '/invoices' : '/stock-audit/invoices', icon: DocumentCheckIcon, routeName: `${base}.invoices.index`, feature: f('invoices') },
        { name: 'Receipts', href: isSubdomain.value ? '/receipts' : '/stock-audit/receipts', icon: DocumentArrowUpIcon, routeName: `${base}.receipts.index`, feature: f('receipts') },
        { name: 'Purchases', href: isSubdomain.value ? '/purchases' : '/stock-audit/purchases', icon: ShoppingCartIcon, routeName: `${base}.purchases.index`, feature: f('purchases') },
        { name: 'Cash Register', href: isSubdomain.value ? '/cash' : '/stock-audit/cash', icon: CurrencyDollarIcon, routeName: `${base}.cash.index`, feature: f('cash') },
        { name: 'Stock Movements', href: isSubdomain.value ? '/movements' : '/stock-audit/movements', icon: ArrowTrendingUpIcon, routeName: `${base}.movements.index`, feature: f('movements') },
        { name: 'Physical Counts', href: isSubdomain.value ? '/physical-counts' : '/stock-audit/physical-counts', icon: ClipboardDocumentListIcon, routeName: `${base}.physical-counts.index`, feature: f('counts') },
        { name: 'Audits', href: isSubdomain.value ? '/audits' : '/stock-audit/audits', icon: DocumentTextIcon, routeName: `${base}.audits.index`, feature: f('audits') },
        { name: 'Suppliers', href: isSubdomain.value ? '/suppliers' : '/stock-audit/suppliers', icon: BuildingStorefrontIcon, routeName: `${base}.suppliers.index`, feature: f('suppliers') },
        { name: 'Departments', href: isSubdomain.value ? '/departments' : '/stock-audit/departments', icon: BuildingOfficeIcon, routeName: `${base}.departments.index`, feature: f('departments') },
        { name: 'Reports', href: isSubdomain.value ? '/reports' : '/stock-audit/reports', icon: DocumentChartBarIcon, routeName: `${base}.reports.index`, feature: f('reports') },
        { name: 'Bins', href: isSubdomain.value ? '/bins' : '/stock-audit/bins', icon: CubeIcon, routeName: `${base}.bins.index`, feature: f('bins') },
        { name: 'Employees', href: isSubdomain.value ? '/employees' : '/stock-audit/employees', icon: UsersIcon, routeName: `${base}.employees.index`, feature: f('employees') },
        { name: 'Roles', href: isSubdomain.value ? '/roles' : '/stock-audit/roles', icon: ShieldCheckIcon, routeName: `${base}.roles.index`, feature: f('roles') },
        { name: 'Messages', href: isSubdomain.value ? '/messages' : '/stock-audit/messages', icon: EnvelopeIcon, routeName: `${base}.messages.inbox`, feature: f('messages'), badge: messageUnreadCount.value },
    ];
    return items.filter(i => i.feature);
});

const isCurrentRoute = (routeName: string) => {
    try {
        const routeHelper = route();
        if (!routeHelper || typeof routeHelper.current !== 'function') {
            return false;
        }
        return routeHelper.current(routeName) || routeHelper.current(routeName + '.*');
    } catch {
        return false;
    }
};

const logout = () => {
    if (isSubdomain.value) {
        const hostParts = window.location.hostname.split('.');
        const account = hostParts.length > 2 ? hostParts[0] : '';
        router.post(route('stockflow.sub.logout', { account }));
    } else {
        router.post(route('stockflow.sub.logout'));
    }
};

const closeUserMenu = () => {
    showUserMenu.value = false;
};

onMounted(() => {
    fetchMessageUnreadCount();
    const interval = setInterval(fetchMessageUnreadCount, 30000);
    onUnmounted(() => clearInterval(interval));

    document.addEventListener('keydown', handleKeydown);
    document.addEventListener('click', (e) => {
        const target = e.target as HTMLElement;
        if (!target.closest('[data-user-menu]')) {
            showUserMenu.value = false;
        }
    });
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <div class="h-screen overflow-hidden bg-gray-50">
        <NotificationToast />
        <ConfirmDialog />

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
                    <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-white px-6 pb-4">
                        <div class="flex h-16 shrink-0 items-center">
                            <Link :href="isSubdomain ? '/' : '/stock-audit'" class="flex items-center gap-2">
                                <div class="h-8 w-8 rounded-lg bg-emerald-600 flex items-center justify-center">
                                    <ClipboardDocumentListIcon class="h-5 w-5 text-white" />
                                </div>
                                <span class="text-xl font-bold text-gray-900">StockFlow</span>
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
                                                    isCurrentRoute(item.routeName)
                                                        ? 'bg-emerald-50 text-emerald-600'
                                                        : 'text-gray-700 hover:text-emerald-600 hover:bg-gray-50',
                                                    'group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold',
                                                ]"
                                            >
                                                <component :is="item.icon" class="h-6 w-6 shrink-0" aria-hidden="true" />
                                                {{ item.name }}
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
                sidebarCollapsed ? 'lg:w-20' : 'lg:w-64'
            ]"
        >
            <div class="flex grow flex-col gap-y-5 overflow-y-auto border-r border-gray-200 bg-white pb-4">
                <div :class="['flex h-16 shrink-0 items-center border-b border-gray-200', sidebarCollapsed ? 'justify-center px-4' : 'justify-between px-6']">
                    <Link :href="isSubdomain ? '/' : '/stock-audit'" :class="['flex items-center gap-3', sidebarCollapsed && 'justify-center']">
                        <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                            <ClipboardDocumentListIcon class="h-5 w-5 text-white" />
                        </div>
                        <div v-if="!sidebarCollapsed">
                            <h3 class="text-lg font-bold text-gray-900">StockFlow</h3>
                            <p class="text-xs text-gray-500">Stock Audit</p>
                        </div>
                    </Link>
                    <button
                        v-if="!sidebarCollapsed"
                        @click="sidebarCollapsed = true"
                        class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors"
                        aria-label="Collapse sidebar"
                    >
                        <ChevronLeftIcon class="h-5 w-5" aria-hidden="true" />
                    </button>
                    <button
                        v-else
                        @click="sidebarCollapsed = false"
                        class="absolute -right-3 top-6 p-1 rounded-full bg-white border border-gray-200 shadow-md hover:shadow-lg text-gray-400 hover:text-emerald-600 transition-all"
                        aria-label="Expand sidebar"
                    >
                        <ChevronLeftIcon class="h-4 w-4 rotate-180" aria-hidden="true" />
                    </button>
                </div>

                <nav :class="['flex flex-1 flex-col', sidebarCollapsed ? 'px-3' : 'px-4']">
                    <ul role="list" class="flex flex-1 flex-col gap-y-2">
                    <li v-for="item in navigation" :key="item.name">
                        <Link
                            :href="item.href"
                            :class="[
                                isCurrentRoute(item.routeName)
                                    ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                                    : 'text-gray-700 hover:text-emerald-600 hover:bg-gray-50 border border-transparent',
                                'group flex items-center gap-x-3 rounded-xl p-2.5 text-sm font-semibold transition-all',
                                sidebarCollapsed && 'justify-center'
                            ]"
                            :title="sidebarCollapsed ? item.name : ''"
                        >
                            <div class="relative">
                                <component :is="item.icon" class="h-5 w-5 shrink-0" aria-hidden="true" />
                                <span
                                    v-if="item.name === 'Messages' && messageUnreadCount > 0 && sidebarCollapsed"
                                    class="absolute -top-1.5 -right-1.5 h-4 w-4 rounded-full bg-red-500 text-white text-[9px] font-bold flex items-center justify-center"
                                >{{ messageUnreadCount > 99 ? '99+' : messageUnreadCount }}</span>
                            </div>
                            <div v-if="!sidebarCollapsed" class="flex items-center gap-2 flex-1">
                                <span>{{ item.name }}</span>
                                <span
                                    v-if="item.name === 'Messages' && messageUnreadCount > 0"
                                    class="ml-auto inline-flex items-center px-1.5 py-0.5 rounded-full bg-red-500 text-white text-[10px] font-bold"
                                >{{ messageUnreadCount > 99 ? '99+' : messageUnreadCount }}</span>
                            </div>
                        </Link>
                    </li>
                    </ul>
                </nav>
            </div>
        </div>

        <!-- Main content -->
        <div :class="['transition-all duration-300', sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64']">
            <!-- Top bar -->
            <div class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
                <button type="button" class="-m-2.5 p-2.5 text-gray-700 lg:hidden" @click="sidebarOpen = true">
                    <span class="sr-only">Open sidebar</span>
                    <Bars3Icon class="h-6 w-6" aria-hidden="true" />
                </button>

                <div class="h-6 w-px bg-gray-200 lg:hidden" aria-hidden="true"></div>

                <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                    <div class="flex flex-1 items-center">
                        <h1 class="text-lg font-semibold text-gray-900">{{ title || 'StockFlow' }}</h1>
                    </div>
                    <div class="flex items-center gap-x-2 lg:gap-x-4">
                        <!-- Notification Bell -->
                        <NotificationBell />

                        <!-- User dropdown -->
                        <div data-user-menu class="relative">
                            <button
                                @click.stop="showUserMenu = !showUserMenu"
                                class="flex items-center gap-3 rounded-lg p-1.5 hover:bg-gray-50 transition-colors"
                            >
                                <span class="hidden sm:block text-sm font-medium text-gray-700">{{ user?.name }}</span>
                                <div class="h-8 w-8 rounded-full bg-emerald-100 flex items-center justify-center">
                                    <span class="text-sm font-semibold text-emerald-700">{{ user?.name?.charAt(0)?.toUpperCase() }}</span>
                                </div>
                            </button>

                            <transition name="dropdown">
                                <div
                                    v-if="showUserMenu"
                                    class="absolute right-0 mt-2 w-56 origin-top-right rounded-xl bg-white shadow-lg ring-1 ring-black/5 focus:outline-none"
                                >
                                    <div class="px-4 py-3 border-b border-gray-100">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ user?.name }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ user?.email }}</p>
                                    </div>
                                    <div class="py-1">
                                        <Link
                                            :href="isSubdomain ? '/settings' : '/stock-audit/settings'"
                                            class="flex w-full items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                        >
                                            <Cog6ToothIcon class="h-5 w-5" />
                                            Settings
                                        </Link>
                                    </div>
                                    <div class="py-1 border-t border-gray-100">
                                        <button
                                            @click="logout"
                                            class="flex w-full items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-red-600"
                                        >
                                            <ArrowRightOnRectangleIcon class="h-5 w-5" />
                                            Sign out
                                        </button>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page content -->
            <main class="h-[calc(100vh-4rem)] overflow-y-auto pb-20 lg:pb-0">
                <slot />
            </main>
        </div>

        <!-- Mobile Bottom Navigation -->
        <div class="fixed bottom-0 left-0 right-0 z-50 border-t border-gray-200 bg-white lg:hidden">
            <nav class="flex items-center justify-around py-2">
                <Link
                    v-for="item in navigation.slice(0, 5)"
                    :key="item.name"
                    :href="item.href"
                    :class="[
                        isCurrentRoute(item.routeName)
                            ? 'text-emerald-600'
                            : 'text-gray-500 hover:text-emerald-600',
                        'flex flex-col items-center gap-1 px-3 py-1 text-xs font-medium transition-colors'
                    ]"
                >
                    <component :is="item.icon" class="h-5 w-5" aria-hidden="true" />
                    <span class="truncate">{{ item.name.split(' ')[0] }}</span>
                </Link>
            </nav>
        </div>
    </div>
</template>

<style scoped>
.dropdown-enter-active { transition: all 0.15s ease-out; }
.dropdown-leave-active { transition: all 0.1s ease-in; }
.dropdown-enter-from { opacity: 0; transform: translateY(-4px); }
.dropdown-leave-to { opacity: 0; transform: translateY(-4px); }
</style>