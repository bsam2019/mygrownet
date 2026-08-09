<script setup lang="ts">
import PublicPage from '@/components/GrowStream/PublicPage.vue';

const updated = 'August 2026';

const sections = [
    {
        heading: '1. Acceptance of Terms & Universal Scope',
        body: 'By creating an account, subscribing, or operating a Creator Platform Hub on GrowStream ("Service"), you agree to these Master Terms of Service. These Terms govern public video streaming, B2B Creator Hubs hosted on *.mygrownet.com subdomains, and connected custom CNAME domains.',
    },
    {
        heading: '2. Accounts & Centralized Identity',
        body: 'GrowStream utilizes MyGrowNet\'s centralized Single Sign-On (SSO) system. You access all platform services, Creator Hub workspaces, and tenant portals under a single user identity. You are responsible for safeguarding your credentials and for all activities conducted under your account.',
    },
    {
        heading: '3. Creator Hub B2B Platform Terms & SLAs',
        body: 'Subscribers activating a Creator Hub receive a hosted subdomain or connect a custom CNAME domain. GrowStream targets 99.5% monthly endpoint uptime. Storage minutes and delivery bandwidth are metered in real-time. Tenants agree to comply with our Acceptable Use Policy (AUP) prohibiting unlawful, defamatory, or harmful content.',
    },
    {
        heading: '4. Financial Policy, BYOP & Treasury Settlement',
        body: 'GrowStream supports two financial payment modes: (a) GrowStream-Managed Rails, where collected funds are held in escrow and remitted weekly (minimum K250 threshold via MTN/Airtel/Zamtel Mobile Money or Bank EFT) subject to statutory Zambian Withholding Tax (15% WHT); and (b) Bring Your Own Payment (BYOP), where payments settle directly into the Tenant\'s Paystack, Flutterwave, or PawaPay merchant account, with platform fees billed via subscription.',
    },
    {
        heading: '5. Copyright, Piracy & Notice-and-Takedown Policy',
        body: 'In compliance with the Zambian Copyright and Performance Rights Act (Cap 406) and international DMCA/EU standards, all uploaded videos undergo automated fingerprinting via Cloudflare Stream. Infringing assets will be disabled within 24 hours of receiving a valid takedown notice. Tenants accumulating 3 copyright strikes are subject to platform termination and subdomain revocation.',
    },
    {
        heading: '6. Data Protection & Privacy (Zambia DPA 2021 & GDPR)',
        body: 'GrowStream strictly enforces the Zambian Data Protection Act No. 4 of 2021 and GDPR principles. Tenant customer data is strictly isolated to prevent cross-tenant access. End-users retain statutory rights to Access, Rectify, and Request Erasure of their personal data.',
    },
    {
        heading: '7. Limitation of Liability',
        body: 'The Service is provided "as is." To the maximum extent permitted by law, MyGrowNet and GrowStream are not liable for indirect, incidental, or consequential damages arising from service interruptions or third-party domain misconfigurations.',
    },
    {
        heading: '8. Changes & Contact',
        body: 'We may update these Terms from time to time. Material updates will be communicated via email or dashboard notification. Questions regarding legal compliance? Contact legal@mygrownet.com.',
    },
];
</script>

<template>
    <PublicPage title="Terms of Service & Platform Policies" :subtitle="`Last updated: ${updated}`">
        <section class="space-y-5">
            <div v-for="s in sections" :key="s.heading" class="bg-surface-container rounded-xl p-6 border border-outline-variant/60">
                <h2 class="font-headline-md text-headline-md mb-3 text-primary">{{ s.heading }}</h2>
                <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">{{ s.body }}</p>
            </div>
        </section>
    </PublicPage>
</template>
