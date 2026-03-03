<template>
  <AdminLayout>
    <Head title="Profit & Loss" />

    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-semibold text-gray-900">Profit & Loss Statement</h1>
            <p class="mt-1 text-sm text-gray-600">
              Comprehensive revenue, expense, and profitability analysis
            </p>
          </div>
          
          <!-- Period Selector -->
          <div class="flex items-center gap-3">
            <!-- Module Filter -->
            <select
              v-model="selectedModuleId"
              @change="handleModuleChange"
              class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option :value="null">All Modules</option>
              <option v-for="module in props.modules" :key="module.id" :value="module.id">
                {{ module.name }}
              </option>
            </select>
            
            <select
              v-model="selectedPeriod"
              @change="handlePeriodChange"
              class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="today">Today</option>
              <option value="week">This Week</option>
              <option value="month">This Month</option>
              <option value="quarter">This Quarter</option>
              <option value="year">This Year</option>
              <option value="custom">Custom Period...</option>
            </select>
            
            <!-- Custom Period Inputs -->
            <div v-if="selectedPeriod === 'custom'" class="flex items-center gap-2">
              <input
                v-model="customStartDate"
                type="date"
                class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
              <span class="text-gray-500">to</span>
              <input
                v-model="customEndDate"
                type="date"
                class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
              <button
                @click="applyCustomPeriod"
                class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm"
              >
                Apply
              </button>
            </div>
            
            <button
              @click="exportPDF"
              :disabled="loading"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
            >
              <ArrowDownTrayIcon class="h-4 w-4 mr-2" aria-hidden="true" />
              Export PDF
            </button>
            
            <button
              @click="refreshData"
              :disabled="loading"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
            >
              <ArrowPathIcon class="h-4 w-4 mr-2" :class="{ 'animate-spin': loading }" aria-hidden="true" />
              Refresh
            </button>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
          <div class="text-center">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            <p class="mt-4 text-sm text-gray-600">Loading P&L data...</p>
          </div>
        </div>

        <!-- Main Content -->
        <div v-else class="space-y-6">
          <!-- Key Profitability Metrics -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Revenue -->
            <MetricCard
              title="Total Revenue"
              :value="formatCurrency(statement?.revenue?.total || 0)"
              :subtitle="`${Object.keys(statement?.revenue?.breakdown || {}).length} sources`"
              icon="currency-dollar"
              color="green"
            />

            <!-- Total Expenses -->
            <MetricCard
              title="Total Expenses"
              :value="formatCurrency(statement?.expenses?.total || 0)"
              :subtitle="`${Object.keys(statement?.expenses?.breakdown || {}).length} categories`"
              icon="arrow-trending-down"
              color="red"
            />

            <!-- Gross Profit -->
            <MetricCard
              title="Gross Profit"
              :value="formatCurrency(statement?.profitability?.gross_profit || 0)"
              :subtitle="`${statement?.profitability?.profit_margin?.toFixed(1) || 0}% margin`"
              :trend="(statement?.profitability?.gross_profit || 0) >= 0 ? 'up' : 'down'"
              icon="chart-bar"
              color="blue"
            />

            <!-- Net Cash Flow -->
            <MetricCard
              title="Net Cash Flow"
              :value="formatCurrency(cashFlow?.net_cash_flow || 0)"
              :subtitle="`Ratio: ${cashFlow?.cash_flow_ratio?.toFixed(2) || 0}`"
              :trend="(cashFlow?.net_cash_flow || 0) >= 0 ? 'up' : 'down'"
              icon="banknotes"
              color="purple"
            />
          </div>

          <!-- P&L Statement Summary -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">P&L Summary</h3>
              <p class="mt-1 text-sm text-gray-600">
                {{ statement?.date_range?.from }} to {{ statement?.date_range?.to }}
              </p>
            </div>
            <div class="p-6">
              <div class="space-y-4">
                <!-- Revenue Section -->
                <div class="border-b border-gray-200 pb-4">
                  <div class="flex items-center justify-between mb-3">
                    <h4 class="text-base font-semibold text-gray-900">Revenue</h4>
                    <span class="text-lg font-bold text-green-600">
                      {{ formatCurrency(statement?.revenue?.total || 0) }}
                    </span>
                  </div>
                  <div class="space-y-2 ml-4">
                    <div
                      v-for="(item, key) in statement?.revenue?.breakdown"
                      :key="key"
                      class="flex items-center justify-between text-sm"
                    >
                      <span class="text-gray-600">{{ item.label }}</span>
                      <div class="flex items-center gap-3">
                        <span class="text-gray-500">({{ item.count }})</span>
                        <span class="text-gray-900 font-medium">
                          {{ formatCurrency(item.amount) }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Expenses Section -->
                <div class="border-b border-gray-200 pb-4">
                  <div class="flex items-center justify-between mb-3">
                    <h4 class="text-base font-semibold text-gray-900">Expenses</h4>
                    <span class="text-lg font-bold text-red-600">
                      {{ formatCurrency(statement?.expenses?.total || 0) }}
                    </span>
                  </div>
                  <div class="space-y-2 ml-4">
                    <div
                      v-for="(item, key) in statement?.expenses?.breakdown"
                      :key="key"
                      class="flex items-center justify-between text-sm"
                    >
                      <span class="text-gray-600">{{ item.label }}</span>
                      <div class="flex items-center gap-3">
                        <span class="text-gray-500">
                          ({{ statement?.profitability?.expense_ratios?.[key]?.toFixed(1) || 0 }}%)
                        </span>
                        <span class="text-gray-900 font-medium">
                          {{ formatCurrency(item.amount) }}
                        </span>
                        <!-- CMS Drill-down Link -->
                        <a 
                          v-if="item.source === 'cms'"
                          :href="route('cms.expenses.index')"
                          class="text-blue-600 hover:underline text-xs flex items-center gap-1"
                          title="View in CMS"
                        >
                          <span>View</span>
                          <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                          </svg>
                        </a>
                      </div>
                    </div>
                    
                    <!-- Platform Expenses Breakdown (if exists) -->
                    <div 
                      v-if="statement?.expenses?.breakdown?.platform_expenses?.breakdown"
                      class="ml-4 mt-2 space-y-1 border-l-2 border-gray-200 pl-3"
                    >
                      <div
                        v-for="(subItem, subKey) in statement.expenses.breakdown.platform_expenses.breakdown"
                        :key="subKey"
                        class="flex items-center justify-between text-xs"
                      >
                        <span class="text-gray-500">{{ subItem.label }}</span>
                        <div class="flex items-center gap-2">
                          <span class="text-gray-400">({{ subItem.count }})</span>
                          <span class="text-gray-700">
                            {{ formatCurrency(subItem.amount) }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Profitability Section -->
                <div class="pt-2">
                  <div class="flex items-center justify-between">
                    <h4 class="text-lg font-bold text-gray-900">Gross Profit</h4>
                    <span
                      class="text-xl font-bold"
                      :class="(statement?.profitability?.gross_profit || 0) >= 0 ? 'text-green-600' : 'text-red-600'"
                    >
                      {{ formatCurrency(statement?.profitability?.gross_profit || 0) }}
                    </span>
                  </div>
                  <div class="mt-2 flex items-center justify-between text-sm">
                    <span class="text-gray-600">Profit Margin</span>
                    <span
                      class="font-semibold"
                      :class="(statement?.profitability?.profit_margin || 0) >= 0 ? 'text-green-600' : 'text-red-600'"
                    >
                      {{ statement?.profitability?.profit_margin?.toFixed(2) || 0 }}%
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Charts Row -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- P&L Trend Chart -->
            <div class="bg-white rounded-lg shadow p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">P&L Trend</h3>
              <canvas ref="plTrendChart"></canvas>
            </div>

            <!-- Expense Breakdown Chart -->
            <div class="bg-white rounded-lg shadow p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Expense Breakdown</h3>
              <canvas ref="expenseBreakdownChart"></canvas>
            </div>
          </div>

          <!-- Module Profitability -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Module Profitability</h3>
            </div>
            <div class="p-6">
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead>
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Module
                      </th>
                      <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Revenue
                      </th>
                      <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Expenses
                      </th>
                      <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Profit
                      </th>
                      <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Margin
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr
                      v-for="module in statement?.by_module"
                      :key="module.module_id"
                    >
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ module.module_name }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-600">
                        {{ formatCurrency(module.revenue) }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600">
                        {{ formatCurrency(module.expenses) }}
                      </td>
                      <td
                        class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold"
                        :class="module.profit >= 0 ? 'text-green-600' : 'text-red-600'"
                      >
                        {{ formatCurrency(module.profit) }}
                      </td>
                      <td
                        class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium"
                        :class="module.profit_margin >= 0 ? 'text-green-600' : 'text-red-600'"
                      >
                        {{ module.profit_margin?.toFixed(1) }}%
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Commission Efficiency & Cash Flow -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Commission Efficiency -->
            <div class="bg-white rounded-lg shadow">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Commission Efficiency</h3>
              </div>
              <div class="p-6">
                <div class="space-y-4">
                  <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">Commission Ratio</span>
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="commissionEfficiency?.is_compliant ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                    >
                      {{ commissionEfficiency?.is_compliant ? 'Compliant' : 'Non-Compliant' }}
                    </span>
                  </div>
                  <div class="relative pt-1">
                    <div class="flex mb-2 items-center justify-between">
                      <div>
                        <span class="text-xs font-semibold inline-block text-blue-600">
                          {{ commissionEfficiency?.commission_ratio?.toFixed(2) || 0 }}%
                        </span>
                      </div>
                      <div class="text-right">
                        <span class="text-xs font-semibold inline-block text-gray-600">
                          Cap: {{ commissionEfficiency?.commission_cap || 25 }}%
                        </span>
                      </div>
                    </div>
                    <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-200">
                      <div
                        :style="{ width: `${Math.min((commissionEfficiency?.commission_ratio || 0) / (commissionEfficiency?.commission_cap || 25) * 100, 100)}%` }"
                        :class="commissionEfficiency?.is_compliant ? 'bg-green-500' : 'bg-red-500'"
                        class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center"
                      ></div>
                    </div>
                  </div>
                  <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200">
                    <div>
                      <p class="text-xs text-gray-500">Total Revenue</p>
                      <p class="text-sm font-semibold text-gray-900">
                        {{ formatCurrency(commissionEfficiency?.total_revenue || 0) }}
                      </p>
                    </div>
                    <div>
                      <p class="text-xs text-gray-500">Total Commissions</p>
                      <p class="text-sm font-semibold text-gray-900">
                        {{ formatCurrency(commissionEfficiency?.total_commissions || 0) }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Cash Flow Analysis -->
            <div class="bg-white rounded-lg shadow">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Cash Flow Analysis</h3>
              </div>
              <div class="p-6">
                <div class="space-y-4">
                  <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Cash Inflows</span>
                    <span class="text-sm font-semibold text-green-600">
                      {{ formatCurrency(cashFlow?.cash_inflows || 0) }}
                    </span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Cash Outflows</span>
                    <span class="text-sm font-semibold text-red-600">
                      {{ formatCurrency(cashFlow?.cash_outflows || 0) }}
                    </span>
                  </div>
                  <div class="pt-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                      <span class="text-base font-medium text-gray-900">Net Cash Flow</span>
                      <span
                        class="text-lg font-bold"
                        :class="(cashFlow?.net_cash_flow || 0) >= 0 ? 'text-green-600' : 'text-red-600'"
                      >
                        {{ formatCurrency(cashFlow?.net_cash_flow || 0) }}
                      </span>
                    </div>
                    <div class="mt-2 flex items-center justify-between text-sm">
                      <span class="text-gray-600">Cash Flow Ratio</span>
                      <span class="font-medium text-gray-900">
                        {{ cashFlow?.cash_flow_ratio?.toFixed(2) || 0 }}
                      </span>
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
import { ArrowPathIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline';
import MetricCard from '@/Components/Admin/MetricCard.vue';
import Chart from 'chart.js/auto';

const props = defineProps({
  initialPeriod: {
    type: String,
    default: 'month'
  },
  modules: {
    type: Array,
    default: () => []
  }
});

const selectedPeriod = ref(props.initialPeriod);
const selectedModuleId = ref(null);
const customStartDate = ref('');
const customEndDate = ref('');
const loading = ref(false);
const statement = ref(null);
const commissionEfficiency = ref(null);
const cashFlow = ref(null);

const plTrendChart = ref(null);
const expenseBreakdownChart = ref(null);
let plTrendChartInstance = null;
let expenseBreakdownChartInstance = null;

const loadData = async () => {
  loading.value = true;
  
  try {
    let url = `/admin/profit-loss/statement?period=${selectedPeriod.value}`;
    
    // Add module filter if selected
    if (selectedModuleId.value) {
      url += `&module_id=${selectedModuleId.value}`;
    }
    
    // Add custom dates if custom period is selected
    if (selectedPeriod.value === 'custom' && customStartDate.value && customEndDate.value) {
      url += `&start_date=${customStartDate.value}&end_date=${customEndDate.value}`;
    }
    
    // Load all data in parallel
    const [statementRes, efficiencyRes, cashFlowRes] = await Promise.all([
      fetch(url),
      fetch(url.replace('/statement', '/commission-efficiency')),
      fetch(url.replace('/statement', '/cash-flow'))
    ]);

    statement.value = (await statementRes.json()).data;
    commissionEfficiency.value = (await efficiencyRes.json()).data;
    cashFlow.value = (await cashFlowRes.json()).data;

    // Update charts
    await nextTick();
    updateCharts();
  } catch (error) {
    console.error('Failed to load P&L data:', error);
  } finally {
    loading.value = false;
  }
};

const handleModuleChange = () => {
  loadData();
};

const handlePeriodChange = () => {
  if (selectedPeriod.value !== 'custom') {
    loadData();
  }
};

const applyCustomPeriod = () => {
  if (customStartDate.value && customEndDate.value) {
    loadData();
  } else {
    alert('Please select both start and end dates');
  }
};

const refreshData = async () => {
  await loadData();
};

const exportPDF = () => {
  loading.value = true;
  
  let url = `/admin/profit-loss/export-pdf?period=${selectedPeriod.value}`;
  
  // Add module filter if selected
  if (selectedModuleId.value) {
    url += `&module_id=${selectedModuleId.value}`;
  }
  
  if (selectedPeriod.value === 'custom' && customStartDate.value && customEndDate.value) {
    url += `&start_date=${customStartDate.value}&end_date=${customEndDate.value}`;
  }
  
  // Open PDF in new window for download
  window.open(url, '_blank');
  
  loading.value = false;
};

const updateCharts = () => {
  // P&L Trend Chart
  if (plTrendChart.value && statement.value?.trends) {
    const ctx = plTrendChart.value.getContext('2d');
    
    if (plTrendChartInstance) {
      plTrendChartInstance.destroy();
    }

    plTrendChartInstance = new Chart(ctx, {
      type: 'line',
      data: {
        labels: statement.value.trends.map(t => t.date),
        datasets: [
          {
            label: 'Revenue',
            data: statement.value.trends.map(t => t.revenue),
            borderColor: 'rgb(34, 197, 94)',
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            tension: 0.4
          },
          {
            label: 'Expenses',
            data: statement.value.trends.map(t => t.expenses),
            borderColor: 'rgb(239, 68, 68)',
            backgroundColor: 'rgba(239, 68, 68, 0.1)',
            tension: 0.4
          },
          {
            label: 'Profit',
            data: statement.value.trends.map(t => t.profit),
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
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

  // Expense Breakdown Chart
  if (expenseBreakdownChart.value && statement.value?.expenses?.breakdown) {
    const ctx = expenseBreakdownChart.value.getContext('2d');
    
    if (expenseBreakdownChartInstance) {
      expenseBreakdownChartInstance.destroy();
    }

    const expenseData = Object.values(statement.value.expenses.breakdown);
    
    expenseBreakdownChartInstance = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: expenseData.map(e => e.label),
        datasets: [{
          data: expenseData.map(e => e.amount),
          backgroundColor: [
            'rgb(239, 68, 68)',
            'rgb(251, 146, 60)',
            'rgb(251, 191, 36)',
            'rgb(168, 85, 247)',
            'rgb(236, 72, 153)',
            'rgb(14, 165, 233)'
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

onMounted(() => {
  loadData();
});
</script>
