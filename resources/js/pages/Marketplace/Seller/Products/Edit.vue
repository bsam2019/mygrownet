<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';
import { ref } from 'vue';

interface Category {
    id: number;
    name: string;
    slug: string;
}

interface Product {
    id: number;
    name: string;
    description: string;
    price: number;
    compare_price: number | null;
    stock_quantity: number;
    category_id: number;
    images: string[];
    status: string;
}

interface Props {
    product: Product;
    categories: Category[];
}

const props = defineProps<Props>();

const form = useForm({
    name: props.product.name,
    description: props.product.description || '',
    price: props.product.price / 100,
    compare_price: props.product.compare_price ? props.product.compare_price / 100 : '',
    stock_quantity: props.product.stock_quantity,
    category_id: props.product.category_id,
    images: [] as File[],
    existing_images: props.product.images || [],
});

const imagePreview = ref<string[]>(props.product.images || []);
const dragActive = ref(false);

const handleImageUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        addImages(Array.from(target.files));
    }
};

const handleDrop = (event: DragEvent) => {
    dragActive.value = false;
    if (event.dataTransfer?.files) {
        addImages(Array.from(event.dataTransfer.files));
    }
};

const addImages = (files: File[]) => {
    const validFiles = files.filter(file => file.type.startsWith('image/'));
    
    validFiles.forEach(file => {
        form.images.push(file);
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value.push(e.target?.result as string);
        };
        reader.readAsDataURL(file);
    });
};

const removeImage = (index: number) => {
    // Check if it's an existing image or a new upload
    if (index < form.existing_images.length) {
        form.existing_images.splice(index, 1);
    } else {
        const newIndex = index - form.existing_images.length;
        form.images.splice(newIndex, 1);
    }
    imagePreview.value.splice(index, 1);
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        price: Math.round(data.price * 100),
        compare_price: data.compare_price ? Math.round(Number(data.compare_price) * 100) : null,
        _method: 'PUT',
    })).post(route('marketplace.seller.products.update', props.product.id), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Edit Product - Seller Dashboard" />
    
    <MarketplaceLayout>
        <div class="bg-gray-50 min-h-screen py-8">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <Link
                        :href="route('marketplace.seller.products.index')"
                        class="text-amber-600 hover:text-amber-700 text-sm font-medium flex items-center gap-1 mb-4"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Products
                    </Link>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Product</h1>
                    <p class="text-gray-500 mt-1">Update your product details</p>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Basic Info -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>
                        
                        <div class="space-y-4">
                            <!-- Product Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Product Name *
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 bg-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors"
                                    placeholder="e.g., Fresh Tomatoes - 5kg Bag"
                                />
                                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                            </div>

                            <!-- Category -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Category *
                                </label>
                                <select
                                    v-model="form.category_id"
                                    required
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 bg-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors"
                                >
                                    <option value="">Select a category</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.category_id" class="mt-1 text-sm text-red-600">{{ form.errors.category_id }}</p>
                            </div>

                            <!-- Description -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Description *
                                </label>
                                <textarea
                                    v-model="form.description"
                                    rows="4"
                                    required
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 bg-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors"
                                    placeholder="Describe your product in detail..."
                                ></textarea>
                                <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Pricing & Stock</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Price -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Price (ZMW) *
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">K</span>
                                    <input
                                        v-model="form.price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        required
                                        class="w-full pl-8 pr-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 bg-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors"
                                    />
                                </div>
                                <p v-if="form.errors.price" class="mt-1 text-sm text-red-600">{{ form.errors.price }}</p>
                            </div>

                            <!-- Compare Price -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Compare Price (Optional)
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">K</span>
                                    <input
                                        v-model="form.compare_price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="w-full pl-8 pr-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 bg-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors"
                                        placeholder="Original price"
                                    />
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Show as discounted if higher than price</p>
                            </div>

                            <!-- Stock -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Stock Quantity *
                                </label>
                                <input
                                    v-model="form.stock_quantity"
                                    type="number"
                                    min="0"
                                    required
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 bg-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors"
                                />
                                <p v-if="form.errors.stock_quantity" class="mt-1 text-sm text-red-600">{{ form.errors.stock_quantity }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Images -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Product Images</h2>
                        
                        <!-- Image Preview -->
                        <div v-if="imagePreview.length" class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
                            <div
                                v-for="(image, index) in imagePreview"
                                :key="index"
                                class="relative aspect-square rounded-lg overflow-hidden bg-gray-100"
                            >
                                <img :src="image" class="w-full h-full object-cover" />
                                <button
                                    type="button"
                                    @click="removeImage(index)"
                                    class="absolute top-2 right-2 p-1 bg-red-500 text-white rounded-full hover:bg-red-600"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                                <span
                                    v-if="index === 0"
                                    class="absolute bottom-2 left-2 px-2 py-0.5 bg-amber-500 text-white text-xs rounded"
                                >
                                    Main
                                </span>
                            </div>
                        </div>

                        <!-- Upload Area -->
                        <div
                            @dragover.prevent="dragActive = true"
                            @dragleave="dragActive = false"
                            @drop.prevent="handleDrop"
                            :class="[
                                'border-2 border-dashed rounded-lg p-8 text-center transition-colors',
                                dragActive ? 'border-amber-500 bg-amber-50' : 'border-gray-300 hover:border-amber-400'
                            ]"
                        >
                            <input
                                type="file"
                                accept="image/*"
                                multiple
                                @change="handleImageUpload"
                                class="hidden"
                                id="image-upload"
                            />
                            <label for="image-upload" class="cursor-pointer">
                                <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="text-gray-600 mb-1">
                                    <span class="text-amber-600 font-medium">Click to upload</span> or drag and drop
                                </p>
                                <p class="text-sm text-gray-500">PNG, JPG up to 5MB each</p>
                            </label>
                        </div>
                        <p v-if="form.errors.images" class="mt-2 text-sm text-red-600">{{ form.errors.images }}</p>
                    </div>

                    <!-- Status Info -->
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="text-sm text-amber-800">
                                    <strong>Current Status:</strong> {{ product.status }}
                                </p>
                                <p class="text-sm text-amber-700 mt-1">
                                    Changes to your product may require re-approval before going live.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex items-center justify-end gap-4">
                        <Link
                            :href="route('marketplace.seller.products.index')"
                            class="px-6 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 font-medium"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            <svg v-if="form.processing" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Update Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </MarketplaceLayout>
</template>
