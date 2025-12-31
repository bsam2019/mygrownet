<script setup lang="ts">
/**
 * Reusable Image Uploader Component
 * Supports file upload, URL input, and image editing (crop, adjust, export)
 * Can be used across GrowBuilder for products, posts, profiles, etc.
 */
import { ref, computed } from 'vue';
import { PhotoIcon, XMarkIcon, PencilIcon, LinkIcon, ArrowUpTrayIcon } from '@heroicons/vue/24/outline';
import ImageEditorModal from '@/pages/GrowBuilder/Editor/components/modals/ImageEditorModal.vue';

interface Props {
    modelValue: string[];
    maxImages?: number;
    siteId?: number;
    subdomain?: string;
    aspectRatio?: number;
    showEditor?: boolean;
    uploadEndpoint?: string;
    acceptTypes?: string;
    maxFileSize?: number; // in MB
}

const props = withDefaults(defineProps<Props>(), {
    maxImages: 10,
    showEditor: true,
    acceptTypes: 'image/jpeg,image/png,image/webp,image/gif',
    maxFileSize: 5,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string[]): void;
}>();

const fileInputRef = ref<HTMLInputElement | null>(null);
const isUploading = ref(false);
const uploadProgress = ref(0);
const uploadError = ref('');

// Image editor state
const showImageEditor = ref(false);
const editingImageUrl = ref('');
const editingImageIndex = ref<number | null>(null);

// URL input state
const showUrlInput = ref(false);
const urlInputValue = ref('');

const images = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const canAddMore = computed(() => images.value.length < props.maxImages);

// Trigger file input
const triggerFileInput = () => {
    fileInputRef.value?.click();
};

// Handle file selection
const handleFileSelect = async (event: Event) => {
    const input = event.target as HTMLInputElement;
    const files = input.files;
    if (!files || files.length === 0) return;

    uploadError.value = '';
    
    for (const file of Array.from(files)) {
        if (!canAddMore.value) break;
        
        // Validate file type
        if (!props.acceptTypes.split(',').some(type => file.type.match(type.trim()))) {
            uploadError.value = `Invalid file type: ${file.name}`;
            continue;
        }
        
        // Validate file size
        if (file.size > props.maxFileSize * 1024 * 1024) {
            uploadError.value = `File too large: ${file.name} (max ${props.maxFileSize}MB)`;
            continue;
        }

        await uploadFile(file);
    }
    
    // Reset input
    input.value = '';
};

// Upload file to server
const uploadFile = async (file: File) => {
    if (!props.uploadEndpoint && !props.siteId) {
        // No upload endpoint - convert to data URL for local preview
        const reader = new FileReader();
        reader.onload = (e) => {
            const dataUrl = e.target?.result as string;
            if (props.showEditor) {
                // Open editor for the uploaded image
                editingImageUrl.value = dataUrl;
                editingImageIndex.value = null; // New image
                showImageEditor.value = true;
            } else {
                images.value = [...images.value, dataUrl];
            }
        };
        reader.readAsDataURL(file);
        return;
    }

    isUploading.value = true;
    uploadProgress.value = 0;

    try {
        const formData = new FormData();
        formData.append('file', file);

        const endpoint = props.uploadEndpoint || `/growbuilder/sites/${props.siteId}/media`;
        
        const response = await fetch(endpoint, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });

        if (!response.ok) {
            throw new Error('Upload failed');
        }

        const data = await response.json();
        const imageUrl = data.url || data.path;

        if (props.showEditor) {
            editingImageUrl.value = imageUrl;
            editingImageIndex.value = null;
            showImageEditor.value = true;
        } else {
            images.value = [...images.value, imageUrl];
        }
    } catch (error) {
        uploadError.value = 'Failed to upload image. Please try again.';
        console.error('Upload error:', error);
    } finally {
        isUploading.value = false;
        uploadProgress.value = 0;
    }
};

// Add image from URL
const addFromUrl = () => {
    if (!urlInputValue.value.trim()) return;
    
    const url = urlInputValue.value.trim();
    
    if (props.showEditor) {
        editingImageUrl.value = url;
        editingImageIndex.value = null;
        showImageEditor.value = true;
    } else {
        images.value = [...images.value, url];
    }
    
    urlInputValue.value = '';
    showUrlInput.value = false;
};

// Edit existing image
const editImage = (index: number) => {
    editingImageUrl.value = images.value[index];
    editingImageIndex.value = index;
    showImageEditor.value = true;
};

// Handle edited image save
const handleImageEditorSave = (dataUrl: string) => {
    if (editingImageIndex.value !== null) {
        // Replace existing image
        const newImages = [...images.value];
        newImages[editingImageIndex.value] = dataUrl;
        images.value = newImages;
    } else {
        // Add new image
        images.value = [...images.value, dataUrl];
    }
    
    showImageEditor.value = false;
    editingImageUrl.value = '';
    editingImageIndex.value = null;
};

// Remove image
const removeImage = (index: number) => {
    images.value = images.value.filter((_, i) => i !== index);
};

// Reorder images (drag and drop)
const draggedIndex = ref<number | null>(null);

const handleDragStart = (index: number) => {
    draggedIndex.value = index;
};

const handleDragOver = (event: DragEvent, index: number) => {
    event.preventDefault();
    if (draggedIndex.value === null || draggedIndex.value === index) return;
    
    const newImages = [...images.value];
    const [draggedItem] = newImages.splice(draggedIndex.value, 1);
    newImages.splice(index, 0, draggedItem);
    images.value = newImages;
    draggedIndex.value = index;
};

const handleDragEnd = () => {
    draggedIndex.value = null;
};
</script>

<template>
    <div class="space-y-4">
        <!-- Image Grid -->
        <div class="grid grid-cols-4 gap-4">
            <!-- Existing Images -->
            <div
                v-for="(image, index) in images"
                :key="index"
                class="relative aspect-square bg-gray-100 rounded-lg overflow-hidden group"
                draggable="true"
                @dragstart="handleDragStart(index)"
                @dragover="handleDragOver($event, index)"
                @dragend="handleDragEnd"
            >
                <img :src="image" class="w-full h-full object-cover" :alt="`Image ${index + 1}`" />
                
                <!-- First image badge -->
                <span v-if="index === 0" class="absolute top-1 left-1 px-1.5 py-0.5 bg-blue-600 text-white text-[10px] font-medium rounded">
                    Main
                </span>
                
                <!-- Hover overlay with actions -->
                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                    <button
                        v-if="showEditor"
                        type="button"
                        @click="editImage(index)"
                        class="p-2 bg-white rounded-full hover:bg-gray-100 transition-colors"
                        title="Edit image"
                    >
                        <PencilIcon class="w-4 h-4 text-gray-700" aria-hidden="true" />
                    </button>
                    <button
                        type="button"
                        @click="removeImage(index)"
                        class="p-2 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors"
                        title="Remove image"
                    >
                        <XMarkIcon class="w-4 h-4" aria-hidden="true" />
                    </button>
                </div>
                
                <!-- Drag handle indicator -->
                <div class="absolute bottom-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <span class="text-[10px] text-white bg-black/50 px-1 rounded">Drag to reorder</span>
                </div>
            </div>

            <!-- Add Image Button -->
            <div v-if="canAddMore" class="aspect-square">
                <div
                    v-if="isUploading"
                    class="w-full h-full border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center"
                >
                    <div class="animate-spin rounded-full h-8 w-8 border-2 border-blue-500 border-t-transparent mb-2"></div>
                    <span class="text-xs text-gray-500">Uploading...</span>
                </div>
                <div v-else class="relative w-full h-full">
                    <button
                        type="button"
                        @click="triggerFileInput"
                        class="w-full h-full border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center text-gray-400 hover:border-blue-400 hover:text-blue-500 transition-colors"
                    >
                        <ArrowUpTrayIcon class="w-8 h-8 mb-1" aria-hidden="true" />
                        <span class="text-xs">Upload</span>
                    </button>
                    
                    <!-- URL input toggle -->
                    <button
                        type="button"
                        @click="showUrlInput = !showUrlInput"
                        class="absolute bottom-1 right-1 p-1 bg-gray-100 rounded hover:bg-gray-200 transition-colors"
                        title="Add from URL"
                    >
                        <LinkIcon class="w-4 h-4 text-gray-500" aria-hidden="true" />
                    </button>
                </div>
            </div>
        </div>

        <!-- URL Input -->
        <div v-if="showUrlInput" class="flex gap-2">
            <input
                v-model="urlInputValue"
                type="url"
                placeholder="Enter image URL..."
                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                @keyup.enter="addFromUrl"
            />
            <button
                type="button"
                @click="addFromUrl"
                class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
            >
                Add
            </button>
            <button
                type="button"
                @click="showUrlInput = false; urlInputValue = ''"
                class="px-3 py-2 text-gray-500 hover:text-gray-700"
            >
                Cancel
            </button>
        </div>

        <!-- Error Message -->
        <p v-if="uploadError" class="text-sm text-red-600">{{ uploadError }}</p>

        <!-- Help Text -->
        <p class="text-xs text-gray-500">
            {{ images.length }}/{{ maxImages }} images. 
            <span v-if="showEditor">Click edit to crop and adjust.</span>
            Drag to reorder. First image is the main product image.
        </p>

        <!-- Hidden File Input -->
        <input
            ref="fileInputRef"
            type="file"
            :accept="acceptTypes"
            multiple
            class="hidden"
            @change="handleFileSelect"
        />

        <!-- Image Editor Modal -->
        <ImageEditorModal
            :show="showImageEditor"
            :image-url="editingImageUrl"
            :aspect-ratio="aspectRatio"
            @close="showImageEditor = false"
            @save="handleImageEditorSave"
        />
    </div>
</template>
