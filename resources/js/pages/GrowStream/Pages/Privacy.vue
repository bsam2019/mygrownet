<script setup lang="ts">
import PublicPage from '@/components/GrowStream/PublicPage.vue';

const updated = 'August 2026';

const sections = [
    {
        heading: 'What We Collect',
        body: 'We collect the information you provide when creating an account (name, email, password) and data about how you use the Service (watch history, device info, payments via our providers).',
    },
    {
        heading: 'How We Use Your Data',
        body: 'We use your data to provide the Service, personalise recommendations, process payments, show relevant content, and improve the platform. We never sell your personal information.',
    },
    {
        heading: 'Payments & Mobile Money',
        body: 'Payments are processed by our payment partners (MTN, Airtel, Zamtel). We only receive confirmation of successful payments — we do not store your mobile money PIN or banking details.',
    },
    {
        heading: 'Cookies & Local Storage',
        body: 'We use cookies and local storage to keep you signed in and remember your preferences, including data saver mode and watch progress.',
    },
    {
        heading: 'Your Rights',
        body: 'You may access, correct, or delete your personal information, and export or request deletion of your watch data at any time by contacting support@mygrownet.com.',
    },
    {
        heading: 'Data Retention',
        body: 'We retain account and billing data for as long as required to provide the Service and comply with legal and financial obligations. Watch history is retained while your account is active.',
    },
    {
        heading: 'Contact',
        body: 'Privacy questions? Email support@mygrownet.com.',
    },
];
</script>

<template>
    <PublicPage title="Privacy Policy" :subtitle="`Last updated: ${updated}`">
        <section class="space-y-5">
            <div v-for="s in sections" :key="s.heading" class="bg-surface-container rounded-xl p-6 border border-outline-variant/60">
                <h2 class="font-headline-md text-headline-md mb-3">{{ s.heading }}</h2>
                <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">{{ s.body }}</p>
            </div>
        </section>
    </PublicPage>
</template>
