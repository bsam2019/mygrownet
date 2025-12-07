<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    ArrowLeftIcon, 
    MagnifyingGlassIcon,
    FunnelIcon,
    Squares2X2Icon,
    ListBulletIcon,
    XMarkIcon,
    PhoneIcon,
} from '@heroicons/vue/24/outline';

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

interface Product {
    id: number;
    name: string;
    description: string | null;
    price: number;
    sale_price: number | null;
    category: string | null;
    is_featured: boolean;
    images: { path: string }[];
}

interface Props {
    business: Business;
    products: {
        data: Product[];
        current_page: number;
        last_page: number;
        total: number;
    };
    categories: string[];
    filters: {
        category?: string;
        search?: string;
    };
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters.search || '');
const selectedCategory = ref(props.filters.category || '');
const viewMode = ref<'grid' | 'list'>('grid');
const showFilters = ref(false);

const defaultTheme: ThemeSettings = {
    primary_color: '#7c3aed',
    secondary_color: '#4f46e5',
    accent_color: '#10b981',
};

const theme = computed(() => ({
    ...defaultTheme,
    ...(props.business.profile?.theme_settings || {}),
}));

const applyFilters = () => {
    router.get(route('bizboost.public.products', props.business.slug), {
        search: searchQuery.value || undefined,
        category: selectedCategory.value || undefined,
    }, { preserveState: true });
};

const clearFilters = () => {
    searchQuery.value = '';
    selectedCategory.value = '';
    applyFilters();
};

const formatPrice = (price: number) => `K${price.toLocaleString()}`;

const whatsappLink = computed(() => {
    const phone = props.business.whatsapp || props.business.phone;
    if (!phone) return '#';
    const cleaned = phone.replace(/\D/g, '');
    return `https://wa.me/${cleaned}`;
});

const hasActiveFilters = computed(() => searchQuery.value || selectedCategory.value);
</script>

<template>
    <Head :title="`Products - ${business.name}`" />

    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white border-b border-gray-100 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6">
                <div class="flex items-center justify-between h-16">
                    <!-- Back & Logo -->
                    <Link 
                        :href="route('bizboost.public.business', business.slug)" 
                        class="flex items-center gap-3 text-gray-600 hover:text-gray-900 transition-colors"
                    >
                        <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
                        <div class="flex items-center gap-2">
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
                        </div>
                    </Link>

                    <!-- Actions -->
                    <div class="flex items-center gap-3">
                        <a
                            v-if="business.profile?.show_whatsapp_button && (business.whatsapp || business.phone)"
                            :href="whatsappLink"
                            target="_blank"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-lg transition-all hover:opacity-90"
                            :style="{ backgroundColor: theme.primary_color }"
                        >
                            <PhoneIcon class="h-4 w-4" aria-hidden="true" />
                            <span class="hidden sm:inline">Contact</span>
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Hero Banner -->
        <div 
            class="relative py-12 sm:py-16 overflow-hidden"
            :style="{ background: `linear-gradient(135deg, ${theme.primary_color}, ${theme.secondary_color})` }"
        >
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-1/2 -right-1/4 w-[500px] h-[500px] rounded-full opacity-20 blur-3xl bg-white"></div>
                <div class="absolute -bottom-1/4 -left-1/4 w-[400px] h-[400px] rounded-full opacity-10 blur-3xl bg-white"></div>
            </div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 text-center">
                <h1 class="text-3xl sm:text-4xl font-bold text-white mb-2">Our Products</h1>
                <p class="text-white/80 text-lg">
                    {{ products.total }} {{ products.total === 1 ? 'product' : 'products' }} available
                </p>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
            <!-- Filters Bar -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-8">
                <div class="flex flex-col lg:flex-row gap-4">
                    <!-- Search -->
                    <div class="flex-1 relative">
                        <MagnifyingGlassIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search products..."
                            class="w-full pl-12 pr-4 py-3 rounded-xl border-gray-200 focus:border-violet-500 focus:ring-violet-500 transition-colors"
                            @keyup.enter="applyFilters"
                        />
                    </div>

                    <!-- Category Filter -->
                    <div class="flex items-center gap-3">
                        <select 
                            v-model="selectedCategory" 
                            class="px-4 py-3 rounded-xl border-gray-200 focus:border-violet-500 focus:ring-violet-500 min-w-[180px]"
                            @change="applyFilters"
                        >
                            <option value="">All Categories</option>
                            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                        </select>

                        <!-- View Toggle -->
                        <div class="hidden sm:flex items-center gap-1 bg-gray-100 rounded-lg p-1">
                            <button
                                @click="viewMode = 'grid'"
                                :class="[
                                    'p-2 rounded-lg transition-colors',
                                    viewMode === 'grid' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700'
                                ]"
                                aria-label="Grid view"
                            >
                                <Squares2X2Icon class="h-5 w-5" aria-hidden="true" />
                            </button>
                            <button
                                @click="viewMode = 'list'"
                                :class="[
                                    'p-2 rounded-lg transition-colors',
                                    viewMode === 'list' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700'
                                ]"
                                aria-label="List view"
                            >
                                <ListBulletIcon class="h-5 w-5" aria-hidden="true" />
                            </button>
                        </div>

                        <!-- Search Button -->
                        <button
                            @click="applyFilters"
                            class="px-6 py-3 text-white font-medium rounded-xl transition-all hover:opacity-90"
                            :style="{ backgroundColor: theme.primary_color }"
                        >
                            Search
                        </button>
                    </div>
                </div>

                <!-- Active Filters -->
                <div v-if="hasActiveFilters" class="flex items-center gap-2 mt-4 pt-4 border-t border-gray-100">
                    <span class="text-sm text-gray-500">Active filters:</span>
                    <span 
                        v-if="searchQuery"
                        class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm bg-gray-100 text-gray-700"
                    >
                        "{{ searchQuery }}"
                        <button @click="searchQuery = ''; applyFilters()" class="hover:text-gray-900">
                            <XMarkIcon class="h-4 w-4" aria-hidden="true" />
                        </button>
                    </span>
                    <span 
                        v-if="selectedCategory"
                        class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm bg-gray-100 text-gray-700"
                    >
                        {{ selectedCategory }}
                        <button @click="selectedCategory = ''; applyFilters()" class="hover:text-gray-900">
                            <XMarkIcon class="h-4 w-4" aria-hidden="true" />
                        </button>
                    </span>
                    <button 
                        @click="clearFilters"
                        class="text-sm font-medium hover:underline"
                        :style="{ color: theme.primary_color }"
                    >
                        Clear all
                    </button>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="products.data.length === 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-16 text-center">
                <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-gray-100 flex items-center justify-center">
                    <MagnifyingGlassIcon class="h-10 w-10 text-gray-400" aria-hidden="true" />
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No products found</h3>
                <p class="text-gray-500 mb-6">Try adjusting your search or filter to find what you're looking for.</p>
                <button 
                    v-if="hasActiveFilters"
                    @click="clearFilters"
                    class="px-6 py-3 text-white font-medium rounded-xl transition-all hover:opacity-90"
                    :style="{ backgroundColor: theme.primary_color }"
                >
                    Clear Filters
                </button>
            </div>

            <!-- Products Grid -->
            <div 
                v-else
                :class="[
                    viewMode === 'grid' 
                        ? 'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6' 
                        : 'space-y-4'
                ]"
            >
                <Link
                    v-for="product in products.data"
                    :key="product.id"
                    :href="route('bizboost.public.product', [business.slug, product.id])"
                    :class="[
                        'group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100',
                        viewMode === 'list' ? 'flex' : ''
                    ]"
                >
                    <!-- Image -->
                    <div 
                        :class="[
                            'bg-gray-100 overflow-hidden relative',
                            viewMode === 'grid' ? 'aspect-square' : 'w-40 h-40 flex-shrink-0'
                        ]"
                    >
                        <img
                            v-if="product.images && product.images.length > 0"
                            :src="`/storage/${product.images[0].path}`"
                            :alt="product.name"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100">
                            <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>

                        <!-- Badges -->
                        <div class="absolute top-3 left-3 flex flex-col gap-2">
                            <span 
                                v-if="product.is_featured"
                                class="px-2.5 py-1 text-xs font-semibold rounded-full text-white"
                                :style="{ backgroundColor: theme.accent_color }"
                            >
                                Featured
                            </span>
                            <span 
                                v-if="product.sale_price"
                                class="px-2.5 py-1 text-xs font-semibold rounded-full bg-red-500 text-white"
                            >
                                Sale
                            </span>
                        </div>
                    </div>

                    <!-- Content -->
                    <div :class="viewMode === 'list' ? 'flex-1 p-5 flex flex-col justify-center' : 'p-5'">
                        <p 
                            v-if="product.category" 
                            class="text-xs font-medium uppercase tracking-wider mb-1"
                            :style="{ color: theme.primary_color }"
                        >
                            {{ product.category }}
                        </p>
                        <h3 class="font-semibold text-gray-900 group-hover:text-violet-600 transition-colors line-clamp-2 mb-2">
                            {{ product.name }}
                        </h3>
                        <p v-if="viewMode === 'list' && product.description" class="text-sm text-gray-500 line-clamp-2 mb-3">
                            {{ product.description }}
                        </p>
                        <div class="flex items-center gap-2">
                            <span 
                                class="text-xl font-bold"
                                :style="{ color: product.sale_price ? theme.accent_color : theme.primary_color }"
                            >
                                {{ formatPrice(product.sale_price || product.price) }}
                            </span>
                            <span 
                                v-if="product.sale_price"
                                class="text-sm text-gray-400 line-through"
                            >
                                {{ formatPrice(product.price) }}
                            </span>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Pagination -->
            <div v-if="products.last_page > 1" class="mt-10 flex items-center justify-center gap-2">
                <Link
                    v-if="products.current_page > 1"
                    :href="route('bizboost.public.products', { slug: business.slug, ...filters, page: products.current_page - 1 })"
                    class="px-5 py-2.5 bg-white border border-gray-200 rounded-xl font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                >
                    Previous
                </Link>
                <span class="px-4 py-2 text-sm text-gray-500">
                    Page {{ products.current_page }} of {{ products.last_page }}
                </span>
                <Link
                    v-if="products.current_page < products.last_page"
                    :href="route('bizboost.public.products', { slug: business.slug, ...filters, page: products.current_page + 1 })"
                    class="px-5 py-2.5 text-white rounded-xl font-medium transition-all hover:opacity-90"
                    :style="{ backgroundColor: theme.primary_color }"
                >
                    Next
                </Link>
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
            aria-label="Chat on WhatsApp"
        >
            <svg class="h-7 w-7" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
        </a>
    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
