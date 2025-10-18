<template>
  <div class="h-64">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, nextTick } from 'vue'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

interface Props {
  data: {
    labels: string[]
    investments: number[]
    counts: number[]
    growth_rate: number
  }
  metric: 'investments' | 'counts'
}

const props = defineProps<Props>()

const chartCanvas = ref<HTMLCanvasElement>()
let chartInstance: Chart | null = null

const createChart = async () => {
  await nextTick()
  
  if (!chartCanvas.value) return

  // Destroy existing chart
  if (chartInstance) {
    chartInstance.destroy()
  }

  const ctx = chartCanvas.value.getContext('2d')
  if (!ctx) return

  const chartData = props.metric === 'investments' ? props.data.investments : props.data.counts
  const label = props.metric === 'investments' ? 'Investment Value (K)' : 'Investment Count'

  chartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels: props.data.labels,
      datasets: [{
        label,
        data: chartData,
        borderColor: props.metric === 'investments' ? '#3b82f6' : '#10b981',
        backgroundColor: props.metric === 'investments' ? '#3b82f620' : '#10b98120',
        borderWidth: 2,
        fill: true,
        tension: 0.4,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: {
            color: '#f3f4f6'
          }
        },
        x: {
          grid: {
            color: '#f3f4f6'
          }
        }
      },
      elements: {
        point: {
          radius: 4,
          hoverRadius: 6
        }
      }
    }
  })
}

onMounted(() => {
  createChart()
})

watch(() => [props.data, props.metric], () => {
  createChart()
}, { deep: true })
</script>