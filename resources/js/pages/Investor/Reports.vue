<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex justify-between items-center">
          <div>
            <div class="flex items-center gap-3">
              <Link
                :href="route('investor.dashboard')"
                class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
              >
                <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
              </Link>
              <div>
                <h1 class="text-xl font-bold text-gray-900">Financial Reports</h1>
                <p class="text-sm text-gray-600">Company performance and financial data</p>
              </div>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <Link
              :href="route('investor.dashboard')"
              class="inline-flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
            >
              Dashboard
            </Link>
            <Link
              :href="route('investor.logout')"
              method="post"
              as="button"
              class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
            >
              Logout
            </Link>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Financial Summary Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <FinancialSummaryCard :financial-summary="financialSummary" />
        <PerformanceChart :performance-metrics="performanceMetrics" />
      </div>

      <!-- Reports List -->
      <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Published Reports</h2>
          <p class="text-sm text-gray-600">Historical financial reports and performance data</p>
        </div>

        <div v-if="reports.length > 0" class="divide-y divide-gray-200">
          <div
            v-for="report in reports"
            :key="report.id"
            class="p-6 hover:bg-gray-50 transition-colors"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                  <h3 class="text-lg font-semibold text-gray-900">{{ report.title }}</h3>
                  <span :class="getReportTypeClass(report.report_type)" class="px-2 py-1 text-xs font-medium rounded-full">
                    {{ report.report_type_label }}
                  </span>
                </div>
                <p class="text-sm text-gray-600 mb-4">
                  {{ report.report_period_display }} • Published {{ report.report_date_formatted }}
                </p>
                
                <!-- Key Metrics -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                  <div class="bg-blue-50 rounded-lg p-3">
                    <p class="text-xs text-blue-600 font-medium">Revenue</p>
                    <p class="text-lg font-bold text-blue-900">K{{ formatNumber(report.total_revenue) }}</p>
                  </div>
                  <div :class="report.net_profit >= 0 ? 'bg-green-50' : 'bg-red-50'" class="rounded-lg p-3">
                    <p :class="report.net_profit >= 0 ? 'text-green-600' : 'text-red-600'" class="text-xs font-medium">Net Profit</p>
                    <p :class="report.net_profit >= 0 ? 'text-green-900' : 'text-red-900'" class="text-lg font-bold">
                      K{{ formatNumber(report.net_profit) }}
                    </p>
                  </div>
                  <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-600 font-medium">Profit Margin</p>
                    <p class="text-lg font-bold text-gray-900">{{ report.profit_margin.toFixed(1) }}%</p>
                  </div>
                  <div class="bg-purple-50 rounded-lg p-3">
                    <p class="text-xs text-purple-600 font-medium">Health Score</p>
                    <p class="text-lg font-bold text-purple-900">{{ report.financial_health_score }}/100</p>
                  </div>
                </div>

                <!-- Additional Details -->
                <div v-if="report.notes" class="mt-4 p-3 bg-gray-50 rounded-lg">
                  <p class="text-sm text-gray-700">{{ report.notes }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="p-12 text-center">
          <DocumentChartBarIcon class="h-16 w-16 mx-auto mb-4 text-gray-400" aria-hidden="true" />
          <h3 class="text-lg font-semibold text-gray-900 mb-2">No Reports Available</h3>
          <p class="text-gray-600 mb-4">
            Financial reports will be published here quarterly. Check back soon for updates.
          </p>
          <Link
            :href="route('investor.dashboard')"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            Return to Dashboard
          </Link>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ArrowLeftIcon, DocumentChartBarIcon } from '@heroicons/vue/24/outline';
import FinancialSummaryCard from '@/components/Investor/FinancialSummaryCard.vue';
import PerformanceChart from '@/components/Investor/PerformanceChart.vue';

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
  health_score_label: string;
  health_score_color: string;
  growth_rate: number | null;
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
  revenue_breakdown: Array<{
    source: string;
    amount: number;
    percentage: number;
  }>;
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
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value);
};

const getReportTypeClass = (type: string): string => {
  const classes = {
    monthly: 'bg-blue-100 text-blue-800',
    quarterly: 'bg-green-100 text-green-800',
    annual: 'bg-purple-100 text-purple-800',
  };
  return classes[type as keyof typeof classes] || 'bg-gray-100 text-gray-800';
};
</script>
