<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Briefcase, TrendingUp, BookOpen, Users, DollarSign, GraduationCap } from 'lucide-vue-next';
import Navigation from '@/components/custom/Navigation.vue';
import Footer from '@/components/custom/Footer.vue';
</script>

<template>
    <Head title="Features - MyGrowNet" />

    <div class="min-h-screen bg-gray-50">
        <Navigation />
        <!-- Hero -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">MyGrowNet Features</h1>
                <p class="text-xl text-blue-100">
                    Empowering members with tools for growth, learning, and wealth creation
                </p>
            </div>
        </div>

        <!-- Features Grid -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Business Growth Fund -->
                <Link
                    :href="route('bgf.about')"
                    class="bg-white rounded-lg shadow-lg p-8 hover:shadow-xl transition group"
                >
                    <div class="flex items-center justify-center w-16 h-16 bg-blue-100 rounded-lg mb-4 group-hover:bg-blue-200 transition">
                        <TrendingUp class="h-8 w-8 text-blue-600" />
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Business Growth Fund</h3>
                    <p class="text-gray-600 mb-4">
                        Access short-term financing for verified business orders. K1,000 - K50,000 funding with profit sharing.
                    </p>
                    <span class="text-blue-600 font-semibold group-hover:underline">Learn More →</span>
                </Link>

                <!-- Venture Builder -->
                <Link
                    :href="route('ventures.about')"
                    class="bg-white rounded-lg shadow-lg p-8 hover:shadow-xl transition group"
                >
                    <div class="flex items-center justify-center w-16 h-16 bg-indigo-100 rounded-lg mb-4 group-hover:bg-indigo-200 transition">
                        <Briefcase class="h-8 w-8 text-indigo-600" />
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Venture Builder</h3>
                    <p class="text-gray-600 mb-4">
                        Co-invest in vetted business projects and become a shareholder. Earn dividends from real businesses.
                    </p>
                    <span class="text-indigo-600 font-semibold group-hover:underline">Learn More →</span>
                </Link>

                <!-- Starter Kit -->
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <div class="flex items-center justify-center w-16 h-16 bg-green-100 rounded-lg mb-4">
                        <BookOpen class="h-8 w-8 text-green-600" />
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Starter Kit</h3>
                    <p class="text-gray-600 mb-4">
                        Essential business resources, training materials, and tools to kickstart your entrepreneurial journey.
                    </p>
                    <span class="text-gray-400 font-semibold">For Members</span>
                </div>

                <!-- Learning Library -->
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <div class="flex items-center justify-center w-16 h-16 bg-purple-100 rounded-lg mb-4">
                        <GraduationCap class="h-8 w-8 text-purple-600" />
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Learning Library</h3>
                    <p class="text-gray-600 mb-4">
                        Access e-books, videos, courses, and training materials to develop your business skills.
                    </p>
                    <span class="text-gray-400 font-semibold">For Members</span>
                </div>

                <!-- Network & Referrals -->
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <div class="flex items-center justify-center w-16 h-16 bg-emerald-100 rounded-lg mb-4">
                        <Users class="h-8 w-8 text-emerald-600" />
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Network & Referrals</h3>
                    <p class="text-gray-600 mb-4">
                        Build your team with our 3×3 matrix system. Earn commissions and bonuses from your network.
                    </p>
                    <span class="text-gray-400 font-semibold">For Members</span>
                </div>

                <!-- MyGrow Wallet -->
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <div class="flex items-center justify-center w-16 h-16 bg-yellow-100 rounded-lg mb-4">
                        <DollarSign class="h-8 w-8 text-yellow-600" />
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">MyGrow Wallet</h3>
                    <p class="text-gray-600 mb-4">
                        Secure digital wallet for managing earnings, bonuses, and transactions within the platform.
                    </p>
                    <span class="text-gray-400 font-semibold">For Members</span>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-16">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl font-bold mb-4">Ready to Get Started?</h2>
                <p class="text-xl mb-8 text-blue-100">
                    Join MyGrowNet today and access all these powerful features
                </p>
                <Link
                    :href="route('register')"
                    class="inline-flex items-center justify-center px-8 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-blue-50 transition text-lg"
                >
                    Create Free Account
                </Link>
            </div>
        </div>

        <Footer />
    </div>
</template>
