<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { 
    StarIcon,
    ArrowUpIcon,
    CheckIcon,
    CurrencyDollarIcon,
    ChartBarIcon,
    UserGroupIcon
} from '@heroicons/vue/24/outline';
import { formatCurrency } from '@/utils/formatting';

interface TierBenefits {
    profit_rate: number;
    referral_levels: number;
    referral_rates: {
        level_1: number;
        level_2: number;
        level_3: number;
    };
    reinvestment_bonus: number;
    withdrawal_penalty_reduction: number;
    matrix_spillover_priority: number;
    enhanced_profit_rate_year2: number;
}

interface Tier {
    id: number;
    name: string;
    minimum_investment: number;
    benefits: TierBenefits;
    is_current: boolean;
    can_upgrade: boolean;
}

interface UpgradeInfo {
    eligible: boolean;
    current_tier: any;
    next_tier: any;
    required_amount: number;
    current_amount: number;
    remaining_amount: number;
}

interface UpgradeBenefits {
    next_tier: string;
    additional_investment_required: number;
    profit_rate_improvement: number;
    referral_improvements: {
        level_1: number;
        level_2: number;
        level_3: number;
    };
    new_features: string[];
    annual_profit_increase_estimate: number;
}

interface PageProps {
    tiers: Tier[];
    currentTier: any;
    upgradeInfo: UpgradeInfo;
    tierProgress: number;
    upgradeBenefits: UpgradeBenefits | null;
}

const page = usePage<PageProps>();

const getTierColor = (tierName: string) => {
    const colors = {
        'Basic': 'border-gray-300 bg-gray-50',
        'Starter': 'border-blue-300 bg-blue-50',
        'Builder': 'border-green-300 bg-green-50',
        'Leader': 'border-purple-300 bg-purple-50',
        'Elite': 'border-yellow-300 bg-yellow-50'
    };
    return colors[tierName as keyof typeof colors] || 'border-gray-300 bg-gray-50';
};

const getTierTextColor = (tierName: string) => {
    const colors = {
        'Basic': 'text-gray-700',
        'Starter': 'text-blue-700',
        'Builder': 'text-green-700',
        'Leader': 'text-purple-700',
        'Elite': 'text-yellow-700'
    };
    return colors[tierName as keyof typeof colors] || 'text-gray-700';
};

const getTierBadgeColor = (tierName: string) => {
    const colors = {
        'Basic': 'bg-gray-100 text-gray-800',
        'Starter': 'bg-blue-100 text-blue-800',
        'Builder': 'bg-green-100 text-green-800',
        'Leader': 'bg-purple-100 text-purple-800',
        'Elite': 'bg-yellow-100 text-yellow-800'
    };
    return colors[tierName as keyof typeof colors] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <MemberLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Investment Tiers
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="space-y-8">
                    <!-- Current Tier Status -->
                    <div v-if="currentTier" class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Your Current Tier</h3>
                                <p class="text-gray-600">Track your progress and see upgrade benefits</p>
                            </div>
                            <div class="text-right">
                                <span :class="getTierBadgeColor(currentTier.name)" 
                                      class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium">
                                    <StarIcon class="h-4 w-4 mr-2" />
                                    {{ currentTier.name }}
                                </span>
                            </div>
                        </div>

                        <!-- Upgrade Progress -->
                        <div v-if="upgradeInfo.next_tier" class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Progress to {{ upgradeInfo.next_tier.name }}</span>
                                <span class="text-sm font-medium">{{ Math.round(tierProgress) }}%</span>
                            </div>
                            
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-primary-600 h-3 rounded-full transition-all duration-300" 
                                     :style="{ width: tierProgress + '%' }"></div>
                            </div>
                            
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">
                                    Current: {{ formatCurrency(upgradeInfo.current_amount) }}
                                </span>
                                <span class="text-gray-600">
                                    Required: {{ formatCurrency(upgradeInfo.required_amount) }}
                                </span>
                            </div>
                            
                            <div v-if="upgradeInfo.remaining_amount > 0" class="text-center p-4 bg-blue-50 rounded-lg">
                                <p class="text-sm text-blue-700">
                                    Invest {{ formatCurrency(upgradeInfo.remaining_amount) }} more to upgrade to {{ upgradeInfo.next_tier.name }}
                                </p>
                            </div>
                            
                            <div v-else class="text-center p-4 bg-green-50 rounded-lg">
                                <p class="text-sm text-green-700 font-medium">
                                    🎉 You're eligible to upgrade to {{ upgradeInfo.next_tier.name }}!
                                </p>
                            </div>
                        </div>

                        <!-- Upgrade Benefits Preview -->
                        <div v-if="upgradeBenefits" class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-3">Upgrade Benefits</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div class="flex items-center">
                                    <ArrowUpIcon class="h-4 w-4 text-green-500 mr-2" />
                                    <span>+{{ upgradeBenefits.profit_rate_improvement }}% profit rate</span>
                                </div>
                                <div class="flex items-center">
                                    <CurrencyDollarIcon class="h-4 w-4 text-green-500 mr-2" />
                                    <span>{{ formatCurrency(upgradeBenefits.annual_profit_increase_estimate) }} more annually</span>
                                </div>
                                <div v-for="feature in upgradeBenefits.new_features" :key="feature" class="flex items-center">
                                    <CheckIcon class="h-4 w-4 text-green-500 mr-2" />
                                    <span>{{ feature }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tier Comparison -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">All Investment Tiers</h3>
                            <Link :href="route('tiers.compare')" 
                                  class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                                Compare Tiers
                            </Link>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">
                            <div v-for="tier in tiers" :key="tier.id" 
                                 :class="[
                                     getTierColor(tier.name),
                                     tier.is_current ? 'ring-2 ring-primary-500' : '',
                                     'relative rounded-xl border-2 p-6 transition-all duration-200 hover:shadow-lg'
                                 ]">
                                
                                <!-- Current Tier Badge -->
                                <div v-if="tier.is_current" 
                                     class="absolute -top-3 left-1/2 transform -translate-x-1/2">
                                    <span class="bg-primary-600 text-white px-3 py-1 rounded-full text-xs font-medium">
                                        Current
                                    </span>
                                </div>

                                <div class="text-center">
                                    <h4 :class="getTierTextColor(tier.name)" 
                                        class="text-xl font-bold mb-2">
                                        {{ tier.name }}
                                    </h4>
                                    
                                    <div class="text-2xl font-bold text-gray-900 mb-4">
                                        {{ formatCurrency(tier.minimum_investment) }}
                                    </div>
                                    
                                    <div class="space-y-3 text-sm">
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-600">Profit Rate</span>
                                            <span class="font-medium">{{ tier.benefits.profit_rate }}%</span>
                                        </div>
                                        
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-600">Referral Levels</span>
                                            <span class="font-medium">{{ tier.benefits.referral_levels }}</span>
                                        </div>
                                        
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-600">Level 1 Commission</span>
                                            <span class="font-medium">{{ tier.benefits.referral_rates.level_1 }}%</span>
                                        </div>
                                        
                                        <div v-if="tier.benefits.referral_rates.level_2 > 0" 
                                             class="flex items-center justify-between">
                                            <span class="text-gray-600">Level 2 Commission</span>
                                            <span class="font-medium">{{ tier.benefits.referral_rates.level_2 }}%</span>
                                        </div>
                                        
                                        <div v-if="tier.benefits.referral_rates.level_3 > 0" 
                                             class="flex items-center justify-between">
                                            <span class="text-gray-600">Level 3 Commission</span>
                                            <span class="font-medium">{{ tier.benefits.referral_rates.level_3 }}%</span>
                                        </div>
                                        
                                        <div v-if="tier.benefits.reinvestment_bonus > 0" 
                                             class="flex items-center justify-between">
                                            <span class="text-gray-600">Reinvestment Bonus</span>
                                            <span class="font-medium">{{ tier.benefits.reinvestment_bonus }}%</span>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-6">
                                        <Link v-if="tier.can_upgrade && !tier.is_current" 
                                              :href="route('tiers.show', tier.id)"
                                              :class="getTierTextColor(tier.name)"
                                              class="block w-full py-2 px-4 border border-current rounded-lg font-medium hover:bg-current hover:text-white transition-colors duration-200">
                                            View Details
                                        </Link>
                                        
                                        <button v-else-if="tier.is_current" 
                                                disabled
                                                class="block w-full py-2 px-4 bg-gray-100 text-gray-500 rounded-lg font-medium cursor-not-allowed">
                                            Current Tier
                                        </button>
                                        
                                        <button v-else 
                                                disabled
                                                class="block w-full py-2 px-4 bg-gray-100 text-gray-400 rounded-lg font-medium cursor-not-allowed">
                                            Not Available
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tier Benefits Explanation -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">How Tiers Work</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="text-center">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <ChartBarIcon class="h-6 w-6 text-blue-600" />
                                </div>
                                <h4 class="font-medium text-gray-900 mb-2">Higher Profit Rates</h4>
                                <p class="text-sm text-gray-600">
                                    Higher tiers earn better annual profit rates on your investments
                                </p>
                            </div>
                            
                            <div class="text-center">
                                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <UserGroupIcon class="h-6 w-6 text-green-600" />
                                </div>
                                <h4 class="font-medium text-gray-900 mb-2">More Referral Levels</h4>
                                <p class="text-sm text-gray-600">
                                    Unlock deeper referral levels and earn from more generations
                                </p>
                            </div>
                            
                            <div class="text-center">
                                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <StarIcon class="h-6 w-6 text-purple-600" />
                                </div>
                                <h4 class="font-medium text-gray-900 mb-2">Exclusive Benefits</h4>
                                <p class="text-sm text-gray-600">
                                    Access reinvestment bonuses, reduced penalties, and priority placement
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>