<template>
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow duration-300">
    <div class="p-6">
      <!-- Header -->
      <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center">
          <ChartBarIcon class="h-5 w-5 text-white" aria-hidden="true" />
        </div>
        <div>
          <h3 class="font-semibold text-gray-900">Financial Performance</h3>
          <p class="text-xs text-gray-500">Company financials overview</p>
        </div>
      </div>
      
      <div v-if="financialSummary && financialSummary.latest_period !== 'No data'" class="space-y-5">
        <!-- Reporting Period Badge -->
        <div class="flex items-center justify-between">
          <span class="text-sm text-gray-600">Latest Report</span>
          <span class="px-3 py-1 bg-blue-50 text-blue-700 text-xs font-semibold rounded-full">
            {{ financialSummary.latest_period_display }}
          </span>
        </div>
        
        <!-- Key Metrics -->
        <div class="grid grid-cols-2 gap-3">
          <div class="bg-gradient-to-br from-blue-50 to-blue-100/50 rounded-xl p-4 border border-blue-100">
            <div class="flex items-center gap-2 mb-2">
              <BanknotesIcon class="h-4 w-4 text-blue-600" aria-hidden="true" />
              <p class="text-xs text-blue-600 font-medium">Total Revenue</p>
            </div>
            <p class="text-xl font-bold text-blue-900">K{{ formatNumber(financialSummary.total_revenue) }}</p>
          </div>
          <div class="bg-gradient-to-br from-emerald-50 to-emerald-100/50 rounded-xl p-4 border border-emerald-100">
            <div class="flex items-center gap-2 mb-2">
              <ArrowTrendingUpIcon class="h-4 w-4 text-emerald-600" aria-hidden="true" />
              <p class="text-xs text-emerald-600 font-medium">Net Profit</p>
            </div>
            <p class="text-xl font-bold text-emerald-900">K{{ formatNumber(financialSummary.net_profit) }}</p>
          </div>
        </div>
        
        <!-- Performance Indicators -->
        <div class="grid grid-cols-2 gap-4 py-4 border-y border-gray-100">
          <div>
            <p class="text-xs text-gray-500 mb-1">Profit Margin</p>
            <div class="flex items-center gap-2">
              <p class="text-lg font-bold text-gray-900">{{ financialSummary.profit_margin.toFixed(1) }}%</p>
              <span class="text-xs text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded">Good</span>
            </div>
          </div>
          <div>
            <p class="text-xs text-gray-500 mb-1">Growth Rate</p>
            <div class="flex items-center gap-2">
              <p class="text-lg font-bold" :class="getGrowthRateColor(financialSummary.growth_rate)">
                {{ financialSummary.growth_rate ? (financialSummary.growth_rate >= 0 ? '+' : '') + financialSummary.growth_rate.toFixed(1) + '%' : 'N/A' }}
              </p>
              <component 
                :is="financialSummary.growth_rate && financialSummary.growth_rate >= 0 ? ArrowTrendingUpIcon : ArrowTrendingDownIcon"
                class="h-4 w-4"
                :class="getGrowthRateColor(financialSummary.growth_rate)"
                aria-hidden="true"
              />
            </div>
          </div>
        </div>
        
        <!-- Financial Health Score -->
        <div class="bg-gray-50 rounded-xl p-4">
          <div class="flex justify-between items-center mb-3">
            <div class="flex items-center gap-2">
              <HeartIcon class="h-4 w-4 text-gray-600" aria-hidden="true" />
              <span class="text-sm font-medium text-gray-700">Financial Health</span>
            </div>
            <span class="text-lg font-bold text-gray-900">{{ financialSummary.health_score }}<span class="text-sm text-gray-500">/100</span></span>
          </div>
          <div class="relative h-2.5 bg-gray-200 rounded-full overflow-hidden">
            <div 
              class="absolute inset-y-0 left-0 rounded-full transition-all duration-700 ease-out"
              :class="getHealthScoreBarColor(financialSummary.health_score_color)"
              :style="{ width: `${financialSummary.health_score}%` }"
            ></div>
          </div>
          <div class="flex justify-between items-center mt-2">
            <span 
              class="text-xs font-medium px-2 py-0.5 rounded-full"
              :class="getHealthScoreBadgeClass(financialSummary.health_score_color)"
            >
              {{ financialSummary.health_score_label }}
            </span>
            <span class="text-xs text-gray-500">
              {{ financialSummary.report_date_formatted }}
            </span>
          </div>
        </div>

        <!-- Revenue Breakdown -->
        <div v-if="financialSummary.revenue_breakdown && financialSummary.revenue_breakdown.length > 0">
          <p class="text-xs text-gray-500 font-medium mb-3 uppercase tracking-wide">Revenue Sources</p>
          <div class="space-y-2">
            <div 
              v-for="source in financialSummary.revenue_breakdown.slice(0, 4)" 
              :key="source.source" 
              class="flex items-center gap-3"
            >
              <div class="w-2 h-2 rounded-full flex-shrink-0" :style="{ backgroundColor: getRevenueSourceColor(source.source) }"></div>
              <span class="text-sm text-gray-700 flex-1">{{ formatRevenueSource(source.source) }}</span>
              <div class="flex items-center gap-2">
                <div class="w-16 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                  <div 
                    class="h-full rounded-full transition-all duration-500"
                    :style="{ width: `${source.percentage}%`, backgroundColor: getRevenueSourceColor(source.source) }"
                  ></div>
                </div>
                <span class="text-xs font-semibold text-gray-900 w-10 text-right">{{ source.percentage }}%</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Empty State -->
      <div v-else class="text-center py-10">
        <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <ChartBarIcon class="h-8 w-8 text-gray-400" aria-hidden="true" />
        </div>
        <p class="text-sm font-medium text-gray-900 mb-1">No Financial Data Yet</p>
        <p class="text-xs text-gray-500">Financial data will be available after the first quarterly report</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { 
  ChartBarIcon, 
  BanknotesIcon, 
  ArrowTrendingUpIcon, 
  ArrowTrendingDownIcon,
  HeartIcon,
} from '@heroicons/vue/24/outline';

interface FinancialSummary {
  latest_period: string;
  latest_period_display: string;
  total_revenue: number;
  total_expenses: number;
  net_profit: number;
  profit_margin: number;
  growth_rate: number | null;
  growth_status: string;
  health_score: number;
  health_score_label: string;
  health_score_color: string;
  report_date_formatted: string;
  revenue_breakdown: Array<{
    source: string;
    amount: number;
    percentage: number;
  }>;
}

defineProps<{
  financialSummary: FinancialSummary | null;
}>();

const formatNumber = (value: number): string => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value);
};

const getGrowthRateColor = (rate: number | null): string => {
  if (!rate) return 'text-gray-500';
  if (rate > 0) return 'text-emerald-600';
  return 'text-red-600';
};

const getHealthScoreBarColor = (color: string): string => {
  const colors: Record<string, string> = {
    green: 'bg-gradient-to-r from-emerald-400 to-emerald-500',
    yellow: 'bg-gradient-to-r from-yellow-400 to-yellow-500',
    orange: 'bg-gradient-to-r from-orange-400 to-orange-500',
    red: 'bg-gradient-to-r from-red-400 to-red-500',
  };
  return colors[color] || 'bg-gray-400';
};

const getHealthScoreBadgeClass = (color: string): string => {
  const colors: Record<string, string> = {
    green: 'bg-emerald-100 text-emerald-700',
    yellow: 'bg-yellow-100 text-yellow-700',
    orange: 'bg-orange-100 text-orange-700',
    red: 'bg-red-100 text-red-700',
  };
  return colors[color] || 'bg-gray-100 text-gray-700';
};

const getRevenueSourceColor = (source: string): string => {
  const colors: Record<string, string> = {
    'subscription_fees': '#3b82f6',
    'learning_packs': '#10b981',
    'workshops_training': '#f59e0b',
    'venture_builder_fees': '#8b5cf6',
    'other_services': '#6b7280',
  };
  return colors[source] || '#6b7280';
};

const formatRevenueSource = (source: string): string => {
  const labels: Record<string, string> = {
    'subscription_fees': 'Subscriptions',
    'learning_packs': 'Learning Packs',
    'workshops_training': 'Workshops',
    'venture_builder_fees': 'Venture Builder',
    'other_services': 'Other',
  };
  return labels[source] || source.replace('_', ' ');
};
</script>
