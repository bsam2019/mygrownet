<template>
  <InvestorLayout :investor="investor" page-title="Reports" active-page="reports">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Financial Reports</h1>
      <p class="mt-1 text-gray-600">Company performance metrics and financial data</p>
    </div>

    <!-- Financial Summary Section -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
      <FinancialSummaryCard :financial-summary="financialSummary" />
      <PerformanceChart :performance-metrics="performanceMetrics" />
    </div>

    <!-- Reports List -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
            <DocumentChartBarIcon class="h-5 w-5 text-white" aria-hidden="true" />
          </div>
          <div>
            <h2 class="font-semibold text-gray-900">Published Reports</h2>
            <p class="text-sm text-gray-500">Historical financial reports and performance data</p>
          </div>
        </div>
      </div>

      <div v-if="reports.length > 0" class="divide-y divide-gray-100">
        <div
          v-for="report in reports"
          :key="report.id"
          class="p-6 hover:bg-gray-50/50 transition-colors"
        >
          <div class="flex flex-col lg:flex-row lg:items-start gap-6">
            <!-- Report Header -->
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-3">
                <h3 class="text-lg font-semibold text-gray-900">{{ report.title }}</h3>
                <span :class="getReportTypeBadgeClass(report.report_type)" class="px-2.5 py-1 text-xs font-semibold rounded-full">
                  {{ report.report_type_label }}
                </span>
              </div>
              <p class="text-sm text-gray-500 mb-4 flex items-center gap-2">
                <CalendarIcon class="h-4 w-4" aria-hidden="true" />
                {{ report.report_period_display }} • Published {{ report.report_date_formatted }}
              </p>
              
              <!-- Key Metrics Grid -->
              <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100/50 rounded-xl p-4 border border-blue-100">
                  <p class="text-xs text-blue-600 font-medium mb-1">Revenue</p>
                  <p class="text-lg font-bold text-blue-900">K{{ formatNumber(report.total_revenue) }}</p>
                </div>
                <div :class="report.net_profit >= 0 ? 'bg-gradient-to-br from-emerald-50 to-emerald-100/50 border-emerald-100' : 'bg-gradient-to-br from-red-50 to-red-100/50 border-red-100'" class="rounded-xl p-4 border">
                  <p :class="report.net_profit >= 0 ? 'text-emerald-600' : 'text-red-600'" class="text-xs font-medium mb-1">Net Profit</p>
                  <p :class="report.net_profit >= 0 ? 'text-emerald-900' : 'text-red-900'" class="text-lg font-bold">
                    K{{ formatNumber(report.net_profit) }}
                  </p>
                </div>
                <div class="bg-gradient-to-br from-gray-50 to-gray-100/50 rounded-xl p-4 border border-gray-200">
                  <p class="text-xs text-gray-600 font-medium mb-1">Profit Margin</p>
                  <p class="text-lg font-bold text-gray-900">{{ report.profit_margin.toFixed(1) }}%</p>
                </div>
                <div class="bg-gradient-to-br from-violet-50 to-violet-100/50 rounded-xl p-4 border border-violet-100">
                  <p class="text-xs text-violet-600 font-medium mb-1">Health Score</p>
                  <div class="flex items-center gap-2">
                    <p class="text-lg font-bold text-violet-900">{{ report.financial_health_score }}</p>
                    <span class="text-xs text-violet-600">/100</span>
                  </div>
                </div>
              </div>

              <!-- Notes -->
              <div v-if="report.notes" class="mt-4 p-4 bg-gray-50 rounded-xl border border-gray-100">
                <p class="text-sm text-gray-700">{{ report.notes }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="p-12 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <DocumentChartBarIcon class="h-8 w-8 text-gray-400" aria-hidden="true" />
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No Reports Available</h3>
        <p class="text-gray-500 mb-6">
          Financial reports will be published here quarterly. Check back soon for updates.
        </p>
        <Link
          :href="route('investor.dashboard')"
          class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-colors"
        >
          Return to Dashboard
        </Link>
      </div>
    </div>
  </InvestorLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import InvestorLayout from '@/layouts/InvestorLayout.vue';
import FinancialSummaryCard from '@/components/Investor/FinancialSummaryCard.vue';
import PerformanceChart from '@/components/Investor/PerformanceChart.vue';
import { DocumentChartBarIcon, CalendarIcon } from '@heroicons/vue/24/outline';

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
  profit_margin: number;
  financial_health_score: number;
  notes: string | null;
}

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
  revenue_breakdown: Array<{ source: string; amount: number; percentage: number }>;
}

interface PerformanceMetrics {
  revenue_trend: { labels: string[]; data: number[] };
  profit_trend: { labels: string[]; data: number[] };
  member_growth: { labels: string[]; data: number[] };
  health_score_trend: { labels: string[]; data: number[] };
}

interface Investor {
  id: number;
  name: string;
  email: string;
}

const props = defineProps<{
  investor: Investor;
  reports: FinancialReport[];
  financialSummary: FinancialSummary | null;
  performanceMetrics: PerformanceMetrics | null;
}>();

const formatNumber = (value: number): string => {
  return new Intl.NumberFormat('en-US').format(value);
};

const getReportTypeBadgeClass = (type: string): string => {
  const classes: Record<string, string> = {
    monthly: 'bg-blue-100 text-blue-700',
    quarterly: 'bg-emerald-100 text-emerald-700',
    annual: 'bg-violet-100 text-violet-700',
  };
  return classes[type] || 'bg-gray-100 text-gray-700';
};
</script>
