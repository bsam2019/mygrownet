<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface Props {
    title: string;
    subtitle?: string;
    backLink?: string;
    backLabel?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="mb-6">
        <Link
            v-if="backLink"
            :href="backLink"
            class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 mb-4"
        >
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            {{ backLabel || 'Back' }}
        </Link>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ title }}</h1>
        <p v-if="subtitle" class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ subtitle }}</p>
    </div>
</template>
