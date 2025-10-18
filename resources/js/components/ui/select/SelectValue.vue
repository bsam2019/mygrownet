<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
    placeholder?: string;
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <span :class="cn('text-sm', props.class)">
        <slot>{{ props.placeholder }}</slot>
    </span>
</template> 
 