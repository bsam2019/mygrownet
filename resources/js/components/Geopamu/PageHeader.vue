<script setup lang="ts">
defineProps<{
  title: string;
  subtitle?: string;
}>();
</script>

<template>
  <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
      <h1 class="text-4xl md:text-5xl font-bold mb-4">{{ title }}</h1>
      <p v-if="subtitle" class="text-xl text-blue-100 max-w-3xl mx-auto">
        {{ subtitle }}
      </p>
    </div>
  </div>
</template>
