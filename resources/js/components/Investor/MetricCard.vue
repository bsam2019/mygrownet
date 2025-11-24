<template>
  <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow">
    <div class="flex items-start justify-between mb-4">
      <div class="flex-1">
        <p class="text-sm font-medium text-gray-500 mb-1">{{ title }}</p>
        <p class="text-3xl font-bold text-gray-900">{{ value }}</p>
      </div>
      <div :class="iconColorClass" class="p-3 rounded-lg">
        <component :is="iconComponent" class="h-6 w-6" aria-hidden="true" />
      </div>
    </div>
    <div class="flex items-center">
      <component
        :is="trendDirection === 'up' ? ArrowTrendingUpIcon : ArrowTrendingDownIcon"
        :class="trendColorClass"
        class="h-4 w-4 mr-1"
        aria-hidden="true"
      />
      <span :class="trendColorClass" class="text-sm font-semibold">{{ trend }}</span>
      <span class="text-sm text-gray-500 ml-2">vs last month</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import {
  UsersIcon,
  CurrencyDollarIcon,
  ChartBarIcon,
  ArrowPathIcon,
  ArrowTrendingUpIcon,
  ArrowTrendingDownIcon
} from '@heroicons/vue/24/outline'

interface Props {
  title: string
  value: string | number
  trend: string
  trendDirection: 'up' | 'down'
  icon: 'users' | 'currency' | 'activity' | 'retention'
}

const props = defineProps<Props>()

const iconComponent = computed(() => {
  const icons = {
    users: UsersIcon,
    currency: CurrencyDollarIcon,
    activity: ChartBarIcon,
    retention: ArrowPathIcon
  }
  return icons[props.icon]
})

const iconColorClass = computed(() => {
  const colors = {
    users: 'bg-blue-100 text-blue-600',
    currency: 'bg-emerald-100 text-emerald-600',
    activity: 'bg-violet-100 text-violet-600',
    retention: 'bg-amber-100 text-amber-600'
  }
  return colors[props.icon]
})

const trendColorClass = computed(() => {
  return props.trendDirection === 'up' ? 'text-emerald-600' : 'text-red-600'
})
</script>
