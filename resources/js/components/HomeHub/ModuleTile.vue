<template>
  <component
    :is="comingSoon ? 'div' : Link"
    :href="comingSoon ? undefined : href"
    class="relative aspect-square rounded-2xl flex flex-col items-center justify-center p-4 transition-all duration-200 hover:scale-105 hover:shadow-lg active:scale-95"
    :class="[bgColor, comingSoon ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer']"
  >
    <!-- Coming Soon Badge -->
    <div
      v-if="comingSoon"
      class="absolute top-2 right-2 bg-white/30 backdrop-blur-sm text-white text-[10px] font-bold px-2 py-0.5 rounded-full"
    >
      Soon
    </div>

    <!-- Icon -->
    <div class="mb-3">
      <component
        :is="icon"
        class="h-10 w-10 text-white"
        aria-hidden="true"
      />
    </div>

    <!-- Title -->
    <span class="text-white text-sm font-semibold text-center leading-tight">
      {{ title }}
    </span>
  </component>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface Props {
  title: string;
  icon: any;
  bgColor: string;
  href: string;
  comingSoon?: boolean;
}

withDefaults(defineProps<Props>(), {
  comingSoon: false,
});
</script>
