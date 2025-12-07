<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    ArrowLeftIcon, 
    ShoppingBagIcon, 
    PhoneIcon,
    ShareIcon,
    HeartIcon,
    CheckIcon,
    XMarkIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
} from '@heroicons/vue/24/outline';
import { HeartIcon as HeartSolidIcon } from '@heroicons/vue/24/solid';

interface ThemeSettings {
    primary_color: string;
    secondary_color: string;
    accent_color: string;
}

interface Profile {
    theme_settings: ThemeSettings | null;
    show_whatsapp_button: boolean;
}

interface Business {
    id: number;
    name: string;
    slug: string;
    logo_path: string | null;
    phone: string | null;
    whatsapp: string | null;
    profile: Profile | null;
}

interface ProductImage {
    id: number;
    path: string;
    is_primary: boolean;
}

interface Product {
    id: number;
    name: string;
    description: string | null;
    price: number;
    sale_price: number | null;
    category: string | null;
    sku: string | null;
    stock_quantity: number | null;
    is_featured: boolean;
    images: ProductImage[];
}

interface Props {
    business: Business;
    product: Product;
    relatedProducts?: Product[];
}

const props = defineProps<Props>();

const selectedImage = ref(0);
const isWishlisted = ref(false);
const showShareMenu = ref(false);

const defaultTheme: ThemeSettings = {
    primary_color: '#7c3aed',
    secondary_color: '#4f46e5',
    accent_color: '#10b981',
};

const theme = computed(() => ({
    ...defaultTheme,
    ...(props.business.profile?.theme_settings || {}),
}));

const formatPrice = (price: number) => `K${price.toLocaleString()}`;

const discountPercentage = computed(() => {
    if (!props.product.sale_price) return 0;
    return Math.round((1 - props.product.sale_price / props.product.price) * 100);
});

const whatsappLink = computed(() => {
    const phone = props.business.whatsapp || props.business.phone;
    if (!phone) return '#';
    const cleaned = phone.replace(/\D/g, '');
    const message = encodeURIComponent(
        `Hi! I'm interested in: ${props.product.name} (${formatPrice(props.product.sale_price || props.product.price)})`
    );
    return `https://wa.me/${cleaned}?text=${message}`;
});

const nextImage = () => {
    if (selectedImage.value < props.product.images.length - 1) {
        selectedImage.value++;
    } else {
        selectedImage.value = 0;
    }
};

const prevImage = () => {
    if (selectedImage.value > 0) {
        selectedImage.value--;
    } else {
        selectedImage.value = props.product.images.length - 1;
    }
};

const shareProduct = async () => {
    if (navigator.share) {
        try {
            await navigator.share({
                title: props.product.name,
                text: `Check out ${props.product.name} from ${props.business.name}`,
                url: window.location.href,
            });
        } catch (err) {
            // User cancelled or error
        }
    } else {
        showShareMenu.value = !showShareMenu.value;
    }
};

const copyLink = () => {
    navigator.clipboard.writeText(window.location.href);
    showShareMenu.value = false;
};
</script>

<template>
    <Head :title="`${product.name} - ${business.name}`" />

    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white border-b border-gray-100 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6">
                <div class="flex items-center justify-between h-16">
                    <!-- Back -->
                    <Link 
                        :href="route('bizboost.public.products', business.slug)" 
                        class="flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors"
                    >
                        <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
                        <span class="hidden sm:inline">Back to Products</span>
                    </Link>

                    <!-- Business Info -->
                    <Link 
                        :href="route('bizboost.public.business', business.slug)"
                        class="flex items-center gap-2"
                    >
                        <div class="w-8 h-8 rounded-lg overflow-hidden shadow-sm ring-1 ring-gray-100">
                            <img 
                                v-if="business.logo_path" 
                                :src="`/storage/${business.logo_path}`" 
                                :alt="business.name" 
                                class="w-full h-full object-cover" 
                            />
                            <div 
                                v-else 
                                class="w-full h-full flex items-center justify-center text-white font-bold text-sm"
                                :style="{ background: `linear-gradient(135deg, ${theme.primary_color}, ${theme.secondary_color})` }"
                            >
                                {{ business.name[0] }}
                            </div>
                        </div>
                        <span class="font-semibold text-gray-900 hidden sm:block">{{ business.name }}</span>
                    </Link>

                    <!-- Actions -->
                    <div class="flex items-center gap-2">
                        <button
                            @click="isWishlisted = !isWishlisted"
                            class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
                            :aria-label="isWishlisted ? 'Remove from wishlist' : 'Add to wishlist'"
                        >
                            <HeartSolidIcon v-if="isWishlisted" class="h-5 w-5 text-red-500" aria-hidden="true" />
                            <HeartIcon v-else class="h-5 w-5 text-gray-500" aria-hidden="true" />
                        </button>
                        <div class="relative">
                            <button
                                @click="shareProduct"
                                class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
                                aria-label="Share product"
                            >
                                <ShareIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                            </button>
                            <!-- Share Menu -->
                            <div 
                                v-if="showShareMenu"
                                class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50"
                            >
                                <button
                                    @click="copyLink"
                                    class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50"
                                >
                                    Copy link
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="lg:flex">
                    <!-- Image Gallery -->
                    <div class="lg:w-1/2 relative">
                        <!-- Main Image -->
                        <div class="aspect-square bg-gray-100 relative overflow-hidden">
                            <img
                                v-if="product.images.length > 0"
                                :src="`/storage/${product.images[selectedImage].path}`"
                                :alt="product.name"
                                class="w-full h-full object-contain"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100">
                                <ShoppingBagIcon class="h-32 w-32 text-gray-300" aria-hidden="true" />
                            </div>

                            <!-- Navigation Arrows -->
                            <template v-if="product.images.length > 1">
                                <button
                                    @click="prevImage"
                                    class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/90 backdrop-blur-sm rounded-full shadow-lg flex items-center justify-center hover:bg-white transition-colors"
                                    aria-label="Previous image"
                                >
                                    <ChevronLeftIcon class="h-5 w-5 text-gray-700" aria-hidden="true" />
                                </button>
                                <button
                                    @click="nextImage"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/90 backdrop-blur-sm rounded-full shadow-lg flex items-center justify-center hover:bg-white transition-colors"
                                    aria-label="Next image"
                                >
                                    <ChevronRightIcon class="h-5 w-5 text-gray-700" aria-hidden="true" />
                                </button>
                            </template>

                            <!-- Badges -->
                            <div class="absolute top-4 left-4 flex flex-col gap-2">
                                <span 
                                    v-if="product.is_featured"
                                    class="px-3 py-1.5 text-sm font-semibold rounded-full text-white"
                                    :style="{ backgroundColor: theme.accent_color }"
                                >
                                    Featured
                                </span>
                                <span 
                                    v-if="product.sale_price"
                                    class="px-3 py-1.5 text-sm font-semibold rounded-full bg-red-500 text-white"
                                >
                                    {{ discountPercentage }}% OFF
                                </span>
                            </div>
                        </div>

                        <!-- Thumbnails -->
                        <div v-if="product.images.length > 1" class="flex gap-3 p-4 overflow-x-auto">
                            <button
                                v-for="(img, index) in product.images"
                                :key="img.id"
                                @click="selectedImage = index"
                                :class="[
                                    'w-20 h-20 rounded-xl overflow-hidden flex-shrink-0 transition-all',
                                    selectedImage === index 
                                        ? 'ring-2 ring-offset-2' 
                                        : 'opacity-60 hover:opacity-100'
                                ]"
                                :style="selectedImage === index ? { '--tw-ring-color': theme.primary_color } : {}"
                            >
                                <img :src="`/storage/${img.path}`" class="w-full h-full object-cover" :alt="`${product.name} image ${index + 1}`" />
                            </button>
                        </div>
                    </div>

                    <!-- Product Details -->
                    <div class="lg:w-1/2 p-6 lg:p-10 flex flex-col">
                        <!-- Category -->
                        <span 
                            v-if="product.category" 
                            class="inline-flex items-center self-start px-3 py-1 rounded-full text-sm font-medium mb-4"
                            :style="{ backgroundColor: theme.primary_color + '15', color: theme.primary_color }"
                        >
                            {{ product.category }}
                        </span>

                        <!-- Title -->
                        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-4">{{ product.name }}</h1>

                        <!-- Price -->
                        <div class="mb-6">
                            <div v-if="product.sale_price" class="flex items-baseline gap-3">
                                <span 
                                    class="text-3xl lg:text-4xl font-bold"
                                    :style="{ color: theme.accent_color }"
                                >
                                    {{ formatPrice(product.sale_price) }}
                                </span>
                                <span class="text-xl text-gray-400 line-through">{{ formatPrice(product.price) }}</span>
                            </div>
                            <div v-else>
                                <span 
                                    class="text-3xl lg:text-4xl font-bold"
                                    :style="{ color: theme.primary_color }"
                                >
                                    {{ formatPrice(product.price) }}
                                </span>
                            </div>
                        </div>

                        <!-- Stock Status -->
                        <div v-if="product.stock_quantity !== null" class="mb-6">
                            <div 
                                v-if="product.stock_quantity > 0" 
                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-green-50 text-green-700"
                            >
                                <CheckIcon class="h-4 w-4" aria-hidden="true" />
                                <span class="text-sm font-medium">In Stock ({{ product.stock_quantity }} available)</span>
                            </div>
                            <div 
                                v-else 
                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-red-50 text-red-700"
                            >
                                <XMarkIcon class="h-4 w-4" aria-hidden="true" />
                                <span class="text-sm font-medium">Out of Stock</span>
                            </div>
                        </div>

                        <!-- Description -->
                        <div v-if="product.description" class="mb-8">
                            <h3 class="text-sm font-semibold text-gray-900 mb-2">Description</h3>
                            <p class="text-gray-600 leading-relaxed whitespace-pre-line">{{ product.description }}</p>
                        </div>

                        <!-- SKU -->
                        <div v-if="product.sku" class="mb-8 text-sm text-gray-500">
                            SKU: {{ product.sku }}
                        </div>

                        <!-- Spacer -->
                        <div class="flex-1"></div>

                        <!-- Action Buttons -->
                        <div class="space-y-3 pt-6 border-t border-gray-100">
                            <a
                                v-if="business.profile?.show_whatsapp_button && (business.whatsapp || business.phone)"
                                :href="whatsappLink"
                                target="_blank"
                                class="w-full px-6 py-4 bg-green-500 text-white rounded-xl font-semibold hover:bg-green-600 transition-all flex items-center justify-center gap-3 shadow-lg shadow-green-500/30"
                            >
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                                Order via WhatsApp
                            </a>
                            <a
                                v-if="business.phone"
                                :href="`tel:${business.phone}`"
                                class="w-full px-6 py-4 text-white rounded-xl font-semibold transition-all flex items-center justify-center gap-3 shadow-lg hover:opacity-90"
                                :style="{ 
                                    backgroundColor: theme.primary_color,
                                    boxShadow: `0 10px 40px -10px ${theme.primary_color}60`
                                }"
                            >
                                <PhoneIcon class="h-6 w-6" aria-hidden="true" />
                                Call to Order
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <div v-if="relatedProducts && relatedProducts.length > 0" class="mt-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">You May Also Like</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
                    <Link
                        v-for="related in relatedProducts"
                        :key="related.id"
                        :href="route('bizboost.public.product', [business.slug, related.id])"
                        class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100"
                    >
                        <div class="aspect-square bg-gray-100 overflow-hidden">
                            <img
                                v-if="related.images && related.images.length > 0"
                                :src="`/storage/${related.images[0].path}`"
                                :alt="related.name"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100">
                                <ShoppingBagIcon class="h-12 w-12 text-gray-300" aria-hidden="true" />
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 group-hover:text-violet-600 transition-colors truncate">
                                {{ related.name }}
                            </h3>
                            <div class="mt-2">
                                <span 
                                    class="text-lg font-bold"
                                    :style="{ color: related.sale_price ? theme.accent_color : theme.primary_color }"
                                >
                                    {{ formatPrice(related.sale_price || related.price) }}
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="mt-12 py-8 bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 text-center">
                <p class="text-gray-500 text-sm">
                    © {{ new Date().getFullYear() }} {{ business.name }}. All rights reserved.
                </p>
                <p class="text-gray-600 text-xs mt-2">
                    Powered by <span class="font-medium" :style="{ color: theme.primary_color }">BizBoost</span>
                </p>
            </div>
        </footer>

        <!-- Floating WhatsApp Button (Mobile) -->
        <a
            v-if="business.profile?.show_whatsapp_button && (business.whatsapp || business.phone)"
            :href="whatsappLink"
            target="_blank"
            class="fixed bottom-6 right-6 md:hidden w-14 h-14 bg-green-500 text-white rounded-full shadow-lg hover:bg-green-600 transition-all z-50 flex items-center justify-center hover:scale-110"
            aria-label="Order via WhatsApp"
        >
            <svg class="h-7 w-7" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
        </a>
    </div>
</template>
