<script setup lang="ts">
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend } from 'chart.js';
import { computed } from 'vue';
import { formatCurrency } from '@/utils/format';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend);

const props = defineProps<{
    data: {
        investments: Array<{ date: string; total: number }>;
        withdrawals: Array<{ date: string; total: number }>;
    };
}>();

const chartData = computed(() => ({
    labels: props.data.investments.map(item => item.date),
    datasets: [
        {
            label: 'Investments',
            data: props.data.investments.map(item => item.total),
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4
        },
        {
            label: 'Withdrawals',
            data: props.data.withdrawals.map(item => item.total),
            borderColor: 'rgb(239, 68, 68)',
            backgroundColor: 'rgba(239, 68, 68, 0.1)',
            tension: 0.4
        }
    ]
}));

const options = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                callback: (value: number) => formatCurrency(value)
            }
        }
    },
    plugins: {
        legend: {
            position: 'top' as const
        }
    }
};
</script>

<template>
    <div class="h-64">
        <Line :data="chartData" :options="options" />
    </div>
</template>
