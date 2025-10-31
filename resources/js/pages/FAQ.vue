<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ChevronDown } from 'lucide-vue-next';
import { ref } from 'vue';
import Navigation from '@/components/custom/Navigation.vue';
import Footer from '@/components/custom/Footer.vue';

const openFaq = ref<number | null>(null);

const toggleFaq = (index: number) => {
    openFaq.value = openFaq.value === index ? null : index;
};

const faqs = [
    {
        category: 'Business Growth Fund',
        questions: [
            {
                q: 'What is the Business Growth Fund?',
                a: 'BGF is a short-term financing program for MyGrowNet members to access funding for verified business orders and opportunities. Funding ranges from K1,000 to K50,000 with profit-sharing arrangements.'
            },
            {
                q: 'Who is eligible for BGF funding?',
                a: 'Active MyGrowNet members in good standing who have completed required training, can provide a verified business opportunity, and contribute 20-30% of the total project cost.'
            },
            {
                q: 'How long does approval take?',
                a: 'Applications are typically reviewed and decided within 3-5 business days. You\'ll be notified via email and platform notification.'
            },
            {
                q: 'What is the profit sharing structure?',
                a: 'Based on your application score: 90-100 (70% member, 30% MyGrowNet), 80-89 (65% member, 35% MyGrowNet), 70-79 (60% member, 40% MyGrowNet).'
            },
            {
                q: 'What happens if my project fails?',
                a: 'You are responsible for returning the full funded amount. Late payment penalties of 5% per month apply. Future BGF applications may be suspended.'
            },
        ]
    },
    {
        category: 'Venture Builder',
        questions: [
            {
                q: 'What is Venture Builder?',
                a: 'Venture Builder allows members to co-invest in vetted business projects. Each funded project becomes a separate limited company, and investors become legal shareholders with dividend rights.'
            },
            {
                q: 'What is the minimum investment?',
                a: 'The minimum investment per venture is K500. You can invest in multiple ventures to diversify your portfolio.'
            },
            {
                q: 'How do I earn returns?',
                a: 'You earn through quarterly or annual dividends based on the company\'s profits. As a shareholder, you receive your proportional share of distributed profits.'
            },
            {
                q: 'What is MyGrowNet\'s role?',
                a: 'MyGrowNet retains 10-20% minority equity and provides ongoing support, monitoring, and facilitation to ensure venture success.'
            },
        ]
    },
    {
        category: 'General',
        questions: [
            {
                q: 'How do I become a member?',
                a: 'Click "Join Now" to create your account. Complete the registration process and choose your membership package to get started.'
            },
            {
                q: 'What is the difference between BGF and Venture Builder?',
                a: 'BGF is short-term financing (1-4 months) for your own business with profit sharing. Venture Builder is long-term co-investment in other businesses where you become a shareholder.'
            },
            {
                q: 'Can I use both BGF and Venture Builder?',
                a: 'Yes! Members can apply for BGF funding for their own businesses AND invest in Venture Builder projects simultaneously.'
            },
            {
                q: 'How secure is my investment?',
                a: 'All ventures are thoroughly vetted. Venture Builder investments are protected by legal shareholder agreements. BGF projects are evaluated using a comprehensive 100-point scoring system.'
            },
        ]
    },
];
</script>

<template>
    <Head title="FAQ - MyGrowNet" />

    <div class="min-h-screen bg-gray-50">
        <Navigation />

        <!-- Hero -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-16">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl font-bold mb-4">Frequently Asked Questions</h1>
                <p class="text-xl text-blue-100">
                    Find answers to common questions about MyGrowNet features
                </p>
            </div>
        </div>

        <!-- FAQ Content -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div v-for="(section, sectionIndex) in faqs" :key="sectionIndex" class="mb-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ section.category }}</h2>
                
                <div class="space-y-4">
                    <div
                        v-for="(faq, faqIndex) in section.questions"
                        :key="faqIndex"
                        class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden"
                    >
                        <button
                            @click="toggleFaq(sectionIndex * 100 + faqIndex)"
                            class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 transition"
                        >
                            <span class="font-semibold text-gray-900">{{ faq.q }}</span>
                            <ChevronDown
                                :class="[
                                    'h-5 w-5 text-gray-500 transition-transform duration-200',
                                    openFaq === (sectionIndex * 100 + faqIndex) ? 'transform rotate-180' : ''
                                ]"
                            />
                        </button>
                        <div
                            v-show="openFaq === (sectionIndex * 100 + faqIndex)"
                            class="px-6 py-4 bg-gray-50 border-t border-gray-200"
                        >
                            <p class="text-gray-700">{{ faq.a }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Still have questions? -->
            <div class="mt-12 bg-blue-50 border-2 border-blue-200 rounded-lg p-8 text-center">
                <h3 class="text-xl font-bold text-gray-900 mb-2">Still have questions?</h3>
                <p class="text-gray-600 mb-4">Our support team is here to help</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link
                        :href="route('register')"
                        class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition"
                    >
                        Join MyGrowNet
                    </Link>
                    <a
                        href="mailto:support@mygrownet.com"
                        class="inline-flex items-center justify-center px-6 py-3 border-2 border-blue-600 text-blue-600 font-semibold rounded-lg hover:bg-blue-50 transition"
                    >
                        Contact Support
                    </a>
                </div>
            </div>
        </div>

        <Footer />
    </div>
</template>
