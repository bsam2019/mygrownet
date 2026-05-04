<script setup lang="ts">
import { computed } from 'vue'
import { Doughnut } from 'vue-chartjs'
import {
  Chart as ChartJS,
  ArcElement,
  Tooltip,
  type ChartData,
  type ChartOptions
} from 'chart.js'

ChartJS.register(ArcElement, Tooltip)

interface Props {
  value: number
  max?: number
  label?: string
  height?: number
  colors?: {
    low: string
    medium: string
    high: string
  }
  thresholds?: {
    medium: number
    high: number
  }
}

const props = withDefaults(defineProps<Props>(), {
  max: 100,
  height: 200,
  colors: () => ({
    low: '#ef4444',
    medium: '#f59e0b',
    high: '#10b981'
  }),
  thresholds: () => ({
    medium: 50,
    high: 75
  })
})

const percentage = computed(() => {
  return Math.min((props.value / props.max) * 100, 100)
})

const gaugeColor = computed(() => {
  const pct = percentage.value
  if (pct >= props.thresholds.high) return props.colors.high
  if (pct >= props.thresholds.medium) return props.colors.medium
  return props.colors.low
})

const chartData = computed<ChartData<'doughnut'>>(() => ({
  datasets: [
    {
      data: [percentage.value, 100 - percentage.value],
      backgroundColor: [gaugeColor.value, '#e5e7eb'],
      borderWidth: 0,
      circumference: 180,
      rotation: 270
    }
  ]
}))

const chartOptions = computed<ChartOptions<'doughnut'>>(() => ({
  responsive: true,
  maintainAspectRatio: false,
  cutout: '75%',
  plugins: {
    legend: {
      display: false
    },
    tooltip: {
      enabled: false
    }
  }
}))
</script>

<template>
  <div class="relative" :style="{ height: `${height}px` }">
    <div class="relative h-full">
      <Doughnut :data="chartData" :options="chartOptions" />
      <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none" style="margin-top: 20%">
        <div class="text-center">
          <div class="text-3xl font-bold" :style="{ color: gaugeColor }">
            {{ percentage.toFixed(1) }}%
          </div>
          <div v-if="label" class="text-sm text-gray-600 mt-1">
            {{ label }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
