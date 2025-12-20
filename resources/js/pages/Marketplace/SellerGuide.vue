<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';
import {
    BuildingStorefrontIcon,
    DocumentCheckIcon,
    CubeIcon,
    TruckIcon,
    CurrencyDollarIcon,
    StarIcon,
    ArrowRightIcon,
    CheckCircleIcon,
} from '@heroicons/vue/24/outline';

const steps = [
    {
        step: 1,
        icon: DocumentCheckIcon,
        title: 'Register & Verify',
        desc: 'Create your seller account and complete KYC verification with your NRC and business documents.',
        tips: ['Use clear photos of your documents', 'Verification takes 1-2 business days', 'You\'ll be notified once approved'],
    },
    {
        step: 2,
        icon: CubeIcon,
        title: 'List Your Products',
        desc: 'Add your products with clear photos, detailed descriptions, and competitive prices.',
        tips: ['Use high-quality product photos', 'Write detailed, honest descriptions', 'Price competitively for your market'],
    },
    {
        step: 3,
        icon: TruckIcon,
        title: 'Fulfill Orders',
        desc: 'When orders come in, prepare and ship them promptly. Update order status to keep buyers informed.',
        tips: ['Ship within 2 business days', 'Package items securely', 'Provide tracking when possible'],
    },
    {
        step: 4,
        icon: CurrencyDollarIcon,
        title: 'Get Paid',
        desc: 'Once buyers confirm delivery, funds are released to your account. Withdraw to Mobile Money or bank.',
        tips: ['Funds released after buyer confirmation', 'Auto-release after 7 days if no disputes', 'Withdraw anytime to your preferred method'],
    },
];

const trustLevels = [
    { level: 'New Seller', badge: '🆕', desc: 'Just registered, building reputation', requirements: 'Complete registration' },
    { level: 'Verified Seller', badge: '✓', desc: 'KYC approved, trusted identity', requirements: 'Pass KYC verification' },
    { level: 'Trusted Seller', badge: '⭐', desc: 'Proven track record', requirements: '50+ orders, 4.5+ rating' },
    { level: 'Top Seller', badge: '👑', desc: 'Elite seller status', requirements: '200+ orders, 4.8+ rating, <2% disputes' },
];

const bestPractices = [
    'Respond to buyer messages within 24 hours',
    'Ship orders within 2 business days',
    'Use quality packaging to prevent damage',
    'Be honest in product descriptions',
    'Handle disputes professionally',
    'Keep your inventory updated',
];
</script>

<template>
    <Head title="Seller Guide - Marketplace" />
    
    <MarketplaceLayout>
        <div class="bg-gray-50 min-h-screen">
            <!-- Hero -->
            <div class="bg-gradient-to-r from-amber-500 to-orange-500 text-white py-16">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <BuildingStorefrontIcon class="h-16 w-16 mx-auto mb-4" aria-hidden="true" />
                    <h1 class="text-3xl md:text-4xl font-bold mb-4">Seller Guide</h1>
                    <p class="text-orange-100 text-lg max-w-2xl mx-auto">
                        Everything you need to know to start selling and grow your business on MyGrowNet Marketplace.
                    </p>
                </div>
            </div>

            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <!-- Getting Started Steps -->
                <div class="mb-16">
                    <h2 class="text-2xl font-bold text-gray-900 mb-8">Getting Started</h2>
                    <div class="space-y-6">
                        <div 
                            v-for="item in steps" 
                            :key="item.step"
                            class="bg-white rounded-xl shadow-sm overflow-hidden"
                        >
                            <div class="md:flex">
                                <div class="md:w-1/3 bg-gradient-to-br from-orange-500 to-amber-500 p-6 text-white">
                                    <div class="flex items-center gap-4 mb-4">
                                        <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                                            <span class="text-2xl font-bold">{{ item.step }}</span>
                                        </div>
                                        <component :is="item.icon" class="h-8 w-8" aria-hidden="true" />
                                    </div>
                                    <h3 class="text-xl font-semibold mb-2">{{ item.title }}</h3>
                                    <p class="text-orange-100">{{ item.desc }}</p>
                                </div>
                                <div class="md:w-2/3 p-6">
                                    <h4 class="font-semibold text-gray-900 mb-3">Tips for Success</h4>
                                    <ul class="space-y-2">
                                        <li v-for="tip in item.tips" :key="tip" class="flex items-start gap-2 text-gray-600">
                                            <CheckCircleIcon class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" aria-hidden="true" />
                                            {{ tip }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Trust Levels -->
                <div class="mb-16">
                    <h2 class="text-2xl font-bold text-gray-900 mb-8">Seller Trust Levels</h2>
                    <div class="grid md:grid-cols-4 gap-4">
                        <div 
                            v-for="level in trustLevels" 
                            :key="level.level"
                            class="bg-white rounded-xl p-6 shadow-sm text-center"
                        >
                            <div class="text-4xl mb-3">{{ level.badge }}</div>
                            <h3 class="font-semibold text-gray-900 mb-1">{{ level.level }}</h3>
                            <p class="text-sm text-gray-500 mb-3">{{ level.desc }}</p>
                            <div class="text-xs text-orange-600 bg-orange-50 rounded-lg px-3 py-2">
                                {{ level.requirements }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Best Practices -->
                <div class="mb-16">
                    <h2 class="text-2xl font-bold text-gray-900 mb-8">Best Practices</h2>
                    <div class="bg-white rounded-xl p-6 shadow-sm">
                        <div class="grid md:grid-cols-2 gap-4">
                            <div v-for="practice in bestPractices" :key="practice" class="flex items-start gap-3">
                                <StarIcon class="h-5 w-5 text-amber-500 flex-shrink-0 mt-0.5" aria-hidden="true" />
                                <span class="text-gray-700">{{ practice }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fees -->
                <div class="mb-16">
                    <h2 class="text-2xl font-bold text-gray-900 mb-8">Fees & Commissions</h2>
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Fee Type</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Amount</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr>
                                    <td class="px-6 py-4 text-gray-900">Registration Fee</td>
                                    <td class="px-6 py-4 text-green-600 font-semibold">FREE</td>
                                    <td class="px-6 py-4 text-gray-500">No cost to join</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 text-gray-900">Listing Fee</td>
                                    <td class="px-6 py-4 text-green-600 font-semibold">FREE</td>
                                    <td class="px-6 py-4 text-gray-500">List unlimited products</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 text-gray-900">Commission</td>
                                    <td class="px-6 py-4 text-orange-600 font-semibold">5%</td>
                                    <td class="px-6 py-4 text-gray-500">Per successful sale</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 text-gray-900">Withdrawal Fee</td>
                                    <td class="px-6 py-4 text-gray-600 font-semibold">K5</td>
                                    <td class="px-6 py-4 text-gray-500">Per withdrawal to Mobile Money</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- CTA -->
                <div class="bg-gradient-to-r from-amber-500 to-orange-500 rounded-2xl p-8 text-center text-white">
                    <h2 class="text-2xl font-bold mb-4">Ready to Start Selling?</h2>
                    <p class="text-orange-100 mb-6">Join hundreds of successful sellers on MyGrowNet Marketplace</p>
                    <Link 
                        :href="route('marketplace.seller.join')"
                        class="inline-flex items-center gap-2 px-8 py-3 bg-white text-orange-600 font-semibold rounded-lg hover:bg-orange-50"
                    >
                        Register as Seller
                        <ArrowRightIcon class="h-5 w-5" aria-hidden="true" />
                    </Link>
                </div>
            </div>
        </div>
    </MarketplaceLayout>
</template>
