<script setup lang="ts">
/**
 * Native app-style page transitions
 * Provides smooth slide animations between pages
 */
defineProps<{
    name?: 'slide' | 'fade' | 'scale';
}>();
</script>

<template>
    <Transition
        :name="name || 'page-slide'"
        mode="out-in"
        appear
    >
        <slot />
    </Transition>
</template>

<style>
/* Slide transition (default) */
.page-slide-enter-active,
.page-slide-leave-active {
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.page-slide-enter-from {
    opacity: 0;
    transform: translateX(20px);
}

.page-slide-leave-to {
    opacity: 0;
    transform: translateX(-20px);
}

/* Fade transition */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Scale transition (for modals/sheets) */
.scale-enter-active,
.scale-leave-active {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.scale-enter-from,
.scale-leave-to {
    opacity: 0;
    transform: scale(0.95);
}
</style>
