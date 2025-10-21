<template>
    <MemberLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Portfolio Overview
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Portfolio Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-3 bg-blue-50 rounded-lg">
                                <CurrencyDollarIcon class="h-6 w-6 text-blue-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Invested</p>
                                <p class="text-2xl font-bold text-gray-900">
                                    {{ formatCurrency(portfolio.total_invested) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-3 bg-green-50 rounded-lg">
                                <ArrowTrendingUpIcon class="h-6 w-6 text-green-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Current Value</p>
                                <p class="text-2xl font-bold text-green-600">
                                    {{ formatCurrency(portfolio.current_value) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-3 bg-emerald-50 rounded-lg">
                                <ArrowTrendingUpIcon class="h-6 w-6 text-emerald-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Returns</p>
                                <p class="text-2xl font-bold" :class="portfolio.total_returns >= 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ formatCurrency(Math.abs(portfolio.total_returns)) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-3 bg-purple-50 rounded-lg">
                                <ChartBarIcon class="h-6 w-6 text-purple-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Average ROI</p>
                                <p class="text-2xl font-bold text-purple-600">
                                    {{ portfolio.average_roi }}%
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Investment List -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                            <div class="p-6 border-b border-gray-100">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-900">My Investments</h3>
                                    <span class="text-sm text-gray-500">{{ portfolio.investment_count }} investments</span>
                                </div>
                            </div>
                            
                            <div class="divide-y divide-gray-100">
                                <div v-for="investment in investments" :key="investment.id" 
                                     class="p-6 hover:bg-gray-50 transition-colors duration-200">
                                    <div class="flex items-center justify-between mb-4">
                                        <div>
                                            <h4 class="text-base font-semibold text-gray-900">
                                                {{ investment.tier.name }}
                                            </h4>
                                            <p class="text-sm text-gray-500">
                                                Invested on {{ investment.investment_date }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-bold text-gray-900">
                                                {{ formatCurrency(investment.current_value) }}
                                            </div>
                                            <div class="text-sm" :class="investment.returns >= 0 ? 'text-green-600' : 'text-red-600'">
                                                {{ investment.returns >= 0 ? '+' : '' }}{{ formatCurrency(Math.abs(investment.returns)) }}
                                                ({{ investment.roi.toFixed(2) }}%)
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-3 gap-4 text-sm">
                                        <div>
                                            <span class="text-gray-500">Initial:</span>
                                            <span class="font-medium ml-1">{{ formatCurrency(investment.amount) }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Profit Rate:</span>
                                            <span class="font-medium ml-1">{{ investment.tier.profit_rate }}%</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Status:</span>
                                            <span class="font-medium ml-1 capitalize" :class="getStatusColor(investment.status)">
                                                {{ investment.status }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty State -->
                            <div v-if="!investments.length" class="p-12 text-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                                    <ChartPieIcon class="h-8 w-8 text-gray-400" />
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No investments yet</h3>
                                <p class="text-gray-500 mb-4">Start building your portfolio by making your first investment.</p>
                                <Link 
                                    :href="route('opportunities')"
                                    class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200"
                                >
                                    Explore Opportunities
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Tier Distribution -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Tier Distribution</h3>
                            <div class="space-y-4">
                                <div v-for="tier in portfolio.tier_distribution" :key="tier.tier" 
                                     class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 rounded-full mr-3" :style="{ backgroundColor: getTierColor(tier.tier) }"></div>
                                        <span class="text-sm font-medium text-gray-700">{{ tier.tier }}</span>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-semibold text-gray-900">{{ tier.percentage.toFixed(1) }}%</div>
                                        <div class="text-xs text-gray-500">{{ formatCurrency(tier.total_amount) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Transactions -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Recent Transactions</h3>
                                <Link 
                                    :href="route('transactions')"
                                    class="text-sm text-primary-600 hover:text-primary-700 font-medium"
                                >
                                    View All
                                </Link>
                            </div>
                            
                            <div class="space-y-3">
                                <div v-for="transaction in recentTransactions" :key="transaction.id" 
                                     class="flex items-center justify-between py-2">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ transaction.type }}</div>
                                        <div class="text-xs text-gray-500">{{ transaction.created_at }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-semibold" :class="getTransactionColor(transaction.type)">
                                            {{ formatCurrency(transaction.amount) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty State -->
                            <div v-if="!recentTransactions.length" class="text-center py-6">
                                <p class="text-sm text-gray-500">No recent transactions</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>

<script setup lang="ts">
import MemberLayout from '@/layouts/MemberLayout.vue';
import { Link } from '@inertiajs/vue3';
import { 
    CurrencyDollarIcon, 
    ArrowTrendingUpIcon, 
    ChartBarIcon,
    ChartPieIcon
} from '@heroicons/vue/24/outline';

interface Portfolio {
    total_invested: number;
    current_value: number;
    total_returns: number;
    average_roi: number;
    investment_count: number;
    tier_distribution: Array<{
        tier: string;
        count: number;
        total_amount: number;
        current_value: number;
        percentage: number;
    }>;
    performance_history: Array<{
        month: string;
        value: number;
        returns: number;
    }>;
}

interface Investment {
    id: number;
    amount: number;
    current_value: number;
    returns: number;
    roi: number;
    tier: {
        name: string;
        profit_rate: number;
    };
    investment_date: string;
    status: string;
}

interface Transaction {
    id: number;
    type: string;
    amount: number;
    description: string;
    status: string;
    created_at: string;
    investment_tier?: string;
}

interface Props {
    portfolio: Portfolio;
    investments: Investment[];
    recentTransactions: Transaction[];
}

const props = defineProps<Props>();

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat().format(amount);
};

const getStatusColor = (status: string): string => {
    switch (status.toLowerCase()) {
        case 'approved':
            return 'text-green-600';
        case 'pending':
            return 'text-yellow-600';
        case 'rejected':
            return 'text-red-600';
        default:
            return 'text-gray-600';
    }
};

const getTierColor = (tierName: string): string => {
    const colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'];
    const index = tierName.length % colors.length;
    return colors[index];
};

const getTransactionColor = (type: string): string => {
    switch (type.toLowerCase()) {
        case 'investment':
            return 'text-blue-600';
        case 'profit':
        case 'commission':
            return 'text-green-600';
        case 'withdrawal':
            return 'text-red-600';
        default:
            return 'text-gray-600';
    }
};
</script>