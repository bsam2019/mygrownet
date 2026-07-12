<script setup lang="ts">
import { Link, Head } from '@inertiajs/vue3';
import PrimeEdgeGuestLayout from '@/layouts/PrimeEdgeGuestLayout.vue';

const services = [
    {
        title: 'Business Advisory',
        description: 'Strategic planning and financial analysis to drive sustainable business growth.',
        features: ['Business plan development', 'Financial projection modeling', 'Market entry strategy', 'Operational efficiency analysis', 'M&A advisory'],
    },
    {
        title: 'Accounting & Compliance',
        description: 'Full-spectrum accounting services ensuring regulatory compliance.',
        features: ['Tax preparation and filing', 'Financial statement preparation', 'Compliance deadline monitoring', 'ZRA regulatory filings', 'Internal audit support'],
    },
    {
        title: 'Personal Financial Advisory',
        description: 'Tailored financial planning for individuals and families.',
        features: ['Retirement planning', 'Investment portfolio review', 'Wealth management', 'Education fund planning', 'Estate planning'],
    },
    {
        title: 'SME Mentorship',
        description: 'Hands-on mentorship for small and medium enterprises.',
        features: ['One-on-one coaching', 'Financial literacy training', 'Cash flow management', 'Business model validation', 'Growth roadmap planning'],
    },
    {
        title: 'Virtual CFO Services',
        description: 'Part-time CFO support at a fraction of the cost.',
        features: ['Financial reporting & dashboards', 'Budgeting & forecasting', 'Board reporting preparation', 'KPI tracking', 'Strategic financial advice'],
    },
    {
        title: 'Tax Advisory',
        description: 'Expert guidance to optimize tax position and ensure compliance.',
        features: ['Corporate tax planning', 'VAT advisory', 'Transfer pricing documentation', 'Tax dispute resolution', 'Cross-border tax structuring'],
    },
];
</script>

<template>
    <PrimeEdgeGuestLayout>
        <Head title="Services - PrimeEdge Advisory" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center mb-16">
                <span class="text-emerald-600 font-semibold text-sm uppercase tracking-wider">Our Services</span>
                <h1 class="text-4xl font-bold text-gray-900 mt-2 mb-4">Comprehensive Financial Solutions</h1>
                <p class="text-gray-600 max-w-2xl mx-auto">From strategic advisory to compliance management, we offer end-to-end financial services designed for the modern business.</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <div v-for="service in services" :key="service.title" class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100 hover:shadow-xl transition-shadow">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">{{ service.title }}</h3>
                    <p class="text-gray-600 text-sm mb-6">{{ service.description }}</p>
                    <ul class="space-y-2">
                        <li v-for="feature in service.features" :key="feature" class="flex items-start gap-2 text-sm text-gray-600">
                            <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            {{ feature }}
                        </li>
                    </ul>
                </div>
            </div>

            <div class="text-center mt-16 bg-gradient-to-r from-emerald-600 to-emerald-700 rounded-2xl p-12">
                <h2 class="text-2xl font-bold text-white mb-2">Not sure which service fits?</h2>
                <p class="text-emerald-100 mb-6">Schedule a free consultation and we'll help you find the right solution.</p>
                <Link :href="route('primeedge.public.contact')" class="inline-flex px-8 py-3.5 bg-white text-emerald-700 font-semibold rounded-xl hover:bg-emerald-50 transition-all shadow-lg">Contact Us</Link>
            </div>
        </div>
    </PrimeEdgeGuestLayout>
</template>
