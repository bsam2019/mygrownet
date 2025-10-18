<script setup lang="ts">
import { computed } from 'vue';
import { CheckCircleIcon, XCircleIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
    type: 'success' | 'error';
    message: string;
    show: boolean;
}>();

const emit = defineEmits(['close']);

const bgColor = computed(() => ({
    'success': 'bg-green-50',
    'error': 'bg-red-50'
}[props.type]));

const textColor = computed(() => ({
    'success': 'text-green-800',
    'error': 'text-red-800'
}[props.type]));

const Icon = computed(() => ({
    'success': CheckCircleIcon,
    'error': XCircleIcon
}[props.type]));
</script>

<template>
    <div v-if="show"
         :class="['rounded-md p-4 fixed top-4 right-4 max-w-md z-50', bgColor]">
        <div class="flex">
            <div class="flex-shrink-0">
                <component :is="Icon" :class="['h-5 w-5', textColor]" />
            </div>
            <div class="ml-3">
                <p :class="['text-sm font-medium', textColor]">{{ message }}</p>
            </div>
            <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                    <button @click="emit('close')"
                            :class="['inline-flex rounded-md p-1.5', textColor, bgColor]">
                        <XMarkIcon class="h-5 w-5" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
