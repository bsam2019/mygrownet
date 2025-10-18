<template>
    <div class="bg-white rounded-lg shadow p-3 sm:p-4">
        <div class="flex flex-col h-full">
            <div class="flex items-start justify-between space-x-2">
                <h4 class="text-xs sm:text-sm text-gray-500 font-medium">{{ title }}</h4>
                <div v-if="showTrend && change !== undefined"
                    class="flex-shrink-0 text-xs sm:text-sm px-1.5 py-0.5 rounded-full"
                    :class="change >= 0 ? 'text-green-600 bg-green-50' : 'text-red-600 bg-red-50'"
                >
                    {{ change >= 0 ? '↑' : '↓' }}{{ Math.abs(change) }}%
                </div>
            </div>
            <div class="mt-2 sm:mt-3">
                <div class="text-lg sm:text-xl font-semibold" :class="getValueColor">{{ formattedValue }}</div>
                <div v-if="subtitle" class="text-xs sm:text-sm text-gray-500 mt-0.5">{{ subtitle }}</div>
                <div v-if="description" class="text-xs text-gray-500 mt-1">{{ description }}</div>
            </div>
            <div v-if="showProgress" class="mt-auto pt-3">
                <div class="h-1.5 w-full bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-500"
                        :class="getProgressColor"
                        :style="{ width: `${Math.min(Math.max(progress, 0), 100)}%` }"
                    ></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: String,
    value: [String, Number],
    type: {
        type: String,
        default: 'number'
    },
    change: Number,
    subtitle: String,
    description: String,
    showTrend: {
        type: Boolean,
        default: true
    },
    showProgress: {
        type: Boolean,
        default: false
    },
    progress: {
        type: Number,
        default: 0
    },
    threshold: {
        type: Object,
        default: () => ({
            warning: 50,
            danger: 25
        })
    }
});

const formattedValue = computed(() => {
    if (props.type === 'currency') {
        return new Intl.NumberFormat('en-ZM', {
            style: 'currency',
            currency: 'ZMW'
        }).format(props.value);
    }
    if (props.type === 'percentage') {
        return `${props.value}%`;
    }
    return props.value?.toLocaleString() ?? '0';
});

const getValueColor = computed(() => {
    if (!props.change) return 'text-gray-900';
    return props.change >= 0 ? 'text-green-600' : 'text-red-600';
});

const getProgressColor = computed(() => {
    const progress = props.progress;
    if (progress <= props.threshold.danger) return 'bg-red-500';
    if (progress <= props.threshold.warning) return 'bg-yellow-500';
    return 'bg-green-500';
});
</script>
