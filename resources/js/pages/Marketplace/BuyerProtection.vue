<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';
import {
    ShieldCheckIcon,
    CurrencyDollarIcon,
    ClockIcon,
    ChatBubbleLeftRightIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

const protections = [
    {
        icon: CurrencyDollarIcon,
        title: 'Escrow Payment Protection',
        desc: 'Your payment is held securely until you confirm receipt of your order. Sellers only get paid after you\'re satisfied.',
    },
    {
        icon: ClockIcon,
        title: '7-Day Confirmation Window',
        desc: 'You have 7 days after delivery to inspect your order and confirm everything is correct before funds are released.',
    },
    {
        icon: ShieldCheckIcon,
        title: 'Verified Sellers Only',
        desc: 'All sellers undergo KYC verification. Look for the verification badge to shop with confidence.',
    },
    {
        icon: ChatBubbleLeftRightIcon,
        title: 'Dispute Resolution',
        desc: 'If something goes wrong, our team mediates between you and the seller to find a fair resolution.',
    },
];

const escrowSteps = [
    { step: 1, title: 'You Place Order', desc: 'Add items to cart and complete checkout' },
    { step: 2, title: 'Payment Held in Escrow', desc: 'Your money is held securely by MyGrowNet' },
    { step: 3, title: 'Seller Ships Order', desc: 'Seller prepares and delivers your order' },
    { step: 4, title: 'You Receive & Inspect', desc: 'Check your order matches the description' },
    { step: 5, title: 'Confirm or Dispute', desc: 'Confirm delivery or open a dispute if issues' },
    { step: 6, title: 'Funds Released', desc: 'Seller receives payment after confirmation' },
];

const covered = [
    'Item not received',
    'Item significantly different from description',
    'Wrong item delivered',
    'Item damaged during shipping',
    'Counterfeit or fake products',
];

const notCovered = [
    'Change of mind after delivery',
    'Minor variations in color/size within tolerance',
    'Damage caused by buyer after delivery',
    'Orders confirmed by buyer',
    'Disputes opened after 7-day window',
];
</script>

<template>
    <Head title="Buyer Protection - Marketplace" />
    
    <MarketplaceLayout>
        <div class="bg-gray-50 min-h-screen">
            <!-- Hero -->
            <div class="bg-gradient-to-r from-green-600 to-emerald-600 text-white py-16">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <ShieldCheckIcon class="h-16 w-16 mx-auto mb-4" aria-hidden="true" />
                    <h1 class="text-3xl md:text-4xl font-bold mb-4">Buyer Protection</h1>
                    <p class="text-green-100 text-lg max-w-2xl mx-auto">
                        Shop with confidence. Every purchase on MyGrowNet Marketplace is protected by our escrow system.
                    </p>
                </div>
            </div>

            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <!-- Protection Features -->
                <div class="grid md:grid-cols-2 gap-6 mb-16">
                    <div 
                        v-for="item in protections" 
                        :key="item.title"
                        class="bg-white rounded-xl p-6 shadow-sm"
                    >
                        <component :is="item.icon" class="h-10 w-10 text-green-600 mb-4" aria-hidden="true" />
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ item.title }}</h3>
                        <p class="text-gray-600">{{ item.desc }}</p>
                    </div>
                </div>

                <!-- How Escrow Works -->
                <div class="mb-16">
                    <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center">How Escrow Protection Works</h2>
                    <div class="relative">
                        <div class="hidden md:block absolute top-8 left-0 right-0 h-0.5 bg-green-200"></div>
                        <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                            <div v-for="step in escrowSteps" :key="step.step" class="relative text-center">
                                <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-3 relative z-10">
                                    <span class="text-xl font-bold text-white">{{ step.step }}</span>
                                </div>
                                <h4 class="font-semibold text-gray-900 text-sm mb-1">{{ step.title }}</h4>
                                <p class="text-xs text-gray-500">{{ step.desc }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- What's Covered -->
                <div class="grid md:grid-cols-2 gap-8 mb-16">
                    <div class="bg-green-50 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-green-800 mb-4 flex items-center gap-2">
                            <CheckCircleIcon class="h-6 w-6" aria-hidden="true" />
                            What's Covered
                        </h3>
                        <ul class="space-y-3">
                            <li v-for="item in covered" :key="item" class="flex items-start gap-2 text-green-700">
                                <CheckCircleIcon class="h-5 w-5 flex-shrink-0 mt-0.5" aria-hidden="true" />
                                {{ item }}
                            </li>
                        </ul>
                    </div>
                    <div class="bg-red-50 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-red-800 mb-4 flex items-center gap-2">
                            <ExclamationTriangleIcon class="h-6 w-6" aria-hidden="true" />
                            What's Not Covered
                        </h3>
                        <ul class="space-y-3">
                            <li v-for="item in notCovered" :key="item" class="flex items-start gap-2 text-red-700">
                                <ExclamationTriangleIcon class="h-5 w-5 flex-shrink-0 mt-0.5" aria-hidden="true" />
                                {{ item }}
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- CTA -->
                <div class="bg-gradient-to-r from-green-600 to-emerald-600 rounded-2xl p-8 text-center text-white">
                    <h2 class="text-2xl font-bold mb-4">Ready to Shop with Confidence?</h2>
                    <p class="text-green-100 mb-6">Browse thousands of products from verified sellers</p>
                    <Link 
                        :href="route('marketplace.home')"
                        class="inline-flex px-8 py-3 bg-white text-green-600 font-semibold rounded-lg hover:bg-green-50"
                    >
                        Start Shopping
                    </Link>
                </div>
            </div>
        </div>
    </MarketplaceLayout>
</template>
