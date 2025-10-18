<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <div class="flex items-center">
      <div class="flex-shrink-0">
        <div :class="[
          'w-8 h-8 rounded-md flex items-center justify-center',
          colorClasses[color]
        ]">
          <component :is="iconComponent" class="w-5 h-5" />
        </div>
      </div>
      <div class="ml-5 w-0 flex-1">
        <dl>
          <dt class="text-sm font-medium text-gray-500 truncate">{{ title }}</dt>
          <dd class="flex items-baseline">
            <div class="text-2xl font-semibold text-gray-900">{{ value }}</div>
            <div v-if="subtitle" class="ml-2 flex items-baseline text-sm text-gray-600">
              {{ subtitle }}
            </div>
          </dd>
        </dl>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { 
  UsersIcon, 
  ArrowTrendingUpIcon, 
  CurrencyDollarIcon, 
  TrophyIcon,
  ChartBarIcon,
  BanknotesIcon
} from '@heroicons/vue/24/outline'

interface Props {
  title: string
  value: string | number
  subtitle?: string
  icon: string
  color: 'blue' | 'green' | 'indigo' | 'purple' | 'amber' | 'red'
}

const props = defineProps<Props>()

const colorClasses = {
  blue: 'bg-blue-100 text-blue-600',
  green: 'bg-green-100 text-green-600',
  indigo: 'bg-indigo-100 text-indigo-600',
  purple: 'bg-purple-100 text-purple-600',
  amber: 'bg-amber-100 text-amber-600',
  red: 'bg-red-100 text-red-600',
}

const iconComponent = computed(() => {
  const icons = {
    'users': UsersIcon,
    'trending-up': ArrowTrendingUpIcon,
    'dollar-sign': CurrencyDollarIcon,
    'award': TrophyIcon,
    'chart-bar': ChartBarIcon,
    'banknotes': BanknotesIcon,
  }
  return icons[props.icon as keyof typeof icons] || ChartBarIcon
})
</script>