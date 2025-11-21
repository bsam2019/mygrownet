<template>
  <button
    type="button"
    @click="$emit('click')"
    :disabled="loading"
    class="absolute right-2 top-2 px-3 py-1.5 bg-gradient-to-r from-purple-600 to-blue-600 text-white text-xs font-medium rounded-lg hover:from-purple-700 hover:to-blue-700 disabled:opacity-50 flex items-center space-x-1 shadow-sm"
  >
    <SparklesIcon class="w-4 h-4" />
    <span>{{ loading ? 'Generating...' : 'AI Generate' }}</span>
  </button>
</template>

<script setup lang="ts">
import { SparklesIcon } from '@heroicons/vue/24/outline';

defineProps<{
  loading?: boolean;
}>();

defineEmits<{
  click: [];
}>();
</script>
