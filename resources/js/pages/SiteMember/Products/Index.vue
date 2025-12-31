<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import SiteMemberLayout from '@/layouts/SiteMemberLayout.vue';
import { ref, computed } from 'vue';
import { PlusIcon, PencilIcon, TrashIcon, CubeIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

interface Product {
    id: number;
    name: string;
    slug: string;
    price: number;
    compare_price: number | null;
    stock_quantity: number;
    track_stock: boolean;
    category: string | null;
    images: string[];
    is_active: boolean;
    is_featured: boolean;
    created_at: string;
}

interface Props {
    site: { id: number; name: string; subdomain: string; theme: { primaryColor?: string } | null };
    settings: { navigation?: { logo?: string } } | null;
    user: { id: number; name: string; email: string; role: any; permissions: string[] };
    products: { data: Product[]; links: any; meta: any };
    productCount: number;
}

const props = defineProps<Props>();
const primaryColor = computed(() => props.site.theme?.primaryColor || '#2563eb');
const deletingProduct = ref<Product | null>(null);
const searchQuery = ref('');

const isAdmin = computed(() => props.user.role?.level >= 100);
const canCreate = computed(() => isAdmin.value || props.user.permissions.includes('products.create'));
const canEdit = computed(() => isAdmin.value || props.user.permissions.includes('products.edit'));
const canDelete = computed(() => isAdmin.value || props.user.permissions.includes('products.delete'));

const filteredProducts = computed(() => {
    if (!searchQuery.value) return props.products.data;
    const query = searchQuery.value.toLowerCase();
    return props.products.data.filter(p => 
        p.name.toLowerCase().includes(query) || 
        p.category?.toLowerCase().includes(query)
    );
});

const formatPrice = (price: number) => `K${(price / 100).toFixed(2)}`;

const getStockStatus = (product: Product) => {
    if (!product.track_stock) return { text: 'Unlimited', class: 'bg-blue-100 text-blue-700' };
    if (product.stock_quantity === 0) return { text: 'Out of Stock', class: 'bg-red-100 text-red-700' };
    if (product.stock_quantity <= 5) return { text: `Low (${product.stock_quantity})`, class: 'bg-amber-100 text-amber-700' };
    return { text: `${product.stock_quantity} in stock`, class: 'bg-emerald-100 text-emerald-700' };
};

const deleteProduct = () => {
    if (!deletingProduct.value) return;
    router.delete(`/sites/${props.site.subdomain}/dashboard/products/${deletingProduct.value.id}`, {
        onSuccess: () => { deletingProduct.value = null; },
    });
};
</script>

<template>
    <SiteMemberLayout :site="site" :settings="settings" :user="user" title="Products">
        <Head :title="`Products - ${site.name}`" />

        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Products</h1>
                    <p class="text-gray-500">{{ productCount }} product{{ productCount !== 1 ? 's' : '' }} in your store</p>
                </div>
                <Link v-if="canCreate" :href="`/sites/${site.subdomain}/dashboard/products/create`"
                    class="inline-flex items-center gap-2 px-4 py-2 text-white text-sm font-medium rounded-lg"
                    :style="{ backgroundColor: primaryColor }">
                    <PlusIcon class="w-5 h-5" aria-hidden="true" />
                    Add Product
                </Link>
            </div>

            <!-- Search -->
            <div class="mb-6">
                <div class="relative">
                    <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" aria-hidden="true" />
                    <input v-model="searchQuery" type="text" placeholder="Search products..."
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>
            </div>

            <!-- Products Grid -->
            <div v-if="filteredProducts.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="product in filteredProducts" :key="product.id"
                    class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                    <!-- Product Image -->
                    <div class="aspect-square bg-gray-100 relative">
                        <img v-if="product.images?.[0]" :src="product.images[0]" :alt="product.name"
                            class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <CubeIcon class="w-16 h-16 text-gray-300" aria-hidden="true" />
                        </div>
                        <!-- Status Badges -->
                        <div class="absolute top-2 left-2 flex flex-col gap-1">
                            <span v-if="!product.is_active" class="px-2 py-0.5 text-xs font-medium bg-gray-800 text-white rounded">
                                Draft
                            </span>
                            <span v-if="product.is_featured" class="px-2 py-0.5 text-xs font-medium bg-amber-500 text-white rounded">
                                Featured
                            </span>
                        </div>
                    </div>
                    
                    <!-- Product Info -->
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 truncate">{{ product.name }}</h3>
                        <p v-if="product.category" class="text-sm text-gray-500 mb-2">{{ product.category }}</p>
                        
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <span class="text-lg font-bold" :style="{ color: primaryColor }">{{ formatPrice(product.price) }}</span>
                                <span v-if="product.compare_price" class="ml-2 text-sm text-gray-400 line-through">
                                    {{ formatPrice(product.compare_price) }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full" :class="getStockStatus(product).class">
                                {{ getStockStatus(product).text }}
                            </span>
                            
                            <div class="flex items-center gap-1">
                                <Link v-if="canEdit" :href="`/sites/${site.subdomain}/dashboard/products/${product.id}/edit`"
                                    class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg" title="Edit">
                                    <PencilIcon class="w-4 h-4" aria-hidden="true" />
                                </Link>
                                <button v-if="canDelete" @click="deletingProduct = product"
                                    class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg" title="Delete">
                                    <TrashIcon class="w-4 h-4" aria-hidden="true" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white rounded-xl border border-gray-200 p-12 text-center">
                <CubeIcon class="w-16 h-16 mx-auto text-gray-300 mb-4" aria-hidden="true" />
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                    {{ searchQuery ? 'No products found' : 'No products yet' }}
                </h3>
                <p class="text-gray-500 mb-4">
                    {{ searchQuery ? 'Try a different search term.' : 'Add your first product to start selling.' }}
                </p>
                <Link v-if="canCreate && !searchQuery" :href="`/sites/${site.subdomain}/dashboard/products/create`"
                    class="inline-flex items-center gap-2 px-4 py-2 text-white font-medium rounded-lg"
                    :style="{ backgroundColor: primaryColor }">
                    <PlusIcon class="w-5 h-5" aria-hidden="true" />
                    Add Product
                </Link>
            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="deletingProduct" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/50" @click="deletingProduct = null"></div>
                <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Delete Product</h3>
                    <p class="text-sm text-gray-600 mb-6">Are you sure you want to delete "{{ deletingProduct.name }}"? This cannot be undone.</p>
                    <div class="flex justify-end gap-3">
                        <button @click="deletingProduct = null" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg">Cancel</button>
                        <button @click="deleteProduct" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </SiteMemberLayout>
</template>
