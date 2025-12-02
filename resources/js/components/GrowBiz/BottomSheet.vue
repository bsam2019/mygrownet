<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
    modelValue: boolean;
    title?: string;
    snapPoints?: number[];
}>();

const emit = defineEmits<{
    'update:modelValue': [value: boolean];
}>();

const sheetRef = ref<HTMLElement | null>(null);
const currentHeight = ref(0);
const isDragging = ref(false);
const startY = ref(0);
const startHeight = ref(0);

const DEFAULT_SNAP_POINTS = [0.4, 0.7, 0.95]; // 40%, 70%, 95% of screen
const snapPoints = props.snapPoints || DEFAULT_SNAP_POINTS;

const close = () => {
    emit('update:modelValue', false);
};

const handleDragStart = (e: TouchEvent | MouseEvent) => {
    isDragging.value = true;
    startY.value = 'touches' in e ? e.touches[0].clientY : e.clientY;
    startHeight.value = currentHeight.value;
};

const handleDrag = (e: TouchEvent | MouseEvent) => {
    if (!isDragging.value) return;
    
    const clientY = 'touches' in e ? e.touches[0].clientY : e.clientY;
    const diff = startY.value - clientY;
    const windowHeight = window.innerHeight;
    const newHeight = Math.max(0, Math.min(windowHeight * 0.95, startHeight.value + diff));
    
    currentHeight.value = newHeight;
};

const handleDragEnd = () => {
    if (!isDragging.value) return;
    isDragging.value = false;
    
    const windowHeight = window.innerHeight;
    const currentRatio = currentHeight.value / windowHeight;
    
    // Find nearest snap point
    let nearestSnap = snapPoints[0];
    let minDiff = Math.abs(currentRatio - snapPoints[0]);
    
    for (const snap of snapPoints) {
        const diff = Math.abs(currentRatio - snap);
        if (diff < minDiff) {
            minDiff = diff;
            nearestSnap = snap;
        }
    }
    
    // Close if dragged below minimum
    if (currentRatio < snapPoints[0] * 0.5) {
        close();
    } else {
        currentHeight.value = windowHeight * nearestSnap;
    }
};

watch(() => props.modelValue, (isOpen) => {
    if (isOpen) {
        currentHeight.value = window.innerHeight * snapPoints[0];
        document.body.style.overflow = 'hidden';
    } else {
        currentHeight.value = 0;
        document.body.style.overflow = '';
    }
});

onMounted(() => {
    document.addEventListener('mousemove', handleDrag);
    document.addEventListener('mouseup', handleDragEnd);
    document.addEventListener('touchmove', handleDrag, { passive: true });
    document.addEventListener('touchend', handleDragEnd);
});

onUnmounted(() => {
    document.removeEventListener('mousemove', handleDrag);
    document.removeEventListener('mouseup', handleDragEnd);
    document.removeEventListener('touchmove', handleDrag);
    document.removeEventListener('touchend', handleDragEnd);
    document.body.style.overflow = '';
});
</script>

<template>
    <Teleport to="body">
        <!-- Backdrop -->
        <Transition name="fade">
            <div 
                v-if="modelValue"
                class="fixed inset-0 bg-black/50 z-50"
                @click="close"
            />
        </Transition>

        <!-- Sheet -->
        <Transition name="sheet">
            <div 
                v-if="modelValue"
                ref="sheetRef"
                class="fixed bottom-0 left-0 right-0 w-full bg-white rounded-t-3xl z-50 flex flex-col safe-area-bottom"
                :style="{ height: `${currentHeight}px` }"
                :class="{ 'transition-height': !isDragging }"
            >
                <!-- Drag handle -->
                <div 
                    class="flex-shrink-0 pt-3 pb-2 cursor-grab active:cursor-grabbing touch-none"
                    @mousedown="handleDragStart"
                    @touchstart="handleDragStart"
                >
                    <div class="w-10 h-1 bg-gray-300 rounded-full mx-auto" />
                </div>

                <!-- Header -->
                <div v-if="title" class="flex items-center justify-between px-4 pb-3 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
                    <button 
                        @click="close"
                        class="p-2 -mr-2 rounded-full hover:bg-gray-100 active:bg-gray-200 transition-colors"
                        aria-label="Close"
                    >
                        <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                    </button>
                </div>

                <!-- Content -->
                <div class="flex-1 overflow-y-auto overscroll-contain">
                    <slot />
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.safe-area-bottom {
    padding-bottom: env(safe-area-inset-bottom);
}

.transition-height {
    transition: height 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.sheet-enter-active,
.sheet-leave-active {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.sheet-enter-from,
.sheet-leave-to {
    transform: translateY(100%);
}

.sheet-enter-to,
.sheet-leave-from {
    transform: translateY(0);
}
</style>
