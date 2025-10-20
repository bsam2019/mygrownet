<template>
    <InvestorLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ title }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-12 text-center">
                        <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-blue-100 mb-6">
                            <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        
                        <h1 class="text-3xl font-bold text-gray-900 mb-4">
                            {{ title }}
                        </h1>
                        
                        <p class="text-lg text-gray-600 mb-8 max-w-2xl mx-auto">
                            This feature is currently under development and will be available soon. 
                            We're working hard to bring you the best experience possible.
                        </p>
                        
                        <div class="flex justify-center gap-4">
                            <Link 
                                :href="route('dashboard')" 
                                class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-lg font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Back to Dashboard
                            </Link>
                        </div>
                        
                        <div class="mt-12 pt-8 border-t border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                What's Available Now
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto">
                                <Link 
                                    :href="route('dashboard')" 
                                    class="p-6 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
                                >
                                    <div class="text-blue-600 mb-2">
                                        <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                        </svg>
                                    </div>
                                    <h4 class="font-semibold text-gray-900 mb-1">Dashboard</h4>
                                    <p class="text-sm text-gray-600">View your overview</p>
                                </Link>
                                
                                <Link 
                                    :href="route('points.index')" 
                                    class="p-6 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
                                >
                                    <div class="text-blue-600 mb-2">
                                        <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                    </div>
                                    <h4 class="font-semibold text-gray-900 mb-1">My Points</h4>
                                    <p class="text-sm text-gray-600">Track your LP & MAP</p>
                                </Link>
                                
                                <Link 
                                    :href="route('referrals.index')" 
                                    class="p-6 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
                                >
                                    <div class="text-blue-600 mb-2">
                                        <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <h4 class="font-semibold text-gray-900 mb-1">Referrals</h4>
                                    <p class="text-sm text-gray-600">Manage your network</p>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </InvestorLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import InvestorLayout from '@/layouts/InvestorLayout.vue';

interface Props {
    feature: string;
    title: string;
}

defineProps<Props>();
</script>
