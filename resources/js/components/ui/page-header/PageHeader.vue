<script setup lang="ts">
defineProps<{
    className?: string;
}>();
</script>

<template>
    <div class="flex items-center justify-between space-y-2">
        <slot />
    </div>
</template>