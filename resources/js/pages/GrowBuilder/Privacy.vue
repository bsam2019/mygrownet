<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const isSubdomain = computed(() => window.location.hostname === 'growbuilder.mygrownet.com');
const homeUrl = computed(() => isSubdomain.value ? '/' : '/growbuilder');
</script>

<template>
    <Head>
        <title>Privacy Policy — GrowBuilder</title>
        <meta name="description" content="GrowBuilder Privacy Policy explaining how we collect, use, and protect your data." />
    </Head>

    <div class="min-h-screen bg-gray-50 text-gray-900 font-sans antialiased">
        <header class="sticky top-0 bg-white/90 backdrop-blur-lg border-b border-gray-100 z-40">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between">
                <Link :href="homeUrl" class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-blue-600 to-blue-700 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                    </div>
                    <span class="text-sm font-bold text-gray-900">GrowBuilder</span>
                </Link>
                <Link :href="homeUrl" class="text-xs text-gray-500 hover:text-gray-700 transition-colors">&larr; Back</Link>
            </div>
        </header>

        <div class="max-w-3xl mx-auto px-4 sm:px-6 py-12">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 sm:p-10 mb-6">
                <h1 class="text-3xl font-extrabold text-gray-900">Privacy Policy</h1>
                <p class="text-gray-500 text-sm mt-2">Last updated: July 22, 2026</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 sm:p-10 space-y-8 text-gray-700 text-sm leading-relaxed">
                <section>
                    <h2 class="text-lg font-bold text-gray-900 mb-3">1. Who We Are</h2>
                    <p>GrowBuilder is operated by Rockshield Investments Limited t/a MyGrowNet ("we", "us", "our"). This Privacy Policy explains how we handle your personal data when you use the GrowBuilder website builder platform.</p>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-gray-900 mb-3">2. Information We Collect</h2>
                    <div class="space-y-4">
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1">Account Information</h3>
                            <p>When you register, we collect your name, email address, and phone number. Payment details are processed by our payment provider and are not stored on our servers.</p>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1">Website Content</h3>
                            <p>All content you create or upload through GrowBuilder — including text, images, files, products, and design settings — is stored on our servers to provide the service.</p>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1">Usage Data</h3>
                            <p>We collect IP addresses, browser type, pages visited, and feature usage to improve the platform and diagnose issues.</p>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1">Visitor Data</h3>
                            <p>If you enable analytics on your website, page view data (URL, referrer, timestamp, approximate location) is collected for your website's analytics dashboard. This data is stored separately from your account data.</p>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-gray-900 mb-3">3. How We Use Your Data</h2>
                    <ul class="list-disc list-inside space-y-1 ml-2">
                        <li>To operate and maintain your websites</li>
                        <li>To process subscription payments</li>
                        <li>To communicate service updates and support requests</li>
                        <li>To improve the platform based on usage patterns</li>
                        <li>To enforce our Terms of Service and prevent abuse</li>
                        <li>To comply with legal obligations</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-gray-900 mb-3">4. Data Sharing</h2>
                    <p>We do not sell your personal data. We may share data with:</p>
                    <ul class="list-disc list-inside space-y-1 ml-2 mt-2">
                        <li><strong>Service providers:</strong> hosting (DigitalOcean), payment processing, email delivery</li>
                        <li><strong>Legal authorities:</strong> when required by Zambian law or to protect our rights</li>
                        <li><strong>Your consent:</strong> when you explicitly authorize us to share specific data</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-gray-900 mb-3">5. Data Security</h2>
                    <p>We encrypt data in transit (TLS) and at rest. Access controls, regular backups, and monitoring are in place. However, no online service is 100% secure. You are responsible for maintaining strong account credentials.</p>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-gray-900 mb-3">6. Data Retention</h2>
                    <p>We retain your account data for as long as your account is active. Website content is retained for 30 days after account closure. Analytics data is retained for 24 months. We may retain certain data longer for legal compliance.</p>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-gray-900 mb-3">7. Your Rights</h2>
                    <p>You have the right to access, correct, export, or delete your personal data. To exercise these rights, contact us at growbuilder@mygrownet.com. We will respond within 30 days.</p>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-gray-900 mb-3">8. Cookies</h2>
                    <p>We use essential cookies for session management and authentication. Optional analytics cookies may be used on the GrowBuilder platform (not on your websites). You can control cookies through your browser settings.</p>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-gray-900 mb-3">9. Third-Party Services</h2>
                    <p>Your websites may include third-party services (analytics, payment gateways, embeds). These services have their own privacy policies. We encourage you to review them before enabling third-party integrations on your site.</p>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-gray-900 mb-3">10. Changes to This Policy</h2>
                    <p>We may update this Privacy Policy. Material changes will be communicated via email or platform notice. Continued use after changes take effect constitutes acceptance.</p>
                </section>

                <section class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900 mb-3">Contact Us</h2>
                    <div class="space-y-1 text-gray-600">
                        <p><strong>Email:</strong> growbuilder@mygrownet.com</p>
                        <p><strong>Privacy:</strong> privacy@mygrownet.com</p>
                        <p><strong>Company:</strong> Rockshield Investments Limited, Lusaka, Zambia</p>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>
