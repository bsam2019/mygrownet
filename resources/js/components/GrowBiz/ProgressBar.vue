<template>
    <div class="w-full">
        <div v-if="showLabel" class="flex justify-between items-center mb-1">
            <span class="text-sm font-medium text-gray-700">{{ label }}</span>
            <span class="text-sm font-semibold" :class="percentageColor">{{ percentage }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full overflow-hidden" :class="heightClass">
            <div 
                class="h-full rounded-full transition-all duration-500 ease-out"
                :class="barColor"
                :style="{ width: `${percentage}%` }"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    percentage: number;
    label?: string;
    showLabel?: boolean;
    size?: 'sm' | 'md' | 'lg';
    color?: 'auto' | 'blue' | 'green' | 'yellow' | 'red';
}

const props = withDefaults(defineProps<Props>(), {
    label: 'Progress',
    showLabel: true,
    size: 'md',
    color: 'auto',
});

const heightClass = computed(() => {
    switch (props.size) {
        case 'sm': return 'h-1.5';
        case 'lg': return 'h-4';
        default: return 'h-2.5';
    }
});

const barColor = computed(() => {
    if (props.color !== 'auto') {
        const colors: Record<string, string> = {
            blue: 'bg-blue-500',
            green: 'bg-green-500',
            yellow: 'bg-yellow-500',
            red: 'bg-red-500',
        };
        return colors[props.color];
    }
    
    // Auto color based on percentage
    if (props.percentage >= 100) return 'bg-green-500';
    if (props.percentage >= 75) return 'bg-blue-500';
    if (props.percentage >= 50) return 'bg-yellow-500';
    if (props.percentage >= 25) return 'bg-orange-500';
    return 'bg-red-500';
});

const percentageColor = computed(() => {
    if (props.percentage >= 100) return 'text-green-600';
    if (props.percentage >= 75) return 'text-blue-600';
    if (props.percentage >= 50) return 'text-yellow-600';
    return 'text-gray-600';
});
</script>
