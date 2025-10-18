<template>
  <div class="chart-container">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script>
import { Chart } from 'chart.js/auto';
import { ref, onMounted, onUnmounted, watch } from 'vue';

export default {
  name: 'LineChart',
  props: {
    data: {
      type: Array,
      required: true,
      default: () => []
    },
    options: {
      type: Object,
      default: () => ({})
    }
  },
  setup(props) {
    const chartCanvas = ref(null);
    let chart = null;

    const createChart = () => {
      if (chartCanvas.value) {
        const ctx = chartCanvas.value.getContext('2d');
        
        // Prepare chart data
        const chartData = {
          labels: props.data.map(item => item.date),
          datasets: [
            {
              label: 'Performance',
              data: props.data.map(item => item.value),
              borderColor: 'rgb(59, 130, 246)',
              backgroundColor: 'rgba(59, 130, 246, 0.1)',
              tension: 0.4,
              fill: true
            }
          ]
        };

        // Default options
        const defaultOptions = {
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
                color: 'rgba(0, 0, 0, 0.05)'
              }
            },
            x: {
              grid: {
                display: false
              }
            }
          }
        };

        // Merge default options with provided options
        const chartOptions = {
          ...defaultOptions,
          ...props.options
        };

        // Create chart
        chart = new Chart(ctx, {
          type: 'line',
          data: chartData,
          options: chartOptions
        });
      }
    };

    // Watch for data changes and update chart
    watch(() => props.data, () => {
      if (chart) {
        chart.destroy();
      }
      createChart();
    }, { deep: true });

    // Initialize chart on mount
    onMounted(() => {
      createChart();
    });

    // Clean up chart on unmount
    onUnmounted(() => {
      if (chart) {
        chart.destroy();
      }
    });

    return {
      chartCanvas
    };
  }
};
</script>

<style scoped>
.chart-container {
  position: relative;
  height: 100%;
  width: 100%;
}
</style>