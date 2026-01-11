<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';
import MediaLibraryModal from '@/pages/GrowBuilder/Editor/components/modals/MediaLibraryModal.vue';
import { ArrowLeftIcon, PhotoIcon, XMarkIcon, InformationCircleIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';

interface Category { id: number; name: string; }
interface ImageGuidelines {
    recommended_size: string;
    max_file_size: string;
    formats: string[];
    background: string;
    lighting: string;
    angles: string;
    details: string;
    lifestyle: string;
    consistency: string;
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

const props = defineProps<{ 
    categories: Category[];
    imageGuidelines?: ImageGuidelines;
}>();

const showGuidelines = ref(false);
const showMediaLibrary = ref(false);
const uploadingImage = ref(false);
const imageUploadError = ref('');
const mediaLibrary = ref<MediaItem[]>([]);

const form = useForm({
    name: '',
    category_id: '',
    description: '',
    price: '',
    compare_price: '',
    stock_quantity: '',
    images: [] as File[],
    media_ids: [] as number[], // Reference existing media library images (no re-upload)
});

const imagePreviews = ref<string[]>([]);
// Track each image: 'file' = new upload, 'media' = reference to existing media library item
const imageTypes = ref<('file' | 'media')[]>([]);

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
    const totalImages = form.images.length + form.media_ids.length;
    if (totalImages >= 8) {
        return;
    }
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
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
        if (response.data.media) {
            mediaLibrary.value.unshift(response.data.media);
        }
        // Reset the input
        target.value = '';
        return response.data.media;
    } catch (error: any) {
        imageUploadError.value = error.response?.data?.message || 'Upload failed';
        throw error;
    } finally {
        uploadingImage.value = false;
    }
};

// Select image from media library (direct select without editing)
// Uses reference-based approach - no re-upload, just store media ID
const selectMediaImage = (media: MediaItem) => {
    const totalImages = form.images.length + form.media_ids.length;
    if (totalImages >= 8) {
        return;
    }
    
    // Store media ID reference instead of re-downloading the file
    form.media_ids.push(media.id as number);
    imagePreviews.value.push(media.url);
    imageTypes.value.push('media');
    showMediaLibrary.value = false;
};

// Handle cropped image from editor (receives dataUrl string, not Blob)
// Cropped images are NEW files since they've been modified
const handleCroppedImage = (dataUrl: string, originalMedia: MediaItem) => {
    const totalImages = form.images.length + form.media_ids.length;
    if (totalImages >= 8) {
        return;
    }
    
    // Convert data URL to Blob then to File - this is a new file (cropped version)
    fetch(dataUrl)
        .then(res => res.blob())
        .then(blob => {
            const filename = `cropped_${originalMedia.originalName || 'image'}.jpg`;
            const file = new File([blob], filename, { type: 'image/jpeg' });
            form.images.push(file);
            imagePreviews.value.push(dataUrl);
            imageTypes.value.push('file');
            showMediaLibrary.value = false;
        })
        .catch(err => {
            console.error('Failed to process cropped image:', err);
        });
};

// Handle stock photo selection - these are new files (downloaded from external source)
const handleStockPhotoSelect = async (photoUrl: string, attribution: string) => {
    const totalImages = form.images.length + form.media_ids.length;
    if (totalImages >= 8) {
        return;
    }
    
    try {
        const response = await fetch(photoUrl);
        const blob = await response.blob();
        const file = new File([blob], `stock_${Date.now()}.jpg`, { type: 'image/jpeg' });
        form.images.push(file);
        imagePreviews.value.push(photoUrl);
        imageTypes.value.push('file');
        showMediaLibrary.value = false;
    } catch (error) {
        console.error('Failed to download stock photo:', error);
    }
};

// Delete image from media library (receives full media object)
const deleteMediaImage = async (media: MediaItem) => {
    try {
        await axios.delete(route('marketplace.seller.media.destroy', media.id));
        mediaLibrary.value = mediaLibrary.value.filter(m => m.id !== media.id);
    } catch (error) {
        console.error('Failed to delete media:', error);
    }
};

const handleImageSelect = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files) {
        const totalImages = form.images.length + form.media_ids.length;
        const newFiles = Array.from(target.files).slice(0, 8 - totalImages);
        form.images.push(...newFiles);
        newFiles.forEach(file => {
            const reader = new FileReader();
            reader.onload = (e) => {
                imagePreviews.value.push(e.target?.result as string);
                imageTypes.value.push('file');
            };
            reader.readAsDataURL(file);
        });
    }
};

const removeImage = (index: number) => {
    const type = imageTypes.value[index];
    
    if (type === 'media') {
        // Find which media_id to remove
        // Count how many 'media' types come before this index
        let mediaIndex = 0;
        for (let i = 0; i < index; i++) {
            if (imageTypes.value[i] === 'media') mediaIndex++;
        }
        form.media_ids.splice(mediaIndex, 1);
    } else {
        // Find which file to remove
        let fileIndex = 0;
        for (let i = 0; i < index; i++) {
            if (imageTypes.value[i] === 'file') fileIndex++;
        }
        form.images.splice(fileIndex, 1);
    }
    
    imagePreviews.value.splice(index, 1);
    imageTypes.value.splice(index, 1);
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
                    <input v-model="form.name" type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 bg-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors" placeholder="e.g. iPhone 13 Pro" />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                    <select v-model="form.category_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 bg-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors">
                        <option value="">Select category</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                    <p v-if="form.errors.category_id" class="mt-1 text-sm text-red-600">{{ form.errors.category_id }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                    <textarea v-model="form.description" rows="4" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 bg-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors" 
                        placeholder="Describe your product..."></textarea>
                    <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price (K) *</label>
                        <input v-model="form.price" type="number" step="0.01" min="1" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 bg-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors" placeholder="0.00" />
                        <p v-if="form.errors.price" class="mt-1 text-sm text-red-600">{{ form.errors.price }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Compare Price (K)</label>
                        <input v-model="form.compare_price" type="number" step="0.01" min="1" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 bg-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors" placeholder="Original price" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity *</label>
                    <input v-model="form.stock_quantity" type="number" min="0" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 bg-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors" placeholder="0" />
                    <p v-if="form.errors.stock_quantity" class="mt-1 text-sm text-red-600">{{ form.errors.stock_quantity }}</p>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium text-gray-700">Product Images * (Max 8)</label>
                        <button type="button" @click="showGuidelines = !showGuidelines" 
                            class="text-sm text-orange-600 hover:text-orange-700 flex items-center gap-1">
                            <InformationCircleIcon class="h-4 w-4" aria-hidden="true" />
                            Image Guidelines
                        </button>
                    </div>

                    <!-- Image Guidelines Panel -->
                    <div v-if="showGuidelines && imageGuidelines" class="mb-4 p-4 bg-orange-50 border border-orange-200 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-2">📸 Image Best Practices</h4>
                        <ul class="space-y-1 text-sm text-gray-700">
                            <li><strong>Size:</strong> {{ imageGuidelines.recommended_size }}</li>
                            <li><strong>Max File Size:</strong> {{ imageGuidelines.max_file_size }}</li>
                            <li><strong>Formats:</strong> {{ imageGuidelines.formats.join(', ') }}</li>
                            <li><strong>Background:</strong> {{ imageGuidelines.background }}</li>
                            <li><strong>Lighting:</strong> {{ imageGuidelines.lighting }}</li>
                            <li><strong>Angles:</strong> {{ imageGuidelines.angles }}</li>
                            <li><strong>Details:</strong> {{ imageGuidelines.details }}</li>
                        </ul>
                        <p class="mt-2 text-xs text-gray-600">
                            💡 <strong>Tip:</strong> High-quality images with white backgrounds get better visibility and more sales!
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <div v-for="(preview, index) in imagePreviews" :key="index" class="relative w-20 h-20">
                            <img :src="preview" class="w-full h-full object-cover rounded-lg border-2 border-gray-200" />
                            <button type="button" @click="removeImage(index)" 
                                class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600">
                                <XMarkIcon class="h-4 w-4" aria-hidden="true" />
                            </button>
                        </div>
                        
                        <!-- Media Library Button -->
                        <button 
                            v-if="(form.images.length + form.media_ids.length) < 8"
                            type="button"
                            @click="openMediaLibrary"
                            class="w-20 h-20 border-2 border-dashed border-orange-300 rounded-lg flex flex-col items-center justify-center cursor-pointer hover:border-orange-500 hover:bg-orange-50 transition-colors"
                        >
                            <PhotoIcon class="h-6 w-6 text-orange-500" aria-hidden="true" />
                            <span class="text-xs text-orange-600 mt-1">Library</span>
                        </button>
                        
                        <!-- Upload Button -->
                        <label v-if="(form.images.length + form.media_ids.length) < 8" 
                            class="w-20 h-20 border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center cursor-pointer hover:border-orange-500 hover:bg-orange-50 transition-colors">
                            <PhotoIcon class="h-6 w-6 text-gray-400" aria-hidden="true" />
                            <span class="text-xs text-gray-500 mt-1">Upload</span>
                            <input type="file" accept="image/*" multiple @change="handleImageSelect" class="hidden" />
                        </label>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">
                        First image will be used as the main product thumbnail. Use the Media Library to edit images with crop, filters, and more!
                    </p>
                    <p v-if="form.errors.images" class="mt-1 text-sm text-red-600">{{ form.errors.images }}</p>
                </div>

                <div class="flex gap-3 pt-4">
                    <Link :href="route('marketplace.seller.products.index')" class="px-6 py-2 border border-gray-300 text-gray-700 bg-white rounded-lg hover:bg-gray-50 font-medium">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing" 
                        class="flex-1 px-6 py-2 bg-orange-500 text-white font-medium rounded-lg hover:bg-orange-600 disabled:opacity-50">
                        {{ form.processing ? 'Saving...' : 'Add Product' }}
                    </button>
                </div>
            </form>
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
