<script setup lang="ts">
interface Props {
    variant?: 'primary' | 'success' | 'warning' | 'danger' | 'info' | 'gray';
    size?: 'sm' | 'md';
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'gray',
    size: 'sm',
});

const variantClasses = {
    primary: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    success: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    warning: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    danger: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    info: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400',
    gray: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
};

const sizeClasses = {
    sm: 'px-2 py-0.5 text-xs',
    md: 'px-2.5 py-1 text-sm',
};
</script>

<template>
    <span
        :class="[
            'inline-flex items-center font-medium rounded-full',
            variantClasses[variant],
            sizeClasses[size],
        ]"
    >
        <slot />
    </span>
</template>
