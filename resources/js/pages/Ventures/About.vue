<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Briefcase, Users, TrendingUp, Shield, DollarSign, CheckCircle } from 'lucide-vue-next';
import Navigation from '@/components/custom/Navigation.vue';
import Footer from '@/components/custom/Footer.vue';

const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth?.user);
</script>

<template>
    <Head title="Venture Builder - MyGrowNet" />

    <div class="min-h-screen bg-gray-50">
        <Navigation />
        
        <!-- Back Button (Mobile) -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 md:hidden">
            <button
                @click="$inertia.visit(route('ventures.index'))"
                class="flex items-center text-sm font-medium text-gray-600 hover:text-gray-900"
            >
                <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Marketplace
            </button>
        </div>
        
        <!-- Hero Section -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-700 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div class="text-center">
                    <h1 class="text-4xl md:text-5xl font-bold mb-4">Venture Builder</h1>
                    <p class="text-xl md:text-2xl mb-8 text-indigo-100">
                        Co-invest in vetted business projects and become a shareholder
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <Link
                            :href="route('ventures.index')"
                            class="inline-flex items-center justify-center px-8 py-3 bg-white text-indigo-600 font-semibold rounded-lg hover:bg-indigo-50 transition"
                        >
                            Browse Ventures
                        </Link>
                        <Link
                            v-if="!isAuthenticated"
                            :href="route('register')"
                            class="inline-flex items-center justify-center px-8 py-3 border-2 border-white text-white font-semibold rounded-lg hover:bg-white hover:text-indigo-600 transition"
                        >
                            Join MyGrowNet
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- What is Venture Builder -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">What is Venture Builder?</h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Venture Builder allows MyGrowNet members to co-invest in carefully vetted business projects.
                    Each funded project becomes a separate limited company, and investors become legal shareholders
                    with real ownership and dividend rights.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-center w-12 h-12 bg-indigo-100 rounded-lg mb-4">
                        <Briefcase class="h-6 w-6 text-indigo-600" />
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Real Ownership</h3>
                    <p class="text-gray-600">
                        Become a legal shareholder in a registered limited company. Your investment gives you real equity.
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-center w-12 h-12 bg-purple-100 rounded-lg mb-4">
                        <TrendingUp class="h-6 w-6 text-purple-600" />
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Dividend Income</h3>
                    <p class="text-gray-600">
                        Receive quarterly or annual dividends based on company profits. Build passive income streams.
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-center w-12 h-12 bg-blue-100 rounded-lg mb-4">
                        <Shield class="h-6 w-6 text-blue-600" />
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Vetted Projects</h3>
                    <p class="text-gray-600">
                        Every venture is thoroughly evaluated by our team. Only viable businesses with strong potential are listed.
                    </p>
                </div>
            </div>
        </div>

        <!-- How It Works -->
        <div class="bg-white py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">How It Works</h2>
                    <p class="text-lg text-gray-600">Simple process from browsing to becoming a shareholder</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div class="text-center">
                        <div class="flex items-center justify-center w-16 h-16 bg-indigo-600 text-white rounded-full mx-auto mb-4 text-2xl font-bold">
                            1
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Browse Ventures</h3>
                        <p class="text-gray-600">Explore available investment opportunities with detailed business plans</p>
                    </div>

                    <div class="text-center">
                        <div class="flex items-center justify-center w-16 h-16 bg-indigo-600 text-white rounded-full mx-auto mb-4 text-2xl font-bold">
                            2
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Review Details</h3>
                        <p class="text-gray-600">Study the business model, financials, and team behind each venture</p>
                    </div>

                    <div class="text-center">
                        <div class="flex items-center justify-center w-16 h-16 bg-indigo-600 text-white rounded-full mx-auto mb-4 text-2xl font-bold">
                            3
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Invest</h3>
                        <p class="text-gray-600">Choose your investment amount and complete the transaction securely</p>
                    </div>

                    <div class="text-center">
                        <div class="flex items-center justify-center w-16 h-16 bg-indigo-600 text-white rounded-full mx-auto mb-4 text-2xl font-bold">
                            4
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Earn Dividends</h3>
                        <p class="text-gray-600">Receive your share of profits as the business grows</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Key Benefits -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Why Invest Through Venture Builder?</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="flex items-start gap-4">
                    <CheckCircle class="h-6 w-6 text-green-600 flex-shrink-0 mt-1" />
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Legal Protection</h3>
                        <p class="text-gray-600">
                            Each venture is a separate limited company with proper governance, shareholder agreements, and legal compliance.
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <CheckCircle class="h-6 w-6 text-green-600 flex-shrink-0 mt-1" />
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Diversification</h3>
                        <p class="text-gray-600">
                            Invest in multiple ventures across different industries to spread risk and maximize returns.
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <CheckCircle class="h-6 w-6 text-green-600 flex-shrink-0 mt-1" />
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Professional Management</h3>
                        <p class="text-gray-600">
                            MyGrowNet provides ongoing support, monitoring, and facilitation to ensure venture success.
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <CheckCircle class="h-6 w-6 text-green-600 flex-shrink-0 mt-1" />
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Transparent Reporting</h3>
                        <p class="text-gray-600">
                            Regular updates, financial reports, and performance metrics keep you informed about your investments.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Investment Details -->
        <div class="bg-indigo-50 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Investment Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-2">Minimum Investment</h3>
                            <p class="text-3xl font-bold text-indigo-600">K500</p>
                            <p class="text-sm text-gray-600 mt-2">Per venture</p>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-2">MyGrowNet Stake</h3>
                            <p class="text-3xl font-bold text-purple-600">10-20%</p>
                            <p class="text-sm text-gray-600 mt-2">Minority equity retained</p>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-2">Dividend Frequency</h3>
                            <p class="text-3xl font-bold text-blue-600">Quarterly</p>
                            <p class="text-sm text-gray-600 mt-2">Or annual based on venture</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-700 text-white py-16">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl font-bold mb-4">Ready to Build Wealth?</h2>
                <p class="text-xl mb-8 text-indigo-100">
                    Start investing in real businesses and earn dividends
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link
                        :href="route('ventures.index')"
                        class="inline-flex items-center justify-center px-8 py-3 bg-white text-indigo-600 font-semibold rounded-lg hover:bg-indigo-50 transition"
                    >
                        View Available Ventures
                    </Link>
                    <Link
                        v-if="!isAuthenticated"
                        :href="route('register')"
                        class="inline-flex items-center justify-center px-8 py-3 border-2 border-white text-white font-semibold rounded-lg hover:bg-white hover:text-indigo-600 transition"
                    >
                        Create Account
                    </Link>
                </div>
            </div>
        </div>

        <Footer />
    </div>
</template>
