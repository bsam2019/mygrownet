<template>
    <div :class="['p-3 sm:p-4 rounded-lg transition-colors duration-150', bgClass]">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between">
            <div class="mb-2 sm:mb-0">
                <h4 :class="['text-sm font-medium', textClass]">
                    {{ title }}
                </h4>
                <p v-if="subtext" class="text-xs mt-1 text-gray-600">{{ subtext }}</p>
            </div>
            <div :class="['text-lg font-semibold', textClass]">
                {{ value }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: String,
    value: [String, Number],
    type: String,
    subtext: String
});

const bgClass = computed(() => ({
    'bg-blue-50 hover:bg-blue-100': props.type === 'info',
    'bg-yellow-50 hover:bg-yellow-100': props.type === 'warning',
    'bg-red-50 hover:bg-red-100': props.type === 'error'
}));

const textClass = computed(() => ({
    'text-blue-800': props.type === 'info',
    'text-yellow-800': props.type === 'warning',
    'text-red-800': props.type === 'error'
}));
</script>
