<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PrimeEdgeGuestLayout from '@/layouts/PrimeEdgeGuestLayout.vue';

const values = [
    { title: 'Integrity', description: 'We uphold the highest ethical standards in every client interaction.' },
    { title: 'Excellence', description: 'Delivering exceptional quality in financial analysis and advisory.' },
    { title: 'Innovation', description: 'Leveraging modern tools and approaches for actionable insights.' },
    { title: 'Partnership', description: 'We work alongside you as a trusted financial partner, not just a vendor.' },
];
</script>

<template>
    <PrimeEdgeGuestLayout>
        <Head title="About - PrimeEdge Advisory" />

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center mb-16">
                <span class="text-emerald-600 font-semibold text-sm uppercase tracking-wider">About Us</span>
                <h1 class="text-4xl font-bold text-gray-900 mt-2 mb-4">Your Trusted Financial Partner</h1>
                <p class="text-gray-600 max-w-2xl mx-auto">PrimeEdge Advisory is a Lusaka-based firm dedicated to helping SMEs and individuals navigate the complexities of financial management with confidence.</p>
            </div>

            <div class="bg-white rounded-2xl p-8 sm:p-12 shadow-lg border border-gray-100 mb-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Our Mission</h2>
                <p class="text-gray-600 leading-relaxed">To empower businesses and individuals with smart financial solutions that drive growth, ensure compliance, and build long-term wealth. We believe that sound financial management should be accessible to every business, regardless of size.</p>
            </div>

            <div class="grid sm:grid-cols-2 gap-6 mb-12">
                <div v-for="value in values" :key="value.title" class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-2">{{ value.title }}</h3>
                    <p class="text-gray-600 text-sm">{{ value.description }}</p>
                </div>
            </div>

            <div class="bg-gradient-to-r from-emerald-50 to-emerald-100 rounded-2xl p-8 sm:p-12 border border-emerald-200">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Why Choose PrimeEdge?</h2>
                <ul class="space-y-3">
                    <li class="flex items-start gap-3 text-gray-700"><svg class="w-5 h-5 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg><span><strong>Tailored approach</strong> — no cookie-cutter solutions, every engagement is custom</span></li>
                    <li class="flex items-start gap-3 text-gray-700"><svg class="w-5 h-5 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg><span><strong>Deep local expertise</strong> — deep understanding of Zambian and Southern African markets</span></li>
                    <li class="flex items-start gap-3 text-gray-700"><svg class="w-5 h-5 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg><span><strong>Tech-enabled</strong> — modern tools for seamless document sharing and compliance tracking</span></li>
                </ul>
            </div>
        </div>
    </PrimeEdgeGuestLayout>
</template>
