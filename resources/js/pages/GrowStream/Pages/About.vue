<script setup lang="ts">
import PublicPage from '@/components/GrowStream/PublicPage.vue';

const sections = [
    {
        heading: 'Our Story',
        body: 'GrowStream is a Zambian streaming platform built for local creators. We started with a simple belief: Zambian stories deserve a stage. Movies, comedy, skits, dramas, soaps, documentaries, and series — made by Zambians, for Zambians, and watched by the world.',
    },
    {
        heading: 'What We Do',
        body: 'We give creators the tools to publish, monetize, and grow an audience — subscriptions, pay-per-view rentals, sponsorships, and transparent payouts. Viewers get premium local entertainment with plans starting at K35/month, payable with MTN, Airtel, or Zamtel Mobile Money.',
    },
    {
        heading: 'Why We Exist',
        body: 'Most streaming platforms carry foreign catalogues that ignore local talent. GrowStream is the opposite: our catalogue is built by the community we serve. Every subscription directly supports the creators you watch.',
    },
    {
        heading: 'Our Values',
        body: 'Local first. Creators are partners, not commodities. Transparency in payments and analytics. Accessibility for everyone — free tier available, no bank card required to start.',
    },
];
</script>

<template>
    <PublicPage title="About GrowStream" subtitle="Watch. Laugh. Binge. — Made in Zambia.">
        <section v-for="(s, i) in sections" :key="s.heading" class="bg-surface-container rounded-xl p-6 border border-outline-variant/60">
            <h2 class="font-headline-md text-headline-md mb-3">{{ s.heading }}</h2>
            <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">{{ s.body }}</p>
        </section>
    </PublicPage>
</template>
