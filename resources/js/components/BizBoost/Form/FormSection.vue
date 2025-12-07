<script setup lang="ts">
interface Props {
    title?: string;
    description?: string;
    icon?: object;
    collapsible?: boolean;
}

defineProps<Props>();
</script>

<template>
    <div class="space-y-5">
        <div v-if="title || $slots.header" class="flex items-start gap-4">
            <div 
                v-if="icon" 
                class="flex-shrink-0 rounded-xl bg-gradient-to-br from-violet-100 to-violet-50 p-2.5 shadow-sm ring-1 ring-violet-200/50 dark:from-violet-900/40 dark:to-violet-800/20 dark:ring-violet-700/30"
            >
                <component :is="icon" class="h-5 w-5 text-violet-600 dark:text-violet-400" aria-hidden="true" />
            </div>
            <div class="flex-1 min-w-0">
                <h3 v-if="title" class="text-base font-semibold text-gray-900 dark:text-white">
                    {{ title }}
                </h3>
                <p v-if="description" class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ description }}
                </p>
                <slot name="header" />
            </div>
        </div>
        <div :class="[icon ? 'pl-14' : '', 'space-y-4']">
            <slot />
        </div>
    </div>
</template>
