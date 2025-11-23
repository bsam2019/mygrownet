<template>
  <div class="text-center py-12 px-4">
    <!-- Icon Container -->
    <div 
      class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center"
      :class="iconBgClass"
    >
      <component 
        v-if="icon" 
        :is="icon" 
        class="h-12 w-12"
        :class="iconColorClass"
        aria-hidden="true"
      />
      <span v-else class="text-3xl">{{ emoji }}</span>
    </div>

    <!-- Title -->
    <h3 class="text-lg font-semibold text-gray-900 mb-2">
      {{ title }}
    </h3>

    <!-- Description -->
    <p class="text-sm text-gray-500 mb-6 max-w-sm mx-auto">
      {{ description }}
    </p>

    <!-- Action Button -->
    <button
      v-if="actionText"
      @click="$emit('action')"
      class="px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 active:scale-95 transition-all min-h-[44px]"
    >
      {{ actionText }}
    </button>

    <!-- Secondary Action -->
    <button
      v-if="secondaryActionText"
      @click="$emit('secondary-action')"
      class="mt-3 px-6 py-3 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 active:scale-95 transition-all min-h-[44px]"
    >
      {{ secondaryActionText }}
    </button>
  </div>
</template>

<script setup lang="ts">
interface Props {
  icon?: any;
  emoji?: string;
  title: string;
  description: string;
  actionText?: string;
  secondaryActionText?: string;
  variant?: 'default' | 'info' | 'success' | 'warning' | 'error';
}

const props = withDefaults(defineProps<Props>(), {
  emoji: '📭',
  variant: 'default'
});

interface Emits {
  (e: 'action'): void;
  (e: 'secondary-action'): void;
}

defineEmits<Emits>();

const iconBgClass = computed(() => {
  const variants = {
    default: 'bg-gray-100',
    info: 'bg-blue-100',
    success: 'bg-green-100',
    warning: 'bg-orange-100',
    error: 'bg-red-100'
  };
  return variants[props.variant];
});

const iconColorClass = computed(() => {
  const variants = {
    default: 'text-gray-400',
    info: 'text-blue-500',
    success: 'text-green-500',
    warning: 'text-orange-500',
    error: 'text-red-500'
  };
  return variants[props.variant];
});
</script>

<script lang="ts">
import { computed } from 'vue';
</script>
