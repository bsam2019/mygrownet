<template>
    <InvestorLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h1 class="text-2xl font-bold text-gray-900">Reports & Analytics</h1>
                <p class="text-gray-600 mt-1">Comprehensive analysis of your investment performance and earnings</p>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <ChartPieIcon class="h-6 w-6 text-blue-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Investments</p>
                            <p class="text-2xl font-bold text-gray-900">{{ investmentSummary.total_investments }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <TrendingUp class="h-6 w-6 text-green-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Current Value</p>
                            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(investmentSummary.current_value) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-emerald-100 rounded-lg">
                            <BanknoteIcon class="h-6 w-6 text-emerald-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Earnings</p>
                            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(earningsSummary.total_earnings) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <GroupIcon class="h-6 w-6 text-purple-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Active Referrals</p>
                            <p class="text-2xl font-bold text-gray-900">{{ referralSummary.active_referrals }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Available Reports -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">Available Reports</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Link :href="route('reports.investments')" 
                          class="block p-6 border border-gray-200 rounded-lg hover:border-blue-300 hover:shadow-md transition-all">
                        <div class="flex items-center mb-4">
                            <ChartBarIcon class="h-8 w-8 text-blue-600" />
                            <h3 class="text-lg font-semibold text-gray-900 ml-3">Investment Performance</h3>
                        </div>
                        <p class="text-gray-600 text-sm">
                            Detailed analysis of your investment performance, ROI trends, and tier progression
                        </p>
                    </Link>

                    <Link :href="route('reports.transactions')" 
                          class="block p-6 border border-gray-200 rounded-lg hover:border-blue-300 hover:shadow-md transition-all">
                        <div class="flex items-center mb-4">
                            <ArrowRightLeftIcon class="h-8 w-8 text-green-600" />
                            <h3 class="text-lg font-semibold text-gray-900 ml-3">Transaction History</h3>
                        </div>
                        <p class="text-gray-600 text-sm">
                            Complete transaction history including withdrawals, commissions, and profit distributions
                        </p>
                    </Link>

                    <Link :href="route('reports.referrals')" 
                          class="block p-6 border border-gray-200 rounded-lg hover:border-blue-300 hover:shadow-md transition-all">
                        <div class="flex items-center mb-4">
                            <GroupIcon class="h-8 w-8 text-purple-600" />
                            <h3 class="text-lg font-semibold text-gray-900 ml-3">Referral Analysis</h3>
                        </div>
                        <p class="text-gray-600 text-sm">
                            Referral network performance, matrix analysis, and commission breakdown
                        </p>
                    </Link>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Earnings Breakdown -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Earnings Breakdown</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Referral Commissions</span>
                            <span class="font-semibold">{{ formatCurrency(earningsSummary.referral_earnings) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Profit Shares</span>
                            <span class="font-semibold">{{ formatCurrency(earningsSummary.profit_earnings) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Matrix Commissions</span>
                            <span class="font-semibold">{{ formatCurrency(earningsSummary.matrix_earnings || 0) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Pending Earnings</span>
                            <span class="font-semibold text-amber-600">{{ formatCurrency(earningsSummary.pending_earnings) }}</span>
                        </div>
                        <hr class="my-2">
                        <div class="flex justify-between items-center text-lg font-bold">
                            <span>Total Earnings</span>
                            <span>{{ formatCurrency(earningsSummary.total_earnings) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Withdrawal Summary -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Withdrawal Summary</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Total Withdrawals</span>
                            <span class="font-semibold">{{ withdrawalSummary.total_withdrawals }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Approved</span>
                            <span class="font-semibold text-green-600">{{ withdrawalSummary.approved_withdrawals }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Pending</span>
                            <span class="font-semibold text-amber-600">{{ withdrawalSummary.pending_withdrawals }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Total Withdrawn</span>
                            <span class="font-semibold">{{ formatCurrency(withdrawalSummary.total_withdrawn) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Total Penalties</span>
                            <span class="font-semibold text-red-600">{{ formatCurrency(withdrawalSummary.total_penalties) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </InvestorLayout>
</template>

<script setup lang="ts">
import InvestorLayout from '@/layouts/InvestorLayout.vue';
import { Link } from '@inertiajs/vue3';
import { 
    ChartPie, 
    ChartBar, 
    TrendingUp, 
    Banknote, 
    Group, 
    ArrowRightLeftIcon 
} from 'lucide-vue-next';
import { formatCurrency } from '@/utils/formatting';

interface Props {
    investmentSummary: {
        total_investments: number;
        total_invested: number;
        current_value: number;
        average_roi: number;
        best_performing: any;
        tier_distribution: Record<string, number>;
    };
    earningsSummary: {
        total_earnings: number;
        referral_earnings: number;
        profit_earnings: number;
        matrix_earnings?: number;
        pending_earnings: number;
        monthly_average: number;
        growth_rate: number;
    };
    referralSummary: {
        total_referrals: number;
        active_referrals: number;
        total_commission: number;
        pending_commission: number;
        matrix_position: any;
        downline_counts: Record<string, number>;
    };
    withdrawalSummary: {
        total_withdrawals: number;
        approved_withdrawals: number;
        pending_withdrawals: number;
        total_withdrawn: number;
        total_penalties: number;
        average_withdrawal: number;
    };
    availableReports: Record<string, string>;
}

const props = defineProps<Props>();


</script>