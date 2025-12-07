<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface Props {
    submitLabel?: string;
    cancelLabel?: string;
    cancelHref?: string;
    processing?: boolean;
    submitVariant?: 'primary' | 'danger';
}

const props = withDefaults(defineProps<Props>(), {
    submitLabel: 'Save',
    cancelLabel: 'Cancel',
    processing: false,
    submitVariant: 'primary',
});

const emit = defineEmits<{
    cancel: [];
}>();

const submitClasses = {
    primary: 'bg-gradient-to-r from-violet-600 to-violet-500 hover:from-violet-700 hover:to-violet-600 focus:ring-violet-500/30',
    danger: 'bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 focus:ring-red-500/30',
};
</script>

<template>
    <div class="flex items-center justify-end gap-3 pt-6 mt-2 border-t border-gray-200 dark:border-gray-700/50">
        <Link
            v-if="cancelHref"
            :href="cancelHref"
            class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800"
        >
            {{ cancelLabel }}
        </Link>
        <button
            v-else
            type="button"
            @click="emit('cancel')"
            class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800"
        >
            {{ cancelLabel }}
        </button>
        <button
            type="submit"
            :disabled="processing"
            :class="[
                'relative px-6 py-2.5 text-sm font-semibold text-white rounded-xl shadow-lg shadow-violet-500/25 transition-all duration-200',
                'focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-900',
                'disabled:opacity-60 disabled:cursor-not-allowed disabled:shadow-none',
                submitClasses[submitVariant],
            ]"
        >
            <span v-if="processing" class="flex items-center gap-2">
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Saving...
            </span>
            <span v-else>{{ submitLabel }}</span>
        </button>
    </div>
</template>
