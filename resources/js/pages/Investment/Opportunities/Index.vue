<script setup>
import { Link } from '@inertiajs/vue3';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { formatCurrency } from '@/utils/formatting';
import { ArrowLeftIcon, ChartBarIcon, ClockIcon, CurrencyDollarIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';

defineProps({
    opportunities: {
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
    <MemberLayout>
        <template #header>
            <div class="flex items-center">
                <Link 
                    :href="route('dashboard')" 
                    class="mr-4 text-gray-600 hover:text-gray-900 transition-colors"
                >
                    <ArrowLeftIcon class="h-5 w-5" />
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Investment Opportunities
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header Section -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Available Investment Opportunities</h1>
                    <p class="mt-2 text-gray-600">Explore our curated selection of investment opportunities with varying risk levels and potential returns.</p>
                </div>

                <!-- Opportunities Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="opportunity in opportunities.data" :key="opportunity.id" 
                        class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ opportunity.name }}
                                </h3>
                                <span 
                                    :class="getRiskLevelColor(opportunity.risk_level)"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                >
                                    <component :is="getRiskLevelIcon(opportunity.risk_level)" class="h-3.5 w-3.5 mr-1" />
                                    {{ opportunity.risk_level.charAt(0).toUpperCase() + opportunity.risk_level.slice(1) }}
                                </span>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-6 line-clamp-3">
                                {{ opportunity.description }}
                            </p>
                            
                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <div class="flex items-center">
                                    <CurrencyDollarIcon class="h-5 w-5 text-gray-400 mr-2" />
                                    <div>
                                        <span class="text-xs text-gray-500 block">Min. Investment</span>
                                        <span class="text-base font-semibold text-gray-900">
                                            {{ formatCurrency(opportunity.minimum_investment) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <ChartBarIcon class="h-5 w-5 text-gray-400 mr-2" />
                                    <div>
                                        <span class="text-xs text-gray-500 block">Expected Returns</span>
                                        <span class="text-base font-semibold text-green-600">
                                            {{ opportunity.expected_returns }}%
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center mb-6">
                                <ClockIcon class="h-5 w-5 text-gray-400 mr-2" />
                                <div>
                                    <span class="text-xs text-gray-500 block">Duration</span>
                                    <span class="text-base font-medium text-gray-900">
                                        {{ opportunity.duration }} months
                                    </span>
                                </div>
                            </div>
                            
                            <Link 
                                :href="route('opportunities.show', opportunity.id)"
                                class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-indigo-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200 shadow-md"
                            >
                                View Details
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="opportunities.data.length === 0" class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                        <ChartBarIcon class="h-8 w-8 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No opportunities available</h3>
                    <p class="text-gray-500 max-w-md mx-auto">There are currently no investment opportunities available. Please check back later.</p>
                </div>

                <!-- Pagination -->
                <div class="mt-8" v-if="opportunities.links && opportunities.links.length > 3">
                    <div class="flex justify-center">
                        <Link
                            v-for="link in opportunities.links"
                            :key="link.label"
                            :href="link.url"
                            class="px-4 py-2 mx-1 rounded-md"
                            :class="{
                                'bg-primary-600 text-white': link.active,
                                'text-gray-700 hover:bg-gray-100': !link.active,
                                'opacity-50 cursor-not-allowed': !link.url
                            }"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template> 