<script setup>
import { Link } from '@inertiajs/vue3';
import InvestorLayout from '@/layouts/InvestorLayout.vue';
import { formatCurrency } from '@/utils/formatting';
import { ArrowLeftIcon, ChartBarIcon, ClockIcon, CurrencyDollarIcon, ExclamationTriangleIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';

defineProps({
    opportunity: {
        type: Object,
        required: true
    }
});

const getRiskLevelIcon = (riskLevel) => {
    switch(riskLevel) {
        case 'low':
            return ChartBarIcon;
        case 'medium':
            return ExclamationTriangleIcon;
        case 'high':
            return ExclamationTriangleIcon;
        default:
            return ChartBarIcon;
    }
};

const getRiskLevelColor = (riskLevel) => {
    switch(riskLevel) {
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
            <div class="flex items-center">
                <Link 
                    :href="route('opportunities')" 
                    class="mr-4 text-gray-600 hover:text-gray-900 transition-colors"
                >
                    <ArrowLeftIcon class="h-5 w-5" />
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ opportunity.name }}
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Breadcrumb -->
                <div class="mb-6 flex items-center text-sm text-gray-500">
                    <Link :href="route('dashboard')" class="hover:text-gray-700">Dashboard</Link>
                    <span class="mx-2">/</span>
                    <Link :href="route('opportunities')" class="hover:text-gray-700">Opportunities</Link>
                    <span class="mx-2">/</span>
                    <span class="text-gray-900">{{ opportunity.name }}</span>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Opportunity Details -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <!-- Main Info -->
                            <div class="lg:col-span-2">
                                <div class="prose max-w-none">
                                    <h1 class="text-2xl font-bold text-gray-900 mb-4">
                                        About this Opportunity
                                    </h1>
                                    <p class="text-gray-600 leading-relaxed">
                                        {{ opportunity.description }}
                                    </p>
                                </div>

                                <!-- Category Info -->
                                <div class="mt-8">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                                        <DocumentTextIcon class="h-5 w-5 text-gray-400 mr-2" />
                                        Category
                                    </h4>
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <p class="text-gray-700 font-medium">{{ opportunity.category.name }}</p>
                                    </div>
                                </div>

                                <!-- Risk Assessment -->
                                <div class="mt-8">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                                        <component :is="getRiskLevelIcon(opportunity.risk_level)" class="h-5 w-5 text-gray-400 mr-2" />
                                        Risk Assessment
                                    </h4>
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <div class="flex items-center">
                                            <span 
                                                :class="getRiskLevelColor(opportunity.risk_level)"
                                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                            >
                                                <component :is="getRiskLevelIcon(opportunity.risk_level)" class="h-4 w-4 mr-1" />
                                                {{ opportunity.risk_level.charAt(0).toUpperCase() + opportunity.risk_level.slice(1) }} Risk
                                            </span>
                                            <p class="ml-4 text-gray-600">
                                                <span v-if="opportunity.risk_level === 'low'">Conservative investment with stable returns</span>
                                                <span v-else-if="opportunity.risk_level === 'medium'">Balanced investment with moderate risk</span>
                                                <span v-else-if="opportunity.risk_level === 'high'">Aggressive investment with higher potential returns</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Investment Details Card -->
                            <div class="lg:col-span-1">
                                <div class="bg-gray-50 rounded-xl p-6 sticky top-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-4 border-b border-gray-200">
                                        Investment Details
                                    </h3>
                                    
                                    <div class="space-y-6">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <CurrencyDollarIcon class="h-6 w-6 text-gray-400" />
                                            </div>
                                            <div class="ml-4">
                                                <span class="text-sm text-gray-500 block">Minimum Investment</span>
                                                <span class="text-2xl font-bold text-gray-900">
                                                    {{ formatCurrency(opportunity.minimum_investment) }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <ChartBarIcon class="h-6 w-6 text-gray-400" />
                                            </div>
                                            <div class="ml-4">
                                                <span class="text-sm text-gray-500 block">Expected Returns</span>
                                                <span class="text-2xl font-bold text-green-600">
                                                    {{ opportunity.expected_returns }}%
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <ClockIcon class="h-6 w-6 text-gray-400" />
                                            </div>
                                            <div class="ml-4">
                                                <span class="text-sm text-gray-500 block">Duration</span>
                                                <span class="text-xl font-semibold text-gray-900">
                                                    {{ opportunity.duration }} months
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-8 pt-6 border-t border-gray-200">
                                        <Link
                                            :href="route('investments.create', { opportunity: opportunity.id })"
                                            class="w-full inline-flex justify-center items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-lg font-medium text-base text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200 shadow-md"
                                        >
                                            Invest Now
                                        </Link>
                                        <p class="mt-3 text-xs text-gray-500 text-center">
                                            By investing, you agree to our terms and conditions
                                        </p>
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