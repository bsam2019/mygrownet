<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import GrowNetGuestLayout from '@/layouts/GrowNetGuestLayout.vue';
</script>

<template>
    <GrowNetGuestLayout>
        <Head title="Terms of Service - GrowNet" />
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-100 text-violet-700 text-sm font-medium mb-6">Terms of Service</div>
            <h1 class="text-4xl font-bold text-slate-900 mb-8">Terms of Service</h1>
            <div class="prose prose-slate max-w-none">
                <p class="text-slate-600 leading-relaxed">Last updated: January 2026</p>
                <h2 class="text-xl font-semibold text-slate-900 mt-8">1. Acceptance of Terms</h2>
                <p class="text-slate-600 leading-relaxed">By accessing or using GrowNet ("the Platform"), you agree to be bound by these Terms of Service. If you do not agree, please do not use the Platform.</p>

                <h2 class="text-xl font-semibold text-slate-900 mt-8">2. Account Registration</h2>
                <p class="text-slate-600 leading-relaxed">You must create an account to access certain features. You are responsible for maintaining the confidentiality of your login credentials and for all activities under your account.</p>

                <h2 class="text-xl font-semibold text-slate-900 mt-8">3. Content Usage</h2>
                <p class="text-slate-600 leading-relaxed">GrowNet provides access to lessons, videos, e-books, and other digital content for personal, non-commercial use. You may not redistribute, sell, or license content without prior written permission.</p>

                <h2 class="text-xl font-semibold text-slate-900 mt-8">4. User Conduct</h2>
                <p class="text-slate-600 leading-relaxed">You agree not to misuse the Platform, including attempting to access restricted areas, interfering with other users, or engaging in illegal activities.</p>

                <h2 class="text-xl font-semibold text-slate-900 mt-8">5. Payments & Subscriptions</h2>
                <p class="text-slate-600 leading-relaxed">Premium features require a paid subscription. Fees are billed in advance and are non-refundable unless required by applicable law. We may change pricing with 30 days' notice.</p>

                <h2 class="text-xl font-semibold text-slate-900 mt-8">6. Termination</h2>
                <p class="text-slate-600 leading-relaxed">We may suspend or terminate your account for violations of these terms. You may cancel your account at any time through your account settings.</p>

                <h2 class="text-xl font-semibold text-slate-900 mt-8">7. Limitation of Liability</h2>
                <p class="text-slate-600 leading-relaxed">GrowNet and MyGrowNet are not liable for indirect, incidental, or consequential damages arising from your use of the Platform.</p>

                <h2 class="text-xl font-semibold text-slate-900 mt-8">8. Changes to Terms</h2>
                <p class="text-slate-600 leading-relaxed">We may update these terms from time to time. Continued use of the Platform after changes constitutes acceptance of the new terms.</p>

                <h2 class="text-xl font-semibold text-slate-900 mt-8">9. Contact</h2>
                <p class="text-slate-600 leading-relaxed">Questions about these terms? <Link href="/contact" class="text-violet-600 hover:text-violet-700 font-medium">Contact us</Link>.</p>
            </div>
        </div>
    </GrowNetGuestLayout>
</template>
