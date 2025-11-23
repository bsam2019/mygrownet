<template>
  <button
    :disabled="loading || disabled"
    :class="[
      'relative transition-all min-h-[44px] font-medium rounded-lg',
      variantClasses,
      sizeClasses,
      (loading || disabled) ? 'opacity-60 cursor-not-allowed' : 'active:scale-95'
    ]"
    v-bind="$attrs"
  >
    <!-- Content (hidden when loading) -->
    <span :class="{ 'opacity-0': loading }">
      <slot />
    </span>

    <!-- Loading Spinner -->
    <div 
      v-if="loading" 
      class="absolute inset-0 flex items-center justify-center"
    >
      <svg 
        class="animate-spin h-5 w-5" 
        viewBox="0 0 24 24"
        fill="none"
      >
        <circle 
          class="opacity-25" 
          cx="12" 
          cy="12" 
          r="10" 
          stroke="currentColor" 
          stroke-width="4"
        />
        <path 
          class="opacity-75" 
          fill="currentColor" 
          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
        />
      </svg>
    </div>
  </button>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  loading?: boolean;
  disabled?: boolean;
  variant?: 'primary' | 'secondary' | 'success' | 'danger' | 'ghost';
  size?: 'sm' | 'md' | 'lg';
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  disabled: false,
  variant: 'primary',
  size: 'md'
});

const variantClasses = computed(() => {
  const variants = {
    primary: 'bg-blue-600 text-white hover:bg-blue-700',
    secondary: 'bg-gray-100 text-gray-700 hover:bg-gray-200',
    success: 'bg-green-600 text-white hover:bg-green-700',
    danger: 'bg-red-600 text-white hover:bg-red-700',
    ghost: 'bg-transparent text-gray-700 hover:bg-gray-100'
  };
  return variants[props.variant];
});

const sizeClasses = computed(() => {
  const sizes = {
    sm: 'px-3 py-2 text-sm',
    md: 'px-4 py-3 text-base',
    lg: 'px-6 py-4 text-lg'
  };
  return sizes[props.size];
});
</script>

<script lang="ts">
// Disable attribute inheritance to prevent duplicate class bindings
export default {
  inheritAttrs: false
};
</script>
