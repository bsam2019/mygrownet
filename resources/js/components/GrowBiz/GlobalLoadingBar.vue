<script setup lang="ts">
/**
 * Global Loading Bar
 * 
 * Shows a progress bar at the top of the screen during navigation.
 * Provides visual feedback for SPA transitions.
 */
import { computed } from 'vue';
import { useNavigationState } from '@/composables/useInertiaEnhancements';

const { isNavigating, loadingProgress } = useNavigationState();

const progressStyle = computed(() => ({
    width: `${loadingProgress.value}%`,
    transition: loadingProgress.value === 100 ? 'width 0.1s ease-out' : 'width 0.3s ease-out',
}));
</script>

<template>
    <Transition
        enter-active-class="transition-opacity duration-150"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-300"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div 
            v-if="isNavigating" 
            class="fixed top-0 left-0 right-0 z-[100] h-1 bg-emerald-100/50"
        >
            <div 
                class="h-full bg-gradient-to-r from-emerald-500 to-emerald-400 shadow-sm shadow-emerald-500/50"
                :style="progressStyle"
            />
            <!-- Glow effect -->
            <div 
                class="absolute right-0 top-0 h-full w-24 bg-gradient-to-l from-white/30 to-transparent animate-pulse"
                :style="{ transform: `translateX(${loadingProgress.value - 100}%)` }"
            />
        </div>
    </Transition>
</template>
