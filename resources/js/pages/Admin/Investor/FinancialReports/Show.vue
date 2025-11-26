<template>
  <div class="min-h-screen bg-gray-50">
    <CustomAdminSidebar />
    
    <div class="lg:pl-64">
      <div class="p-6">
        <!-- Header -->
        <div class="mb-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
              <Link
                :href="route('admin.financial-reports.index')"
                class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
              >
                <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
              </Link>
              <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ report.title }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ report.report_period_display }} • {{ report.report_date_formatted }}</p>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <span :class="getStatusClass(report.is_published)">
                {{ report.is_published ? 'Published' : 'Draft' }}
              </span>
              <Link
                :href="route('admin.financial-reports.edit', report.id)"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
              >
                <PencilIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                Edit Report
              </Link>
            </div>
          </div>
        </div>

        <!-- Financial Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
          <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500 mb-1">Total Revenue</p>
            <p class="text-2xl font-bold text-gray-900">K{{ formatNumber(report.total_revenue) }}</p>
          </div>
          <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500 mb-1">Total Expenses</p>
            <p class="text-2xl font-bold text-gray-900">K{{ formatNumber(report.total_expenses) }}</p>
          </div>
          <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500 mb-1">Net Profit</p>
            <p class="text-2xl font-bold" :class="report.net_profit >= 0 ? 'text-green-600' : 'text-red-600'">
              K{{ formatNumber(report.net_profit) }}
            </p>
          </div>
          <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500 mb-1">Health Score</p>
            <div class="flex items-center gap-2">
              <p class="text-2xl font-bold text-gray-900">{{ report.financial_health_score }}</p>
              <span :class="getHealthScoreClass(report.health_score_color)" class="px-2 py-1 text-xs font-medium rounded-full">
                {{ report.health_score_label }}
              </span>
            </div>
          </div>
        </div>

        <!-- Detailed Metrics -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
          <!-- Financial Metrics -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Financial Metrics</h3>
            <div class="space-y-4">
              <div class="flex justify-between items-center py-2 border-b border-gray-100">
                <span class="text-gray-600">Profit Margin</span>
                <span class="font-semibold text-gray-900">{{ report.profit_margin.toFixed(1) }}%</span>
              </div>
              <div v-if="report.gross_margin" class="flex justify-between items-center py-2 border-b border-gray-100">
                <span class="text-gray-600">Gross Margin</span>
                <span class="font-semibold text-gray-900">{{ report.gross_margin.toFixed(1) }}%</span>
              </div>
              <div v-if="report.operating_margin" class="flex justify-between items-center py-2 border-b border-gray-100">
                <span class="text-gray-600">Operating Margin</span>
                <span class="font-semibold text-gray-900">{{ report.operating_margin.toFixed(1) }}%</span>
              </div>
              <div v-if="report.cash_flow !== null" class="flex justify-between items-center py-2 border-b border-gray-100">
                <span class="text-gray-600">Cash Flow</span>
                <span class="font-semibold" :class="report.cash_flow >= 0 ? 'text-green-600' : 'text-red-600'">
                  K{{ formatNumber(report.cash_flow) }}
                </span>
              </div>
              <div v-if="report.growth_rate !== null" class="flex justify-between items-center py-2">
                <span class="text-gray-600">Growth Rate</span>
                <span class="font-semibold" :class="report.growth_rate >= 0 ? 'text-green-600' : 'text-red-600'">
                  {{ report.growth_rate >= 0 ? '+' : '' }}{{ report.growth_rate.toFixed(1) }}%
                </span>
              </div>
            </div>
          </div>

          <!-- Platform Metrics -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Platform Metrics</h3>
            <div class="space-y-4">
              <div v-if="report.total_members" class="flex justify-between items-center py-2 border-b border-gray-100">
                <span class="text-gray-600">Total Members</span>
                <span class="font-semibold text-gray-900">{{ formatNumber(report.total_members) }}</span>
              </div>
              <div v-if="report.active_members" class="flex justify-between items-center py-2 border-b border-gray-100">
                <span class="text-gray-600">Active Members</span>
                <span class="font-semibold text-gray-900">{{ formatNumber(report.active_members) }}</span>
              </div>
              <div v-if="report.monthly_recurring_revenue" class="flex justify-between items-center py-2 border-b border-gray-100">
                <span class="text-gray-600">Monthly Recurring Revenue</span>
                <span class="font-semibold text-gray-900">K{{ formatNumber(report.monthly_recurring_revenue) }}</span>
              </div>
              <div v-if="report.churn_rate !== null" class="flex justify-between items-center py-2 border-b border-gray-100">
                <span class="text-gray-600">Churn Rate</span>
                <span class="font-semibold text-gray-900">{{ report.churn_rate.toFixed(1) }}%</span>
              </div>
              <div v-if="report.customer_acquisition_cost" class="flex justify-between items-center py-2 border-b border-gray-100">
                <span class="text-gray-600">Customer Acquisition Cost</span>
                <span class="font-semibold text-gray-900">K{{ formatNumber(report.customer_acquisition_cost) }}</span>
              </div>
              <div v-if="report.lifetime_value" class="flex justify-between items-center py-2">
                <span class="text-gray-600">Lifetime Value</span>
                <span class="font-semibold text-gray-900">K{{ formatNumber(report.lifetime_value) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div v-if="report.notes" class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Notes</h3>
          <p class="text-gray-700 whitespace-pre-wrap">{{ report.notes }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import CustomAdminSidebar from '@/components/CustomAdminSidebar.vue';
import { ArrowLeftIcon, PencilIcon } from '@heroicons/vue/24/outline';

interface FinancialReport {
  id: number;
  title: string;
  report_type: string;
  report_type_label: string;
  report_period: string;
  report_period_display: string;
  report_date: string;
  report_date_formatted: string;
  total_revenue: number;
  total_expenses: number;
  net_profit: number;
  gross_margin: number | null;
  operating_margin: number | null;
  net_margin: number | null;
  profit_margin: number;
  cash_flow: number | null;
  total_members: number | null;
  active_members: number | null;
  monthly_recurring_revenue: number | null;
  customer_acquisition_cost: number | null;
  lifetime_value: number | null;
  churn_rate: number | null;
  growth_rate: number | null;
  notes: string | null;
  is_published: boolean;
  financial_health_score: number;
  health_score_label: string;
  health_score_color: string;
}

const props = defineProps<{
  report: FinancialReport;
}>();

const formatNumber = (value: number): string => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value);
};

const getStatusClass = (isPublished: boolean): string => {
  return isPublished
    ? 'px-3 py-1 text-sm font-medium rounded-full bg-green-100 text-green-800'
    : 'px-3 py-1 text-sm font-medium rounded-full bg-yellow-100 text-yellow-800';
};

const getHealthScoreClass = (color: string): string => {
  const classes = {
    green: 'bg-green-100 text-green-800',
    yellow: 'bg-yellow-100 text-yellow-800',
    orange: 'bg-orange-100 text-orange-800',
    red: 'bg-red-100 text-red-800',
  };
  return classes[color as keyof typeof classes] || 'bg-gray-100 text-gray-800';
};
</script>
