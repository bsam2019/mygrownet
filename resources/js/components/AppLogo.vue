<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground drop-shadow-[0_0_10px_rgba(37,99,235,0.5)]">
        <AppLogoIcon class="size-8 fill-current text-white dark:text-black" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate font-semibold leading-none text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-blue-600 drop-shadow-[0_0_8px_rgba(37,99,235,0.4)]">MyGrowNet</span>
        <span class="text-xs text-muted-foreground truncate">Growth Platform</span>
    </div>
</template>
