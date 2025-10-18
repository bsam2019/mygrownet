<template>
    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0 mb-4 sm:mb-6">
            <h3 class="text-base sm:text-lg font-medium text-gray-900">Investment Trends</h3>
            <div class="flex items-center space-x-2">
                <select
                    v-model="selectedPeriod"
                    class="w-full sm:w-auto rounded-md border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500"
                    @change="$emit('update:period', selectedPeriod)"
                >
                    <option value="week">Last Week</option>
                    <option value="month">Last Month</option>
                    <option value="quarter">Last Quarter</option>
                    <option value="year">Last Year</option>
                </select>
                <button 
                    @click="refreshData"
                    class="p-2 text-gray-500 hover:text-gray-700 focus:outline-none"
                    :class="{ 'animate-spin': isRefreshing }"
                >
                    <ArrowPathIcon class="w-5 h-5" />
                </button>
            </div>
        </div>

        <div class="h-64 sm:h-80 lg:h-96">
            <canvas ref="trendChart"></canvas>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mt-4 sm:mt-6">
            <div v-for="stat in trendStats" :key="stat.label" 
                class="text-center p-3 bg-gray-50 rounded-lg transition-all duration-200 hover:bg-gray-100">
                <p class="text-xs sm:text-sm text-gray-600">{{ stat.label }}</p>
                <p class="mt-1 text-lg sm:text-xl font-semibold" :class="stat.valueClass">
                    {{ formatValue(stat.value, stat.type) }}
                </p>
                <p class="text-xs flex items-center justify-center" :class="getGrowthClass(stat.growth)">
                    <component 
                        :is="stat.growth >= 0 ? ArrowUpIcon : ArrowDownIcon" 
                        class="w-3 h-3 mr-1"
                    />
                    {{ formatGrowth(stat.growth) }}
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import {
    Chart,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
} from 'chart.js';
import { 
    ArrowPathIcon,
    ArrowUpIcon,
    ArrowDownIcon
} from '@heroicons/vue/24/outline';
import { formatKwacha, formatPercent } from '@/utils/format';

// Register Chart.js components
Chart.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

const props = defineProps({
    initialData: {
        type: Object,
        required: true,
        default: () => ({
            period: 'month',
            labels: [],
            amounts: [],
            counts: [],
            totals: { amount: 0, count: 0 },
            averages: { amount: 0 },
            rates: { success: 0 },
            growth: { amount: 0, count: 0, average: 0, success: 0 }
        })
    }
});

const emit = defineEmits(['update:period']);
const selectedPeriod = ref(props.initialData?.period || 'month');
const isRefreshing = ref(false);
const trendChart = ref(null);
let chart = null;

watch(selectedPeriod, (newPeriod) => {
    emit('update:period', newPeriod);
});

watch(() => props.initialData, () => {
    if (chart && trendChart.value) {
        updateChartData(props.initialData);
    }
}, { deep: true });

const updateChartData = (data) => {
    if (chart && data) {
        chart.data.labels = data.labels || [];
        chart.data.datasets[0].data = data.amounts || [];
        chart.data.datasets[1].data = data.counts || [];
        chart.update('none');
    }
};

const refreshData = async () => {
    isRefreshing.value = true;
    try {
        await emit('update:period', selectedPeriod.value);
    } finally {
        isRefreshing.value = false;
    }
};

const trendStats = computed(() => [
    {
        label: 'Total Investments',
        value: Number(props.initialData?.totals?.amount ?? 0),
        type: 'currency',
        growth: Number(props.initialData?.growth?.amount ?? 0),
        valueClass: 'text-blue-600'
    },
    {
        label: 'Number of Investments',
        value: Number(props.initialData?.totals?.count ?? 0),
        type: 'number',
        growth: Number(props.initialData?.growth?.count ?? 0),
        valueClass: 'text-green-600'
    },
    {
        label: 'Average Investment',
        value: Number(props.initialData?.averages?.amount ?? 0),
        type: 'currency',
        growth: Number(props.initialData?.growth?.average ?? 0),
        valueClass: 'text-purple-600'
    },
    {
        label: 'Success Rate',
        value: Number(props.initialData?.rates?.success ?? 0),
        type: 'percentage',
        growth: Number(props.initialData?.growth?.success ?? 0),
        valueClass: 'text-indigo-600'
    }
]);

const chartData = computed(() => ({
    labels: props.initialData?.labels ?? [],
    amounts: props.initialData?.amounts ?? [],
    counts: props.initialData?.counts ?? [],
}));

const initChart = () => {
    if (!trendChart.value) return;

    const ctx = trendChart.value.getContext('2d');

    if (chart) {
        chart.destroy();
    }

    chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.value.labels,
            datasets: [
                {
                    label: 'Investment Amount',
                    data: chartData.value.amounts,
                    borderColor: '#2563EB',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Number of Investments',
                    data: chartData.value.counts,
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 15,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    titleColor: '#111827',
                    bodyColor: '#374151',
                    borderColor: '#E5E7EB',
                    borderWidth: 1,
                    padding: 12,
                    boxPadding: 6,
                    usePointStyle: true
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
        }
    });
};

const formatValue = (value, type) => {
    const numValue = Number(value);
    if (isNaN(numValue)) return '-';

    if (type === 'currency') {
        return formatKwacha(numValue);
    }
    if (type === 'percentage') {
        return formatPercent(numValue);
    }
    return numValue.toLocaleString();
};

const formatGrowth = (value) => {
    const numValue = Number(value);
    if (isNaN(numValue)) return '0%';
    return numValue > 0 ? `↑ ${formatPercent(numValue)}` : `↓ ${formatPercent(-numValue)}`;
};

const getGrowthClass = (value) => {
    const numValue = Number(value);
    return !isNaN(numValue) && numValue >= 0 ? 'text-green-600' : 'text-red-600';
};

watch(() => props.initialData, () => {
    if (trendChart.value) {
        initChart();
    }
}, { deep: true });

onMounted(() => {
    if (trendChart.value) {
        initChart();
    }
});
</script>
