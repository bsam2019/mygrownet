<template>
    <div class="min-h-screen bg-gray-50">
        <Navigation />
        
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl font-bold mb-4">7-Level Professional Progression</h1>
                <p class="text-xl text-blue-100">Grow from Associate to Ambassador through skill development and community building</p>
                <p class="text-sm text-blue-200 mt-2">Subscription-based membership • No investment required</p>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Professional Levels Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-12">
                <div v-for="level in professionalLevels" :key="level.name" 
                     :class="[level.color, 'bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-all duration-300 border-2']">
                    <div class="text-center mb-6">
                        <div :class="[level.badgeColor, 'inline-flex items-center justify-center w-16 h-16 rounded-full mb-4']">
                            <span class="text-2xl font-bold">{{ level.level }}</span>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ level.name }}</h3>
                        <div class="text-sm text-gray-600 mb-3">{{ level.networkSize }} network members</div>
                        <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ level.role }}
                        </div>
                    </div>
                    
                    <div class="space-y-3 mb-6">
                        <div class="text-center">
                            <div class="text-sm text-gray-600 mb-1">Monthly Qualification</div>
                            <div class="text-lg font-bold text-blue-600">{{ level.mapRequired }} MAP</div>
                        </div>
                        <div class="text-center">
                            <div class="text-sm text-gray-600 mb-1">Lifetime Points</div>
                            <div class="text-lg font-bold text-indigo-600">{{ level.lpRequired }} LP</div>
                        </div>
                    </div>
                    
                    <ul class="space-y-2 mb-6">
                        <li v-for="benefit in level.benefits" :key="benefit" 
                            class="flex items-start text-sm text-gray-700">
                            <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>{{ benefit }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Key Features -->
            <div class="grid md:grid-cols-3 gap-8 mb-12">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="font-semibold text-lg mb-2 text-gray-900">Learn & Grow</h3>
                    <p class="text-gray-600">Access educational content, mentorship, and skill-building workshops at every level</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="font-semibold text-lg mb-2 text-gray-900">Earn Multiple Ways</h3>
                    <p class="text-gray-600">Referral bonuses, level commissions, milestone rewards, and quarterly profit-sharing</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="font-semibold text-lg mb-2 text-gray-900">Build Your Network</h3>
                    <p class="text-gray-600">3×3 forced matrix with spillover, up to 3,279 members across 7 levels</p>
                </div>
            </div>

            <!-- Points System Overview -->
            <div class="bg-gradient-to-r from-indigo-50 to-blue-50 rounded-lg p-8 mb-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Dual Points System</h2>
                <div class="grid md:grid-cols-2 gap-8">
                    <div class="bg-white rounded-lg p-6 shadow-md">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Lifetime Points (LP)</h3>
                        </div>
                        <p class="text-gray-600 mb-4">Accumulate forever to advance through professional levels</p>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li class="flex items-start">
                                <span class="text-indigo-600 mr-2">•</span>
                                <span>Never expire or reset</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-indigo-600 mr-2">•</span>
                                <span>Determines your professional level</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-indigo-600 mr-2">•</span>
                                <span>Earned through network building and engagement</span>
                            </li>
                        </ul>
                    </div>
                    <div class="bg-white rounded-lg p-6 shadow-md">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Monthly Activity Points (MAP)</h3>
                        </div>
                        <p class="text-gray-600 mb-4">Reset monthly, required to qualify for earnings</p>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li class="flex items-start">
                                <span class="text-blue-600 mr-2">•</span>
                                <span>Reset to 0 on the 1st of each month</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-blue-600 mr-2">•</span>
                                <span>Must meet minimum MAP to receive earnings</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-blue-600 mr-2">•</span>
                                <span>Streak multipliers for consecutive active months</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Profit Sharing -->
            <div class="bg-white rounded-lg shadow-lg p-8 mb-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 text-center">Quarterly Profit-Sharing</h2>
                <p class="text-gray-600 text-center mb-8 max-w-3xl mx-auto">
                    All active members receive direct profit-sharing from MyGrowNet's community empowerment projects. 
                    60% of project profits distributed quarterly to members.
                </p>
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="bg-green-50 rounded-lg p-6">
                        <h3 class="font-semibold text-lg mb-3 text-gray-900">50% Equal Share</h3>
                        <p class="text-gray-600">All active members receive an equal portion regardless of level</p>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-6">
                        <h3 class="font-semibold text-lg mb-3 text-gray-900">50% Weighted Share</h3>
                        <p class="text-gray-600">Distributed proportionally based on professional level (Ambassadors earn more)</p>
                    </div>
                </div>
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        <strong>Active Member:</strong> Current subscription paid + logged in within last 30 days
                    </p>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg p-8 text-center text-white">
                <h2 class="text-3xl font-bold mb-4">Ready to Start Your Journey?</h2>
                <p class="text-xl text-blue-100 mb-6">Join MyGrowNet today and begin your path from Associate to Ambassador</p>
                <Link :href="route('register')" 
                      class="inline-block bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-blue-50 transition-colors shadow-lg">
                    Get Started Now
                </Link>
                <p class="text-sm text-blue-200 mt-4">No investment required • Subscription-based membership</p>
            </div>
        </div>

        <Footer />
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Navigation from '@/components/custom/Navigation.vue';
import Footer from '@/components/custom/Footer.vue';

const professionalLevels = [
    {
        level: 1,
        name: 'Associate',
        networkSize: '3',
        role: 'New member, learning',
        mapRequired: 100,
        lpRequired: 0,
        minTime: 'Immediate',
        additionalReqs: 'Registration complete',
        milestoneBonus: null,
        profitShareMultiplier: '1.0x',
        color: 'border-gray-300',
        badgeColor: 'bg-gray-100 text-gray-700',
        benefits: [
            'Basic educational content',
            'Peer circle access',
            '7-level commission structure',
            'Monthly qualification: 100 MAP',
            'Profit-sharing: 1.0x base share'
        ]
    },
    {
        level: 2,
        name: 'Professional',
        networkSize: '9',
        role: 'Skilled member, applying',
        mapRequired: 200,
        lpRequired: 500,
        minTime: '1 month',
        additionalReqs: '3 direct referrals',
        milestoneBonus: 'K500',
        profitShareMultiplier: '1.2x',
        color: 'border-blue-300',
        badgeColor: 'bg-blue-100 text-blue-700',
        benefits: [
            'Advanced educational content',
            'Group mentorship access',
            'Enhanced commission rates',
            'Monthly qualification: 200 MAP',
            'Profit-sharing: 1.2x base share'
        ]
    },
    {
        level: 3,
        name: 'Senior',
        networkSize: '27',
        role: 'Experienced, team building',
        mapRequired: 300,
        lpRequired: 1500,
        minTime: '3 months',
        additionalReqs: '2 active directs, 1 course',
        milestoneBonus: 'K1,500',
        profitShareMultiplier: '1.5x',
        color: 'border-green-300',
        badgeColor: 'bg-green-100 text-green-700',
        benefits: [
            'Premium content library',
            '1-on-1 mentorship sessions',
            'Team building bonuses',
            'Monthly qualification: 300 MAP',
            'Profit-sharing: 1.5x base share'
        ]
    },
    {
        level: 4,
        name: 'Manager',
        networkSize: '81',
        role: 'Team leader',
        mapRequired: 400,
        lpRequired: 4000,
        minTime: '6 months',
        additionalReqs: '1 Professional in downline, 3 courses',
        milestoneBonus: 'K5,000',
        profitShareMultiplier: '2.0x',
        color: 'border-purple-300',
        badgeColor: 'bg-purple-100 text-purple-700',
        benefits: [
            'Leadership training programs',
            'Team performance bonuses',
            'Booster fund: K5,000',
            'Monthly qualification: 400 MAP',
            'Profit-sharing: 2.0x base share'
        ]
    },
    {
        level: 5,
        name: 'Director',
        networkSize: '243',
        role: 'Strategic leader',
        mapRequired: 500,
        lpRequired: 10000,
        minTime: '12 months',
        additionalReqs: '1 Senior in downline, 5 courses',
        milestoneBonus: 'K15,000',
        profitShareMultiplier: '2.5x',
        color: 'border-indigo-300',
        badgeColor: 'bg-indigo-100 text-indigo-700',
        benefits: [
            'Strategic leadership content',
            'Business facilitation services',
            'Booster fund: K15,000',
            'Monthly qualification: 500 MAP',
            'Profit-sharing: 2.5x base share'
        ]
    },
    {
        level: 6,
        name: 'Executive',
        networkSize: '729',
        role: 'Top performer',
        mapRequired: 600,
        lpRequired: 25000,
        minTime: '18 months',
        additionalReqs: '1 Manager in downline, 10 courses',
        milestoneBonus: 'K50,000',
        profitShareMultiplier: '3.0x',
        color: 'border-yellow-300',
        badgeColor: 'bg-yellow-100 text-yellow-700',
        benefits: [
            'Executive coaching access',
            'Innovation lab participation',
            'Booster fund: K50,000',
            'Monthly qualification: 600 MAP',
            'Profit-sharing: 3.0x base share'
        ]
    },
    {
        level: 7,
        name: 'Ambassador',
        networkSize: '2,187',
        role: 'Brand representative',
        mapRequired: 800,
        lpRequired: 50000,
        minTime: '24 months',
        additionalReqs: '1 Director in downline, 15 courses, 1 project',
        milestoneBonus: 'K150,000',
        profitShareMultiplier: '4.0x',
        color: 'border-red-300',
        badgeColor: 'bg-red-100 text-red-700',
        benefits: [
            'VIP brand ambassador status',
            'Exclusive events & retreats',
            'Booster fund: K150,000',
            'Monthly qualification: 800 MAP',
            'Profit-sharing: 4.0x base share (MAX)'
        ]
    }
];
</script>
