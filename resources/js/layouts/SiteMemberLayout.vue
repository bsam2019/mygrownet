<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    HomeIcon,
    UserIcon,
    ShoppingBagIcon,
    DocumentTextIcon,
    UsersIcon,
    Cog6ToothIcon,
    ArrowRightOnRectangleIcon,
    Bars3Icon,
    XMarkIcon,
    BellIcon,
    ChevronLeftIcon,
    ChevronDownIcon,
    BriefcaseIcon,
    ChartBarIcon,
    InboxIcon,
    EyeIcon,
    ShieldCheckIcon,
    EnvelopeIcon,
    CubeIcon,
} from '@heroicons/vue/24/outline';

interface Props {
    site: {
        id: number;
        name: string;
        subdomain: string;
        logo?: string | null;
        theme?: { primaryColor?: string } | null;
    };
    settings?: {
        navigation?: {
            logo?: string;
            logoText?: string;
        };
    } | null;
    user: {
        id: number;
        name: string;
        email: string;
        avatar?: string | null;
        role: {
            name: string;
            slug: string;
            level: number;
            type: 'staff' | 'client';
            color?: string;
        } | null;
        permissions: string[];
    };
    title?: string;
}

const props = defineProps<Props>();
const page = usePage();

// UI State
const mobileMenuOpen = ref(false);
const sidebarCollapsed = ref(false);
const profileDropdownOpen = ref(false);

// Theme
const primaryColor = computed(() => props.site.theme?.primaryColor || '#2563eb');
const subdomain = computed(() => props.site.subdomain);

// Logo - from settings.navigation.logo (same as site preview)
const siteLogo = computed(() => props.settings?.navigation?.logo || props.site.logo || null);

// Role helpers
const isAdmin = computed(() => props.user.role?.level >= 100);
const isStaff = computed(() => props.user.role?.type === 'staff');
const hasPermission = (permission: string) => isAdmin.value || props.user.permissions.includes(permission);

// Check if a nav item is current based on URL
const isCurrentPage = (href: string) => {
    const currentUrl = page.url;
    // Exact match for dashboard, prefix match for others
    if (href.endsWith('/dashboard')) {
        return currentUrl === href || currentUrl === href + '/';
    }
    return currentUrl.startsWith(href);
};

// Navigation
const navigation = computed(() => {
    const items = [
        { name: 'Dashboard', href: `/sites/${subdomain.value}/dashboard`, icon: HomeIcon },
        { name: 'Profile', href: `/sites/${subdomain.value}/dashboard/profile`, icon: UserIcon },
    ];

    if (isStaff.value) {
        if (hasPermission('orders.view')) {
            items.push({ name: 'Orders', href: `/sites/${subdomain.value}/dashboard/orders`, icon: InboxIcon });
        }
        if (hasPermission('products.view')) {
            items.push({ name: 'Products', href: `/sites/${subdomain.value}/dashboard/products`, icon: CubeIcon });
        }
        if (hasPermission('messages.view')) {
            items.push({ name: 'Messages', href: `/sites/${subdomain.value}/dashboard/messages`, icon: EnvelopeIcon });
        }
        if (hasPermission('posts.view')) {
            items.push({ name: 'Posts', href: `/sites/${subdomain.value}/dashboard/posts`, icon: DocumentTextIcon });
        }
        if (hasPermission('users.view')) {
            items.push({ name: 'Users', href: `/sites/${subdomain.value}/dashboard/users`, icon: UsersIcon });
        }
        if (hasPermission('analytics.view') || isAdmin.value) {
            items.push({ name: 'Analytics', href: `/sites/${subdomain.value}/dashboard/analytics`, icon: ChartBarIcon });
        }
        if (isAdmin.value) {
            items.push({ name: 'Settings', href: `/sites/${subdomain.value}/dashboard/settings`, icon: Cog6ToothIcon });
        }
    } else {
        items.push({ name: 'My Orders', href: `/sites/${subdomain.value}/dashboard/orders`, icon: ShoppingBagIcon });
    }

    return items;
});

const logout = () => router.post(`/sites/${subdomain.value}/logout`);
const userInitials = computed(() => props.user.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2));

const roleBadgeConfig = computed(() => {
    const color = props.user.role?.color || 'gray';
    const colorMap: Record<string, { bg: string; text: string }> = {
        indigo: { bg: 'bg-indigo-100', text: 'text-indigo-700' },
        purple: { bg: 'bg-purple-100', text: 'text-purple-700' },
        blue: { bg: 'bg-blue-100', text: 'text-blue-700' },
        cyan: { bg: 'bg-cyan-100', text: 'text-cyan-700' },
        emerald: { bg: 'bg-emerald-100', text: 'text-emerald-700' },
        yellow: { bg: 'bg-yellow-100', text: 'text-yellow-700' },
        amber: { bg: 'bg-amber-100', text: 'text-amber-700' },
        gray: { bg: 'bg-gray-100', text: 'text-gray-700' },
    };
    return colorMap[color] || colorMap.gray;
});
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Mobile Menu -->
        <Teleport to="body">
            <div v-if="mobileMenuOpen" class="fixed inset-0 z-50 lg:hidden">
                <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="mobileMenuOpen = false"></div>
                <div class="fixed inset-y-0 left-0 w-80 bg-white shadow-2xl flex flex-col">
                    <div class="flex items-center justify-between p-4 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <img v-if="siteLogo" :src="siteLogo" :alt="site.name" class="h-8 w-8 rounded-lg object-cover" />
                            <div v-else class="w-8 h-8 rounded-lg flex items-center justify-center text-white font-bold text-sm" :style="{ backgroundColor: primaryColor }">
                                {{ site.name.charAt(0) }}
                            </div>
                            <span class="font-semibold text-gray-900">{{ site.name }}</span>
                        </div>
                        <button @click="mobileMenuOpen = false" class="p-2 rounded-lg text-gray-400 hover:bg-gray-100" aria-label="Close menu">
                            <XMarkIcon class="w-5 h-5" aria-hidden="true" />
                        </button>
                    </div>
                    <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
                        <Link v-for="item in navigation" :key="item.name" :href="item.href" @click="mobileMenuOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all"
                            :class="isCurrentPage(item.href) ? 'text-white' : 'text-gray-600 hover:bg-gray-100'"
                            :style="isCurrentPage(item.href) ? { backgroundColor: primaryColor } : {}">
                            <component :is="item.icon" class="w-5 h-5" aria-hidden="true" />
                            {{ item.name }}
                        </Link>
                    </nav>
                    <div class="p-3 border-t border-gray-100">
                        <button @click="logout" class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50">
                            <ArrowRightOnRectangleIcon class="w-5 h-5" aria-hidden="true" />
                            Sign Out
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Desktop Sidebar -->
        <aside :class="['hidden lg:flex lg:flex-col lg:fixed lg:inset-y-0 bg-white border-r border-gray-200 transition-all duration-300 z-30', sidebarCollapsed ? 'lg:w-20' : 'lg:w-64']">
            <!-- Logo + Collapse Toggle -->
            <div class="flex items-center h-16 px-4 border-b border-gray-100" :class="sidebarCollapsed ? 'justify-center' : 'justify-between'">
                <div v-if="!sidebarCollapsed" class="flex items-center gap-3 min-w-0 flex-1">
                    <img v-if="siteLogo" :src="siteLogo" :alt="site.name" class="h-9 w-9 rounded-lg object-cover flex-shrink-0" />
                    <div v-else class="w-9 h-9 rounded-lg flex items-center justify-center text-white font-bold flex-shrink-0" :style="{ backgroundColor: primaryColor }">
                        {{ site.name.charAt(0) }}
                    </div>
                    <span class="font-semibold text-gray-900 truncate">{{ site.name }}</span>
                </div>
                <button 
                    @click="sidebarCollapsed = !sidebarCollapsed"
                    class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors flex-shrink-0"
                    :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                    aria-label="Toggle sidebar"
                >
                    <ChevronLeftIcon :class="['w-5 h-5 transition-transform', sidebarCollapsed && 'rotate-180']" aria-hidden="true" />
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
                <Link v-for="item in navigation" :key="item.name" :href="item.href"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all group relative"
                    :class="isCurrentPage(item.href) ? 'text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                    :style="isCurrentPage(item.href) ? { backgroundColor: primaryColor } : {}"
                    :title="sidebarCollapsed ? item.name : undefined">
                    <component :is="item.icon" class="w-5 h-5 flex-shrink-0" aria-hidden="true" />
                    <span v-if="!sidebarCollapsed">{{ item.name }}</span>
                    <!-- Tooltip for collapsed state -->
                    <div v-if="sidebarCollapsed" class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 pointer-events-none whitespace-nowrap z-50">
                        {{ item.name }}
                    </div>
                </Link>
            </nav>
        </aside>

        <!-- Main Content -->
        <div :class="['lg:transition-all lg:duration-300', sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64']">
            <!-- Header -->
            <header class="sticky top-0 z-20 bg-white border-b border-gray-200">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6">
                    <div class="flex items-center gap-4">
                        <button @click="mobileMenuOpen = true" class="lg:hidden p-2 -ml-2 rounded-lg text-gray-500 hover:bg-gray-100" aria-label="Open menu">
                            <Bars3Icon class="w-6 h-6" aria-hidden="true" />
                        </button>
                        <h1 class="text-lg font-semibold text-gray-900">{{ title || 'Dashboard' }}</h1>
                    </div>
                    <div class="flex items-center gap-2">
                        <!-- Header Actions Slot -->
                        <slot name="header-actions">
                            <a :href="`/sites/${subdomain}`" target="_blank" class="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100">
                                <EyeIcon class="w-4 h-4" aria-hidden="true" />
                                View Site
                            </a>
                        </slot>
                        <button class="p-2 rounded-lg text-gray-500 hover:bg-gray-100" aria-label="Notifications">
                            <BellIcon class="w-5 h-5" aria-hidden="true" />
                        </button>
                        <div class="relative">
                            <button @click="profileDropdownOpen = !profileDropdownOpen" class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-gray-100">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white font-semibold text-sm" :style="{ backgroundColor: primaryColor }">
                                    {{ userInitials }}
                                </div>
                                <div class="hidden md:block text-left">
                                    <p class="text-sm font-medium text-gray-900">{{ user.name }}</p>
                                    <p class="text-xs text-gray-500">{{ user.role?.name || 'Member' }}</p>
                                </div>
                                <ChevronDownIcon class="hidden md:block w-4 h-4 text-gray-400" aria-hidden="true" />
                            </button>
                            <div v-if="profileDropdownOpen" class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-200 py-1 z-50" @click="profileDropdownOpen = false">
                                <div class="px-4 py-3 border-b border-gray-100">
                                    <p class="text-sm font-medium text-gray-900">{{ user.name }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ user.email }}</p>
                                    <span v-if="user.role" class="inline-flex items-center gap-1 mt-2 px-2 py-0.5 rounded text-xs font-medium" :class="[roleBadgeConfig.bg, roleBadgeConfig.text]">
                                        <ShieldCheckIcon v-if="isAdmin" class="w-3 h-3" aria-hidden="true" />
                                        <BriefcaseIcon v-else-if="isStaff" class="w-3 h-3" aria-hidden="true" />
                                        {{ user.role.name }}
                                    </span>
                                </div>
                                <Link :href="`/sites/${subdomain}/dashboard/profile`" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <UserIcon class="w-4 h-4" aria-hidden="true" />
                                    Your Profile
                                </Link>
                                <a :href="`/sites/${subdomain}`" target="_blank" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <EyeIcon class="w-4 h-4" aria-hidden="true" />
                                    View Site
                                </a>
                                <div class="border-t border-gray-100 mt-1 pt-1">
                                    <button @click="logout" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                        <ArrowRightOnRectangleIcon class="w-4 h-4" aria-hidden="true" />
                                        Sign Out
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <div v-if="profileDropdownOpen" class="fixed inset-0 z-10" @click="profileDropdownOpen = false"></div>

            <!-- Page Content -->
            <main class="p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>
