<template>
    <InvestorLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ opportunity.name }}
                    </h2>
                    <p class="text-gray-600 mt-1">Investment Opportunity Details</p>
                </div>
                <Link 
                    :href="route('opportunities')"
                    class="text-sm text-primary-600 hover:text-primary-700 font-medium flex items-center"
                >
                    <ArrowLeftIcon class="h-4 w-4 mr-1" />
                    Back to Opportunities
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <!-- Header Section -->
                    <div class="p-8 border-b border-gray-200">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900">{{ opportunity.name }}</h1>
                                <p class="text-gray-600 mt-2">{{ opportunity.description }}</p>
                            </div>
                            <span 
                                :class="getRiskBadgeClass(opportunity.risk_level)"
                                class="px-4 py-2 rounded-full text-sm font-medium"
                            >
                                {{ opportunity.risk_level.charAt(0).toUpperCase() + opportunity.risk_level.slice(1) }} Risk
                            </span>
                        </div>

                        <!-- Key Metrics -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div class="text-center p-4 bg-primary-50 rounded-lg">
                                <div class="text-3xl font-bold text-primary-600">{{ opportunity.expected_returns }}%</div>
                                <div class="text-sm text-gray-600 mt-1">Annual Returns</div>
                            </div>
                            <div class="text-center p-4 bg-green-50 rounded-lg">
                                <div class="text-3xl font-bold text-green-600">{{ formatCurrency(opportunity.minimum_investment) }}</div>
                                <div class="text-sm text-gray-600 mt-1">Min Investment</div>
                            </div>
                            <div class="text-center p-4 bg-blue-50 rounded-lg">
                                <div class="text-3xl font-bold text-blue-600">{{ opportunity.duration }}</div>
                                <div class="text-sm text-gray-600 mt-1">Months</div>
                            </div>
                            <div class="text-center p-4 bg-purple-50 rounded-lg">
                                <div class="text-3xl font-bold text-purple-600">{{ opportunity.risk_level.charAt(0).toUpperCase() + opportunity.risk_level.slice(1) }}</div>
                                <div class="text-sm text-gray-600 mt-1">Risk Level</div>
                            </div>
                        </div>
                    </div>

                    <!-- Features Section -->
                    <div class="p-8 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 mb-6">Investment Features</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="feature in opportunity.features" :key="feature" 
                                 class="flex items-center p-4 bg-gray-50 rounded-lg">
                                <CheckCircleIcon class="h-6 w-6 text-green-500 mr-3 flex-shrink-0" />
                                <span class="text-gray-700">{{ feature }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Benefits Section -->
                    <div class="p-8 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 mb-6">Key Benefits</h2>
                        <div class="space-y-4">
                            <div v-for="benefit in opportunity.benefits" :key="benefit" 
                                 class="flex items-start">
                                <StarIcon class="h-5 w-5 text-yellow-500 mr-3 mt-0.5 flex-shrink-0" />
                                <span class="text-gray-700">{{ benefit }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Investment Calculator -->
                    <div class="p-8 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 mb-6">Investment Calculator</h2>
                        <div class="bg-gray-50 rounded-lg p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Investment Amount (K)
                                    </label>
                                    <input
                                        v-model.number="calculatorAmount"
                                        type="number"
                                        :min="opportunity.minimum_investment"
                                        class="block w-full border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500"
                                        placeholder="Enter amount"
                                    />
                                    <p class="text-xs text-gray-500 mt-1">
                                        Minimum: {{ formatCurrency(opportunity.minimum_investment) }}
                                    </p>
                                </div>
                                <div class="space-y-4">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Annual Returns:</span>
                                        <span class="font-semibold">{{ formatCurrency(calculatedReturns) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Total Value (1 year):</span>
                                        <span class="font-bold text-green-600">{{ formatCurrency(calculatedTotal) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Section -->
                    <div class="p-8">
                        <div class="flex flex-col sm:flex-row gap-4">
                            <Link 
                                :href="route('investments.create', { opportunity: opportunity.id })"
                                class="flex-1 inline-flex justify-center items-center px-6 py-3 bg-primary-600 border border-transparent rounded-lg font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200"
                            >
                                <CurrencyDollarIcon class="h-5 w-5 mr-2" />
                                Invest Now
                            </Link>
                            <button
                                @click="shareOpportunity"
                                class="flex-1 inline-flex justify-center items-center px-6 py-3 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200"
                            >
                                <ShareIcon class="h-5 w-5 mr-2" />
                                Share Opportunity
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </InvestorLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import InvestorLayout from '@/layouts/InvestorLayout.vue';
import { Link } from '@inertiajs/vue3';
import { 
    ArrowLeftIcon, 
    CheckCircleIcon, 
    StarIcon, 
    CurrencyDollarIcon, 
    ShareIcon 
} from '@heroicons/vue/24/outline';

interface Opportunity {
    id: number;
    name: string;
    description: string;
    minimum_investment: number;
    expected_returns: number;
    risk_level: string;
    duration: number;
    features: string[];
    benefits: string[];
}

interface Props {
    opportunity: Opportunity;
}

const props = defineProps<Props>();

const calculatorAmount = ref(props.opportunity.minimum_investment);

const calculatedReturns = computed(() => {
    return (calculatorAmount.value * props.opportunity.expected_returns) / 100;
});

const calculatedTotal = computed(() => {
    return calculatorAmount.value + calculatedReturns.value;
});

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat().format(amount);
};

const getRiskBadgeClass = (riskLevel: string): string => {
    switch (riskLevel.toLowerCase()) {
        case 'low':
            return 'bg-green-100 text-green-800';
        case 'medium':
            return 'bg-yellow-100 text-yellow-800';
        case 'high':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const shareOpportunity = () => {
    if (navigator.share) {
        navigator.share({
            title: `${props.opportunity.name} - Investment Opportunity`,
            text: `Check out this investment opportunity: ${props.opportunity.description}`,
            url: window.location.href
        });
    } else {
        // Fallback to copying URL
        navigator.clipboard.writeText(window.location.href);
        // You could show a toast notification here
    }
};
</script>