<template>
  <div class="bg-white rounded-xl shadow-lg p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-900">{{ roundName }}</h3>
      <span :class="statusClass" class="px-3 py-1 rounded-full text-xs font-medium">
        {{ statusLabel }}
      </span>
    </div>

    <div class="space-y-4">
      <div>
        <div class="flex justify-between text-sm mb-2">
          <span class="text-gray-600">Fundraising Progress</span>
          <span class="font-semibold text-gray-900">{{ progressPercentage }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3">
          <div 
            class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-500"
            :style="{ width: `${Math.min(progressPercentage, 100)}%` }"
          ></div>
        </div>
        <div class="flex justify-between text-xs text-gray-500 mt-1">
          <span>K{{ formatNumber(raisedAmount) }} raised</span>
          <span>K{{ formatNumber(goalAmount) }} goal</span>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200">
        <div>
          <p class="text-xs text-gray-500 mb-1">Company Valuation</p>
          <p class="text-lg font-bold text-gray-900">K{{ formatNumber(valuation) }}</p>
        </div>
        <div>
          <p class="text-xs text-gray-500 mb-1">Total Investors</p>
          <p class="text-lg font-bold text-gray-900">{{ totalInvestors }}</p>
        </div>
      </div>

      <div class="bg-blue-50 rounded-lg p-4">
        <div class="flex items-start gap-3">
          <InformationCircleIcon class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" aria-hidden="true" />
          <div>
            <p class="text-sm font-medium text-blue-900">Your Position</p>
            <p class="text-xs text-blue-700 mt-1">
              You own {{ equityPercentage }}% of the company, valued at K{{ formatNumber(yourValue) }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { InformationCircleIcon } from '@heroicons/vue/24/outline';

interface Props {
  roundName: string;
  valuation: number;
  goalAmount: number;
  raisedAmount: number;
  progressPercentage: number;
  totalInvestors: number;
  status: string;
  statusLabel: string;
  equityPercentage: number;
}

const props = defineProps<Props>();

const formatNumber = (value: number): string => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value);
};

const yourValue = computed(() => {
  return props.valuation * (props.equityPercentage / 100);
});

const statusClass = computed(() => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    closed: 'bg-gray-100 text-gray-800',
    upcoming: 'bg-blue-100 text-blue-800',
  };
  return classes[props.status as keyof typeof classes] || classes.active;
});
</script>
