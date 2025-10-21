<template>
    <MemberLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Business Growth Levels
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">7-Level Business Growth Path</h1>
                    <p class="text-lg text-gray-600">Your entrepreneurial journey from Associate to Ambassador</p>
                    <div class="mt-4 inline-flex items-center px-4 py-2 bg-blue-100 rounded-lg">
                        <span class="text-sm font-medium text-blue-800">Your Lifetime Points:</span>
                        <span class="ml-2 text-xl font-bold text-blue-900">{{ lifetimePoints.toLocaleString() }} LP</span>
                    </div>
                </div>

                <!-- Levels Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <div 
                        v-for="level in levels" 
                        :key="level.level"
                        :class="[
                            'bg-white rounded-lg shadow-lg p-6 transition-all duration-300',
                            level.isCurrentLevel ? 'ring-4 ring-blue-500 ring-offset-2' : '',
                            level.isAchieved ? 'border-2 border-green-500' : 'border-2 border-gray-200',
                            'hover:shadow-xl'
                        ]"
                    >
                        <!-- Level Badge -->
                        <div class="text-center mb-6">
                            <div 
                                :class="[
                                    'inline-flex items-center justify-center w-16 h-16 rounded-full mb-4',
                                    level.isCurrentLevel ? 'bg-blue-600 text-white' : 
                                    level.isAchieved ? 'bg-green-600 text-white' : 
                                    'bg-gray-200 text-gray-600'
                                ]"
                            >
                                <span class="text-2xl font-bold">{{ level.level }}</span>
                            </div>
                            
                            <!-- Current Level Indicator -->
                            <div v-if="level.isCurrentLevel" class="mb-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-600 text-white">
                                    CURRENT LEVEL
                                </span>
                            </div>
                            
                            <!-- Achieved Indicator -->
                            <div v-else-if="level.isAchieved" class="mb-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-600 text-white">
                                    ✓ ACHIEVED
                                </span>
                            </div>
                            
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ level.name }}</h3>
                            <div class="text-sm text-gray-600 mb-3">{{ level.networkSize }} network members</div>
                            <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ level.role }}
                            </div>
                        </div>

                        <!-- Requirements -->
                        <div class="space-y-3 mb-6">
                            <div class="text-center">
                                <div class="text-sm text-gray-600 mb-1">Lifetime Points Required</div>
                                <div class="text-lg font-bold text-indigo-600">
                                    {{ level.lpRequired === 0 ? 'None' : level.lpRequired.toLocaleString() + ' LP' }}
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="text-sm text-gray-600 mb-1">Monthly Qualification</div>
                                <div class="text-lg font-bold text-blue-600">{{ level.mapRequired }} MAP</div>
                            </div>
                            <div class="text-center">
                                <div class="text-sm text-gray-600 mb-1">Minimum Time</div>
                                <div class="text-sm font-medium text-gray-900">{{ level.minTime }}</div>
                            </div>
                        </div>

                        <!-- Progress Bar (for non-achieved levels) -->
                        <div v-if="!level.isAchieved && level.lpRequired > 0" class="mb-6">
                            <div class="flex justify-between text-xs text-gray-600 mb-1">
                                <span>Progress</span>
                                <span>{{ Math.round(level.progressPercentage) }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div 
                                    class="bg-gradient-to-r from-blue-500 to-indigo-600 h-2 rounded-full transition-all duration-300"
                                    :style="{ width: `${level.progressPercentage}%` }"
                                ></div>
                            </div>
                        </div>

                        <!-- Additional Requirements -->
                        <div class="mb-6">
                            <div class="text-xs font-semibold text-gray-700 mb-2">Requirements:</div>
                            <div class="text-xs text-gray-600 bg-gray-50 rounded p-2">
                                {{ level.additionalReqs }}
                            </div>
                        </div>

                        <!-- Milestone Bonus -->
                        <div v-if="level.milestoneBonus" class="mb-6 text-center">
                            <div class="text-xs text-gray-600 mb-1">Milestone Bonus</div>
                            <div class="text-lg font-bold text-green-600">{{ level.milestoneBonus }}</div>
                        </div>

                        <!-- Profit Share Multiplier -->
                        <div class="mb-6 text-center">
                            <div class="text-xs text-gray-600 mb-1">Profit Share Multiplier</div>
                            <div class="text-lg font-bold text-purple-600">{{ level.profitShareMultiplier }}</div>
                        </div>

                        <!-- Benefits -->
                        <div>
                            <div class="text-xs font-semibold text-gray-700 mb-2">Benefits:</div>
                            <ul class="space-y-2">
                                <li 
                                    v-for="(benefit, index) in level.benefits" 
                                    :key="index"
                                    class="flex items-start text-xs text-gray-700"
                                >
                                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>{{ benefit }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- How to Progress Section -->
                <div class="mt-12 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">How to Progress Through Levels</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-white rounded-lg p-6 shadow-md">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <h3 class="font-semibold text-lg mb-2 text-gray-900">Build Your Network</h3>
                            <p class="text-gray-600 text-sm">Refer new members and help them succeed. Earn LP for direct and indirect referrals.</p>
                        </div>
                        
                        <div class="bg-white rounded-lg p-6 shadow-md">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <h3 class="font-semibold text-lg mb-2 text-gray-900">Complete Training</h3>
                            <p class="text-gray-600 text-sm">Take courses and earn certifications. Each completed course adds to your LP total.</p>
                        </div>
                        
                        <div class="bg-white rounded-lg p-6 shadow-md">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="font-semibold text-lg mb-2 text-gray-900">Stay Active</h3>
                            <p class="text-gray-600 text-sm">Meet monthly MAP requirements through daily logins, purchases, and community engagement.</p>
                        </div>
                    </div>
                </div>

                <!-- CTA -->
                <div class="mt-8 text-center">
                    <Link 
                        :href="route('mygrownet.membership.show')" 
                        class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-lg font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                    >
                        View My Business Profile
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
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
    levels: any[];
    currentLevel: string;
    lifetimePoints: number;
}

defineProps<Props>();
</script>
