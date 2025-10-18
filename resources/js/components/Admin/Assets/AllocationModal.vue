<template>
  <div>
    <!-- Placeholder for Allocation Modal -->
  </div>
</template>

<script setup lang="ts">
defineProps<{
  show: boolean;
  asset?: any;
}>();

defineEmits(['close', 'allocated']);
</script>
