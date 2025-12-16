<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue';
import {
    WifiIcon,
    SignalSlashIcon,
    ArrowPathIcon,
} from '@heroicons/vue/24/outline';

const isOnline = ref(navigator.onLine);
const showBanner = ref(false);
const pendingSync = ref(0);

const handleOnline = () => {
    isOnline.value = true;
    showBanner.value = true;
    setTimeout(() => {
        showBanner.value = false;
    }, 3000);
};

const handleOffline = () => {
    isOnline.value = false;
    showBanner.value = true;
};

onMounted(() => {
    window.addEventListener('online', handleOnline);
    window.addEventListener('offline', handleOffline);

    // Check initial state
    if (!navigator.onLine) {
        showBanner.value = true;
    }
});

onUnmounted(() => {
    window.removeEventListener('online', handleOnline);
    window.removeEventListener('offline', handleOffline);
});
</script>

<template>
    <Transition
        enter-active-class="transition-transform duration-300 ease-out"
        enter-from-class="-translate-y-full"
        enter-to-class="translate-y-0"
        leave-active-class="transition-transform duration-200 ease-in"
        leave-from-class="translate-y-0"
        leave-to-class="-translate-y-full"
    >
        <div 
            v-if="showBanner"
            :class="[
                'fixed top-0 left-0 right-0 z-50 px-4 py-2 text-center text-sm font-medium safe-area-top',
                isOnline ? 'bg-emerald-500 text-white' : 'bg-amber-500 text-white'
            ]"
        >
            <div class="flex items-center justify-center gap-2">
                <component 
                    :is="isOnline ? WifiIcon : SignalSlashIcon" 
                    class="h-4 w-4" 
                    aria-hidden="true" 
                />
                <span v-if="isOnline">Back online! Syncing your data...</span>
                <span v-else>You're offline. Changes will sync when connected.</span>
                <ArrowPathIcon 
                    v-if="isOnline" 
                    class="h-4 w-4 animate-spin" 
                    aria-hidden="true" 
                />
            </div>
        </div>
    </Transition>

    <!-- Persistent offline indicator (small) -->
    <div 
        v-if="!isOnline && !showBanner"
        class="fixed top-2 right-2 z-40 px-2 py-1 bg-amber-500 text-white text-xs font-medium rounded-full flex items-center gap-1"
    >
        <SignalSlashIcon class="h-3 w-3" aria-hidden="true" />
        Offline
    </div>
</template>
