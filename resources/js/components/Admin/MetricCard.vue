<template>
  <div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center justify-between">
      <div class="flex-1">
        <p class="text-sm font-medium text-gray-600">{{ title }}</p>
        <p class="mt-2 text-3xl font-semibold text-gray-900">{{ value }}</p>
        <p v-if="subtitle" class="mt-1 text-sm text-gray-500">{{ subtitle }}</p>
        
        <div v-if="change !== undefined" class="mt-2 flex items-center text-sm">
          <span
            :class="trend === 'up' ? 'text-green-600' : 'text-red-600'"
            class="flex items-center font-medium"
          >
            <component
              :is="trend === 'up' ? ArrowTrendingUpIcon : ArrowTrendingDownIcon"
              class="h-4 w-4 mr-1"
              aria-hidden="true"
            />
            {{ Math.abs(change).toFixed(1) }}%
          </span>
          <span class="ml-2 text-gray-500">vs previous period</span>
        </div>
      </div>
      
      <div :class="getIconColorClass(color)" class="flex-shrink-0">
        <component :is="getIcon(icon)" class="h-8 w-8" aria-hidden="true" />
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  CurrencyDollarIcon,
  ChartBarIcon,
  ArrowTrendingDownIcon,
  ArrowTrendingUpIcon,
  BanknotesIcon,
  UserGroupIcon,
  ShoppingCartIcon,
  DocumentTextIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    required: true
  },
  subtitle: {
    type: String,
    default: null
  },
  change: {
    type: Number,
    default: undefined
  },
  trend: {
    type: String,
    default: 'up',
    validator: (value) => ['up', 'down'].includes(value)
  },
  icon: {
    type: String,
    default: 'chart-bar'
  },
  color: {
    type: String,
    default: 'blue',
    validator: (value) => ['blue', 'green', 'orange', 'purple', 'red', 'gray'].includes(value)
  }
});

const getIcon = (iconName) => {
  const icons = {
    'currency-dollar': CurrencyDollarIcon,
    'chart-bar': ChartBarIcon,
    'arrow-trending-down': ArrowTrendingDownIcon,
    'arrow-trending-up': ArrowTrendingUpIcon,
    'banknotes': BanknotesIcon,
    'user-group': UserGroupIcon,
    'shopping-cart': ShoppingCartIcon,
    'document-text': DocumentTextIcon
  };
  return icons[iconName] || ChartBarIcon;
};

const getIconColorClass = (color) => {
  const classes = {
    blue: 'text-blue-600 bg-blue-100 p-3 rounded-lg',
    green: 'text-green-600 bg-green-100 p-3 rounded-lg',
    orange: 'text-orange-600 bg-orange-100 p-3 rounded-lg',
    purple: 'text-purple-600 bg-purple-100 p-3 rounded-lg',
    red: 'text-red-600 bg-red-100 p-3 rounded-lg',
    gray: 'text-gray-600 bg-gray-100 p-3 rounded-lg'
  };
  return classes[color] || classes.blue;
};
</script>
