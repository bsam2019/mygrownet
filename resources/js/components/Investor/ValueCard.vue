<template>
  <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all hover:-translate-y-1">
    <div class="flex items-start mb-4">
      <div class="flex-shrink-0 p-3 bg-blue-100 rounded-lg">
        <component :is="iconComponent" class="h-6 w-6 text-blue-600" aria-hidden="true" />
      </div>
    </div>
    <h3 class="text-xl font-bold text-gray-900 mb-2">{{ title }}</h3>
    <p class="text-gray-600 leading-relaxed">{{ description }}</p>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import {
  ChartBarIcon,
  GlobeAltIcon,
  ArrowTrendingUpIcon,
  ShieldCheckIcon,
  HeartIcon,
  UserGroupIcon
} from '@heroicons/vue/24/outline'

interface Props {
  title: string
  description: string
  icon: 'chart-bar' | 'globe' | 'trending-up' | 'shield-check' | 'heart' | 'user-group'
}

const props = defineProps<Props>()

const iconComponent = computed(() => {
  const icons = {
    'chart-bar': ChartBarIcon,
    'globe': GlobeAltIcon,
    'trending-up': ArrowTrendingUpIcon,
    'shield-check': ShieldCheckIcon,
    'heart': HeartIcon,
    'user-group': UserGroupIcon
  }
  return icons[props.icon]
})
</script>
