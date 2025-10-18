<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import InvestorLayout from '@/layouts/InvestorLayout.vue';
import { 
    ChartBarIcon, 
    CurrencyDollarIcon, 
    UserGroupIcon,
    ArrowTrendingUpIcon,
    CalendarIcon,
    StarIcon
} from '@heroicons/vue/24/outline';
import { formatCurrency, formatPercentage } from '@/utils/formatting';

interface PerformanceReport {
    overview: {
        direct_referrals_count: number;
        active_referrals_count: number;
        total_referrals_count: number;
        total_commission_earned: number;
        pending_commission: number;
        commissions_by_level: Array<{
            level: number;
            count: number;
            total_amount: number;
        }>;
        matrix_downline: {
            level_1: number;
            level_2: number;
            level_3: number;
            total: number;
        };
        referral_conversion_rate: number;
    };
    matrix_performance: {
        has_matrix_position: boolean;
        performance_metrics: Array<any>;
    };
    earnings_breakdown: {
        referral_commissions: number;
        profit_shares: number;
        matrix_commissions: number;
        reinvestment_bonuses: number;
        total_earnings: number;
        pending_earnings: number;
    };
    tier_analysis: Array<any>;
    growth_metrics: Array<any>;
    commission_trends: {
        by_level: Array<any>;
        by_month: Array<any>;
        recent_performance: {
            last_30_days: number;
            last_90_days: number;
            last_year: number;
        };
    };
}

interface Props {
    report: PerformanceReport;
    user: {
        id: number;
        name: string;
        email: string;
    };
}

const props = defineProps<Props>();

const totalEarnings = computed(() => {
    return props.report.earnings_breakdown.total_earnings;
});

const conversionRate = computed(() => {
    return props.report.overview.referral_conversion_rate;
});

const getLevelColor = (level: number): string => {
    const colors: Record<number, string> = {
        1: 'bg-blue-50 text-blue-600',
        2: 'bg-green-50 text-green-600',
        3: 'bg-purple-50 text-purple-600'
    };
    return colors[level] || 'bg-gray-50 text-gray-600';
};
</script>

<template>
    <InvestorLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Performance Report
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Overview Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="p-3 bg-green-50 rounded-lg">
                                    <CurrencyDollarIcon class="h-6 w-6 text-green-600" />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Earnings</p>
                                <p class="text-2xl font-bold text-green-600">
                                    {{ formatCurrency(totalEarnings) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="p-3 bg-blue-50 rounded-lg">
                                    <UserGroupIcon class="h-6 w-6 text-blue-600" />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Referrals</p>
                                <p class="text-2xl font-bold text-blue-600">
                                    {{ report.overview.total_referrals_count }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="p-3 bg-purple-50 rounded-lg">
                                    <ChartBarIcon class="h-6 w-6 text-purple-600" />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Conversion Rate</p>
                                <p class="text-2xl font-bold text-purple-600">
                                    {{ formatPercentage(conversionRate) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="p-3 bg-yellow-50 rounded-lg">
                                    <StarIcon class="h-6 w-6 text-yellow-600" />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Active Referrals</p>
                                <p class="text-2xl font-bold text-yellow-600">
                                    {{ report.overview.active_referrals_count }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Earnings Breakdown -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Earnings Breakdown</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Referral Commissions</span>
                                <span class="font-semibold text-green-600">
                                    {{ formatCurrency(report.earnings_breakdown.referral_commissions) }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Matrix Commissions</span>
                                <span class="font-semibold text-blue-600">
                                    {{ formatCurrency(report.earnings_breakdown.matrix_commissions) }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Profit Shares</span>
                                <span class="font-semibold text-purple-600">
                                    {{ formatCurrency(report.earnings_breakdown.profit_shares) }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Reinvestment Bonuses</span>
                                <span class="font-semibold text-yellow-600">
                                    {{ formatCurrency(report.earnings_breakdown.reinvestment_bonuses) }}
                                </span>
                            </div>
                            <div class="border-t pt-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-base font-medium text-gray-900">Total Earnings</span>
                                    <span class="text-lg font-bold text-green-600">
                                        {{ formatCurrency(report.earnings_breakdown.total_earnings) }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Pending Earnings</span>
                                <span class="font-semibold text-orange-600">
                                    {{ formatCurrency(report.earnings_breakdown.pending_earnings) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Matrix Performance -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Matrix Performance</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-sm text-gray-600">Matrix Status</span>
                                <span 
                                    :class="report.matrix_performance.has_matrix_position ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                >
                                    {{ report.matrix_performance.has_matrix_position ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Level 1 Downline</span>
                                    <span class="font-semibold text-blue-600">
                                        {{ report.overview.matrix_downline.level_1 }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Level 2 Downline</span>
                                    <span class="font-semibold text-green-600">
                                        {{ report.overview.matrix_downline.level_2 }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Level 3 Downline</span>
                                    <span class="font-semibold text-purple-600">
                                        {{ report.overview.matrix_downline.level_3 }}
                                    </span>
                                </div>
                                <div class="border-t pt-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-base font-medium text-gray-900">Total Downline</span>
                                        <span class="text-lg font-bold text-gray-900">
                                            {{ report.overview.matrix_downline.total }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Commission by Level -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Commission by Level</h3>
                    <div v-if="report.overview.commissions_by_level.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div 
                            v-for="levelData in report.overview.commissions_by_level" 
                            :key="levelData.level"
                            class="bg-gray-50 rounded-lg p-4"
                        >
                            <div class="flex items-center justify-between mb-3">
                                <span 
                                    :class="getLevelColor(levelData.level)"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                >
                                    Level {{ levelData.level }}
                                </span>
                                <span class="text-sm font-medium text-gray-900">
                                    {{ levelData.count }} commissions
                                </span>
                            </div>
                            <div class="text-2xl font-bold text-green-600">
                                {{ formatCurrency(levelData.total_amount) }}
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8">
                        <ChartBarIcon class="h-12 w-12 text-gray-400 mx-auto mb-4" />
                        <p class="text-gray-500">No commission data available yet</p>
                    </div>
                </div>

                <!-- Recent Performance -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Recent Performance</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-blue-50 rounded-lg p-4">
                            <div class="flex items-center">
                                <CalendarIcon class="h-5 w-5 text-blue-600 mr-2" />
                                <span class="text-sm font-medium text-blue-900">Last 30 Days</span>
                            </div>
                            <div class="mt-2 text-2xl font-bold text-blue-600">
                                {{ formatCurrency(report.commission_trends.recent_performance.last_30_days) }}
                            </div>
                        </div>
                        
                        <div class="bg-green-50 rounded-lg p-4">
                            <div class="flex items-center">
                                <CalendarIcon class="h-5 w-5 text-green-600 mr-2" />
                                <span class="text-sm font-medium text-green-900">Last 90 Days</span>
                            </div>
                            <div class="mt-2 text-2xl font-bold text-green-600">
                                {{ formatCurrency(report.commission_trends.recent_performance.last_90_days) }}
                            </div>
                        </div>
                        
                        <div class="bg-purple-50 rounded-lg p-4">
                            <div class="flex items-center">
                                <CalendarIcon class="h-5 w-5 text-purple-600 mr-2" />
                                <span class="text-sm font-medium text-purple-900">Last Year</span>
                            </div>
                            <div class="mt-2 text-2xl font-bold text-purple-600">
                                {{ formatCurrency(report.commission_trends.recent_performance.last_year) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="flex justify-center space-x-4">
                    <Link 
                        :href="route('referrals.index')"
                        class="inline-flex items-center px-4 py-2 bg-gray-100 border border-transparent rounded-lg font-medium text-sm text-gray-700 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                    >
                        Back to Referrals
                    </Link>
                    <Link 
                        :href="route('referrals.commissions')"
                        class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    >
                        View Commission History
                    </Link>
                </div>
            </div>
        </div>
    </InvestorLayout>
</template>