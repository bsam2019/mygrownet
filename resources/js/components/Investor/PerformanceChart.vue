<template>
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow duration-300">
    <div class="p-6">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-gradient-to-br from-violet-500 to-purple-600 rounded-xl flex items-center justify-center">
            <ChartBarSquareIcon class="h-5 w-5 text-white" aria-hidden="true" />
          </div>
          <div>
            <h3 class="font-semibold text-gray-900">Performance Trends</h3>
            <p class="text-xs text-gray-500">Historical data analysis</p>
          </div>
        </div>
      </div>
      
      <div v-if="performanceMetrics && hasData" class="space-y-5">
        <!-- Chart Tabs -->
        <div class="flex p-1 bg-gray-100 rounded-xl">
          <button
            v-for="tab in chartTabs"
            :key="tab.key"
            @click="switchTab(tab.key)"
            :class="[
              'flex-1 flex items-center justify-center gap-2 px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200',
              activeTab === tab.key
                ? 'bg-white text-gray-900 shadow-sm'
                : 'text-gray-600 hover:text-gray-900'
            ]"
          >
            <span>{{ tab.label }}</span>
          </button>
        </div>

        <!-- Chart Container -->
        <div class="relative h-56 sm:h-64">
          <canvas ref="chartCanvas"></canvas>
        </div>

        <!-- Chart Stats -->
        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-100">
          <div class="bg-gray-50 rounded-xl p-3">
            <p class="text-xs text-gray-500 mb-1">{{ getStatLabel() }}</p>
            <p class="text-lg font-bold" :class="getStatColor()">
              {{ getStatValue() }}
            </p>
          </div>
          <div class="bg-gray-50 rounded-xl p-3">
            <p class="text-xs text-gray-500 mb-1">Trend</p>
            <div class="flex items-center gap-2">
              <ArrowTrendingUpIcon v-if="getTrendDirection() === 'up'" class="h-5 w-5 text-emerald-600" aria-hidden="true" />
              <ArrowTrendingDownIcon v-else-if="getTrendDirection() === 'down'" class="h-5 w-5 text-red-600" aria-hidden="true" />
              <MinusIcon v-else class="h-5 w-5 text-gray-500" aria-hidden="true" />
              <p class="text-lg font-bold" :class="getTrendColor()">
                {{ getTrendValue() }}
              </p>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Empty State -->
      <div v-else class="text-center py-10">
        <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <ChartBarSquareIcon class="h-8 w-8 text-gray-400" aria-hidden="true" />
        </div>
        <p class="text-sm font-medium text-gray-900 mb-1">No Trend Data Yet</p>
        <p class="text-xs text-gray-500">Performance trends will appear after multiple reporting periods</p>
      </div>
    </div>
  </div>
</template>


<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { Chart, registerables, type ChartConfiguration } from 'chart.js';
import { 
  ChartBarSquareIcon,
  ArrowTrendingUpIcon,
  ArrowTrendingDownIcon,
  MinusIcon,
} from '@heroicons/vue/24/outline';

Chart.register(...registerables);

interface PerformanceMetrics {
  revenue_trend: { labels: string[]; data: number[] };
  profit_trend: { labels: string[]; data: number[] };
  member_growth: { labels: string[]; data: number[] };
  health_score_trend: { labels: string[]; data: number[] };
}

const props = defineProps<{
  performanceMetrics: PerformanceMetrics | null;
}>();

const activeTab = ref('revenue');
const chartCanvas = ref<HTMLCanvasElement | null>(null);
let chartInstance: Chart | null = null;

const chartTabs = [
  { key: 'revenue', label: 'Revenue' },
  { key: 'profit', label: 'Profit' },
  { key: 'members', label: 'Members' },
  { key: 'health', label: 'Health' },
];

const hasData = computed(() => {
  if (!props.performanceMetrics) return false;
  return props.performanceMetrics.revenue_trend.data.length > 0;
});

const getChartConfig = (tab: string): ChartConfiguration => {
  const metrics = props.performanceMetrics;
  if (!metrics) {
    return { type: 'line', data: { labels: [], datasets: [] } };
  }

  const configs: Record<string, { data: { labels: string[]; data: number[] }; color: string; label: string }> = {
    revenue: { data: metrics.revenue_trend, color: '#3b82f6', label: 'Revenue (K)' },
    profit: { data: metrics.profit_trend, color: '#10b981', label: 'Net Profit (K)' },
    members: { data: metrics.member_growth, color: '#8b5cf6', label: 'Total Members' },
    health: { data: metrics.health_score_trend, color: '#f59e0b', label: 'Health Score' },
  };

  const config = configs[tab];

  return {
    type: 'line',
    data: {
      labels: config.data.labels,
      datasets: [{
        label: config.label,
        data: config.data.data,
        borderColor: config.color,
        backgroundColor: `${config.color}20`,
        fill: true,
        tension: 0.4,
        pointRadius: 4,
        pointBackgroundColor: 'white',
        pointBorderColor: config.color,
        pointBorderWidth: 2,
        pointHoverRadius: 6,
        borderWidth: 2.5,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { intersect: false, mode: 'index' },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: 'white',
          titleColor: '#1f2937',
          bodyColor: '#4b5563',
          borderColor: '#e5e7eb',
          borderWidth: 1,
          padding: 12,
          cornerRadius: 8,
          displayColors: false,
          callbacks: {
            label: (context) => {
              const value = context.parsed.y;
              if (tab === 'members') return `${value.toLocaleString()} members`;
              if (tab === 'health') return `${value}/100`;
              return `K${value.toLocaleString()}`;
            },
          },
        },
      },
      scales: {
        x: { grid: { display: false }, ticks: { color: '#9ca3af', font: { size: 11 } } },
        y: {
          beginAtZero: tab === 'health' || tab === 'members',
          max: tab === 'health' ? 100 : undefined,
          grid: { color: '#f3f4f6' },
          ticks: {
            color: '#9ca3af',
            font: { size: 11 },
            callback: (value) => {
              if (tab === 'members') return value.toLocaleString();
              if (tab === 'health') return value;
              return `K${((value as number) / 1000).toFixed(0)}k`;
            },
          },
        },
      },
    },
  };
};

const createChart = () => {
  if (!chartCanvas.value || !hasData.value) return;
  if (chartInstance) chartInstance.destroy();
  chartInstance = new Chart(chartCanvas.value, getChartConfig(activeTab.value));
};

const switchTab = (tab: string) => {
  activeTab.value = tab;
  nextTick(() => createChart());
};

const formatNumber = (value: number): string => {
  return new Intl.NumberFormat('en-US').format(value);
};

const getLatestValue = (data: number[]): number => data.length > 0 ? data[data.length - 1] : 0;

const getStatLabel = (): string => {
  const labels: Record<string, string> = { revenue: 'Latest Revenue', profit: 'Latest Profit', members: 'Total Members', health: 'Health Score' };
  return labels[activeTab.value] || 'Value';
};

const getStatValue = (): string => {
  const metrics = props.performanceMetrics;
  if (!metrics) return 'N/A';
  const dataMap: Record<string, number[]> = { revenue: metrics.revenue_trend.data, profit: metrics.profit_trend.data, members: metrics.member_growth.data, health: metrics.health_score_trend.data };
  const value = getLatestValue(dataMap[activeTab.value]);
  if (activeTab.value === 'members') return formatNumber(value);
  if (activeTab.value === 'health') return `${value}/100`;
  return `K${formatNumber(value)}`;
};

const getStatColor = (): string => {
  if (activeTab.value === 'profit' && props.performanceMetrics) {
    return getLatestValue(props.performanceMetrics.profit_trend.data) >= 0 ? 'text-emerald-600' : 'text-red-600';
  }
  return 'text-gray-900';
};

const calculateTrend = (): number | null => {
  const metrics = props.performanceMetrics;
  if (!metrics) return null;
  const dataMap: Record<string, number[]> = { revenue: metrics.revenue_trend.data, profit: metrics.profit_trend.data, members: metrics.member_growth.data, health: metrics.health_score_trend.data };
  const data = dataMap[activeTab.value];
  if (data.length < 2) return null;
  const latest = data[data.length - 1], previous = data[data.length - 2];
  if (previous === 0) return null;
  return ((latest - previous) / Math.abs(previous)) * 100;
};

const getTrendValue = (): string => {
  const trend = calculateTrend();
  if (trend === null) return 'N/A';
  return `${trend >= 0 ? '+' : ''}${trend.toFixed(1)}%`;
};

const getTrendColor = (): string => {
  const trend = calculateTrend();
  if (trend === null) return 'text-gray-500';
  return trend > 0 ? 'text-emerald-600' : trend < 0 ? 'text-red-600' : 'text-gray-500';
};

const getTrendDirection = (): string => {
  const trend = calculateTrend();
  if (trend === null) return 'neutral';
  return trend > 0 ? 'up' : trend < 0 ? 'down' : 'neutral';
};

onMounted(() => nextTick(() => createChart()));
onUnmounted(() => { if (chartInstance) chartInstance.destroy(); });
watch(() => props.performanceMetrics, () => nextTick(() => createChart()), { deep: true });
</script>
