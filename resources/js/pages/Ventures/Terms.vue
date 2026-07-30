<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import VenturesGuestLayout from '@/layouts/VenturesGuestLayout.vue';
</script>

<template>
    <VenturesGuestLayout>
        <Head title="Terms of Service - Venture Builder" />
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="mb-8">
                <Link :href="route('venture.sub.welcome')" class="text-sm text-amber-600 hover:text-amber-700 font-medium">&larr; Back to home</Link>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-6">Terms of Service</h1>
            <p class="text-sm text-gray-500 mb-8">Last updated: January 2026</p>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-3">1. Acceptance of Terms</h2>
                <p class="text-gray-600 leading-relaxed">
                    By accessing or using Venture Builder ("the Platform"), you agree to be bound by these Terms of Service.
                    If you do not agree, do not use the Platform. These terms are a legally binding agreement between you
                    and MyGrowNet.
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-3">2. Investment Risks</h2>
                <p class="text-gray-600 leading-relaxed">
                    Investing in early-stage ventures involves substantial risk. You may lose all or part of your investment.
                    Past performance of any venture or the Platform does not guarantee future results. You should only invest
                    capital you can afford to lose and should consult with independent financial advisers before making any
                    investment decision.
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-3">3. Eligibility</h2>
                <p class="text-gray-600 leading-relaxed">
                    You must be at least 18 years old and have the legal capacity to enter into binding contracts.
                    The Platform is intended for users in Zambia. By using the Platform, you represent that you meet
                    these eligibility requirements.
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-3">4. Account Responsibility</h2>
                <p class="text-gray-600 leading-relaxed">
                    You are responsible for maintaining the confidentiality of your account credentials and for all
                    activities that occur under your account. Notify us immediately of any unauthorized use. MyGrowNet
                    is not liable for any loss or damage arising from your failure to protect your account.
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-3">5. Investment Process</h2>
                <p class="text-gray-600 leading-relaxed mb-3">All investments are made on a best-efforts basis. The investment process includes:</p>
                <ul class="list-disc pl-6 text-gray-600 space-y-2">
                    <li>Review of the venture's business plan and offering documents</li>
                    <li>Execution of a legally binding investment agreement</li>
                    <li>Transfer of funds to the venture's designated account</li>
                    <li>Issuance of shares or other equity instruments as specified</li>
                </ul>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-3">6. Fees</h2>
                <p class="text-gray-600 leading-relaxed">
                    MyGrowNet may charge fees for facilitating investments, processing transactions, and providing
                    ongoing monitoring services. All applicable fees will be disclosed before you complete any investment.
                    Fees are non-refundable except as expressly stated in the relevant offering documents.
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-3">7. Limitation of Liability</h2>
                <p class="text-gray-600 leading-relaxed">
                    To the maximum extent permitted by law, MyGrowNet shall not be liable for any indirect, incidental,
                    special, consequential, or punitive damages arising out of or relating to your use of the Platform,
                    including but not limited to loss of investments, lost profits, or business interruption.
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-3">8. Changes to Terms</h2>
                <p class="text-gray-600 leading-relaxed">
                    We reserve the right to modify these terms at any time. Changes will be effective upon posting.
                    Your continued use of the Platform after changes constitutes acceptance of the updated terms.
                </p>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-3">9. Governing Law</h2>
                <p class="text-gray-600 leading-relaxed">
                    These terms are governed by the laws of the Republic of Zambia. Any disputes arising from these
                    terms shall be resolved in the courts of Zambia.
                </p>
            </section>
        </div>
    </VenturesGuestLayout>
</template>
