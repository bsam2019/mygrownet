<template>
    <MemberLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Investment Opportunities
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Investment Opportunities</h1>
                    <p class="mt-2 text-gray-600">Choose from our range of investment tiers designed to maximize your returns</p>
                </div>

                <!-- Opportunities Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="opportunity in opportunities" :key="opportunity.id" 
                         class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                        <!-- Header -->
                        <div class="p-6 border-b border-gray-100">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-xl font-bold text-gray-900">{{ opportunity.name }}</h3>
                                <span 
                                    :class="getRiskBadgeClass(opportunity.risk_level)"
                                    class="px-3 py-1 rounded-full text-xs font-medium"
                                >
                                    {{ opportunity.risk_level.charAt(0).toUpperCase() + opportunity.risk_level.slice(1) }} Risk
                                </span>
                            </div>
                            <p class="text-gray-600 text-sm">{{ opportunity.description }}</p>
                        </div>

                        <!-- Key Metrics -->
                        <div class="p-6">
                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-primary-600">{{ opportunity.expected_returns }}%</div>
                                    <div class="text-sm text-gray-500">Annual Returns</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-gray-900">{{ formatCurrency(opportunity.minimum_investment) }}</div>
                                    <div class="text-sm text-gray-500">Min Investment</div>
                                </div>
                            </div>

                            <!-- Features -->
                            <div class="mb-6">
                                <h4 class="text-sm font-semibold text-gray-900 mb-3">Key Features</h4>
                                <ul class="space-y-2">
                                    <li v-for="feature in opportunity.features" :key="feature" 
                                        class="flex items-center text-sm text-gray-600">
                                        <CheckIcon class="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                                        {{ feature }}
                                    </li>
                                </ul>
                            </div>

                            <!-- Actions -->
                            <div class="space-y-3">
                                <Link 
                                    :href="route('investments.create', { opportunity: opportunity.id })"
                                    class="w-full inline-flex justify-center items-center px-4 py-3 bg-primary-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200"
                                >
                                    Invest Now
                                </Link>
                                <Link 
                                    :href="route('opportunities.show', opportunity.id)"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200"
                                >
                                    Learn More
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="!opportunities.length" class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                        <LightBulbIcon class="h-8 w-8 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No opportunities available</h3>
                    <p class="text-gray-500">Check back later for new investment opportunities.</p>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>

<script setup lang="ts">
import MemberLayout from '@/layouts/MemberLayout.vue';
import { Link } from '@inertiajs/vue3';
import { CheckIcon, LightBulbIcon } from '@heroicons/vue/24/outline';

interface Opportunity {
    id: number;
    name: string;
    description: string;
    minimum_investment: number;
    expected_returns: number;
    risk_level: string;
    duration: number;
    features: string[];
}

interface Props {
    opportunities: Opportunity[];
}

const props = defineProps<Props>();

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
</script>