<script setup lang="ts">
import { computed } from 'vue';

const logoUrl = computed(() => '/logo.png');
</script>

<template>
    <img 
        :src="logoUrl" 
        alt="MyGrowNet Logo" 
        class="w-32 h-32 object-contain"
        style="filter: drop-shadow(0 0 20px rgba(37, 99, 235, 0.7)) drop-shadow(0 0 10px rgba(37, 99, 235, 0.5));"
    />
</template>
