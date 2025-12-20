<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';
import {
    ShieldCheckIcon,
    UserGroupIcon,
    GlobeAltIcon,
    HeartIcon,
    BuildingStorefrontIcon,
    ArrowRightIcon,
} from '@heroicons/vue/24/outline';

const values = [
    {
        icon: ShieldCheckIcon,
        title: 'Trust First',
        desc: 'Every transaction is protected by our escrow system. We verify all sellers to ensure you shop with confidence.',
    },
    {
        icon: UserGroupIcon,
        title: 'Community Driven',
        desc: 'We\'re building a marketplace by Zambians, for Zambians. Supporting local businesses and entrepreneurs.',
    },
    {
        icon: GlobeAltIcon,
        title: 'Nationwide Reach',
        desc: 'Connecting buyers and sellers across all 10 provinces of Zambia. Shop local, reach national.',
    },
    {
        icon: HeartIcon,
        title: 'Fair & Transparent',
        desc: 'Low fees, clear policies, and honest dealings. We succeed when our sellers and buyers succeed.',
    },
];

const stats = [
    { value: '500+', label: 'Verified Sellers' },
    { value: '10,000+', label: 'Products Listed' },
    { value: '10', label: 'Provinces Covered' },
    { value: '99.9%', label: 'Payout Success Rate' },
];

const team = [
    { name: 'MyGrowNet Team', role: 'Building Zambia\'s Digital Future', desc: 'A passionate team dedicated to empowering Zambian entrepreneurs and creating economic opportunities through technology.' },
];
</script>

<template>
    <Head title="About Us - Marketplace" />
    
    <MarketplaceLayout>
        <div class="bg-gray-50 min-h-screen">
            <!-- Hero -->
            <div class="bg-gradient-to-r from-amber-500 to-orange-500 text-white py-20">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h1 class="text-3xl md:text-5xl font-bold mb-6">About MyGrowNet Marketplace</h1>
                    <p class="text-xl text-orange-100 max-w-2xl mx-auto">
                        Zambia's first trust-first marketplace, connecting buyers and sellers with escrow-protected transactions.
                    </p>
                </div>
            </div>

            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <!-- Mission -->
                <div class="text-center mb-16">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Our Mission</h2>
                    <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                        To create a safe, trusted marketplace where Zambian entrepreneurs can grow their businesses 
                        and buyers can shop with complete confidence. We believe in the power of local commerce 
                        and are committed to making online shopping accessible, secure, and fair for everyone.
                    </p>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-16">
                    <div v-for="stat in stats" :key="stat.label" class="bg-white rounded-xl p-6 text-center shadow-sm">
                        <div class="text-3xl font-bold text-orange-600 mb-1">{{ stat.value }}</div>
                        <div class="text-sm text-gray-500">{{ stat.label }}</div>
                    </div>
                </div>

                <!-- Values -->
                <div class="mb-16">
                    <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center">Our Values</h2>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div 
                            v-for="value in values" 
                            :key="value.title"
                            class="bg-white rounded-xl p-6 shadow-sm"
                        >
                            <component :is="value.icon" class="h-10 w-10 text-orange-500 mb-4" aria-hidden="true" />
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ value.title }}</h3>
                            <p class="text-gray-600">{{ value.desc }}</p>
                        </div>
                    </div>
                </div>

                <!-- Story -->
                <div class="bg-white rounded-2xl p-8 shadow-sm mb-16">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Our Story</h2>
                    <div class="prose prose-gray max-w-none">
                        <p class="text-gray-600 mb-4">
                            MyGrowNet Marketplace was born from a simple observation: Zambian entrepreneurs were 
                            struggling to reach customers beyond their immediate communities, while buyers lacked 
                            a trusted platform to purchase from sellers they didn't know personally.
                        </p>
                        <p class="text-gray-600 mb-4">
                            We saw the vibrant commerce happening on WhatsApp groups and Facebook pages, but also 
                            the challenges—scams, payment disputes, and lack of accountability. We knew there had 
                            to be a better way.
                        </p>
                        <p class="text-gray-600 mb-4">
                            That's why we built MyGrowNet Marketplace with trust at its core. Our escrow system 
                            ensures that buyers' money is protected until they receive their orders, while sellers 
                            are guaranteed payment for legitimate sales. It's a win-win that enables commerce to 
                            flourish.
                        </p>
                        <p class="text-gray-600">
                            Today, we're proud to serve sellers and buyers across all 10 provinces of Zambia, 
                            and we're just getting started. Join us in building Zambia's digital commerce future.
                        </p>
                    </div>
                </div>

                <!-- Part of MyGrowNet -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 rounded-2xl p-8 text-white mb-16">
                    <div class="flex items-center gap-4 mb-6">
                        <BuildingStorefrontIcon class="h-12 w-12 text-orange-400" aria-hidden="true" />
                        <div>
                            <h2 class="text-2xl font-bold">Part of MyGrowNet</h2>
                            <p class="text-gray-400">Community Empowerment Platform</p>
                        </div>
                    </div>
                    <p class="text-gray-300 mb-6">
                        MyGrowNet Marketplace is part of the larger MyGrowNet ecosystem—a community empowerment 
                        platform designed to help Zambians learn, earn, and grow. From skills training to 
                        business funding, we're building tools to help our community thrive.
                    </p>
                    <Link 
                        :href="route('welcome')"
                        class="inline-flex items-center gap-2 text-orange-400 hover:text-orange-300"
                    >
                        Learn more about MyGrowNet
                        <ArrowRightIcon class="h-4 w-4" aria-hidden="true" />
                    </Link>
                </div>

                <!-- CTA -->
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="bg-orange-50 rounded-xl p-8 text-center">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Ready to Shop?</h3>
                        <p class="text-gray-600 mb-6">Browse thousands of products from verified sellers</p>
                        <Link 
                            :href="route('marketplace.home')"
                            class="inline-flex px-6 py-3 bg-orange-500 text-white font-semibold rounded-lg hover:bg-orange-600"
                        >
                            Start Shopping
                        </Link>
                    </div>
                    <div class="bg-amber-50 rounded-xl p-8 text-center">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Want to Sell?</h3>
                        <p class="text-gray-600 mb-6">Join our community of successful sellers</p>
                        <Link 
                            :href="route('marketplace.seller.join')"
                            class="inline-flex px-6 py-3 bg-amber-500 text-white font-semibold rounded-lg hover:bg-amber-600"
                        >
                            Become a Seller
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </MarketplaceLayout>
</template>
