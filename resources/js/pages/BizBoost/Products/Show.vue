<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import Card from '@/Components/BizBoost/UI/Card.vue';
import { ArrowLeftIcon, PencilIcon, TrashIcon, PhotoIcon, CubeIcon, TagIcon, ArchiveBoxIcon } from '@heroicons/vue/24/outline';
import Swal from 'sweetalert2';

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
    sku: string | null;
    category: string | null;
    stock_quantity: number | null;
    is_active: boolean;
    images: ProductImage[];
    created_at: string;
}

interface Props {
    product: Product;
}

const props = defineProps<Props>();

const formatPrice = (price: number) => `K${price.toLocaleString()}`;

const deleteProduct = () => {
    Swal.fire({
        title: 'Delete Product?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Yes, delete',
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('bizboost.products.destroy', props.product.id));
        }
    });
};
</script>

<template>
    <Head :title="`${product.name} - BizBoost`" />

    <BizBoostLayout :title="product.name">
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <Link 
                    :href="route('bizboost.products.index')" 
                    class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors"
                >
                    <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                    Back to Products
                </Link>
                <div class="flex items-center gap-2">
                    <Link
                        :href="route('bizboost.products.edit', product.id)"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        <PencilIcon class="h-4 w-4" aria-hidden="true" />
                        Edit
                    </Link>
                    <button
                        @click="deleteProduct"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-sm font-medium text-red-700 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors"
                    >
                        <TrashIcon class="h-4 w-4" aria-hidden="true" />
                        Delete
                    </button>
                </div>
            </div>

            <Card padding="none">
                <div class="md:flex">
                    <!-- Images -->
                    <div class="md:w-1/2 bg-gradient-to-br from-gray-100 to-gray-50 dark:from-gray-800 dark:to-gray-900">
                        <div v-if="product.images.length > 0" class="aspect-square">
                            <img 
                                :src="`/storage/${product.images[0].path}`" 
                                :alt="product.name" 
                                class="w-full h-full object-contain" 
                            />
                        </div>
                        <div v-else class="aspect-square flex items-center justify-center">
                            <PhotoIcon class="h-24 w-24 text-gray-300 dark:text-gray-600" aria-hidden="true" />
                        </div>
                        <div v-if="product.images.length > 1" class="flex gap-2 p-4">
                            <div 
                                v-for="img in product.images" 
                                :key="img.id" 
                                class="w-16 h-16 bg-white dark:bg-gray-700 rounded-lg overflow-hidden ring-1 ring-gray-200 dark:ring-gray-600"
                            >
                                <img :src="`/storage/${img.path}`" class="w-full h-full object-cover" />
                            </div>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="md:w-1/2 p-6 lg:p-8">
                        <div class="flex items-start justify-between">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ product.name }}</h1>
                                <span 
                                    v-if="product.category" 
                                    class="inline-flex items-center gap-1.5 mt-2 px-2.5 py-1 bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300 text-sm rounded-lg"
                                >
                                    <TagIcon class="h-3.5 w-3.5" aria-hidden="true" />
                                    {{ product.category }}
                                </span>
                            </div>
                            <span 
                                :class="[
                                    'px-2.5 py-1 rounded-lg text-sm font-medium',
                                    product.is_active 
                                        ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' 
                                        : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'
                                ]"
                            >
                                {{ product.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <div class="mt-6">
                            <div v-if="product.sale_price" class="flex items-baseline gap-3">
                                <span class="text-3xl font-bold text-green-600 dark:text-green-400">{{ formatPrice(product.sale_price) }}</span>
                                <span class="text-lg text-gray-400 dark:text-gray-500 line-through">{{ formatPrice(product.price) }}</span>
                            </div>
                            <div v-else class="text-3xl font-bold text-gray-900 dark:text-white">{{ formatPrice(product.price) }}</div>
                        </div>

                        <p v-if="product.description" class="mt-4 text-gray-600 dark:text-gray-300 leading-relaxed">
                            {{ product.description }}
                        </p>

                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 space-y-3">
                            <div v-if="product.sku" class="flex items-center gap-3 text-sm">
                                <div class="p-2 bg-gray-100 dark:bg-gray-700 rounded-lg">
                                    <CubeIcon class="h-4 w-4 text-gray-500 dark:text-gray-400" aria-hidden="true" />
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">SKU:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white font-medium">{{ product.sku }}</span>
                                </div>
                            </div>
                            <div v-if="product.stock_quantity !== null" class="flex items-center gap-3 text-sm">
                                <div class="p-2 bg-gray-100 dark:bg-gray-700 rounded-lg">
                                    <ArchiveBoxIcon class="h-4 w-4 text-gray-500 dark:text-gray-400" aria-hidden="true" />
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Stock:</span>
                                    <span 
                                        :class="[
                                            'ml-2 font-medium',
                                            product.stock_quantity > 10 
                                                ? 'text-green-600 dark:text-green-400' 
                                                : product.stock_quantity > 0 
                                                    ? 'text-amber-600 dark:text-amber-400' 
                                                    : 'text-red-600 dark:text-red-400'
                                        ]"
                                    >
                                        {{ product.stock_quantity }} units
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Card>
        </div>
    </BizBoostLayout>
</template>
