<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ShoppingCartIcon, XMarkIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

interface Product {
    id: number;
    name: string;
    description: string | null;
    price: number;
    sale_price: number | null;
    currency: string;
    category: string | null;
    is_featured: boolean;
    images: { path: string }[];
    image_url: string | null;
}

interface CartItem {
    id: number;
    name: string;
    price: number;
    image_url: string | null;
    quantity: number;
}

interface Props {
    business: { id: number; name: string; slug: string; logo_path: string | null; phone: string | null; whatsapp: string | null; currency: string; profile: any };
    products: { data: Product[]; current_page: number; last_page: number; total: number };
    categories: string[];
    cart: Record<number, CartItem>;
    filters: { category?: string; search?: string };
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters.search || '');
const selectedCategory = ref(props.filters.category || '');
const showCart = ref(false);

const cartCount = computed(() => Object.values(props.cart).reduce((sum, item) => sum + item.quantity, 0));
const cartTotal = computed(() => Object.values(props.cart).reduce((sum, item) => sum + item.price * item.quantity, 0));

const applyFilters = () => {
    router.get(route('bizboost.public.shop', props.business.slug), {
        search: searchQuery.value || undefined,
        category: selectedCategory.value || undefined,
    }, { preserveState: true });
};

const addToCart = (product: Product) => {
    router.post(route('bizboost.public.shop.cart.add', props.business.slug), {
        product_id: product.id,
        quantity: 1,
    }, { preserveState: true });
};

const updateCartItem = (productId: number, quantity: number) => {
    router.post(route('bizboost.public.shop.cart.update', props.business.slug), {
        product_id: productId,
        quantity: Math.max(0, quantity),
    }, { preserveState: true });
};

const formatPrice = (amount: number) => {
    const currency = props.business.currency || 'ZMW';
    return `${currency} ${Number(amount).toFixed(2)}`;
};

const whatsappLink = computed(() => {
    const phone = props.business.whatsapp || props.business.phone;
    if (!phone) return '#';
    return `https://wa.me/${phone.replace(/\D/g, '')}`;
});
</script>

<template>
    <Head :title="`Shop - ${business.name}`" />
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white border-b border-gray-100 sticky top-0 z-30">
            <div class="max-w-5xl mx-auto px-4 h-14 flex items-center justify-between">
                <Link :href="route('bizboost.public.business', business.slug)" class="font-semibold text-gray-900 truncate">
                    {{ business.name }}
                </Link>
                <div class="flex items-center gap-3">
                    <button @click="showCart = !showCart" class="relative p-2 text-gray-600 hover:text-gray-900">
                        <ShoppingCartIcon class="h-6 w-6" />
                        <span v-if="cartCount > 0" class="absolute -top-1 -right-1 bg-emerald-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium">
                            {{ cartCount }}
                        </span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Hero -->
        <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 py-10 text-center text-white">
            <h1 class="text-2xl font-bold">Our Store</h1>
            <p class="text-emerald-100 mt-1">{{ products.total }} product{{ products.total !== 1 ? 's' : '' }} available</p>
        </div>

        <div class="max-w-5xl mx-auto px-4 py-6">
            <!-- Search + Filters -->
            <div class="bg-white rounded-xl shadow-sm border p-3 mb-6 flex gap-2">
                <input v-model="searchQuery" placeholder="Search products..." class="flex-1 px-3 py-2 border rounded-lg text-sm"
                    @keyup.enter="applyFilters" />
                <select v-model="selectedCategory" @change="applyFilters" class="px-3 py-2 border rounded-lg text-sm">
                    <option value="">All</option>
                    <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                </select>
                <button @click="applyFilters" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700">Search</button>
            </div>

            <!-- Products Grid -->
            <div v-if="products.data.length === 0" class="bg-white rounded-xl shadow-sm border p-12 text-center text-gray-500">
                No products found
            </div>
            <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                <div v-for="product in products.data" :key="product.id"
                    class="bg-white rounded-xl shadow-sm border hover:shadow-md transition-shadow overflow-hidden">
                    <Link :href="route('bizboost.public.shop.product', [business.slug, product.id])" class="block">
                        <div class="aspect-square bg-gray-100 overflow-hidden">
                            <img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="w-full h-full object-cover hover:scale-105 transition-transform" />
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                                <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                    </Link>
                    <div class="p-3">
                        <p v-if="product.category" class="text-xs text-emerald-600 font-medium uppercase tracking-wide">{{ product.category }}</p>
                        <Link :href="route('bizboost.public.shop.product', [business.slug, product.id])" class="block font-medium text-gray-900 text-sm mt-1 line-clamp-2 hover:text-emerald-700">
                            {{ product.name }}
                        </Link>
                        <div class="mt-2 flex items-center justify-between">
                            <div>
                                <span v-if="product.sale_price" class="text-sm text-gray-400 line-through mr-1">{{ formatPrice(product.price) }}</span>
                                <span class="font-bold text-gray-900">{{ formatPrice(product.sale_price || product.price) }}</span>
                            </div>
                            <button @click="addToCart(product)" class="p-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-xs font-medium">
                                Add
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="products.last_page > 1" class="mt-8 flex justify-center gap-2">
                <Link v-if="products.current_page > 1" :href="route('bizboost.public.shop', { slug: business.slug, page: products.current_page - 1 })" class="px-4 py-2 bg-white border rounded-lg text-sm hover:bg-gray-50">Previous</Link>
                <span class="px-3 py-2 text-sm text-gray-500">Page {{ products.current_page }} / {{ products.last_page }}</span>
                <Link v-if="products.current_page < products.last_page" :href="route('bizboost.public.shop', { slug: business.slug, page: products.current_page + 1 })" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700">Next</Link>
            </div>
        </div>

        <!-- Cart Slideover -->
        <div v-if="showCart" class="fixed inset-0 z-50 flex">
            <div class="absolute inset-0 bg-black/40" @click="showCart = false"></div>
            <div class="relative ml-auto w-full max-w-sm bg-white h-full shadow-xl flex flex-col">
                <div class="flex items-center justify-between p-4 border-b">
                    <h2 class="font-semibold text-gray-900">Cart ({{ cartCount }})</h2>
                    <button @click="showCart = false"><XMarkIcon class="h-6 w-6 text-gray-500" /></button>
                </div>
                <div v-if="Object.keys(cart).length === 0" class="flex-1 flex items-center justify-center text-gray-400 text-sm">Your cart is empty</div>
                <div v-else class="flex-1 overflow-y-auto p-4 space-y-4">
                    <div v-for="(item, id) in cart" :key="id" class="flex gap-3 bg-gray-50 rounded-lg p-3">
                        <img v-if="item.image_url" :src="item.image_url" class="w-16 h-16 rounded-lg object-cover" />
                        <div v-else class="w-16 h-16 rounded-lg bg-gray-200 flex items-center justify-center text-gray-400 text-xs">No img</div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-sm text-gray-900 truncate">{{ item.name }}</p>
                            <p class="text-sm text-gray-500">{{ formatPrice(item.price) }}</p>
                            <div class="flex items-center gap-2 mt-2">
                                <button @click="updateCartItem(Number(id), item.quantity - 1)" class="w-7 h-7 rounded border flex items-center justify-center text-gray-600 hover:bg-gray-200">-</button>
                                <span class="w-6 text-center text-sm font-medium">{{ item.quantity }}</span>
                                <button @click="updateCartItem(Number(id), item.quantity + 1)" class="w-7 h-7 rounded border flex items-center justify-center text-gray-600 hover:bg-gray-200">+</button>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-sm">{{ formatPrice(item.price * item.quantity) }}</p>
                            <button @click="updateCartItem(Number(id), 0)" class="text-xs text-red-500 hover:text-red-700 mt-1">Remove</button>
                        </div>
                    </div>
                </div>
                <div v-if="Object.keys(cart).length > 0" class="border-t p-4 space-y-3">
                    <div class="flex justify-between font-semibold text-lg"><span>Total</span><span>{{ formatPrice(cartTotal) }}</span></div>
                    <Link :href="route('bizboost.public.shop.checkout', business.slug)" class="block w-full text-center py-3 bg-emerald-600 text-white rounded-xl font-medium hover:bg-emerald-700">
                        Proceed to Checkout
                    </Link>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="py-6 text-center text-gray-400 text-xs border-t bg-white mt-8">
            &copy; {{ new Date().getFullYear() }} {{ business.name }}. Powered by BizBoost
        </footer>
    </div>
</template>

<style scoped>
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>
