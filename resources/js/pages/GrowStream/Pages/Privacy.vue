<script setup lang="ts">
import PublicPage from '@/components/GrowStream/PublicPage.vue';

const updated = 'August 2026';

const sections = [
    {
        heading: '1. Information We Collect',
        body: 'We collect account identification (name, email, phone number), authentication metadata, video watch metrics, subscription transaction logs, and technical telemetry (IP address, browser type). For Creator Hub tenants, we process organization details, custom domain bindings, and payment gateway credentials.',
    },
    {
        heading: '2. How We Use Information',
        body: 'We process personal information to operate public video streaming, authenticate users via MyGrowNet SSO, calculate watch-time analytics, process payments and BYOP payouts, enforce video access controls, and detect fraudulent or copyright-infringing activity.',
    },
    {
        heading: '3. Data Isolation & Tenant Privacy',
        body: 'Creator Hub operates a multi-tenant isolation model. Customer records, student progress, and subscription analytics for a specific academy (e.g. mrbanda.mygrownet.com) are strictly isolated. No tenant can access another tenant\'s user data.',
    },
    {
        heading: '4. Statutory Compliance (Zambia DPA 2021 & GDPR)',
        body: 'We comply with the Zambian Data Protection Act No. 4 of 2021 and GDPR standards. Data subjects have statutory rights to: (a) Access personal data; (b) Request correction of inaccurate information; and (c) Request data erasure, subject to financial accounting retention mandates.',
    },
    {
        heading: '5. Security & Encryption',
        body: 'All data in transit is protected using TLS 1.3 encryption. Tenant payment credentials for Bring Your Own Payment (BYOP) integrations are stored strictly encrypted using AES-256 GCM.',
    },
    {
        heading: '6. Data Protection Officer',
        body: 'For privacy inquiries, data access requests, or regulatory communications, contact our Data Protection Officer at privacy@mygrownet.com.',
    },
];
</script>

<template>
    <PublicPage title="Privacy Policy & Data Protection" :subtitle="`Last updated: ${updated}`">
        <section class="space-y-5">
            <div v-for="s in sections" :key="s.heading" class="bg-surface-container rounded-xl p-6 border border-outline-variant/60">
                <h2 class="font-headline-md text-headline-md mb-3 text-primary">{{ s.heading }}</h2>
                <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">{{ s.body }}</p>
            </div>
        </section>
    </PublicPage>
</template>
