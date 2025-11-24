<template>
  <div class="flex items-start justify-between">
    <div class="flex-1">
      <p class="text-sm font-medium text-gray-700 mb-1">{{ label }}</p>
      <p class="text-2xl font-bold text-gray-900">{{ value }}</p>
      <p v-if="note" class="text-xs text-gray-500 mt-1">{{ note }}</p>
    </div>
    <div :class="statusColorClass" class="p-2 rounded-lg">
      <component :is="statusIcon" class="h-5 w-5" aria-hidden="true" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { CheckCircleIcon, MinusCircleIcon } from '@heroicons/vue/24/solid'

interface Props {
  label: string
  value: string
  status: 'positive' | 'neutral' | 'negative'
  note?: string
}

const props = defineProps<Props>()

const statusColorClass = computed(() => {
  const colors = {
    positive: 'bg-emerald-100 text-emerald-600',
    neutral: 'bg-gray-100 text-gray-600',
    negative: 'bg-red-100 text-red-600'
  }
  return colors[props.status]
})

const statusIcon = computed(() => {
  return props.status === 'positive' ? CheckCircleIcon : MinusCircleIcon
})
</script>
