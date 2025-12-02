<script setup lang="ts">
/**
 * Native app-style skeleton loading states
 * Use instead of spinners for a more polished feel
 */
defineProps<{
    type?: 'card' | 'list' | 'stats' | 'text' | 'avatar' | 'custom';
    count?: number;
}>();
</script>

<template>
    <!-- Stats skeleton -->
    <div v-if="type === 'stats'" class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div v-for="i in (count || 4)" :key="i" class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="skeleton w-10 h-10 rounded-lg" />
                <div class="ml-3 flex-1">
                    <div class="skeleton h-3 w-16 mb-2" />
                    <div class="skeleton h-6 w-12" />
                </div>
            </div>
        </div>
    </div>

    <!-- List skeleton -->
    <div v-else-if="type === 'list'" class="bg-white rounded-lg shadow divide-y divide-gray-100">
        <div v-for="i in (count || 5)" :key="i" class="p-4">
            <div class="flex items-center">
                <div class="skeleton w-10 h-10 rounded-full" />
                <div class="ml-3 flex-1">
                    <div class="skeleton h-4 w-3/4 mb-2" />
                    <div class="skeleton h-3 w-1/2" />
                </div>
                <div class="skeleton h-6 w-16 rounded-full" />
            </div>
        </div>
    </div>

    <!-- Card skeleton -->
    <div v-else-if="type === 'card'" class="bg-white rounded-lg shadow p-4">
        <div class="skeleton h-5 w-1/3 mb-4" />
        <div class="space-y-3">
            <div class="skeleton h-4 w-full" />
            <div class="skeleton h-4 w-5/6" />
            <div class="skeleton h-4 w-4/6" />
        </div>
    </div>

    <!-- Text skeleton -->
    <div v-else-if="type === 'text'" class="space-y-2">
        <div v-for="i in (count || 3)" :key="i" class="skeleton h-4" :style="{ width: `${100 - (i * 10)}%` }" />
    </div>

    <!-- Avatar skeleton -->
    <div v-else-if="type === 'avatar'" class="flex items-center gap-3">
        <div class="skeleton w-12 h-12 rounded-full" />
        <div class="flex-1">
            <div class="skeleton h-4 w-24 mb-2" />
            <div class="skeleton h-3 w-32" />
        </div>
    </div>

    <!-- Custom slot -->
    <div v-else>
        <slot />
    </div>
</template>

<style scoped>
.skeleton {
    background: linear-gradient(
        90deg,
        #f0f0f0 25%,
        #e0e0e0 50%,
        #f0f0f0 75%
    );
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    border-radius: 4px;
}

@keyframes shimmer {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}
</style>
