<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    modelValue: string | number;
    label?: string;
    type?: string;
    placeholder?: string;
    error?: string;
    required?: boolean;
    disabled?: boolean;
    hint?: string;
    id?: string;
    icon?: object;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'text',
    required: false,
    disabled: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: string | number];
}>();

const inputId = computed(() => props.id || `input-${Math.random().toString(36).substr(2, 9)}`);
</script>

<template>
    <div class="space-y-2">
        <label
            v-if="label"
            :for="inputId"
            class="block text-sm font-medium text-gray-700 dark:text-gray-200"
        >
            {{ label }}
            <span v-if="required" class="text-violet-500 ml-0.5">*</span>
        </label>
        <div class="relative">
            <div v-if="icon" class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                <component :is="icon" class="h-5 w-5 text-gray-400 dark:text-gray-500" aria-hidden="true" />
            </div>
            <input
                :id="inputId"
                :type="type"
                :value="modelValue"
                :placeholder="placeholder"
                :required="required"
                :disabled="disabled"
                :class="[
                    'block w-full rounded-xl border border-gray-200 bg-white/80 backdrop-blur-sm text-gray-900 shadow-sm transition-all duration-200',
                    'placeholder:text-gray-400',
                    'hover:border-gray-300 hover:bg-white',
                    'focus:border-violet-500 focus:bg-white focus:ring-2 focus:ring-violet-500/20 focus:outline-none',
                    'disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500 disabled:border-gray-200',
                    'dark:border-gray-700 dark:bg-gray-800/80 dark:text-white dark:placeholder:text-gray-500',
                    'dark:hover:border-gray-600 dark:hover:bg-gray-800',
                    'dark:focus:border-violet-400 dark:focus:bg-gray-800 dark:focus:ring-violet-400/20',
                    'dark:disabled:bg-gray-900 dark:disabled:border-gray-800',
                    icon ? 'pl-11 pr-4 py-3' : 'px-4 py-3',
                ]"
                @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
            />
        </div>
        <p v-if="hint && !error" class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">{{ hint }}</p>
        <p v-if="error" class="flex items-center gap-1.5 text-sm text-red-600 dark:text-red-400 mt-1.5">
            <svg class="h-4 w-4 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
            </svg>
            {{ error }}
        </p>
    </div>
</template>
