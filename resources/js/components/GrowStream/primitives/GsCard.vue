<script setup lang="ts">
interface Props {
    hover?: boolean;
}

withDefaults(defineProps<Props>(), {
    hover: false,
});
</script>

<template>
    <div :class="['gs-card', hover ? 'gs-card-hover' : '']">
        <slot />
    </div>
</template>
