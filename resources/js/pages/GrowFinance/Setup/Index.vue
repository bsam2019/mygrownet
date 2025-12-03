<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-500 to-blue-600 flex flex-col items-center justify-center p-6">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-white/20 backdrop-blur-sm mb-4">
                    <ChartBarIcon class="h-10 w-10 text-white" aria-hidden="true" />
                </div>
                <h1 class="text-3xl font-bold text-white">GrowFinance</h1>
                <p class="text-blue-100 mt-2">Simple accounting for your business</p>
            </div>

            <!-- Setup Card -->
            <div class="bg-white rounded-3xl shadow-2xl p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-2">Welcome! 👋</h2>
                <p class="text-gray-600 mb-6">
                    Let's set up your accounting system. We'll create a standard chart of accounts to get you started.
                </p>

                <!-- Features List -->
                <div class="space-y-4 mb-8">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                            <CheckIcon class="h-4 w-4 text-emerald-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Track Sales & Income</p>
                            <p class="text-sm text-gray-500">Record all money coming into your business</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                            <CheckIcon class="h-4 w-4 text-emerald-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Manage Expenses</p>
                            <p class="text-sm text-gray-500">Keep track of where your money goes</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                            <CheckIcon class="h-4 w-4 text-emerald-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Financial Reports</p>
                            <p class="text-sm text-gray-500">See your profit, cash flow, and more</p>
                        </div>
                    </div>
                </div>

                <!-- Setup Button -->
                <form @submit.prevent="handleSetup">
                    <button 
                        type="submit"
                        :disabled="isLoading"
                        class="w-full py-4 rounded-2xl bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold shadow-lg shadow-blue-500/30 active:scale-[0.98] transition-transform disabled:opacity-50"
                    >
                        <span v-if="isLoading" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                            </svg>
                            Setting up...
                        </span>
                        <span v-else>Get Started</span>
                    </button>
                </form>
            </div>

            <!-- Back Link -->
            <div class="text-center mt-6">
                <Link :href="route('home')" class="text-white/80 hover:text-white text-sm">
                    ← Back to Home Hub
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { ChartBarIcon, CheckIcon } from '@heroicons/vue/24/outline';

const isLoading = ref(false);

const handleSetup = () => {
    isLoading.value = true;
    router.post(route('growfinance.setup.initialize'));
};
</script>
