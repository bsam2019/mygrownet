<template>
    <span :class="badgeClasses">
        {{ label }}
    </span>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    priority: string;
    size?: 'sm' | 'md';
}

const props = withDefaults(defineProps<Props>(), {
    size: 'md'
});

const priorityConfig: Record<string, { label: string; classes: string }> = {
    low: { label: 'Low', classes: 'bg-gray-100 text-gray-800' },
    medium: { label: 'Medium', classes: 'bg-blue-100 text-blue-800' },
    high: { label: 'High', classes: 'bg-orange-100 text-orange-800' },
    urgent: { label: 'Urgent', classes: 'bg-red-100 text-red-800' },
};

const sizeClasses = {
    sm: 'px-2 py-0.5 text-[10px]',
    md: 'px-2.5 py-0.5 text-xs'
};

const config = computed(() => priorityConfig[props.priority] || { label: props.priority, classes: 'bg-gray-100 text-gray-800' });
const label = computed(() => config.value.label);
const badgeClasses = computed(() => `inline-flex items-center rounded-full font-medium ${sizeClasses[props.size]} ${config.value.classes}`);
</script>
