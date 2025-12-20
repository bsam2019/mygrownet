<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';
import { computed } from 'vue';
import {
    BuildingStorefrontIcon,
    ShieldCheckIcon,
    CurrencyDollarIcon,
    UserGroupIcon,
    ChartBarIcon,
    CheckCircleIcon,
    ArrowRightIcon,
    SparklesIcon,
} from '@heroicons/vue/24/outline';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const benefits = [
    {
        icon: UserGroupIcon,
        title: 'Reach Thousands of Buyers',
        desc: 'Access customers across all 10 provinces of Zambia. Your products visible to thousands of active shoppers.',
    },
    {
        icon: ShieldCheckIcon,
        title: 'Secure Escrow Payments',
        desc: 'Get paid safely through our escrow system. Funds are guaranteed once you deliver.',
    },
    {
        icon: CurrencyDollarIcon,
        title: 'Low Commission Fees',
        desc: 'Only 5% commission per sale. No listing fees, no monthly fees. You keep more of your earnings.',
    },
    {
        icon: ChartBarIcon,
        title: 'Seller Dashboard & Analytics',
        desc: 'Track your sales, manage orders, and grow your business with powerful tools.',
    },
];

const steps = [
    { step: 1, title: 'Create Account', desc: 'Sign up for free in 2 minutes' },
    { step: 2, title: 'Verify Identity', desc: 'Upload your NRC for KYC verification' },
    { step: 3, title: 'List Products', desc: 'Add your products with photos and prices' },
    { step: 4, title: 'Start Selling', desc: 'Receive orders and grow your business' },
];

const stats = [
    { value: '500+', label: 'Active Sellers' },
    { value: 'K5', label: 'Min Withdrawal' },
    { value: '5%', label: 'Commission Only' },
    { value: '24hrs', label: 'KYC Approval' },
];

const faqs = [
    { q: 'How much does it cost to sell?', a: 'Registration is FREE. We only charge 5% commission on successful sales. No listing fees or monthly subscriptions.' },
    { q: 'What documents do I need?', a: 'You\'ll need a valid NRC (front and back). For registered businesses, you can also upload your business registration certificate.' },
    { q: 'How do I get paid?', a: 'Funds are released to your seller wallet after buyers confirm delivery. You can withdraw to Mobile Money (MTN MoMo, Airtel Money) or bank account.' },
    { q: 'How long does verification take?', a: 'Most KYC verifications are completed within 24 hours. You\'ll receive an email/SMS once approved.' },
];
</script>

<template>
    <Head title="Become a Seller - Marketplace" />
    
    <MarketplaceLayout>
        <div class="bg-gray-50 min-h-screen">
            <!-- Hero Section -->
            <section class="relative bg-gradient-to-br from-amber-500 via-orange-500 to-orange-600 overflow-hidden">
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.4\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
                </div>
                
                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
                    <div class="grid lg:grid-cols-2 gap-12 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 rounded-full text-white text-sm mb-6">
                                <SparklesIcon class="h-4 w-4" aria-hidden="true" />
                                Join 500+ Successful Sellers
                            </div>
                            <h1 class="text-4xl md:text-5xl font-bold text-white mb-6 leading-tight">
                                Turn Your Products Into <span class="text-yellow-300">Profits</span>
                            </h1>
                            <p class="text-xl text-orange-100 mb-8">
                                Start selling on Zambia's most trusted marketplace. Reach customers nationwide with escrow-protected transactions.
                            </p>
                            
                            <!-- CTA Buttons -->
                            <div class="flex flex-col sm:flex-row gap-4">
                                <template v-if="user">
                                    <!-- User is logged in - go directly to registration -->
                                    <Link 
                                        :href="route('marketplace.seller.register')"
                                        class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white text-orange-600 font-semibold rounded-xl hover:bg-orange-50 transition-colors shadow-lg"
                                    >
                                        <BuildingStorefrontIcon class="h-5 w-5" aria-hidden="true" />
                                        Register Your Business
                                        <ArrowRightIcon class="h-5 w-5" aria-hidden="true" />
                                    </Link>
                                </template>
                                <template v-else>
                                    <!-- User not logged in - show login/register options -->
                                    <Link 
                                        :href="route('register', { redirect: route('marketplace.seller.register') })"
                                        class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white text-orange-600 font-semibold rounded-xl hover:bg-orange-50 transition-colors shadow-lg"
                                    >
                                        <BuildingStorefrontIcon class="h-5 w-5" aria-hidden="true" />
                                        Create Account & Start Selling
                                    </Link>
                                    <Link 
                                        :href="route('login', { redirect: route('marketplace.seller.register') })"
                                        class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-orange-700 text-white font-semibold rounded-xl hover:bg-orange-800 transition-colors"
                                    >
                                        Already have an account? Sign In
                                    </Link>
                                </template>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="hidden lg:grid grid-cols-2 gap-4">
                            <div v-for="stat in stats" :key="stat.label" class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 text-white text-center">
                                <div class="text-3xl font-bold text-yellow-300">{{ stat.value }}</div>
                                <div class="text-orange-100 text-sm">{{ stat.label }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Benefits -->
            <section class="py-16">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-12">
                        <h2 class="text-3xl font-bold text-gray-900 mb-4">Why Sell on MyGrowNet?</h2>
                        <p class="text-gray-600 max-w-2xl mx-auto">
                            Join hundreds of successful sellers who trust our platform to grow their businesses.
                        </p>
                    </div>
                    
                    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div 
                            v-for="benefit in benefits" 
                            :key="benefit.title"
                            class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow"
                        >
                            <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center mb-4">
                                <component :is="benefit.icon" class="h-6 w-6 text-orange-600" aria-hidden="true" />
                            </div>
                            <h3 class="font-semibold text-gray-900 mb-2">{{ benefit.title }}</h3>
                            <p class="text-sm text-gray-600">{{ benefit.desc }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- How It Works -->
            <section class="py-16 bg-white">
                <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-12">
                        <h2 class="text-3xl font-bold text-gray-900 mb-4">Start Selling in 4 Easy Steps</h2>
                    </div>
                    
                    <div class="grid md:grid-cols-4 gap-8">
                        <div v-for="item in steps" :key="item.step" class="text-center relative">
                            <!-- Connector -->
                            <div v-if="item.step < 4" class="hidden md:block absolute top-8 left-1/2 w-full h-0.5 bg-orange-200"></div>
                            
                            <div class="relative">
                                <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-amber-500 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg relative z-10">
                                    <span class="text-2xl font-bold text-white">{{ item.step }}</span>
                                </div>
                                <h3 class="font-semibold text-gray-900 mb-1">{{ item.title }}</h3>
                                <p class="text-sm text-gray-500">{{ item.desc }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- FAQs -->
            <section class="py-16">
                <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-12">
                        <h2 class="text-3xl font-bold text-gray-900 mb-4">Frequently Asked Questions</h2>
                    </div>
                    
                    <div class="space-y-4">
                        <details v-for="faq in faqs" :key="faq.q" class="bg-white rounded-xl shadow-sm group">
                            <summary class="px-6 py-4 cursor-pointer flex items-center justify-between font-medium text-gray-900">
                                {{ faq.q }}
                                <svg class="h-5 w-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </summary>
                            <div class="px-6 pb-4 text-gray-600">{{ faq.a }}</div>
                        </details>
                    </div>
                </div>
            </section>

            <!-- Final CTA -->
            <section class="py-16 bg-gradient-to-r from-orange-500 to-amber-500">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h2 class="text-3xl font-bold text-white mb-4">Ready to Start Your Selling Journey?</h2>
                    <p class="text-orange-100 mb-8 text-lg">
                        Join thousands of sellers already growing their businesses on MyGrowNet Marketplace.
                    </p>
                    
                    <template v-if="user">
                        <Link 
                            :href="route('marketplace.seller.register')"
                            class="inline-flex items-center gap-2 px-10 py-4 bg-white text-orange-600 font-semibold rounded-xl hover:bg-orange-50 transition-colors shadow-lg text-lg"
                        >
                            Register Your Business Now
                            <ArrowRightIcon class="h-5 w-5" aria-hidden="true" />
                        </Link>
                    </template>
                    <template v-else>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <Link 
                                :href="route('register', { redirect: route('marketplace.seller.register') })"
                                class="inline-flex items-center justify-center gap-2 px-10 py-4 bg-white text-orange-600 font-semibold rounded-xl hover:bg-orange-50 transition-colors shadow-lg text-lg"
                            >
                                Create Free Account
                            </Link>
                            <Link 
                                :href="route('login', { redirect: route('marketplace.seller.register') })"
                                class="inline-flex items-center justify-center gap-2 px-10 py-4 bg-orange-700 text-white font-semibold rounded-xl hover:bg-orange-800 transition-colors text-lg"
                            >
                                Sign In
                            </Link>
                        </div>
                    </template>
                </div>
            </section>
        </div>
    </MarketplaceLayout>
</template>
