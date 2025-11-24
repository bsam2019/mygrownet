<template>
  <div class="bg-white rounded-xl shadow-lg p-6">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-lg font-semibold text-gray-900">Platform Performance</h3>
      <ChartBarIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
    </div>

    <div class="grid grid-cols-2 gap-4 mb-6">
      <div class="bg-gray-50 rounded-lg p-4">
        <div class="flex items-center gap-2 mb-2">
          <UsersIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
          <p class="text-xs text-gray-500 font-medium">Total Members</p>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ formatNumber(totalMembers) }}</p>
      </div>

      <div class="bg-gray-50 rounded-lg p-4">
        <div class="flex items-center gap-2 mb-2">
          <BanknotesIcon class="h-5 w-5 text-green-600" aria-hidden="true" />
          <p class="text-xs text-gray-500 font-medium">Monthly Revenue</p>
        </div>
        <p class="text-2xl font-bold text-gray-900">K{{ formatNumber(monthlyRevenue) }}</p>
      </div>

      <div class="bg-gray-50 rounded-lg p-4">
        <div class="flex items-center gap-2 mb-2">
          <FireIcon class="h-5 w-5 text-orange-600" aria-hidden="true" />
          <p class="text-xs text-gray-500 font-medium">Active Rate</p>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ activeRate }}%</p>
      </div>

      <div class="bg-gray-50 rounded-lg p-4">
        <div class="flex items-center gap-2 mb-2">
          <ArrowTrendingUpIcon class="h-5 w-5 text-purple-600" aria-hidden="true" />
          <p class="text-xs text-gray-500 font-medium">Retention</p>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ retentionRate }}%</p>
      </div>
    </div>

    <div class="pt-4 border-t border-gray-200">
      <p class="text-xs text-gray-500 mb-3 font-medium">Revenue Growth (Last 12 Months)</p>
      <div class="h-32">
        <canvas ref="chartCanvas"></canvas>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { 
  UsersIcon, 
  BanknotesIcon, 
  FireIcon, 
  ArrowTrendingUpIcon,
  ChartBarIcon 
} from '@heroicons/vue/24/outline';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

interface Props {
  totalMembers: number;
  monthlyRevenue: number;
  activeRate: number;
  retentionRate: number;
  revenueGrowth: {
    labels: string[];
    data: number[];
  };
}

const props = defineProps<Props>();
const chartCanvas = ref<HTMLCanvasElement | null>(null);

const formatNumber = (value: number): string => {
  return new Intl.NumberFormat('en-US').format(value);
};

onMounted(() => {
  if (chartCanvas.value) {
    new Chart(chartCanvas.value, {
      type: 'line',
      data: {
        labels: props.revenueGrowth.labels,
        datasets: [{
          label: 'Revenue',
          data: props.revenueGrowth.data,
          borderColor: '#2563eb',
          backgroundColor: 'rgba(37, 99, 235, 0.1)',
          tension: 0.4,
          fill: true,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            callbacks: {
              label: (context) => `K${context.parsed.y.toLocaleString()}`
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: (value) => `K${value}`
            }
          }
        }
      }
    });
  }
});
</script>
