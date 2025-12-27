<template>
    <div 
        class="p-4 rounded-lg border"
        :class="darkMode ? 'bg-gray-800/50 border-gray-700' : 'bg-gray-50 border-gray-200'"
    >
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">
                Result
            </span>
            <button
                @click="$emit('copy', text)"
                class="flex items-center gap-1 px-2 py-1 text-xs rounded-md transition-colors"
                :class="darkMode 
                    ? 'hover:bg-gray-700 text-gray-400 hover:text-gray-200' 
                    : 'hover:bg-gray-200 text-gray-500 hover:text-gray-700'"
            >
                <ClipboardIcon class="w-3.5 h-3.5" aria-hidden="true" />
                Copy
            </button>
        </div>
        <p class="text-sm whitespace-pre-wrap" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">
            {{ text }}
        </p>
    </div>
</template>

<script setup lang="ts">
import { ClipboardIcon } from '@heroicons/vue/24/outline';

defineProps<{
    darkMode?: boolean;
    text: string;
}>();

defineEmits<{
    copy: [text: string];
}>();
</script>
