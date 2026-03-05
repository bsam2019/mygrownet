<template>
  <div class="text-center py-8 px-4">
    <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-2xl flex items-center justify-center">
      <component :is="icon" class="h-8 w-8 text-gray-400" aria-hidden="true" />
    </div>
    <h3 class="text-base font-semibold text-gray-900 mb-2">{{ title }}</h3>
    <p class="text-sm text-gray-600 leading-relaxed mb-6 max-w-sm mx-auto">
      {{ description }}
    </p>
    <button
      v-if="actionText && actionHandler"
      @click="actionHandler"
      class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 active:scale-95 transition-all shadow-sm"
    >
      <component v-if="actionIcon" :is="actionIcon" class="h-5 w-5" aria-hidden="true" />
      <span>{{ actionText }}</span>
    </button>
  </div>
</template>

<script setup lang="ts">
import type { Component } from 'vue';

interface Props {
  icon: Component;
  title: string;
  description: string;
  actionText?: string;
  actionIcon?: Component;
  actionHandler?: () => void;
}

defineProps<Props>();
</script>
