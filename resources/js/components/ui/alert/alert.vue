<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';

const props = withDefaults(defineProps<{
  variant?: 'default' | 'destructive';
  class?: string;
}>(), {
  variant: 'default',
  class: ''
});

const alertClasses = computed(() => {
  return cn(
    'relative w-full rounded-lg border p-4',
    {
      'bg-background text-foreground': props.variant === 'default',
      'border-destructive/50 text-destructive dark:border-destructive [&>svg]:text-destructive': props.variant === 'destructive',
    },
    props.class
  );
});
</script>

<template>
  <div :class="alertClasses" role="alert">
    <slot />
  </div>
</template> 