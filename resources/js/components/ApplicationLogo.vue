<script setup lang="ts">
import { computed } from 'vue';

const logoUrl = computed(() => '/logo.png');
</script>

<template>
    <img 
        :src="logoUrl" 
        alt="MyGrowNet Logo" 
        class="w-32 h-32 object-contain drop-shadow-[0_0_20px_rgba(37,99,235,0.6)] hover:drop-shadow-[0_0_30px_rgba(37,99,235,0.8)] transition-all duration-300"
    />
</template>
