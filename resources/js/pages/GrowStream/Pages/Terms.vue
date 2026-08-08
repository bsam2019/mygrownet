<script setup lang="ts">
import PublicPage from '@/components/GrowStream/PublicPage.vue';

const updated = 'August 2026';

const sections = [
    {
        heading: '1. Acceptance of Terms',
        body: 'By creating an account or using GrowStream (the "Service"), you agree to these Terms of Service. If you do not agree, please do not use the Service.',
    },
    {
        heading: '2. Accounts & Eligibility',
        body: 'You must be at least 13 years old to use the Service. You are responsible for safeguarding your account credentials and for all activity that occurs under your account.',
    },
    {
        heading: '3. Subscriptions & Billing',
        body: 'Paid plans are billed on a monthly or annual basis via supported mobile money providers. You can upgrade, downgrade, or cancel at any time. Cancellation takes effect at the end of your current billing cycle.',
    },
    {
        heading: '4. Content & Conduct',
        body: 'The Service hosts user-generated content from approved creators. You agree not to upload unlawful, infringing, or harmful content, and not to attempt to circumvent access controls or payment mechanisms.',
    },
    {
        heading: '5. Payments to Creators',
        body: 'Creators earn revenue through subscriptions, rentals, and sponsorships based on the Service\'s payout policy. All payouts are subject to applicable taxes and verification requirements.',
    },
    {
        heading: '6. Intellectual Property',
        body: 'GrowStream and its platform are owned by MyGrowNet. Creator content remains the property of its respective creators, who license it for distribution on the platform.',
    },
    {
        heading: '7. Limitation of Liability',
        body: 'The Service is provided "as is." To the maximum extent permitted by law, MyGrowNet is not liable for indirect, incidental, or consequential damages arising from your use of the Service.',
    },
    {
        heading: '8. Changes to These Terms',
        body: 'We may update these Terms from time to time. Material changes will be communicated to you before they take effect.',
    },
    {
        heading: '9. Contact',
        body: 'Questions about these Terms? Reach us at support@mygrownet.com.',
    },
];
</script>

<template>
    <PublicPage title="Terms of Service" :subtitle="`Last updated: ${updated}`">
        <section class="space-y-5">
            <div v-for="s in sections" :key="s.heading" class="bg-surface-container rounded-xl p-6 border border-outline-variant/60">
                <h2 class="font-headline-md text-headline-md mb-3">{{ s.heading }}</h2>
                <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">{{ s.body }}</p>
            </div>
        </section>
    </PublicPage>
</template>
