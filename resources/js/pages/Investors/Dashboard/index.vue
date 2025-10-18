<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import InvestorLayout from '@/layouts/InvestorLayout.vue';
import { 
    CurrencyDollarIcon, 
    ChartBarIcon, 
    UserGroupIcon, 
    BanknotesIcon,
    ExclamationCircleIcon,
    ArrowRightIcon,
    ClockIcon,
    ArrowTrendingUpIcon,
    LightBulbIcon
} from '@heroicons/vue/24/outline';
import { formatCurrency } from '@/utils/formatting';
import RewardSystemOverview from '@/components/RewardSystemOverview.vue';
import RewardSystemActions from '@/components/RewardSystemActions.vue';

interface Portfolio {
    total_investment: number;
    total_returns: number;
    active_referrals: number;
    referral_earnings: number;
    total_earnings: number;
    pending_earnings: number;
}

interface Investment {
    id: number;
    amount: number;
    returns: number;
    created_at: string;
    opportunity: {
        id: number;
        name: string;
        risk_level: string;
        duration: number;
    };
}

interface InvestmentOpportunity {
    id: number;
    name: string;
    description: string;
    minimum_investment: number;
    expected_returns: number;
    risk_level: string;
}

interface PageProps {
    portfolio: Portfolio;
    investments?: { data: Investment[] };
    investment_opportunities: InvestmentOpportunity[];
    [key: string]: any;
}

const page = usePage<PageProps>();
const loading = ref(false);
const error = ref<string | null>(null);

const portfolio = computed(() => page.props.portfolio || {
    total_investment: 0,
    total_returns: 0,
    active_referrals: 0,
    referral_earnings: 0,
    total_earnings: 0,
    pending_earnings: 0
});

const investments = computed(() => page.props.investments?.data || []);
const investmentOpportunities = computed(() => page.props.investment_opportunities || []);

const getRiskLevelColor = (riskLevel: string | undefined): string => {
    if (!riskLevel) return 'text-gray-600 bg-gray-50';
    
    switch(riskLevel.toLowerCase()) {
        case 'low':
            return 'text-green-600 bg-green-50';
        case 'medium':
            return 'text-yellow-600 bg-yellow-50';
        case 'high':
            return 'text-red-600 bg-red-50';
        default:
            return 'text-gray-600 bg-gray-50';
    }
};
</script>

<template>
    <InvestorLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Loading State -->
                <div v-if="loading" class="flex justify-center items-center h-64">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <ExclamationCircleIcon class="h-5 w-5 text-red-400" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                {{ error }}
                            </h3>
                        </div>
                    </div>
                </div>

                <!-- Dashboard Content -->
                <div v-else class="space-y-6">
                    <!-- Welcome Section -->
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-gray-900">Welcome to Your Investment Dashboard</h1>
                        <p class="mt-2 text-gray-600">Track your investments, explore opportunities, and manage your portfolio all in one place.</p>
                    </div>

                    <!-- VBIF Stats Overview -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="p-3 bg-primary-50 rounded-lg">
                                        <BanknotesIcon class="h-6 w-6 text-primary-600" />
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Total Investment</p>
                                    <p class="text-2xl font-bold text-gray-900">
                                        {{ formatCurrency(portfolio.total_investment) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="p-3 bg-green-50 rounded-lg">
                                        <ArrowTrendingUpIcon class="h-6 w-6 text-green-600" />
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Total Earnings</p>
                                    <p class="text-2xl font-bold text-green-600">
                                        {{ formatCurrency(portfolio.total_earnings) }}
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
                                    <p class="text-sm font-medium text-gray-500">Active Referrals</p>
                                    <p class="text-2xl font-bold text-gray-900">
                                        {{ portfolio.active_referrals }}
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
                                    <p class="text-sm font-medium text-gray-500">Pending Earnings</p>
                                    <p class="text-2xl font-bold text-purple-600">
                                        {{ formatCurrency(portfolio.pending_earnings) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reward System Overview -->
                    <div class="mb-8">
                        <RewardSystemOverview 
                            :earnings="$page.props.earnings"
                            :referral-stats="$page.props.referralStats"
                            :tier-info="$page.props.tierInfo"
                            :matrix-data="$page.props.downlineCounts"
                        />
                    </div>

                    <!-- Main Content Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Active Investments -->
                        <div class="lg:col-span-2">
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="p-6">
                                    <div class="flex justify-between items-center mb-6">
                                        <h3 class="text-lg font-semibold text-gray-900">Active Investments</h3>
                                        <Link 
                                            :href="route('investments.index')" 
                                            class="text-sm text-primary-600 hover:text-primary-700 font-medium flex items-center"
                                        >
                                            View All
                                            <ArrowRightIcon class="h-4 w-4 ml-1" />
                                        </Link>
                                    </div>

                                    <div class="space-y-6">
                                        <div v-for="investment in investments" :key="investment.id" 
                                            class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors duration-200">
                                            <div class="flex justify-between items-start mb-3">
                                                <div>
                                                    <h4 class="text-base font-semibold text-gray-900">
                                                        {{ investment.opportunity.name }}
                                                    </h4>
                                                    <p class="text-sm text-gray-500">
                                                        Invested on {{ investment.created_at }}
                                                    </p>
                                                </div>
                                                <span 
                                                    :class="getRiskLevelColor(investment.opportunity?.risk_level)"
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                >
                                                    {{ investment.opportunity?.risk_level ? investment.opportunity.risk_level.charAt(0).toUpperCase() + investment.opportunity.risk_level.slice(1) : 'Unknown' }} Risk
                                                </span>
                                            </div>
                                            
                                            <div class="grid grid-cols-2 gap-4 mb-3">
                                                <div class="flex items-center">
                                                    <CurrencyDollarIcon class="h-5 w-5 text-gray-400 mr-2" />
                                                    <div>
                                                        <span class="text-xs text-gray-500 block">Amount</span>
                                                        <span class="text-sm font-semibold text-gray-900">
                                                            {{ formatCurrency(investment.amount) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="flex items-center">
                                                    <ChartBarIcon class="h-5 w-5 text-gray-400 mr-2" />
                                                    <div>
                                                        <span class="text-xs text-gray-500 block">Returns</span>
                                                        <span class="text-sm font-semibold text-green-600">
                                                            {{ formatCurrency(investment.returns) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="flex items-center">
                                                <ClockIcon class="h-5 w-5 text-gray-400 mr-2" />
                                                <div>
                                                    <span class="text-xs text-gray-500 block">Duration</span>
                                                    <span class="text-sm font-medium text-gray-900">
                                                        {{ investment.opportunity.duration }} months
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Empty State -->
                                    <div v-if="!investments.length" class="text-center py-8">
                                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                                            <BanknotesIcon class="h-8 w-8 text-gray-400" />
                                        </div>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">No active investments</h3>
                                        <p class="text-gray-500 max-w-md mx-auto">Start your investment journey by exploring our available opportunities.</p>
                                        <Link 
                                            :href="route('opportunities')" 
                                            class="mt-4 inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200"
                                        >
                                            Explore Opportunities
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar Content -->
                        <div class="lg:col-span-1 space-y-6">
                            <!-- Reward System Actions -->
                            <RewardSystemActions 
                                :referral-code="$page.props.auth?.user?.referral_code"
                                :can-upgrade="$page.props.tierInfo?.eligible"
                                :next-tier="$page.props.tierInfo?.next_tier?.name"
                                :pending-commissions="$page.props.earnings?.pending_earnings"
                            />

                            <!-- Featured Opportunities -->
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="p-6">
                                    <div class="flex justify-between items-center mb-6">
                                        <h3 class="text-lg font-semibold text-gray-900">Featured Opportunities</h3>
                                        <Link 
                                            :href="route('opportunities')" 
                                            class="text-sm text-primary-600 hover:text-primary-700 font-medium flex items-center"
                                        >
                                            View All
                                            <ArrowRightIcon class="h-4 w-4 ml-1" />
                                        </Link>
                                    </div>

                                    <div class="space-y-4">
                                        <div v-for="opportunity in investmentOpportunities" :key="opportunity.id" 
                                            class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors duration-200">
                                            <div class="flex justify-between items-start mb-2">
                                                <h4 class="text-base font-semibold text-gray-900">
                                                    {{ opportunity.name }}
                                                </h4>
                                                <span 
                                                    :class="getRiskLevelColor(opportunity.risk_level)"
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                >
                                                    {{ opportunity.risk_level ? opportunity.risk_level.charAt(0).toUpperCase() + opportunity.risk_level.slice(1) : 'Unknown' }} Risk
                                                </span>
                                            </div>
                                            
                                            <p class="text-sm text-gray-600 mb-3">{{ opportunity.description }}</p>
                                            
                                            <div class="flex justify-between items-center">
                                                <div class="text-sm">
                                                    <span class="text-gray-500">Min Investment:</span>
                                                    <span class="font-semibold text-gray-900 ml-1">
                                                        {{ formatCurrency(opportunity.minimum_investment) }}
                                                    </span>
                                                </div>
                                                <div class="text-sm">
                                                    <span class="text-gray-500">Expected Returns:</span>
                                                    <span class="font-semibold text-green-600 ml-1">
                                                        {{ opportunity.expected_returns }}%
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="mt-3">
                                                <Link 
                                                    :href="route('investments.create', { opportunity: opportunity.id })" 
                                                    class="w-full inline-flex justify-center items-center px-4 py-2 bg-primary-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200"
                                                >
                                                    Invest Now
                                                </Link>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Empty State for Opportunities -->
                                    <div v-if="!investmentOpportunities.length" class="text-center py-8">
                                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                                            <LightBulbIcon class="h-8 w-8 text-gray-400" />
                                        </div>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">No opportunities available</h3>
                                        <p class="text-gray-500">Check back later for new investment opportunities.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </InvestorLayout>
</template>
