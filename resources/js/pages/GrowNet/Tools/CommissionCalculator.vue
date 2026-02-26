<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { CalculatorIcon, UsersIcon, DollarSignIcon } from 'lucide-vue-next';

interface CommissionRates {
    subscription: Record<string, number>;
    starter_kit: Record<string, number>;
}

interface NetworkStats {
    level_1: number;
    level_2: number;
    level_3: number;
    level_4: number;
    level_5: number;
    level_6: number;
    level_7: number;
}

interface Props {
    commissionRates: CommissionRates;
    networkStats: NetworkStats;
    userTier: 'basic' | 'premium' | null;
}

const props = defineProps<Props>();

// Input values
const teamSizes = ref({
    level_1: props.networkStats.level_1 || 3,
    level_2: props.networkStats.level_2 || 9,
    level_3: props.networkStats.level_3 || 27,
    level_4: props.networkStats.level_4 || 81,
    level_5: props.networkStats.level_5 || 243,
    level_6: props.networkStats.level_6 || 729,
    level_7: props.networkStats.level_7 || 2187,
});

const subscriptionPrice = ref(500);
const starterKitPrice = ref(500);
const activePercentage = ref(50); // % of team that's active

// Calculations
const calculations = computed(() => {
    const results: any[] = [];
    let totalCommission = 0;

    for (let level = 1; level <= 7; level++) {
        const levelKey = `level_${level}`;
        const teamSize = teamSizes.value[levelKey as keyof typeof teamSizes.value];
        const activeMembers = Math.floor(teamSize * (activePercentage.value / 100));
        
        // Subscription commissions
        const subRate = props.commissionRates.subscription[levelKey] || 0;
        const subCommission = activeMembers * subscriptionPrice.value * (subRate / 100);
        
        // Starter kit commissions
        const skRate = props.commissionRates.starter_kit[levelKey] || 0;
        const skCommission = activeMembers * starterKitPrice.value * (skRate / 100);
        
        const levelTotal = subCommission + skCommission;
        totalCommission += levelTotal;
        
        results.push({
            level,
            teamSize,
            activeMembers,
            subRate,
            skRate,
            subCommission,
            skCommission,
            levelTotal,
        });
    }

    return {
        levels: results,
        totalCommission,
        monthlyProjection: totalCommission,
        yearlyProjection: totalCommission * 12,
    };
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
    <Head title="Commission Calculator" />

    <MemberLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <CalculatorIcon class="h-8 w-8 text-blue-600" />
                        Commission Calculator
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Calculate your potential earnings based on team size and activity
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Input Panel -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Assumptions -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Assumptions</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Subscription Price (K)
                                    </label>
                                    <input
                                        v-model.number="subscriptionPrice"
                                        type="number"
                                        min="0"
                                        step="50"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Starter Kit Price (K)
                                    </label>
                                    <input
                                        v-model.number="starterKitPrice"
                                        type="number"
                                        min="0"
                                        step="50"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                </div>

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
                            </div>
                        </div>

                        <!-- Team Size Inputs -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Team Size by Level</h3>
                            
                            <div class="space-y-3">
                                <div v-for="level in 7" :key="level">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Level {{ level }}
                                    </label>
                                    <input
                                        v-model.number="teamSizes[`level_${level}` as keyof typeof teamSizes]"
                                        type="number"
                                        min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Results Panel -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Summary Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow p-6 text-white">
                                <DollarSignIcon class="h-8 w-8 mb-2 opacity-80" />
                                <p class="text-sm opacity-90">Monthly Projection</p>
                                <p class="text-3xl font-bold mt-1">{{ formatCurrency(calculations.monthlyProjection) }}</p>
                            </div>

                            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow p-6 text-white">
                                <DollarSignIcon class="h-8 w-8 mb-2 opacity-80" />
                                <p class="text-sm opacity-90">Yearly Projection</p>
                                <p class="text-3xl font-bold mt-1">{{ formatCurrency(calculations.yearlyProjection) }}</p>
                            </div>

                            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow p-6 text-white">
                                <UsersIcon class="h-8 w-8 mb-2 opacity-80" />
                                <p class="text-sm opacity-90">Total Team</p>
                                <p class="text-3xl font-bold mt-1">
                                    {{ Object.values(teamSizes).reduce((a, b) => a + b, 0) }}
                                </p>
                            </div>
                        </div>

                        <!-- Detailed Breakdown -->
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Commission Breakdown by Level</h3>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Team Size</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Active</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sub Rate</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SK Rate</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="result in calculations.levels" :key="result.level">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                Level {{ result.level }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                {{ result.teamSize }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                {{ result.activeMembers }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                {{ result.subRate }}%
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                {{ result.skRate }}%
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-right text-green-600">
                                                {{ formatCurrency(result.levelTotal) }}
                                            </td>
                                        </tr>
                                        <tr class="bg-gray-50 font-bold">
                                            <td colspan="5" class="px-6 py-4 text-sm text-gray-900">
                                                Total Monthly Commission
                                            </td>
                                            <td class="px-6 py-4 text-sm text-right text-blue-600">
                                                {{ formatCurrency(calculations.totalCommission) }}
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
                                subscription renewals, and starter kit purchases. This calculator is for planning purposes only.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
