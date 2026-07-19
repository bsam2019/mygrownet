<script setup lang="ts">
interface Props {
  title: string
  description?: string
  divider?: boolean
}

withDefaults(defineProps<Props>(), {
  divider: true,
})
</script>

<template>
  <div :class="{ 'pt-6 border-t border-gray-200': divider && $attrs.class !== 'first' }">
    <div class="mb-4">
      <h3 class="text-base font-semibold text-gray-900">{{ title }}</h3>
      <p v-if="description" class="mt-1 text-sm text-gray-500">{{ description }}</p>
    </div>
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
      <slot />
    </div>
  </div>
</template>
