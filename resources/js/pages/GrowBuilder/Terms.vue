<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const isSubdomain = computed(() => window.location.hostname === 'growbuilder.mygrownet.com');
const homeUrl = computed(() => isSubdomain.value ? '/' : '/growbuilder');
</script>

<template>
    <Head>
        <title>Terms of Service — GrowBuilder</title>
        <meta name="description" content="GrowBuilder Terms of Service governing the use of our website builder platform." />
    </Head>

    <div class="min-h-screen bg-gray-50 text-gray-900 font-sans antialiased">
        <!-- Minimal header -->
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
                <h1 class="text-3xl font-extrabold text-gray-900">Terms of Service</h1>
                <p class="text-gray-500 text-sm mt-2">Last updated: July 22, 2026</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 sm:p-10 space-y-8 text-gray-700 text-sm leading-relaxed">
                <section>
                    <h2 class="text-lg font-bold text-gray-900 mb-3">1. Acceptance of Terms</h2>
                    <p>These Terms of Service ("Terms") govern your use of GrowBuilder, a website building platform provided by Rockshield Investments Limited t/a MyGrowNet ("we", "us", "our"). By using GrowBuilder, you agree to these Terms.</p>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-gray-900 mb-3">2. Description of Service</h2>
                    <p>GrowBuilder provides a drag-and-drop website builder, hosting, custom domain support, e-commerce functionality, and related tools. Your website content is stored on our servers and made accessible via a subdomain or your own domain.</p>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-gray-900 mb-3">3. Account &amp; Registration</h2>
                    <p>You must create an account to use GrowBuilder. You are responsible for maintaining the confidentiality of your credentials. You must be at least 18 years old or have parental consent.</p>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-gray-900 mb-3">4. User Content</h2>
                    <p>You retain ownership of all content you create using GrowBuilder. By publishing content through our platform, you grant us a non-exclusive, worldwide license to host, store, and display your content solely for the purpose of providing the service.</p>
                    <p class="mt-2">You represent that your content does not violate any laws or third-party rights. Prohibited content includes but is not limited to: illegal material, hate speech, malware, phishing pages, and content that infringes intellectual property rights.</p>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-gray-900 mb-3">5. Acceptable Use</h2>
                    <p>You agree not to:</p>
                    <ul class="list-disc list-inside mt-2 space-y-1 ml-2">
                        <li>Use GrowBuilder for any unlawful purpose</li>
                        <li>Attempt to circumvent our security measures</li>
                        <li>Host content that harms minors or depicts exploitation</li>
                        <li>Distribute malware or engage in phishing</li>
                        <li>Use automated tools to abuse the platform</li>
                        <li>Violate the intellectual property rights of others</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-gray-900 mb-3">6. Payment &amp; Subscriptions</h2>
                    <p>Subscription fees are billed in Zambian Kwacha (ZMW) on a monthly or annual basis. Fees are non-refundable except as required by law. We may change pricing with 30 days notice. Non-payment may result in suspension or termination of your website.</p>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-gray-900 mb-3">7. Domains</h2>
                    <p>Free subdomains (yourname.mygrownet.com) are provided at no cost. We reserve the right to reclaim inactive subdomains. Custom domains are your responsibility to register and renew. We do not provide domain registration services.</p>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-gray-900 mb-3">8. Intellectual Property</h2>
                    <p>GrowBuilder, its logo, templates, and platform code are owned by Rockshield Investments Limited. Templates are provided as a starting point — modifications become your content. You may not resell or redistribute platform templates in their original form.</p>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-gray-900 mb-3">9. Limitation of Liability</h2>
                    <p>GrowBuilder is provided "as is" without warranties. To the maximum extent permitted by law, we are not liable for indirect damages, data loss, or service interruptions. Our total liability shall not exceed the fees paid by you in the 12 months preceding a claim.</p>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-gray-900 mb-3">10. Termination</h2>
                    <p>We may suspend or terminate your account for violation of these Terms. Upon termination, your websites will be taken offline. We encourage you to export your content before termination. We retain backups for 30 days after account closure.</p>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-gray-900 mb-3">11. Changes to Terms</h2>
                    <p>We may update these Terms at any time. Material changes will be notified via email or platform notice. Continued use after changes constitutes acceptance.</p>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-gray-900 mb-3">12. Governing Law</h2>
                    <p>These Terms are governed by the laws of the Republic of Zambia. Disputes shall be resolved in the courts of Lusaka, Zambia.</p>
                </section>

                <section class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900 mb-3">Contact</h2>
                    <div class="space-y-1 text-gray-600">
                        <p><strong>Email:</strong> growbuilder@mygrownet.com</p>
                        <p><strong>Company:</strong> Rockshield Investments Limited, Lusaka, Zambia</p>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>
