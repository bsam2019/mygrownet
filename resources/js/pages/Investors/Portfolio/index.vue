<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import InvestorLayout from '@/layouts/InvestorLayout.vue';
import PortfolioStats from '@/components/Portfolio/PortfolioStats.vue';
import { 
    CurrencyDollarIcon, 
    ChartBarIcon, 
    ArrowTrendingUpIcon,
    ClockIcon,
    DocumentTextIcon
} from '@heroicons/vue/24/outline';
import { formatCurrency } from '@/utils/formatting';

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

interface InvestmentData {
    data: Investment[];
    meta: {
        next_payment_date?: string;
    };
}

interface Portfolio {
    total_investment: number;
    total_returns: number;
    active_referrals: number;
    referral_earnings: number;
}

interface PageProps {
    investments?: InvestmentData;
    portfolio: Portfolio;
    [key: string]: any;
}

const page = usePage<PageProps>();
const loading = ref(false);
const error = ref<string | null>(null);

// Initialize investments with default values
const defaultInvestments: InvestmentData = {
    data: [],
    meta: {
        next_payment_date: undefined
    }
};

// Initialize the investments ref with computed to ensure reactivity and safe access
const investments = computed(() => {
    return page.props.investments || defaultInvestments;
});

const portfolio = computed(() => {
    return page.props.portfolio || {
        total_investment: 0,
        total_returns: 0,
        active_referrals: 0,
        referral_earnings: 0
    };
});



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
    <InvestorLayout :footer-nav-items="footerNavItems">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Portfolio
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

                <!-- Portfolio Content -->
                <div v-else class="space-y-6">
                    <!-- Welcome Section -->
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-gray-900">Your Investment Portfolio</h1>
                        <p class="mt-2 text-gray-600">Track and manage all your active investments in one place.</p>
                    </div>

                    <!-- Portfolio Stats -->
                    <PortfolioStats
                        :portfolio="portfolio"
                        :investments="investments"
                    />

                    <!-- Active Investments -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-lg font-semibold text-gray-900">Active Investments</h3>
                            </div>

                            <div class="space-y-6">
                                <div v-for="investment in investments?.data || []" :key="investment.id" 
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
                            <div v-if="!investments?.data?.length" class="text-center py-8">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                                    <DocumentTextIcon class="h-8 w-8 text-gray-400" />
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
            </div>
        </div>
    </InvestorLayout>
</template> 