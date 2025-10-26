<template>
    <MemberLayout title="Starter Kit">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Hero Section -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-lg shadow-xl overflow-hidden mb-8">
                    <div class="px-6 py-12 sm:px-12 sm:py-16 lg:px-16">
                        <div class="text-center">
                            <h1 class="text-4xl font-extrabold text-white sm:text-5xl">
                                MyGrowNet Starter Kit
                            </h1>
                            <p class="mt-4 text-xl text-blue-100">
                                Everything you need to succeed - All in one package
                            </p>
                            <div class="mt-8">
                                <div class="inline-flex items-center">
                                    <span class="text-3xl font-bold text-white line-through mr-4">
                                        K1,050
                                    </span>
                                    <span class="text-5xl font-extrabold text-yellow-300">
                                        K500
                                    </span>
                                </div>
                                <p class="mt-2 text-lg text-blue-100">
                                    Save K550 (52% discount!)
                                </p>
                            </div>
                            <div class="mt-8">
                                <Link
                                    v-if="!hasStarterKit"
                                    :href="route('starter-kit.purchase')"
                                    class="inline-flex items-center px-8 py-4 border border-transparent text-lg font-medium rounded-md text-blue-700 bg-white hover:bg-blue-50 transition"
                                >
                                    Get Started Now
                                    <svg class="ml-2 -mr-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </Link>
                                <Link
                                    v-else
                                    :href="route('starter-kit.dashboard')"
                                    class="inline-flex items-center px-8 py-4 border border-transparent text-lg font-medium rounded-md text-blue-700 bg-white hover:bg-blue-50 transition"
                                >
                                    Go to Dashboard
                                    <svg class="ml-2 -mr-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- What's Included -->
                <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 mb-6 text-center">
                        What's Included in Your Starter Kit
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Learning & Training -->
                        <div class="border border-gray-200 rounded-lg p-6">
                            <div class="text-4xl mb-4">🎓</div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">
                                Learning & Training
                            </h3>
                            <p class="text-gray-600 mb-4">K400 value</p>
                            <ul class="space-y-2 text-sm text-gray-700">
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    3-module business course
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Success Guide eBook
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    3 video tutorials
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    30-day library access
                                </li>
                            </ul>
                        </div>

                        <!-- Shop Credit -->
                        <div class="border border-gray-200 rounded-lg p-6 bg-green-50">
                            <div class="text-4xl mb-4">💰</div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">
                                Shop Credit
                            </h3>
                            <p class="text-gray-600 mb-4">K100 value</p>
                            <ul class="space-y-2 text-sm text-gray-700">
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    K100 to spend immediately
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Valid for 90 days
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Use on any shop products
                                </li>
                            </ul>
                        </div>

                        <!-- Business Tools -->
                        <div class="border border-gray-200 rounded-lg p-6">
                            <div class="text-4xl mb-4">🛠️</div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">
                                Business Tools
                            </h3>
                            <p class="text-gray-600 mb-4">K200 value</p>
                            <ul class="space-y-2 text-sm text-gray-700">
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Marketing templates
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Pitch deck slides
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Pre-written content
                                </li>
                            </ul>
                        </div>

                        <!-- Platform Access -->
                        <div class="border border-gray-200 rounded-lg p-6">
                            <div class="text-4xl mb-4">🌐</div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">
                                Platform Access
                            </h3>
                            <p class="text-gray-600 mb-4">K250 value</p>
                            <ul class="space-y-2 text-sm text-gray-700">
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Member dashboard
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Referral system
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Weekly webinars (1 month)
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Points system activation
                                </li>
                            </ul>
                        </div>

                        <!-- Bonuses -->
                        <div class="border border-gray-200 rounded-lg p-6 bg-yellow-50">
                            <div class="text-4xl mb-4">🎁</div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">
                                Bonus Features
                            </h3>
                            <p class="text-gray-600 mb-4">K100 value</p>
                            <ul class="space-y-2 text-sm text-gray-700">
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Achievement badges
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Completion certificates
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Community access
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Progressive unlocking
                                </li>
                            </ul>
                        </div>

                        <!-- Total Value -->
                        <div class="border-2 border-blue-500 rounded-lg p-6 bg-blue-50">
                            <div class="text-4xl mb-4">💎</div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">
                                Total Value
                            </h3>
                            <p class="text-3xl font-bold text-blue-600 mb-4">K1,050</p>
                            <div class="text-center">
                                <p class="text-sm text-gray-600 mb-2">Your Price:</p>
                                <p class="text-4xl font-extrabold text-green-600">K500</p>
                                <p class="text-sm text-gray-600 mt-2">Save K550!</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CTA Section -->
                <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">
                        Ready to Start Your Journey?
                    </h2>
                    <p class="text-gray-600 mb-6">
                        Join thousands of members who are already building their future with MyGrowNet
                    </p>
                    <Link
                        v-if="!hasStarterKit"
                        :href="route('starter-kit.purchase')"
                        class="inline-flex items-center px-8 py-4 border border-transparent text-lg font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition"
                    >
                        Purchase Starter Kit - K500
                    </Link>
                    <Link
                        v-else
                        :href="route('starter-kit.dashboard')"
                        class="inline-flex items-center px-8 py-4 border border-transparent text-lg font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition"
                    >
                        Access Your Starter Kit
                    </Link>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>

<script setup>
import MemberLayout from '@/layouts/MemberLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    hasStarterKit: Boolean,
    price: Number,
    shopCredit: Number,
    valueBreakdown: Array,
});
</script>
