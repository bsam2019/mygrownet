<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import GeopamuNavigation from '@/Components/Geopamu/GeopamuNavigation.vue';
import GeopamuFooter from '@/Components/Geopamu/GeopamuFooter.vue';
</script>

<template>
  <div class="min-h-screen bg-white">
    <GeopamuNavigation />
    
    <main>
      <slot />
    </main>

    <GeopamuFooter />
  </div>
</template>
