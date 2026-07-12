<script setup lang="ts">
import BizBoostGuestLayout from '@/layouts/BizBoostGuestLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const user = computed(() => usePage().props.auth?.user);
const homeUrl = computed(() => user.value ? '/dashboard' : route('bizboost.sub.welcome'));
</script>

<template>
    <BizBoostGuestLayout>
        <Head title="Privacy Policy - BizBoost" />

        <div class="min-h-screen bg-gradient-to-b from-violet-50 via-white to-white py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <Link :href="homeUrl" class="inline-flex items-center mb-6 text-sm font-medium text-purple-600 hover:text-purple-700">
                    <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to home
                </Link>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 mb-8">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-600 to-purple-700 flex items-center justify-center shadow-sm">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Privacy Policy</h1>
                            <p class="text-gray-500">Last Updated: October 31, 2025</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 space-y-10">
                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-3">1. Information We Collect</h2>
                        <p class="text-gray-600 leading-relaxed">
                            We collect information you provide when creating an account (name, email, phone number),
                            and data you generate while using BizBoost (business information, posts, customer data,
                            analytics). We also collect usage data to improve our service.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-3">2. How We Use Your Information</h2>
                        <p class="text-gray-600 leading-relaxed">
                            Your information is used to provide and improve BizBoost services, process transactions,
                            send service-related communications, and comply with legal obligations. We do not sell
                            your personal data.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-3">3. Data Security</h2>
                        <p class="text-gray-600 leading-relaxed">
                            We implement industry-standard security measures to protect your data, including encryption
                            in transit and at rest, access controls, and regular security audits.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-3">4. Data Retention</h2>
                        <p class="text-gray-600 leading-relaxed">
                            We retain your data for as long as your account is active. Upon account deletion, we
                            delete or anonymize your data within 90 days, except where retention is required by law.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-3">5. Your Rights</h2>
                        <p class="text-gray-600 leading-relaxed">
                            You have the right to access, correct, or delete your personal data. You may also
                            export your data or restrict processing. To exercise these rights, contact us at
                            <a href="mailto:support@mygrownet.com" class="text-purple-600 hover:text-purple-700">support@mygrownet.com</a>.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-3">6. Contact</h2>
                        <p class="text-gray-600 leading-relaxed">
                            For privacy-related inquiries, contact us at
                            <a href="mailto:support@mygrownet.com" class="text-purple-600 hover:text-purple-700">support@mygrownet.com</a>.
                        </p>
                    </section>
                </div>
            </div>
        </div>
    </BizBoostGuestLayout>
</template>
