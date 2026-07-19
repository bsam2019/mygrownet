<script setup lang="ts">
import { computed } from 'vue'
import { Line } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler,
  type ChartData,
  type ChartOptions
} from 'chart.js'

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
)

interface Dataset {
  label: string
  data: number[]
  color: string
  fill?: boolean
}

interface Props {
  labels: string[]
  datasets: Dataset[]
  height?: number
  showLegend?: boolean
  yAxisLabel?: string
  xAxisLabel?: string
  tension?: number
  showGrid?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  height: 300,
  showLegend: true,
  tension: 0.4,
  showGrid: true
})

const chartData = computed<ChartData<'line'>>(() => ({
  labels: props.labels,
  datasets: props.datasets.map(dataset => ({
    label: dataset.label,
    data: dataset.data,
    borderColor: dataset.color,
    backgroundColor: dataset.fill ? `${dataset.color}33` : 'transparent',
    borderWidth: 2,
    pointRadius: 4,
    pointHoverRadius: 6,
    pointBackgroundColor: dataset.color,
    pointBorderColor: '#ffffff',
    pointBorderWidth: 2,
    fill: dataset.fill || false,
    tension: props.tension
  }))
}))

const chartOptions = computed<ChartOptions<'line'>>(() => ({
  responsive: true,
  maintainAspectRatio: false,
  interaction: {
    mode: 'index',
    intersect: false
  },
  plugins: {
    legend: {
      display: props.showLegend,
      position: 'top',
      align: 'end',
      labels: {
        padding: 15,
        font: {
          size: 12,
          family: 'system-ui, -apple-system, sans-serif'
        },
        color: '#111827',
        usePointStyle: true,
        pointStyle: 'circle',
        boxWidth: 8,
        boxHeight: 8
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
          const label = context.dataset.label || ''
          const value = context.parsed.y
          return `${label}: ${value.toLocaleString()}`
        }
      }
    }
  },
  scales: {
    x: {
      grid: {
        display: props.showGrid,
        color: '#e5e7eb',
        drawBorder: false
      },
      ticks: {
        color: '#6b7280',
        font: {
          size: 11
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
      }
    },
    y: {
      grid: {
        display: props.showGrid,
        color: '#e5e7eb',
        drawBorder: false
      },
      ticks: {
        color: '#6b7280',
        font: {
          size: 11
        },
        callback: (value) => {
          if (typeof value === 'number') {
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

const isEmpty = computed(() => props.labels.length === 0 || props.datasets.length === 0)
</script>

<template>
  <div class="relative" :style="{ height: `${height}px` }">
    <div v-if="isEmpty" class="flex items-center justify-center h-full">
      <p class="text-sm text-gray-500">No data available</p>
    </div>
    <Line v-else :data="chartData" :options="chartOptions" />
  </div>
</template>
