<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';

interface Props {
    /** Skeleton variant */
    variant?: 'list' | 'card' | 'detail' | 'text' | 'avatar' | 'button';
    /** Number of items for list variant */
    count?: number;
    /** Show "Still loading" message after delay */
    showSlowMessage?: boolean;
    /** Delay before showing slow message (ms) */
    slowMessageDelay?: number;
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'text',
    count: 5,
    showSlowMessage: true,
    slowMessageDelay: 3000,
});

const showSlowLoading = ref(false);
let slowTimeout: ReturnType<typeof setTimeout> | null = null;

onMounted(() => {
    if (props.showSlowMessage) {
        slowTimeout = setTimeout(() => {
            showSlowLoading.value = true;
        }, props.slowMessageDelay);
    }
});

onUnmounted(() => {
    if (slowTimeout) {
        clearTimeout(slowTimeout);
    }
});
</script>

<template>
    <div class="animate-pulse">
        <!-- Text skeleton -->
        <template v-if="variant === 'text'">
            <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-3/4 mb-2"></div>
            <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-1/2"></div>
        </template>

        <!-- Avatar skeleton -->
        <template v-else-if="variant === 'avatar'">
            <div class="h-10 w-10 bg-slate-200 dark:bg-slate-700 rounded-full"></div>
        </template>

        <!-- Button skeleton -->
        <template v-else-if="variant === 'button'">
            <div class="h-10 bg-slate-200 dark:bg-slate-700 rounded-lg w-24"></div>
        </template>

        <!-- List skeleton -->
        <template v-else-if="variant === 'list'">
            <div class="space-y-3">
                <div
                    v-for="i in count"
                    :key="i"
                    class="flex items-center gap-3 p-3 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700"
                >
                    <div class="h-12 w-12 bg-slate-200 dark:bg-slate-700 rounded-lg flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-3/4 mb-2"></div>
                        <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded w-1/2"></div>
                    </div>
                    <div class="h-8 w-16 bg-slate-200 dark:bg-slate-700 rounded-lg flex-shrink-0"></div>
                </div>
            </div>
        </template>

        <!-- Card skeleton -->
        <template v-else-if="variant === 'card'">
            <div class="grid grid-cols-2 gap-3">
                <div
                    v-for="i in 4"
                    :key="i"
                    class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4"
                >
                    <div class="h-8 w-8 bg-slate-200 dark:bg-slate-700 rounded-lg mb-3"></div>
                    <div class="h-6 bg-slate-200 dark:bg-slate-700 rounded w-1/2 mb-2"></div>
                    <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-3/4"></div>
                </div>
            </div>
        </template>

        <!-- Detail skeleton -->
        <template v-else-if="variant === 'detail'">
            <div class="space-y-6">
                <!-- Header -->
                <div class="flex items-center gap-4">
                    <div class="h-16 w-16 bg-slate-200 dark:bg-slate-700 rounded-xl"></div>
                    <div class="flex-1">
                        <div class="h-6 bg-slate-200 dark:bg-slate-700 rounded w-1/2 mb-2"></div>
                        <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-1/3"></div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-3 gap-3">
                    <div v-for="i in 3" :key="i" class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
                        <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-1/2 mb-2"></div>
                        <div class="h-6 bg-slate-200 dark:bg-slate-700 rounded w-3/4"></div>
                    </div>
                </div>

                <!-- Content sections -->
                <div class="space-y-4">
                    <div class="h-5 bg-slate-200 dark:bg-slate-700 rounded w-1/4"></div>
                    <div class="space-y-2">
                        <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-full"></div>
                        <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-5/6"></div>
                        <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-4/6"></div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Slow loading message -->
        <Transition name="fade">
            <div
                v-if="showSlowLoading"
                class="text-center mt-4 text-sm text-slate-500 dark:text-slate-400"
            >
                Still loading...
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
