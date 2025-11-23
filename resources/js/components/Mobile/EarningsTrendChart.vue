<template>
  <div class="bg-white rounded-xl shadow-sm p-4">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-sm font-bold text-gray-900">Earnings Trend</h3>
      <span class="text-xs text-gray-500">Last 6 months</span>
    </div>
    
    <div v-if="hasData" class="space-y-3">
      <!-- Chart Area -->
      <div class="relative h-32 flex items-end justify-between gap-1">
        <div
          v-for="(month, index) in chartData"
          :key="index"
          class="flex-1 flex flex-col items-center gap-1"
        >
          <!-- Bar -->
          <div class="w-full flex flex-col justify-end" style="height: 100px;">
            <div
              class="w-full rounded-t-lg transition-all duration-300 hover:opacity-80 cursor-pointer"
              :class="getBarColor(month.amount)"
              :style="{ height: getBarHeight(month.amount) + '%' }"
              @click="showMonthDetails(month)"
            >
            </div>
          </div>
          <!-- Label -->
          <span class="text-xs text-gray-500 font-medium">{{ month.label }}</span>
        </div>
      </div>
      
      <!-- Summary Stats -->
      <div class="grid grid-cols-3 gap-2 pt-3 border-t">
        <div class="text-center">
          <p class="text-xs text-gray-500">Average</p>
          <p class="text-sm font-bold text-gray-900">K{{ formatCurrency(average) }}</p>
        </div>
        <div class="text-center">
          <p class="text-xs text-gray-500">Highest</p>
          <p class="text-sm font-bold text-green-600">K{{ formatCurrency(highest) }}</p>
        </div>
        <div class="text-center">
          <p class="text-xs text-gray-500">Total</p>
          <p class="text-sm font-bold text-blue-600">K{{ formatCurrency(total) }}</p>
        </div>
      </div>
    </div>
    
    <div v-else class="text-center py-8 text-gray-400">
      <svg class="h-12 w-12 mx-auto mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
      </svg>
      <p class="text-sm">No earnings data yet</p>
      <p class="text-xs mt-1">Start earning to see your trend</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface MonthData {
  month: string;
  label: string;
  amount: number;
}

interface Props {
  earnings: MonthData[];
}

const props = defineProps<Props>();

const hasData = computed(() => props.earnings && props.earnings.length > 0);

const chartData = computed(() => {
  if (!hasData.value) return [];
  return props.earnings.slice(-6); // Last 6 months
});

const maxAmount = computed(() => {
  if (!hasData.value) return 0;
  return Math.max(...chartData.value.map(m => m.amount));
});

const average = computed(() => {
  if (!hasData.value) return 0;
  const sum = chartData.value.reduce((acc, m) => acc + m.amount, 0);
  return Math.round(sum / chartData.value.length);
});

const highest = computed(() => {
  if (!hasData.value) return 0;
  return maxAmount.value;
});

const total = computed(() => {
  if (!hasData.value) return 0;
  return chartData.value.reduce((acc, m) => acc + m.amount, 0);
});

const getBarHeight = (amount: number): number => {
  if (maxAmount.value === 0) return 0;
  return Math.max((amount / maxAmount.value) * 100, 5); // Minimum 5% height for visibility
};

const getBarColor = (amount: number): string => {
  const percentage = maxAmount.value > 0 ? (amount / maxAmount.value) * 100 : 0;
  if (percentage >= 80) return 'bg-gradient-to-t from-green-500 to-green-400';
  if (percentage >= 50) return 'bg-gradient-to-t from-blue-500 to-blue-400';
  if (percentage >= 25) return 'bg-gradient-to-t from-indigo-500 to-indigo-400';
  return 'bg-gradient-to-t from-gray-400 to-gray-300';
};

const formatCurrency = (amount: number): string => {
  return amount.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
};

const showMonthDetails = (month: MonthData) => {
  // Could emit event or show tooltip
  console.log('Month details:', month);
};
</script>
