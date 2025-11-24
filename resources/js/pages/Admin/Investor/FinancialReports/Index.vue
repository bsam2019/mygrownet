<template>
  <div class="min-h-screen bg-gray-50">
    <CustomAdminSidebar />
    
    <div class="lg:pl-64">
      <div class="p-6">
        <!-- Header -->
        <div class="mb-6">
          <div class="flex justify-between items-center">
            <div>
              <h1 class="text-2xl font-bold text-gray-900">Financial Reports</h1>
              <p class="mt-1 text-sm text-gray-500">Manage quarterly and annual financial reports for investors</p>
            </div>
            <Link
              :href="route('admin.financial-reports.create')"
              class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
              Create Report
            </Link>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                <DocumentTextIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Reports</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.total_reports }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                <CheckCircleIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Published</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.published_reports }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                <ClockIcon class="h-6 w-6 text-yellow-600" aria-hidden="true" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Drafts</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.draft_reports }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                <TrendingUpIcon class="h-6 w-6 text-purple-600" aria-hidden="true" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Health Score</p>
                <p class="text-2xl font-bold text-gray-900">
                  {{ stats.latest_report?.financial_health_score || 'N/A' }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Reports Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Report</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Revenue</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Profit</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Health Score</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="report in reports" :key="report.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ report.title }}</div>
                    <div class="text-sm text-gray-500">{{ report.report_type_label }}</div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ report.report_period_display }}</div>
                  <div class="text-sm text-gray-500">{{ formatDate(report.report_date) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  K{{ formatNumber(report.total_revenue) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">K{{ formatNumber(report.net_profit) }}</div>
                  <div class="text-sm" :class="report.net_profit >= 0 ? 'text-green-600' : 'text-red-600'">
                    {{ report.profit_margin.toFixed(1) }}% margin
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="text-sm font-medium text-gray-900">{{ report.financial_health_score }}</div>
                    <div class="ml-2 px-2 py-1 text-xs rounded-full" :class="getHealthScoreClass(report.health_score_color)">
                      {{ report.health_score_label }}
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClass(report.is_published)">
                    {{ report.is_published ? 'Published' : 'Draft' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex justify-end gap-2">
                    <Link
                      :href="route('admin.financial-reports.show', report.id)"
                      class="text-blue-600 hover:text-blue-900"
                    >
                      View
                    </Link>
                    <Link
                      :href="route('admin.financial-reports.edit', report.id)"
                      class="text-gray-600 hover:text-gray-900"
                    >
                      Edit
                    </Link>
                    <button
                      v-if="!report.is_published"
                      @click="publishReport(report.id)"
                      class="text-green-600 hover:text-green-900"
                    >
                      Publish
                    </button>
                    <button
                      v-else
                      @click="unpublishReport(report.id)"
                      class="text-orange-600 hover:text-orange-900"
                    >
                      Unpublish
                    </button>
                    <button
                      @click="deleteReport(report.id)"
                      class="text-red-600 hover:text-red-900"
                    >
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="reports.length === 0">
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                  <DocumentTextIcon class="h-12 w-12 mx-auto mb-2 text-gray-400" aria-hidden="true" />
                  <p class="text-sm">No financial reports created yet</p>
                  <p class="text-xs mt-1">Create your first report to get started</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import CustomAdminSidebar from '@/components/CustomAdminSidebar.vue';
import { 
  PlusIcon, 
  DocumentTextIcon, 
  CheckCircleIcon,
  ClockIcon,
  TrendingUpIcon
} from '@heroicons/vue/24/outline';

interface FinancialReport {
  id: number;
  title: string;
  report_type: string;
  report_type_label: string;
  report_period: string;
  report_period_display: string;
  report_date: string;
  total_revenue: number;
  total_expenses: number;
  net_profit: number;
  profit_margin: number;
  financial_health_score: number;
  health_score_label: string;
  health_score_color: string;
  is_published: boolean;
}

interface Stats {
  total_reports: number;
  published_reports: number;
  draft_reports: number;
  latest_report?: {
    financial_health_score: number;
  };
}

const props = defineProps<{
  reports: FinancialReport[];
  stats: Stats;
}>();

const formatNumber = (value: number): string => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value);
};

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const getStatusClass = (isPublished: boolean): string => {
  return isPublished
    ? 'px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800'
    : 'px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800';
};

const getHealthScoreClass = (color: string): string => {
  const classes = {
    green: 'bg-green-100 text-green-800',
    yellow: 'bg-yellow-100 text-yellow-800',
    orange: 'bg-orange-100 text-orange-800',
    red: 'bg-red-100 text-red-800',
  };
  return classes[color] || 'bg-gray-100 text-gray-800';
};

const publishReport = (reportId: number) => {
  if (confirm('Publish this financial report? It will be visible to investors.')) {
    router.post(route('admin.financial-reports.publish', reportId));
  }
};

const unpublishReport = (reportId: number) => {
  if (confirm('Unpublish this financial report? It will no longer be visible to investors.')) {
    router.post(route('admin.financial-reports.unpublish', reportId));
  }
};

const deleteReport = (reportId: number) => {
  if (confirm('Delete this financial report permanently? This action cannot be undone.')) {
    router.delete(route('admin.financial-reports.destroy', reportId));
  }
};
</script>