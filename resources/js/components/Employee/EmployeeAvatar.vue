<template>
  <div 
    :class="[
      'rounded-full flex items-center justify-center font-medium',
      sizeClasses,
      statusClasses
    ]"
  >
    <span :class="textSizeClasses">
      {{ initials }}
    </span>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  name: string;
  status?: 'active' | 'inactive' | 'terminated' | 'suspended';
  size?: 'sm' | 'md' | 'lg' | 'xl';
}

const props = withDefaults(defineProps<Props>(), {
  status: 'active',
  size: 'md'
});

const initials = computed(() => {
  const names = props.name.split(' ');
  return names.length >= 2 
    ? `${names[0].charAt(0)}${names[1].charAt(0)}`.toUpperCase()
    : names[0].charAt(0).toUpperCase();
});

const sizeClasses = computed(() => {
  const sizes = {
    sm: 'h-8 w-8',
    md: 'h-10 w-10',
    lg: 'h-12 w-12',
    xl: 'h-16 w-16'
  };
  return sizes[props.size];
});

const textSizeClasses = computed(() => {
  const sizes = {
    sm: 'text-xs',
    md: 'text-sm',
    lg: 'text-base',
    xl: 'text-lg'
  };
  return sizes[props.size];
});

const statusClasses = computed(() => {
  const classes = {
    active: 'bg-blue-100 text-blue-700',
    inactive: 'bg-gray-100 text-gray-600',
    terminated: 'bg-red-100 text-red-700',
    suspended: 'bg-yellow-100 text-yellow-700'
  };
  return classes[props.status];
});
</script>