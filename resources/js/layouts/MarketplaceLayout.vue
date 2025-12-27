<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import {
    HomeIcon,
    ShoppingCartIcon,
    MagnifyingGlassIcon,
    UserCircleIcon,
    XMarkIcon,
    ArrowRightOnRectangleIcon,
    Bars3Icon,
    BuildingStorefrontIcon,
    ClipboardDocumentListIcon,
    HeartIcon,
    TagIcon,
} from '@heroicons/vue/24/outline';
import {
    HomeIcon as HomeIconSolid,
    ShoppingCartIcon as ShoppingCartIconSolid,
    UserCircleIcon as UserCircleIconSolid,
} from '@heroicons/vue/24/solid';
import PWAInstallPrompt from '@/components/Marketplace/PWAInstallPrompt.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const cartCount = computed(() => (page.props.cartCount as number) || 0);
const cartItems = computed(() => (page.props.cartItems as any[]) || []);
const cartSubtotal = computed(() => page.props.cartFormattedSubtotal as string);

const mobileMenuOpen = ref(false);
const cartOpen = ref(false);
const searchOpen = ref(false);
const searchQuery = ref('');

const navigation = [
    { name: 'Home', href: 'marketplace.home', icon: HomeIcon, iconSolid: HomeIconSolid },
    { name: 'Cart', href: 'marketplace.cart', icon: ShoppingCartIcon, iconSolid: ShoppingCartIconSolid, badge: cartCount },
    { name: 'Account', href: 'marketplace.orders.index', icon: UserCircleIcon, iconSolid: UserCircleIconSolid, auth: true },
];

const isActive = (routeName: string) => {
    return route().current(routeName) || route().current(routeName + '.*');
};

const handleSearch = () => {
    if (searchQuery.value.trim()) {
        router.get(route('marketplace.search'), { q: searchQuery.value });
        searchOpen.value = false;
    }
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

onMounted(() => {
    document.addEventListener('click', (e) => {
        const target = e.target as HTMLElement;
        if (!target.closest('.mobile-menu-container')) {
            mobileMenuOpen.value = false;
            cartOpen.value = false;
        }
    });
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <!-- Header -->
        <header class="sticky top-0 z-40 bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <Link :href="route('marketplace.home')" class="flex items-center gap-2">
                        <div class="flex aspect-square size-10 items-center justify-center rounded-xl bg-gradient-to-br from-orange-500 to-amber-500 shadow-lg">
                            <BuildingStorefrontIcon class="size-6 text-white" aria-hidden="true" />
                        </div>
                        <div class="hidden sm:flex flex-col">
                            <span class="text-lg font-bold text-gray-900 leading-tight">GrowMarket</span>
                            <span class="text-[10px] text-gray-500 leading-tight">Trust-First Shopping</span>
                        </div>
                    </Link>

                    <!-- Search Bar (Desktop) -->
                    <div class="hidden md:flex flex-1 max-w-lg mx-8">
                        <form @submit.prevent="handleSearch" class="w-full">
                            <div class="relative">
                                <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Search products..."
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-full focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                />
                            </div>
                        </form>
                    </div>

                    <!-- Right Actions -->
                    <div class="flex items-center gap-2">
                        <!-- Mobile Search Toggle -->
                        <button 
                            @click="searchOpen = !searchOpen"
                            class="md:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100"
                            aria-label="Search"
                        >
                            <MagnifyingGlassIcon class="h-6 w-6" aria-hidden="true" />
                        </button>

                        <!-- Cart with Dropdown -->
                        <div class="relative mobile-menu-container">
                            <button 
                                @click.stop="cartOpen = !cartOpen"
                                class="relative p-2 rounded-lg text-gray-600 hover:bg-gray-100"
                                aria-label="Shopping cart"
                            >
                                <ShoppingCartIcon class="h-6 w-6" aria-hidden="true" />
                                <span 
                                    v-if="cartCount > 0"
                                    class="absolute -top-1 -right-1 min-w-[20px] h-5 bg-orange-500 rounded-full flex items-center justify-center"
                                >
                                    <span class="text-xs font-bold text-white">{{ cartCount > 9 ? '9+' : cartCount }}</span>
                                </span>
                            </button>

                            <!-- Cart Dropdown -->
                            <Transition
                                enter-active-class="transition ease-out duration-100"
                                enter-from-class="opacity-0 scale-95"
                                enter-to-class="opacity-100 scale-100"
                                leave-active-class="transition ease-in duration-75"
                                leave-from-class="opacity-100 scale-100"
                                leave-to-class="opacity-0 scale-95"
                            >
                                <div 
                                    v-if="cartOpen"
                                    class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-50"
                                >
                                    <div v-if="cartCount > 0" class="max-h-96 overflow-y-auto">
                                        <!-- Cart Header -->
                                        <div class="px-4 py-2 border-b border-gray-200">
                                            <h3 class="font-semibold text-gray-900">Shopping Cart</h3>
                                            <p class="text-sm text-gray-500">{{ cartCount }} {{ cartCount === 1 ? 'item' : 'items' }}</p>
                                        </div>

                                        <!-- Cart Items Preview -->
                                        <div class="py-2">
                                            <div 
                                                v-for="item in cartItems" 
                                                :key="item.product_id"
                                                class="px-4 py-2 hover:bg-gray-50 transition-colors"
                                            >
                                                <div class="flex gap-3">
                                                    <div class="w-12 h-12 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                                        <img 
                                                            v-if="item.image_url"
                                                            :src="item.image_url"
                                                            :alt="item.name"
                                                            class="w-full h-full object-cover"
                                                        />
                                                        <div v-else class="w-full h-full flex items-center justify-center">
                                                            <ShoppingCartIcon class="h-6 w-6 text-gray-300" aria-hidden="true" />
                                                        </div>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-medium text-gray-900 truncate">{{ item.name }}</p>
                                                        <div class="flex items-center justify-between mt-1">
                                                            <span class="text-xs text-gray-500">Qty: {{ item.quantity }}</span>
                                                            <span class="text-sm font-semibold text-orange-600">{{ item.formatted_total }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Show more indicator if there are more items -->
                                            <div v-if="cartCount > 3" class="px-4 py-2 text-center">
                                                <p class="text-xs text-gray-500">+ {{ cartCount - 3 }} more {{ cartCount - 3 === 1 ? 'item' : 'items' }}</p>
                                            </div>
                                        </div>

                                        <!-- Subtotal -->
                                        <div class="px-4 py-2 border-t border-gray-200">
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-medium text-gray-700">Subtotal:</span>
                                                <span class="text-lg font-bold text-gray-900">{{ cartSubtotal }}</span>
                                            </div>
                                        </div>

                                        <!-- Cart Actions -->
                                        <div class="px-4 py-3 border-t border-gray-200 space-y-2">
                                            <Link 
                                                :href="route('marketplace.cart')"
                                                class="block w-full px-4 py-2 text-center bg-gray-100 text-gray-900 font-medium rounded-lg hover:bg-gray-200 transition-colors"
                                                @click="cartOpen = false"
                                            >
                                                View Full Cart
                                            </Link>
                                            <Link 
                                                :href="route('marketplace.checkout')"
                                                class="block w-full px-4 py-2 text-center bg-orange-500 text-white font-medium rounded-lg hover:bg-orange-600 transition-colors"
                                                @click="cartOpen = false"
                                            >
                                                Proceed to Checkout
                                            </Link>
                                        </div>
                                    </div>

                                    <!-- Empty Cart -->
                                    <div v-else class="px-4 py-8 text-center">
                                        <ShoppingCartIcon class="h-12 w-12 mx-auto text-gray-300 mb-3" aria-hidden="true" />
                                        <p class="text-gray-500 mb-4">Your cart is empty</p>
                                        <Link 
                                            :href="route('marketplace.home')"
                                            class="inline-block px-4 py-2 bg-orange-500 text-white font-medium rounded-lg hover:bg-orange-600 transition-colors"
                                            @click="cartOpen = false"
                                        >
                                            Start Shopping
                                        </Link>
                                    </div>
                                </div>
                            </Transition>
                        </div>

                        <!-- User Menu -->
                        <div v-if="user" class="relative mobile-menu-container">
                            <button 
                                @click.stop="mobileMenuOpen = !mobileMenuOpen"
                                class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-gray-100"
                            >
                                <div class="h-8 w-8 rounded-full bg-gradient-to-br from-orange-500 to-amber-500 flex items-center justify-center">
                                    <span class="text-sm font-semibold text-white">{{ getInitials(user.name) }}</span>
                                </div>
                            </button>

                            <!-- Dropdown -->
                            <Transition
                                enter-active-class="transition ease-out duration-100"
                                enter-from-class="opacity-0 scale-95"
                                enter-to-class="opacity-100 scale-100"
                                leave-active-class="transition ease-in duration-75"
                                leave-from-class="opacity-100 scale-100"
                                leave-to-class="opacity-0 scale-95"
                            >
                                <div 
                                    v-if="mobileMenuOpen"
                                    class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50"
                                >
                                    <div class="px-4 py-2 border-b border-gray-100">
                                        <p class="font-medium text-gray-900">{{ user.name }}</p>
                                        <p class="text-sm text-gray-500">{{ user.email }}</p>
                                    </div>
                                    
                                    <Link 
                                        :href="route('marketplace.orders.index')"
                                        class="flex items-center gap-3 px-4 py-2 text-gray-700 hover:bg-gray-50"
                                    >
                                        <ClipboardDocumentListIcon class="h-5 w-5" aria-hidden="true" />
                                        My Orders
                                    </Link>
                                    
                                    <Link 
                                        :href="route('marketplace.seller.dashboard')"
                                        class="flex items-center gap-3 px-4 py-2 text-gray-700 hover:bg-gray-50"
                                    >
                                        <BuildingStorefrontIcon class="h-5 w-5" aria-hidden="true" />
                                        Seller Dashboard
                                    </Link>
                                    
                                    <div class="border-t border-gray-100 mt-2 pt-2">
                                        <button 
                                            @click="handleLogout"
                                            class="flex items-center gap-3 px-4 py-2 text-red-600 hover:bg-red-50 w-full"
                                        >
                                            <ArrowRightOnRectangleIcon class="h-5 w-5" aria-hidden="true" />
                                            Sign Out
                                        </button>
                                    </div>
                                </div>
                            </Transition>
                        </div>

                        <template v-else>
                            <Link 
                                :href="route('login')"
                                class="hidden sm:inline-flex px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900"
                            >
                                Sign In
                            </Link>
                            <Link 
                                :href="route('register')"
                                class="px-4 py-2 text-sm font-medium text-white bg-orange-500 rounded-lg hover:bg-orange-600"
                            >
                                Join
                            </Link>
                        </template>
                    </div>
                </div>

                <!-- Mobile Search -->
                <Transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-2"
                >
                    <div v-if="searchOpen" class="md:hidden pb-4">
                        <form @submit.prevent="handleSearch">
                            <div class="relative">
                                <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Search products..."
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-full focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                    autofocus
                                />
                            </div>
                        </form>
                    </div>
                </Transition>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- PWA Install Prompt -->
        <PWAInstallPrompt />

        <!-- Footer -->
        <footer class="bg-gray-900 text-white mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                    <div>
                        <h3 class="font-semibold mb-4">Shop</h3>
                        <ul class="space-y-2 text-sm text-gray-400">
                            <li><Link :href="route('marketplace.home')" class="hover:text-white">All Products</Link></li>
                            <li><Link :href="route('marketplace.search')" class="hover:text-white">Categories</Link></li>
                            <li><Link :href="route('marketplace.search', { sort: 'popular' })" class="hover:text-white">Popular</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="font-semibold mb-4">Sell</h3>
                        <ul class="space-y-2 text-sm text-gray-400">
                            <li><Link :href="route('marketplace.seller.join')" class="hover:text-white">Become a Seller</Link></li>
                            <li><Link :href="route('marketplace.seller.dashboard')" class="hover:text-white">Seller Dashboard</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="font-semibold mb-4">Support</h3>
                        <ul class="space-y-2 text-sm text-gray-400">
                            <li><Link :href="route('marketplace.help')" class="hover:text-white">Help Center</Link></li>
                            <li><Link :href="route('marketplace.buyer-protection')" class="hover:text-white">Buyer Protection</Link></li>
                            <li><Link :href="route('marketplace.seller-guide')" class="hover:text-white">Seller Guide</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="font-semibold mb-4">About</h3>
                        <ul class="space-y-2 text-sm text-gray-400">
                            <li><Link :href="route('marketplace.about')" class="hover:text-white">About Us</Link></li>
                            <li><Link :href="route('marketplace.terms')" class="hover:text-white">Terms of Service</Link></li>
                            <li><Link :href="route('marketplace.privacy')" class="hover:text-white">Privacy Policy</Link></li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-gray-800 mt-8 pt-8 text-center text-sm text-gray-400">
                    <p>&copy; {{ new Date().getFullYear() }} GrowMarket. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
