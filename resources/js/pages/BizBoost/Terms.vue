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
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">Terms of Service</h1>
                    <p class="text-gray-600">Last Updated: October 31, 2025</p>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-8 space-y-8">
                    <section>
                        <h2 class="text-2xl font-semibold text-gray-900 mb-4">1. Acceptance of Terms</h2>
                        <p class="text-gray-700 leading-relaxed">
                            By accessing or using BizBoost ("the Service"), you agree to be bound by these Terms of Service.
                            If you do not agree, do not use the Service. BizBoost is a product of MyGrowNet, operated by
                            Rockshield Investments Limited.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-semibold text-gray-900 mb-4">2. Description of Service</h2>
                        <p class="text-gray-700 leading-relaxed">
                            BizBoost provides small and medium enterprises with marketing, growth, and business management
                            tools including social media management, content generation, customer relationship management,
                            and analytics.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-semibold text-gray-900 mb-4">3. User Accounts</h2>
                        <p class="text-gray-700 leading-relaxed">
                            Your BizBoost account is tied to your MyGrowNet account. You are responsible for maintaining
                            the confidentiality of your login credentials and for all activities under your account.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-semibold text-gray-900 mb-4">4. Subscriptions & Billing</h2>
                        <p class="text-gray-700 leading-relaxed">
                            Paid features require a subscription. Fees are billed in advance and are non-refundable
                            except as required by applicable law. We may change pricing with notice.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-semibold text-gray-900 mb-4">5. Limitation of Liability</h2>
                        <p class="text-gray-700 leading-relaxed">
                            BizBoost is provided "as is" without warranty. To the maximum extent permitted by law,
                            Rockshield Investments Limited shall not be liable for any indirect, incidental, or
                            consequential damages.
                        </p>
                    </section>
                </div>
            </div>
        </div>
    </BizBoostGuestLayout>
</template>
