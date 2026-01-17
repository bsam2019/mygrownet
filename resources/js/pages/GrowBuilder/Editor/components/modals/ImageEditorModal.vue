<script setup lang="ts">
/**
 * Image Editor Modal - Rewritten with clean cropping logic
 */
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import {
    XMarkIcon,
    ArrowPathIcon,
    CheckIcon,
    LockClosedIcon,
} from '@heroicons/vue/24/outline';

interface CropBox {
    left: number;   // percentage from left edge
    top: number;    // percentage from top edge
    right: number;  // percentage from right edge
    bottom: number; // percentage from bottom edge
}

interface ImageAdjustments {
    brightness: number;
    contrast: number;
    saturation: number;
    blur: number;
}

const props = defineProps<{
    show: boolean;
    imageUrl: string;
    aspectRatio?: number;
    forceAspectRatio?: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'save', dataUrl: string): void;
}>();

// Refs
const canvasRef = ref<HTMLCanvasElement | null>(null);
const imageRef = ref<HTMLImageElement | null>(null);
const containerRef = ref<HTMLDivElement | null>(null);

// State
const isLoading = ref(true);
const activeTab = ref<'crop' | 'adjust' | 'export'>('crop');
const imageNaturalWidth = ref(0);
const imageNaturalHeight = ref(0);

// Crop box using edges (easier to reason about)
// Values are percentages: 0 = edge of image, 100 = opposite edge
const cropBox = ref<CropBox>({ left: 0, top: 0, right: 0, bottom: 0 });

// Interaction state
const interactionMode = ref<'none' | 'drag' | 'resize'>('none');
const activeHandle = ref<string | null>(null);
const startMouse = ref({ x: 0, y: 0 });
const startCrop = ref<CropBox>({ left: 0, top: 0, right: 0, bottom: 0 });

// Image adjustments
const adjustments = ref<ImageAdjustments>({
    brightness: 100,
    contrast: 100,
    saturation: 100,
    blur: 0,
});

// Export settings
const exportQuality = ref(90);
const exportScale = ref(100);
const exportFormat = ref<'jpeg' | 'png' | 'webp'>('jpeg');

// Aspect ratio
const aspectRatios = [
    { label: 'Free', value: null },
    { label: '1:1', value: 1 },
    { label: '16:9', value: 16/9 },
    { label: '4:3', value: 4/3 },
    { label: '3:2', value: 3/2 },
];
const selectedRatio = ref<number | null>(props.aspectRatio || null);
const isRatioLocked = computed(() => props.forceAspectRatio && props.aspectRatio);
const forcedRatioLabel = computed(() => {
    if (!props.aspectRatio) return '';
    const found = aspectRatios.find(r => r.value === props.aspectRatio);
    return found ? found.label : `${props.aspectRatio}:1`;
});

// Computed: crop area as x, y, width, height percentages
const cropArea = computed(() => ({
    x: cropBox.value.left,
    y: cropBox.value.top,
    width: 100 - cropBox.value.left - cropBox.value.right,
    height: 100 - cropBox.value.top - cropBox.value.bottom,
}));

// Computed: crop in pixels
const cropPixels = computed(() => ({
    x: Math.round((cropArea.value.x / 100) * imageNaturalWidth.value),
    y: Math.round((cropArea.value.y / 100) * imageNaturalHeight.value),
    width: Math.round((cropArea.value.width / 100) * imageNaturalWidth.value),
    height: Math.round((cropArea.value.height / 100) * imageNaturalHeight.value),
}));

const outputDimensions = computed(() => ({
    width: Math.round(cropPixels.value.width * (exportScale.value / 100)),
    height: Math.round(cropPixels.value.height * (exportScale.value / 100)),
}));

const sizeReduction = computed(() => {
    const original = imageNaturalWidth.value * imageNaturalHeight.value;
    const output = outputDimensions.value.width * outputDimensions.value.height;
    if (original === 0) return 0;
    return Math.round((1 - output / original) * 100);
});

const filterStyle = computed(() => {
    const { brightness, contrast, saturation, blur } = adjustments.value;
    return `brightness(${brightness}%) contrast(${contrast}%) saturate(${saturation}%) blur(${blur}px)`;
});

// Background style for crop overlay
const cropBackgroundStyle = computed(() => {
    const { x, y, width, height } = cropArea.value;
    if (width <= 0 || height <= 0) return {};
    return {
        backgroundImage: `url(${props.imageUrl})`,
        backgroundSize: `${(100 / width) * 100}% ${(100 / height) * 100}%`,
        backgroundPosition: `${-(x / width) * 100}% ${-(y / height) * 100}%`,
        filter: filterStyle.value,
    };
});

// Load image
const loadImage = () => {
    isLoading.value = true;
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = () => {
        imageNaturalWidth.value = img.naturalWidth;
        imageNaturalHeight.value = img.naturalHeight;
        imageRef.value = img;
        isLoading.value = false;
        resetCrop();
    };
    img.onerror = () => { isLoading.value = false; };
    img.src = props.imageUrl;
};

// Apply aspect ratio to current crop
const applyAspectRatio = (ratio: number | null) => {
    selectedRatio.value = ratio;
    if (!ratio || imageNaturalWidth.value === 0) return;
    
    const imageRatio = imageNaturalWidth.value / imageNaturalHeight.value;
    const currentWidth = cropArea.value.width;
    const currentHeight = cropArea.value.height;
    
    // Calculate new dimensions maintaining aspect ratio
    const targetRatio = ratio / imageRatio;
    
    if (currentWidth / currentHeight > targetRatio) {
        // Too wide, reduce width
        const newWidth = currentHeight * targetRatio;
        const diff = currentWidth - newWidth;
        cropBox.value.right += diff / 2;
        cropBox.value.left += diff / 2;
    } else {
        // Too tall, reduce height
        const newHeight = currentWidth / targetRatio;
        const diff = currentHeight - newHeight;
        cropBox.value.bottom += diff / 2;
        cropBox.value.top += diff / 2;
    }
};

const resetCrop = () => {
    cropBox.value = { left: 0, top: 0, right: 0, bottom: 0 };
    selectedRatio.value = props.aspectRatio || null;
    if (selectedRatio.value && imageNaturalWidth.value > 0) {
        applyAspectRatio(selectedRatio.value);
    }
};

const resetAdjustments = () => {
    adjustments.value = { brightness: 100, contrast: 100, saturation: 100, blur: 0 };
};

const resetAll = () => {
    resetCrop();
    resetAdjustments();
    exportQuality.value = 90;
    exportScale.value = 100;
};

// Mouse position to percentage
const getMousePercent = (e: MouseEvent) => {
    if (!containerRef.value) return { x: 0, y: 0 };
    const rect = containerRef.value.getBoundingClientRect();
    return {
        x: ((e.clientX - rect.left) / rect.width) * 100,
        y: ((e.clientY - rect.top) / rect.height) * 100,
    };
};

// Start dragging the crop box
const startDrag = (e: MouseEvent) => {
    e.preventDefault();
    console.log('Start drag'); // Debug
    interactionMode.value = 'drag';
    startMouse.value = getMousePercent(e);
    startCrop.value = { ...cropBox.value };
    document.addEventListener('mousemove', handleMouseMove);
    document.addEventListener('mouseup', handleMouseUp);
};

// Start resizing from a handle
const startResize = (e: MouseEvent, handle: string) => {
    e.preventDefault();
    e.stopPropagation();
    console.log('Start resize:', handle); // Debug
    interactionMode.value = 'resize';
    activeHandle.value = handle;
    startMouse.value = getMousePercent(e);
    startCrop.value = { ...cropBox.value };
    document.addEventListener('mousemove', handleMouseMove);
    document.addEventListener('mouseup', handleMouseUp);
};

const handleMouseMove = (e: MouseEvent) => {
    const mouse = getMousePercent(e);
    const deltaX = mouse.x - startMouse.value.x;
    const deltaY = mouse.y - startMouse.value.y;
    
    if (interactionMode.value === 'drag') {
        handleDragMove(deltaX, deltaY);
    } else if (interactionMode.value === 'resize') {
        handleResizeMove(deltaX, deltaY);
    }
};

const handleMouseUp = () => {
    interactionMode.value = 'none';
    activeHandle.value = null;
    document.removeEventListener('mousemove', handleMouseMove);
    document.removeEventListener('mouseup', handleMouseUp);
};

// Handle drag movement - move the entire crop box
const handleDragMove = (deltaX: number, deltaY: number) => {
    const { left, top, right, bottom } = startCrop.value;
    const width = 100 - left - right;
    const height = 100 - top - bottom;
    
    // Calculate new position, clamped to bounds
    let newLeft = Math.max(0, Math.min(left + deltaX, 100 - width));
    let newTop = Math.max(0, Math.min(top + deltaY, 100 - height));
    
    cropBox.value = {
        left: newLeft,
        top: newTop,
        right: 100 - newLeft - width,
        bottom: 100 - newTop - height,
    };
};

// Handle resize movement - resize from specific handle
const handleResizeMove = (deltaX: number, deltaY: number) => {
    const handle = activeHandle.value;
    if (!handle) return;
    
    let { left, top, right, bottom } = startCrop.value;
    const minSize = 10; // Minimum 10% size
    
    // Determine which edges to move based on handle
    const moveLeft = handle.includes('w');
    const moveRight = handle.includes('e');
    const moveTop = handle.includes('n');
    const moveBottom = handle.includes('s');
    
    // Apply deltas to appropriate edges
    if (moveLeft) {
        left = Math.max(0, Math.min(startCrop.value.left + deltaX, 100 - right - minSize));
    }
    if (moveRight) {
        right = Math.max(0, Math.min(startCrop.value.right - deltaX, 100 - left - minSize));
    }
    if (moveTop) {
        top = Math.max(0, Math.min(startCrop.value.top + deltaY, 100 - bottom - minSize));
    }
    if (moveBottom) {
        bottom = Math.max(0, Math.min(startCrop.value.bottom - deltaY, 100 - top - minSize));
    }
    
    // Apply aspect ratio constraint if needed
    if (selectedRatio.value) {
        const result = constrainToAspectRatio(left, top, right, bottom, handle);
        left = result.left;
        top = result.top;
        right = result.right;
        bottom = result.bottom;
    }
    
    cropBox.value = { left, top, right, bottom };
};

// Constrain crop box to aspect ratio
const constrainToAspectRatio = (left: number, top: number, right: number, bottom: number, handle: string) => {
    if (!selectedRatio.value) return { left, top, right, bottom };
    
    const imageRatio = imageNaturalWidth.value / imageNaturalHeight.value;
    const targetRatio = selectedRatio.value / imageRatio;
    
    const width = 100 - left - right;
    const height = 100 - top - bottom;
    const currentRatio = width / height;
    
    const moveLeft = handle.includes('w');
    const moveRight = handle.includes('e');
    const moveTop = handle.includes('n');
    const moveBottom = handle.includes('s');
    
    if (Math.abs(currentRatio - targetRatio) < 0.01) {
        return { left, top, right, bottom };
    }
    
    // Determine which dimension to adjust based on handle
    if (moveLeft || moveRight) {
        // Width changed, adjust height
        const newHeight = width / targetRatio;
        const heightDiff = height - newHeight;
        
        if (moveTop) {
            // Anchor bottom, adjust top
            top = Math.max(0, top + heightDiff);
        } else {
            // Anchor top, adjust bottom
            bottom = Math.max(0, bottom + heightDiff);
        }
    } else if (moveTop || moveBottom) {
        // Height changed, adjust width
        const newWidth = height * targetRatio;
        const widthDiff = width - newWidth;
        
        if (moveLeft) {
            // Anchor right, adjust left
            left = Math.max(0, left + widthDiff);
        } else {
            // Anchor left, adjust right
            right = Math.max(0, right + widthDiff);
        }
    }
    
    return { left, top, right, bottom };
};

// Save the cropped image
const saveCrop = () => {
    if (!imageRef.value || !canvasRef.value) return;
    const canvas = canvasRef.value;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    const { width, height } = outputDimensions.value;
    canvas.width = width;
    canvas.height = height;

    ctx.filter = filterStyle.value;
    ctx.drawImage(
        imageRef.value,
        cropPixels.value.x, cropPixels.value.y, cropPixels.value.width, cropPixels.value.height,
        0, 0, width, height
    );

    const mimeType = exportFormat.value === 'png' ? 'image/png' : exportFormat.value === 'webp' ? 'image/webp' : 'image/jpeg';
    const dataUrl = canvas.toDataURL(mimeType, exportQuality.value / 100);
    emit('save', dataUrl);
};

// Watchers
watch(() => props.imageUrl, () => { if (props.show && props.imageUrl) loadImage(); });
watch(() => props.show, (show) => { 
    if (show && props.imageUrl) { 
        loadImage(); 
    } 
});

// Cleanup
onUnmounted(() => {
    document.removeEventListener('mousemove', handleMouseMove);
    document.removeEventListener('mouseup', handleMouseUp);
});
</script>

<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80" @click="emit('close')">
            <div class="bg-gray-900 rounded-xl shadow-2xl w-full max-w-5xl h-[90vh] flex flex-col overflow-hidden" @click.stop>
                <!-- Header -->
                <div class="flex items-center justify-between px-4 py-2 bg-gray-800 border-b border-gray-700">
                    <div class="flex items-center gap-4">
                        <h2 class="text-sm font-medium text-white">Edit Image</h2>
                        <div class="flex items-center gap-1 text-xs text-gray-400">
                            <span>{{ imageNaturalWidth }}×{{ imageNaturalHeight }}</span>
                            <span class="text-gray-600">→</span>
                            <span class="text-blue-400">{{ outputDimensions.width }}×{{ outputDimensions.height }}</span>
                            <span v-if="sizeReduction > 0" class="text-green-400 ml-1">(-{{ sizeReduction }}%)</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="resetAll" class="px-2 py-1 text-xs text-gray-400 hover:text-white hover:bg-gray-700 rounded">
                            <ArrowPathIcon class="w-4 h-4 inline mr-1" aria-hidden="true" />Reset
                        </button>
                        <button @click="emit('close')" class="p-1 hover:bg-gray-700 rounded" aria-label="Close">
                            <XMarkIcon class="w-5 h-5 text-gray-400" aria-hidden="true" />
                        </button>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="flex-1 flex overflow-hidden">
                    <!-- Image Canvas Area -->
                    <div class="flex-1 flex items-center justify-center p-4 bg-gray-950 overflow-hidden">
                        <div v-if="isLoading" class="flex items-center justify-center">
                            <div class="animate-spin rounded-full h-8 w-8 border-2 border-blue-500 border-t-transparent"></div>
                        </div>
                        <div v-else ref="containerRef" class="relative max-w-full max-h-full select-none" style="display: inline-block;">
                            <!-- Base Image (dimmed) -->
                            <img
                                :src="imageUrl"
                                class="max-w-full max-h-[calc(90vh-120px)] block pointer-events-none"
                                :style="{ filter: filterStyle, opacity: 0.3 }"
                                alt="Original"
                                draggable="false"
                            />
                            
                            <!-- Crop Overlay Box -->
                            <div
                                class="absolute border-2 border-blue-500 shadow-lg cursor-move"
                                :style="{
                                    left: cropArea.x + '%',
                                    top: cropArea.y + '%',
                                    width: cropArea.width + '%',
                                    height: cropArea.height + '%',
                                    ...cropBackgroundStyle,
                                }"
                                @mousedown="startDrag"
                            >
                                <!-- Rule of thirds grid -->
                                <div class="absolute inset-0 pointer-events-none">
                                    <div class="absolute left-1/3 top-0 bottom-0 w-px bg-white/30"></div>
                                    <div class="absolute left-2/3 top-0 bottom-0 w-px bg-white/30"></div>
                                    <div class="absolute top-1/3 left-0 right-0 h-px bg-white/30"></div>
                                    <div class="absolute top-2/3 left-0 right-0 h-px bg-white/30"></div>
                                </div>
                            </div>
                            
                            <!-- Resize Handles - rendered OUTSIDE crop box for better z-index -->
                            <div class="absolute pointer-events-none" :style="{
                                left: cropArea.x + '%',
                                top: cropArea.y + '%',
                                width: cropArea.width + '%',
                                height: cropArea.height + '%',
                            }">
                                <!-- Corner handles -->
                                <div class="absolute w-6 h-6 bg-white border-2 border-blue-600 rounded-full shadow-lg cursor-nw-resize pointer-events-auto"
                                     style="top: -12px; left: -12px;"
                                     @mousedown.stop.prevent="startResize($event, 'nw')"></div>
                                <div class="absolute w-6 h-6 bg-white border-2 border-blue-600 rounded-full shadow-lg cursor-ne-resize pointer-events-auto"
                                     style="top: -12px; right: -12px;"
                                     @mousedown.stop.prevent="startResize($event, 'ne')"></div>
                                <div class="absolute w-6 h-6 bg-white border-2 border-blue-600 rounded-full shadow-lg cursor-sw-resize pointer-events-auto"
                                     style="bottom: -12px; left: -12px;"
                                     @mousedown.stop.prevent="startResize($event, 'sw')"></div>
                                <div class="absolute w-6 h-6 bg-white border-2 border-blue-600 rounded-full shadow-lg cursor-se-resize pointer-events-auto"
                                     style="bottom: -12px; right: -12px;"
                                     @mousedown.stop.prevent="startResize($event, 'se')"></div>
                                
                                <!-- Edge handles -->
                                <div class="absolute w-10 h-6 bg-white border-2 border-blue-600 rounded-full shadow-lg cursor-n-resize pointer-events-auto"
                                     style="top: -12px; left: 50%; transform: translateX(-50%);"
                                     @mousedown.stop.prevent="startResize($event, 'n')"></div>
                                <div class="absolute w-10 h-6 bg-white border-2 border-blue-600 rounded-full shadow-lg cursor-s-resize pointer-events-auto"
                                     style="bottom: -12px; left: 50%; transform: translateX(-50%);"
                                     @mousedown.stop.prevent="startResize($event, 's')"></div>
                                <div class="absolute w-6 h-10 bg-white border-2 border-blue-600 rounded-full shadow-lg cursor-w-resize pointer-events-auto"
                                     style="left: -12px; top: 50%; transform: translateY(-50%);"
                                     @mousedown.stop.prevent="startResize($event, 'w')"></div>
                                <div class="absolute w-6 h-10 bg-white border-2 border-blue-600 rounded-full shadow-lg cursor-e-resize pointer-events-auto"
                                     style="right: -12px; top: 50%; transform: translateY(-50%);"
                                     @mousedown.stop.prevent="startResize($event, 'e')"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Sidebar -->
                    <div class="w-64 bg-gray-800 border-l border-gray-700 flex flex-col">
                        <!-- Tabs -->
                        <div class="flex border-b border-gray-700">
                            <button @click="activeTab = 'crop'" :class="['flex-1 py-2 text-xs font-medium', activeTab === 'crop' ? 'text-blue-400 border-b-2 border-blue-400 bg-gray-700/50' : 'text-gray-400 hover:text-white']">Crop</button>
                            <button @click="activeTab = 'adjust'" :class="['flex-1 py-2 text-xs font-medium', activeTab === 'adjust' ? 'text-blue-400 border-b-2 border-blue-400 bg-gray-700/50' : 'text-gray-400 hover:text-white']">Adjust</button>
                            <button @click="activeTab = 'export'" :class="['flex-1 py-2 text-xs font-medium', activeTab === 'export' ? 'text-blue-400 border-b-2 border-blue-400 bg-gray-700/50' : 'text-gray-400 hover:text-white']">Export</button>
                        </div>

                        <!-- Tab Content -->
                        <div class="flex-1 overflow-y-auto p-3 space-y-4">
                            <!-- Crop Tab -->
                            <template v-if="activeTab === 'crop'">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-2">Aspect Ratio</label>
                                    <div v-if="isRatioLocked" class="mb-3 p-2 bg-blue-900/50 border border-blue-700 rounded-lg">
                                        <div class="flex items-center gap-2 text-blue-300 text-xs">
                                            <LockClosedIcon class="w-4 h-4" aria-hidden="true" />
                                            <span>{{ forcedRatioLabel }} required</span>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-1">
                                        <button v-for="r in aspectRatios" :key="r.label" 
                                            @click="!isRatioLocked && applyAspectRatio(r.value)"
                                            :disabled="isRatioLocked && r.value !== aspectRatio"
                                            :class="['px-2 py-1.5 text-xs rounded', 
                                                selectedRatio === r.value ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600',
                                                isRatioLocked && r.value !== aspectRatio ? 'opacity-40 cursor-not-allowed' : '']">
                                            {{ r.label }}
                                        </button>
                                    </div>
                                </div>
                                <button v-if="!isRatioLocked" @click="resetCrop" class="w-full py-1.5 text-xs text-gray-400 hover:text-white bg-gray-700 hover:bg-gray-600 rounded">
                                    Reset Crop
                                </button>
                            </template>

                            <!-- Adjust Tab -->
                            <template v-if="activeTab === 'adjust'">
                                <div>
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="text-gray-400">Brightness</span>
                                        <span class="text-gray-300">{{ adjustments.brightness }}%</span>
                                    </div>
                                    <input type="range" v-model.number="adjustments.brightness" min="0" max="200" class="w-full h-1.5 bg-gray-700 rounded-lg cursor-pointer accent-blue-500" />
                                </div>
                                <div>
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="text-gray-400">Contrast</span>
                                        <span class="text-gray-300">{{ adjustments.contrast }}%</span>
                                    </div>
                                    <input type="range" v-model.number="adjustments.contrast" min="0" max="200" class="w-full h-1.5 bg-gray-700 rounded-lg cursor-pointer accent-blue-500" />
                                </div>
                                <div>
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="text-gray-400">Saturation</span>
                                        <span class="text-gray-300">{{ adjustments.saturation }}%</span>
                                    </div>
                                    <input type="range" v-model.number="adjustments.saturation" min="0" max="200" class="w-full h-1.5 bg-gray-700 rounded-lg cursor-pointer accent-blue-500" />
                                </div>
                                <div>
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="text-gray-400">Blur</span>
                                        <span class="text-gray-300">{{ adjustments.blur }}px</span>
                                    </div>
                                    <input type="range" v-model.number="adjustments.blur" min="0" max="20" step="0.5" class="w-full h-1.5 bg-gray-700 rounded-lg cursor-pointer accent-blue-500" />
                                </div>
                                <button @click="resetAdjustments" class="w-full py-1.5 text-xs text-gray-400 hover:text-white bg-gray-700 hover:bg-gray-600 rounded">
                                    Reset Adjustments
                                </button>
                            </template>

                            <!-- Export Tab -->
                            <template v-if="activeTab === 'export'">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-2">Format</label>
                                    <div class="grid grid-cols-3 gap-1">
                                        <button v-for="fmt in ['jpeg', 'png', 'webp']" :key="fmt" @click="exportFormat = fmt as any"
                                            :class="['px-2 py-1.5 text-xs rounded uppercase', exportFormat === fmt ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600']">
                                            {{ fmt }}
                                        </button>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="text-gray-400">Quality</span>
                                        <span class="text-gray-300">{{ exportQuality }}%</span>
                                    </div>
                                    <input type="range" v-model.number="exportQuality" min="10" max="100" class="w-full h-1.5 bg-gray-700 rounded-lg cursor-pointer accent-blue-500" />
                                </div>
                                <div>
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="text-gray-400">Scale</span>
                                        <span class="text-gray-300">{{ exportScale }}%</span>
                                    </div>
                                    <input type="range" v-model.number="exportScale" min="10" max="200" class="w-full h-1.5 bg-gray-700 rounded-lg cursor-pointer accent-blue-500" />
                                    <p class="text-xs text-gray-500 mt-1">Output: {{ outputDimensions.width }}×{{ outputDimensions.height }}px</p>
                                </div>
                            </template>
                        </div>

                        <!-- Apply Button -->
                        <div class="p-3 border-t border-gray-700">
                            <button @click="saveCrop" class="w-full flex items-center justify-center gap-2 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                                <CheckIcon class="w-4 h-4" aria-hidden="true" />
                                Apply Changes
                            </button>
                        </div>
                    </div>
                </div>

                <canvas ref="canvasRef" class="hidden"></canvas>
            </div>
        </div>
    </Teleport>
</template>
