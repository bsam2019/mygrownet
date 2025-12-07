<script setup lang="ts">
import { computed } from 'vue';
import { CheckIcon } from '@heroicons/vue/24/solid';

interface Props {
    modelValue: boolean;
    label?: string;
    description?: string;
    error?: string;
    disabled?: boolean;
    id?: string;
}

const props = withDefaults(defineProps<Props>(), {
    disabled: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: boolean];
}>();

const inputId = computed(() => props.id || `checkbox-${Math.random().toString(36).substr(2, 9)}`);
</script>

<template>
    <div class="group flex items-start gap-3">
        <div class="flex h-6 items-center">
            <div class="relative">
                <input
                    :id="inputId"
                    type="checkbox"
                    :checked="modelValue"
                    :disabled="disabled"
                    class="peer sr-only"
                    @change="emit('update:modelValue', ($event.target as HTMLInputElement).checked)"
                />
                <div
                    :class="[
                        'h-5 w-5 rounded-md border-2 transition-all duration-200 cursor-pointer',
                        'flex items-center justify-center',
                        modelValue
                            ? 'bg-violet-600 border-violet-600 dark:bg-violet-500 dark:border-violet-500'
                            : 'bg-white border-gray-300 dark:bg-gray-800 dark:border-gray-600',
                        'group-hover:border-violet-400 dark:group-hover:border-violet-500',
                        disabled ? 'opacity-50 cursor-not-allowed' : '',
                    ]"
                    @click="!disabled && emit('update:modelValue', !modelValue)"
                >
                    <CheckIcon
                        v-if="modelValue"
                        class="h-3.5 w-3.5 text-white"
                        aria-hidden="true"
                    />
                </div>
            </div>
        </div>
        <div v-if="label || description" class="flex-1 min-w-0">
            <label
                v-if="label"
                :for="inputId"
                :class="[
                    'text-sm font-medium cursor-pointer select-none',
                    modelValue ? 'text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300',
                ]"
            >
                {{ label }}
            </label>
            <p v-if="description" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                {{ description }}
            </p>
        </div>
        <p v-if="error" class="text-sm text-red-600 dark:text-red-400">{{ error }}</p>
    </div>
</template>
