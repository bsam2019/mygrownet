<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { 
    DocumentTextIcon,
    PaintBrushIcon,
    ClockIcon,
    Bars3Icon,
    XMarkIcon,
    UserCircleIcon,
    ArrowLeftOnRectangleIcon,
    HomeIcon,
    ChevronDownIcon
} from '@heroicons/vue/24/outline';
import { ref, computed, onMounted, onUnmounted } from 'vue';

const mobileMenuOpen = ref(false);
const profileDropdownOpen = ref(false);

const page = usePage();
const user = computed(() => page.props.auth?.user || null);

const navigation = [
    { name: 'Dashboard', href: route('quick-invoice.index'), icon: DocumentTextIcon },
    { name: 'Create', href: route('quick-invoice.create'), icon: DocumentTextIcon },
    { name: 'History', href: route('quick-invoice.history'), icon: ClockIcon },
    { name: 'Design Studio', href: route('quick-invoice.design-studio'), icon: PaintBrushIcon },
];

// Close dropdown when clicking outside
const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement;
    if (!target.closest('.profile-dropdown')) {
        profileDropdownOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Compact Top Navigation -->
        <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <Link :href="route('quick-invoice.index')" class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                            <DocumentTextIcon class="h-6 w-6 text-white" aria-hidden="true" />
                        </div>
                        <div>
                            <h1 class="text-lg font-bold text-gray-900">Quick Invoice</h1>
                            <p class="text-xs text-gray-500">by MyGrowNet</p>
                        </div>
                    </Link>

                    <!-- Desktop Navigation -->
                    <div class="hidden md:flex items-center gap-1">
                        <Link
                            v-for="item in navigation"
                            :key="item.name"
                            :href="item.href"
                            class="flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition"
                            :class="$page.url.startsWith(item.href) 
                                ? 'bg-blue-50 text-blue-700' 
                                : 'text-gray-700 hover:bg-gray-100'"
                        >
                            <component :is="item.icon" class="h-4 w-4" aria-hidden="true" />
                            {{ item.name }}
                        </Link>
                    </div>

                    <!-- Right Side: Profile Dropdown & Mobile Menu -->
                    <div class="flex items-center gap-2">
                        <!-- Profile Dropdown (Desktop) -->
                        <div v-if="user" class="hidden md:block relative profile-dropdown">
                            <button
                                @click="profileDropdownOpen = !profileDropdownOpen"
                                class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-100 transition"
                                aria-label="User menu"
                            >
                                <UserCircleIcon class="h-6 w-6 text-gray-600" aria-hidden="true" />
                                <span class="text-sm font-medium text-gray-700">{{ user.name }}</span>
                                <ChevronDownIcon class="h-4 w-4 text-gray-500" aria-hidden="true" />
                            </button>

                            <!-- Dropdown Menu -->
                            <div
                                v-if="profileDropdownOpen"
                                class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
                            >
                                <div class="px-4 py-3 border-b border-gray-200">
                                    <p class="text-sm font-medium text-gray-900">{{ user.name }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ user.email }}</p>
                                </div>
                                
                                <Link
                                    :href="route('dashboard')"
                                    class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition"
                                >
                                    <HomeIcon class="h-4 w-4" aria-hidden="true" />
                                    Main Dashboard
                                </Link>
                                
                                <Link
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition"
                                >
                                    <ArrowLeftOnRectangleIcon class="h-4 w-4" aria-hidden="true" />
                                    Sign Out
                                </Link>
                            </div>
                        </div>

                        <!-- Mobile menu button -->
                        <button
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            class="md:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100"
                            aria-label="Toggle menu"
                        >
                            <Bars3Icon v-if="!mobileMenuOpen" class="h-6 w-6" aria-hidden="true" />
                            <XMarkIcon v-else class="h-6 w-6" aria-hidden="true" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Navigation -->
            <div v-if="mobileMenuOpen" class="md:hidden border-t border-gray-200 bg-white">
                <div class="px-4 py-3 space-y-1">
                    <!-- Navigation Links -->
                    <Link
                        v-for="item in navigation"
                        :key="item.name"
                        :href="item.href"
                        @click="mobileMenuOpen = false"
                        class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition"
                        :class="$page.url.startsWith(item.href) 
                            ? 'bg-blue-50 text-blue-700' 
                            : 'text-gray-700 hover:bg-gray-100'"
                    >
                        <component :is="item.icon" class="h-5 w-5" aria-hidden="true" />
                        {{ item.name }}
                    </Link>

                    <!-- User Section (Mobile) -->
                    <div v-if="user" class="pt-3 mt-3 border-t border-gray-200">
                        <div class="px-4 py-2 mb-2">
                            <p class="text-sm font-medium text-gray-900">{{ user.name }}</p>
                            <p class="text-xs text-gray-500">{{ user.email }}</p>
                        </div>
                        
                        <Link
                            :href="route('dashboard')"
                            @click="mobileMenuOpen = false"
                            class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition"
                        >
                            <HomeIcon class="h-5 w-5" aria-hidden="true" />
                            Main Dashboard
                        </Link>
                        
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            @click="mobileMenuOpen = false"
                            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-red-600 hover:bg-red-50 rounded-lg transition"
                        >
                            <ArrowLeftOnRectangleIcon class="h-5 w-5" aria-hidden="true" />
                            Sign Out
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="text-center text-sm text-gray-600">
                    <p>© {{ new Date().getFullYear() }} MyGrowNet. All rights reserved.</p>
                    <p class="mt-1">Quick Invoice - Create professional documents instantly</p>
                </div>
            </div>
        </footer>
    </div>
</template>
