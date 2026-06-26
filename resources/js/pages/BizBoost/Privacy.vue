<script setup lang="ts">
import BizBoostGuestLayout from '@/layouts/BizBoostGuestLayout.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <BizBoostGuestLayout>
        <div class="min-h-screen bg-gray-50 py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <Link :href="route('bizboost.sub.welcome')" class="inline-flex items-center mb-6 text-sm font-medium text-gray-600 hover:text-gray-900">
                    <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to home
                </Link>

                <div class="bg-white rounded-lg shadow-sm p-8 mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">Privacy Policy</h1>
                    <p class="text-gray-600">Last Updated: October 31, 2025</p>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-8 space-y-8">
                    <section>
                        <h2 class="text-2xl font-semibold text-gray-900 mb-4">1. Information We Collect</h2>
                        <p class="text-gray-700 leading-relaxed">
                            We collect information you provide when creating an account (name, email, phone number),
                            and data you generate while using BizBoost (business information, posts, customer data,
                            analytics). We also collect usage data to improve our service.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-semibold text-gray-900 mb-4">2. How We Use Your Information</h2>
                        <p class="text-gray-700 leading-relaxed">
                            Your information is used to provide and improve BizBoost services, process transactions,
                            send service-related communications, and comply with legal obligations. We do not sell
                            your personal data.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-semibold text-gray-900 mb-4">3. Data Security</h2>
                        <p class="text-gray-700 leading-relaxed">
                            We implement industry-standard security measures to protect your data, including encryption
                            in transit and at rest, access controls, and regular security audits.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-semibold text-gray-900 mb-4">4. Data Retention</h2>
                        <p class="text-gray-700 leading-relaxed">
                            We retain your data for as long as your account is active. Upon account deletion, we
                            delete or anonymize your data within 90 days, except where retention is required by law.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-semibold text-gray-900 mb-4">5. Contact</h2>
                        <p class="text-gray-700 leading-relaxed">
                            For privacy-related inquiries, contact us at support@mygrownet.com.
                        </p>
                    </section>
                </div>
            </div>
        </div>
    </BizBoostGuestLayout>
</template>
