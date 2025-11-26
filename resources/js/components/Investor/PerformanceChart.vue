<template>
  <div class="bg-white rounded-xl shadow-lg p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Trends</h3>
    
    <div v-if="performanceMetrics && hasData" class="space-y-6">
      <!-- Chart Tabs -->
      <div class="flex space-x-1 bg-gray-100 rounded-lg p-1">
        <button
          v-for="tab in chartTabs"
          :key="tab.key"
          @click="switchTab(tab.key)"
          :class="[
            'flex-1 px-3 py-2 text-sm font-medium rounded-md transition-colors',
            activeTab === tab.key
              ? 'bg-white text-blue-600 shadow-sm'
              : 'text-gray-600 hover:text-gray-900'
          ]"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Chart Container -->
      <div class="h-64">
        <canvas ref="chartCanvas"></canvas>
      </div>

      <!-- Chart Legend/Info -->
      <div class="grid grid-cols-2 gap-4 text-sm">
        <div v-if="activeTab === 'revenue'">
          <p class="text-gray-600">Latest Revenue</p>
          <p class="font-semibold text-gray-900">
            K{{ formatNumber(getLatestValue(performanceMetrics.revenue_trend.data)) }}
          </p>
        </div>
        <div v-if="activeTab === 'profit'">
          <p class="text-gray-600">Latest Profit</p>
          <p class="font-semibold" :class="getLatestValue(performanceMetrics.profit_trend.data) >= 0 ? 'text-green-600' : 'text-red-600'">
            K{{ formatNumber(getLatestValue(performanceMetrics.profit_trend.data)) }}
          </p>
        </div>
        <div v-if="activeTab === 'members'">
          <p class="text-gray-600">Total Members</p>
          <p class="font-semibold text-gray-900">
            {{ formatNumber(getLatestValue(performanceMetrics.member_growth.data)) }}
          </p>
        </div>
        <div v-if="activeTab === 'health'">
          <p class="text-gray-600">Health Score</p>
          <p class="font-semibold text-gray-900">
            {{ getLatestValue(performanceMetrics.health_score_trend.data) }}/100
          </p>
        </div>
        
        <div>
          <p class="text-gray-600">Trend</p>
          <p class="font-semibold" :class="getTrendColor()">
            {{ getTrendDirection() }}
          </p>
        </div>
      </div>
    </div>
    
    <!-- Empty State -->
    <div v-else class="text-center py-8 text-gray-500">
      <ChartBarIcon class="h-12 w-12 mx-auto mb-2 text-gray-400" aria-hidden="true" />
      <p class="text-sm">Performance trends will be available after multiple reporting periods</p>
      <p class="text-xs mt-1">Charts will show historical data once we have 2+ reports</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { Chart, registerables, type ChartConfiguration } from 'chart.js';
import { ChartBarIcon } from '@heroicons/vue/24/outline';

Chart.register(...registerables);

interface PerformanceMetrics {
  revenue_trend: {
    labels: string[];
    data: number[];
  };
  profit_trend: {
    labels: string[];
    data: number[];
  };
  member_growth: {
    labels: string[];
    data: number[];
  };
  health_score_trend: {
    labels: string[];
    data: number[];
  };
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
    revenue: {
      data: metrics.revenue_trend,
      color: '#3b82f6',
      label: 'Revenue (K)',
    },
    profit: {
      data: metrics.profit_trend,
      color: '#10b981',
      label: 'Net Profit (K)',
    },
    members: {
      data: metrics.member_growth,
      color: '#8b5cf6',
      label: 'Total Members',
    },
    health: {
      data: metrics.health_score_trend,
      color: '#f59e0b',
      label: 'Health Score',
    },
  };

  const config = configs[tab];

  return {
    type: 'line',
    data: {
      labels: config.data.labels,
      datasets: [
        {
          label: config.label,
          data: config.data.data,
          borderColor: config.color,
          backgroundColor: `${config.color}20`,
          fill: true,
          tension: 0.4,
          pointRadius: 4,
          pointHoverRadius: 6,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false,
        },
        tooltip: {
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
        y: {
          beginAtZero: tab === 'health' || tab === 'members',
          max: tab === 'health' ? 100 : undefined,
          ticks: {
            callback: (value) => {
              if (tab === 'members') return value.toLocaleString();
              if (tab === 'health') return value;
              return `K${(value as number / 1000).toFixed(0)}k`;
            },
          },
        },
      },
    },
  };
};

const createChart = () => {
  if (!chartCanvas.value || !hasData.value) return;

  if (chartInstance) {
    chartInstance.destroy();
  }

  const config = getChartConfig(activeTab.value);
  chartInstance = new Chart(chartCanvas.value, config);
};

const switchTab = (tab: string) => {
  activeTab.value = tab;
  nextTick(() => createChart());
};

const formatNumber = (value: number): string => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value);
};

const getLatestValue = (data: number[]): number => {
  return data.length > 0 ? data[data.length - 1] : 0;
};

const getTrendDirection = (): string => {
  const metrics = props.performanceMetrics;
  if (!metrics) return 'N/A';

  const dataMap: Record<string, number[]> = {
    revenue: metrics.revenue_trend.data,
    profit: metrics.profit_trend.data,
    members: metrics.member_growth.data,
    health: metrics.health_score_trend.data,
  };

  const data = dataMap[activeTab.value];
  if (data.length < 2) return 'N/A';

  const latest = data[data.length - 1];
  const previous = data[data.length - 2];
  const change = previous > 0 ? ((latest - previous) / previous) * 100 : 0;

  if (change > 5) return `↑ +${change.toFixed(1)}%`;
  if (change < -5) return `↓ ${change.toFixed(1)}%`;
  return `→ ${change >= 0 ? '+' : ''}${change.toFixed(1)}%`;
};

const getTrendColor = (): string => {
  const direction = getTrendDirection();
  if (direction.startsWith('↑')) return 'text-green-600';
  if (direction.startsWith('↓')) return 'text-red-600';
  return 'text-gray-600';
};

onMounted(() => {
  nextTick(() => createChart());
});

onUnmounted(() => {
  if (chartInstance) {
    chartInstance.destroy();
  }
});

watch(() => props.performanceMetrics, () => {
  nextTick(() => createChart());
}, { deep: true });
</script>