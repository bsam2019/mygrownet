<script setup lang="ts">
/**
 * ClientLayout - Layout for non-MLM users (clients, business accounts)
 * 
 * A simpler layout without MLM-specific navigation items.
 * Used for wallet, shop, and general app features.
 */
import { usePage, Link, router } from '@inertiajs/vue3';
import { computed, ref, onMounted, watch } from 'vue';
import { 
    HomeIcon, 
    WalletIcon, 
    ShoppingBagIcon, 
    UserIcon,
    Cog6ToothIcon,
    ArrowRightOnRectangleIcon,
    Bars3Icon,
    XMarkIcon,
    BellIcon,
    CheckCircleIcon,
    ExclamationCircleIcon
} from '@heroicons/vue/24/outline';

interface Props {
    title?: string;
}

withDefaults(defineProps<Props>(), {
    title: 'MyGrowNet'
});

const page = usePage();
const mobileMenuOpen = ref(false);
const showFlash = ref(false);
const flashMessage = ref('');
const flashType = ref<'success' | 'error'>('success');

const user = computed(() => page.props.auth?.user);

// Watch for flash messages
watch(() => page.props.flash, (flash: any) => {
    if (flash?.success) {
        flashMessage.value = flash.success;
        flashType.value = 'success';
        showFlash.value = true;
        setTimeout(() => showFlash.value = false, 5000);
    } else if (flash?.error) {
        flashMessage.value = flash.error;
        flashType.value = 'error';
        showFlash.value = true;
        setTimeout(() => showFlash.value = false, 5000);
    }
}, { immediate: true });

const navigation = [
    { name: 'Dashboard', href: '/dashboard', icon: HomeIcon },
    { name: 'Wallet', href: '/wallet', icon: WalletIcon },
    { name: 'Shop', href: '/marketplace', icon: ShoppingBagIcon },
];

const userNavigation = [
    { name: 'Profile', href: '/settings/profile', icon: UserIcon },
    { name: 'Settings', href: '/settings', icon: Cog6ToothIcon },
];

const isCurrentRoute = (href: string) => {
    return window.location.pathname.startsWith(href);
};

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Flash Messages -->
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="transform -translate-y-full opacity-0"
            enter-to-class="transform translate-y-0 opacity-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="transform translate-y-0 opacity-100"
            leave-to-class="transform -translate-y-full opacity-0"
        >
            <div 
                v-if="showFlash" 
                :class="[
                    'fixed top-0 left-0 right-0 z-50 px-4 py-3 shadow-lg',
                    flashType === 'success' ? 'bg-green-500' : 'bg-red-500'
                ]"
            >
                <div class="max-w-7xl mx-auto flex items-center justify-between">
                    <div class="flex items-center gap-2 text-white">
                        <CheckCircleIcon v-if="flashType === 'success'" class="h-5 w-5" aria-hidden="true" />
                        <ExclamationCircleIcon v-else class="h-5 w-5" aria-hidden="true" />
                        <span class="text-sm font-medium">{{ flashMessage }}</span>
                    </div>
                    <button 
                        @click="showFlash = false" 
                        class="text-white/80 hover:text-white"
                        aria-label="Dismiss message"
                    >
                        <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                    </button>
                </div>
            </div>
        </Transition>

        <!-- Top Navigation Bar -->
        <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <!-- Logo & Desktop Nav -->
                    <div class="flex">
                        <div class="flex flex-shrink-0 items-center">
                            <Link href="/dashboard" class="flex items-center gap-2">
                                <div class="h-8 w-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                                    <span class="text-white font-bold text-sm">MG</span>
                                </div>
                                <span class="hidden sm:block text-lg font-semibold text-gray-900">MyGrowNet</span>
                            </Link>
                        </div>
                        
                        <!-- Desktop Navigation -->
                        <div class="hidden sm:ml-8 sm:flex sm:space-x-4">
                            <Link
                                v-for="item in navigation"
                                :key="item.name"
                                :href="item.href"
                                :class="[
                                    isCurrentRoute(item.href)
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                                    'inline-flex items-center gap-2 border-b-2 px-1 pt-1 text-sm font-medium'
                                ]"
                            >
                                <component :is="item.icon" class="h-5 w-5" aria-hidden="true" />
                                {{ item.name }}
                            </Link>
                        </div>
                    </div>

                    <!-- Right side -->
                    <div class="hidden sm:ml-6 sm:flex sm:items-center gap-4">
                        <!-- Notifications -->
                        <button
                            type="button"
                            class="relative rounded-full bg-white p-2 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                            aria-label="View notifications"
                        >
                            <BellIcon class="h-6 w-6" aria-hidden="true" />
                        </button>

                        <!-- Profile dropdown -->
                        <div class="relative">
                            <div class="flex items-center gap-3">
                                <div class="text-right hidden md:block">
                                    <p class="text-sm font-medium text-gray-900">{{ user?.name }}</p>
                                    <p class="text-xs text-gray-500">{{ user?.email }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Link
                                        href="/settings/profile"
                                        class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-100 text-blue-600 hover:bg-blue-200"
                                    >
                                        <UserIcon class="h-5 w-5" aria-hidden="true" />
                                    </Link>
                                    <button
                                        @click="logout"
                                        class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200"
                                        aria-label="Sign out"
                                    >
                                        <ArrowRightOnRectangleIcon class="h-5 w-5" aria-hidden="true" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile menu button -->
                    <div class="flex items-center sm:hidden">
                        <button
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500"
                            :aria-label="mobileMenuOpen ? 'Close menu' : 'Open menu'"
                        >
                            <XMarkIcon v-if="mobileMenuOpen" class="h-6 w-6" aria-hidden="true" />
                            <Bars3Icon v-else class="h-6 w-6" aria-hidden="true" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div v-if="mobileMenuOpen" class="sm:hidden border-t border-gray-200">
                <div class="space-y-1 pb-3 pt-2">
                    <Link
                        v-for="item in navigation"
                        :key="item.name"
                        :href="item.href"
                        :class="[
                            isCurrentRoute(item.href)
                                ? 'border-blue-500 bg-blue-50 text-blue-700'
                                : 'border-transparent text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800',
                            'flex items-center gap-3 border-l-4 py-2 pl-3 pr-4 text-base font-medium'
                        ]"
                        @click="mobileMenuOpen = false"
                    >
                        <component :is="item.icon" class="h-5 w-5" aria-hidden="true" />
                        {{ item.name }}
                    </Link>
                </div>
                <div class="border-t border-gray-200 pb-3 pt-4">
                    <div class="flex items-center px-4">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                <UserIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                            </div>
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium text-gray-800">{{ user?.name }}</div>
                            <div class="text-sm font-medium text-gray-500">{{ user?.email }}</div>
                        </div>
                    </div>
                    <div class="mt-3 space-y-1">
                        <Link
                            v-for="item in userNavigation"
                            :key="item.name"
                            :href="item.href"
                            class="flex items-center gap-3 px-4 py-2 text-base font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-800"
                            @click="mobileMenuOpen = false"
                        >
                            <component :is="item.icon" class="h-5 w-5" aria-hidden="true" />
                            {{ item.name }}
                        </Link>
                        <button
                            @click="logout"
                            class="flex w-full items-center gap-3 px-4 py-2 text-base font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-800"
                        >
                            <ArrowRightOnRectangleIcon class="h-5 w-5" aria-hidden="true" />
                            Sign out
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-auto">
            <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                <p class="text-center text-sm text-gray-500">
                    © {{ new Date().getFullYear() }} MyGrowNet. All rights reserved.
                </p>
            </div>
        </footer>
    </div>
</template>
