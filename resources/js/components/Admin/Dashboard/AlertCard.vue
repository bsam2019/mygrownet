<template>
    <Link v-if="link" :href="link" class="block">
        <div :class="['p-4 rounded-lg shadow-sm transition-all duration-150 hover:shadow-md', bgClass]">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <component :is="icon" class="w-5 h-5" :class="textClass" />
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between">
                        <h4 :class="['text-sm font-medium truncate', textClass]">
                            {{ title }}
                        </h4>
                        <div :class="['ml-2 text-lg font-semibold', textClass]">
                            {{ value }}
                        </div>
                    </div>
                    <p v-if="subtext" class="mt-1 text-xs text-gray-600 truncate">{{ subtext }}</p>
                </div>
            </div>
        </div>
    </Link>
    <div v-else :class="['p-4 rounded-lg shadow-sm', bgClass]">
        <div class="flex items-start space-x-3">
            <div class="flex-shrink-0">
                <component :is="icon" class="w-5 h-5" :class="textClass" />
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between">
                    <h4 :class="['text-sm font-medium truncate', textClass]">
                        {{ title }}
                    </h4>
                    <div :class="['ml-2 text-lg font-semibold', textClass]">
                        {{ value }}
                    </div>
                </div>
                <p v-if="subtext" class="mt-1 text-xs text-gray-600 truncate">{{ subtext }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    ExclamationTriangleIcon,
    InformationCircleIcon,
    ExclamationCircleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    value: {
        type: [String, Number],
        required: true
    },
    type: {
        type: String,
        default: 'info',
        validator: (value) => ['info', 'warning', 'error'].includes(value)
    },
    subtext: {
        type: String,
        default: ''
    },
    link: {
        type: String,
        default: ''
    }
});

const icon = computed(() => ({
    'info': InformationCircleIcon,
    'warning': ExclamationTriangleIcon,
    'error': ExclamationCircleIcon
}[props.type]));

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
