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

interface Dataset {
  label: string
  data: number[]
  color: string
}

interface Props {
  labels: string[]
  datasets: Dataset[]
  height?: number
  showLegend?: boolean
  yAxisLabel?: string
  xAxisLabel?: string
  stacked?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  height: 300,
  showLegend: true,
  stacked: true
})

const chartData = computed<ChartData<'bar'>>(() => ({
  labels: props.labels,
  datasets: props.datasets.map(dataset => ({
    label: dataset.label,
    data: dataset.data,
    backgroundColor: dataset.color,
    borderColor: dataset.color,
    borderWidth: 0,
    borderRadius: props.stacked ? 0 : 6
  }))
}))

const chartOptions = computed<ChartOptions<'bar'>>(() => ({
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
        },
        footer: (tooltipItems) => {
          if (props.stacked) {
            const total = tooltipItems.reduce((sum, item) => sum + item.parsed.y, 0)
            return `Total: ${total.toLocaleString()}`
          }
          return ''
        }
      }
    }
  },
  scales: {
    x: {
      stacked: props.stacked,
      grid: {
        display: false,
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
      stacked: props.stacked,
      grid: {
        display: true,
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
    <Bar v-else :data="chartData" :options="chartOptions" />
  </div>
</template>
