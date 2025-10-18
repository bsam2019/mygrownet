<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
          <div class="p-2 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg mr-3">
            <BanknotesIcon class="h-6 w-6 text-white" />
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Investment Overview</h3>
            <p class="text-sm text-gray-500">Your VBIF investment portfolio</p>
          </div>
        </div>
        <Link 
          :href="route('investments.index')" 
          class="text-sm text-primary-600 hover:text-primary-700 font-medium flex items-center"
        >
          View All
          <ArrowRightIcon class="h-4 w-4 ml-1" />
        </Link>
      </div>

      <!-- Investment Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm opacity-80">Total Investment</p>
              <p class="text-2xl font-bold mt-1">{{ formatCurrency(overview.total_investment) }}</p>
            </div>
            <div class="p-2 bg-white/10 rounded-lg">
              <CurrencyDollarIcon class="h-6 w-6" />
            </div>
          </div>
          <p class="text-sm mt-2 opacity-80">
            Across {{ overview.active_investments }} active investments
          </p>
        </div>

        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 text-white">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm opacity-80">Current Value</p>
              <p class="text-2xl font-bold mt-1">{{ formatCurrency(overview.current_value) }}</p>
            </div>
            <div class="p-2 bg-white/10 rounded-lg">
              <ArrowTrendingUpIcon class="h-6 w-6" />
            </div>
          </div>
          <p class="text-sm mt-2 opacity-80">
            <span :class="overview.value_change >= 0 ? 'text-green-300' : 'text-red-300'">
              {{ overview.value_change >= 0 ? '↑' : '↓' }} {{ formatPercentage(Math.abs(overview.value_change)) }}
            </span>
            this month
          </p>
        </div>

        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-4 text-white">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm opacity-80">Total Earnings</p>
              <p class="text-2xl font-bold mt-1">{{ formatCurrency(overview.total_earnings) }}</p>
            </div>
            <div class="p-2 bg-white/10 rounded-lg">
              <ChartBarIcon class="h-6 w-6" />
            </div>
          </div>
          <p class="text-sm mt-2 opacity-80">
            {{ formatPercentage(overview.roi) }} ROI
          </p>
        </div>

        <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg p-4 text-white">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm opacity-80">Current Tier</p>
              <p class="text-xl font-bold mt-1">{{ tierInfo?.current_tier?.name || 'No Tier' }}</p>
            </div>
            <div class="p-2 bg-white/10 rounded-lg">
              <StarIcon class="h-6 w-6" />
            </div>
          </div>
          <p class="text-sm mt-2 opacity-80">
            {{ formatPercentage(tierInfo?.current_tier?.fixed_profit_rate || 0) }} profit rate
          </p>
        </div>
      </div>

      <!-- Tier Progress Section -->
      <div v-if="tierInfo?.next_tier" class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg border border-blue-200">
        <div class="flex items-center justify-between mb-3">
          <h4 class="text-sm font-medium text-gray-900">Tier Upgrade Progress</h4>
          <span class="text-sm font-medium text-blue-600">{{ Math.round(tierProgress) }}%</span>
        </div>
        
        <div class="w-full bg-gray-200 rounded-full h-3 mb-3">
          <div 
            class="bg-gradient-to-r from-blue-500 to-purple-600 h-3 rounded-full transition-all duration-500" 
            :style="{ width: tierProgress + '%' }"
          ></div>
        </div>
        
        <div class="flex justify-between items-center">
          <div class="text-sm text-gray-600">
            Need {{ formatCurrency(tierInfo.remaining_amount) }} more to reach {{ tierInfo.next_tier.name }}
          </div>
          <Link 
            :href="route('investments.tier-upgrade')" 
            class="text-sm bg-blue-600 text-white px-3 py-1 rounded-lg hover:bg-blue-700 transition-colors"
          >
            Upgrade Now
          </Link>
        </div>
      </div>

      <!-- Investment Distribution -->
      <div v-if="investmentDistribution?.length > 0">
        <h4 class="text-sm font-medium text-gray-700 mb-4">Investment Distribution by Tier</h4>
        <div class="space-y-3">
          <div v-for="tier in investmentDistribution" :key="tier.tier_name" class="relative">
            <div class="flex justify-between items-center mb-1">
              <div class="flex items-center">
                <span class="text-sm font-medium text-gray-600">{{ tier.tier_name }}</span>
                <span class="ml-2 text-xs text-gray-500">({{ formatPercentage(tier.profit_rate) }} rate)</span>
              </div>
              <span class="text-sm font-medium text-gray-900">{{ formatCurrency(tier.amount) }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div 
                class="h-2 rounded-full transition-all duration-300"
                :style="{ 
                  width: tier.percentage + '%', 
                  backgroundColor: getTierColor(tier.tier_name) 
                }"
              ></div>
            </div>
            <div class="flex justify-between mt-1">
              <span class="text-xs text-gray-500">{{ formatPercentage(tier.percentage) }} of portfolio</span>
              <span class="text-xs text-green-600">+{{ formatCurrency(tier.expected_annual_return) }}/year</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { 
  BanknotesIcon,
  CurrencyDollarIcon,
  ArrowTrendingUpIcon,
  ChartBarIcon,
  StarIcon,
  ArrowRightIcon
} from '@heroicons/vue/24/outline';
import { formatCurrency, formatPercentage } from '@/utils/formatting';

interface InvestmentOverview {
  total_investment: number;
  current_value: number;
  total_earnings: number;
  roi: number;
  value_change: number;
  active_investments: number;
}

interface TierInfo {
  current_tier?: {
    name: string;
    fixed_profit_rate: number;
  };
  next_tier?: {
    name: string;
  };
  remaining_amount: number;
}

interface InvestmentDistribution {
  tier_name: string;
  amount: number;
  percentage: number;
  profit_rate: number;
  expected_annual_return: number;
}

interface Props {
  overview: InvestmentOverview;
  tierInfo?: TierInfo;
  tierProgress: number;
  investmentDistribution?: InvestmentDistribution[];
}

const props = withDefaults(defineProps<Props>(), {
  overview: () => ({
    total_investment: 0,
    current_value: 0,
    total_earnings: 0,
    roi: 0,
    value_change: 0,
    active_investments: 0,
  }),
  tierProgress: 0,
  investmentDistribution: () => [],
});

const getTierColor = (tierName: string): string => {
  const colors: Record<string, string> = {
    'Basic': '#6b7280',
    'Starter': '#3b82f6',
    'Builder': '#2563eb',
    'Leader': '#1d4ed8',
    'Elite': '#4f46e5',
  };
  return colors[tierName] || '#6b7280';
};
</script>