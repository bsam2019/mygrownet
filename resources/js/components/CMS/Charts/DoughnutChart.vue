<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { Doughnut } from 'vue-chartjs'
import {
  Chart as ChartJS,
  ArcElement,
  Tooltip,
  Legend,
  type ChartData,
  type ChartOptions
} from 'chart.js'

ChartJS.register(ArcElement, Tooltip, Legend)

interface Props {
  data: Record<string, number>
  colors?: string[]
  labels?: string[]
  title?: string
  height?: number
  showLegend?: boolean
  centerText?: string
}

const props = withDefaults(defineProps<Props>(), {
  colors: () => ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'],
  height: 300,
  showLegend: true
})

const chartData = computed<ChartData<'doughnut'>>(() => {
  const labels = props.labels || Object.keys(props.data)
  const values = Object.values(props.data)
  
  return {
    labels: labels.map(label => 
      label.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')
    ),
    datasets: [
      {
        data: values,
        backgroundColor: props.colors.slice(0, labels.length),
        borderColor: '#ffffff',
        borderWidth: 2,
        hoverOffset: 4
      }
    ]
  }
})

const chartOptions = computed<ChartOptions<'doughnut'>>(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: props.showLegend,
      position: 'bottom',
      labels: {
        padding: 15,
        font: {
          size: 12,
          family: 'system-ui, -apple-system, sans-serif'
        },
        color: '#111827',
        usePointStyle: true,
        pointStyle: 'circle'
      }
    },
    tooltip: {
      backgroundColor: '#1f2937',
      titleColor: '#ffffff',
      bodyColor: '#ffffff',
      padding: 12,
      cornerRadius: 8,
      displayColors: true,
      callbacks: {
        label: (context) => {
          const label = context.label || ''
          const value = context.parsed || 0
          const total = context.dataset.data.reduce((a: number, b: number) => a + b, 0)
          const percentage = ((value / total) * 100).toFixed(1)
          return `${label}: ${value} (${percentage}%)`
        }
      }
    }
  },
  cutout: '65%'
}))

const isEmpty = computed(() => Object.keys(props.data).length === 0)
</script>

<template>
  <div class="relative" :style="{ height: `${height}px` }">
    <div v-if="isEmpty" class="flex items-center justify-center h-full">
      <p class="text-sm text-gray-500">No data available</p>
    </div>
    <div v-else class="relative h-full">
      <Doughnut :data="chartData" :options="chartOptions" />
      <div v-if="centerText" class="absolute inset-0 flex items-center justify-center pointer-events-none">
        <div class="text-center">
          <div class="text-2xl font-bold text-gray-900">{{ centerText }}</div>
        </div>
      </div>
    </div>
  </div>
</template>
