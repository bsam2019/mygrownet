<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { 
    CurrencyDollarIcon,
    ChartBarIcon,
    ClockIcon,
    ExclamationTriangleIcon,
    CheckCircleIcon,
    ArrowTrendingUpIcon,
    CalendarIcon,
    LockClosedIcon,
    BanknotesIcon
} from '@heroicons/vue/24/outline';
import { formatCurrency, formatDate } from '@/utils/formatting';

interface Investment {
    id: number;
    amount: number;
    status: string;
    created_at: string;
    tier: {
        id: number;
        name: string;
        fixed_profit_rate: number;
    };
    user: {
        id: number;
        name: string;
        email: string;
    };
}

interface InvestmentMetrics {
    initial_amount: number;
    current_value: number;
    profit_amount: number;
    roi_percentage: number;
    annualized_return: number;
    investment_age_days: number;
    tier: string;
    lock_in_status: {
        is_locked: boolean;
        days_remaining: number;
        months_remaining: number;
        end_date: string;
        message: string;
    };
    withdrawal_eligibility: {
        can_withdraw: boolean;
        withdrawal_type: string;
        penalty_amount: number;
        net_amount: number;
        message: string;
    };
}

interface WithdrawalInfo {
    can_withdraw: boolean;
    withdrawal_type: string;
    requires_approval?: boolean;
    penalty_amount: number;
    net_amount: number;
    penalty_breakdown?: any;
    message: string;
}

interface Projection {
    month: number;
    projected_value: number;
    projected_profit: number;
    roi_percentage: number;
}

interface Penalties {
    months_remaining: number;
    penalty_tier: string;
    profit_penalty_rate: number;
    capital_penalty_rate: number;
    profit_penalty_amount: number;
    capital_penalty_amount: number;
    total_penalty: number;
    net_withdrawable: number;
}

interface LockInStatus {
    is_locked: boolean;
    days_remaining: number;
    months_remaining: number;
    end_date: string;
    message: string;
}

interface PageProps {
    investment: Investment;
    metrics: InvestmentMetrics;
    withdrawalInfo: WithdrawalInfo;
    projections: Projection[];
    penalties: Penalties;
    lockInStatus: LockInStatus;
}

const page = usePage<PageProps>();

const getStatusColor = (status: string) => {
    const colors = {
        'active': 'bg-green-100 text-green-800',
        'pending': 'bg-yellow-100 text-yellow-800',
        'rejected': 'bg-red-100 text-red-800',
        'completed': 'bg-blue-100 text-blue-800'
    };
    return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800';
};

const getTierColor = (tier: string) => {
    const colors = {
        'Basic': 'bg-gray-100 text-gray-800',
        'Starter': 'bg-blue-100 text-blue-800',
        'Builder': 'bg-green-100 text-green-800',
        'Leader': 'bg-purple-100 text-purple-800',
        'Elite': 'bg-yellow-100 text-yellow-800'
    };
    return colors[tier as keyof typeof colors] || 'bg-gray-100 text-gray-800';
};

const getPenaltyTierColor = (tier: string) => {
    const colors = {
        'tier_1': 'bg-red-100 text-red-800',
        'tier_2': 'bg-red-100 text-red-800',
        'tier_3': 'bg-orange-100 text-orange-800',
        'tier_4': 'bg-yellow-100 text-yellow-800',
        'none': 'bg-green-100 text-green-800'
    };
    return colors[tier as keyof typeof colors] || 'bg-gray-100 text-gray-800';
};

const chartData = computed(() => {
    return page.props.projections.map(p => ({
        month: `Month ${p.month}`,
        value: p.projected_value,
        profit: p.projected_profit
    }));
});
</script>

<template>
    <MemberLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Investment Details
                </h2>
                <Link :href="route('investments.index')" 
                      class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                    ← Back to Investments
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="space-y-8">
                    <!-- Investment Overview -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Investment Overview</h3>
                                <p class="text-gray-600">Investment ID: #{{ investment.id }}</p>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span :class="getStatusColor(investment.status)" 
                                      class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium">
                                    {{ investment.status.charAt(0).toUpperCase() + investment.status.slice(1) }}
                                </span>
                                <span :class="getTierColor(investment.tier.name)" 
                                      class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium">
                                    {{ investment.tier.name }}
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div class="text-center p-4 bg-blue-50 rounded-lg">
                                <CurrencyDollarIcon class="h-8 w-8 text-blue-600 mx-auto mb-2" />
                                <div class="text-2xl font-bold text-gray-900">{{ formatCurrency(metrics.initial_amount) }}</div>
                                <div class="text-sm text-gray-500">Initial Investment</div>
                            </div>
                            
                            <div class="text-center p-4 bg-green-50 rounded-lg">
                                <ArrowTrendingUpIcon class="h-8 w-8 text-green-600 mx-auto mb-2" />
                                <div class="text-2xl font-bold text-green-600">{{ formatCurrency(metrics.current_value) }}</div>
                                <div class="text-sm text-gray-500">Current Value</div>
                            </div>
                            
                            <div class="text-center p-4 bg-purple-50 rounded-lg">
                                <ChartBarIcon class="h-8 w-8 text-purple-600 mx-auto mb-2" />
                                <div class="text-2xl font-bold text-purple-600">{{ metrics.roi_percentage.toFixed(2) }}%</div>
                                <div class="text-sm text-gray-500">ROI</div>
                            </div>
                            
                            <div class="text-center p-4 bg-yellow-50 rounded-lg">
                                <CalendarIcon class="h-8 w-8 text-yellow-600 mx-auto mb-2" />
                                <div class="text-2xl font-bold text-yellow-600">{{ metrics.investment_age_days }}</div>
                                <div class="text-sm text-gray-500">Days Active</div>
                            </div>
                        </div>
                    </div>

                    <!-- Lock-in Period & Withdrawal Status -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Lock-in Period -->
                        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                            <div class="flex items-center mb-4">
                                <LockClosedIcon class="h-6 w-6 text-gray-600 mr-3" />
                                <h3 class="text-lg font-semibold text-gray-900">Lock-in Period</h3>
                            </div>
                            
                            <div v-if="lockInStatus.is_locked" class="space-y-4">
                                <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <div class="flex items-center">
                                        <ExclamationTriangleIcon class="h-5 w-5 text-yellow-600 mr-2" />
                                        <span class="text-sm font-medium text-yellow-800">Investment is locked</span>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                                        <div class="text-xl font-bold text-gray-900">{{ lockInStatus.days_remaining }}</div>
                                        <div class="text-sm text-gray-500">Days Remaining</div>
                                    </div>
                                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                                        <div class="text-xl font-bold text-gray-900">{{ lockInStatus.months_remaining }}</div>
                                        <div class="text-sm text-gray-500">Months Remaining</div>
                                    </div>
                                </div>
                                
                                <div class="text-sm text-gray-600">
                                    <strong>Lock-in ends:</strong> {{ formatDate(lockInStatus.end_date) }}
                                </div>
                            </div>
                            
                            <div v-else class="space-y-4">
                                <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                                    <div class="flex items-center">
                                        <CheckCircleIcon class="h-5 w-5 text-green-600 mr-2" />
                                        <span class="text-sm font-medium text-green-800">Lock-in period completed</span>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600">{{ lockInStatus.message }}</p>
                            </div>
                        </div>

                        <!-- Withdrawal Information -->
                        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                            <div class="flex items-center mb-4">
                                <BanknotesIcon class="h-6 w-6 text-gray-600 mr-3" />
                                <h3 class="text-lg font-semibold text-gray-900">Withdrawal Status</h3>
                            </div>
                            
                            <div class="space-y-4">
                                <div :class="withdrawalInfo.can_withdraw ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'" 
                                     class="p-4 border rounded-lg">
                                    <div class="flex items-center">
                                        <component :is="withdrawalInfo.can_withdraw ? CheckCircleIcon : ExclamationTriangleIcon" 
                                                   :class="withdrawalInfo.can_withdraw ? 'text-green-600' : 'text-red-600'" 
                                                   class="h-5 w-5 mr-2" />
                                        <span :class="withdrawalInfo.can_withdraw ? 'text-green-800' : 'text-red-800'" 
                                              class="text-sm font-medium">
                                            {{ withdrawalInfo.message }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                                        <div class="text-lg font-bold text-gray-900">{{ formatCurrency(withdrawalInfo.net_amount) }}</div>
                                        <div class="text-sm text-gray-500">Net Withdrawable</div>
                                    </div>
                                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                                        <div class="text-lg font-bold text-red-600">{{ formatCurrency(withdrawalInfo.penalty_amount) }}</div>
                                        <div class="text-sm text-gray-500">Penalty Amount</div>
                                    </div>
                                </div>
                                
                                <div v-if="withdrawalInfo.requires_approval" class="text-sm text-gray-600">
                                    <strong>Note:</strong> Early withdrawal requires emergency approval
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Penalty Breakdown (if applicable) -->
                    <div v-if="penalties.total_penalty > 0" class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Penalty Breakdown</h3>
                        
                        <div class="mb-4">
                            <span :class="getPenaltyTierColor(penalties.penalty_tier)" 
                                  class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium">
                                Penalty Tier: {{ penalties.penalty_tier.replace('_', ' ').toUpperCase() }}
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="text-center p-4 bg-red-50 rounded-lg">
                                <div class="text-lg font-bold text-red-600">{{ penalties.profit_penalty_rate }}%</div>
                                <div class="text-sm text-gray-500">Profit Penalty Rate</div>
                            </div>
                            <div class="text-center p-4 bg-red-50 rounded-lg">
                                <div class="text-lg font-bold text-red-600">{{ penalties.capital_penalty_rate }}%</div>
                                <div class="text-sm text-gray-500">Capital Penalty Rate</div>
                            </div>
                            <div class="text-center p-4 bg-orange-50 rounded-lg">
                                <div class="text-lg font-bold text-orange-600">{{ formatCurrency(penalties.profit_penalty_amount) }}</div>
                                <div class="text-sm text-gray-500">Profit Penalty</div>
                            </div>
                            <div class="text-center p-4 bg-orange-50 rounded-lg">
                                <div class="text-lg font-bold text-orange-600">{{ formatCurrency(penalties.capital_penalty_amount) }}</div>
                                <div class="text-sm text-gray-500">Capital Penalty</div>
                            </div>
                        </div>
                    </div>

                    <!-- Future Projections -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">12-Month Projections</h3>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Projected Value</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Projected Profit</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ROI %</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="projection in projections.slice(0, 6)" :key="projection.month" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            Month {{ projection.month }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatCurrency(projection.projected_value) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">
                                            {{ formatCurrency(projection.projected_profit) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ projection.roi_percentage.toFixed(2) }}%
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div v-if="projections.length > 6" class="mt-4 text-center">
                            <button class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                                View All Projections
                            </button>
                        </div>
                    </div>

                    <!-- Investment Details -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Investment Details</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Investment Date</span>
                                    <span class="text-sm font-medium text-gray-900">{{ formatDate(investment.created_at) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Tier</span>
                                    <span class="text-sm font-medium text-gray-900">{{ investment.tier.name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Profit Rate</span>
                                    <span class="text-sm font-medium text-gray-900">{{ investment.tier.fixed_profit_rate }}% annually</span>
                                </div>
                            </div>
                            
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Status</span>
                                    <span :class="getStatusColor(investment.status)" 
                                          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium">
                                        {{ investment.status.charAt(0).toUpperCase() + investment.status.slice(1) }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Annualized Return</span>
                                    <span class="text-sm font-medium text-green-600">{{ metrics.annualized_return.toFixed(2) }}%</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Total Profit</span>
                                    <span class="text-sm font-medium text-green-600">{{ formatCurrency(metrics.profit_amount) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>