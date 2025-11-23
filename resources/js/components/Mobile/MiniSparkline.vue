<template>
  <svg 
    :width="width" 
    :height="height" 
    class="inline-block"
    :class="className"
  >
    <polyline
      :points="points"
      fill="none"
      :stroke="color"
      :stroke-width="strokeWidth"
      stroke-linecap="round"
      stroke-linejoin="round"
    />
    <!-- Optional fill area -->
    <polygon
      v-if="filled"
      :points="fillPoints"
      :fill="fillColor"
      opacity="0.2"
    />
  </svg>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  data: number[];
  width?: number;
  height?: number;
  color?: string;
  fillColor?: string;
  strokeWidth?: number;
  filled?: boolean;
  className?: string;
}

const props = withDefaults(defineProps<Props>(), {
  width: 100,
  height: 30,
  color: '#3b82f6',
  fillColor: '#3b82f6',
  strokeWidth: 2,
  filled: false,
  className: ''
});

const points = computed(() => {
  if (!props.data || props.data.length === 0) return '';
  
  const max = Math.max(...props.data);
  const min = Math.min(...props.data);
  const range = max - min || 1;
  
  const xStep = props.width / (props.data.length - 1 || 1);
  const padding = 2;
  
  return props.data
    .map((value, index) => {
      const x = index * xStep;
      const y = props.height - padding - ((value - min) / range) * (props.height - padding * 2);
      return `${x},${y}`;
    })
    .join(' ');
});

const fillPoints = computed(() => {
  if (!props.filled || !points.value) return '';
  return `0,${props.height} ${points.value} ${props.width},${props.height}`;
});
</script>
