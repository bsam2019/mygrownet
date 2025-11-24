<template>
  <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl shadow-xl p-6 text-white">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold">Your Investment</h3>
      <span :class="statusClass" class="px-3 py-1 rounded-full text-xs font-medium">
        {{ statusLabel }}
      </span>
    </div>

    <div class="space-y-4">
      <div>
        <p class="text-blue-100 text-sm mb-1">Current Value</p>
        <p class="text-3xl font-bold">K{{ formatNumber(currentValue) }}</p>
      </div>

      <div class="grid grid-cols-2 gap-4 pt-4 border-t border-blue-500">
        <div>
          <p class="text-blue-100 text-xs mb-1">Initial Investment</p>
          <p class="text-lg font-semibold">K{{ formatNumber(initialInvestment) }}</p>
        </div>
        <div>
          <p class="text-blue-100 text-xs mb-1">Return (ROI)</p>
          <p class="text-lg font-semibold" :class="roiClass">
            {{ roiPercentage >= 0 ? '+' : '' }}{{ roiPercentage }}%
          </p>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <p class="text-blue-100 text-xs mb-1">Equity Ownership</p>
          <p class="text-lg font-semibold">{{ equityPercentage }}%</p>
        </div>
        <div>
          <p class="text-blue-100 text-xs mb-1">Holding Period</p>
          <p class="text-lg font-semibold">{{ holdingMonths }} months</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  initialInvestment: number;
  currentValue: number;
  roiPercentage: number;
  equityPercentage: number;
  holdingMonths: number;
  status: string;
  statusLabel: string;
}

const props = defineProps<Props>();

const formatNumber = (value: number): string => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value);
};

const statusClass = computed(() => {
  const classes = {
    ciu: 'bg-blue-400 text-blue-900',
    shareholder: 'bg-green-400 text-green-900',
    exited: 'bg-gray-400 text-gray-900',
  };
  return classes[props.status as keyof typeof classes] || classes.ciu;
});

const roiClass = computed(() => {
  return props.roiPercentage >= 0 ? 'text-green-300' : 'text-red-300';
});
</script>
