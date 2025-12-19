<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';
import { ArrowLeftIcon, PhotoIcon, XMarkIcon } from '@heroicons/vue/24/outline';

interface Category { id: number; name: string; }

defineProps<{ categories: Category[] }>();

const form = useForm({
    name: '',
    category_id: '',
    description: '',
    price: '',
    compare_price: '',
    stock_quantity: '',
    images: [] as File[],
});

const imagePreviews = ref<string[]>([]);

const handleImageSelect = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files) {
        const newFiles = Array.from(target.files).slice(0, 5 - form.images.length);
        form.images.push(...newFiles);
        newFiles.forEach(file => {
            const reader = new FileReader();
            reader.onload = (e) => imagePreviews.value.push(e.target?.result as string);
            reader.readAsDataURL(file);
        });
    }
};

const removeImage = (index: number) => {
    form.images.splice(index, 1);
    imagePreviews.value.splice(index, 1);
};

const submit = () => {
    form.post(route('marketplace.seller.products.store'), { forceFormData: true });
};
</script>

<template>
    <Head title="Add Product - Seller Dashboard" />
    <MarketplaceLayout>
        <div class="max-w-2xl mx-auto px-4 py-8">
            <div class="flex items-center gap-4 mb-6">
                <Link :href="route('marketplace.seller.products.index')" class="p-2 hover:bg-gray-100 rounded-lg">
                    <ArrowLeftIcon class="h-5 w-5" />
                </Link>
                <h1 class="text-2xl font-bold text-gray-900">Add Product</h1>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-xl border p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                    <input v-model="form.name" type="text" class="w-full border-gray-300 rounded-lg" placeholder="e.g. iPhone 13 Pro" />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                    <select v-model="form.category_id" class="w-full border-gray-300 rounded-lg">
                        <option value="">Select category</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                    <p v-if="form.errors.category_id" class="mt-1 text-sm text-red-600">{{ form.errors.category_id }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                    <textarea v-model="form.description" rows="4" class="w-full border-gray-300 rounded-lg" 
                        placeholder="Describe your product..."></textarea>
                    <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price (K) *</label>
                        <input v-model="form.price" type="number" step="0.01" min="1" class="w-full border-gray-300 rounded-lg" placeholder="0.00" />
                        <p v-if="form.errors.price" class="mt-1 text-sm text-red-600">{{ form.errors.price }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Compare Price (K)</label>
                        <input v-model="form.compare_price" type="number" step="0.01" min="1" class="w-full border-gray-300 rounded-lg" placeholder="Original price" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity *</label>
                    <input v-model="form.stock_quantity" type="number" min="0" class="w-full border-gray-300 rounded-lg" placeholder="0" />
                    <p v-if="form.errors.stock_quantity" class="mt-1 text-sm text-red-600">{{ form.errors.stock_quantity }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Product Images * (Max 5)</label>
                    <div class="flex flex-wrap gap-3">
                        <div v-for="(preview, index) in imagePreviews" :key="index" class="relative w-20 h-20">
                            <img :src="preview" class="w-full h-full object-cover rounded-lg" />
                            <button type="button" @click="removeImage(index)" 
                                class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center">
                                <XMarkIcon class="h-4 w-4" />
                            </button>
                        </div>
                        <label v-if="form.images.length < 5" 
                            class="w-20 h-20 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center cursor-pointer hover:border-orange-500">
                            <PhotoIcon class="h-8 w-8 text-gray-400" />
                            <input type="file" accept="image/*" multiple @change="handleImageSelect" class="hidden" />
                        </label>
                    </div>
                    <p v-if="form.errors.images" class="mt-1 text-sm text-red-600">{{ form.errors.images }}</p>
                </div>

                <div class="flex gap-3 pt-4">
                    <Link :href="route('marketplace.seller.products.index')" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing" 
                        class="flex-1 px-6 py-2 bg-orange-500 text-white font-medium rounded-lg hover:bg-orange-600 disabled:opacity-50">
                        {{ form.processing ? 'Saving...' : 'Add Product' }}
                    </button>
                </div>
            </form>
        </div>
    </MarketplaceLayout>
</template>
