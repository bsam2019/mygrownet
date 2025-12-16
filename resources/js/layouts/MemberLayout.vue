<script setup lang="ts">
/**
 * MemberLayout - Alias for ClientLayout
 * 
 * This file exists for backward compatibility.
 * All functionality has been moved to ClientLayout.
 */
import ClientLayout from '@/layouts/ClientLayout.vue';
</script>

<template>
    <ClientLayout>
        <slot />
    </ClientLayout>
</template>
