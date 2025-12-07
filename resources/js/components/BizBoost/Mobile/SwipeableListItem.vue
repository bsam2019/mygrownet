<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, type Component } from 'vue';
import { useHaptics } from '@/composables/useHaptics';

interface SwipeAction {
    label: string;
    icon: Component;
    color: 'red' | 'violet' | 'green' | 'blue' | 'amber';
    onClick: () => void;
}

interface Props {
    /** Actions revealed when swiping left */
    leftActions?: SwipeAction[];
    /** Actions revealed when swiping right */
    rightActions?: SwipeAction[];
    /** Percentage of width to auto-trigger primary action */
    autoTriggerThreshold?: number;
    /** Disable swipe actions */
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    autoTriggerThreshold: 0.6,
    disabled: false,
});

const emit = defineEmits<{
    (e: 'swipe-open', direction: 'left' | 'right'): void;
    (e: 'swipe-close'): void;
}>();

const { light, medium } = useHaptics();

// State
const containerRef = ref<HTMLElement | null>(null);
const contentRef = ref<HTMLElement | null>(null);
const translateX = ref(0);
const isDragging = ref(false);
const startX = ref(0);
const startTranslateX = ref(0);
const containerWidth = ref(0);
const isOpen = ref<'left' | 'right' | null>(null);
const hasTriggeredHaptic = ref(false);

// Action button width
const actionWidth = 80;

// Computed
const leftActionsWidth = computed(() => (props.leftActions?.length ?? 0) * actionWidth);
const rightActionsWidth = computed(() => (props.rightActions?.length ?? 0) * actionWidth);

const canSwipeLeft = computed(() => (props.rightActions?.length ?? 0) > 0);
const canSwipeRight = computed(() => (props.leftActions?.length ?? 0) > 0);

const autoTriggerDistance = computed(() => containerWidth.value * props.autoTriggerThreshold);

const colorClasses: Record<string, string> = {
    red: 'bg-red-500 text-white',
    violet: 'bg-violet-500 text-white',
    green: 'bg-green-500 text-white',
    blue: 'bg-blue-500 text-white',
    amber: 'bg-amber-500 text-white',
};

// Touch handlers
const handleTouchStart = (e: TouchEvent) => {
    if (props.disabled) return;

    const touch = e.touches[0];
    startX.value = touch.clientX;
    startTranslateX.value = translateX.value;
    isDragging.value = true;
    hasTriggeredHaptic.value = false;

    if (containerRef.value) {
        containerWidth.value = containerRef.value.offsetWidth;
    }
};

const handleTouchMove = (e: TouchEvent) => {
    if (!isDragging.value || props.disabled) return;

    const touch = e.touches[0];
    const diff = touch.clientX - startX.value;
    let newTranslate = startTranslateX.value + diff;

    // Limit swipe based on available actions
    if (newTranslate > 0 && !canSwipeRight.value) {
        newTranslate = 0;
    } else if (newTranslate < 0 && !canSwipeLeft.value) {
        newTranslate = 0;
    }

    // Apply resistance at edges
    if (newTranslate > leftActionsWidth.value) {
        const overflow = newTranslate - leftActionsWidth.value;
        newTranslate = leftActionsWidth.value + overflow * 0.3;
    } else if (newTranslate < -rightActionsWidth.value) {
        const overflow = Math.abs(newTranslate) - rightActionsWidth.value;
        newTranslate = -rightActionsWidth.value - overflow * 0.3;
    }

    translateX.value = newTranslate;

    // Prevent vertical scroll during horizontal swipe
    if (Math.abs(diff) > 10) {
        e.preventDefault();
    }

    // Haptic feedback at auto-trigger threshold
    if (!hasTriggeredHaptic.value && Math.abs(newTranslate) >= autoTriggerDistance.value) {
        medium();
        hasTriggeredHaptic.value = true;
    }
};

const handleTouchEnd = () => {
    if (!isDragging.value) return;
    isDragging.value = false;

    const absTranslate = Math.abs(translateX.value);

    // Auto-trigger if past threshold
    if (absTranslate >= autoTriggerDistance.value) {
        if (translateX.value > 0 && props.leftActions?.length) {
            // Trigger first left action
            props.leftActions[0].onClick();
            close();
            return;
        } else if (translateX.value < 0 && props.rightActions?.length) {
            // Trigger first right action
            props.rightActions[0].onClick();
            close();
            return;
        }
    }

    // Snap to open or closed position
    if (translateX.value > leftActionsWidth.value / 2 && canSwipeRight.value) {
        openLeft();
    } else if (translateX.value < -rightActionsWidth.value / 2 && canSwipeLeft.value) {
        openRight();
    } else {
        close();
    }
};

const openLeft = () => {
    translateX.value = leftActionsWidth.value;
    isOpen.value = 'left';
    light();
    emit('swipe-open', 'left');
};

const openRight = () => {
    translateX.value = -rightActionsWidth.value;
    isOpen.value = 'right';
    light();
    emit('swipe-open', 'right');
};

const close = () => {
    translateX.value = 0;
    isOpen.value = null;
    emit('swipe-close');
};

const handleActionClick = (action: SwipeAction) => {
    light();
    action.onClick();
    close();
};

// Close when clicking outside
const handleClickOutside = (e: MouseEvent) => {
    if (isOpen.value && containerRef.value && !containerRef.value.contains(e.target as Node)) {
        close();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

// Expose close method
defineExpose({ close });
</script>

<template>
    <div
        ref="containerRef"
        class="relative overflow-hidden"
        @touchstart="handleTouchStart"
        @touchmove="handleTouchMove"
        @touchend="handleTouchEnd"
        @touchcancel="handleTouchEnd"
    >
        <!-- Left actions (revealed when swiping right) -->
        <div
            v-if="leftActions?.length"
            class="absolute inset-y-0 left-0 flex"
        >
            <button
                v-for="(action, index) in leftActions"
                :key="`left-${index}`"
                :class="[
                    'flex flex-col items-center justify-center px-4 transition-transform',
                    colorClasses[action.color],
                ]"
                :style="{ width: `${actionWidth}px` }"
                @click="handleActionClick(action)"
                :aria-label="action.label"
            >
                <component :is="action.icon" class="h-5 w-5 mb-1" aria-hidden="true" />
                <span class="text-xs font-medium">{{ action.label }}</span>
            </button>
        </div>

        <!-- Right actions (revealed when swiping left) -->
        <div
            v-if="rightActions?.length"
            class="absolute inset-y-0 right-0 flex"
        >
            <button
                v-for="(action, index) in rightActions"
                :key="`right-${index}`"
                :class="[
                    'flex flex-col items-center justify-center px-4 transition-transform',
                    colorClasses[action.color],
                ]"
                :style="{ width: `${actionWidth}px` }"
                @click="handleActionClick(action)"
                :aria-label="action.label"
            >
                <component :is="action.icon" class="h-5 w-5 mb-1" aria-hidden="true" />
                <span class="text-xs font-medium">{{ action.label }}</span>
            </button>
        </div>

        <!-- Main content -->
        <div
            ref="contentRef"
            class="relative bg-white dark:bg-slate-900"
            :style="{
                transform: `translateX(${translateX}px)`,
                transition: isDragging ? 'none' : 'transform 0.3s ease-out',
            }"
        >
            <slot />
        </div>
    </div>
</template>
