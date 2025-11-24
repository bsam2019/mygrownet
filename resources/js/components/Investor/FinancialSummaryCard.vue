<template>
  <div class="bg-white rounded-xl shadow-lg p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
      <ChartBarIcon class="h-5 w-5 mr-2 text-blue-600" aria-hidden="true" />
      Company Financial Performance
    </h3>
    
    <div v-if="financialSummary && financialSummary.latest_period !== 'No data'" class="space-y-4">
      <!-- Reporting Period -->
      <div class="flex justify-between items-center pb-3 border-b border-gray-200">
        <span class="text-sm text-gray-600">Latest Report</span>
        <span class="font-semibold text-gray-900">{{ financialSummary.latest_period_display }}</span>
      </div>
      
      <!-- Key Metrics Grid -->
      <div class="grid grid-cols-2 gap-4">
        <div class="bg-blue-50 rounded-lg p-3">
          <p class="text-xs text-blue-600 font-medium">Total Revenue</p>
          <p class="text-lg font-bold text-blue-900">K{{ formatNumber(financialSummary.total_revenue) }}</p>
        </div>
        <div class="bg-green-50 rounded-lg p-3">
          <p class="text-xs text-green-600 font-medium">Net Profit</p>
          <p class="text-lg font-bold text-green-900">K{{ formatNumber(financialSummary.net_profit) }}</p>
        </div>
      </div>
      
      <!-- Performance Indicators -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <p class="text-xs text-gray-500">Profit Margin</p>
          <p class="text-sm font-semibold text-gray-900">{{ financialSummary.profit_margin.toFixed(1) }}%</p>
        </div>
        <div>
          <p class="text-xs text-gray-500">Growth Rate</p>
          <p class="text-sm font-semibold" :class="getGrowthRateColor(financialSummary.growth_rate)">
            {{ financialSummary.growth_rate ? (financialSummary.growth_rate >= 0 ? '+' : '') + financialSummary.growth_rate.toFixed(1) + '%' : 'N/A' }}
          </p>
        </div>
      </div>
      
      <!-- Financial Health Score -->
      <div class="bg-gray-50 rounded-lg p-4">
        <div class="flex justify-between items-center mb-2">
          <span class="text-xs text-gray-600 font-medium">Financial Health Score</span>
          <span class="text-sm font-bold text-gray-900">{{ financialSummary.health_score }}/100</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
          <div 
            class="h-2 rounded-full transition-all duration-500"
            :class="getHealthScoreBarColor(financialSummary.health_score_color)"
            :style="{ width: `${financialSummary.health_score}%` }"
          ></div>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-xs" :class="getHealthScoreTextColor(financialSummary.health_score_color)">
            {{ financialSummary.health_score_label }}
          </span>
          <span class="text-xs text-gray-500">
            {{ financialSummary.report_date_formatted }}
          </span>
        </div>
      </div>

      <!-- Revenue Breakdown -->
      <div v-if="financialSummary.revenue_breakdown && financialSummary.revenue_breakdown.length > 0" class="pt-4 border-t border-gray-200">
        <p class="text-xs text-gray-600 font-medium mb-3">Revenue Sources</p>
        <div class="space-y-2">
          <div v-for="source in financialSummary.revenue_breakdown.slice(0, 3)" :key="source.source" class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="w-2 h-2 rounded-full mr-2" :style="{ backgroundColor: getRevenueSourceColor(source.source) }"></div>
              <span class="text-xs text-gray-700">{{ formatRevenueSource(source.source) }}</span>
            </div>
            <span class="text-xs font-medium text-gray-900">{{ source.percentage }}%</span>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Empty State -->
    <div v-else class="text-center py-6 text-gray-500">
      <ChartBarIcon class="h-12 w-12 mx-auto mb-2 text-gray-400" aria-hidden="true" />
      <p class="text-sm">Financial data will be available after the first quarterly report</p>
      <p class="text-xs mt-1">Reports are published quarterly</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ChartBarIcon } from '@heroicons/vue/24/outline';

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

const props = defineProps<{
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
  if (rate > 10) return 'text-green-600';
  if (rate > 5) return 'text-green-500';
  if (rate > 0) return 'text-yellow-600';
  return 'text-red-600';
};

const getHealthScoreBarColor = (color: string): string => {
  const colors = {
    green: 'bg-green-500',
    yellow: 'bg-yellow-500',
    orange: 'bg-orange-500',
    red: 'bg-red-500',
  };
  return colors[color] || 'bg-gray-500';
};

const getHealthScoreTextColor = (color: string): string => {
  const colors = {
    green: 'text-green-600',
    yellow: 'text-yellow-600',
    orange: 'text-orange-600',
    red: 'text-red-600',
  };
  return colors[color] || 'text-gray-600';
};

const getRevenueSourceColor = (source: string): string => {
  const colors = {
    'subscription_fees': '#3b82f6',
    'learning_packs': '#10b981',
    'workshops_training': '#f59e0b',
    'venture_builder_fees': '#8b5cf6',
    'other_services': '#6b7280',
  };
  return colors[source] || '#6b7280';
};

const formatRevenueSource = (source: string): string => {
  const labels = {
    'subscription_fees': 'Subscriptions',
    'learning_packs': 'Learning Packs',
    'workshops_training': 'Workshops',
    'venture_builder_fees': 'Venture Builder',
    'other_services': 'Other',
  };
  return labels[source] || source.replace('_', ' ');
};
</script>