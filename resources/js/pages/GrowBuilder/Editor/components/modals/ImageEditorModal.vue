<script setup lang="ts">
import { ref, computed, watch, onUnmounted, nextTick } from 'vue';
import { XMarkIcon, ArrowPathIcon, CheckIcon, LockClosedIcon } from '@heroicons/vue/24/outline';

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

// ─── Refs ────────────────────────────────────────────────────────────────────
const canvasRef = ref<HTMLCanvasElement | null>(null);
const imgEl     = ref<HTMLImageElement  | null>(null); // rendered <img> — our coordinate origin

// ─── Image state ─────────────────────────────────────────────────────────────
const isLoading    = ref(true);
const naturalW     = ref(0);
const naturalH     = ref(0);
const loadedImg    = ref<HTMLImageElement | null>(null);

// ─── Crop state  (display pixels, relative to imgEl) ─────────────────────────
const crop = ref({ x: 0, y: 0, w: 0, h: 0 });

// ─── Drag state ───────────────────────────────────────────────────────────────
type Handle = 'move' | 'n' | 's' | 'e' | 'w' | 'nw' | 'ne' | 'sw' | 'se';
const dragging   = ref(false);
const dragHandle = ref<Handle>('move');
// Everything we need for the drag captured at mousedown — nothing changes mid-drag
const dragStart  = ref({ mx: 0, my: 0, x: 0, y: 0, w: 0, h: 0, imgW: 0, imgH: 0 });

// ─── UI state ─────────────────────────────────────────────────────────────────
const activeTab = ref<'crop' | 'adjust' | 'export'>('crop');
const adj = ref<ImageAdjustments>({ brightness: 100, contrast: 100, saturation: 100, blur: 0 });
const exportQuality = ref(90);
const exportScale   = ref(100);
const exportFormat  = ref<'jpeg' | 'png' | 'webp'>('jpeg');

// ─── Aspect ratio ─────────────────────────────────────────────────────────────
const aspectRatios = [
    { label: 'Free', value: null },
    { label: '1:1',  value: 1 },
    { label: '16:9', value: 16 / 9 },
    { label: '4:3',  value: 4 / 3 },
    { label: '3:2',  value: 3 / 2 },
];
const selectedRatio = ref<number | null>(props.aspectRatio ?? null);
const isRatioLocked = computed(() => !!(props.forceAspectRatio && props.aspectRatio));

// ─── Computed ─────────────────────────────────────────────────────────────────
const filterStyle = computed(() => {
    const { brightness, contrast, saturation, blur } = adj.value;
    return `brightness(${brightness}%) contrast(${contrast}%) saturate(${saturation}%) blur(${blur}px)`;
});

// Crop as CSS percentages of the rendered image (used to position overlays)
const cropPct = computed(() => {
    const iw = imgEl.value?.clientWidth  || 1;
    const ih = imgEl.value?.clientHeight || 1;
    return {
        left:   (crop.value.x / iw) * 100,
        top:    (crop.value.y / ih) * 100,
        width:  (crop.value.w / iw) * 100,
        height: (crop.value.h / ih) * 100,
    };
});

// Background that shows the full-brightness image through the crop window
const cropBgStyle = computed(() => {
    const iw = imgEl.value?.clientWidth  || 1;
    const ih = imgEl.value?.clientHeight || 1;
    const { x, y } = crop.value;
    return {
        backgroundImage:    `url(${props.imageUrl})`,
        backgroundSize:     `${iw}px ${ih}px`,
        backgroundPosition: `-${x}px -${y}px`,
        backgroundRepeat:   'no-repeat',
        filter:             filterStyle.value,
    };
});

const outputDimensions = computed(() => {
    const iw = imgEl.value?.clientWidth  || 1;
    const ih = imgEl.value?.clientHeight || 1;
    return {
        width:  Math.round(crop.value.w * (naturalW.value / iw) * (exportScale.value / 100)),
        height: Math.round(crop.value.h * (naturalH.value / ih) * (exportScale.value / 100)),
    };
});

const sizeReduction = computed(() => {
    const orig = naturalW.value * naturalH.value;
    const out  = outputDimensions.value.width * outputDimensions.value.height;
    return orig ? Math.round((1 - out / orig) * 100) : 0;
});

// ─── Load ─────────────────────────────────────────────────────────────────────
const loadImage = () => {
    isLoading.value = true;
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = async () => {
        naturalW.value  = img.naturalWidth;
        naturalH.value  = img.naturalHeight;
        loadedImg.value = img;
        isLoading.value = false;
        await nextTick();
        resetCrop();
    };
    img.onerror = () => { isLoading.value = false; };
    img.src = props.imageUrl;
};

// ─── Crop helpers ─────────────────────────────────────────────────────────────
const clampCrop = (c: typeof crop.value): typeof crop.value => {
    const iw  = imgEl.value?.clientWidth  ?? 0;
    const ih  = imgEl.value?.clientHeight ?? 0;
    const MIN = 20;
    let { x, y, w, h } = c;
    w = Math.max(MIN, Math.min(w, iw));
    h = Math.max(MIN, Math.min(h, ih));
    x = Math.max(0, Math.min(x, iw - w));
    y = Math.max(0, Math.min(y, ih - h));
    return { x, y, w, h };
};

const fitRatio = (ratio: number | null, c: typeof crop.value): typeof crop.value => {
    if (!ratio) return { ...c };
    const iw = imgEl.value?.clientWidth  || 1;
    const ih = imgEl.value?.clientHeight || 1;
    // Convert the desired aspect ratio into display-pixel space
    const displayRatio = ratio * (naturalH.value / naturalW.value) * (iw / ih);
    let { x, y, w, h } = c;
    if (w / h > displayRatio) {
        const nw = h * displayRatio;
        x += (w - nw) / 2;
        w  = nw;
    } else {
        const nh = w / displayRatio;
        y += (h - nh) / 2;
        h  = nh;
    }
    return { x, y, w, h };
};

const resetCrop = () => {
    const iw = imgEl.value?.clientWidth  ?? 0;
    const ih = imgEl.value?.clientHeight ?? 0;
    if (!iw || !ih) return;
    let c = { x: 0, y: 0, w: iw, h: ih };
    if (selectedRatio.value) c = fitRatio(selectedRatio.value, c) as typeof c;
    crop.value = clampCrop(c);
};

const applyAspectRatio = (ratio: number | null) => {
    selectedRatio.value = ratio;
    crop.value = clampCrop(fitRatio(ratio, crop.value) as typeof crop.value);
};

const resetAdjustments = () => {
    adj.value = { brightness: 100, contrast: 100, saturation: 100, blur: 0 };
};

const resetAll = () => {
    resetCrop();
    resetAdjustments();
    exportQuality.value = 90;
    exportScale.value   = 100;
};

// ─── Mouse handlers ───────────────────────────────────────────────────────────
const onMouseDown = (e: MouseEvent, handle: Handle) => {
    e.preventDefault();
    e.stopPropagation();
    dragging.value  = true;
    dragHandle.value = handle;
    // Snapshot everything we need — none of these values will change during the drag
    dragStart.value = {
        mx:   e.clientX,
        my:   e.clientY,
        imgW: imgEl.value?.clientWidth  ?? 0,
        imgH: imgEl.value?.clientHeight ?? 0,
        ...crop.value,
    };
    window.addEventListener('mousemove', onMouseMove);
    window.addEventListener('mouseup',   onMouseUp);
};

const onMouseMove = (e: MouseEvent) => {
    if (!dragging.value) return;

    // Delta in display pixels from where the drag started
    const dx = e.clientX - dragStart.value.mx;
    const dy = e.clientY - dragStart.value.my;

    const { x: sx, y: sy, w: sw, h: sh, imgW, imgH } = dragStart.value;
    const handle = dragHandle.value;
    const MIN = 20;

    let x = sx, y = sy, w = sw, h = sh;

    if (handle === 'move') {
        // Just translate — size stays the same
        x = Math.max(0, Math.min(sx + dx, imgW - sw));
        y = Math.max(0, Math.min(sy + dy, imgH - sh));
        crop.value = { x, y, w, h };
        return;
    }

    // Resize: each handle moves specific edges
    if (handle === 'e'  || handle === 'ne' || handle === 'se') w = sw + dx;
    if (handle === 's'  || handle === 'sw' || handle === 'se') h = sh + dy;
    if (handle === 'w'  || handle === 'nw' || handle === 'sw') { x = sx + dx; w = sw - dx; }
    if (handle === 'n'  || handle === 'nw' || handle === 'ne') { y = sy + dy; h = sh - dy; }

    // Clamp size to minimum (and pin the opposite edge)
    if (w < MIN) {
        w = MIN;
        if (handle.includes('w')) x = sx + sw - MIN;
    }
    if (h < MIN) {
        h = MIN;
        if (handle.includes('n')) y = sy + sh - MIN;
    }

    // Clamp to image bounds
    if (x < 0)        { w += x; x = 0; }
    if (y < 0)        { h += y; y = 0; }
    if (x + w > imgW) { w = imgW - x; }
    if (y + h > imgH) { h = imgH - y; }

    let next = { x, y, w, h };

    // Apply aspect ratio during resize
    if (selectedRatio.value) {
        next = clampCrop(fitRatio(selectedRatio.value, next) as typeof next);
    }

    crop.value = next;
};

const onMouseUp = () => {
    dragging.value = false;
    window.removeEventListener('mousemove', onMouseMove);
    window.removeEventListener('mouseup',   onMouseUp);
};

// ─── Save ─────────────────────────────────────────────────────────────────────
const saveCrop = () => {
    if (!loadedImg.value || !canvasRef.value || !imgEl.value) return;
    const ctx = canvasRef.value.getContext('2d');
    if (!ctx) return;

    const scaleX = naturalW.value / imgEl.value.clientWidth;
    const scaleY = naturalH.value / imgEl.value.clientHeight;
    const { width, height } = outputDimensions.value;

    canvasRef.value.width  = width;
    canvasRef.value.height = height;
    ctx.filter = filterStyle.value;
    ctx.drawImage(
        loadedImg.value,
        crop.value.x * scaleX, crop.value.y * scaleY,
        crop.value.w * scaleX, crop.value.h * scaleY,
        0, 0, width, height,
    );

    const mime    = exportFormat.value === 'png' ? 'image/png' : exportFormat.value === 'webp' ? 'image/webp' : 'image/jpeg';
    emit('save', canvasRef.value.toDataURL(mime, exportQuality.value / 100));
};

// ─── Watchers ─────────────────────────────────────────────────────────────────
watch(() => props.show,     (v) => { if (v) loadImage(); });
watch(() => props.imageUrl, ()  => { if (props.show) loadImage(); });
onUnmounted(() => {
    window.removeEventListener('mousemove', onMouseMove);
    window.removeEventListener('mouseup',   onMouseUp);
});
</script>

<template>
    <Teleport to="body">
        <div v-if="show"
             class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80"
             @click="emit('close')">
            <div class="bg-gray-900 rounded-xl shadow-2xl w-full max-w-5xl h-[90vh] flex flex-col overflow-hidden"
                 @click.stop>

                <!-- Header -->
                <div class="flex items-center justify-between px-4 py-2 bg-gray-800 border-b border-gray-700">
                    <div class="flex items-center gap-4">
                        <h2 class="text-sm font-medium text-white">Edit Image</h2>
                        <div class="flex items-center gap-1 text-xs text-gray-400">
                            <span>{{ naturalW }}×{{ naturalH }}</span>
                            <span class="text-gray-600">→</span>
                            <span class="text-blue-400">{{ outputDimensions.width }}×{{ outputDimensions.height }}</span>
                            <span v-if="sizeReduction > 0" class="text-green-400 ml-1">(-{{ sizeReduction }}%)</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="resetAll" class="px-2 py-1 text-xs text-gray-400 hover:text-white hover:bg-gray-700 rounded">
                            <ArrowPathIcon class="w-4 h-4 inline mr-1" />Reset
                        </button>
                        <button @click="emit('close')" class="p-1 hover:bg-gray-700 rounded">
                            <XMarkIcon class="w-5 h-5 text-gray-400" />
                        </button>
                    </div>
                </div>

                <!-- Main -->
                <div class="flex-1 flex overflow-hidden">

                    <!-- Image area -->
                    <div class="flex-1 flex items-center justify-center p-4 bg-gray-950 overflow-hidden">
                        <div v-if="isLoading" class="flex items-center justify-center">
                            <div class="animate-spin rounded-full h-8 w-8 border-2 border-blue-500 border-t-transparent" />
                        </div>

                        <div v-else class="relative inline-block select-none" style="line-height:0">
                            <!-- Base image (dimmed). This element IS our coordinate space. -->
                            <img ref="imgEl"
                                 :src="imageUrl"
                                 class="max-w-full max-h-[calc(90vh-120px)] block pointer-events-none"
                                 :style="{ filter: filterStyle, opacity: 0.35 }"
                                 draggable="false"
                                 alt="" />

                            <!-- Dark overlays around crop area -->
                            <div class="absolute inset-0 pointer-events-none">
                                <!-- top strip -->
                                <div class="absolute bg-black/50"
                                     :style="{ left:0, top:0, right:0, height: cropPct.top+'%' }" />
                                <!-- bottom strip -->
                                <div class="absolute bg-black/50"
                                     :style="{ left:0, bottom:0, right:0, top: (cropPct.top+cropPct.height)+'%' }" />
                                <!-- left strip -->
                                <div class="absolute bg-black/50"
                                     :style="{ top: cropPct.top+'%', height: cropPct.height+'%', left:0, width: cropPct.left+'%' }" />
                                <!-- right strip -->
                                <div class="absolute bg-black/50"
                                     :style="{ top: cropPct.top+'%', height: cropPct.height+'%', left: (cropPct.left+cropPct.width)+'%', right:0 }" />
                            </div>

                            <!-- Bright crop window + drag to move -->
                            <div class="absolute border-2 border-blue-400 cursor-move"
                                 :style="{
                                     left:   cropPct.left   + '%',
                                     top:    cropPct.top    + '%',
                                     width:  cropPct.width  + '%',
                                     height: cropPct.height + '%',
                                     ...cropBgStyle,
                                 }"
                                 @mousedown="onMouseDown($event, 'move')">
                                <!-- Rule of thirds -->
                                <div class="absolute inset-0 pointer-events-none overflow-hidden">
                                    <div class="absolute left-1/3 top-0 bottom-0 w-px bg-white/25" />
                                    <div class="absolute left-2/3 top-0 bottom-0 w-px bg-white/25" />
                                    <div class="absolute top-1/3 left-0 right-0 h-px bg-white/25" />
                                    <div class="absolute top-2/3 left-0 right-0 h-px bg-white/25" />
                                </div>
                            </div>

                            <!-- Handles layer (same size/position as crop window but pointer-events pass-through except handles) -->
                            <div class="absolute pointer-events-none"
                                 :style="{
                                     left:   cropPct.left   + '%',
                                     top:    cropPct.top    + '%',
                                     width:  cropPct.width  + '%',
                                     height: cropPct.height + '%',
                                 }">
                                <!-- Corner handles -->
                                <div class="absolute w-4 h-4 bg-white border-2 border-blue-500 rounded-full shadow pointer-events-auto cursor-nw-resize"
                                     style="top:-8px;left:-8px" @mousedown.stop.prevent="onMouseDown($event,'nw')" />
                                <div class="absolute w-4 h-4 bg-white border-2 border-blue-500 rounded-full shadow pointer-events-auto cursor-ne-resize"
                                     style="top:-8px;right:-8px" @mousedown.stop.prevent="onMouseDown($event,'ne')" />
                                <div class="absolute w-4 h-4 bg-white border-2 border-blue-500 rounded-full shadow pointer-events-auto cursor-sw-resize"
                                     style="bottom:-8px;left:-8px" @mousedown.stop.prevent="onMouseDown($event,'sw')" />
                                <div class="absolute w-4 h-4 bg-white border-2 border-blue-500 rounded-full shadow pointer-events-auto cursor-se-resize"
                                     style="bottom:-8px;right:-8px" @mousedown.stop.prevent="onMouseDown($event,'se')" />

                                <!-- Edge handles -->
                                <div class="absolute h-3 bg-white border-2 border-blue-500 rounded-full shadow pointer-events-auto cursor-n-resize"
                                     style="top:-6px;left:50%;transform:translateX(-50%);width:32px" @mousedown.stop.prevent="onMouseDown($event,'n')" />
                                <div class="absolute h-3 bg-white border-2 border-blue-500 rounded-full shadow pointer-events-auto cursor-s-resize"
                                     style="bottom:-6px;left:50%;transform:translateX(-50%);width:32px" @mousedown.stop.prevent="onMouseDown($event,'s')" />
                                <div class="absolute w-3 bg-white border-2 border-blue-500 rounded-full shadow pointer-events-auto cursor-w-resize"
                                     style="left:-6px;top:50%;transform:translateY(-50%);height:32px" @mousedown.stop.prevent="onMouseDown($event,'w')" />
                                <div class="absolute w-3 bg-white border-2 border-blue-500 rounded-full shadow pointer-events-auto cursor-e-resize"
                                     style="right:-6px;top:50%;transform:translateY(-50%);height:32px" @mousedown.stop.prevent="onMouseDown($event,'e')" />
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="w-64 bg-gray-800 border-l border-gray-700 flex flex-col">
                        <div class="flex border-b border-gray-700">
                            <button v-for="tab in (['crop','adjust','export'] as const)" :key="tab"
                                    @click="activeTab = tab"
                                    :class="['flex-1 py-2 text-xs font-medium capitalize',
                                        activeTab === tab
                                            ? 'text-blue-400 border-b-2 border-blue-400 bg-gray-700/50'
                                            : 'text-gray-400 hover:text-white']">
                                {{ tab }}
                            </button>
                        </div>

                        <div class="flex-1 overflow-y-auto p-3 space-y-4">

                            <!-- Crop tab -->
                            <template v-if="activeTab === 'crop'">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-2">Aspect Ratio</label>
                                    <div v-if="isRatioLocked" class="mb-3 p-2 bg-blue-900/50 border border-blue-700 rounded-lg flex items-center gap-2 text-blue-300 text-xs">
                                        <LockClosedIcon class="w-4 h-4" /> Locked
                                    </div>
                                    <div class="grid grid-cols-3 gap-1">
                                        <button v-for="r in aspectRatios" :key="r.label"
                                                @click="!isRatioLocked && applyAspectRatio(r.value)"
                                                :class="['px-2 py-1.5 text-xs rounded',
                                                    selectedRatio === r.value ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600',
                                                    isRatioLocked && r.value !== aspectRatio ? 'opacity-40 cursor-not-allowed' : '']">
                                            {{ r.label }}
                                        </button>
                                    </div>
                                </div>
                                <button v-if="!isRatioLocked" @click="resetCrop"
                                        class="w-full py-1.5 text-xs text-gray-400 hover:text-white bg-gray-700 hover:bg-gray-600 rounded">
                                    Reset Crop
                                </button>
                            </template>

                            <!-- Adjust tab -->
                            <template v-if="activeTab === 'adjust'">
                                <div>
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="text-gray-400">Brightness</span>
                                        <span class="text-gray-300">{{ adj.brightness }}%</span>
                                    </div>
                                    <input type="range" v-model.number="adj.brightness" min="0" max="200" class="w-full h-1.5 bg-gray-700 rounded-lg cursor-pointer accent-blue-500" />
                                </div>
                                <div>
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="text-gray-400">Contrast</span>
                                        <span class="text-gray-300">{{ adj.contrast }}%</span>
                                    </div>
                                    <input type="range" v-model.number="adj.contrast" min="0" max="200" class="w-full h-1.5 bg-gray-700 rounded-lg cursor-pointer accent-blue-500" />
                                </div>
                                <div>
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="text-gray-400">Saturation</span>
                                        <span class="text-gray-300">{{ adj.saturation }}%</span>
                                    </div>
                                    <input type="range" v-model.number="adj.saturation" min="0" max="200" class="w-full h-1.5 bg-gray-700 rounded-lg cursor-pointer accent-blue-500" />
                                </div>
                                <div>
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="text-gray-400">Blur</span>
                                        <span class="text-gray-300">{{ adj.blur }}px</span>
                                    </div>
                                    <input type="range" v-model.number="adj.blur" min="0" max="20" step="0.5" class="w-full h-1.5 bg-gray-700 rounded-lg cursor-pointer accent-blue-500" />
                                </div>
                                <button @click="resetAdjustments"
                                        class="w-full py-1.5 text-xs text-gray-400 hover:text-white bg-gray-700 hover:bg-gray-600 rounded">
                                    Reset Adjustments
                                </button>
                            </template>

                            <!-- Export tab -->
                            <template v-if="activeTab === 'export'">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-2">Format</label>
                                    <div class="grid grid-cols-3 gap-1">
                                        <button v-for="fmt in (['jpeg','png','webp'] as const)" :key="fmt"
                                                @click="exportFormat = fmt"
                                                :class="['px-2 py-1.5 text-xs rounded uppercase',
                                                    exportFormat === fmt ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600']">
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

                        <div class="p-3 border-t border-gray-700">
                            <button @click="saveCrop"
                                    class="w-full flex items-center justify-center gap-2 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                                <CheckIcon class="w-4 h-4" /> Apply Changes
                            </button>
                        </div>
                    </div>
                </div>

                <canvas ref="canvasRef" class="hidden" />
            </div>
        </div>
    </Teleport>
</template>