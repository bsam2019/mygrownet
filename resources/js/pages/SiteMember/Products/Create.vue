<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import SiteMemberLayout from '@/layouts/SiteMemberLayout.vue';
import ImageUploader from '@/components/GrowBuilder/ImageUploader.vue';
import { ref, computed } from 'vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

interface Props {
    site: { id: number; name: string; subdomain: string; theme: { primaryColor?: string } | null };
    settings: { navigation?: { logo?: string } } | null;
    user: { id: number; name: string; email: string; role: any; permissions: string[] };
    categories: string[];
}

const props = defineProps<Props>();
const primaryColor = computed(() => props.site.theme?.primaryColor || '#2563eb');

const form = useForm({
    name: '',
    description: '',
    short_description: '',
    price: '',
    compare_price: '',
    stock_quantity: 0,
    track_stock: true,
    sku: '',
    category: '',
    images: [] as string[],
    is_active: true,
    is_featured: false,
});

const newCategory = ref('');
const showNewCategory = ref(false);

const submit = () => {
    const category = showNewCategory.value ? newCategory.value : form.category;
    form.transform((data) => ({
        ...data,
        category: category || null,
        price: parseFloat(data.price) || 0,
        compare_price: data.compare_price ? parseFloat(data.compare_price) : null,
    })).post(`/sites/${props.site.subdomain}/dashboard/products`);
};
</script>

<template>
    <SiteMemberLayout :site="site" :settings="settings" :user="user" title="Add Product">
        <Head :title="`Add Product - ${site.name}`" />

        <div class="max-w-3xl mx-auto">
            <!-- Header -->
            <div class="mb-6">
                <Link :href="`/sites/${site.subdomain}/dashboard/products`"
                    class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-4">
                    <ArrowLeftIcon class="w-4 h-4" aria-hidden="true" />
                    Back to Products
                </Link>
                <h1 class="text-2xl font-bold text-gray-900">Add Product</h1>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Info -->
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                            <input v-model="form.name" type="text" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="e.g., Premium T-Shirt" />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Short Description</label>
                            <input v-model="form.short_description" type="text" maxlength="500"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="Brief product summary" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Description</label>
                            <textarea v-model="form.description" rows="4"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="Detailed product description..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Pricing -->
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Pricing</h2>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Price (K) *</label>
                            <input v-model="form.price" type="number" step="0.01" min="0" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="0.00" />
                            <p v-if="form.errors.price" class="mt-1 text-sm text-red-600">{{ form.errors.price }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Compare Price (K)</label>
                            <input v-model="form.compare_price" type="number" step="0.01" min="0"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="Original price for discount display" />
                        </div>
                    </div>
                </div>

                <!-- Inventory -->
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Inventory</h2>
                    
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <input v-model="form.track_stock" type="checkbox" id="track_stock"
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" />
                            <label for="track_stock" class="text-sm text-gray-700">Track stock quantity</label>
                        </div>

                        <div v-if="form.track_stock" class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity</label>
                                <input v-model="form.stock_quantity" type="number" min="0"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                                <input v-model="form.sku" type="text"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    placeholder="Stock keeping unit" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Category -->
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Category</h2>
                    
                    <div v-if="!showNewCategory && categories.length > 0">
                        <select v-model="form.category"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">No category</option>
                            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                        </select>
                        <button type="button" @click="showNewCategory = true"
                            class="mt-2 text-sm text-blue-600 hover:text-blue-700">
                            + Create new category
                        </button>
                    </div>
                    <div v-else>
                        <input v-model="newCategory" type="text"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter category name" />
                        <button v-if="categories.length > 0" type="button" @click="showNewCategory = false"
                            class="mt-2 text-sm text-gray-500 hover:text-gray-700">
                            Choose existing category
                        </button>
                    </div>
                </div>

                <!-- Images -->
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Images</h2>
                    
                    <ImageUploader
                        v-model="form.images"
                        :site-id="site.id"
                        :subdomain="site.subdomain"
                        :max-images="8"
                        :show-editor="true"
                    />
                </div>

                <!-- Status -->
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Status</h2>
                    
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <input v-model="form.is_active" type="checkbox" id="is_active"
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" />
                            <label for="is_active" class="text-sm text-gray-700">Active (visible in store)</label>
                        </div>
                        <div class="flex items-center gap-3">
                            <input v-model="form.is_featured" type="checkbox" id="is_featured"
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" />
                            <label for="is_featured" class="text-sm text-gray-700">Featured product</label>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3">
                    <Link :href="`/sites/${site.subdomain}/dashboard/products`"
                        class="px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing"
                        class="px-6 py-2.5 text-white text-sm font-medium rounded-lg disabled:opacity-50"
                        :style="{ backgroundColor: primaryColor }">
                        {{ form.processing ? 'Creating...' : 'Create Product' }}
                    </button>
                </div>
            </form>
        </div>
    </SiteMemberLayout>
</template>
