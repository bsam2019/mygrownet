<script setup lang="ts">
import { computed, ref } from 'vue';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { formatCurrency } from '@/utils/formatting';

interface ReferralLevel {
    level: number;
    team_size: number;
    commission_percentage: number;
    potential_earnings: number;
    per_person: number;
}

interface Props {
    registrationAmount: number;
    referralBonusStructure: ReferralLevel[];
    totalPotential: number;
    totalTeamSize: number;
    commissionRates: Record<number, number>;
    levelNames: Record<number, string>;
}

const props = defineProps<Props>();

const activeSection = ref('overview');

const formatNumber = (num: number) => {
    return new Intl.NumberFormat('en-US').format(num);
};

const sections = [
    { id: 'overview', label: 'Overview' },
    { id: 'referral-bonus', label: 'Referral Bonus' },
    { id: 'position-bonus', label: 'Position Bonus' },
    { id: 'income-streams', label: '6 Income Streams' },
    { id: 'levels', label: '7 Professional Levels' },
    { id: 'points', label: 'Points System' },
    { id: 'examples', label: 'Income Examples' },
    { id: 'getting-started', label: 'Getting Started' },
];

// Position Bonus (Milestone Rewards) Data
const positionBonuses = [
    { position: 'Associate', teamSize: 3, cumulative: 3, lpEstimate: 0, milestoneBonus: '-', physicalReward: null },
    { position: 'Professional', teamSize: 9, cumulative: 12, lpEstimate: 2500, milestoneBonus: 'K500', physicalReward: null },
    { position: 'Senior', teamSize: 27, cumulative: 39, lpEstimate: 4000, milestoneBonus: 'K1,500 + Smartphone', physicalReward: 'Smartphone' },
    { position: 'Manager', teamSize: 81, cumulative: 120, lpEstimate: 12500, milestoneBonus: 'K5,000 + Motorbike', physicalReward: 'Motorbike' },
    { position: 'Director', teamSize: 243, cumulative: 363, lpEstimate: 60000, milestoneBonus: 'K15,000 + Vehicle', physicalReward: 'Vehicle' },
    { position: 'Executive', teamSize: 729, cumulative: 1092, lpEstimate: 160000, milestoneBonus: 'K50,000 + Luxury', physicalReward: 'Luxury Vehicle' },
    { position: 'Ambassador', teamSize: 2187, cumulative: 3279, lpEstimate: 350000, milestoneBonus: 'K150,000 + Property', physicalReward: 'Investment Property' },
];
</script>

<template>
    <MemberLayout>
        <div class="py-6 md:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="text-center mb-8 md:mb-12">
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        MyGrowNet Compensation Plan
                    </h1>
                    <p class="text-lg md:text-xl text-gray-600 max-w-3xl mx-auto">
                        Build your network and earn through our 7-level referral bonus system
                    </p>
                </div>

                <!-- Registration Amount Card -->
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 md:p-8 mb-8 text-white">
                    <div class="text-center">
                        <p class="text-lg md:text-xl mb-2 opacity-90">Registration Amount</p>
                        <p class="text-4xl md:text-5xl font-bold">K{{ formatNumber(registrationAmount) }}</p>
                        <p class="mt-4 text-sm md:text-base opacity-90">
                            One-time registration fee that unlocks your earning potential
                        </p>
                    </div>
                </div>

                <!-- Referral Bonus Structure -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
                    <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 px-6 py-4">
                        <h2 class="text-xl md:text-2xl font-bold text-white">
                            7-Level Referral Bonus Structure
                        </h2>
                    </div>
                    
                    <div class="p-6">
                        <!-- Desktop Table -->
                        <div class="hidden md:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Level
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Team Size
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Commission %
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Cash Value
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            BP Earned
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total BP Potential
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="level in referralBonusStructure" :key="level.level" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800 font-bold">
                                                {{ level.level }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900 font-medium">
                                            {{ formatNumber(level.team_size) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                                {{ level.commission_percentage }}%
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">
                                            K{{ formatNumber(level.per_person) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-indigo-600">
                                            {{ (level.per_person / 2).toFixed(1) }} BP
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-emerald-600">
                                            {{ formatNumber(Math.round(level.potential_earnings / 2)) }} BP
                                        </td>
                                    </tr>
                                    <tr class="bg-blue-50 font-bold">
                                        <td class="px-6 py-4 text-left text-sm text-gray-900">
                                            TOTAL
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm text-gray-900">
                                            {{ formatNumber(totalTeamSize) }}
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm text-gray-900">
                                            48%
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm text-gray-600">
                                            K{{ formatNumber(totalPotential) }}
                                        </td>
                                        <td class="px-6 py-4"></td>
                                        <td class="px-6 py-4 text-right text-lg text-emerald-600">
                                            {{ formatNumber(Math.round(totalPotential / 2)) }} BP
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Cards -->
                        <div class="md:hidden space-y-4">
                            <div v-for="level in referralBonusStructure" :key="level.level" 
                                 class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center space-x-3">
                                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-blue-100 text-blue-800 font-bold">
                                            {{ level.level }}
                                        </span>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ levelNames[level.level] }}</p>
                                            <p class="text-xs text-gray-500">Level {{ level.level }}</p>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                        {{ level.commission_percentage }}%
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <div>
                                        <p class="text-gray-500">Team Size</p>
                                        <p class="font-medium text-gray-900">{{ formatNumber(level.team_size) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Cash Value</p>
                                        <p class="font-medium text-gray-900">K{{ formatNumber(level.per_person) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">BP Per Person</p>
                                        <p class="font-bold text-indigo-600">{{ (level.per_person / 2).toFixed(1) }} BP</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Total BP Potential</p>
                                        <p class="text-lg font-bold text-emerald-600">{{ formatNumber(Math.round(level.potential_earnings / 2)) }} BP</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Mobile Total -->
                            <div class="border-2 border-emerald-500 rounded-lg p-4 bg-emerald-50">
                                <div class="text-center">
                                    <p class="text-sm text-gray-600 mb-1">Maximum BP Potential</p>
                                    <p class="text-2xl font-bold text-emerald-600">{{ formatNumber(Math.round(totalPotential / 2)) }} BP</p>
                                    <p class="text-xs text-gray-500 mt-1">With {{ formatNumber(totalTeamSize) }} team members</p>
                                    <p class="text-xs text-gray-500">(Cash value: K{{ formatNumber(totalPotential) }})</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Position Bonus (Milestone Rewards) -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
                    <div class="bg-gradient-to-r from-purple-500 to-indigo-600 px-6 py-4">
                        <h2 class="text-xl md:text-2xl font-bold text-white flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            Position Bonus (Milestone Rewards)
                        </h2>
                        <p class="text-purple-100 text-sm mt-1">Bonus pool distribution based on team performance</p>
                    </div>
                    
                    <div class="p-6">
                        <!-- Desktop Table -->
                        <div class="hidden md:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-purple-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-purple-900 uppercase tracking-wider">
                                            Position
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-purple-900 uppercase tracking-wider">
                                            Team Size
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-purple-900 uppercase tracking-wider">
                                            Cumulative
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-purple-900 uppercase tracking-wider">
                                            LP Estimate
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-purple-900 uppercase tracking-wider">
                                            Milestone Bonus
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="bonus in positionBonuses" :key="bonus.position" class="hover:bg-purple-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                {{ bonus.position }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900 font-medium">
                                            {{ formatNumber(bonus.teamSize) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">
                                            {{ formatNumber(bonus.cumulative) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-indigo-600">
                                            {{ formatNumber(bonus.lpEstimate) }} LP
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-emerald-600">
                                            {{ bonus.milestoneBonus }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Cards -->
                        <div class="md:hidden space-y-4">
                            <div v-for="bonus in positionBonuses" :key="bonus.position" 
                                 class="border border-purple-200 rounded-lg p-4 hover:shadow-md transition-shadow bg-purple-50">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-600 text-white">
                                        {{ bonus.position }}
                                    </span>
                                </div>
                                <div class="space-y-3 text-sm">
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <p class="text-purple-700">Team Size</p>
                                            <p class="font-medium text-gray-900">{{ formatNumber(bonus.teamSize) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-purple-700">Cumulative</p>
                                            <p class="font-medium text-gray-900">{{ formatNumber(bonus.cumulative) }}</p>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-purple-700">LP Estimate</p>
                                        <p class="text-lg font-bold text-indigo-600">{{ formatNumber(bonus.lpEstimate) }} LP</p>
                                    </div>
                                    <div>
                                        <p class="text-purple-700">Milestone Bonus</p>
                                        <p class="text-base font-bold text-emerald-600">{{ bonus.milestoneBonus }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- How It Works -->
                <div class="bg-white rounded-lg shadow-lg p-6 md:p-8 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">How It Works</h2>
                    <div class="space-y-4">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800 font-bold">
                                    1
                                </span>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">Registration Commission (Converted to BP)</h3>
                                <p class="text-gray-600">
                                    When someone in your network pays the K500 registration fee, you earn a commission percentage that's <strong>converted to BP</strong> (Bonus Points) at K2 per BP.
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800 font-bold">
                                    2
                                </span>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">7 Levels Deep (BP Earnings)</h3>
                                <p class="text-gray-600">
                                    <strong>Level 1 (Direct referrals):</strong> 15% × K500 = K75 → <strong class="text-indigo-600">37.5 BP</strong><br>
                                    <strong>Level 2 (Referrals of referrals):</strong> 10% × K500 = K50 → <strong class="text-indigo-600">25 BP</strong><br>
                                    <strong>Level 3:</strong> 8% × K500 = K40 → <strong class="text-indigo-600">20 BP</strong><br>
                                    And so on through 7 levels deep!
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800 font-bold">
                                    3
                                </span>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">Maximum BP Potential</h3>
                                <p class="text-gray-600">
                                    If you fill all <strong>{{ formatNumber(totalTeamSize) }} positions</strong> in your 7-level network, 
                                    you could earn <strong class="text-emerald-600">{{ formatNumber(Math.round(totalPotential / 2)) }} BP</strong> 
                                    (cash value: K{{ formatNumber(totalPotential) }}) in referral bonuses!
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 6 Income Streams -->
                <div class="bg-white rounded-lg shadow-lg p-6 md:p-8 mb-8">
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-6">6 Powerful Income Streams</h2>
                    
                    <!-- Income Stream 1 -->
                    <div class="mb-8">
                        <h3 class="text-xl font-bold text-blue-600 mb-3">1. Monthly Bonus Pool (BP-Based Distribution)</h3>
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4">
                            <p class="font-semibold mb-2">60% of monthly company profits distributed based on your BP share!</p>
                            <p class="text-gray-700 text-sm mb-2"><strong>Profit Sources:</strong> MyGrow Investments returns, MyGrow Shop profits, Partnership revenues, Service fees</p>
                            <p class="text-gray-700">Formula: <strong>Your Bonus = (Your BP ÷ Total BP) × 60% of Monthly Profit</strong></p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="font-semibold mb-2">How You Earn BP:</p>
                            <ul class="text-sm text-gray-700 space-y-1 mb-3">
                                <li>• <strong>Referrals:</strong> 37.5 BP per Level 1 referral (from K75 commission)</li>
                                <li>• <strong>Product Sales:</strong> 10-20 BP per K100 sold</li>
                                <li>• <strong>Courses:</strong> 30-100 BP per completion</li>
                                <li>• <strong>Daily Login:</strong> 5 BP/day, 50 BP/week streak, 200 BP/month streak</li>
                                <li>• <strong>Subscription Renewal:</strong> 25 BP/month</li>
                            </ul>
                            <p class="font-semibold mb-2">Example Distribution:</p>
                            <p class="text-sm text-gray-700">Company Monthly Profit: K100,000 | Bonus Pool (60%): K60,000</p>
                            <p class="text-sm text-gray-700">You earned: 500 BP out of 50,000 total BP in platform</p>
                            <p class="text-lg font-bold text-emerald-600 mt-2">Your Share = K600</p>
                            <p class="text-xs text-gray-500 mt-2">The more BP you earn through activities, the bigger your monthly bonus!</p>
                        </div>
                    </div>

                    <!-- Income Stream 2 -->
                    <div class="mb-8">
                        <h3 class="text-xl font-bold text-blue-600 mb-3">2. Referral Commissions (Converted to BP)</h3>
                        <p class="text-gray-700 mb-2">Earn BP when you refer new members:</p>
                        <ul class="list-disc list-inside text-gray-700 space-y-1">
                            <li><strong>Level 1:</strong> 37.5 BP per direct referral (K75 value)</li>
                            <li><strong>Levels 2-7:</strong> Decreasing BP from network growth</li>
                            <li>BP accumulates for monthly bonus pool distribution</li>
                            <li>No limit on referrals or BP earnings</li>
                        </ul>
                    </div>

                    <!-- Income Stream 3 -->
                    <div class="mb-8">
                        <h3 class="text-xl font-bold text-blue-600 mb-3">3. Network Commissions (7 Levels)</h3>
                        <p class="text-gray-700 mb-4">Earn from your entire network across 7 professional levels - see table above for details</p>
                    </div>

                    <!-- Income Stream 4 -->
                    <div class="mb-8">
                        <h3 class="text-xl font-bold text-blue-600 mb-3">4. Product Sales Commissions</h3>
                        <p class="text-gray-700 mb-2">Earn from MyGrow Shop sales:</p>
                        <ul class="list-disc list-inside text-gray-700 space-y-1">
                            <li><strong>Personal purchases:</strong> 10 BP per K100 spent</li>
                            <li><strong>Direct referral sales:</strong> 20 BP per K100</li>
                            <li><strong>Network sales:</strong> 5 BP per K100</li>
                        </ul>
                    </div>

                    <!-- Income Stream 5 -->
                    <div class="mb-8">
                        <h3 class="text-xl font-bold text-blue-600 mb-3">5. Quarterly Profit-Sharing (Investment Projects)</h3>
                        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 mb-3">
                            <p class="font-semibold mb-2">60% of investment project profits shared with ALL active members!</p>
                            <p class="text-sm text-gray-700">MyGrowNet invests in profitable community projects (agriculture, manufacturing, real estate, services). Every active member receives a share of the profits.</p>
                        </div>
                        <p class="text-gray-700 mb-2">Distribution Structure:</p>
                        <ul class="list-disc list-inside text-gray-700 space-y-1 mb-3">
                            <li><strong>50% Equal Share:</strong> All active members receive equal portion</li>
                            <li><strong>50% Weighted Share:</strong> Based on professional level (Associate: 1.0x → Ambassador: 4.0x)</li>
                            <li><strong>Paid Quarterly:</strong> Every 3 months</li>
                            <li><strong>Passive Income:</strong> Grows with company investments</li>
                        </ul>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="font-semibold mb-2">Example:</p>
                            <p class="text-sm text-gray-700">Project Profit: K100,000 | Member Share (60%): K60,000</p>
                            <p class="text-sm text-gray-700">Equal Share (K30,000) ÷ 1,000 active members = K30 each</p>
                            <p class="text-sm text-gray-700">Weighted Share (K30,000) based on level multiplier</p>
                            <p class="text-sm text-gray-700 mt-2"><strong>Associate (1.0x):</strong> K30 + K20 = <strong class="text-emerald-600">K50 total</strong></p>
                            <p class="text-sm text-gray-700"><strong>Ambassador (4.0x):</strong> K30 + K80 = <strong class="text-emerald-600">K110 total</strong></p>
                        </div>
                    </div>

                    <!-- Income Stream 6 -->
                    <div class="mb-8">
                        <h3 class="text-xl font-bold text-blue-600 mb-3">6. Milestone Rewards</h3>
                        <p class="text-gray-700 mb-4">Earn bonuses as you advance through professional levels:</p>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cash Bonus</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Physical Reward</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr><td class="px-4 py-2">Professional</td><td class="px-4 py-2 font-semibold text-emerald-600">K500</td><td class="px-4 py-2">-</td></tr>
                                    <tr><td class="px-4 py-2">Senior</td><td class="px-4 py-2 font-semibold text-emerald-600">K1,500</td><td class="px-4 py-2">Smartphone</td></tr>
                                    <tr><td class="px-4 py-2">Manager</td><td class="px-4 py-2 font-semibold text-emerald-600">K5,000</td><td class="px-4 py-2">Motorbike</td></tr>
                                    <tr><td class="px-4 py-2">Director</td><td class="px-4 py-2 font-semibold text-emerald-600">K15,000</td><td class="px-4 py-2">Vehicle</td></tr>
                                    <tr><td class="px-4 py-2">Executive</td><td class="px-4 py-2 font-semibold text-emerald-600">K50,000</td><td class="px-4 py-2">Luxury Rewards</td></tr>
                                    <tr><td class="px-4 py-2">Ambassador</td><td class="px-4 py-2 font-semibold text-emerald-600">K150,000</td><td class="px-4 py-2">Property</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Points System -->
                <div class="bg-white rounded-lg shadow-lg p-6 md:p-8 mb-8">
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-6">The Dual-Point System</h2>
                    
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4">
                            <h3 class="text-lg font-bold text-blue-600 mb-2">Life Points (LP)</h3>
                            <p class="text-sm text-gray-700 mb-2">Your Career Progression</p>
                            <ul class="text-sm text-gray-700 space-y-1">
                                <li>✓ Never expire - accumulate forever</li>
                                <li>✓ Determine professional level</li>
                                <li>✓ Unlock leadership benefits</li>
                                <li>✓ Measure long-term commitment</li>
                            </ul>
                        </div>
                        
                        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4">
                            <h3 class="text-lg font-bold text-emerald-600 mb-2">Bonus Points (BP)</h3>
                            <p class="text-sm text-gray-700 mb-2">Your Monthly Earnings</p>
                            <ul class="text-sm text-gray-700 space-y-1">
                                <li>✓ Reset monthly - fresh start</li>
                                <li>✓ Determine bonus pool share</li>
                                <li>✓ Reward current activity</li>
                                <li>✓ Drive immediate income</li>
                            </ul>
                        </div>
                    </div>

                    <h3 class="text-xl font-bold text-gray-900 mb-4">How to Earn Points</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Activity</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">LP</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">BP</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 text-sm">
                                <tr class="bg-green-50">
                                    <td class="px-4 py-2 font-semibold text-green-900">Registration (Welcome Package)</td>
                                    <td class="px-4 py-2 text-center font-bold text-green-600">25 LP</td>
                                    <td class="px-4 py-2 text-center font-bold text-green-600">K225 cash</td>
                                </tr>
                                <tr class="bg-blue-50">
                                    <td class="px-4 py-2 font-semibold text-blue-900">Level 1 Referral Commission</td>
                                    <td class="px-4 py-2 text-center font-bold text-blue-600">-</td>
                                    <td class="px-4 py-2 text-center font-bold text-blue-600">37.5 BP</td>
                                </tr>
                                <tr><td class="px-4 py-2">Direct referral (they get)</td><td class="px-4 py-2 text-center font-semibold">25 LP</td><td class="px-4 py-2 text-center font-semibold">K225 cash</td></tr>
                                <tr><td class="px-4 py-2">Complete basic course</td><td class="px-4 py-2 text-center">30</td><td class="px-4 py-2 text-center">30</td></tr>
                                <tr><td class="px-4 py-2">Complete advanced course</td><td class="px-4 py-2 text-center">60</td><td class="px-4 py-2 text-center">60</td></tr>
                                <tr><td class="px-4 py-2">Attend workshop</td><td class="px-4 py-2 text-center">50</td><td class="px-4 py-2 text-center">50</td></tr>
                                <tr><td class="px-4 py-2">Monthly subscription</td><td class="px-4 py-2 text-center">25</td><td class="px-4 py-2 text-center">25</td></tr>
                                <tr><td class="px-4 py-2">Personal purchase (per K100)</td><td class="px-4 py-2 text-center">10</td><td class="px-4 py-2 text-center">10</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-6 p-6 bg-gradient-to-r from-emerald-50 to-teal-50 rounded-lg border border-emerald-200">
                        <h4 class="text-lg font-bold text-emerald-900 mb-3">🎯 How Referral Commissions Work</h4>
                        <div class="grid md:grid-cols-2 gap-4 text-sm text-emerald-900">
                            <div>
                                <p class="font-semibold mb-2">Registration Package:</p>
                                <ul class="space-y-1 ml-4">
                                    <li>• New member pays <strong>K500</strong></li>
                                    <li>• They receive <strong>25 LP + K225 cash</strong></li>
                                    <li>• Total value: <strong>K345</strong></li>
                                </ul>
                            </div>
                            <div>
                                <p class="font-semibold mb-2">Your Commission (Level 1):</p>
                                <ul class="space-y-1 ml-4">
                                    <li>• You earn <strong>15%</strong> commission = K75</li>
                                    <li>• K75 converted to <strong>37.5 BP</strong> (at K2/BP)</li>
                                    <li>• BP accumulates for monthly bonus pool</li>
                                    <li>• Higher levels earn less BP per referral</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Income Potential -->
                <div class="bg-white rounded-lg shadow-lg p-6 md:p-8 mb-8">
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-6">Income Potential by Level</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Monthly Income</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr><td class="px-6 py-3">Associate</td><td class="px-6 py-3 text-right font-semibold text-gray-900">K100 - K300</td></tr>
                                <tr><td class="px-6 py-3">Professional</td><td class="px-6 py-3 text-right font-semibold text-gray-900">K300 - K800</td></tr>
                                <tr><td class="px-6 py-3">Senior</td><td class="px-6 py-3 text-right font-semibold text-gray-900">K800 - K2,000</td></tr>
                                <tr><td class="px-6 py-3">Manager</td><td class="px-6 py-3 text-right font-semibold text-emerald-600">K2,000 - K5,000</td></tr>
                                <tr><td class="px-6 py-3">Director</td><td class="px-6 py-3 text-right font-semibold text-emerald-600">K5,000 - K12,000</td></tr>
                                <tr><td class="px-6 py-3">Executive</td><td class="px-6 py-3 text-right font-semibold text-emerald-600">K12,000 - K25,000</td></tr>
                                <tr><td class="px-6 py-3">Ambassador</td><td class="px-6 py-3 text-right font-bold text-emerald-600">K25,000 - K50,000+</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Getting Started -->
                <div class="bg-white rounded-lg shadow-lg p-6 md:p-8 mb-8">
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-6">Getting Started</h2>
                    
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-bold text-blue-600 mb-3">Step 1: Register</h3>
                            <p class="text-gray-700">Pay K500 one-time registration fee</p>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-bold text-blue-600 mb-3">Step 2: Get Your Welcome Package</h3>
                            <p class="text-gray-700">Receive <strong>25 LP</strong> (Lifetime Points) + <strong>K225 cash bonus</strong> upon registration (Total value: K345)</p>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-bold text-blue-600 mb-3">Step 3: Start Earning</h3>
                            <p class="text-gray-700">Complete courses, make referrals, and engage daily</p>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-bold text-blue-600 mb-3">Step 4: Build Your Network</h3>
                            <p class="text-gray-700">Use the 3×3 matrix system with spillover benefits</p>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-bold text-blue-600 mb-3">Step 5: Track Progress</h3>
                            <p class="text-gray-700">Monitor your BP, earnings, and level advancement</p>
                        </div>
                    </div>
                </div>

                <!-- Key Features -->
                <div class="bg-gradient-to-r from-blue-50 to-emerald-50 rounded-lg p-6 md:p-8 mb-8">
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-6">Key Features</h2>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="flex items-start space-x-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span class="text-gray-700">Legal & Compliant - Registered company</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span class="text-gray-700">Multiple Income Streams</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span class="text-gray-700">Fair BP-based Distribution</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span class="text-gray-700">7 Professional Levels</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span class="text-gray-700">Activity Multipliers (up to 1.5x)</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span class="text-gray-700">30-Day Money-Back Guarantee</span>
                        </div>
                    </div>
                </div>

                <!-- Why MyGrowNet is Different -->
                <div class="bg-white rounded-lg shadow-lg p-6 md:p-8 mb-8">
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-6">Why MyGrowNet is Different</h2>
                    
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-bold text-blue-600 mb-3">vs Traditional MLM</h3>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-gray-500 mb-1">Traditional MLM</p>
                                    <ul class="text-sm text-gray-700 space-y-1">
                                        <li>• Recruitment only focus</li>
                                        <li>• Often overpriced products</li>
                                        <li>• Fixed commissions</li>
                                        <li>• Often unclear earnings</li>
                                    </ul>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-emerald-600 mb-1">MyGrowNet</p>
                                    <ul class="text-sm text-gray-700 space-y-1">
                                        <li>✓ Multiple income streams</li>
                                        <li>✓ Real educational value</li>
                                        <li>✓ Fair BP-based distribution</li>
                                        <li>✓ Clear calculations</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-bold text-blue-600 mb-3">vs Investment Schemes</h3>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-gray-500 mb-1">Investment Schemes</p>
                                    <ul class="text-sm text-gray-700 space-y-1">
                                        <li>• Often illegal</li>
                                        <li>• Guaranteed returns (unsustainable)</li>
                                        <li>• No real products</li>
                                        <li>• Pyramid collapse</li>
                                    </ul>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-emerald-600 mb-1">MyGrowNet</p>
                                    <ul class="text-sm text-gray-700 space-y-1">
                                        <li>✓ Registered company</li>
                                        <li>✓ Returns based on effort</li>
                                        <li>✓ Real subscriptions & services</li>
                                        <li>✓ Multiple revenue sources</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FAQ -->
                <div class="bg-white rounded-lg shadow-lg p-6 md:p-8 mb-8">
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-6">Frequently Asked Questions</h2>
                    
                    <div class="space-y-4">
                        <div class="border-l-4 border-blue-500 bg-blue-50 p-4">
                            <h3 class="font-bold text-gray-900 mb-2">Q: Is this an investment scheme?</h3>
                            <p class="text-gray-700"><strong>A:</strong> No. MyGrowNet is a subscription-based platform. You pay for products, services, and learning materials, not pooled investments.</p>
                        </div>

                        <div class="border-l-4 border-blue-500 bg-blue-50 p-4">
                            <h3 class="font-bold text-gray-900 mb-2">Q: How much can I really earn?</h3>
                            <p class="text-gray-700"><strong>A:</strong> It depends on your effort. Part-time members earn K300-K1,000/month. Full-time builders can earn K5,000-K50,000/month.</p>
                        </div>

                        <div class="border-l-4 border-blue-500 bg-blue-50 p-4">
                            <h3 class="font-bold text-gray-900 mb-2">Q: Do I need to recruit to earn?</h3>
                            <p class="text-gray-700"><strong>A:</strong> No. You can earn through learning, product sales, and engagement. Recruitment is just one of many earning paths.</p>
                        </div>

                        <div class="border-l-4 border-blue-500 bg-blue-50 p-4">
                            <h3 class="font-bold text-gray-900 mb-2">Q: When do I get paid?</h3>
                            <p class="text-gray-700"><strong>A:</strong> Direct commissions are instant. Monthly bonuses are paid by the 7th of each month. Quarterly profit-sharing every 3 months.</p>
                        </div>

                        <div class="border-l-4 border-blue-500 bg-blue-50 p-4">
                            <h3 class="font-bold text-gray-900 mb-2">Q: Is there a joining fee?</h3>
                            <p class="text-gray-700"><strong>A:</strong> Yes, a one-time registration fee of <strong>K500</strong>.</p>
                        </div>
                    </div>
                </div>

                <!-- Important Notice -->
                <div class="bg-amber-50 border-l-4 border-amber-500 rounded-lg p-6 md:p-8 mb-8">
                    <h2 class="text-xl font-bold text-amber-900 mb-4">Important Notice</h2>
                    <p class="text-gray-700 mb-4">
                        MyGrowNet is <strong>not a "get rich quick" scheme</strong>. It's a legitimate business opportunity that rewards:
                    </p>
                    <ul class="space-y-2 text-gray-700">
                        <li class="flex items-start">
                            <span class="text-amber-600 font-bold mr-2">•</span>
                            <span><strong>Learning</strong> - Develop valuable skills</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-amber-600 font-bold mr-2">•</span>
                            <span><strong>Effort</strong> - Work consistently</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-amber-600 font-bold mr-2">•</span>
                            <span><strong>Leadership</strong> - Help others succeed</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-amber-600 font-bold mr-2">•</span>
                            <span><strong>Patience</strong> - Build long-term wealth</span>
                        </li>
                    </ul>
                    <p class="text-gray-700 mt-4 font-semibold">
                        Your success depends on YOU. We provide the platform, training, and support. You provide the commitment, effort, and consistency.
                    </p>
                </div>

                <!-- Legal Disclaimer -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 md:p-8 mb-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Legal Disclaimer</h2>
                    
                    <div class="space-y-4 text-sm text-gray-700">
                        <p>
                            This compensation plan is subject to change. All figures presented are examples and not guaranteed. 
                            Actual earnings depend on individual effort, market conditions, and platform performance.
                        </p>
                        
                        <p>
                            MyGrowNet is a registered private limited company operating legally in Zambia. This is not an investment scheme, 
                            pyramid scheme, or Ponzi scheme. Members pay for subscriptions, products, and services, not pooled investments.
                        </p>

                        <p>
                            <strong>No Guaranteed Returns:</strong> Unlike investment schemes, MyGrowNet does not guarantee any specific returns. 
                            Your earnings are based on your activity, effort, and the performance of the platform.
                        </p>

                        <p>
                            <strong>Risk Disclosure:</strong> As with any business opportunity, there are risks involved. You may not earn back 
                            your initial investment. Past performance of other members does not guarantee future results.
                        </p>

                        <p>
                            <strong>Compliance:</strong> MyGrowNet operates in full compliance with Zambian laws and regulations. We maintain 
                            transparent operations and regular audits.
                        </p>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-300">
                        <h3 class="font-bold text-gray-900 mb-3">Terms & Conditions</h3>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li>• Members must be 18 years or older</li>
                            <li>• Registration fee is non-refundable after 30 days</li>
                            <li>• Monthly subscriptions must be maintained for active status</li>
                            <li>• Commissions are paid only to active members</li>
                            <li>• Platform reserves the right to modify terms with notice</li>
                            <li>• Fraudulent activity results in immediate termination</li>
                            <li>• All earnings are subject to applicable taxes</li>
                        </ul>
                    </div>
                </div>

                <!-- Call to Action -->
                <div class="bg-gradient-to-r from-blue-600 to-emerald-600 rounded-lg shadow-lg p-6 md:p-8 text-white text-center">
                    <h2 class="text-2xl md:text-3xl font-bold mb-4">Ready to Start Building Your Network?</h2>
                    <p class="text-lg mb-6 opacity-90">
                        Share your referral link and start earning commissions today!
                    </p>
                    <a :href="route('my-team.index')" 
                       class="inline-block bg-white text-blue-600 font-bold py-3 px-8 rounded-lg hover:bg-gray-100 transition-colors">
                        View My Team
                    </a>
                </div>

                <!-- Footer -->
                <div class="text-center text-gray-500 text-sm mt-8 pt-8 border-t border-gray-200">
                    <p>© 2025 MyGrowNet. All rights reserved.</p>
                    <p class="mt-2">Version 1.0 | October 2025</p>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>


<style scoped>
.section-nav {
    position: sticky;
    top: 80px;
    max-height: calc(100vh - 100px);
    overflow-y: auto;
}

@media (max-width: 1024px) {
    .section-nav {
        position: relative;
        top: 0;
        max-height: none;
    }
}
</style>
