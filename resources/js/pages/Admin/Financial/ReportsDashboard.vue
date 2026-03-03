<template>
  <AdminLayout>
    <Head title="Financial Reports" />

    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-semibold text-gray-900">Financial Reports</h1>
            <p class="mt-1 text-sm text-gray-600">
              Comprehensive financial analytics and insights
            </p>
          </div>
          
          <!-- Period Selector -->
          <div class="flex items-center gap-3">
            <select
              v-model="selectedPeriod"
              @change="loadData"
              class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="today">Today</option>
              <option value="week">This Week</option>
              <option value="month">This Month</option>
              <option value="quarter">This Quarter</option>
              <option value="year">This Year</option>
            </select>
            
            <button
              @click="refreshData"
              :disabled="loading"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
            >
              <ArrowPathIcon class="h-4 w-4 mr-2" :class="{ 'animate-spin': loading }" />
              Refresh
            </button>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
          <div class="text-center">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            <p class="mt-4 text-sm text-gray-600">Loading financial data...</p>
          </div>
        </div>

        <!-- Main Content -->
        <div v-else class="space-y-6">
          <!-- Key Metrics Cards -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Revenue -->
            <MetricCard
              title="Total Revenue"
              :value="formatCurrency(overview?.revenue_metrics?.total_revenue || 0)"
              :change="overview?.revenue_metrics?.growth_rate || 0"
              :trend="(overview?.revenue_metrics?.growth_rate || 0) >= 0 ? 'up' : 'down'"
              icon="currency-dollar"
              color="green"
            />

            <!-- Gross Profit -->
            <MetricCard
              title="Gross Profit"
              :value="formatCurrency(overview?.profitability?.gross_profit || 0)"
              :subtitle="`${overview?.profitability?.profit_margin?.toFixed(1) || 0}% margin`"
              icon="chart-bar"
              color="blue"
            />

            <!-- Total Expenses -->
            <MetricCard
              title="Total Expenses"
              :value="formatCurrency(overview?.expense_metrics?.total_expenses || 0)"
              :subtitle="`${overview?.expense_metrics?.transaction_count || 0} transactions`"
              icon="arrow-trending-down"
              color="orange"
            />

            <!-- Net Cash Flow -->
            <MetricCard
              title="Net Cash Flow"
              :value="formatCurrency(overview?.cash_flow?.net_cash_flow || 0)"
              :subtitle="`Ratio: ${overview?.cash_flow?.cash_flow_ratio?.toFixed(2) || 0}`"
              :trend="(overview?.cash_flow?.net_cash_flow || 0) >= 0 ? 'up' : 'down'"
              icon="banknotes"
              color="purple"
            />
          </div>

          <!-- Charts Row -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Revenue Trend Chart -->
            <div class="bg-white rounded-lg shadow p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Revenue Trend</h3>
              <canvas ref="revenueTrendChart"></canvas>
            </div>

            <!-- Revenue by Module Chart -->
            <div class="bg-white rounded-lg shadow p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Revenue by Module</h3>
              <canvas ref="moduleRevenueChart"></canvas>
            </div>
          </div>

          <!-- Transaction Breakdown -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Transaction Breakdown</h3>
            </div>
            <div class="p-6">
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead>
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Transaction Type
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Count
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Total Amount
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <template v-for="transaction in transactionBreakdown" :key="transaction.type">
                      <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                          {{ formatTransactionType(transaction.type) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                          {{ transaction.total_count }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                          {{ formatCurrency(transaction.total_amount) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                          <div class="flex gap-2">
                            <span
                              v-for="(status, key) in transaction.by_status"
                              :key="key"
                              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                              :class="getStatusClass(key)"
                            >
                              {{ key }}: {{ status.count }}
                            </span>
                          </div>
                        </td>
                      </tr>
                    </template>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Compliance Metrics -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Compliance Metrics</h3>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Commission Cap Compliance -->
                <div>
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Commission to Revenue Ratio</span>
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="compliance?.commission_cap_compliant ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                    >
                      {{ compliance?.commission_cap_compliant ? 'Compliant' : 'Non-Compliant' }}
                    </span>
                  </div>
                  <div class="relative pt-1">
                    <div class="flex mb-2 items-center justify-between">
                      <div>
                        <span class="text-xs font-semibold inline-block text-blue-600">
                          {{ compliance?.commission_to_revenue_ratio?.toFixed(2) || 0 }}%
                        </span>
                      </div>
                      <div class="text-right">
                        <span class="text-xs font-semibold inline-block text-gray-600">
                          Cap: {{ compliance?.commission_cap_threshold || 25 }}%
                        </span>
                      </div>
                    </div>
                    <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-200">
                      <div
                        :style="{ width: `${Math.min((compliance?.commission_to_revenue_ratio || 0) / (compliance?.commission_cap_threshold || 25) * 100, 100)}%` }"
                        :class="compliance?.commission_cap_compliant ? 'bg-green-500' : 'bg-red-500'"
                        class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center"
                      ></div>
                    </div>
                  </div>
                </div>

                <!-- Payout Timing Compliance -->
                <div>
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Payout Timing Compliance</span>
                    <span class="text-sm text-gray-600">
                      {{ compliance?.timely_payouts || 0 }} / {{ compliance?.total_payouts || 0 }}
                    </span>
                  </div>
                  <div class="relative pt-1">
                    <div class="flex mb-2 items-center justify-between">
                      <div>
                        <span class="text-xs font-semibold inline-block text-blue-600">
                          {{ compliance?.payout_timing_compliance?.toFixed(1) || 0 }}%
                        </span>
                      </div>
                      <div class="text-right">
                        <span class="text-xs font-semibold inline-block text-gray-600">
                          Target: 100%
                        </span>
                      </div>
                    </div>
                    <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-200">
                      <div
                        :style="{ width: `${compliance?.payout_timing_compliance || 0}%` }"
                        :class="(compliance?.payout_timing_compliance || 0) >= 95 ? 'bg-green-500' : 'bg-yellow-500'"
                        class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center"
                      ></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Module Performance -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Module Performance</h3>
            </div>
            <div class="p-6">
              <div class="space-y-4">
                <div
                  v-for="module in moduleRevenue"
                  :key="module.module_id"
                  class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"
                >
                  <div class="flex-1">
                    <div class="flex items-center justify-between mb-2">
                      <span class="text-sm font-medium text-gray-900">{{ module.module_name }}</span>
                      <span class="text-sm font-semibold text-gray-900">
                        {{ formatCurrency(module.revenue) }}
                      </span>
                    </div>
                    <div class="flex items-center gap-4 text-xs text-gray-600">
                      <span>{{ module.transaction_count }} transactions</span>
                      <span>{{ module.percentage?.toFixed(1) }}% of total</span>
                    </div>
                    <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                      <div
                        class="bg-blue-600 h-2 rounded-full"
                        :style="{ width: `${module.percentage}%` }"
                      ></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ArrowPathIcon } from '@heroicons/vue/24/outline';
import MetricCard from '@/Components/Admin/MetricCard.vue';
import Chart from 'chart.js/auto';

const selectedPeriod = ref('month');
const loading = ref(false);
const overview = ref(null);
const transactionBreakdown = ref([]);
const moduleRevenue = ref([]);
const transactionTrends = ref([]);
const compliance = ref(null);

const revenueTrendChart = ref(null);
const moduleRevenueChart = ref(null);
let revenueTrendChartInstance = null;
let moduleRevenueChartInstance = null;

const loadData = async () => {
  loading.value = true;
  
  try {
    // Load all data in parallel
    const [overviewRes, breakdownRes, moduleRes, trendsRes, complianceRes] = await Promise.all([
      fetch(`/admin/financial/v2/overview?period=${selectedPeriod.value}`),
      fetch(`/admin/financial/v2/transaction-breakdown?period=${selectedPeriod.value}`),
      fetch(`/admin/financial/v2/revenue-by-module?period=${selectedPeriod.value}`),
      fetch(`/admin/financial/v2/transaction-trends?period=${selectedPeriod.value}`),
      fetch(`/admin/financial/v2/compliance-metrics?period=${selectedPeriod.value}`)
    ]);

    overview.value = (await overviewRes.json()).data;
    transactionBreakdown.value = (await breakdownRes.json()).data;
    moduleRevenue.value = (await moduleRes.json()).data;
    transactionTrends.value = (await trendsRes.json()).data;
    compliance.value = (await complianceRes.json()).data;

    // Update charts
    await nextTick();
    updateCharts();
  } catch (error) {
    console.error('Failed to load financial data:', error);
  } finally {
    loading.value = false;
  }
};

const refreshData = async () => {
  // Clear cache first
  try {
    await fetch('/admin/financial/v2/clear-cache', { method: 'POST' });
  } catch (error) {
    console.error('Failed to clear cache:', error);
  }
  
  await loadData();
};

const updateCharts = () => {
  // Revenue Trend Chart
  if (revenueTrendChart.value) {
    const ctx = revenueTrendChart.value.getContext('2d');
    
    if (revenueTrendChartInstance) {
      revenueTrendChartInstance.destroy();
    }

    revenueTrendChartInstance = new Chart(ctx, {
      type: 'line',
      data: {
        labels: transactionTrends.value.map(t => t.date),
        datasets: [
          {
            label: 'Revenue',
            data: transactionTrends.value.map(t => t.revenue),
            borderColor: 'rgb(34, 197, 94)',
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            tension: 0.4
          },
          {
            label: 'Expenses',
            data: transactionTrends.value.map(t => t.expenses),
            borderColor: 'rgb(239, 68, 68)',
            backgroundColor: 'rgba(239, 68, 68, 0.1)',
            tension: 0.4
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            position: 'bottom'
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: (value) => 'K' + value.toLocaleString()
            }
          }
        }
      }
    });
  }

  // Module Revenue Chart
  if (moduleRevenueChart.value && moduleRevenue.value.length > 0) {
    const ctx = moduleRevenueChart.value.getContext('2d');
    
    if (moduleRevenueChartInstance) {
      moduleRevenueChartInstance.destroy();
    }

    moduleRevenueChartInstance = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: moduleRevenue.value.map(m => m.module_name),
        datasets: [{
          data: moduleRevenue.value.map(m => m.revenue),
          backgroundColor: [
            'rgb(59, 130, 246)',
            'rgb(34, 197, 94)',
            'rgb(168, 85, 247)',
            'rgb(251, 146, 60)',
            'rgb(236, 72, 153)',
            'rgb(14, 165, 233)',
            'rgb(132, 204, 22)',
            'rgb(251, 191, 36)'
          ]
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            position: 'bottom'
          },
          tooltip: {
            callbacks: {
              label: (context) => {
                const label = context.label || '';
                const value = context.parsed || 0;
                return `${label}: K${value.toLocaleString()}`;
              }
            }
          }
        }
      }
    });
  }
};

const formatCurrency = (amount) => {
  return 'K' + parseFloat(amount || 0).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
};

const formatTransactionType = (type) => {
  return type.split('_').map(word => 
    word.charAt(0).toUpperCase() + word.slice(1)
  ).join(' ');
};

const getStatusClass = (status) => {
  const classes = {
    completed: 'bg-green-100 text-green-800',
    pending: 'bg-yellow-100 text-yellow-800',
    failed: 'bg-red-100 text-red-800',
    cancelled: 'bg-gray-100 text-gray-800'
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

onMounted(() => {
  loadData();
});
</script>
