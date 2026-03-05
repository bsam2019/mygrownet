<template>
  <div v-if="trend" class="flex items-center gap-1 text-xs mt-1">
    <component 
      :is="trendIcon" 
      class="h-3 w-3"
      :class="trendColorClass"
      aria-hidden="true"
    />
    <span :class="trendColorClass" class="font-medium">
      {{ trend.value }}
    </span>
    <span class="text-gray-500">{{ trend.period }}</span>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { ArrowUpIcon, ArrowDownIcon, MinusIcon } from '@heroicons/vue/24/solid';

interface Trend {
  direction: 'up' | 'down' | 'neutral';
  value: string;
  period: string;
}

interface Props {
  trend?: Trend;
}

const props = defineProps<Props>();

const trendIcon = computed(() => {
  if (!props.trend) return null;
  
  switch (props.trend.direction) {
    case 'up':
      return ArrowUpIcon;
    case 'down':
      return ArrowDownIcon;
    default:
      return MinusIcon;
  }
});

const trendColorClass = computed(() => {
  if (!props.trend) return '';
  
  switch (props.trend.direction) {
    case 'up':
      return 'text-green-600';
    case 'down':
      return 'text-red-600';
    default:
      return 'text-gray-500';
  }
});
</script>
