<script setup lang="ts">
import BizBoostGuestLayout from '@/layouts/BizBoostGuestLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const user = computed(() => usePage().props.auth?.user);
const homeUrl = computed(() => user.value ? '/dashboard' : route('bizboost.sub.welcome'));
</script>

<template>
    <BizBoostGuestLayout>
        <Head title="About - BizBoost" />

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
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-600 to-purple-700 flex items-center justify-center shadow-lg">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">About BizBoost</h1>
                            <p class="text-gray-500">Marketing & Growth Assistant for SMEs</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-3">Our Mission</h2>
                        <p class="text-gray-600 leading-relaxed">
                            BizBoost empowers small and medium enterprises with affordable, AI-powered marketing tools.
                            We believe every business deserves access to professional-grade marketing capabilities
                            without the complexity or cost of traditional enterprise solutions.
                        </p>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">What We Offer</h2>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div v-for="item in [
                                { title: 'Social Media Management', desc: 'Create, schedule, and publish content across multiple platforms' },
                                { title: 'AI Content Generation', desc: 'Generate engaging posts, captions, and marketing copy with AI' },
                                { title: 'Customer Management', desc: 'Track and manage customer relationships in one place' },
                                { title: 'Business Analytics', desc: 'Get insights on your marketing performance and growth' },
                                { title: 'Marketing Campaigns', desc: 'Launch and manage multi-channel marketing campaigns' },
                                { title: 'Team Collaboration', desc: 'Work together with your team on marketing initiatives' },
                            ]" :key="item.title" class="flex items-start gap-3 p-3 rounded-lg bg-gray-50">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900">{{ item.title }}</h3>
                                    <p class="text-sm text-gray-500">{{ item.desc }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-3">Powered by MyGrowNet</h2>
                        <p class="text-gray-600 leading-relaxed">
                            BizBoost is part of the MyGrowNet ecosystem, a comprehensive platform by Rockshield
                            Investments Limited that provides digital tools for business growth, financial management,
                            and community development across Africa.
                        </p>
                        <div class="mt-4 flex items-center gap-2 text-sm text-gray-500">
                            <span>A product of</span>
                            <Link href="/" class="text-purple-600 hover:text-purple-700 font-medium">MyGrowNet</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BizBoostGuestLayout>
</template>
