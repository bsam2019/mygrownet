<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { CheckCircle, TrendingUp, Shield, Clock, Users, DollarSign } from 'lucide-vue-next';
import Navigation from '@/components/custom/Navigation.vue';
import Footer from '@/components/custom/Footer.vue';

const page = usePage();
const isAuthenticated = computed(() => {
    return !!page.props.auth?.user;
});
</script>

<template>
    <Head title="Business Growth Fund - MyGrowNet" />

    <div class="min-h-screen bg-gray-50">
        <Navigation />
        <!-- Hero Section -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div class="text-center">
                    <h1 class="text-4xl md:text-5xl font-bold mb-4">Business Growth Fund</h1>
                    <p class="text-xl md:text-2xl mb-8 text-blue-100">
                        Short-term financing for verified business orders and opportunities
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <Link
                            v-if="isAuthenticated"
                            :href="route('mygrownet.bgf.index')"
                            class="inline-flex items-center justify-center px-8 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-blue-50 transition"
                        >
                            Go to Dashboard
                        </Link>
                        <Link
                            v-else
                            :href="route('register')"
                            class="inline-flex items-center justify-center px-8 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-blue-50 transition"
                        >
                            Join MyGrowNet
                        </Link>
                        <Link
                            :href="route('bgf.how-it-works')"
                            class="inline-flex items-center justify-center px-8 py-3 border-2 border-white text-white font-semibold rounded-lg hover:bg-white hover:text-blue-600 transition"
                        >
                            How It Works
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Key Features -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Why Choose BGF?</h2>
                <p class="text-lg text-gray-600">Fast, flexible financing designed for MyGrowNet members</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-center w-12 h-12 bg-blue-100 rounded-lg mb-4">
                        <Clock class="h-6 w-6 text-blue-600" />
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Quick Approval</h3>
                    <p class="text-gray-600">
                        Get funding decisions within 3-5 business days. Fast-track your business opportunities.
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-center w-12 h-12 bg-green-100 rounded-lg mb-4">
                        <DollarSign class="h-6 w-6 text-green-600" />
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Up to K50,000</h3>
                    <p class="text-gray-600">
                        Access funding from K1,000 to K50,000 per project based on your business needs.
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-center w-12 h-12 bg-emerald-100 rounded-lg mb-4">
                        <TrendingUp class="h-6 w-6 text-emerald-600" />
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Profit Sharing</h3>
                    <p class="text-gray-600">
                        Keep 60-70% of profits. Fair partnership model that rewards your success.
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-center w-12 h-12 bg-blue-100 rounded-lg mb-4">
                        <Users class="h-6 w-6 text-blue-600" />
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Member Priority</h3>
                    <p class="text-gray-600">
                        Exclusive to MyGrowNet members. Your membership level affects your score.
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-center w-12 h-12 bg-yellow-100 rounded-lg mb-4">
                        <Shield class="h-6 w-6 text-yellow-600" />
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Low Risk</h3>
                    <p class="text-gray-600">
                        Only 20-30% member contribution required. We share the risk with you.
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-center w-12 h-12 bg-green-100 rounded-lg mb-4">
                        <CheckCircle class="h-6 w-6 text-green-600" />
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Verified Orders</h3>
                    <p class="text-gray-600">
                        Perfect for order fulfillment. We verify your business opportunity.
                    </p>
                </div>
            </div>
        </div>

        <!-- How It Works -->
        <div class="bg-white py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Simple Process</h2>
                    <p class="text-lg text-gray-600">From application to funding in 4 easy steps</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div class="text-center">
                        <div class="flex items-center justify-center w-16 h-16 bg-blue-600 text-white rounded-full mx-auto mb-4 text-2xl font-bold">
                            1
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Apply Online</h3>
                        <p class="text-gray-600">Submit your business opportunity with required documents</p>
                    </div>

                    <div class="text-center">
                        <div class="flex items-center justify-center w-16 h-16 bg-blue-600 text-white rounded-full mx-auto mb-4 text-2xl font-bold">
                            2
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Evaluation</h3>
                        <p class="text-gray-600">Our team reviews and scores your application</p>
                    </div>

                    <div class="text-center">
                        <div class="flex items-center justify-center w-16 h-16 bg-blue-600 text-white rounded-full mx-auto mb-4 text-2xl font-bold">
                            3
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Approval</h3>
                        <p class="text-gray-600">Get approved and sign your funding contract</p>
                    </div>

                    <div class="text-center">
                        <div class="flex items-center justify-center w-16 h-16 bg-blue-600 text-white rounded-full mx-auto mb-4 text-2xl font-bold">
                            4
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Get Funded</h3>
                        <p class="text-gray-600">Receive funds and execute your business plan</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Eligibility -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="bg-blue-50 rounded-lg p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Eligibility Requirements</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex items-start gap-3">
                        <CheckCircle class="h-6 w-6 text-green-600 flex-shrink-0 mt-1" />
                        <div>
                            <h3 class="font-semibold text-gray-900">Active MyGrowNet Member</h3>
                            <p class="text-gray-600">Must have an active membership with good standing</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <CheckCircle class="h-6 w-6 text-green-600 flex-shrink-0 mt-1" />
                        <div>
                            <h3 class="font-semibold text-gray-900">Verified Business Opportunity</h3>
                            <p class="text-gray-600">Must provide proof of order or business plan</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <CheckCircle class="h-6 w-6 text-green-600 flex-shrink-0 mt-1" />
                        <div>
                            <h3 class="font-semibold text-gray-900">Member Contribution</h3>
                            <p class="text-gray-600">20-30% of total project cost required</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <CheckCircle class="h-6 w-6 text-green-600 flex-shrink-0 mt-1" />
                        <div>
                            <h3 class="font-semibold text-gray-900">Completion Timeline</h3>
                            <p class="text-gray-600">Project must be completable within 1-4 months</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-16">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl font-bold mb-4">Ready to Grow Your Business?</h2>
                <p class="text-xl mb-8 text-blue-100">
                    Join thousands of members accessing business funding through MyGrowNet
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link
                        v-if="isAuthenticated"
                        :href="route('mygrownet.bgf.create')"
                        class="inline-flex items-center justify-center px-8 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-blue-50 transition"
                    >
                        Apply Now
                    </Link>
                    <Link
                        v-else
                        :href="route('register')"
                        class="inline-flex items-center justify-center px-8 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-blue-50 transition"
                    >
                        Join MyGrowNet
                    </Link>
                    <Link
                        :href="route('bgf.terms')"
                        class="inline-flex items-center justify-center px-8 py-3 border-2 border-white text-white font-semibold rounded-lg hover:bg-white hover:text-blue-600 transition"
                    >
                        View Terms & Conditions
                    </Link>
                </div>
            </div>
        </div>

        <Footer />
    </div>
</template>
