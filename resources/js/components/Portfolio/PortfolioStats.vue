<script setup lang="ts">
import { computed } from 'vue';
import { BanknotesIcon, ArrowTrendingUpIcon, ChartBarIcon, CalendarIcon } from '@heroicons/vue/24/outline';
import { formatCurrency } from '@/utils/formatting';

interface Portfolio {
    total_investment: number;
    total_returns: number;
    active_referrals: number;
    referral_earnings: number;
}

interface Props {
    portfolio: Portfolio;
    investments?: {
        data: any[];
        meta: {
            next_payment_date?: string;
        };
    };
}

const props = withDefaults(defineProps<Props>(), {
    investments: () => ({
        data: [],
        meta: {
            next_payment_date: undefined
        }
    })
});

const activeInvestmentsCount = computed(() => props.investments?.data?.length || 0);
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Investment -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="p-3 bg-primary-50 rounded-lg">
                        <BanknotesIcon class="h-6 w-6 text-primary-600" />
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Invested</p>
                    <p class="text-2xl font-bold text-gray-900">
                        {{ formatCurrency(portfolio.total_investment) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Total Returns -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="p-3 bg-green-50 rounded-lg">
                        <ArrowTrendingUpIcon class="h-6 w-6 text-green-600" />
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Returns</p>
                    <p class="text-2xl font-bold text-green-600">
                        {{ formatCurrency(portfolio.total_returns) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Active Investments -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="p-3 bg-blue-50 rounded-lg">
                        <ChartBarIcon class="h-6 w-6 text-blue-600" />
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Active Investments</p>
                    <p class="text-2xl font-bold text-gray-900">
                        {{ activeInvestmentsCount }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Next Payment -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="p-3 bg-purple-50 rounded-lg">
                        <CalendarIcon class="h-6 w-6 text-purple-600" />
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Next Payment</p>
                    <p class="text-2xl font-bold text-gray-900">
                        {{ investments?.meta?.next_payment_date || 'N/A' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template> 