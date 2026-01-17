<script setup lang="ts">
/**
 * Enhanced Media Library Modal
 * Tabs: My Media, Stock Photos
 * Features: Upload, Crop, Delete, Stock photo search
 */
import { ref, computed } from 'vue';
import {
    XMarkIcon,
    PhotoIcon,
    ScissorsIcon,
    TrashIcon,
    CloudArrowUpIcon,
    MagnifyingGlassIcon,
    ArrowDownTrayIcon,
    GlobeAltIcon,
} from '@heroicons/vue/24/outline';
import ImageEditorModal from './ImageEditorModal.vue';

interface MediaItem {
    id: number | string;
    url: string;
    thumbnailUrl?: string;
    originalName: string;
}

interface StockPhoto {
    id: string;
    urls: { regular: string; small: string; thumb: string };
    alt_description: string;
    user: { name: string };
}

const props = defineProps<{
    show: boolean;
    mediaLibrary: MediaItem[];
    uploading: boolean;
    uploadError: string | null;
    allowCrop?: boolean;
    aspectRatio?: number;
    forceAspectRatio?: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'upload', event: Event): void;
    (e: 'select', media: MediaItem): void;
    (e: 'selectCropped', dataUrl: string, originalMedia: MediaItem): void;
    (e: 'selectStockPhoto', url: string, attribution: string): void;
    (e: 'delete', media: MediaItem): void;
}>();

// Tab state
const activeTab = ref<'library' | 'stock'>('library');

// Image editor state
const showImageEditor = ref(false);
const selectedMediaForEdit = ref<MediaItem | null>(null);

// Stock photos state
const stockSearchQuery = ref('');
const stockPhotos = ref<StockPhoto[]>([]);
const stockLoading = ref(false);
const stockCategories = ['business', 'technology', 'nature', 'food', 'office', 'people', 'abstract', 'minimal'];

// Stock photo for editing
const stockPhotoForEdit = ref<StockPhoto | null>(null);

// Handle media selection
const handleMediaClick = (media: MediaItem) => {
    if (props.allowCrop) {
        selectedMediaForEdit.value = media;
        showImageEditor.value = true;
    } else {
        emit('select', media);
    }
};

const handleDirectSelect = (media: MediaItem, e: Event) => {
    e.stopPropagation();
    emit('select', media);
};

const handleCropSave = (dataUrl: string) => {
    if (selectedMediaForEdit.value) {
        emit('selectCropped', dataUrl, selectedMediaForEdit.value);
    } else if (stockPhotoForEdit.value) {
        // For stock photos, emit as cropped with attribution
        emit('selectCropped', dataUrl, {
            id: stockPhotoForEdit.value.id,
            url: stockPhotoForEdit.value.urls.regular,
            originalName: `Stock photo by ${stockPhotoForEdit.value.user.name}`,
        });
    }
    showImageEditor.value = false;
    selectedMediaForEdit.value = null;
    stockPhotoForEdit.value = null;
};

const handleDelete = (media: MediaItem, e: Event) => {
    e.stopPropagation();
    if (confirm(`Delete "${media.originalName}"?`)) {
        emit('delete', media);
    }
};

const closeImageEditor = () => {
    showImageEditor.value = false;
    selectedMediaForEdit.value = null;
    stockPhotoForEdit.value = null;
};

// Stock photos functions
const searchStockPhotos = async (query: string) => {
    if (!query.trim()) return;
    stockLoading.value = true;
    
    try {
        // Using Lorem Picsum for reliable demo images
        // Each image gets a unique seed based on query + index for variety
        const baseId = query.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0);
        
        stockPhotos.value = Array.from({ length: 16 }, (_, i) => {
            const imageId = (baseId + i * 10) % 1000 + 100; // Generate IDs between 100-1099
            return {
                id: `stock-${query}-${i}`,
                urls: {
                    regular: `https://picsum.photos/id/${imageId}/800/600`,
                    small: `https://picsum.photos/id/${imageId}/400/300`,
                    thumb: `https://picsum.photos/id/${imageId}/200/150`,
                },
                alt_description: `${query} photo`,
                user: { name: 'Lorem Picsum' },
            };
        });
    } catch (err) {
        console.error('Stock photo search failed:', err);
    } finally {
        stockLoading.value = false;
    }
};

const selectStockPhoto = (photo: StockPhoto) => {
    if (props.allowCrop) {
        // Open image editor for stock photo
        stockPhotoForEdit.value = photo;
        selectedMediaForEdit.value = {
            id: photo.id,
            url: photo.urls.regular,
            originalName: `Stock photo by ${photo.user.name}`,
        };
        showImageEditor.value = true;
    } else {
        emit('selectStockPhoto', photo.urls.regular, `Photo by ${photo.user.name}`);
    }
};

// Direct select stock photo without editing
const selectStockPhotoDirect = (photo: StockPhoto, e: Event) => {
    e.stopPropagation();
    emit('selectStockPhoto', photo.urls.regular, `Photo by ${photo.user.name}`);
};

const handleStockSearch = () => {
    searchStockPhotos(stockSearchQuery.value);
};

const selectCategory = (cat: string) => {
    stockSearchQuery.value = cat;
    searchStockPhotos(cat);
};
</script>

<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click="emit('close')">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[85vh] flex flex-col" @click.stop>
                <!-- Header with Tabs -->
                <div class="border-b border-gray-200">
                    <div class="flex items-center justify-between px-4 pt-4 pb-0">
                        <h2 class="text-lg font-semibold text-gray-900">Media Library</h2>
                        <button @click="emit('close')" class="p-1 hover:bg-gray-100 rounded-lg" aria-label="Close">
                            <XMarkIcon class="w-5 h-5 text-gray-500" />
                        </button>
                    </div>
                    
                    <!-- Tabs -->
                    <div class="flex gap-1 px-4 mt-3">
                        <button
                            @click="activeTab = 'library'"
                            :class="[
                                'flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-t-lg border-b-2 transition-colors',
                                activeTab === 'library'
                                    ? 'text-blue-600 border-blue-600 bg-blue-50'
                                    : 'text-gray-500 border-transparent hover:text-gray-700 hover:bg-gray-50'
                            ]"
                        >
                            <CloudArrowUpIcon class="w-4 h-4" />
                            My Media
                        </button>
                        <button
                            @click="activeTab = 'stock'; stockPhotos.length === 0 && searchStockPhotos('business')"
                            :class="[
                                'flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-t-lg border-b-2 transition-colors',
                                activeTab === 'stock'
                                    ? 'text-blue-600 border-blue-600 bg-blue-50'
                                    : 'text-gray-500 border-transparent hover:text-gray-700 hover:bg-gray-50'
                            ]"
                        >
                            <GlobeAltIcon class="w-4 h-4" />
                            Stock Photos
                        </button>
                    </div>
                </div>

                <!-- My Media Tab -->
                <template v-if="activeTab === 'library'">
                    <!-- Upload Section -->
                    <div class="p-4 border-b border-gray-100 bg-gray-50">
                        <label class="flex flex-col items-center justify-center w-full h-20 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-400 hover:bg-blue-50/50 transition-colors">
                            <div class="flex items-center gap-3">
                                <CloudArrowUpIcon class="w-6 h-6 text-gray-400" />
                                <div class="text-left">
                                    <p class="text-sm text-gray-600">
                                        <span v-if="uploading">Uploading...</span>
                                        <span v-else><span class="text-blue-600 font-medium">Click to upload</span> or drag and drop</span>
                                    </p>
                                    <p class="text-xs text-gray-400">PNG, JPG, GIF, WebP up to 5MB</p>
                                </div>
                            </div>
                            <input type="file" class="hidden" accept="image/*" @change="emit('upload', $event)" :disabled="uploading" />
                        </label>
                        <p v-if="uploadError" class="mt-2 text-sm text-red-600">{{ uploadError }}</p>
                    </div>

                    <!-- Media Grid -->
                    <div class="flex-1 overflow-y-auto p-4 custom-scrollbar">
                        <div v-if="mediaLibrary.length > 0" class="grid grid-cols-4 md:grid-cols-5 gap-3">
                            <div
                                v-for="media in mediaLibrary"
                                :key="media.id"
                                class="group relative aspect-square rounded-lg overflow-hidden border-2 border-transparent hover:border-blue-500 transition-colors cursor-pointer bg-gray-100"
                                @click="handleMediaClick(media)"
                            >
                                <img :src="media.thumbnailUrl || media.url" :alt="media.originalName" class="w-full h-full object-cover" loading="lazy" />
                                
                                <!-- Overlay -->
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
                                    <button v-if="allowCrop" class="p-2 bg-white rounded-full shadow-lg hover:bg-gray-100" title="Crop">
                                        <ScissorsIcon class="w-4 h-4 text-gray-700" />
                                    </button>
                                    <button @click="handleDirectSelect(media, $event)" class="p-2 bg-blue-600 rounded-full shadow-lg hover:bg-blue-700" title="Select">
                                        <PhotoIcon class="w-4 h-4 text-white" />
                                    </button>
                                    <button @click="handleDelete(media, $event)" class="p-2 bg-red-600 rounded-full shadow-lg hover:bg-red-700" title="Delete">
                                        <TrashIcon class="w-4 h-4 text-white" />
                                    </button>
                                </div>
                                
                                <!-- File name -->
                                <div class="absolute bottom-0 left-0 right-0 p-1 bg-black/60 text-white text-xs truncate opacity-0 group-hover:opacity-100">
                                    {{ media.originalName }}
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-16 text-gray-400">
                            <PhotoIcon class="w-16 h-16 mx-auto mb-4 text-gray-200" />
                            <p class="font-medium">No images uploaded yet</p>
                            <p class="text-sm mt-1">Upload your first image above</p>
                        </div>
                    </div>
                </template>

                <!-- Stock Photos Tab -->
                <template v-if="activeTab === 'stock'">
                    <!-- Search -->
                    <div class="p-4 border-b border-gray-100 bg-gray-50">
                        <div class="flex gap-2">
                            <div class="flex-1 relative">
                                <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                                <input
                                    v-model="stockSearchQuery"
                                    @keyup.enter="handleStockSearch"
                                    type="text"
                                    placeholder="Search free photos..."
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                            <button @click="handleStockSearch" :disabled="stockLoading" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                Search
                            </button>
                        </div>
                        
                        <!-- Categories -->
                        <div class="flex flex-wrap gap-2 mt-3">
                            <button
                                v-for="cat in stockCategories"
                                :key="cat"
                                @click="selectCategory(cat)"
                                :class="[
                                    'px-3 py-1 text-xs font-medium rounded-full capitalize transition-colors',
                                    stockSearchQuery === cat ? 'bg-blue-600 text-white' : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-100'
                                ]"
                            >
                                {{ cat }}
                            </button>
                        </div>
                    </div>

                    <!-- Stock Photos Grid -->
                    <div class="flex-1 overflow-y-auto p-4 custom-scrollbar">
                        <div v-if="stockLoading" class="flex items-center justify-center py-12">
                            <div class="animate-spin rounded-full h-8 w-8 border-2 border-blue-600 border-t-transparent"></div>
                        </div>
                        <div v-else-if="stockPhotos.length > 0" class="grid grid-cols-3 md:grid-cols-4 gap-3">
                            <div
                                v-for="photo in stockPhotos"
                                :key="photo.id"
                                @click="selectStockPhoto(photo)"
                                class="group relative aspect-[4/3] rounded-lg overflow-hidden bg-gray-100 hover:ring-2 hover:ring-blue-500 transition-all cursor-pointer"
                            >
                                <img :src="photo.urls.small" :alt="photo.alt_description" class="w-full h-full object-cover" loading="lazy" />
                                
                                <!-- Overlay with actions -->
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
                                    <button v-if="allowCrop" class="p-2 bg-white rounded-full shadow-lg hover:bg-gray-100" title="Edit & Crop">
                                        <ScissorsIcon class="w-4 h-4 text-gray-700" />
                                    </button>
                                    <button @click="selectStockPhotoDirect(photo, $event)" class="p-2 bg-blue-600 rounded-full shadow-lg hover:bg-blue-700" title="Use directly">
                                        <ArrowDownTrayIcon class="w-4 h-4 text-white" />
                                    </button>
                                </div>
                                
                                <div class="absolute bottom-0 left-0 right-0 p-2 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100">
                                    <p class="text-xs text-white truncate">{{ photo.user.name }}</p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-16 text-gray-400">
                            <GlobeAltIcon class="w-16 h-16 mx-auto mb-4 text-gray-200" />
                            <p class="font-medium">Search for free stock photos</p>
                            <p class="text-sm mt-1">Powered by Lorem Picsum</p>
                        </div>
                    </div>

                    <!-- Attribution -->
                    <div class="px-4 py-2 border-t border-gray-200 bg-gray-50 text-center">
                        <p class="text-xs text-gray-500">
                            Demo photos by <a href="https://picsum.photos" target="_blank" class="text-blue-600 hover:underline">Lorem Picsum</a> • Free to use
                        </p>
                    </div>
                </template>
            </div>
        </div>
    </Teleport>
    
    <!-- Image Editor Modal -->
    <ImageEditorModal
        :show="showImageEditor"
        :image-url="selectedMediaForEdit?.url || ''"
        :aspect-ratio="aspectRatio"
        :force-aspect-ratio="forceAspectRatio"
        @close="closeImageEditor"
        @save="handleCropSave"
    />
</template>
