<template>
    <!-- Hide the entire floating button when chat is open -->
    <div v-if="!isOpen" class="fixed bottom-6 right-6 z-40 flex flex-col items-end gap-3">
        <!-- Tooltip hint (shows on first visit) -->
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 translate-x-4"
            enter-to-class="opacity-100 translate-x-0"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 translate-x-0"
            leave-to-class="opacity-0 translate-x-4"
        >
            <div 
                v-if="showHint"
                class="flex items-center gap-2 px-4 py-2 rounded-full shadow-lg text-sm font-medium"
                :class="darkMode 
                    ? 'bg-gray-800 text-white border border-gray-700' 
                    : 'bg-white text-gray-900 border border-gray-200'"
            >
                <span>Need help? Ask AI</span>
                <button 
                    @click="dismissHint"
                    class="p-0.5 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700"
                    aria-label="Dismiss hint"
                >
                    <XMarkIcon class="w-3.5 h-3.5 opacity-50" aria-hidden="true" />
                </button>
            </div>
        </Transition>

        <!-- Main Button -->
        <button
            @click="$emit('toggle')"
            class="group relative w-14 h-14 rounded-full shadow-lg transition-all duration-300 flex items-center justify-center bg-gradient-to-br from-violet-500 via-purple-500 to-indigo-600 hover:from-violet-600 hover:via-purple-600 hover:to-indigo-700 hover:scale-105 hover:shadow-xl hover:shadow-purple-500/25"
            :class="pulseAnimation ? 'animate-pulse-subtle' : ''"
            aria-label="Open AI Assistant"
        >
            <!-- Sparkle effect on hover -->
            <div class="absolute inset-0 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                <div class="absolute top-1 right-2 w-1.5 h-1.5 bg-white/60 rounded-full animate-ping" />
                <div class="absolute bottom-2 left-3 w-1 h-1 bg-white/40 rounded-full animate-ping" style="animation-delay: 150ms" />
            </div>

            <!-- Icon -->
            <SparklesIcon class="w-6 h-6 text-white" aria-hidden="true" />

            <!-- Notification dot -->
            <div 
                v-if="hasNotification"
                class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-500 rounded-full border-2 border-white dark:border-gray-900 flex items-center justify-center"
            >
                <span class="text-[9px] font-bold text-white">!</span>
            </div>
        </button>

        <!-- Context indicator (shows what AI knows about) -->
        <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
        >
            <div 
                v-if="contextSummary"
                class="text-[10px] px-2 py-1 rounded-full max-w-[150px] truncate"
                :class="darkMode ? 'bg-gray-800/80 text-gray-400' : 'bg-white/80 text-gray-500'"
            >
                {{ contextSummary }}
            </div>
        </Transition>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { SparklesIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
    isOpen: boolean;
    darkMode?: boolean;
    hasNotification?: boolean;
    currentSection?: { type: string } | null;
    currentPage?: { title: string } | null;
}>();

defineEmits<{
    toggle: [];
}>();

const showHint = ref(false);
const pulseAnimation = ref(false);

// Context summary for the indicator
const contextSummary = computed(() => {
    if (props.currentSection?.type) {
        return `Editing ${props.currentSection.type}`;
    }
    if (props.currentPage?.title) {
        return `On ${props.currentPage.title}`;
    }
    return null;
});

// Dismiss the hint
const dismissHint = () => {
    showHint.value = false;
    localStorage.setItem('ai_hint_dismissed', 'true');
};

// Show hint on first visit
onMounted(() => {
    const dismissed = localStorage.getItem('ai_hint_dismissed');
    if (!dismissed) {
        setTimeout(() => {
            showHint.value = true;
            // Auto-dismiss after 10 seconds
            setTimeout(() => {
                showHint.value = false;
            }, 10000);
        }, 3000);
    }

    // Subtle pulse animation every 30 seconds to draw attention
    setInterval(() => {
        if (!props.isOpen) {
            pulseAnimation.value = true;
            setTimeout(() => {
                pulseAnimation.value = false;
            }, 2000);
        }
    }, 30000);
});
</script>

<style scoped>
@keyframes pulse-subtle {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 10px 15px -3px rgb(139 92 246 / 0.2);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 20px 25px -5px rgb(139 92 246 / 0.3);
    }
}

.animate-pulse-subtle {
    animation: pulse-subtle 2s ease-in-out;
}
</style>
