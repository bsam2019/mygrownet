<script setup lang="ts">
import { computed } from 'vue';

const logoUrl = computed(() => '/logo.png');
</script>

<template>
    <img 
        :src="logoUrl" 
        alt="MyGrowNet Logo" 
        class="w-32 h-32 object-contain"
    />
</template>
