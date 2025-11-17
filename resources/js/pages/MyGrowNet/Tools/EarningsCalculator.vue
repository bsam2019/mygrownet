<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { 
    CalculatorIcon, 
    UsersIcon, 
    DollarSignIcon,
    TrendingUpIcon,
    AwardIcon,
    GiftIcon,
    BuildingIcon
} from 'lucide-vue-next';

interface Props {
    userTier: 'basic' | 'premium' | null;
    currentNetwork?: {
        level_1: number;
        level_2: number;
        level_3: number;
        level_4: number;
        level_5: number;
        level_6: number;
        level_7: number;
    };
}

const props = defineProps<Props>();

// Active earning type
const activeEarningType = ref<'all' | 'referral' | 'lgr' | 'community' | 'performance'>('all');

// Input values
const teamSizes = ref({
    level_1: props.currentNetwork?.level_1 || 3,
    level_2: props.currentNetwork?.level_2 || 9,
    level_3: props.currentNetwork?.level_3 || 27,
    level_4: props.currentNetwork?.level_4 || 81,
    level_5: props.currentNetwork?.level_5 || 243,
    level_6: props.currentNetwork?.level_6 || 729,
    level_7: props.currentNetwork?.level_7 || 2187,
});

const activePercentage = ref(50); // % of team that's active
const subscriptionPrice = ref(500);
const starterKitPrice = ref(500);
const workshopPrice = ref(300);
const productPrice = ref(200);

// LGR inputs
const lgrQualified = ref(props.userTier === 'premium');
const quarterlyProfit = ref(50000); // Company quarterly profit
const totalQualifiedMembers = ref(100);

// Community rewards inputs
const communityProjectProfit = ref(20000);
const yourContribution = ref(1000);
const totalContributions = ref(50000);

// Performance bonus inputs
const monthlyTarget = ref(5000);
const achievedAmount = ref(0);

// Commission rates (7 levels)
const commissionRates = {
    subscription: { 1: 10, 2: 5, 3: 3, 4: 2, 5: 1, 6: 1, 7: 1 },
    starter_kit: { 1: 10, 2: 5, 3: 3, 4: 2, 5: 1, 6: 1, 7: 1 },
    workshop: { 1: 10, 2: 5, 3: 3, 4: 2, 5: 1, 6: 1, 7: 1 },
    product: { 1: 10, 2: 5, 3: 3, 4: 2, 5: 1, 6: 1, 7: 1 },
};

// Calculations
const referralEarnings = computed(() => {
    let total = 0;
    const breakdown: any[] = [];

    for (let level = 1; level <= 7; level++) {
        const levelKey = `level_${level}` as keyof typeof teamSizes.value;
        const teamSize = teamSizes.value[levelKey];
        const activeMembers = Math.floor(teamSize * (activePercentage.value / 100));
        
        const subRate = commissionRates.subscription[level as keyof typeof commissionRates.subscription] || 0;
        const skRate = commissionRates.starter_kit[level as keyof typeof commissionRates.starter_kit] || 0;
        const wsRate = commissionRates.workshop[level as keyof typeof commissionRates.workshop] || 0;
        const prodRate = commissionRates.product[level as keyof typeof commissionRates.product] || 0;
        
        const subCommission = activeMembers * subscriptionPrice.value * (subRate / 100);
        const skCommission = activeMembers * starterKitPrice.value * (skRate / 100);
        const wsCommission = activeMembers * workshopPrice.value * (wsRate / 100) * 0.5; // Assume 50% attend workshops
        const prodCommission = activeMembers * productPrice.value * (prodRate / 100) * 0.3; // Assume 30% buy products
        
        const levelTotal = subCommission + skCommission + wsCommission + prodCommission;
        total += levelTotal;
        
        breakdown.push({
            level,
            teamSize,
            activeMembers,
            subCommission,
            skCommission,
            wsCommission,
            prodCommission,
            levelTotal,
        });
    }

    return { total, breakdown };
});

const lgrEarnings = computed(() => {
    if (!lgrQualified.value) return 0;
    
    // LGR: 60% of quarterly profit distributed to qualified members
    const distributionPool = quarterlyProfit.value * 0.6;
    const perMember = distributionPool / totalQualifiedMembers.value;
    
    // Monthly equivalent
    return perMember / 3;
});

const communityRewards = computed(() => {
    if (totalContributions.value === 0) return 0;
    
    // Your share based on contribution percentage
    const yourShare = (yourContribution.value / totalContributions.value) * communityProjectProfit.value;
    
    return yourShare;
});

const performanceBonus = computed(() => {
    if (achievedAmount.value < monthlyTarget.value) return 0;
    
    // 10% bonus for hitting target
    const bonus = achievedAmount.value * 0.1;
    
    return bonus;
});

const totalEarnings = computed(() => {
    let total = 0;
    
    if (activeEarningType.value === 'all' || activeEarningType.value === 'referral') {
        total += referralEarnings.value.total;
    }
    if (activeEarningType.value === 'all' || activeEarningType.value === 'lgr') {
        total += lgrEarnings.value;
    }
    if (activeEarningType.value === 'all' || activeEarningType.value === 'community') {
        total += communityRewards.value;
    }
    if (activeEarningType.value === 'all' || activeEarningType.value === 'performance') {
        total += performanceBonus.value;
    }
    
    return total;
});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(amount);
};
</script>

<template>
    <Head title="Earnings Calculator" />

    <MemberLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <CalculatorIcon class="h-8 w-8 text-blue-600" />
                        Earnings Calculator
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Calculate your potential earnings from all income streams
                    </p>
                </div>

                <!-- Earning Type Selector -->
                <div class="mb-6 bg-white rounded-lg shadow p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Select Earning Type</h3>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-2">
                        <button
                            @click="activeEarningType = 'all'"
                            :class="activeEarningType === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'"
                            class="px-4 py-2 rounded-lg font-semibold text-sm transition-colors hover:shadow-md"
                        >
                            All Earnings
                        </button>
                        <button
                            @click="activeEarningType = 'referral'"
                            :class="activeEarningType === 'referral' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700'"
                            class="px-4 py-2 rounded-lg font-semibold text-sm transition-colors hover:shadow-md"
                        >
                            Referral Bonus
                        </button>
                        <button
                            @click="activeEarningType = 'lgr'"
                            :class="activeEarningType === 'lgr' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700'"
                            class="px-4 py-2 rounded-lg font-semibold text-sm transition-colors hover:shadow-md"
                        >
                            LGR
                        </button>
                        <button
                            @click="activeEarningType = 'community'"
                            :class="activeEarningType === 'community' ? 'bg-orange-600 text-white' : 'bg-gray-100 text-gray-700'"
                            class="px-4 py-2 rounded-lg font-semibold text-sm transition-colors hover:shadow-md"
                        >
                            Community
                        </button>
                        <button
                            @click="activeEarningType = 'performance'"
                            :class="activeEarningType === 'performance' ? 'bg-pink-600 text-white' : 'bg-gray-100 text-gray-700'"
                            class="px-4 py-2 rounded-lg font-semibold text-sm transition-colors hover:shadow-md"
                        >
                            Performance
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Input Panel -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Referral Inputs -->
                        <div v-if="activeEarningType === 'all' || activeEarningType === 'referral'" class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <UsersIcon class="h-5 w-5 text-green-600" />
                                Referral Settings
                            </h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Active Members (%)
                                    </label>
                                    <input
                                        v-model.number="activePercentage"
                                        type="range"
                                        min="0"
                                        max="100"
                                        class="w-full"
                                    />
                                    <div class="text-center text-sm text-gray-600 mt-1">{{ activePercentage }}%</div>
                                </div>

                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Team Size by Level</label>
                                    <div v-for="level in 7" :key="level" class="flex items-center gap-2">
                                        <span class="text-xs text-gray-600 w-16">Level {{ level }}</span>
                                        <input
                                            v-model.number="teamSizes[`level_${level}` as keyof typeof teamSizes]"
                                            type="number"
                                            min="0"
                                            class="flex-1 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- LGR Inputs -->
                        <div v-if="activeEarningType === 'all' || activeEarningType === 'lgr'" class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <AwardIcon class="h-5 w-5 text-purple-600" />
                                LGR Settings
                            </h3>
                            
                            <div class="space-y-4">
                                <div class="flex items-center gap-2">
                                    <input
                                        v-model="lgrQualified"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                                    />
                                    <label class="text-sm font-medium text-gray-700">
                                        I'm LGR Qualified (Premium Tier)
                                    </label>
                                </div>

                                <div v-if="lgrQualified">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Quarterly Company Profit (K)
                                    </label>
                                    <input
                                        v-model.number="quarterlyProfit"
                                        type="number"
                                        min="0"
                                        step="1000"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    />
                                </div>

                                <div v-if="lgrQualified">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Total Qualified Members
                                    </label>
                                    <input
                                        v-model.number="totalQualifiedMembers"
                                        type="number"
                                        min="1"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Community Rewards Inputs -->
                        <div v-if="activeEarningType === 'all' || activeEarningType === 'community'" class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <GiftIcon class="h-5 w-5 text-orange-600" />
                                Community Rewards
                            </h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Project Profit (K)
                                    </label>
                                    <input
                                        v-model.number="communityProjectProfit"
                                        type="number"
                                        min="0"
                                        step="1000"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Your Contribution (K)
                                    </label>
                                    <input
                                        v-model.number="yourContribution"
                                        type="number"
                                        min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Total Contributions (K)
                                    </label>
                                    <input
                                        v-model.number="totalContributions"
                                        type="number"
                                        min="1"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Performance Bonus Inputs -->
                        <div v-if="activeEarningType === 'all' || activeEarningType === 'performance'" class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <TrendingUpIcon class="h-5 w-5 text-pink-600" />
                                Performance Bonus
                            </h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Monthly Target (K)
                                    </label>
                                    <input
                                        v-model.number="monthlyTarget"
                                        type="number"
                                        min="0"
                                        step="100"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Achieved Amount (K)
                                    </label>
                                    <input
                                        v-model.number="achievedAmount"
                                        type="number"
                                        min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                    />
                                </div>

                                <div v-if="achievedAmount >= monthlyTarget" class="bg-green-50 border border-green-200 rounded-lg p-3">
                                    <p class="text-sm text-green-800 font-semibold">🎉 Target Achieved! 10% Bonus Unlocked</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Results Panel -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Summary Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow p-6 text-white">
                                <DollarSignIcon class="h-8 w-8 mb-2 opacity-80" />
                                <p class="text-sm opacity-90">Monthly Projection</p>
                                <p class="text-3xl font-bold mt-1">{{ formatCurrency(totalEarnings) }}</p>
                            </div>

                            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow p-6 text-white">
                                <DollarSignIcon class="h-8 w-8 mb-2 opacity-80" />
                                <p class="text-sm opacity-90">Yearly Projection</p>
                                <p class="text-3xl font-bold mt-1">{{ formatCurrency(totalEarnings * 12) }}</p>
                            </div>
                        </div>

                        <!-- Earnings Breakdown -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Earnings Breakdown</h3>
                            
                            <div class="space-y-4">
                                <!-- Referral Earnings -->
                                <div v-if="activeEarningType === 'all' || activeEarningType === 'referral'" class="border-l-4 border-green-500 pl-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-semibold text-gray-900">Referral Commissions</h4>
                                        <span class="text-lg font-bold text-green-600">{{ formatCurrency(referralEarnings.total) }}</span>
                                    </div>
                                    <p class="text-sm text-gray-600">From 7-level network commissions</p>
                                </div>

                                <!-- LGR Earnings -->
                                <div v-if="activeEarningType === 'all' || activeEarningType === 'lgr'" class="border-l-4 border-purple-500 pl-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-semibold text-gray-900">LGR Profit Sharing</h4>
                                        <span class="text-lg font-bold text-purple-600">{{ formatCurrency(lgrEarnings) }}</span>
                                    </div>
                                    <p class="text-sm text-gray-600">
                                        {{ lgrQualified ? 'Monthly share from quarterly profits' : 'Upgrade to Premium to qualify' }}
                                    </p>
                                </div>

                                <!-- Community Rewards -->
                                <div v-if="activeEarningType === 'all' || activeEarningType === 'community'" class="border-l-4 border-orange-500 pl-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-semibold text-gray-900">Community Rewards</h4>
                                        <span class="text-lg font-bold text-orange-600">{{ formatCurrency(communityRewards) }}</span>
                                    </div>
                                    <p class="text-sm text-gray-600">Your share: {{ ((yourContribution / totalContributions) * 100).toFixed(1) }}%</p>
                                </div>

                                <!-- Performance Bonus -->
                                <div v-if="activeEarningType === 'all' || activeEarningType === 'performance'" class="border-l-4 border-pink-500 pl-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-semibold text-gray-900">Performance Bonus</h4>
                                        <span class="text-lg font-bold text-pink-600">{{ formatCurrency(performanceBonus) }}</span>
                                    </div>
                                    <p class="text-sm text-gray-600">
                                        {{ achievedAmount >= monthlyTarget ? '10% bonus for hitting target' : 'Hit your target to unlock bonus' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Detailed Referral Breakdown -->
                        <div v-if="activeEarningType === 'all' || activeEarningType === 'referral'" class="bg-white rounded-lg shadow overflow-hidden">
                            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Referral Commission Details</h3>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Team</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Active</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="result in referralEarnings.breakdown" :key="result.level">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                Level {{ result.level }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                {{ result.teamSize }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                {{ result.activeMembers }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-right text-green-600">
                                                {{ formatCurrency(result.levelTotal) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Disclaimer -->
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <p class="text-sm text-yellow-800">
                                <strong>Note:</strong> These are projections based on your inputs. Actual earnings may vary based on team activity, 
                                market conditions, and company performance. Use this calculator for planning purposes only.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
