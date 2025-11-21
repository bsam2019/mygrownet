<template>
  <div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900">{{ title }}</h2>
    <p class="mt-2 text-gray-600">{{ description }}</p>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  title: string;
  description: string;
}>();
</script>
