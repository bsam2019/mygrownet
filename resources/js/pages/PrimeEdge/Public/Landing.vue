<script setup lang="ts">
import { Link, Head } from '@inertiajs/vue3';
import PrimeEdgeGuestLayout from '@/layouts/PrimeEdgeGuestLayout.vue';
import { ref } from 'vue';

const activeFaq = ref<number | null>(null);

const faqs = [
    { q: 'What services does PrimeEdge Advisory offer?', a: 'We provide business advisory, accounting & compliance, personal financial advisory, financial planning, and SME mentorship.' },
    { q: 'How do I get started?', a: 'Submit an inquiry through our contact form or schedule a free consultation call. We\'ll discuss your needs and provide a tailored proposal.' },
    { q: 'Do you serve clients outside Zambia?', a: 'Yes, our accounting and virtual advisory services accommodate clients from across Southern Africa.' },
    { q: 'What is the pricing model?', a: 'We offer custom pricing based on each engagement scope. Submit an inquiry and we\'ll provide a detailed quote with no obligation.' },
];
</script>

<template>
    <PrimeEdgeGuestLayout>
        <Head title="PrimeEdge Advisory - Smart Financial Solutions" />

        <section class="relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-600 via-emerald-700 to-emerald-900"></div>
            <div class="absolute top-20 right-20 w-72 h-72 bg-emerald-400/20 rounded-full blur-3xl"></div>
            <div class="absolute bottom-20 left-20 w-96 h-96 bg-emerald-300/10 rounded-full blur-3xl"></div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 sm:py-32">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/20 text-emerald-200 border border-emerald-400/30 mb-6">Trusted financial advisory services</span>
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white leading-tight mb-6">Smart Financial <span class="text-emerald-300">Solutions</span> for Business Growth</h1>
                        <p class="text-lg text-emerald-100/80 mb-8 max-w-lg">Expert advisory in accounting, compliance, and business strategy to help your business thrive with confidence.</p>
                        <div class="flex flex-wrap gap-4">
                            <Link :href="route('primeedge.register')" class="px-8 py-3.5 bg-white text-emerald-700 font-semibold rounded-xl hover:bg-emerald-50 transition-all shadow-xl hover:shadow-2xl">Get Started Free</Link>
                            <Link :href="route('primeedge.public.services')" class="px-8 py-3.5 border border-emerald-400/50 text-white font-semibold rounded-xl hover:bg-white/10 transition-all">Explore Services</Link>
                        </div>
                    </div>
                    <div class="hidden lg:flex justify-center">
                        <div class="w-80 h-80 rounded-3xl bg-gradient-to-br from-emerald-400/20 to-emerald-600/20 border border-emerald-400/20 backdrop-blur-sm flex items-center justify-center">
                            <svg class="w-40 h-40 text-emerald-300/60" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center mb-16">
                <span class="text-emerald-600 font-semibold text-sm uppercase tracking-wider">What We Offer</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mt-2">Tailored Financial Services</h2>
                <p class="text-gray-600 mt-3 max-w-2xl mx-auto">Comprehensive solutions designed for SMEs and individuals across Southern Africa.</p>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100 hover:shadow-xl transition-shadow">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Business Advisory</h3>
                    <p class="text-gray-600 text-sm">Strategic planning, financial analysis, and business process improvement to drive sustainable growth.</p>
                </div>
                <div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100 hover:shadow-xl transition-shadow">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Accounting & Compliance</h3>
                    <p class="text-gray-600 text-sm">Tax preparation, regulatory filing, financial statements, and compliance deadline tracking.</p>
                </div>
                <div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100 hover:shadow-xl transition-shadow">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Personal Financial Advisory</h3>
                    <p class="text-gray-600 text-sm">Personalized financial planning, investment advice, retirement planning, and wealth management.</p>
                </div>
            </div>
        </section>

        <section class="bg-emerald-700 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl font-bold text-white mb-4">Ready to Take Control of Your Finances?</h2>
                <p class="text-emerald-100 mb-8 max-w-lg mx-auto">Let's discuss how PrimeEdge Advisory can help your business or personal finances grow.</p>
                <Link :href="route('primeedge.public.contact')" class="inline-flex px-8 py-3.5 bg-white text-emerald-700 font-semibold rounded-xl hover:bg-emerald-50 transition-all shadow-lg">Schedule a Free Consultation</Link>
            </div>
        </section>

        <section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <h2 class="text-3xl font-bold text-gray-900 text-center mb-12">Frequently Asked Questions</h2>
            <div class="space-y-4">
                <div v-for="(faq, i) in faqs" :key="i" class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <button @click="activeFaq = activeFaq === i ? null : i" class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <span class="font-medium text-gray-900">{{ faq.q }}</span>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="activeFaq === i ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                    <div v-if="activeFaq === i" class="px-6 pb-4 text-gray-600 text-sm">{{ faq.a }}</div>
                </div>
            </div>
        </section>
    </PrimeEdgeGuestLayout>
</template>
