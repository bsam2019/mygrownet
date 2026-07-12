<script setup lang="ts">
import BizBoostGuestLayout from '@/layouts/BizBoostGuestLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const user = computed(() => usePage().props.auth?.user);
const homeUrl = computed(() => user.value ? '/dashboard' : route('bizboost.sub.welcome'));
</script>

<template>
    <BizBoostGuestLayout>
        <Head title="Terms of Service - BizBoost" />

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
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Terms of Service</h1>
                            <p class="text-gray-500">Last Updated: October 31, 2025</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 space-y-10">
                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-3">1. Acceptance of Terms</h2>
                        <p class="text-gray-600 leading-relaxed">
                            By accessing or using BizBoost ("the Service"), you agree to be bound by these Terms of Service.
                            If you do not agree, do not use the Service. BizBoost is a product of MyGrowNet, operated by
                            Rockshield Investments Limited.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-3">2. Description of Service</h2>
                        <p class="text-gray-600 leading-relaxed">
                            BizBoost provides small and medium enterprises with marketing, growth, and business management
                            tools including social media management, content generation, customer relationship management,
                            and analytics.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-3">3. User Accounts</h2>
                        <p class="text-gray-600 leading-relaxed">
                            Your BizBoost account is tied to your MyGrowNet account. You are responsible for maintaining
                            the confidentiality of your login credentials and for all activities under your account.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-3">4. Subscriptions & Billing</h2>
                        <p class="text-gray-600 leading-relaxed">
                            Paid features require a subscription. Fees are billed in advance and are non-refundable
                            except as required by applicable law. We may change pricing with notice.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-3">5. Limitation of Liability</h2>
                        <p class="text-gray-600 leading-relaxed">
                            BizBoost is provided "as is" without warranty. To the maximum extent permitted by law,
                            Rockshield Investments Limited shall not be liable for any indirect, incidental, or
                            consequential damages.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-3">6. Contact</h2>
                        <p class="text-gray-600 leading-relaxed">
                            For questions about these terms, contact us at
                            <a href="mailto:support@mygrownet.com" class="text-purple-600 hover:text-purple-700">support@mygrownet.com</a>.
                        </p>
                    </section>
                </div>
            </div>
        </div>
    </BizBoostGuestLayout>
</template>
