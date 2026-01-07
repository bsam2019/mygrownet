<script setup lang="ts">
defineProps<{
  categories: Array<{ id: string; name: string }>;
  modelValue: string;
}>();

const emit = defineEmits<{
  'update:modelValue': [value: string];
}>();
</script>

<template>
  <div class="flex flex-wrap gap-3 justify-center mb-12">
    <button
      v-for="category in categories"
      :key="category.id"
      @click="emit('update:modelValue', category.id)"
      :class="[
        'px-6 py-2 rounded-full font-medium transition-all',
        modelValue === category.id
          ? 'bg-blue-600 text-white shadow-lg'
          : 'bg-white text-gray-700 hover:bg-gray-100 shadow-sm'
      ]"
    >
      {{ category.name }}
    </button>
  </div>
</template>
