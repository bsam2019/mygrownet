<script setup lang="ts">
/**
 * Image Editor Modal
 * Crop, resize, adjust brightness/contrast/saturation, and export with quality settings
 */
import { ref, computed, watch } from 'vue';
import {
    XMarkIcon,
    ArrowPathIcon,
    CheckIcon,
    SunIcon,
    AdjustmentsHorizontalIcon,
    PhotoIcon,
} from '@heroicons/vue/24/outline';

interface CropArea {
    x: number;
    y: number;
    width: number;
    height: number;
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
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'save', dataUrl: string): void;
}>();

const canvasRef = ref<HTMLCanvasElement | null>(null);
const previewCanvasRef = ref<HTMLCanvasElement | null>(null);
const imageRef = ref<HTMLImageElement | null>(null);
const containerRef = ref<HTMLDivElement | null>(null);

const isLoading = ref(true);
const isDragging = ref(false);
const isResizing = ref(false);
const resizeHandle = ref<string | null>(null);
const activeTab = ref<'crop' | 'adjust' | 'export'>('crop');

const imageNaturalWidth = ref(0);
const imageNaturalHeight = ref(0);

// Crop area in percentage (0-100)
const cropArea = ref<CropArea>({ x: 0, y: 0, width: 100, height: 100 });
const dragStart = ref({ x: 0, y: 0 });
const cropStart = ref<CropArea>({ x: 0, y: 0, width: 0, height: 0 });

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

// Aspect ratios
const aspectRatios = [
    { label: 'Free', value: null },
    { label: '1:1', value: 1 },
    { label: '16:9', value: 16/9 },
    { label: '4:3', value: 4/3 },
    { label: '3:2', value: 3/2 },
];
const selectedRatio = ref<number | null>(props.aspectRatio || null);

// Computed values
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
        if (selectedRatio.value) applyAspectRatio(selectedRatio.value);
    };
    img.onerror = () => { isLoading.value = false; };
    img.src = props.imageUrl;
};

const applyAspectRatio = (ratio: number | null) => {
    selectedRatio.value = ratio;
    if (!ratio) return;
    const imageRatio = imageNaturalWidth.value / imageNaturalHeight.value;
    if (ratio > imageRatio) {
        const newHeight = (cropArea.value.width / ratio) * (imageNaturalWidth.value / imageNaturalHeight.value);
        cropArea.value.height = Math.min(newHeight, 100 - cropArea.value.y);
    } else {
        const newWidth = (cropArea.value.height * ratio) * (imageNaturalHeight.value / imageNaturalWidth.value);
        cropArea.value.width = Math.min(newWidth, 100 - cropArea.value.x);
    }
};

const resetCrop = () => {
    cropArea.value = { x: 0, y: 0, width: 100, height: 100 };
    selectedRatio.value = null;
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

// Drag handlers
const startDrag = (e: MouseEvent) => {
    if (isResizing.value) return;
    isDragging.value = true;
    dragStart.value = { x: e.clientX, y: e.clientY };
    cropStart.value = { ...cropArea.value };
    document.addEventListener('mousemove', handleDrag);
    document.addEventListener('mouseup', stopDrag);
};

const handleDrag = (e: MouseEvent) => {
    if (!isDragging.value || !containerRef.value) return;
    const rect = containerRef.value.getBoundingClientRect();
    const deltaX = ((e.clientX - dragStart.value.x) / rect.width) * 100;
    const deltaY = ((e.clientY - dragStart.value.y) / rect.height) * 100;
    cropArea.value.x = Math.max(0, Math.min(cropStart.value.x + deltaX, 100 - cropArea.value.width));
    cropArea.value.y = Math.max(0, Math.min(cropStart.value.y + deltaY, 100 - cropArea.value.height));
};

const stopDrag = () => {
    isDragging.value = false;
    document.removeEventListener('mousemove', handleDrag);
    document.removeEventListener('mouseup', stopDrag);
};

const startResize = (e: MouseEvent, handle: string) => {
    e.stopPropagation();
    isResizing.value = true;
    resizeHandle.value = handle;
    dragStart.value = { x: e.clientX, y: e.clientY };
    cropStart.value = { ...cropArea.value };
    document.addEventListener('mousemove', handleResize);
    document.addEventListener('mouseup', stopResize);
};

const handleResize = (e: MouseEvent) => {
    if (!isResizing.value || !containerRef.value || !resizeHandle.value) return;
    const rect = containerRef.value.getBoundingClientRect();
    const deltaX = ((e.clientX - dragStart.value.x) / rect.width) * 100;
    const deltaY = ((e.clientY - dragStart.value.y) / rect.height) * 100;
    const handle = resizeHandle.value;
    let { x, y, width, height } = cropStart.value;

    if (handle.includes('e')) width = Math.max(10, Math.min(cropStart.value.width + deltaX, 100 - x));
    if (handle.includes('w')) { const newX = Math.max(0, cropStart.value.x + deltaX); width = cropStart.value.width - (newX - cropStart.value.x); if (width >= 10) x = newX; }
    if (handle.includes('s')) height = Math.max(10, Math.min(cropStart.value.height + deltaY, 100 - y));
    if (handle.includes('n')) { const newY = Math.max(0, cropStart.value.y + deltaY); height = cropStart.value.height - (newY - cropStart.value.y); if (height >= 10) y = newY; }

    if (selectedRatio.value) {
        const imageRatio = imageNaturalWidth.value / imageNaturalHeight.value;
        if (handle.includes('e') || handle.includes('w')) height = (width / selectedRatio.value) * imageRatio;
        else width = (height * selectedRatio.value) / imageRatio;
    }
    cropArea.value = { x, y, width, height };
};

const stopResize = () => {
    isResizing.value = false;
    resizeHandle.value = null;
    document.removeEventListener('mousemove', handleResize);
    document.removeEventListener('mouseup', stopResize);
};

// Save with all adjustments
const saveCrop = () => {
    if (!imageRef.value || !canvasRef.value) return;
    const canvas = canvasRef.value;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    const { width, height } = outputDimensions.value;
    canvas.width = width;
    canvas.height = height;

    // Apply filters
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

watch(() => props.imageUrl, () => { if (props.show && props.imageUrl) loadImage(); });
watch(() => props.show, (show) => { if (show && props.imageUrl) { loadImage(); resetAll(); } });
</script>

<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80" @click="emit('close')">
            <div class="bg-gray-900 rounded-xl shadow-2xl w-full max-w-5xl h-[90vh] flex flex-col overflow-hidden" @click.stop>
                <!-- Compact Header -->
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
                        <button @click="resetAll" class="px-2 py-1 text-xs text-gray-400 hover:text-white hover:bg-gray-700 rounded transition-colors">
                            <ArrowPathIcon class="w-4 h-4 inline mr-1" />Reset All
                        </button>
                        <button @click="emit('close')" class="p-1 hover:bg-gray-700 rounded" aria-label="Close">
                            <XMarkIcon class="w-5 h-5 text-gray-400" />
                        </button>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="flex-1 flex overflow-hidden">
                    <!-- Image Canvas Area (Large) -->
                    <div class="flex-1 flex items-center justify-center p-4 bg-gray-950 overflow-hidden">
                        <div v-if="isLoading" class="flex items-center justify-center">
                            <div class="animate-spin rounded-full h-8 w-8 border-2 border-blue-500 border-t-transparent"></div>
                        </div>
                        <div v-else ref="containerRef" class="relative max-w-full max-h-full" style="display: inline-block;">
                            <!-- Base Image with Adjustments -->
                            <img
                                :src="imageUrl"
                                class="max-w-full max-h-[calc(90vh-120px)] block"
                                :style="{ filter: filterStyle, opacity: 0.4 }"
                                alt="Original"
                            />
                            <!-- Crop Overlay -->
                            <div
                                class="absolute cursor-move border-2 border-blue-500 shadow-lg"
                                :style="{
                                    left: cropArea.x + '%', top: cropArea.y + '%',
                                    width: cropArea.width + '%', height: cropArea.height + '%',
                                    backgroundImage: `url(${imageUrl})`,
                                    backgroundSize: `${100 / cropArea.width * 100}% ${100 / cropArea.height * 100}%`,
                                    backgroundPosition: `${-cropArea.x / cropArea.width * 100}% ${-cropArea.y / cropArea.height * 100}%`,
                                    filter: filterStyle,
                                }"
                                @mousedown="startDrag"
                            >
                                <!-- Corner Handles -->
                                <div class="absolute -top-1.5 -left-1.5 w-3 h-3 bg-blue-500 border-2 border-white cursor-nw-resize rounded-sm" @mousedown="startResize($event, 'nw')"></div>
                                <div class="absolute -top-1.5 -right-1.5 w-3 h-3 bg-blue-500 border-2 border-white cursor-ne-resize rounded-sm" @mousedown="startResize($event, 'ne')"></div>
                                <div class="absolute -bottom-1.5 -left-1.5 w-3 h-3 bg-blue-500 border-2 border-white cursor-sw-resize rounded-sm" @mousedown="startResize($event, 'sw')"></div>
                                <div class="absolute -bottom-1.5 -right-1.5 w-3 h-3 bg-blue-500 border-2 border-white cursor-se-resize rounded-sm" @mousedown="startResize($event, 'se')"></div>
                                <!-- Edge Handles -->
                                <div class="absolute top-1/2 -left-1.5 w-3 h-6 -translate-y-1/2 bg-blue-500 border-2 border-white cursor-w-resize rounded-sm" @mousedown="startResize($event, 'w')"></div>
                                <div class="absolute top-1/2 -right-1.5 w-3 h-6 -translate-y-1/2 bg-blue-500 border-2 border-white cursor-e-resize rounded-sm" @mousedown="startResize($event, 'e')"></div>
                                <div class="absolute -top-1.5 left-1/2 w-6 h-3 -translate-x-1/2 bg-blue-500 border-2 border-white cursor-n-resize rounded-sm" @mousedown="startResize($event, 'n')"></div>
                                <div class="absolute -bottom-1.5 left-1/2 w-6 h-3 -translate-x-1/2 bg-blue-500 border-2 border-white cursor-s-resize rounded-sm" @mousedown="startResize($event, 's')"></div>
                                <!-- Grid -->
                                <div class="absolute inset-0 pointer-events-none">
                                    <div class="absolute left-1/3 top-0 bottom-0 w-px bg-white/20"></div>
                                    <div class="absolute left-2/3 top-0 bottom-0 w-px bg-white/20"></div>
                                    <div class="absolute top-1/3 left-0 right-0 h-px bg-white/20"></div>
                                    <div class="absolute top-2/3 left-0 right-0 h-px bg-white/20"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Sidebar (Compact Controls) -->
                    <div class="w-64 bg-gray-800 border-l border-gray-700 flex flex-col">
                        <!-- Tabs -->
                        <div class="flex border-b border-gray-700">
                            <button @click="activeTab = 'crop'" :class="['flex-1 py-2 text-xs font-medium transition-colors', activeTab === 'crop' ? 'text-blue-400 border-b-2 border-blue-400 bg-gray-700/50' : 'text-gray-400 hover:text-white']">Crop</button>
                            <button @click="activeTab = 'adjust'" :class="['flex-1 py-2 text-xs font-medium transition-colors', activeTab === 'adjust' ? 'text-blue-400 border-b-2 border-blue-400 bg-gray-700/50' : 'text-gray-400 hover:text-white']">Adjust</button>
                            <button @click="activeTab = 'export'" :class="['flex-1 py-2 text-xs font-medium transition-colors', activeTab === 'export' ? 'text-blue-400 border-b-2 border-blue-400 bg-gray-700/50' : 'text-gray-400 hover:text-white']">Export</button>
                        </div>

                        <!-- Tab Content -->
                        <div class="flex-1 overflow-y-auto p-3 space-y-4">
                            <!-- Crop Tab -->
                            <template v-if="activeTab === 'crop'">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-2">Aspect Ratio</label>
                                    <div class="grid grid-cols-3 gap-1">
                                        <button v-for="r in aspectRatios" :key="r.label" @click="applyAspectRatio(r.value)"
                                            :class="['px-2 py-1.5 text-xs rounded transition-colors', selectedRatio === r.value ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600']">
                                            {{ r.label }}
                                        </button>
                                    </div>
                                </div>
                                <button @click="resetCrop" class="w-full py-1.5 text-xs text-gray-400 hover:text-white bg-gray-700 hover:bg-gray-600 rounded transition-colors">
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
                                    <input type="range" v-model.number="adjustments.brightness" min="0" max="200" class="w-full h-1.5 bg-gray-700 rounded-lg appearance-none cursor-pointer accent-blue-500" />
                                </div>
                                <div>
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="text-gray-400">Contrast</span>
                                        <span class="text-gray-300">{{ adjustments.contrast }}%</span>
                                    </div>
                                    <input type="range" v-model.number="adjustments.contrast" min="0" max="200" class="w-full h-1.5 bg-gray-700 rounded-lg appearance-none cursor-pointer accent-blue-500" />
                                </div>
                                <div>
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="text-gray-400">Saturation</span>
                                        <span class="text-gray-300">{{ adjustments.saturation }}%</span>
                                    </div>
                                    <input type="range" v-model.number="adjustments.saturation" min="0" max="200" class="w-full h-1.5 bg-gray-700 rounded-lg appearance-none cursor-pointer accent-blue-500" />
                                </div>
                                <div>
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="text-gray-400">Blur</span>
                                        <span class="text-gray-300">{{ adjustments.blur }}px</span>
                                    </div>
                                    <input type="range" v-model.number="adjustments.blur" min="0" max="20" step="0.5" class="w-full h-1.5 bg-gray-700 rounded-lg appearance-none cursor-pointer accent-blue-500" />
                                </div>
                                <button @click="resetAdjustments" class="w-full py-1.5 text-xs text-gray-400 hover:text-white bg-gray-700 hover:bg-gray-600 rounded transition-colors">
                                    Reset Adjustments
                                </button>
                            </template>

                            <!-- Export Tab -->
                            <template v-if="activeTab === 'export'">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-2">Format</label>
                                    <div class="grid grid-cols-3 gap-1">
                                        <button v-for="fmt in ['jpeg', 'png', 'webp']" :key="fmt" @click="exportFormat = fmt as any"
                                            :class="['px-2 py-1.5 text-xs rounded uppercase transition-colors', exportFormat === fmt ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600']">
                                            {{ fmt }}
                                        </button>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="text-gray-400">Quality</span>
                                        <span class="text-gray-300">{{ exportQuality }}%</span>
                                    </div>
                                    <input type="range" v-model.number="exportQuality" min="10" max="100" class="w-full h-1.5 bg-gray-700 rounded-lg appearance-none cursor-pointer accent-blue-500" />
                                </div>
                                <div>
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="text-gray-400">Scale</span>
                                        <span class="text-gray-300">{{ exportScale }}%</span>
                                    </div>
                                    <input type="range" v-model.number="exportScale" min="10" max="200" class="w-full h-1.5 bg-gray-700 rounded-lg appearance-none cursor-pointer accent-blue-500" />
                                    <p class="text-xs text-gray-500 mt-1">Output: {{ outputDimensions.width }}×{{ outputDimensions.height }}px</p>
                                </div>
                            </template>
                        </div>

                        <!-- Apply Button -->
                        <div class="p-3 border-t border-gray-700">
                            <button @click="saveCrop" class="w-full flex items-center justify-center gap-2 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                <CheckIcon class="w-4 h-4" />
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
