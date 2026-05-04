<script setup lang="ts">
import { computed } from 'vue'
import { Bar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
  type ChartData,
  type ChartOptions
} from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend)

interface Props {
  labels: string[]
  data: number[]
  color?: string
  height?: number
  horizontal?: boolean
  showLegend?: boolean
  yAxisLabel?: string
  xAxisLabel?: string
  maxBars?: number
}

const props = withDefaults(defineProps<Props>(), {
  color: '#3b82f6',
  height: 300,
  horizontal: false,
  showLegend: false,
  maxBars: 10
})

const chartData = computed<ChartData<'bar'>>(() => {
  // Limit to maxBars if specified
  const limitedLabels = props.labels.slice(0, props.maxBars)
  const limitedData = props.data.slice(0, props.maxBars)
  
  return {
    labels: limitedLabels,
    datasets: [
      {
        data: limitedData,
        backgroundColor: props.color,
        borderColor: props.color,
        borderWidth: 0,
        borderRadius: 6,
        barThickness: props.horizontal ? undefined : 40,
        maxBarThickness: 60
      }
    ]
  }
})

const chartOptions = computed<ChartOptions<'bar'>>(() => ({
  indexAxis: props.horizontal ? 'y' : 'x',
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: props.showLegend
    },
    tooltip: {
      backgroundColor: '#1f2937',
      titleColor: '#ffffff',
      bodyColor: '#ffffff',
      padding: 12,
      cornerRadius: 8,
      displayColors: false,
      callbacks: {
        label: (context) => {
          const value = context.parsed[props.horizontal ? 'x' : 'y']
          return `${value.toLocaleString()}`
        }
      }
    }
  },
  scales: {
    x: {
      grid: {
        display: !props.horizontal,
        color: '#e5e7eb',
        drawBorder: false
      },
      ticks: {
        color: '#6b7280',
        font: {
          size: 11
        },
        callback: function(value) {
          if (props.horizontal && typeof value === 'number') {
            return value.toLocaleString()
          }
          return value
        }
      },
      title: {
        display: !!props.xAxisLabel,
        text: props.xAxisLabel,
        color: '#374151',
        font: {
          size: 12,
          weight: '600'
        }
      },
      beginAtZero: true
    },
    y: {
      grid: {
        display: props.horizontal,
        color: '#e5e7eb',
        drawBorder: false
      },
      ticks: {
        color: '#6b7280',
        font: {
          size: 11
        },
        callback: function(value) {
          if (!props.horizontal && typeof value === 'number') {
            return value.toLocaleString()
          }
          return value
        }
      },
      title: {
        display: !!props.yAxisLabel,
        text: props.yAxisLabel,
        color: '#374151',
        font: {
          size: 12,
          weight: '600'
        }
      },
      beginAtZero: true
    }
  }
}))

const isEmpty = computed(() => props.labels.length === 0 || props.data.length === 0)
</script>

<template>
  <div class="relative" :style="{ height: `${height}px` }">
    <div v-if="isEmpty" class="flex items-center justify-center h-full">
      <p class="text-sm text-gray-500">No data available</p>
    </div>
    <Bar v-else :data="chartData" :options="chartOptions" />
  </div>
</template>
