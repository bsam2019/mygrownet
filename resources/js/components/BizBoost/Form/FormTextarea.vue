<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    modelValue: string;
    label?: string;
    placeholder?: string;
    error?: string;
    required?: boolean;
    disabled?: boolean;
    hint?: string;
    rows?: number;
    id?: string;
    maxLength?: number;
}

const props = withDefaults(defineProps<Props>(), {
    required: false,
    disabled: false,
    rows: 3,
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const inputId = computed(() => props.id || `textarea-${Math.random().toString(36).substr(2, 9)}`);
const charCount = computed(() => props.modelValue?.length || 0);
</script>

<template>
    <div class="space-y-2">
        <div class="flex items-center justify-between">
            <label
                v-if="label"
                :for="inputId"
                class="block text-sm font-medium text-gray-700 dark:text-gray-200"
            >
                {{ label }}
                <span v-if="required" class="text-violet-500 ml-0.5">*</span>
            </label>
            <span v-if="maxLength" class="text-xs text-gray-400 dark:text-gray-500">
                {{ charCount }}/{{ maxLength }}
            </span>
        </div>
        <textarea
            :id="inputId"
            :value="modelValue"
            :placeholder="placeholder"
            :required="required"
            :disabled="disabled"
            :rows="rows"
            :maxlength="maxLength"
            :class="[
                'block w-full rounded-xl border border-gray-200 bg-white/80 backdrop-blur-sm px-4 py-3 text-gray-900 shadow-sm transition-all duration-200 resize-none',
                'placeholder:text-gray-400',
                'hover:border-gray-300 hover:bg-white',
                'focus:border-violet-500 focus:bg-white focus:ring-2 focus:ring-violet-500/20 focus:outline-none',
                'disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500 disabled:border-gray-200',
                'dark:border-gray-700 dark:bg-gray-800/80 dark:text-white dark:placeholder:text-gray-500',
                'dark:hover:border-gray-600 dark:hover:bg-gray-800',
                'dark:focus:border-violet-400 dark:focus:bg-gray-800 dark:focus:ring-violet-400/20',
                'dark:disabled:bg-gray-900 dark:disabled:border-gray-800',
            ]"
            @input="emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
        ></textarea>
        <p v-if="hint && !error" class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">{{ hint }}</p>
        <p v-if="error" class="flex items-center gap-1.5 text-sm text-red-600 dark:text-red-400 mt-1.5">
            <svg class="h-4 w-4 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
            </svg>
            {{ error }}
        </p>
    </div>
</template>
