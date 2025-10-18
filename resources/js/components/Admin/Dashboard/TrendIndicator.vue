<template>
    <span class="flex items-center space-x-1" :class="colorClass">
        <component :is="icon" class="w-4 h-4" />
        <span>{{ formattedValue }}</span>
    </span>
</template>

<script setup>
import { computed } from 'vue';
import { ArrowUpIcon, ArrowDownIcon, MinusIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    value: {
        type: Number,
        required: true
    }
});

const icon = computed(() => {
    if (props.value > 0) return ArrowUpIcon;
    if (props.value < 0) return ArrowDownIcon;
    return MinusIcon;
});

const colorClass = computed(() => {
    if (props.value > 0) return 'text-white/90';
    if (props.value < 0) return 'text-white/90';
    return 'text-white/70';
});

const formattedValue = computed(() => {
    const absValue = Math.abs(props.value);
    return `${absValue}%`;
});
</script>
