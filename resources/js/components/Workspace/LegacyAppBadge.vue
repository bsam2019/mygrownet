<script setup lang="ts">
defineProps<{
    legacy?: boolean;
    migrationDeadline?: string | null;
}>();
</script>

<template>
    <span
        v-if="legacy"
        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800"
    >
        Legacy
        <span v-if="migrationDeadline" class="ml-1 text-amber-600">· Migrate by {{ migrationDeadline }}</span>
    </span>
</template>
