<template>
    <Link
        :class="classes"
    >
        <slot />
    </Link>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    active: {
        type: Boolean,
        default: false,
    },
});

const classes = computed(() => ({
    'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out': true,
    'border-indigo-400 text-gray-900 focus:outline-none focus:border-indigo-700': props.active,
    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300': !props.active,
}));
</script>