<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Navigation from '@/components/custom/Navigation.vue';
import Footer from '@/components/custom/Footer.vue';

const categories = [
    {
        title: 'Business Growth',
        description: 'Help businesses start, grow, and reach more customers.',
        items: ['Business websites', 'Digital marketing', 'Social media advertising', 'Business management', 'Document management'],
        apps: ['GrowBuilder', 'BizBoost', 'CMS', 'BizDocs'],
        url: '/solutions/business-growth',
    },
    {
        title: 'Online Selling',
        description: 'Everything needed to sell products and services online.',
        items: ['Online stores', 'Digital marketplace', 'Product catalogues', 'Order management'],
        apps: ['GrowMarket', 'GrowMart'],
        url: '/solutions/online-selling',
    },
    {
        title: 'Investment & Wealth Creation',
        description: 'Allow members to participate in business opportunities.',
        items: ['Venture investment', 'Business partnerships', 'Referral network', 'Member rewards'],
        apps: ['Venture Builder', 'GrowNet'],
        url: '/solutions/investment-wealth',
    },
    {
        title: 'Hospitality & Travel',
        description: 'Help accommodation providers manage bookings.',
        items: ['Property listings', 'Online reservations', 'Guest management'],
        apps: ['ZamStay'],
        url: '/solutions/hospitality-travel',
    },
    {
        title: 'Cloud & Digital Workspace',
        description: 'Secure storage and collaboration.',
        items: ['Cloud storage', 'File backup', 'Secure document sharing'],
        apps: ['GrowStorage', 'BizDocs'],
        url: '/solutions/cloud-workspace',
    },
    {
        title: 'AI Solutions',
        description: 'Use AI to automate work and improve productivity.',
        items: ['AI marketing', 'Content generation', 'Customer engagement', 'Business automation'],
        apps: ['BizBoost'],
        url: '/solutions/ai-solutions',
    },
];
</script>

<template>
    <Head title="Solutions - MyGrowNet" />

    <div class="min-h-screen bg-gray-50">
        <Navigation />

        <div class="py-12 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-gradient-to-r from-blue-500 to-blue-700 rounded-lg shadow-xl p-8 md:p-12 relative overflow-hidden">
                    <div class="absolute top-10 left-20 w-48 h-48 bg-white bg-opacity-5 rounded-full"></div>
                    <div class="absolute bottom-16 right-16 w-32 h-32 bg-white bg-opacity-5 rounded-full"></div>
                    <div class="relative z-10">
                        <h1 class="text-4xl font-bold text-white leading-tight">Our Solutions</h1>
                        <p class="mt-4 text-lg text-white text-opacity-90 max-w-3xl">From marketing to cloud storage, we provide everything your business needs to grow and succeed in the digital economy.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="space-y-8">
                <div v-for="(cat, i) in categories" :key="i" class="bg-white rounded-lg shadow-lg p-8 md:p-12">
                    <div class="md:flex md:gap-12">
                        <div class="md:w-2/5">
                            <h2 class="text-2xl font-bold text-gray-800 mb-3">{{ cat.title }}</h2>
                            <p class="text-gray-600 mb-4">{{ cat.description }}</p>
                            <ul class="space-y-2 mb-6">
                                <li v-for="item in cat.items" :key="item" class="flex items-start gap-2 text-gray-600">
                                    <span class="text-blue-600 font-bold">✓</span>
                                    <span>{{ item }}</span>
                                </li>
                            </ul>
                            <Link :href="cat.url" class="inline-block px-5 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition text-sm">Learn More</Link>
                        </div>
                        <div class="md:w-3/5 mt-6 md:mt-0">
                            <p class="text-sm text-gray-500 font-semibold mb-3 uppercase tracking-wide">Supporting Apps</p>
                            <div class="flex flex-wrap gap-2">
                                <span v-for="app in cat.apps" :key="app" class="px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg text-sm font-medium">{{ app }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Footer />
    </div>
</template>
