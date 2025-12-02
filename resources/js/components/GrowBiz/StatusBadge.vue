<template>
    <span :class="badgeClasses">
        {{ label }}
    </span>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    status: string;
    size?: 'sm' | 'md';
}

const props = withDefaults(defineProps<Props>(), {
    size: 'md'
});

const statusConfig: Record<string, { label: string; classes: string }> = {
    pending: { label: 'Pending', classes: 'bg-gray-100 text-gray-800' },
    in_progress: { label: 'In Progress', classes: 'bg-blue-100 text-blue-800' },
    on_hold: { label: 'On Hold', classes: 'bg-yellow-100 text-yellow-800' },
    completed: { label: 'Completed', classes: 'bg-green-100 text-green-800' },
    cancelled: { label: 'Cancelled', classes: 'bg-red-100 text-red-800' },
    active: { label: 'Active', classes: 'bg-green-100 text-green-800' },
    inactive: { label: 'Inactive', classes: 'bg-gray-100 text-gray-800' },
    on_leave: { label: 'On Leave', classes: 'bg-yellow-100 text-yellow-800' },
    terminated: { label: 'Terminated', classes: 'bg-red-100 text-red-800' },
};

const sizeClasses = {
    sm: 'px-2 py-0.5 text-[10px]',
    md: 'px-2.5 py-0.5 text-xs'
};

const config = computed(() => statusConfig[props.status] || { label: props.status, classes: 'bg-gray-100 text-gray-800' });
const label = computed(() => config.value.label);
const badgeClasses = computed(() => `inline-flex items-center rounded-full font-medium ${sizeClasses[props.size]} ${config.value.classes}`);
</script>
