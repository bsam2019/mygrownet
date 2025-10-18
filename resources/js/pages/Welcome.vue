<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-blue-50">
    <Navigation />
    <Hero />
    <FeatureHighlights />
    <ProfessionalLevels />
    <MyGrowNetStats />
    <HowItWorks />
    <RewardSystem />
    <SuccessStories />
    <CallToAction />
    <Footer />
  </div>
</template>

<script setup>
import Navigation from '@/components/custom/Navigation.vue';
import Hero from '@/components/custom/Hero.vue';
import FeatureHighlights from '@/components/custom/FeatureHighlights.vue';
import ProfessionalLevels from '@/components/custom/ProfessionalLevels.vue';
import MyGrowNetStats from '@/components/custom/MyGrowNetStats.vue';
import HowItWorks from '@/components/custom/HowItWorks.vue';
import RewardSystem from '@/components/custom/RewardSystem.vue';
import SuccessStories from '@/components/custom/SuccessStories.vue';
import CallToAction from '@/components/custom/CallToAction.vue';
import Footer from '@/components/custom/Footer.vue';
</script>
