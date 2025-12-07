<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, type Component } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useHaptics } from '@/composables/useHaptics';
import {
    HomeIcon,
    ShoppingBagIcon,
    UsersIcon,
    CurrencyDollarIcon,
    EllipsisHorizontalIcon,
} from '@heroicons/vue/24/outline';
import {
    HomeIcon as HomeIconSolid,
    ShoppingBagIcon as ShoppingBagIconSolid,
    UsersIcon as UsersIconSolid,
    CurrencyDollarIcon as CurrencyDollarIconSolid,
    EllipsisHorizontalIcon as EllipsisHorizontalIconSolid,
} from '@heroicons/vue/24/solid';

interface NavItem {
    name: string;
    href: string;
    icon: Component;
    iconActive: Component;
    badge?: number;
    routePattern?: string;
}

interface Props {
    hideOnScroll?: boolean;
    moreMenuOpen?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    hideOnScroll: true,
    moreMenuOpen: false,
});

const emit = defineEmits<{
    (e: 'openMore'): void;
}>();

const page = usePage();
const { light } = useHaptics();

// Navigation items
const navItems: NavItem[] = [
    {
        name: 'Home',
        href: '/bizboost',
        icon: HomeIcon,
        iconActive: HomeIconSolid,
        routePattern: 'bizboost.dashboard',
    },
    {
        name: 'Products',
        href: '/bizboost/products',
        icon: ShoppingBagIcon,
        iconActive: ShoppingBagIconSolid,
        routePattern: 'bizboost.products.*',
    },
    {
        name: 'Customers',
        href: '/bizboost/customers',
        icon: UsersIcon,
        iconActive: UsersIconSolid,
        routePattern: 'bizboost.customers.*',
    },
    {
        name: 'Sales',
        href: '/bizboost/sales',
        icon: CurrencyDollarIcon,
        iconActive: CurrencyDollarIconSolid,
        routePattern: 'bizboost.sales.*',
    },
];

const moreItem = {
    name: 'More',
    icon: EllipsisHorizontalIcon,
    iconActive: EllipsisHorizontalIconSolid,
};

// Check if a nav item is active
const isActive = (item: NavItem): boolean => {
    if (item.routePattern) {
        return route().current(item.routePattern);
    }
    return page.url === item.href;
};

// Scroll-aware visibility
const isVisible = ref(true);
const lastScrollY = ref(0);
const scrollThreshold = 10;

const handleScroll = () => {
    if (!props.hideOnScroll) return;

    const currentScrollY = window.scrollY;
    const diff = currentScrollY - lastScrollY.value;

    if (Math.abs(diff) < scrollThreshold) return;

    if (diff > 0 && currentScrollY > 100) {
        // Scrolling down
        isVisible.value = false;
    } else {
        // Scrolling up
        isVisible.value = true;
    }

    lastScrollY.value = currentScrollY;
};

const handleNavClick = () => {
    light();
};

const handleMoreClick = () => {
    light();
    emit('openMore');
};

onMounted(() => {
    if (props.hideOnScroll) {
        window.addEventListener('scroll', handleScroll, { passive: true });
    }
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <nav
        :class="[
            'fixed bottom-0 left-0 right-0 z-50 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-700 transition-transform duration-300 lg:hidden',
            isVisible ? 'translate-y-0' : 'translate-y-full',
        ]"
        :style="{ paddingBottom: 'env(safe-area-inset-bottom)' }"
        role="navigation"
        aria-label="Bottom navigation"
    >
        <div class="flex items-center justify-around h-16 px-2">
            <!-- Main nav items -->
            <Link
                v-for="item in navItems"
                :key="item.name"
                :href="item.href"
                :class="[
                    'flex flex-col items-center justify-center flex-1 h-full px-1 py-2 transition-colors relative',
                    isActive(item)
                        ? 'text-violet-600 dark:text-violet-400'
                        : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300',
                ]"
                @click="handleNavClick"
                :aria-current="isActive(item) ? 'page' : undefined"
            >
                <!-- Active indicator -->
                <div
                    v-if="isActive(item)"
                    class="absolute top-1 left-1/2 -translate-x-1/2 w-8 h-1 bg-violet-600 dark:bg-violet-400 rounded-full"
                />
                
                <!-- Icon -->
                <component
                    :is="isActive(item) ? item.iconActive : item.icon"
                    class="h-6 w-6"
                    aria-hidden="true"
                />
                
                <!-- Label -->
                <span class="text-xs mt-1 font-medium">{{ item.name }}</span>
                
                <!-- Badge -->
                <span
                    v-if="item.badge && item.badge > 0"
                    class="absolute top-1 right-1/4 min-w-[18px] h-[18px] flex items-center justify-center bg-red-500 text-white text-[10px] font-bold rounded-full px-1"
                >
                    {{ item.badge > 99 ? '99+' : item.badge }}
                </span>
            </Link>

            <!-- More button -->
            <button
                :class="[
                    'flex flex-col items-center justify-center flex-1 h-full px-1 py-2 transition-colors',
                    moreMenuOpen
                        ? 'text-violet-600 dark:text-violet-400'
                        : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300',
                ]"
                @click="handleMoreClick"
                aria-label="Open more menu"
                :aria-expanded="moreMenuOpen"
            >
                <component
                    :is="moreMenuOpen ? moreItem.iconActive : moreItem.icon"
                    class="h-6 w-6"
                    aria-hidden="true"
                />
                <span class="text-xs mt-1 font-medium">{{ moreItem.name }}</span>
            </button>
        </div>
    </nav>
</template>
