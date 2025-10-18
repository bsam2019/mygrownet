<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
          <div class="p-2 bg-gradient-to-r from-green-500 to-green-600 rounded-lg mr-3">
            <ChartBarIcon class="h-6 w-6 text-white" />
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Investment Performance</h3>
            <p class="text-sm text-gray-500">Track your investment growth over time</p>
          </div>
        </div>
        <div class="flex items-center space-x-2">
          <select 
            v-model="selectedPeriod" 
            @change="updateChart"
            data-testid="period-select"
            class="text-sm border border-gray-300 rounded-lg px-3 py-1 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="7d">7 Days</option>
            <option value="30d">30 Days</option>
            <option value="90d">90 Days</option>
            <option value="1y">1 Year</option>
            <option value="all">All Time</option>
          </select>
          <button
            @click="toggleChartType"
            data-testid="chart-type-toggle"
            class="text-sm text-gray-600 hover:text-gray-900 transition-colors px-3 py-1 border border-gray-300 rounded-lg"
          >
            {{ chartType === 'line' ? 'Bar Chart' : 'Line Chart' }}
          </button>
        </div>
      </div>

      <!-- Performance Metrics -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="text-center p-3 bg-blue-50 rounded-lg">
          <div class="text-lg font-bold text-blue-600">{{ formatCurrency(metrics.current_value) }}</div>
          <div class="text-xs text-blue-700">Current Value</div>
        </div>
        <div class="text-center p-3 bg-green-50 rounded-lg">
          <div class="text-lg font-bold text-green-600">{{ formatCurrency(metrics.total_gains) }}</div>
          <div class="text-xs text-green-700">Total Gains</div>
        </div>
        <div class="text-center p-3 bg-purple-50 rounded-lg">
          <div class="text-lg font-bold text-purple-600">{{ formatPercentage(metrics.roi) }}</div>
          <div class="text-xs text-purple-700">ROI</div>
        </div>
        <div class="text-center p-3 bg-yellow-50 rounded-lg">
          <div class="text-lg font-bold text-yellow-600">{{ formatPercentage(metrics.annual_return) }}</div>
          <div class="text-xs text-yellow-700">Annual Return</div>
        </div>
      </div>

      <!-- Chart Container -->
      <div class="relative">
        <div v-if="loading" data-testid="loading-spinner" class="flex justify-center items-center h-64">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>
        
        <div v-else-if="chartData.length === 0" data-testid="empty-chart" class="flex flex-col items-center justify-center h-64 text-gray-500">
          <ChartBarIcon class="h-12 w-12 mb-2" />
          <p>No performance data available</p>
        </div>
        
        <div v-else class="h-64">
          <canvas ref="chartCanvas" class="w-full h-full"></canvas>
        </div>
      </div>

      <!-- Performance Insights -->
      <div v-if="insights.length > 0" class="mt-6 pt-6 border-t border-gray-200">
        <h4 class="text-sm font-medium text-gray-900 mb-3">Performance Insights</h4>
        <div class="space-y-2">
          <div 
            v-for="insight in insights" 
            :key="insight.id"
            :class="[
              'flex items-start p-3 rounded-lg text-sm',
              insight.type === 'positive' ? 'bg-green-50 text-green-800' :
              insight.type === 'negative' ? 'bg-red-50 text-red-800' :
              'bg-blue-50 text-blue-800'
            ]"
          >
            <div class="flex-shrink-0 mr-2 mt-0.5">
              <div :class="[
                'w-2 h-2 rounded-full',
                insight.type === 'positive' ? 'bg-green-500' :
                insight.type === 'negative' ? 'bg-red-500' :
                'bg-blue-500'
              ]"></div>
            </div>
            <div>
              <div class="font-medium">{{ insight.title }}</div>
              <div class="text-xs opacity-80 mt-1">{{ insight.description }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Earnings Breakdown -->
      <div class="mt-6 pt-6 border-t border-gray-200">
        <h4 class="text-sm font-medium text-gray-900 mb-3">Earnings Breakdown</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="p-3 bg-gray-50 rounded-lg">
            <div class="flex justify-between items-center mb-2">
              <span class="text-sm text-gray-600">Profit Shares</span>
              <span class="text-sm font-medium text-green-600">{{ formatCurrency(earningsBreakdown.profit_shares) }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div 
                data-testid="profit-shares-bar"
                class="bg-green-500 h-2 rounded-full transition-all duration-300"
                :style="{ width: (earningsBreakdown.profit_shares / metrics.total_gains * 100) + '%' }"
              ></div>
            </div>
          </div>
          
          <div class="p-3 bg-gray-50 rounded-lg">
            <div class="flex justify-between items-center mb-2">
              <span class="text-sm text-gray-600">Quarterly Bonuses</span>
              <span class="text-sm font-medium text-blue-600">{{ formatCurrency(earningsBreakdown.quarterly_bonuses) }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div 
                class="bg-blue-500 h-2 rounded-full transition-all duration-300"
                :style="{ width: (earningsBreakdown.quarterly_bonuses / metrics.total_gains * 100) + '%' }"
              ></div>
            </div>
          </div>
          
          <div class="p-3 bg-gray-50 rounded-lg">
            <div class="flex justify-between items-center mb-2">
              <span class="text-sm text-gray-600">Reinvestment Bonus</span>
              <span class="text-sm font-medium text-purple-600">{{ formatCurrency(earningsBreakdown.reinvestment_bonus) }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div 
                class="bg-purple-500 h-2 rounded-full transition-all duration-300"
                :style="{ width: (earningsBreakdown.reinvestment_bonus / metrics.total_gains * 100) + '%' }"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, computed } from 'vue';
import { Chart, registerables } from 'chart.js';
import { ChartBarIcon } from '@heroicons/vue/24/outline';
import { formatCurrency, formatPercentage, formatDate } from '@/utils/formatting';

Chart.register(...registerables);

interface ChartDataPoint {
  date: string;
  value: number;
  profit_shares: number;
  quarterly_bonuses: number;
  reinvestment_bonus: number;
}

interface PerformanceMetrics {
  current_value: number;
  total_gains: number;
  roi: number;
  annual_return: number;
}

interface EarningsBreakdown {
  profit_shares: number;
  quarterly_bonuses: number;
  reinvestment_bonus: number;
}

interface PerformanceInsight {
  id: string;
  type: 'positive' | 'negative' | 'neutral';
  title: string;
  description: string;
}

interface Props {
  chartData: ChartDataPoint[];
  metrics: PerformanceMetrics;
  earningsBreakdown: EarningsBreakdown;
  insights?: PerformanceInsight[];
}

const props = withDefaults(defineProps<Props>(), {
  chartData: () => [],
  metrics: () => ({
    current_value: 0,
    total_gains: 0,
    roi: 0,
    annual_return: 0,
  }),
  earningsBreakdown: () => ({
    profit_shares: 0,
    quarterly_bonuses: 0,
    reinvestment_bonus: 0,
  }),
  insights: () => [],
});

const emit = defineEmits<{
  periodChange: [period: string];
}>();

const chartCanvas = ref<HTMLCanvasElement | null>(null);
const loading = ref(false);
const selectedPeriod = ref('30d');
const chartType = ref<'line' | 'bar'>('line');
let chart: Chart | null = null;

const createChart = () => {
  if (!chartCanvas.value || props.chartData.length === 0) return;

  const ctx = chartCanvas.value.getContext('2d');
  if (!ctx) return;

  // Destroy existing chart
  if (chart) {
    chart.destroy();
  }

  const chartData = {
    labels: props.chartData.map(item => formatDate(item.date)),
    datasets: [
      {
        label: 'Investment Value',
        data: props.chartData.map(item => item.value),
        borderColor: 'rgb(59, 130, 246)',
        backgroundColor: chartType.value === 'line' 
          ? 'rgba(59, 130, 246, 0.1)' 
          : 'rgba(59, 130, 246, 0.8)',
        tension: 0.4,
        fill: chartType.value === 'line',
      },
      {
        label: 'Profit Shares',
        data: props.chartData.map(item => item.profit_shares),
        borderColor: 'rgb(34, 197, 94)',
        backgroundColor: chartType.value === 'line' 
          ? 'rgba(34, 197, 94, 0.1)' 
          : 'rgba(34, 197, 94, 0.8)',
        tension: 0.4,
        fill: false,
      },
      {
        label: 'Quarterly Bonuses',
        data: props.chartData.map(item => item.quarterly_bonuses),
        borderColor: 'rgb(168, 85, 247)',
        backgroundColor: chartType.value === 'line' 
          ? 'rgba(168, 85, 247, 0.1)' 
          : 'rgba(168, 85, 247, 0.8)',
        tension: 0.4,
        fill: false,
      }
    ]
  };

  const options = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        display: true,
        position: 'top' as const,
        labels: {
          usePointStyle: true,
          padding: 20,
        }
      },
      tooltip: {
        mode: 'index' as const,
        intersect: false,
        callbacks: {
          label: function(context: any) {
            return `${context.dataset.label}: ${formatCurrency(context.parsed.y)}`;
          }
        }
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        grid: {
          color: 'rgba(0, 0, 0, 0.05)'
        },
        ticks: {
          callback: function(value: any) {
            return formatCurrency(value);
          }
        }
      },
      x: {
        grid: {
          display: false
        }
      }
    },
    interaction: {
      mode: 'nearest' as const,
      axis: 'x' as const,
      intersect: false
    }
  };

  chart = new Chart(ctx, {
    type: chartType.value,
    data: chartData,
    options: options
  });
};

const updateChart = () => {
  loading.value = true;
  emit('periodChange', selectedPeriod.value);
  
  // Simulate loading delay
  setTimeout(() => {
    createChart();
    loading.value = false;
  }, 500);
};

const toggleChartType = () => {
  chartType.value = chartType.value === 'line' ? 'bar' : 'line';
  createChart();
};

// Watch for data changes
watch(() => props.chartData, () => {
  createChart();
}, { deep: true });

onMounted(() => {
  createChart();
});

onUnmounted(() => {
  if (chart) {
    chart.destroy();
  }
});
</script>