<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';

const props = withDefaults(defineProps<{
  class?: string;
}>(), {
  class: ''
});

const descriptionClasses = computed(() => {
  return cn('text-sm [&_p]:leading-relaxed', props.class);
});
</script>

<template>
  <div :class="descriptionClasses">
    <slot />
  </div>
</template> 