<template>
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-all duration-300 group">
    <div class="flex items-start justify-between">
      <div class="flex-1">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">{{ title }}</p>
        <p class="text-xl font-bold text-gray-900">{{ value }}</p>
      </div>
      <div :class="iconBgClass" class="w-10 h-10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
        <CalendarIcon v-if="icon === 'calendar'" class="h-5 w-5" :class="iconColorClass" aria-hidden="true" />
        <CurrencyDollarIcon v-else-if="icon === 'currency'" class="h-5 w-5" :class="iconColorClass" aria-hidden="true" />
        <UsersIcon v-else-if="icon === 'users'" class="h-5 w-5" :class="iconColorClass" aria-hidden="true" />
        <ChartBarIcon v-else-if="icon === 'chart'" class="h-5 w-5" :class="iconColorClass" aria-hidden="true" />
        <ArrowTrendingUpIcon v-else class="h-5 w-5" :class="iconColorClass" aria-hidden="true" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import {
  CalendarIcon,
  CurrencyDollarIcon,
  UsersIcon,
  ChartBarIcon,
  ArrowTrendingUpIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps<{
  title: string;
  value: string;
  icon?: 'calendar' | 'currency' | 'users' | 'chart' | 'trend';
  color?: 'blue' | 'emerald' | 'violet' | 'amber' | 'gray';
}>();

const iconBgClass = computed(() => {
  const colors = {
    blue: 'bg-blue-50',
    emerald: 'bg-emerald-50',
    violet: 'bg-violet-50',
    amber: 'bg-amber-50',
    gray: 'bg-gray-50',
  };
  return colors[props.color || 'blue'];
});

const iconColorClass = computed(() => {
  const colors = {
    blue: 'text-blue-600',
    emerald: 'text-emerald-600',
    violet: 'text-violet-600',
    amber: 'text-amber-600',
    gray: 'text-gray-600',
  };
  return colors[props.color || 'blue'];
});
</script>
