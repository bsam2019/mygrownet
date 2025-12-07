import { ref, computed, onMounted, onUnmounted, type Ref } from 'vue';
import { useHaptics } from './useHaptics';

export type SwipeDirection = 'left' | 'right' | 'up' | 'down' | null;

interface SwipeState {
    startX: number;
    startY: number;
    currentX: number;
    currentY: number;
    startTime: number;
    isSwiping: boolean;
}

interface SwipeOptions {
    /** Minimum distance (px) to trigger a swipe */
    threshold?: number;
    /** Minimum velocity (px/ms) to trigger a swipe */
    velocityThreshold?: number;
    /** Prevent vertical scroll during horizontal swipe */
    preventScrollOnSwipe?: boolean;
    /** Enable haptic feedback */
    hapticFeedback?: boolean;
    /** Directions to detect */
    directions?: ('left' | 'right' | 'up' | 'down')[];
}

interface SwipeResult {
    direction: SwipeDirection;
    deltaX: number;
    deltaY: number;
    velocity: number;
    duration: number;
}

const defaultOptions: Required<SwipeOptions> = {
    threshold: 50,
    velocityThreshold: 0.3,
    preventScrollOnSwipe: true,
    hapticFeedback: true,
    directions: ['left', 'right', 'up', 'down'],
};

export function useSwipeGesture(
    elementRef: Ref<HTMLElement | null>,
    options: SwipeOptions = {}
) {
    const opts = { ...defaultOptions, ...options };
    const { light } = useHaptics();

    // State
    const state = ref<SwipeState>({
        startX: 0,
        startY: 0,
        currentX: 0,
        currentY: 0,
        startTime: 0,
        isSwiping: false,
    });

    const isSwiping = computed(() => state.value.isSwiping);
    const deltaX = computed(() => state.value.currentX - state.value.startX);
    const deltaY = computed(() => state.value.currentY - state.value.startY);

    const direction = computed<SwipeDirection>(() => {
        const dx = deltaX.value;
        const dy = deltaY.value;
        const absDx = Math.abs(dx);
        const absDy = Math.abs(dy);

        if (absDx < 10 && absDy < 10) return null;

        // Determine primary direction
        if (absDx > absDy) {
            return dx > 0 ? 'right' : 'left';
        } else {
            return dy > 0 ? 'down' : 'up';
        }
    });

    const progress = computed(() => {
        const dx = Math.abs(deltaX.value);
        const dy = Math.abs(deltaY.value);
        const distance = Math.max(dx, dy);
        return Math.min(distance / opts.threshold, 1);
    });

    // Track if we've passed the threshold for haptic feedback
    let hasTriggeredHaptic = false;

    const handleTouchStart = (e: TouchEvent) => {
        const touch = e.touches[0];
        state.value = {
            startX: touch.clientX,
            startY: touch.clientY,
            currentX: touch.clientX,
            currentY: touch.clientY,
            startTime: Date.now(),
            isSwiping: true,
        };
        hasTriggeredHaptic = false;
    };

    const handleTouchMove = (e: TouchEvent) => {
        if (!state.value.isSwiping) return;

        const touch = e.touches[0];
        state.value.currentX = touch.clientX;
        state.value.currentY = touch.clientY;

        const dx = Math.abs(deltaX.value);
        const dy = Math.abs(deltaY.value);

        // Prevent vertical scroll during horizontal swipe
        if (opts.preventScrollOnSwipe && dx > dy && dx > 10) {
            e.preventDefault();
        }

        // Trigger haptic when passing threshold
        if (opts.hapticFeedback && !hasTriggeredHaptic && progress.value >= 1) {
            light();
            hasTriggeredHaptic = true;
        }
    };

    const handleTouchEnd = (): SwipeResult | null => {
        if (!state.value.isSwiping) return null;

        const duration = Date.now() - state.value.startTime;
        const dx = deltaX.value;
        const dy = deltaY.value;
        const distance = Math.sqrt(dx * dx + dy * dy);
        const velocity = distance / duration;

        const result: SwipeResult = {
            direction: direction.value,
            deltaX: dx,
            deltaY: dy,
            velocity,
            duration,
        };

        // Reset state
        state.value.isSwiping = false;

        // Check if swipe meets threshold
        const meetsThreshold = 
            Math.abs(dx) >= opts.threshold || 
            Math.abs(dy) >= opts.threshold ||
            velocity >= opts.velocityThreshold;

        if (!meetsThreshold || !result.direction) {
            return null;
        }

        // Check if direction is enabled
        if (!opts.directions.includes(result.direction)) {
            return null;
        }

        return result;
    };

    const handleTouchCancel = () => {
        state.value.isSwiping = false;
    };

    // Event callbacks
    let onSwipeCallback: ((result: SwipeResult) => void) | null = null;
    let onSwipeStartCallback: (() => void) | null = null;
    let onSwipeEndCallback: ((result: SwipeResult | null) => void) | null = null;

    const wrappedTouchEnd = () => {
        const result = handleTouchEnd();
        onSwipeEndCallback?.(result);
        if (result) {
            onSwipeCallback?.(result);
        }
    };

    const wrappedTouchStart = (e: TouchEvent) => {
        handleTouchStart(e);
        onSwipeStartCallback?.();
    };

    onMounted(() => {
        const el = elementRef.value;
        if (!el) return;

        el.addEventListener('touchstart', wrappedTouchStart, { passive: true });
        el.addEventListener('touchmove', handleTouchMove, { passive: false });
        el.addEventListener('touchend', wrappedTouchEnd, { passive: true });
        el.addEventListener('touchcancel', handleTouchCancel, { passive: true });
    });

    onUnmounted(() => {
        const el = elementRef.value;
        if (!el) return;

        el.removeEventListener('touchstart', wrappedTouchStart);
        el.removeEventListener('touchmove', handleTouchMove);
        el.removeEventListener('touchend', wrappedTouchEnd);
        el.removeEventListener('touchcancel', handleTouchCancel);
    });

    /**
     * Register a callback for when a swipe is detected
     */
    const onSwipe = (callback: (result: SwipeResult) => void) => {
        onSwipeCallback = callback;
    };

    /**
     * Register a callback for when swiping starts
     */
    const onSwipeStart = (callback: () => void) => {
        onSwipeStartCallback = callback;
    };

    /**
     * Register a callback for when swiping ends (regardless of threshold)
     */
    const onSwipeEnd = (callback: (result: SwipeResult | null) => void) => {
        onSwipeEndCallback = callback;
    };

    /**
     * Reset the swipe state
     */
    const reset = () => {
        state.value = {
            startX: 0,
            startY: 0,
            currentX: 0,
            currentY: 0,
            startTime: 0,
            isSwiping: false,
        };
    };

    return {
        // State
        isSwiping,
        deltaX,
        deltaY,
        direction,
        progress,

        // Callbacks
        onSwipe,
        onSwipeStart,
        onSwipeEnd,

        // Actions
        reset,
    };
}
