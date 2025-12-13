<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ToastContainer from '@/Components/GrowBiz/ToastContainer.vue';
import {
    HomeIcon,
    ArchiveBoxIcon,
    TagIcon,
    ArrowTrendingUpIcon,
    ExclamationTriangleIcon,
    Cog6ToothIcon,
    Bars3Icon,
    XMarkIcon,
    ChevronLeftIcon,
    EllipsisHorizontalIcon,
} from '@heroicons/vue/24/outline';

interface Props {
    title?: string;
}

defineProps<Props>();

const page = usePage();
const user = computed(() => page.props.auth?.user);

const sidebarOpen = ref(false);
const sidebarCollapsed = ref(false);

// Navigation items
const navigation = [
    { name: 'Dashboard', href: '/inventory', icon: HomeIcon, routeName: 'inventory.dashboard' },
    { name: 'Items', href: '/inventory/items', icon: ArchiveBoxIcon, routeName: 'inventory.items' },
    { name: 'Categories', href: '/inventory/categories', icon: TagIcon, routeName: 'inventory.categories' },
    { name: 'Movements', href: '/inventory/movements', icon: ArrowTrendingUpIcon, routeName: 'inventory.movements' },
    { name: 'Alerts', href: '/inventory/alerts', icon: ExclamationTriangleIcon, routeName: 'inventory.alerts' },
    { name: 'Settings', href: '/inventory/settings', icon: Cog6ToothIcon, routeName: 'inventory.settings' },
];

const isCurrentRoute = (routeName: string) => {
    try {
        return route().current(routeName) || route().current(routeName + '.*');
    } catch {
        return false;
    }
};

// Close sidebar on escape key
const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && sidebarOpen.value) {
        sidebarOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <div class="h-screen overflow-hidden bg-gray-50">
        <!-- Toast Notifications -->
        <ToastContainer />

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
                            <Link href="/inventory" class="flex items-center gap-2">
                                <div class="h-8 w-8 rounded-lg bg-teal-600 flex items-center justify-center">
                                    <ArchiveBoxIcon class="h-5 w-5 text-white" />
                                </div>
                                <span class="text-xl font-bold text-gray-900">Inventory</span>
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
                                                        ? 'bg-teal-50 text-teal-600'
                                                        : 'text-gray-700 hover:text-teal-600 hover:bg-gray-50',
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
                <!-- Header with collapse toggle -->
                <div :class="['flex h-16 shrink-0 items-center border-b border-gray-200', sidebarCollapsed ? 'justify-center px-4' : 'justify-between px-6']">
                    <Link href="/inventory" :class="['flex items-center gap-3', sidebarCollapsed && 'justify-center']">
                        <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-teal-500 to-teal-600 flex items-center justify-center shadow-lg shadow-teal-500/30">
                            <ArchiveBoxIcon class="h-5 w-5 text-white" />
                        </div>
                        <div v-if="!sidebarCollapsed">
                            <h3 class="text-lg font-bold text-gray-900">Inventory</h3>
                            <p class="text-xs text-gray-500">Stock Management</p>
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
                        class="absolute -right-3 top-6 p-1 rounded-full bg-white border border-gray-200 shadow-md hover:shadow-lg text-gray-400 hover:text-teal-600 transition-all"
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
                                        ? 'bg-teal-50 text-teal-700 border border-teal-200'
                                        : 'text-gray-700 hover:text-teal-600 hover:bg-gray-50 border border-transparent',
                                    'group flex items-center gap-x-3 rounded-xl p-2.5 text-sm font-semibold transition-all',
                                    sidebarCollapsed && 'justify-center'
                                ]"
                                :title="sidebarCollapsed ? item.name : ''"
                            >
                                <component :is="item.icon" class="h-5 w-5 shrink-0" aria-hidden="true" />
                                <span v-if="!sidebarCollapsed">{{ item.name }}</span>
                            </Link>
                        </li>

                        <!-- All Apps Link -->
                        <li class="mt-auto">
                            <Link
                                href="/apps"
                                :class="[
                                    'group flex items-center gap-x-3 rounded-xl p-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 hover:text-teal-600 transition-all border border-gray-200 hover:border-teal-200',
                                    sidebarCollapsed && 'justify-center'
                                ]"
                                :title="sidebarCollapsed ? 'All Apps' : ''"
                            >
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                </svg>
                                <span v-if="!sidebarCollapsed">All Apps</span>
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

                <!-- Separator -->
                <div class="h-6 w-px bg-gray-200 lg:hidden" aria-hidden="true"></div>

                <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                    <div class="flex flex-1 items-center">
                        <h1 class="text-lg font-semibold text-gray-900">{{ title || 'Inventory' }}</h1>
                    </div>
                    <div class="flex items-center gap-x-4 lg:gap-x-6">
                        <!-- User menu -->
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-gray-700">{{ user?.name }}</span>
                            <div class="h-8 w-8 rounded-full bg-teal-100 flex items-center justify-center">
                                <span class="text-sm font-medium text-teal-700">{{ user?.name?.charAt(0) }}</span>
                            </div>
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
                    v-for="item in navigation"
                    :key="item.name"
                    :href="item.href"
                    :class="[
                        isCurrentRoute(item.routeName)
                            ? 'text-teal-600'
                            : 'text-gray-500 hover:text-teal-600',
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
