<script setup lang="ts">
interface Props {
    padding?: 'none' | 'sm' | 'md' | 'lg';
    hover?: boolean;
    variant?: 'default' | 'elevated' | 'bordered';
}

const props = withDefaults(defineProps<Props>(), {
    padding: 'md',
    hover: false,
    variant: 'default',
});

const paddingClasses = {
    none: '',
    sm: 'p-4',
    md: 'p-6',
    lg: 'p-8',
};

const variantClasses = {
    default: 'bg-white shadow-sm ring-1 ring-gray-200/60 dark:bg-gray-800 dark:ring-gray-700/60',
    elevated: 'bg-white shadow-lg shadow-gray-200/50 ring-1 ring-gray-100 dark:bg-gray-800 dark:shadow-gray-900/50 dark:ring-gray-700/40',
    bordered: 'bg-white/80 backdrop-blur-sm border border-gray-200 dark:bg-gray-800/80 dark:border-gray-700',
};
</script>

<template>
    <div
        :class="[
            'rounded-2xl',
            variantClasses[variant],
            paddingClasses[padding],
            hover && 'transition-all duration-200 hover:shadow-md hover:ring-violet-300 dark:hover:ring-violet-500/50 cursor-pointer',
        ]"
    >
        <slot />
    </div>
</template>
