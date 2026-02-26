<script setup lang="ts">
import { computed } from 'vue';
import type { StorageUsage } from '@/types/storage';

interface Props {
    usage: StorageUsage;
}

const props = defineProps<Props>();

const progressColor = computed(() => {
    if (props.usage.percent_used >= 90) return 'bg-red-600';
    if (props.usage.percent_used >= 80) return 'bg-amber-600';
    return 'bg-blue-600';
});

const textColor = computed(() => {
    if (props.usage.percent_used >= 90) return 'text-red-600';
    if (props.usage.percent_used >= 80) return 'text-amber-600';
    return 'text-gray-700';
});
</script>

<template>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700">Storage Used</span>
            <span :class="['text-sm font-semibold', textColor]">
                {{ usage.percent_used.toFixed(1) }}%
            </span>
        </div>
        
        <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
            <div
                :class="['h-2 rounded-full transition-all duration-300', progressColor]"
                :style="{ width: `${Math.min(usage.percent_used, 100)}%` }"
            ></div>
        </div>
        
        <div class="flex items-center justify-between text-xs text-gray-600">
            <span>{{ usage.formatted_used }} used</span>
            <span>{{ usage.formatted_quota }} total</span>
        </div>
        
        <div v-if="usage.near_limit" class="mt-2 text-xs text-amber-600 flex items-center gap-1">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span>Storage almost full</span>
        </div>
    </div>
</template>
