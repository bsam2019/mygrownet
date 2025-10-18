<template>
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
            <h3 class="text-base font-semibold">Analytics Overview</h3>
            <div class="inline-flex p-0.5 space-x-1 bg-gray-100 rounded-lg">
                <button
                    v-for="type in chartTypes"
                    :key="type"
                    @click="activeChart = type"
                    class="px-3 py-1.5 text-xs rounded-md transition-all duration-200 whitespace-nowrap"
                    :class="getButtonClass(type)"
                >
                    {{ type }}
                </button>
            </div>
        </div>

        <div class="relative h-[300px] sm:h-[400px]">
            <div v-if="loading"
                class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 z-10 transition-opacity duration-200"
            >
                <div class="text-gray-500 animate-pulse">Loading...</div>
            </div>
            <div v-else-if="error" class="absolute inset-0 flex items-center justify-center">
                <div class="text-red-500 text-sm">{{ error }}</div>
            </div>
            <canvas ref="chartRef" class="w-full h-full"></canvas>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import Chart from 'chart.js/auto';
import { formatCurrency, formatShortDate, formatPercentage } from '@/utils/formatting';

// Debounce function
const debounce = (fn, wait) => {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            fn(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

const props = defineProps({
    chartData: {
        type: Object,
        required: true
    }
});

const chartRef = ref(null);
const chart = ref(null);
const activeChart = ref('Investments');
const chartTypes = ['Investments', 'Users', 'Returns'];
const loading = ref(false);
const error = ref(null);

const getButtonClass = (type) => {
    const isActive = activeChart.value === type;
    return {
        'bg-white shadow-sm text-blue-600 hover:bg-blue-50': isActive,
        'hover:bg-gray-200 text-gray-600': !isActive,
        'focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1': true
    };
};

const getDatasets = () => {
    if (!props.chartData) return [];

    switch (activeChart.value) {
        case 'Investments':
            return [{
                label: 'Total Investments',
                data: props.chartData.period_stats?.total_investments || [],
                borderColor: '#2563EB',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                tension: 0.4,
                fill: true,
                borderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 5,
                pointBackgroundColor: '#ffffff',
                pointHoverBackgroundColor: '#2563EB',
                pointBorderWidth: 2,
                pointHoverBorderWidth: 2,
                pointBorderColor: '#2563EB',
                segment: {
                    borderColor: ctx => '#2563EB'
                }
            }];
        case 'Users':
            return [{
                label: 'Active Users',
                data: props.chartData.users || [],
                borderColor: '#10B981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true,
                borderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 5,
                pointBackgroundColor: '#ffffff',
                pointHoverBackgroundColor: '#10B981',
                pointBorderWidth: 2,
                pointHoverBorderWidth: 2,
                pointBorderColor: '#10B981',
                segment: {
                    borderColor: ctx => '#10B981'
                }
            }];
        case 'Returns':
            return [{
                label: 'Average ROI',
                data: props.chartData.returns || [],
                borderColor: '#F59E0B',
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                tension: 0.4,
                fill: true,
                borderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 5,
                pointBackgroundColor: '#ffffff',
                pointHoverBackgroundColor: '#F59E0B',
                pointBorderWidth: 2,
                pointHoverBorderWidth: 2,
                pointBorderColor: '#F59E0B',
                segment: {
                    borderColor: ctx => '#F59E0B'
                }
            }];
        default:
            return [];
    }
};

const validateChartData = (data) => {
    if (!data) return 'No data available';
    if (!Array.isArray(data.labels) || data.labels.length === 0) return 'No data for selected period';
    return null;
};

const getChartOptions = () => ({
    responsive: true,
    maintainAspectRatio: false,
    layout: {
        padding: {
            left: 10,
            right: 20,
            top: 20,
            bottom: 10
        }
    },
    interaction: {
        intersect: false,
        mode: 'index'
    },
    animation: {
        duration: 750,
        easing: 'easeInOutQuart'
    },
    transitions: {
        active: {
            animation: {
                duration: 400
            }
        }
    },
    plugins: {
        legend: {
            position: 'top',
            align: 'end',
            labels: {
                boxWidth: 12,
                boxHeight: 12,
                padding: 15,
                usePointStyle: true,
                font: {
                    size: 11
                }
            }
        },
        tooltip: {
            animation: {
                duration: 150
            },
            padding: 12,
            callbacks: {
                label: function(context) {
                    const value = context.raw;
                    if (activeChart.value === 'Returns') {
                        return formatPercentage(value);
                    } else if (activeChart.value === 'Investments') {
                        return formatCurrency(value);
                    }
                    return value.toLocaleString();
                }
            }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                padding: 8,
                callback: function(value) {
                    if (activeChart.value === 'Returns') {
                        return formatPercentage(value);
                    } else if (activeChart.value === 'Investments') {
                        return formatCurrency(value);
                    }
                    return value.toLocaleString();
                }
            },
            grid: {
                drawBorder: false,
                color: 'rgba(0, 0, 0, 0.05)'
            }
        },
        x: {
            ticks: {
                maxRotation: 45,
                minRotation: 45,
                padding: 8,
                callback: function(value, index) {
                    const total = props.chartData.labels.length;
                    return index % Math.ceil(total / 7) === 0 ?
                        formatShortDate(props.chartData.labels[index]) : '';
                }
            },
            grid: {
                display: false
            }
        }
    }
});

const createChart = async () => {
    error.value = null;
    loading.value = true;

    try {
        const validationError = validateChartData(props.chartData);
        if (validationError) {
            error.value = validationError;
            return;
        }

        if (chart.value) {
            chart.value.destroy();
        }

        const ctx = chartRef.value.getContext('2d');
        const datasets = getDatasets();

        chart.value = new Chart(ctx, {
            type: 'line',
            data: {
                labels: props.chartData.labels.map(formatShortDate),
                datasets
            },
            options: getChartOptions()
        });
    } catch (e) {
        console.error('Chart error:', e);
        error.value = 'Error loading chart data';
    } finally {
        loading.value = false;
    }
};

// Debounced chart update
const debouncedCreateChart = debounce(async () => {
    await createChart();
}, 250);

watch(activeChart, debouncedCreateChart);
watch(() => props.chartData, debouncedCreateChart, { deep: true });

onMounted(() => {
    if (props.chartData) {
        createChart();
    }
});
</script>
