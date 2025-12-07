<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { useHaptics } from '@/composables/useHaptics';

interface Props {
    modelValue: boolean;
    title?: string;
    /** Height as percentage of viewport (0-1) */
    maxHeight?: number;
    /** Allow dismissing by dragging down */
    dismissible?: boolean;
    /** Prevent dismissal when form inputs are focused */
    preventDismissOnForm?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    title: '',
    maxHeight: 0.9,
    dismissible: true,
    preventDismissOnForm: true,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void;
    (e: 'close'): void;
}>();

const { light, medium } = useHaptics();

// Refs
const sheetRef = ref<HTMLElement | null>(null);
const contentRef = ref<HTMLElement | null>(null);
const isAnimating = ref(false);
const dragY = ref(0);
const isDragging = ref(false);
const startY = ref(0);
const sheetHeight = ref(0);

// Computed
const isOpen = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const translateY = computed(() => {
    if (!isOpen.value) return '100%';
    if (isDragging.value) return `${Math.max(0, dragY.value)}px`;
    return '0';
});

const backdropOpacity = computed(() => {
    if (!isOpen.value) return 0;
    if (isDragging.value && sheetHeight.value > 0) {
        const progress = 1 - (dragY.value / sheetHeight.value);
        return Math.max(0, Math.min(0.5, progress * 0.5));
    }
    return 0.5;
});

// Check if form input is focused
const isFormFocused = (): boolean => {
    if (!props.preventDismissOnForm) return false;
    const activeEl = document.activeElement;
    if (!activeEl) return false;
    return ['INPUT', 'TEXTAREA', 'SELECT'].includes(activeEl.tagName);
};

// Drag handlers
const handleDragStart = (e: TouchEvent) => {
    if (!props.dismissible || isFormFocused()) return;
    
    const touch = e.touches[0];
    startY.value = touch.clientY;
    isDragging.value = true;
    dragY.value = 0;
    
    if (sheetRef.value) {
        sheetHeight.value = sheetRef.value.offsetHeight;
    }
};

const handleDragMove = (e: TouchEvent) => {
    if (!isDragging.value) return;
    
    const touch = e.touches[0];
    const diff = touch.clientY - startY.value;
    
    // Only allow dragging down
    if (diff > 0) {
        dragY.value = diff;
        e.preventDefault();
    }
};

const handleDragEnd = () => {
    if (!isDragging.value) return;
    
    isDragging.value = false;
    
    // If dragged more than 50% of height, close
    if (dragY.value > sheetHeight.value * 0.5) {
        medium();
        close();
    } else {
        // Snap back
        dragY.value = 0;
    }
};

// Open/close handlers
const open = () => {
    isOpen.value = true;
    light();
    document.body.style.overflow = 'hidden';
};

const close = () => {
    isAnimating.value = true;
    isOpen.value = false;
    emit('close');
    document.body.style.overflow = '';
    
    setTimeout(() => {
        isAnimating.value = false;
    }, 300);
};

const handleBackdropClick = () => {
    if (props.dismissible && !isFormFocused()) {
        close();
    }
};

// Handle escape key
const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && isOpen.value && props.dismissible) {
        close();
    }
};

// Watch for external changes
watch(() => props.modelValue, (newVal) => {
    if (newVal) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});

onMounted(() => {
    document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
    document.body.style.overflow = '';
});
</script>

<template>
    <Teleport to="body">
        <Transition name="sheet">
            <div
                v-if="isOpen || isAnimating"
                class="fixed inset-0 z-[100] lg:hidden"
            >
                <!-- Backdrop -->
                <div
                    class="absolute inset-0 bg-black transition-opacity duration-300"
                    :style="{ opacity: backdropOpacity }"
                    @click="handleBackdropClick"
                />
                
                <!-- Sheet -->
                <div
                    ref="sheetRef"
                    class="absolute bottom-0 left-0 right-0 bg-white dark:bg-slate-900 rounded-t-2xl shadow-xl transition-transform duration-300 ease-out"
                    :style="{
                        transform: `translateY(${translateY})`,
                        maxHeight: `${props.maxHeight * 100}vh`,
                    }"
                    @touchstart="handleDragStart"
                    @touchmove="handleDragMove"
                    @touchend="handleDragEnd"
                    @touchcancel="handleDragEnd"
                >
                    <!-- Drag handle -->
                    <div
                        v-if="dismissible"
                        class="flex justify-center pt-3 pb-2 cursor-grab active:cursor-grabbing"
                    >
                        <div class="w-10 h-1 bg-slate-300 dark:bg-slate-600 rounded-full" />
                    </div>
                    
                    <!-- Header -->
                    <div
                        v-if="title"
                        class="px-4 pb-3 border-b border-slate-200 dark:border-slate-700"
                    >
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                            {{ title }}
                        </h2>
                    </div>
                    
                    <!-- Content -->
                    <div
                        ref="contentRef"
                        class="overflow-y-auto overscroll-contain"
                        :style="{ maxHeight: `calc(${props.maxHeight * 100}vh - 60px)` }"
                    >
                        <slot />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.sheet-enter-active,
.sheet-leave-active {
    transition: opacity 0.3s ease;
}

.sheet-enter-from,
.sheet-leave-to {
    opacity: 0;
}

.sheet-enter-active .absolute.bottom-0,
.sheet-leave-active .absolute.bottom-0 {
    transition: transform 0.3s ease-out;
}

.sheet-enter-from .absolute.bottom-0,
.sheet-leave-to .absolute.bottom-0 {
    transform: translateY(100%);
}
</style>
