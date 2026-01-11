<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';
import {
    PlusIcon,
    PencilIcon,
    TrashIcon,
    CubeIcon,
    EyeIcon,
    ExclamationTriangleIcon,
    ChatBubbleLeftEllipsisIcon,
} from '@heroicons/vue/24/outline';
import { computed, ref } from 'vue';

interface FieldFeedback {
    field: string;
    message: string;
}

interface Product {
    id: number;
    name: string;
    slug: string;
    price: number;
    stock_quantity: number;
    status: string;
    views: number;
    primary_image_url: string | null;
    formatted_price: string;
    category: { name: string };
    rejection_reason: string | null;
    rejection_category: string | null;
    field_feedback: FieldFeedback[] | null;
    appeal_message: string | null;
    appealed_at: string | null;
}

interface Category {
    id: number;
    name: string;
}

defineProps<{
    products: { data: Product[]; links: any };
    categories: Category[];
    filters: { status: string; category: string };
}>();

const page = usePage();
const errors = computed(() => page.props.errors as Record<string, string>);

// Feedback modal
const showFeedbackModal = ref(false);
const selectedProduct = ref<Product | null>(null);

const getStatusColor = (status: string) => ({
    'draft': 'bg-gray-100 text-gray-700',
    'pending': 'bg-yellow-100 text-yellow-700',
    'active': 'bg-green-100 text-green-700',
    'rejected': 'bg-red-100 text-red-700',
    'changes_requested': 'bg-orange-100 text-orange-700',
    'suspended': 'bg-orange-100 text-orange-700',
}[status] || 'bg-gray-100 text-gray-700');

const getStatusLabel = (status: string) => ({
    'draft': 'Draft',
    'pending': 'Pending Review',
    'active': 'Active',
    'rejected': 'Rejected',
    'changes_requested': 'Changes Needed',
    'suspended': 'Suspended',
}[status] || status);

const deleteProduct = (id: number) => {
    if (confirm('Are you sure you want to delete this product?')) {
        router.delete(route('marketplace.seller.products.destroy', id));
    }
};

const viewFeedback = (product: Product) => {
    selectedProduct.value = product;
    showFeedbackModal.value = true;
};

const hasFeedback = (product: Product) => {
    return ['rejected', 'changes_requested'].includes(product.status) && product.rejection_reason;
};
</script>

<template>
    <Head title="My Products - Marketplace" />
    
    <MarketplaceLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Error Alert -->
            <div v-if="errors.seller" class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg flex items-start gap-3">
                <ExclamationTriangleIcon class="h-5 w-5 text-yellow-600 flex-shrink-0 mt-0.5" aria-hidden="true" />
                <div>
                    <p class="text-sm font-medium text-yellow-800">{{ errors.seller }}</p>
                    <p class="text-xs text-yellow-700 mt-1">Your account is currently under review. You'll be able to add products once approved.</p>
                </div>
            </div>

            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900">My Products</h1>
                <Link 
                    :href="route('marketplace.seller.products.create')"
                    class="flex items-center gap-2 px-4 py-2 bg-orange-500 text-white font-medium rounded-lg hover:bg-orange-600"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                    Add Product
                </Link>
            </div>

            <!-- Empty State -->
            <div v-if="products.data.length === 0" class="text-center py-16 bg-white rounded-xl border border-gray-200">
                <CubeIcon class="h-16 w-16 text-gray-300 mx-auto mb-4" aria-hidden="true" />
                <h2 class="text-xl font-semibold text-gray-900 mb-2">No products yet</h2>
                <p class="text-gray-500 mb-6">Start selling by adding your first product.</p>
                <Link 
                    :href="route('marketplace.seller.products.create')"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-orange-500 text-white font-medium rounded-lg hover:bg-orange-600"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                    Add Product
                </Link>
            </div>

            <!-- Products Table -->
            <div v-else class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Product</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Category</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Price</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Stock</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Status</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Views</th>
                                <th class="px-4 py-3 text-right text-sm font-medium text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                            <img 
                                                v-if="product.primary_image_url"
                                                :src="product.primary_image_url"
                                                :alt="product.name"
                                                class="w-full h-full object-cover"
                                            />
                                            <div v-else class="w-full h-full flex items-center justify-center">
                                                <CubeIcon class="h-6 w-6 text-gray-400" aria-hidden="true" />
                                            </div>
                                        </div>
                                        <span class="font-medium text-gray-900 line-clamp-1">{{ product.name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ product.category?.name }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ product.formatted_price }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ product.stock_quantity }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span :class="['text-xs px-2 py-1 rounded-full font-medium', getStatusColor(product.status)]">
                                            {{ getStatusLabel(product.status) }}
                                        </span>
                                        <button
                                            v-if="hasFeedback(product)"
                                            @click="viewFeedback(product)"
                                            class="p-1 text-orange-600 hover:bg-orange-50 rounded"
                                            :title="product.status === 'rejected' ? 'View rejection reason' : 'View requested changes'"
                                        >
                                            <ChatBubbleLeftEllipsisIcon class="h-4 w-4" aria-hidden="true" />
                                        </button>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ product.views }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link 
                                            v-if="product.status === 'active'"
                                            :href="route('marketplace.product', product.slug)"
                                            class="p-1.5 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded"
                                            aria-label="View product"
                                        >
                                            <EyeIcon class="h-5 w-5" aria-hidden="true" />
                                        </Link>
                                        <Link 
                                            :href="route('marketplace.seller.products.edit', product.id)"
                                            class="p-1.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded"
                                            aria-label="Edit product"
                                        >
                                            <PencilIcon class="h-5 w-5" aria-hidden="true" />
                                        </Link>
                                        <button 
                                            @click="deleteProduct(product.id)"
                                            class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded"
                                            aria-label="Delete product"
                                        >
                                            <TrashIcon class="h-5 w-5" aria-hidden="true" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Feedback Modal -->
            <Teleport to="body">
                <div v-if="showFeedbackModal && selectedProduct" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                    <div class="bg-white rounded-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <h3 class="text-lg font-bold text-gray-900">
                                    {{ selectedProduct.status === 'rejected' ? 'Rejection Feedback' : 'Changes Requested' }}
                                </h3>
                                <button @click="showFeedbackModal = false" class="text-gray-400 hover:text-gray-600">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div :class="[
                                'p-4 rounded-lg mb-4',
                                selectedProduct.status === 'rejected' ? 'bg-red-50' : 'bg-orange-50'
                            ]">
                                <p :class="[
                                    'text-sm font-medium mb-1',
                                    selectedProduct.status === 'rejected' ? 'text-red-800' : 'text-orange-800'
                                ]">
                                    {{ selectedProduct.rejection_category?.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                                </p>
                                <p :class="[
                                    'text-sm',
                                    selectedProduct.status === 'rejected' ? 'text-red-700' : 'text-orange-700'
                                ]">
                                    {{ selectedProduct.rejection_reason }}
                                </p>
                            </div>

                            <!-- Field-specific feedback -->
                            <div v-if="selectedProduct.field_feedback && selectedProduct.field_feedback.length > 0" class="mb-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Specific Issues:</h4>
                                <div class="space-y-2">
                                    <div 
                                        v-for="(feedback, idx) in selectedProduct.field_feedback" 
                                        :key="idx"
                                        class="p-3 bg-gray-50 rounded-lg"
                                    >
                                        <p class="text-sm">
                                            <span class="font-medium text-gray-900">{{ feedback.field }}:</span>
                                            <span class="text-gray-600"> {{ feedback.message }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Appeal status -->
                            <div v-if="selectedProduct.appeal_message" class="mb-4 p-3 bg-blue-50 rounded-lg">
                                <p class="text-sm font-medium text-blue-800 mb-1">Your Appeal:</p>
                                <p class="text-sm text-blue-700">{{ selectedProduct.appeal_message }}</p>
                                <p class="text-xs text-blue-600 mt-1">
                                    Submitted: {{ selectedProduct.appealed_at ? new Date(selectedProduct.appealed_at).toLocaleDateString() : 'N/A' }}
                                </p>
                            </div>

                            <div class="flex gap-3">
                                <button
                                    @click="showFeedbackModal = false"
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium"
                                >
                                    Close
                                </button>
                                <Link
                                    :href="route('marketplace.seller.products.edit', selectedProduct.id)"
                                    class="flex-1 px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 font-medium text-center"
                                >
                                    {{ selectedProduct.status === 'rejected' ? 'Edit & Appeal' : 'Make Changes' }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </Teleport>
        </div>
    </MarketplaceLayout>
</template>
