<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { ShoppingBagIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';

interface Product {
    id: number;
    name: string;
    slug: string;
    description: string;
    price: number;
    formatted_price: string;
    compare_price: number | null;
    formatted_compare_price: string | null;
    discount_percentage: number;
    primary_image_url: string | null;
    stock_quantity: number;
}

const props = defineProps<{
    content: {
        title?: string;
        subtitle?: string;
        displayMode?: 'all' | 'featured' | 'category';
        limit?: number;
        columns?: number;
        showPrices?: boolean;
        showAddToCart?: boolean;
    };
    style: {
        backgroundColor?: string;
        minHeight?: number;
        headingFontSize?: number;
        subtitleFontSize?: number;
    };
    siteId: number;
    isPreview?: boolean;
}>();

const products = ref<Product[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);

const sectionStyle = computed(() => ({
    backgroundColor: props.style.backgroundColor || '#ffffff',
    minHeight: props.style.minHeight ? `${props.style.minHeight}px` : 'auto',
}));

const headingStyle = computed(() => ({
    fontSize: props.style.headingFontSize ? `${props.style.headingFontSize}px` : '36px',
}));

const subtitleStyle = computed(() => ({
    fontSize: props.style.subtitleFontSize ? `${props.style.subtitleFontSize}px` : '18px',
}));

const gridColumns = computed(() => {
    const cols = props.content.columns || 3;
    return {
        'grid-cols-1': true,
        'md:grid-cols-2': cols >= 2,
        'lg:grid-cols-3': cols >= 3,
        'xl:grid-cols-4': cols === 4,
    };
});

const loadProducts = async () => {
    loading.value = true;
    error.value = null;
    
    try {
        const params: Record<string, any> = {};
        
        if (props.content.displayMode === 'featured') {
            params.featured = true;
        }
        
        if (props.content.limit) {
            params.limit = props.content.limit;
        }
        
        const response = await axios.get(
            route('growbuilder.marketplace.products', props.siteId),
            { params }
        );
        
        products.value = response.data.products || [];
    } catch (err: any) {
        error.value = err.response?.data?.message || 'Failed to load products';
        console.error('Failed to load marketplace products:', err);
    } finally {
        loading.value = false;
    }
};

const getProductUrl = (product: Product) => {
    // In preview, link to marketplace product page
    return `/marketplace/products/${product.slug}`;
};

onMounted(() => {
    loadProducts();
});
</script>

<template>
    <section class="py-16 px-4" :style="sectionStyle">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div v-if="content.title || content.subtitle" class="text-center mb-12">
                <h2 
                    v-if="content.title" 
                    class="font-bold text-gray-900 mb-4"
                    :style="headingStyle"
                >
                    {{ content.title }}
                </h2>
                <p 
                    v-if="content.subtitle" 
                    class="text-gray-600 max-w-2xl mx-auto"
                    :style="subtitleStyle"
                >
                    {{ content.subtitle }}
                </p>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                <p class="text-gray-600 mt-4">Loading products...</p>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="text-center py-12">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-4">
                    <ShoppingBagIcon class="h-8 w-8 text-red-600" aria-hidden="true" />
                </div>
                <p class="text-red-600 font-medium">{{ error }}</p>
                <p class="text-gray-600 text-sm mt-2">Please enable marketplace integration in site settings</p>
            </div>

            <!-- Empty State -->
            <div v-else-if="products.length === 0" class="text-center py-12">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                    <ShoppingBagIcon class="h-8 w-8 text-gray-400" aria-hidden="true" />
                </div>
                <p class="text-gray-600 font-medium">No products available</p>
                <p class="text-gray-500 text-sm mt-2">Add products to your marketplace shop to display them here</p>
            </div>

            <!-- Products Grid -->
            <div v-else class="grid gap-6" :class="gridColumns">
                <a
                    v-for="product in products"
                    :key="product.id"
                    :href="getProductUrl(product)"
                    class="group bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden border border-gray-200"
                >
                    <!-- Product Image -->
                    <div class="aspect-square bg-gray-100 overflow-hidden">
                        <img
                            v-if="product.primary_image_url"
                            :src="product.primary_image_url"
                            :alt="product.name"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <ShoppingBagIcon class="h-16 w-16 text-gray-300" aria-hidden="true" />
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors line-clamp-2">
                            {{ product.name }}
                        </h3>
                        
                        <p v-if="product.description" class="text-sm text-gray-600 mb-3 line-clamp-2">
                            {{ product.description }}
                        </p>

                        <!-- Price -->
                        <div v-if="content.showPrices !== false" class="flex items-center gap-2 mb-3">
                            <span class="text-xl font-bold text-gray-900">
                                {{ product.formatted_price }}
                            </span>
                            <span v-if="product.compare_price" class="text-sm text-gray-500 line-through">
                                {{ product.formatted_compare_price }}
                            </span>
                            <span v-if="product.discount_percentage > 0" class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-1 rounded">
                                -{{ product.discount_percentage }}%
                            </span>
                        </div>

                        <!-- Stock Status -->
                        <div class="flex items-center justify-between">
                            <span 
                                :class="[
                                    'text-xs font-medium',
                                    product.stock_quantity > 0 ? 'text-green-600' : 'text-red-600'
                                ]"
                            >
                                {{ product.stock_quantity > 0 ? 'In Stock' : 'Out of Stock' }}
                            </span>

                            <!-- Buy Now Button -->
                            <button
                                v-if="content.showAddToCart !== false && product.stock_quantity > 0"
                                type="button"
                                class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
                                @click.prevent="window.location.href = getProductUrl(product)"
                            >
                                Buy Now
                            </button>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </section>
</template>
