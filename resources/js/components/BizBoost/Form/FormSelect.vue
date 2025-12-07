<script setup lang="ts">
import { computed } from 'vue';
import { ChevronDownIcon } from '@heroicons/vue/24/outline';

interface Option {
    value: string | number | null;
    label: string;
    disabled?: boolean;
}

interface Props {
    modelValue: string | number | null;
    label?: string;
    options: Option[];
    placeholder?: string;
    error?: string;
    required?: boolean;
    disabled?: boolean;
    hint?: string;
    id?: string;
    icon?: object;
}

const props = withDefaults(defineProps<Props>(), {
    required: false,
    disabled: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: string | number | null];
}>();

const inputId = computed(() => props.id || `select-${Math.random().toString(36).substr(2, 9)}`);

const handleChange = (event: Event) => {
    const target = event.target as HTMLSelectElement;
    const value = target.value;
    emit('update:modelValue', value === '' ? null : value);
};
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
            <select
                :id="inputId"
                :value="modelValue ?? ''"
                :required="required"
                :disabled="disabled"
                :class="[
                    'block w-full appearance-none rounded-xl border border-gray-200 bg-white/80 backdrop-blur-sm text-gray-900 shadow-sm transition-all duration-200',
                    'hover:border-gray-300 hover:bg-white',
                    'focus:border-violet-500 focus:bg-white focus:ring-2 focus:ring-violet-500/20 focus:outline-none',
                    'disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500 disabled:border-gray-200',
                    'dark:border-gray-700 dark:bg-gray-800/80 dark:text-white',
                    'dark:hover:border-gray-600 dark:hover:bg-gray-800',
                    'dark:focus:border-violet-400 dark:focus:bg-gray-800 dark:focus:ring-violet-400/20',
                    'dark:disabled:bg-gray-900 dark:disabled:border-gray-800',
                    icon ? 'pl-11 pr-10 py-3' : 'pl-4 pr-10 py-3',
                ]"
                @change="handleChange"
            >
                <option v-if="placeholder" value="" disabled class="text-gray-400">{{ placeholder }}</option>
                <option
                    v-for="option in options"
                    :key="String(option.value)"
                    :value="option.value ?? ''"
                    :disabled="option.disabled"
                >
                    {{ option.label }}
                </option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                <ChevronDownIcon class="h-5 w-5 text-gray-400 dark:text-gray-500" aria-hidden="true" />
            </div>
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
