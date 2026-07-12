<template>
    <div class="min-h-screen bg-gray-50">
        <header class="bg-white shadow-sm sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <Link :href="route('growmart.home')" class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                        <div class="p-1.5 bg-emerald-600 rounded-lg">
                            <ShoppingCartIcon class="h-6 w-6 text-white" />
                        </div>
                        <span class="text-xl font-bold text-gray-900">GrowMart</span>
                    </Link>

                    <div class="flex-1 max-w-lg mx-2 md:mx-4">
                        <div class="relative">
                            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                            <input
                                v-model="searchQuery"
                                @keydown.enter="search"
                                type="text"
                                placeholder="Search groceries..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm"
                            />
                        </div>
                    </div>

                    <div class="flex items-center gap-1 sm:gap-3">
                        <Link :href="route('growmart.wishlist')" class="relative p-2 text-gray-600 hover:text-red-500 transition-colors" title="Wishlist">
                            <HeartIcon class="h-6 w-6" />
                        </Link>
                        <NotificationBell />
                        <Link :href="route('growmart.cart')" class="relative p-2 text-gray-600 hover:text-emerald-600 transition-colors">
                            <ShoppingBagIcon class="h-6 w-6" />
                            <span v-if="cartCount > 0" class="absolute -top-0.5 -right-0.5 bg-emerald-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium">
                                {{ cartCount > 99 ? '99+' : cartCount }}
                            </span>
                        </Link>
                        <Link :href="route('growmart.orders.index')" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 text-sm text-gray-600 hover:text-emerald-600 transition-colors">
                            <ClipboardDocumentListIcon class="h-5 w-5" />
                            Orders
                        </Link>
                        <template v-if="$page.props.auth.user">
                            <div class="hidden sm:flex items-center gap-2 pl-2 border-l border-gray-200">
                                <span class="text-sm text-gray-500">{{ $page.props.auth.user.name }}</span>
                                <button @click="logout" class="text-sm text-gray-400 hover:text-red-500 transition-colors">Sign Out</button>
                            </div>
                        </template>
                        <template v-else>
                            <Link :href="route('growmart.login')" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-emerald-600 hover:text-emerald-700 transition-colors">
                                Sign In
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </header>

        <main>
            <slot />
        </main>

        <ToastContainer />

        <footer class="bg-white border-t border-gray-200 mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Company</h3>
                        <ul class="mt-4 space-y-2">
                            <li><Link :href="route('growmart.home')" class="text-sm text-gray-600 hover:text-emerald-600">Home</Link></li>
                            <li><Link :href="route('growmart.products.index')" class="text-sm text-gray-600 hover:text-emerald-600">Products</Link></li>
                            <li><Link :href="route('growmart.contact')" class="text-sm text-gray-600 hover:text-emerald-600">Contact</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Legal</h3>
                        <ul class="mt-4 space-y-2">
                            <li><Link :href="route('growmart.terms')" class="text-sm text-gray-600 hover:text-emerald-600">Terms of Service</Link></li>
                            <li><Link :href="route('growmart.refund')" class="text-sm text-gray-600 hover:text-emerald-600">Refund Policy</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Contact</h3>
                        <ul class="mt-4 space-y-2">
                            <li class="text-sm text-gray-600">{{ contact.phone }}</li>
                            <li class="text-sm text-gray-600">{{ contact.email }}</li>
                            <li class="text-sm text-gray-600">{{ contact.address }}</li>
                        </ul>
                    </div>
                </div>
                <div class="mt-8 pt-8 border-t border-gray-200 text-center">
                    <p class="text-sm text-gray-500">&copy; {{ new Date().getFullYear() }} GrowMart. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ShoppingCartIcon, ShoppingBagIcon, MagnifyingGlassIcon, ClipboardDocumentListIcon, HeartIcon } from '@heroicons/vue/24/outline';
import ToastContainer from '@/components/GrowMart/ToastContainer.vue';
import { useToast } from '@/composables/useToast';
import NotificationBell from '@/components/GrowMart/NotificationBell.vue';

const page = usePage();
const { toast } = useToast();
const searchQuery = ref('');

const contact = {
    email: 'support@growmart.co.zm',
    phone: '+260 97 123 4567',
    address: 'Plot 123, Great East Road, Lusaka, Zambia',
};

const cartCount = computed(() => (page.props as any).cartCount ?? 0);
const flash = computed(() => (page.props as any).flash);

watch(flash, (f) => {
    if (!f) return;
    if (f.success) toast.success(f.success, 5000);
    if (f.error) toast.error(f.error, 8000);
    if (f.warning) toast.warning(f.warning, 5000);
    if (f.info) toast.info(f.info, 5000);
}, { immediate: true, deep: true });

const search = () => {
    if (searchQuery.value.trim()) {
        router.get(route('growmart.products.index', { q: searchQuery.value }));
    }
};

const logout = () => {
    router.post(route('growmart.logout'));
};
</script>
