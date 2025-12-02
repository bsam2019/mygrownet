<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { ArrowPathIcon } from '@heroicons/vue/24/outline';

const emit = defineEmits<{
    refresh: [];
}>();

const props = defineProps<{
    loading?: boolean;
    threshold?: number;
}>();

const pullDistance = ref(0);
const isPulling = ref(false);
const isRefreshing = ref(false);
const containerRef = ref<HTMLElement | null>(null);

const THRESHOLD = props.threshold || 80;
let startY = 0;
let currentY = 0;

const handleTouchStart = (e: TouchEvent) => {
    if (window.scrollY === 0 && !isRefreshing.value) {
        startY = e.touches[0].clientY;
        isPulling.value = true;
    }
};

const handleTouchMove = (e: TouchEvent) => {
    if (!isPulling.value || isRefreshing.value) return;
    
    currentY = e.touches[0].clientY;
    const diff = currentY - startY;
    
    if (diff > 0 && window.scrollY === 0) {
        // Apply resistance to pull
        pullDistance.value = Math.min(diff * 0.5, THRESHOLD * 1.5);
        
        if (pullDistance.value > 10) {
            e.preventDefault();
        }
    }
};

const handleTouchEnd = () => {
    if (pullDistance.value >= THRESHOLD && !isRefreshing.value) {
        isRefreshing.value = true;
        pullDistance.value = THRESHOLD * 0.6;
        emit('refresh');
    } else {
        pullDistance.value = 0;
    }
    isPulling.value = false;
};

const finishRefresh = () => {
    isRefreshing.value = false;
    pullDistance.value = 0;
};

// Expose finish method for parent
defineExpose({ finishRefresh });

onMounted(() => {
    const container = containerRef.value;
    if (container) {
        container.addEventListener('touchstart', handleTouchStart, { passive: true });
        container.addEventListener('touchmove', handleTouchMove, { passive: false });
        container.addEventListener('touchend', handleTouchEnd, { passive: true });
    }
});

onUnmounted(() => {
    const container = containerRef.value;
    if (container) {
        container.removeEventListener('touchstart', handleTouchStart);
        container.removeEventListener('touchmove', handleTouchMove);
        container.removeEventListener('touchend', handleTouchEnd);
    }
});
</script>

<template>
    <div ref="containerRef" class="pull-to-refresh-container">
        <!-- Pull indicator -->
        <div 
            class="pull-indicator"
            :style="{ 
                transform: `translateY(${pullDistance - 50}px)`,
                opacity: Math.min(pullDistance / THRESHOLD, 1)
            }"
        >
            <div 
                class="pull-icon"
                :class="{ 'is-refreshing': isRefreshing }"
                :style="{ 
                    transform: `rotate(${isRefreshing ? 0 : (pullDistance / THRESHOLD) * 180}deg)`
                }"
            >
                <ArrowPathIcon class="h-6 w-6 text-emerald-600" aria-hidden="true" />
            </div>
            <span class="text-xs text-gray-500 mt-1">
                {{ isRefreshing ? 'Refreshing...' : pullDistance >= THRESHOLD ? 'Release to refresh' : 'Pull to refresh' }}
            </span>
        </div>

        <!-- Content with pull offset -->
        <div 
            class="pull-content"
            :style="{ transform: `translateY(${pullDistance}px)` }"
        >
            <slot />
        </div>
    </div>
</template>

<style scoped>
.pull-to-refresh-container {
    position: relative;
    overflow: hidden;
    min-height: 100%;
}

.pull-indicator {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 50px;
    pointer-events: none;
    z-index: 10;
}

.pull-icon {
    transition: transform 0.1s ease;
}

.pull-icon.is-refreshing {
    animation: spin 1s linear infinite;
}

.pull-content {
    transition: transform 0.2s ease;
    will-change: transform;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
