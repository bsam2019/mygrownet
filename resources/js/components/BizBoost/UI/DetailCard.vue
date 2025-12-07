<script setup lang="ts">
interface Props {
    title?: string;
    subtitle?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-200/60 dark:bg-gray-800 dark:ring-gray-700/60 overflow-hidden">
        <div v-if="title || $slots.header" class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <slot name="header">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
                <p v-if="subtitle" class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ subtitle }}</p>
            </slot>
        </div>
        <div class="p-6">
            <slot />
        </div>
        <div v-if="$slots.footer" class="px-6 py-4 bg-gray-50 border-t border-gray-200 dark:bg-gray-800/50 dark:border-gray-700">
            <slot name="footer" />
        </div>
    </div>
</template>
