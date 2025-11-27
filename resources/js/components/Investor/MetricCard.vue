<template>
  <div class="bg-gray-50 rounded-xl p-4 hover:bg-gray-100 transition-colors duration-200">
    <div class="flex items-center justify-between mb-2">
      <div :class="iconBgClass" class="w-8 h-8 rounded-lg flex items-center justify-center">
        <UsersIcon v-if="icon === 'users'" class="h-4 w-4" :class="iconColorClass" aria-hidden="true" />
        <CurrencyDollarIcon v-else-if="icon === 'currency'" class="h-4 w-4" :class="iconColorClass" aria-hidden="true" />
        <BoltIcon v-else-if="icon === 'activity'" class="h-4 w-4" :class="iconColorClass" aria-hidden="true" />
        <ArrowPathIcon v-else-if="icon === 'refresh'" class="h-4 w-4" :class="iconColorClass" aria-hidden="true" />
        <ChartBarIcon v-else class="h-4 w-4" :class="iconColorClass" aria-hidden="true" />
      </div>
      <span 
        v-if="trend"
        :class="[
          'text-xs font-semibold px-1.5 py-0.5 rounded',
          trendUp ? 'text-emerald-700 bg-emerald-100' : 'text-red-700 bg-red-100'
        ]"
      >
        {{ trend }}
      </span>
    </div>
    <p class="text-xs text-gray-500 font-medium mb-0.5">{{ label }}</p>
    <p class="text-lg font-bold text-gray-900">{{ value }}</p>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import {
  UsersIcon,
  CurrencyDollarIcon,
  BoltIcon,
  ArrowPathIcon,
  ChartBarIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps<{
  label: string;
  value: string;
  trend?: string;
  trendUp?: boolean;
  icon?: 'users' | 'currency' | 'activity' | 'refresh' | 'chart';
}>();

const iconBgClass = computed(() => {
  return 'bg-white';
});

const iconColorClass = computed(() => {
  return 'text-gray-600';
});
</script>
