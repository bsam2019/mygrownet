<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-blue-50">
    <Navigation />
    <Hero />
    <FeaturedApps v-if="featuredApps && featuredApps.length > 0" :apps="featuredApps" />
    <FeatureHighlights />
    <HowItWorks />
    <SuccessStories />
    <CallToAction />
    <Footer />
  </div>
</template>

<script setup>
import Navigation from '@/components/custom/Navigation.vue';
import Hero from '@/components/custom/Hero.vue';
import FeaturedApps from '@/components/custom/FeaturedApps.vue';
import FeatureHighlights from '@/components/custom/FeatureHighlights.vue';
import HowItWorks from '@/components/custom/HowItWorks.vue';
import SuccessStories from '@/components/custom/SuccessStories.vue';
import CallToAction from '@/components/custom/CallToAction.vue';
import Footer from '@/components/custom/Footer.vue';

defineProps({
  featuredApps: {
    type: Array,
    default: () => []
  }
});
</script>
