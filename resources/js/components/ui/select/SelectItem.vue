<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
    value: string | number;
    class?: HTMLAttributes['class'];
}>();

const emit = defineEmits<{
    (e: 'select', value: string | number): void;
}>();
</script>

<template>
    <div
        :class="cn(
            'relative flex w-full cursor-pointer select-none items-center rounded-sm py-1.5 pl-8 pr-2 text-sm outline-none hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50',
            props.class
        )"
        :data-value="props.value"
        @click="emit('select', props.value)"
        role="option"
    >
        <span class="absolute left-2 flex h-3.5 w-3.5 items-center justify-center">
            <slot name="icon" />
        </span>
        <slot />
    </div>
</template> 