<template>
    <MemberLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Compare Investment Tiers
                    </h2>
                    <p class="text-gray-600 mt-1">Compare benefits and features across different tiers</p>
                </div>
                <Link 
                    :href="route('tiers.index')"
                    class="text-sm text-primary-600 hover:text-primary-700 font-medium flex items-center"
                >
                    <ArrowLeftIcon class="h-4 w-4 mr-1" />
                    Back to Tiers
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="space-y-8">
                    <!-- Tier Selection -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Select Tiers to Compare</h3>
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                            <div v-for="tier in allTiers" :key="tier.id" class="relative">
                                <input
                                    :id="`tier-${tier.id}`"
                                    v-model="selectedTierIds"
                                    :value="tier.id"
                                    type="checkbox"
                                    class="sr-only"
                                    :disabled="!selectedTierIds.includes(tier.id) && selectedTierIds.length >= 3"
                                />
                                <label
                                    :for="`tier-${tier.id}`"
                                    :class="[
                                        selectedTierIds.includes(tier.id) 
                                            ? 'border-primary-500 bg-primary-50' 
                                            : 'border-gray-200 bg-white hover:border-gray-300',
                                        'block cursor-pointer rounded-lg border-2 p-4 text-center transition-all duration-200'
                                    ]"
                                >
                                    <div class="text-sm font-medium text-gray-900">{{ tier.name }}</div>
                                    <div class="text-xs text-gray-500 mt-1">{{ formatCurrency(tier.minimum_investment) }}</div>
                                </label>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Select up to 3 tiers to compare</p>
                    </div>

                    <!-- Comparison Table -->
                    <div v-if="selectedTiers.length >= 2" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Features
                                        </th>
                                        <th v-for="tier in selectedTiers" :key="tier.id" 
                                            class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div>
                                                <div class="font-semibold text-gray-900">{{ tier.name }}</div>
                                                <div class="text-xs text-gray-500 mt-1">{{ formatCurrency(tier.minimum_investment) }}</div>
                                                <div v-if="tier.is_current" class="mt-2">
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                                                        Current
                                                    </span>
                                                </div>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <!-- Minimum Investment -->
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            Minimum Investment
                                        </td>
                                        <td v-for="tier in selectedTiers" :key="`min-${tier.id}`" 
                                            class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
                                            {{ formatCurrency(tier.minimum_investment) }}
                                        </td>
                                    </tr>

                                    <!-- Annual Profit Rate -->
                                    <tr class="bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            Annual Profit Rate
                                        </td>
                                        <td v-for="tier in selectedTiers" :key="`profit-${tier.id}`" 
                                            class="px-6 py-4 whitespace-nowrap text-sm text-center font-semibold text-green-600">
                                            {{ tier.benefits.profit_rate }}%
                                        </td>
                                    </tr>

                                    <!-- Referral Levels -->
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            Referral Levels
                                        </td>
                                        <td v-for="tier in selectedTiers" :key="`levels-${tier.id}`" 
                                            class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
                                            {{ tier.benefits.referral_levels }}
                                        </td>
                                    </tr>

                                    <!-- Level 1 Commission -->
                                    <tr class="bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            Level 1 Commission
                                        </td>
                                        <td v-for="tier in selectedTiers" :key="`l1-${tier.id}`" 
                                            class="px-6 py-4 whitespace-nowrap text-sm text-center text-blue-600 font-medium">
                                            {{ tier.benefits.referral_rates.level_1 }}%
                                        </td>
                                    </tr>

                                    <!-- Level 2 Commission -->
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            Level 2 Commission
                                        </td>
                                        <td v-for="tier in selectedTiers" :key="`l2-${tier.id}`" 
                                            class="px-6 py-4 whitespace-nowrap text-sm text-center text-blue-600 font-medium">
                                            {{ tier.benefits.referral_rates.level_2 > 0 ? tier.benefits.referral_rates.level_2 + '%' : 'N/A' }}
                                        </td>
                                    </tr>

                                    <!-- Level 3 Commission -->
                                    <tr class="bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            Level 3 Commission
                                        </td>
                                        <td v-for="tier in selectedTiers" :key="`l3-${tier.id}`" 
                                            class="px-6 py-4 whitespace-nowrap text-sm text-center text-blue-600 font-medium">
                                            {{ tier.benefits.referral_rates.level_3 > 0 ? tier.benefits.referral_rates.level_3 + '%' : 'N/A' }}
                                        </td>
                                    </tr>

                                    <!-- Reinvestment Bonus -->
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            Reinvestment Bonus
                                        </td>
                                        <td v-for="tier in selectedTiers" :key="`reinvest-${tier.id}`" 
                                            class="px-6 py-4 whitespace-nowrap text-sm text-center text-purple-600 font-medium">
                                            {{ tier.benefits.reinvestment_bonus > 0 ? tier.benefits.reinvestment_bonus + '%' : 'N/A' }}
                                        </td>
                                    </tr>

                                    <!-- Withdrawal Penalty Reduction -->
                                    <tr class="bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            Withdrawal Penalty Reduction
                                        </td>
                                        <td v-for="tier in selectedTiers" :key="`penalty-${tier.id}`" 
                                            class="px-6 py-4 whitespace-nowrap text-sm text-center text-green-600 font-medium">
                                            {{ tier.benefits.withdrawal_penalty_reduction > 0 ? tier.benefits.withdrawal_penalty_reduction + '%' : 'Standard' }}
                                        </td>
                                    </tr>

                                    <!-- Matrix Spillover Priority -->
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            Matrix Spillover Priority
                                        </td>
                                        <td v-for="tier in selectedTiers" :key="`matrix-${tier.id}`" 
                                            class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
                                            {{ getMatrixPriorityText(tier.benefits.matrix_spillover_priority) }}
                                        </td>
                                    </tr>

                                    <!-- Enhanced Year 2 Rate -->
                                    <tr class="bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            Enhanced Year 2+ Rate
                                        </td>
                                        <td v-for="tier in selectedTiers" :key="`year2-${tier.id}`" 
                                            class="px-6 py-4 whitespace-nowrap text-sm text-center text-green-600 font-medium">
                                            {{ tier.benefits.enhanced_profit_rate_year2 > 0 ? tier.benefits.enhanced_profit_rate_year2 + '%' : 'Same' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Investment Calculator -->
                    <div v-if="selectedTiers.length >= 2" class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Investment Calculator</h3>
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Investment Amount (K)
                            </label>
                            <input
                                v-model.number="calculatorAmount"
                                type="number"
                                min="500"
                                step="100"
                                class="block w-full max-w-xs border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500"
                                placeholder="Enter amount"
                            />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div v-for="tier in selectedTiers" :key="`calc-${tier.id}`" 
                                 class="bg-gray-50 rounded-lg p-4">
                                <h4 class="font-medium text-gray-900 mb-3">{{ tier.name }}</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Annual Profit:</span>
                                        <span class="font-semibold text-green-600">
                                            {{ formatCurrency(calculateAnnualProfit(tier, calculatorAmount)) }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Level 1 Commission (per referral):</span>
                                        <span class="font-semibold text-blue-600">
                                            {{ formatCurrency(calculateCommission(tier, calculatorAmount, 1)) }}
                                        </span>
                                    </div>
                                    <div v-if="tier.benefits.referral_rates.level_2 > 0" class="flex justify-between">
                                        <span class="text-gray-600">Level 2 Commission (per referral):</span>
                                        <span class="font-semibold text-blue-600">
                                            {{ formatCurrency(calculateCommission(tier, calculatorAmount, 2)) }}
                                        </span>
                                    </div>
                                    <div v-if="tier.benefits.referral_rates.level_3 > 0" class="flex justify-between">
                                        <span class="text-gray-600">Level 3 Commission (per referral):</span>
                                        <span class="font-semibold text-blue-600">
                                            {{ formatCurrency(calculateCommission(tier, calculatorAmount, 3)) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div v-if="selectedTiers.length >= 2" class="flex justify-center space-x-4">
                        <Link 
                            :href="route('opportunities')"
                            class="px-6 py-3 bg-primary-600 border border-transparent rounded-lg font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200"
                        >
                            Start Investing
                        </Link>
                        <Link 
                            :href="route('tiers.index')"
                            class="px-6 py-3 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200"
                        >
                            View All Tiers
                        </Link>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-12">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                            <ChartBarIcon class="h-8 w-8 text-gray-400" />
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Select Tiers to Compare</h3>
                        <p class="text-gray-500">Choose at least 2 tiers from the selection above to see a detailed comparison.</p>
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ArrowLeftIcon, ChartBarIcon } from '@heroicons/vue/24/outline';

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

interface Props {
    tiers: Tier[];
    comparisons: any[];
    currentTier: any;
}

const props = defineProps<Props>();

// Get all available tiers for selection
const allTiers = computed(() => {
    // You might want to fetch all tiers here or pass them as props
    return props.tiers;
});

const selectedTierIds = ref<number[]>(props.tiers.map(t => t.id));
const calculatorAmount = ref(1000);

const selectedTiers = computed(() => {
    return allTiers.value.filter(tier => selectedTierIds.value.includes(tier.id));
});

// Watch for changes in selected tiers and update URL
watch(selectedTierIds, (newIds) => {
    if (newIds.length >= 2) {
        router.get(route('tiers.compare'), { tiers: newIds }, { 
            preserveState: true,
            preserveScroll: true,
            only: ['tiers', 'comparisons']
        });
    }
}, { deep: true });

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat().format(amount);
};

const getMatrixPriorityText = (priority: number): string => {
    switch (priority) {
        case 1: return 'Highest';
        case 2: return 'High';
        case 3: return 'Medium';
        case 4: return 'Low';
        default: return 'Standard';
    }
};

const calculateAnnualProfit = (tier: Tier, amount: number): number => {
    return (amount * tier.benefits.profit_rate) / 100;
};

const calculateCommission = (tier: Tier, amount: number, level: number): number => {
    let rate = 0;
    switch (level) {
        case 1: rate = tier.benefits.referral_rates.level_1; break;
        case 2: rate = tier.benefits.referral_rates.level_2; break;
        case 3: rate = tier.benefits.referral_rates.level_3; break;
    }
    return (amount * rate) / 100;
};
</script>