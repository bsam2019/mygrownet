<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <h3 class="text-lg font-semibold leading-none tracking-tight">
        <slot />
    </h3>
</template>
