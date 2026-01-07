<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import GeopamuLayout from '@/Layouts/GeopamuLayout.vue';
import HeroSection from '@/Components/Geopamu/HeroSection.vue';
import ServicesOverview from '@/Components/Geopamu/ServicesOverview.vue';
import FeaturedWork from '@/Components/Geopamu/FeaturedWork.vue';
import WhyChooseUs from '@/Components/Geopamu/WhyChooseUs.vue';
import CallToAction from '@/Components/Geopamu/CallToAction.vue';
</script>

<template>
  <Head title="Geopamu - Professional Printing & Branding Solutions" />
  
  <GeopamuLayout>
    <HeroSection />
    <ServicesOverview />
    <FeaturedWork />
    <WhyChooseUs />
    <CallToAction />
  </GeopamuLayout>
</template>
