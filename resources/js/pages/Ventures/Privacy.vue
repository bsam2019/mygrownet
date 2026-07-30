<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import VenturesGuestLayout from '@/layouts/VenturesGuestLayout.vue';
</script>

<template>
    <VenturesGuestLayout>
        <Head title="Privacy Policy - Venture Builder" />
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="mb-8">
                <Link :href="route('venture.sub.welcome')" class="text-sm text-amber-600 hover:text-amber-700 font-medium">&larr; Back to home</Link>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-6">Privacy Policy</h1>
            <p class="text-sm text-gray-500 mb-8">Last updated: January 2026</p>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-3">1. Information We Collect</h2>
                <p class="text-gray-600 leading-relaxed mb-3">We collect information you provide directly, including:</p>
                <ul class="list-disc pl-6 text-gray-600 space-y-2">
                    <li>Account registration details (name, email, phone number)</li>
                    <li>Identity verification documents (government-issued ID, proof of address)</li>
                    <li>Financial information for investment purposes</li>
                    <li>Investment preferences and portfolio data</li>
                    <li>Communications with our support team</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-3">2. How We Use Your Information</h2>
                <p class="text-gray-600 leading-relaxed mb-3">We use your information to:</p>
                <ul class="list-disc pl-6 text-gray-600 space-y-2">
                    <li>Process and manage your investments</li>
                    <li>Verify your identity and comply with legal requirements</li>
                    <li>Communicate about your investments and platform updates</li>
                    <li>Improve and personalize your experience</li>
                    <li>Detect and prevent fraudulent activity</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-3">3. Data Sharing</h2>
                <p class="text-gray-600 leading-relaxed">
                    We may share your information with venture operators for investment administration,
                    with regulatory authorities as required by law, and with service providers who assist in
                    platform operations (payment processing, identity verification, document storage).
                    We do not sell your personal information to third parties.
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-3">4. Data Security</h2>
                <p class="text-gray-600 leading-relaxed">
                    We implement industry-standard security measures including encryption, access controls,
                    and regular security audits to protect your information. However, no method of electronic
                    storage is 100% secure, and we cannot guarantee absolute security.
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-3">5. Data Retention</h2>
                <p class="text-gray-600 leading-relaxed">
                    We retain your information for as long as your account is active and for a reasonable period
                    thereafter to comply with legal obligations, resolve disputes, and enforce agreements.
                    You may request deletion of your data by contacting our support team.
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-3">6. Your Rights</h2>
                <p class="text-gray-600 leading-relaxed mb-3">You have the right to:</p>
                <ul class="list-disc pl-6 text-gray-600 space-y-2">
                    <li>Access your personal data held by us</li>
                    <li>Request correction of inaccurate data</li>
                    <li>Request deletion of your data (subject to legal requirements)</li>
                    <li>Withdraw consent for data processing where applicable</li>
                    <li>Lodge a complaint with the relevant data protection authority</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-3">7. Cookies</h2>
                <p class="text-gray-600 leading-relaxed">
                    We use essential cookies for authentication and platform functionality. Analytics cookies
                    help us understand usage patterns. You can control cookie preferences through your browser settings.
                    Disabling certain cookies may affect platform functionality.
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-3">8. Changes to This Policy</h2>
                <p class="text-gray-600 leading-relaxed">
                    We may update this policy periodically. Changes will be posted here with an updated revision date.
                    Material changes will be communicated via email or platform notification.
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-3">9. Contact Us</h2>
                <p class="text-gray-600 leading-relaxed">
                    For questions about this policy or your data, contact our Data Protection Officer at
                    <a href="mailto:privacy@mygrownet.com" class="text-amber-600 hover:text-amber-700">privacy@mygrownet.com</a>
                    or write to MyGrowNet, Lusaka, Zambia.
                </p>
            </section>
        </div>
    </VenturesGuestLayout>
</template>
