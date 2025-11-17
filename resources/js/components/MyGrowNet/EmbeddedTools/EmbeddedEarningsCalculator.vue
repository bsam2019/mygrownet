<script setup lang="ts">
import { ref, computed } from 'vue';
import { 
    CalculatorIcon, 
    UsersIcon, 
    DollarSignIcon,
    TrendingUpIcon,
    AwardIcon,
    GiftIcon
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

const activePercentage = ref(50);
const subscriptionPrice = ref(500);
const starterKitPrice = ref(500);
const workshopPrice = ref(300);
const productPrice = ref(200);

// LGR inputs
const lgrQualified = ref(props.userTier === 'premium');
const quarterlyProfit = ref(50000);
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
        const wsCommission = activeMembers * workshopPrice.value * (wsRate / 100) * 0.5;
        const prodCommission = activeMembers * productPrice.value * (prodRate / 100) * 0.3;
        
        const levelTotal = subCommission + skCommission + wsCommission + prodCommission;
        total += levelTotal;
        
        breakdown.push({
            level,
            teamSize,
            activeMembers,
            levelTotal,
        });
    }

    return { total, breakdown };
});

const lgrEarnings = computed(() => {
    if (!lgrQualified.value) return 0;
    const distributionPool = quarterlyProfit.value * 0.6;
    const perMember = distributionPool / totalQualifiedMembers.value;
    return perMember / 3;
});

const communityRewards = computed(() => {
    if (totalContributions.value === 0) return 0;
    const yourShare = (yourContribution.value / totalContributions.value) * communityProjectProfit.value;
    return yourShare;
});

const performanceBonus = computed(() => {
    if (achievedAmount.value < monthlyTarget.value) return 0;
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
    <div class="space-y-4">
        <!-- Earning Type Selector -->
        <div class="grid grid-cols-2 gap-2">
            <button
                @click="activeEarningType = 'all'"
                :class="activeEarningType === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'"
                class="px-3 py-2 rounded-lg font-semibold text-xs transition-colors"
            >
                All Earnings
            </button>
            <button
                @click="activeEarningType = 'referral'"
                :class="activeEarningType === 'referral' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700'"
                class="px-3 py-2 rounded-lg font-semibold text-xs transition-colors"
            >
                Referral
            </button>
            <button
                @click="activeEarningType = 'lgr'"
                :class="activeEarningType === 'lgr' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700'"
                class="px-3 py-2 rounded-lg font-semibold text-xs transition-colors"
            >
                LGR
            </button>
            <button
                @click="activeEarningType = 'performance'"
                :class="activeEarningType === 'performance' ? 'bg-pink-600 text-white' : 'bg-gray-100 text-gray-700'"
                class="px-3 py-2 rounded-lg font-semibold text-xs transition-colors"
            >
                Performance
            </button>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-4 text-white">
                <DollarSignIcon class="h-6 w-6 mb-1 opacity-80" />
                <p class="text-xs opacity-90">Monthly</p>
                <p class="text-xl font-bold mt-1">{{ formatCurrency(totalEarnings) }}</p>
            </div>

            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg p-4 text-white">
                <DollarSignIcon class="h-6 w-6 mb-1 opacity-80" />
                <p class="text-xs opacity-90">Yearly</p>
                <p class="text-xl font-bold mt-1">{{ formatCurrency(totalEarnings * 12) }}</p>
            </div>
        </div>

        <!-- Input Section -->
        <div class="bg-white rounded-lg p-4 space-y-3">
            <div v-if="activeEarningType === 'all' || activeEarningType === 'referral'">
                <h4 class="text-sm font-semibold text-gray-900 mb-2">Referral Settings</h4>
                <div class="space-y-2">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Active Members (%)</label>
                        <input
                            v-model.number="activePercentage"
                            type="range"
                            min="0"
                            max="100"
                            class="w-full"
                        />
                        <div class="text-center text-xs text-gray-600">{{ activePercentage }}%</div>
                    </div>
                    <div v-for="level in 3" :key="level">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Level {{ level }}</label>
                        <input
                            v-model.number="teamSizes[`level_${level}` as keyof typeof teamSizes]"
                            type="number"
                            min="0"
                            class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                        />
                    </div>
                </div>
            </div>

            <div v-if="activeEarningType === 'all' || activeEarningType === 'lgr'">
                <h4 class="text-sm font-semibold text-gray-900 mb-2">LGR Settings</h4>
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <input
                            v-model="lgrQualified"
                            type="checkbox"
                            class="rounded border-gray-300 text-purple-600"
                        />
                        <label class="text-xs font-medium text-gray-700">I'm LGR Qualified</label>
                    </div>
                    <div v-if="lgrQualified">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Quarterly Profit (K)</label>
                        <input
                            v-model.number="quarterlyProfit"
                            type="number"
                            min="0"
                            step="1000"
                            class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-purple-500"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Earnings Breakdown -->
        <div class="bg-white rounded-lg p-4">
            <h4 class="text-sm font-semibold text-gray-900 mb-3">Earnings Breakdown</h4>
            <div class="space-y-2">
                <div v-if="activeEarningType === 'all' || activeEarningType === 'referral'" class="flex justify-between items-center py-2 border-b">
                    <span class="text-xs text-gray-600">Referral Commissions</span>
                    <span class="text-sm font-bold text-green-600">{{ formatCurrency(referralEarnings.total) }}</span>
                </div>
                <div v-if="activeEarningType === 'all' || activeEarningType === 'lgr'" class="flex justify-between items-center py-2 border-b">
                    <span class="text-xs text-gray-600">LGR Profit Sharing</span>
                    <span class="text-sm font-bold text-purple-600">{{ formatCurrency(lgrEarnings) }}</span>
                </div>
                <div v-if="activeEarningType === 'all' || activeEarningType === 'performance'" class="flex justify-between items-center py-2 border-b">
                    <span class="text-xs text-gray-600">Performance Bonus</span>
                    <span class="text-sm font-bold text-pink-600">{{ formatCurrency(performanceBonus) }}</span>
                </div>
            </div>
        </div>

        <!-- Disclaimer -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
            <p class="text-xs text-yellow-800">
                <strong>Note:</strong> These are projections. Actual earnings may vary.
            </p>
        </div>
    </div>
</template>
