<template>
    <div class="w-full h-full">
        <canvas ref="chartCanvas"></canvas>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';

const props = defineProps<{
    data: {
        labels: string[];
        datasets: Array<{
            label: string;
            data: number[];
            borderColor?: string;
            backgroundColor?: string;
        }>;
    };
    color?: string;
}>();

const chartCanvas = ref<HTMLCanvasElement | null>(null);
let chartInstance: any = null;

const createChart = async () => {
    if (!chartCanvas.value) return;

    // Simple canvas-based chart (fallback if Chart.js not available)
    const ctx = chartCanvas.value.getContext('2d');
    if (!ctx) return;

    const canvas = chartCanvas.value;
    const width = canvas.width = canvas.offsetWidth;
    const height = canvas.height = canvas.offsetHeight;

    // Clear canvas
    ctx.clearRect(0, 0, width, height);

    // Get data
    const dataset = props.data.datasets[0];
    const values = dataset.data;
    const max = Math.max(...values);
    const min = Math.min(...values);
    const range = max - min || 1;

    // Draw line
    ctx.beginPath();
    ctx.strokeStyle = dataset.borderColor || '#2563eb';
    ctx.lineWidth = 2;

    values.forEach((value, index) => {
        const x = (index / (values.length - 1)) * width;
        const y = height - ((value - min) / range) * (height - 40) - 20;
        
        if (index === 0) {
            ctx.moveTo(x, y);
        } else {
            ctx.lineTo(x, y);
        }
    });

    ctx.stroke();

    // Draw points
    ctx.fillStyle = dataset.borderColor || '#2563eb';
    values.forEach((value, index) => {
        const x = (index / (values.length - 1)) * width;
        const y = height - ((value - min) / range) * (height - 40) - 20;
        
        ctx.beginPath();
        ctx.arc(x, y, 4, 0, 2 * Math.PI);
        ctx.fill();
    });

    // Draw labels
    ctx.fillStyle = '#6b7280';
    ctx.font = '10px sans-serif';
    ctx.textAlign = 'center';
    
    props.data.labels.forEach((label, index) => {
        if (index % Math.ceil(props.data.labels.length / 6) === 0) {
            const x = (index / (values.length - 1)) * width;
            ctx.fillText(label, x, height - 5);
        }
    });
};

onMounted(() => {
    createChart();
});

watch(() => props.data, () => {
    createChart();
}, { deep: true });
</script>
