<template>
  <div
    class="relative p-4 border border-gray-200 rounded-lg hover:border-blue-300 transition-colors"
    :class="{ 'opacity-60': status === 'coming-soon' }"
  >
    <div class="flex items-start space-x-3">
      <div class="flex-shrink-0">
        <component
          :is="iconComponent"
          class="h-8 w-8"
          :class="status === 'available' ? 'text-blue-600' : 'text-gray-400'"
          aria-hidden="true"
        />
      </div>
      <div class="flex-1 min-w-0">
        <h3 class="text-sm font-medium text-gray-900">{{ title }}</h3>
        <p class="mt-1 text-sm text-gray-500">{{ description }}</p>
        <div class="mt-3">
          <button
            v-if="status === 'available'"
            class="text-sm font-medium text-blue-600 hover:text-blue-500"
            aria-label="View document"
          >
            View Document →
          </button>
          <span
            v-else
            class="text-sm font-medium text-gray-400"
          >
            Coming Soon
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import {
  DocumentTextIcon,
  BuildingOfficeIcon,
  DocumentChartBarIcon,
  ShieldCheckIcon,
} from '@heroicons/vue/24/outline'

interface Props {
  title: string
  description: string
  icon: string
  status: 'available' | 'coming-soon'
}

const props = defineProps<Props>()

const iconComponent = computed(() => {
  const icons: Record<string, any> = {
    DocumentTextIcon,
    BuildingOfficeIcon,
    DocumentChartBarIcon,
    ShieldCheckIcon,
  }
  return icons[props.icon] || DocumentTextIcon
})
</script>
