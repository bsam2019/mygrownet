<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { ShoppingCartIcon, ShoppingBagIcon, CheckIcon, MinusIcon, PlusIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';

interface Product {
    id: number;
    name: string;
    slug: string;
    price: number;
    priceFormatted: string;
    comparePrice: number | null;
    comparePriceFormatted: string | null;
    image: string | null;
    images: string[];
    description: string | null;
    shortDescription: string | null;
    category: string | null;
    sku: string | null;
    inStock: boolean;
    stockQuantity: number | null;
    trackStock: boolean;
    isFeatured: boolean;
    hasDiscount: boolean;
    discountPercentage: number;
}

interface Site {
    id: number;
    name: string;
    subdomain: string;
    logo: string | null;
}

interface Settings {
    navigation?: { logo?: string; logoText?: string };
    footer?: { backgroundColor?: string; textColor?: string };
}

interface CartItem {
    product: { id: number; name: string; slug: string; price: number; priceFormatted: string; image: string | null };
    quantity: number;
}

const props = defineProps<{
    site: Site;
    product: Product;
    relatedProducts: Product[];
    settings: Settings | null;
}>();

const quantity = ref(1);
const selectedImage = ref(0);
const cartNotification = ref('');

// Cart state
const cart = ref<CartItem[]>([]);
const cartStorageKey = computed(() => `gb_cart_${props.site.subdomain}`);

onMounted(() => {
    const savedCart = localStorage.getItem(cartStorageKey.value);
    if (savedCart) {
        try { cart.value = JSON.parse(savedCart); } catch (e) { cart.value = []; }
    }
});

const saveCart = () => localStorage.setItem(cartStorageKey.value, JSON.stringify(cart.value));

const allImages = computed(() => {
    const images = props.product.images || [];
    if (props.product.image && !images.includes(props.product.image)) {
        return [props.product.image, ...images];
    }
    return images.length > 0 ? images : (props.product.image ? [props.product.image] : []);
});

const increaseQuantity = () => {
    if (!props.product.trackStock || quantity.value < (props.product.stockQuantity || 999)) {
        quantity.value++;
    }
};

const decreaseQuantity = () => {
    if (quantity.value > 1) quantity.value--;
};

const addToCart = () => {
    const existingItem = cart.value.find(item => item.product.id === props.product.id);
    if (existingItem) {
        existingItem.quantity += quantity.value;
    } else {
        cart.value.push({
            product: {
                id: props.product.id,
                name: props.product.name,
                slug: props.product.slug,
                price: props.product.price,
                priceFormatted: props.product.priceFormatted,
                image: props.product.image,
            },
            quantity: quantity.value,
        });
    }
    saveCart();
    cartNotification.value = `${props.product.name} added to cart`;
    setTimeout(() => { cartNotification.value = ''; }, 2000);
};

const getProductUrl = (product: Product) => `/sites/${props.site.subdomain}/product/${product.slug}`;
const homeUrl = computed(() => `/sites/${props.site.subdomain}`);
</script>

<template>
    <Head :title="`${product.name} - ${site.name}`" />

    <div class="min-h-screen bg-gray-50">
        <!-- Simple Header -->
        <nav class="bg-white border-b border-gray-200 sticky top-0 z-40">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <a :href="homeUrl" class="flex items-center gap-2 text-gray-600 hover:text-gray-900">
                        <ArrowLeftIcon class="w-5 h-5" aria-hidden="true" />
                        <span class="text-sm font-medium">Back to Store</span>
                    </a>
                    <a :href="homeUrl" class="text-lg font-bold text-gray-900">{{ site.name }}</a>
                    <div class="w-24"></div>
                </div>
            </div>
        </nav>

        <!-- Product Content -->
        <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 p-6 lg:p-8">
                    <!-- Image Gallery -->
                    <div>
                        <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden mb-4">
                            <img v-if="allImages[selectedImage]" :src="allImages[selectedImage]" :alt="product.name" class="w-full h-full object-cover" />
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                                <ShoppingBagIcon class="w-24 h-24" aria-hidden="true" />
                            </div>
                        </div>
                        <div v-if="allImages.length > 1" class="flex gap-2 overflow-x-auto">
                            <button v-for="(img, idx) in allImages" :key="idx" @click="selectedImage = idx"
                                :class="['w-20 h-20 rounded-lg overflow-hidden border-2 flex-shrink-0', selectedImage === idx ? 'border-blue-500' : 'border-transparent']">
                                <img :src="img" :alt="`${product.name} ${idx + 1}`" class="w-full h-full object-cover" />
                            </button>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div>
                        <div v-if="product.category" class="text-sm text-blue-600 font-medium mb-2">{{ product.category }}</div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-4">{{ product.name }}</h1>
                        
                        <!-- Price -->
                        <div class="flex items-center gap-3 mb-6">
                            <span class="text-3xl font-bold text-blue-600">{{ product.priceFormatted }}</span>
                            <span v-if="product.comparePriceFormatted" class="text-xl text-gray-400 line-through">{{ product.comparePriceFormatted }}</span>
                            <span v-if="product.hasDiscount" class="bg-red-100 text-red-600 text-sm font-semibold px-2 py-1 rounded">-{{ product.discountPercentage }}%</span>
                        </div>

                        <!-- Stock Status -->
                        <div class="mb-6">
                            <span v-if="product.inStock" class="inline-flex items-center gap-1 text-green-600 text-sm font-medium">
                                <CheckIcon class="w-4 h-4" aria-hidden="true" /> In Stock
                                <span v-if="product.trackStock && product.stockQuantity" class="text-gray-500">({{ product.stockQuantity }} available)</span>
                            </span>
                            <span v-else class="text-red-600 text-sm font-medium">Out of Stock</span>
                        </div>

                        <!-- Short Description -->
                        <p v-if="product.shortDescription" class="text-gray-600 mb-6">{{ product.shortDescription }}</p>

                        <!-- Quantity & Add to Cart -->
                        <div v-if="product.inStock" class="flex flex-col sm:flex-row gap-4 mb-6">
                            <div class="flex items-center border rounded-lg">
                                <button @click="decreaseQuantity" class="p-3 hover:bg-gray-100" aria-label="Decrease quantity">
                                    <MinusIcon class="w-5 h-5" aria-hidden="true" />
                                </button>
                                <span class="w-12 text-center font-medium">{{ quantity }}</span>
                                <button @click="increaseQuantity" class="p-3 hover:bg-gray-100" aria-label="Increase quantity">
                                    <PlusIcon class="w-5 h-5" aria-hidden="true" />
                                </button>
                            </div>
                            <button @click="addToCart" class="flex-1 py-3 px-6 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                                <ShoppingCartIcon class="w-5 h-5" aria-hidden="true" />
                                Add to Cart
                            </button>
                        </div>

                        <!-- SKU -->
                        <div v-if="product.sku" class="text-sm text-gray-500">SKU: {{ product.sku }}</div>
                    </div>
                </div>

                <!-- Description -->
                <div v-if="product.description" class="border-t p-6 lg:p-8">
                    <h2 class="text-lg font-semibold mb-4">Description</h2>
                    <div class="prose prose-sm max-w-none text-gray-600" v-html="product.description"></div>
                </div>
            </div>

            <!-- Related Products -->
            <div v-if="relatedProducts.length > 0" class="mt-12">
                <h2 class="text-xl font-bold mb-6">Related Products</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <a v-for="p in relatedProducts" :key="p.id" :href="getProductUrl(p)" class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                        <div class="aspect-square bg-gray-100">
                            <img v-if="p.image" :src="p.image" :alt="p.name" class="w-full h-full object-cover" />
                        </div>
                        <div class="p-3">
                            <h3 class="font-medium text-sm line-clamp-2">{{ p.name }}</h3>
                            <p class="text-blue-600 font-bold text-sm mt-1">{{ p.priceFormatted }}</p>
                        </div>
                    </a>
                </div>
            </div>
        </main>

        <!-- Cart Notification -->
        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="translate-y-2 opacity-0" enter-to-class="translate-y-0 opacity-100" leave-active-class="transition ease-in duration-200" leave-from-class="translate-y-0 opacity-100" leave-to-class="translate-y-2 opacity-0">
            <div v-if="cartNotification" class="fixed bottom-4 left-1/2 -translate-x-1/2 bg-gray-900 text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-2 z-50">
                <CheckIcon class="w-5 h-5 text-green-400" aria-hidden="true" />
                {{ cartNotification }}
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>
