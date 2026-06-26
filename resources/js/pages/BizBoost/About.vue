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
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">About BizBoost</h1>
                    <p class="text-gray-600">Marketing & Growth Assistant for SMEs</p>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-8 space-y-8">
                    <section>
                        <h2 class="text-2xl font-semibold text-gray-900 mb-4">Our Mission</h2>
                        <p class="text-gray-700 leading-relaxed">
                            BizBoost empowers small and medium enterprises with affordable, AI-powered marketing tools.
                            We believe every business deserves access to professional-grade marketing capabilities
                            without the complexity or cost of traditional enterprise solutions.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-semibold text-gray-900 mb-4">What We Offer</h2>
                        <ul class="list-disc pl-5 space-y-2 text-gray-700">
                            <li>Social media content creation and scheduling</li>
                            <li>AI-powered content generation</li>
                            <li>Customer relationship management</li>
                            <li>Business analytics and insights</li>
                            <li>Multi-channel marketing campaigns</li>
                            <li>Team collaboration tools</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-2xl font-semibold text-gray-900 mb-4">Powered by MyGrowNet</h2>
                        <p class="text-gray-700 leading-relaxed">
                            BizBoost is part of the MyGrowNet ecosystem, a comprehensive platform by Rockshield
                            Investments Limited that provides digital tools for business growth, financial management,
                            and community development across Africa.
                        </p>
                    </section>
                </div>
            </div>
        </div>
    </BizBoostGuestLayout>
</template>
