<template>
  <span 
    :class="[
      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
      statusClasses
    ]"
  >
    <span 
      :class="[
        'w-1.5 h-1.5 rounded-full mr-1.5',
        dotClasses
      ]"
    />
    {{ statusText }}
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  status: 'active' | 'inactive' | 'terminated' | 'suspended';
}

const props = defineProps<Props>();

const statusText = computed(() => {
  const texts = {
    active: 'Active',
    inactive: 'Inactive',
    terminated: 'Terminated',
    suspended: 'Suspended'
  };
  return texts[props.status];
});

const statusClasses = computed(() => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    inactive: 'bg-gray-100 text-gray-800',
    terminated: 'bg-red-100 text-red-800',
    suspended: 'bg-yellow-100 text-yellow-800'
  };
  return classes[props.status];
});

const dotClasses = computed(() => {
  const classes = {
    active: 'bg-green-400',
    inactive: 'bg-gray-400',
    terminated: 'bg-red-400',
    suspended: 'bg-yellow-400'
  };
  return classes[props.status];
});
</script>