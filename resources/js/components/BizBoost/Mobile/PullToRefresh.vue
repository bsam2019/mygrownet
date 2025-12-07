<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useHaptics } from '@/composables/useHaptics';
import { ArrowPathIcon } from '@heroicons/vue/24/outline';

interface Props {
    /** Callback when refresh is triggered */
    onRefresh: () => Promise<void>;
    /** Disable pull-to-refresh */
    disabled?: boolean;
    /** Pull distance threshold to trigger refresh (px) */
    threshold?: number;
    /** Maximum pull distance (px) */
    maxPull?: number;
}

const props = withDefaults(defineProps<Props>(), {
    disabled: false,
    threshold: 80,
    maxPull: 120,
});

const emit = defineEmits<{
    (e: 'refresh-start'): void;
    (e: 'refresh-end', success: boolean): void;
}>();

const { light, success: hapticSuccess, error: hapticError } = useHaptics();

// State
const containerRef = ref<HTMLElement | null>(null);
const isPulling = ref(false);
const isRefreshing = ref(false);
const pullDistance = ref(0);
const startY = ref(0);
const hasTriggeredHaptic = ref(false);

// Computed
const progress = computed(() => Math.min(pullDistance.value / props.threshold, 1));
const canTrigger = computed(() => progress.value >= 1 && !isRefreshing.value);

const indicatorStyle = computed(() => ({
    transform: `translateY(${Math.min(pullDistance.value, props.maxPull) - 60}px)`,
    opacity: Math.min(progress.value, 1),
}));

const spinnerRotation = computed(() => {
    if (isRefreshing.value) return 0;
    return progress.value * 180;
});

// Touch handlers
const handleTouchStart = (e: TouchEvent) => {
    if (props.disabled || isRefreshing.value) return;
    
    // Only trigger if at top of scroll
    const scrollTop = containerRef.value?.scrollTop ?? 0;
    if (scrollTop > 0) return;

    startY.value = e.touches[0].clientY;
    isPulling.value = true;
    hasTriggeredHaptic.value = false;
};

const handleTouchMove = (e: TouchEvent) => {
    if (!isPulling.value || props.disabled || isRefreshing.value) return;

    const currentY = e.touches[0].clientY;
    const diff = currentY - startY.value;

    // Only pull down
    if (diff > 0) {
        // Apply resistance
        pullDistance.value = Math.min(diff * 0.5, props.maxPull);
        
        // Prevent scroll while pulling
        if (pullDistance.value > 10) {
            e.preventDefault();
        }

        // Haptic feedback when reaching threshold
        if (!hasTriggeredHaptic.value && progress.value >= 1) {
            light();
            hasTriggeredHaptic.value = true;
        }
    }
};

const handleTouchEnd = async () => {
    if (!isPulling.value) return;
    isPulling.value = false;

    if (canTrigger.value) {
        await triggerRefresh();
    } else {
        // Snap back
        pullDistance.value = 0;
    }
};

const triggerRefresh = async () => {
    isRefreshing.value = true;
    pullDistance.value = props.threshold; // Hold at threshold
    emit('refresh-start');

    try {
        await props.onRefresh();
        hapticSuccess();
        emit('refresh-end', true);
    } catch (error) {
        hapticError();
        emit('refresh-end', false);
        console.error('[PullToRefresh] Refresh failed:', error);
    } finally {
        isRefreshing.value = false;
        pullDistance.value = 0;
    }
};

onMounted(() => {
    const el = containerRef.value;
    if (!el) return;

    el.addEventListener('touchstart', handleTouchStart, { passive: true });
    el.addEventListener('touchmove', handleTouchMove, { passive: false });
    el.addEventListener('touchend', handleTouchEnd, { passive: true });
    el.addEventListener('touchcancel', handleTouchEnd, { passive: true });
});

onUnmounted(() => {
    const el = containerRef.value;
    if (!el) return;

    el.removeEventListener('touchstart', handleTouchStart);
    el.removeEventListener('touchmove', handleTouchMove);
    el.removeEventListener('touchend', handleTouchEnd);
    el.removeEventListener('touchcancel', handleTouchEnd);
});
</script>

<template>
    <div ref="containerRef" class="relative overflow-auto">
        <!-- Pull indicator -->
        <div
            class="absolute left-0 right-0 flex justify-center pointer-events-none z-10"
            :style="indicatorStyle"
        >
            <div
                :class="[
                    'flex items-center justify-center w-10 h-10 rounded-full bg-white dark:bg-slate-800 shadow-lg transition-transform',
                    isRefreshing && 'animate-spin',
                ]"
                :style="{ transform: `rotate(${spinnerRotation}deg)` }"
            >
                <ArrowPathIcon
                    :class="[
                        'h-5 w-5 transition-colors',
                        canTrigger ? 'text-violet-600' : 'text-slate-400',
                    ]"
                    aria-hidden="true"
                />
            </div>
        </div>

        <!-- Refreshing text -->
        <div
            v-if="isRefreshing"
            class="absolute left-0 right-0 flex justify-center pointer-events-none z-10"
            :style="{ transform: `translateY(${props.threshold + 10}px)` }"
        >
            <span class="text-xs text-slate-500 dark:text-slate-400">
                Refreshing...
            </span>
        </div>

        <!-- Content with pull offset -->
        <div
            :style="{
                transform: `translateY(${pullDistance}px)`,
                transition: isPulling ? 'none' : 'transform 0.3s ease-out',
            }"
        >
            <slot />
        </div>
    </div>
</template>
