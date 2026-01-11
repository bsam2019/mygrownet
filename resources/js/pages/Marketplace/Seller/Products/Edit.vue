<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';
import MediaLibraryModal from '@/pages/GrowBuilder/Editor/components/modals/MediaLibraryModal.vue';
import { PhotoIcon, XMarkIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';
import { ref, computed } from 'vue';
import axios from 'axios';

interface Category {
    id: number;
    name: string;
    slug: string;
}

interface FieldFeedback {
    field: string;
    message: string;
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
    image_urls: string[];
    status: string;
    rejection_reason: string | null;
    rejection_category: string | null;
    field_feedback: FieldFeedback[] | null;
    appeal_message: string | null;
    appealed_at: string | null;
}

interface MediaItem {
    id: number | string;
    url: string;
    thumbnailUrl?: string;
    originalName: string;
    filename?: string;
    size?: number;
    mime_type?: string;
}

interface Props {
    product: Product;
    categories: Category[];
}

const props = defineProps<Props>();

// Initialize image types: existing images are 'existing', new uploads are 'file', media refs are 'media'
const existingImageCount = (props.product.images || []).length;

const form = useForm({
    name: props.product.name,
    description: props.product.description || '',
    price: props.product.price / 100,
    compare_price: props.product.compare_price ? props.product.compare_price / 100 : '',
    stock_quantity: props.product.stock_quantity,
    category_id: props.product.category_id,
    images: [] as File[],
    existing_images: props.product.images || [],
    media_ids: [] as number[],
    appeal_message: '',
});

// Use image_urls (full URLs) for display, fallback to images if not available
const imagePreview = ref<string[]>(props.product.image_urls || props.product.images || []);
// Track type: 'existing' = already saved, 'file' = new upload, 'media' = media library reference
const imageTypes = ref<('existing' | 'file' | 'media')[]>(
    Array(existingImageCount).fill('existing')
);

// Media library state
const showMediaLibrary = ref(false);
const uploadingImage = ref(false);
const imageUploadError = ref('');
const mediaLibrary = ref<MediaItem[]>([]);

// Appeal state
const showAppealForm = ref(false);
const appealMessage = ref('');
const appealProcessing = ref(false);

// Check if product needs attention
const needsChanges = computed(() => props.product.status === 'changes_requested');
const isRejected = computed(() => props.product.status === 'rejected');
const canAppeal = computed(() => isRejected.value && !props.product.appeal_message);
const hasAppealed = computed(() => isRejected.value && props.product.appeal_message);

// Get field-specific feedback for a field
const getFieldFeedback = (fieldName: string) => {
    if (!props.product.field_feedback) return null;
    return props.product.field_feedback.find(f => f.field === fieldName);
};

// Load media library
const loadMediaLibrary = async () => {
    try {
        const response = await axios.get(route('marketplace.seller.media.index'));
        mediaLibrary.value = response.data.data || [];
    } catch (error) {
        console.error('Failed to load media library:', error);
    }
};

// Open media library
const openMediaLibrary = () => {
    const totalImages = form.existing_images.length + form.images.length + form.media_ids.length;
    if (totalImages >= 8) return;
    loadMediaLibrary();
    showMediaLibrary.value = true;
};

// Upload image to media library
const uploadImage = async (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (!target.files || target.files.length === 0) return;
    
    const file = target.files[0];
    uploadingImage.value = true;
    imageUploadError.value = '';
    
    const formData = new FormData();
    formData.append('file', file);
    
    try {
        const response = await axios.post(route('marketplace.seller.media.store'), formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        if (response.data.media) {
            mediaLibrary.value.unshift(response.data.media);
        }
        target.value = '';
        return response.data.media;
    } catch (error: any) {
        imageUploadError.value = error.response?.data?.message || 'Upload failed';
        throw error;
    } finally {
        uploadingImage.value = false;
    }
};

// Select image from media library (reference-based, no re-upload)
const selectMediaImage = (media: MediaItem) => {
    const totalImages = form.existing_images.length + form.images.length + form.media_ids.length;
    if (totalImages >= 8) return;
    
    form.media_ids.push(media.id as number);
    imagePreview.value.push(media.url);
    imageTypes.value.push('media');
    showMediaLibrary.value = false;
};

// Handle cropped image from editor
const handleCroppedImage = (dataUrl: string, originalMedia: MediaItem) => {
    const totalImages = form.existing_images.length + form.images.length + form.media_ids.length;
    if (totalImages >= 8) return;
    
    fetch(dataUrl)
        .then(res => res.blob())
        .then(blob => {
            const filename = `cropped_${originalMedia.originalName || 'image'}.jpg`;
            const file = new File([blob], filename, { type: 'image/jpeg' });
            form.images.push(file);
            imagePreview.value.push(dataUrl);
            imageTypes.value.push('file');
            showMediaLibrary.value = false;
        })
        .catch(err => console.error('Failed to process cropped image:', err));
};

// Handle stock photo selection
const handleStockPhotoSelect = async (photoUrl: string, attribution: string) => {
    const totalImages = form.existing_images.length + form.images.length + form.media_ids.length;
    if (totalImages >= 8) return;
    
    try {
        const response = await fetch(photoUrl);
        const blob = await response.blob();
        const file = new File([blob], `stock_${Date.now()}.jpg`, { type: 'image/jpeg' });
        form.images.push(file);
        imagePreview.value.push(photoUrl);
        imageTypes.value.push('file');
        showMediaLibrary.value = false;
    } catch (error) {
        console.error('Failed to download stock photo:', error);
    }
};

// Delete image from media library
const deleteMediaImage = async (media: MediaItem) => {
    try {
        await axios.delete(route('marketplace.seller.media.destroy', media.id));
        mediaLibrary.value = mediaLibrary.value.filter(m => m.id !== media.id);
    } catch (error) {
        console.error('Failed to delete media:', error);
    }
};

// Direct file upload (not through media library)
const handleImageUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        const totalImages = form.existing_images.length + form.images.length + form.media_ids.length;
        const newFiles = Array.from(target.files).slice(0, 8 - totalImages);
        
        newFiles.forEach(file => {
            form.images.push(file);
            const reader = new FileReader();
            reader.onload = (e) => {
                imagePreview.value.push(e.target?.result as string);
                imageTypes.value.push('file');
            };
            reader.readAsDataURL(file);
        });
    }
};

const removeImage = (index: number) => {
    const type = imageTypes.value[index];
    
    if (type === 'existing') {
        // Count existing images before this index
        let existingIndex = 0;
        for (let i = 0; i < index; i++) {
            if (imageTypes.value[i] === 'existing') existingIndex++;
        }
        form.existing_images.splice(existingIndex, 1);
    } else if (type === 'media') {
        // Count media refs before this index
        let mediaIndex = 0;
        for (let i = 0; i < index; i++) {
            if (imageTypes.value[i] === 'media') mediaIndex++;
        }
        form.media_ids.splice(mediaIndex, 1);
    } else {
        // Count file uploads before this index
        let fileIndex = 0;
        for (let i = 0; i < index; i++) {
            if (imageTypes.value[i] === 'file') fileIndex++;
        }
        form.images.splice(fileIndex, 1);
    }
    
    imagePreview.value.splice(index, 1);
    imageTypes.value.splice(index, 1);
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(route('marketplace.seller.products.update', props.product.id), {
        forceFormData: true,
    });
};

// Submit appeal for rejected product
const submitAppeal = () => {
    if (!appealMessage.value.trim()) return;
    
    appealProcessing.value = true;
    router.post(route('marketplace.seller.products.appeal', props.product.id), {
        appeal_message: appealMessage.value,
    }, {
        onSuccess: () => {
            showAppealForm.value = false;
            appealMessage.value = '';
        },
        onFinish: () => appealProcessing.value = false,
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
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Product Images (Max 8)</h2>
                        
                        <!-- Image Preview -->
                        <div class="flex flex-wrap gap-3 mb-4">
                            <div
                                v-for="(image, index) in imagePreview"
                                :key="index"
                                class="relative w-20 h-20"
                            >
                                <img :src="image" class="w-full h-full object-cover rounded-lg border-2 border-gray-200" />
                                <button
                                    type="button"
                                    @click="removeImage(index)"
                                    class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600"
                                >
                                    <XMarkIcon class="h-4 w-4" aria-hidden="true" />
                                </button>
                                <span
                                    v-if="index === 0"
                                    class="absolute bottom-1 left-1 px-1.5 py-0.5 bg-amber-500 text-white text-xs rounded"
                                >
                                    Main
                                </span>
                            </div>
                            
                            <!-- Media Library Button -->
                            <button 
                                v-if="(form.existing_images.length + form.images.length + form.media_ids.length) < 8"
                                type="button"
                                @click="openMediaLibrary"
                                class="w-20 h-20 border-2 border-dashed border-orange-300 rounded-lg flex flex-col items-center justify-center cursor-pointer hover:border-orange-500 hover:bg-orange-50 transition-colors"
                            >
                                <PhotoIcon class="h-6 w-6 text-orange-500" aria-hidden="true" />
                                <span class="text-xs text-orange-600 mt-1">Library</span>
                            </button>
                            
                            <!-- Direct Upload Button -->
                            <label 
                                v-if="(form.existing_images.length + form.images.length + form.media_ids.length) < 8"
                                class="w-20 h-20 border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center cursor-pointer hover:border-orange-500 hover:bg-orange-50 transition-colors"
                            >
                                <PhotoIcon class="h-6 w-6 text-gray-400" aria-hidden="true" />
                                <span class="text-xs text-gray-500 mt-1">Upload</span>
                                <input type="file" accept="image/*" multiple @change="handleImageUpload" class="hidden" />
                            </label>
                        </div>
                        
                        <p class="text-xs text-gray-500">
                            First image is the main thumbnail. Use Media Library to edit images with crop, filters, and more!
                        </p>
                        <p v-if="form.errors.images" class="mt-2 text-sm text-red-600">{{ form.errors.images }}</p>
                    </div>

                    <!-- Feedback Alert (for rejected or changes_requested) -->
                    <div v-if="needsChanges || isRejected" :class="[
                        'rounded-lg p-4 border',
                        isRejected ? 'bg-red-50 border-red-200' : 'bg-orange-50 border-orange-200'
                    ]">
                        <div class="flex items-start gap-3">
                            <ExclamationTriangleIcon :class="[
                                'h-5 w-5 mt-0.5 flex-shrink-0',
                                isRejected ? 'text-red-600' : 'text-orange-600'
                            ]" aria-hidden="true" />
                            <div class="flex-1">
                                <p :class="[
                                    'text-sm font-medium',
                                    isRejected ? 'text-red-800' : 'text-orange-800'
                                ]">
                                    {{ isRejected ? 'Product Rejected' : 'Changes Requested' }} - 
                                    {{ product.rejection_category?.replace(/_/g, ' ').replace(/\b\w/g, (l: string) => l.toUpperCase()) }}
                                </p>
                                <p :class="[
                                    'text-sm mt-1',
                                    isRejected ? 'text-red-700' : 'text-orange-700'
                                ]">
                                    {{ product.rejection_reason }}
                                </p>
                                
                                <!-- Field-specific feedback -->
                                <div v-if="product.field_feedback && product.field_feedback.length > 0" class="mt-3 space-y-2">
                                    <p :class="[
                                        'text-xs font-medium',
                                        isRejected ? 'text-red-800' : 'text-orange-800'
                                    ]">Specific issues to address:</p>
                                    <div v-for="(feedback, idx) in product.field_feedback" :key="idx" 
                                         :class="[
                                             'text-sm p-2 rounded',
                                             isRejected ? 'bg-red-100' : 'bg-orange-100'
                                         ]">
                                        <span class="font-medium">{{ feedback.field }}:</span> {{ feedback.message }}
                                    </div>
                                </div>

                                <!-- Appeal section for rejected products -->
                                <div v-if="isRejected" class="mt-4 pt-3 border-t" :class="isRejected ? 'border-red-200' : 'border-orange-200'">
                                    <div v-if="hasAppealed" class="p-3 bg-blue-50 rounded-lg">
                                        <p class="text-sm font-medium text-blue-800">Appeal Submitted</p>
                                        <p class="text-sm text-blue-700 mt-1">{{ product.appeal_message }}</p>
                                        <p class="text-xs text-blue-600 mt-1">
                                            Submitted: {{ product.appealed_at ? new Date(product.appealed_at).toLocaleDateString() : 'N/A' }}
                                        </p>
                                    </div>
                                    <div v-else-if="showAppealForm" class="space-y-3">
                                        <textarea
                                            v-model="appealMessage"
                                            rows="3"
                                            placeholder="Explain why you believe this product should be approved..."
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                        ></textarea>
                                        <div class="flex gap-2">
                                            <button
                                                type="button"
                                                @click="showAppealForm = false"
                                                class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50"
                                            >
                                                Cancel
                                            </button>
                                            <button
                                                type="button"
                                                @click="submitAppeal"
                                                :disabled="!appealMessage.trim() || appealProcessing"
                                                class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                                            >
                                                {{ appealProcessing ? 'Submitting...' : 'Submit Appeal' }}
                                            </button>
                                        </div>
                                    </div>
                                    <button
                                        v-else-if="canAppeal"
                                        type="button"
                                        @click="showAppealForm = true"
                                        class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                                    >
                                        Appeal this decision →
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Info (for other statuses) -->
                    <div v-else class="bg-amber-50 border border-amber-200 rounded-lg p-4">
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
                            {{ needsChanges ? 'Resubmit for Review' : 'Update Product' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Media Library Modal -->
        <MediaLibraryModal
            :show="showMediaLibrary"
            :mediaLibrary="mediaLibrary"
            :uploading="uploadingImage"
            :uploadError="imageUploadError"
            :allowCrop="true"
            @close="showMediaLibrary = false"
            @upload="uploadImage"
            @select="selectMediaImage"
            @selectCropped="handleCroppedImage"
            @selectStockPhoto="handleStockPhotoSelect"
            @delete="deleteMediaImage"
        />
    </MarketplaceLayout>
</template>
