emeb<template>
    <MemberLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                My Membership
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Member Profile Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                            <div class="flex-1">
                                <h3 class="text-2xl font-bold text-gray-900">{{ user.name }}</h3>
                                <p class="text-gray-600 mt-1">Member since {{ user.joined_at }}</p>
                                <div class="mt-2 flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                                    <span v-if="user.email" class="text-sm text-gray-500 break-all">{{ user.email }}</span>
                                    <span v-if="user.phone" class="text-sm text-gray-500">{{ user.phone }}</span>
                                </div>
                            </div>
                            <div class="md:text-right">
                                <div class="inline-flex items-center px-4 py-2 bg-blue-100 rounded-lg">
                                    <span class="text-sm font-medium text-blue-800">Member ID:</span>
                                    <span class="ml-2 text-lg font-bold text-blue-900">{{ user.referral_code }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Professional Level Card -->
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 md:p-8 text-white">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                            <div class="flex-1">
                                <p class="text-blue-100 text-sm font-medium mb-2">Current Professional Level</p>
                                <h2 class="text-3xl md:text-4xl font-bold mb-2">{{ currentLevel.name }}</h2>
                                <p class="text-blue-100 text-base md:text-lg">{{ currentLevel.role }}</p>
                                <div class="mt-4 grid grid-cols-2 gap-4 md:flex md:items-center md:gap-6">
                                    <div>
                                        <p class="text-blue-100 text-sm">Network Size</p>
                                        <p class="text-xl md:text-2xl font-bold">{{ currentLevel.networkSize }}</p>
                                    </div>
                                    <div>
                                        <p class="text-blue-100 text-sm">Profit Share Weight</p>
                                        <p class="text-xl md:text-2xl font-bold">{{ currentLevel.profitShareMultiplier }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-center md:justify-end">
                                <div class="w-24 h-24 md:w-32 md:h-32 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                    <span class="text-5xl md:text-6xl font-bold">{{ currentLevel.level }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Points Overview -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 p-3 bg-indigo-100 rounded-lg">
                                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Lifetime Points</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ points.lifetime.toLocaleString() }} LP</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 p-3 bg-blue-100 rounded-lg">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Business Points (This Month)</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ points.business }} / {{ points.required_business }} BP</p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div 
                                        class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                                        :style="{ width: `${Math.min(100, (points.business / points.required_business) * 100)}%` }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 p-3 bg-green-100 rounded-lg">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Network Size</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ network.total_network }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ network.direct_referrals }} direct</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Progress to Next Level -->
                <div v-if="nextLevel" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Progress to {{ nextLevel.name }}</h3>
                                <p class="text-sm text-gray-600">{{ progress.points_needed.toLocaleString() }} LP needed</p>
                            </div>
                            <div class="text-right">
                                <span class="text-3xl font-bold text-blue-600">{{ progress.percentage }}%</span>
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-4">
                            <div 
                                class="bg-gradient-to-r from-blue-500 to-indigo-600 h-4 rounded-full transition-all duration-300"
                                :style="{ width: `${progress.percentage}%` }"
                            ></div>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-4">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm text-gray-600 mb-1">Requirements</p>
                                <p class="text-sm font-medium text-gray-900">{{ nextLevel.additionalReqs }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm text-gray-600 mb-1">Milestone Bonus</p>
                                <p class="text-sm font-medium text-green-600">{{ nextLevel.milestoneBonus }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Level Benefits -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Your Current Benefits</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div 
                                v-for="(benefit, index) in currentLevel.benefits" 
                                :key="index"
                                class="flex items-start"
                            >
                                <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm text-gray-700">{{ benefit }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Link 
                        :href="route('points.index')" 
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow"
                    >
                        <div class="p-6 text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-100 rounded-lg mb-3">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <h4 class="font-semibold text-gray-900 mb-1">View Points History</h4>
                            <p class="text-sm text-gray-600">Track your LP & BP</p>
                        </div>
                    </Link>

                    <Link 
                        :href="route('mygrownet.levels.index')" 
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow"
                    >
                        <div class="p-6 text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 bg-indigo-100 rounded-lg mb-3">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                            <h4 class="font-semibold text-gray-900 mb-1">Professional Levels</h4>
                            <p class="text-sm text-gray-600">View 7-level progression</p>
                        </div>
                    </Link>

                    <Link 
                        :href="route('my-team.index')" 
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow"
                    >
                        <div class="p-6 text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 bg-green-100 rounded-lg mb-3">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <h4 class="font-semibold text-gray-900 mb-1">My Network</h4>
                            <p class="text-sm text-gray-600">Manage referrals</p>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import MemberLayout from '@/layouts/MemberLayout.vue';

interface Props {
    user: {
        name: string;
        email: string;
        phone: string;
        referral_code: string;
        joined_at: string;
    };
    currentLevel: any;
    nextLevel: any;
    points: {
        lifetime: number;
        business: number;
        required_business: number;
    };
    progress: {
        percentage: number;
        points_needed: number;
    };
    network: {
        direct_referrals: number;
        total_network: number;
    };
}

defineProps<Props>();
</script>
