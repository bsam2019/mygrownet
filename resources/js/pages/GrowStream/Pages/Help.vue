<script setup lang="ts">
import PublicPage from '@/components/GrowStream/PublicPage.vue';

const faqs = [
    { q: 'How do I create an account?', a: 'Tap "Sign Up Free" in the top bar. Create an account with just your name and a password — no card required for the free tier.' },
    { q: 'How much does GrowStream cost?', a: 'We have a free tier that lets you watch with ads at standard quality. Paid plans start at K35/month with higher quality, downloads, and offline viewing.' },
    { q: 'How do I pay?', a: 'Pay easily with MTN, Airtel, or Zamtel Mobile Money. You can also cancel or switch plans anytime from your account.' },
    { q: 'Can I watch offline?', a: 'Yes — Downloads are available on paid plans. Save episodes to watch later without using data.' },
    { q: 'How do I become a creator?', a: 'Open the Creator menu and tap "Become a Creator". Submit your profile for approval and start uploading your content once approved.' },
    { q: 'How do creators get paid?', a: 'Creators earn through subscriptions, pay-per-view rentals, and sponsorships. Payouts are processed transparently from your Creator dashboard.' },
    { q: 'First episode free?', a: 'Yes — the first episode of every series is always free so you can try before you subscribe.' },
];

const contactItems = [
    { label: 'Email', value: 'support@mygrownet.com', href: 'mailto:support@mygrownet.com' },
    { label: 'WhatsApp', value: '+260 000 000 000', href: '#' },
    { label: 'Help Center', value: 'Visit Contact Us', href: route('growstream.pages.contact') },
];
</script>

<template>
    <PublicPage title="Help Center" subtitle="Everything you need to get the most out of GrowStream.">
        <section class="space-y-3">
            <div v-for="(f, i) in faqs" :key="i" class="bg-surface-container rounded-xl p-5 border border-outline-variant/60">
                <h2 class="font-label-md text-label-md text-on-surface mb-2">{{ f.q }}</h2>
                <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">{{ f.a }}</p>
            </div>
        </section>

        <section class="bg-surface-container rounded-xl p-6 border border-outline-variant/60">
            <h2 class="font-headline-md text-headline-md mb-4">Still need help?</h2>
            <div class="space-y-3">
                <a v-for="c in contactItems" :key="c.label" :href="c.href" class="flex items-center justify-between gap-4 rounded-lg px-4 py-3 bg-surface-container-low hover:bg-surface-container-high transition-colors">
                    <span class="font-label-md text-label-md text-on-surface">{{ c.label }}</span>
                    <span class="font-label-md text-label-md text-primary">{{ c.value }}</span>
                </a>
            </div>
        </section>
    </PublicPage>
</template>
