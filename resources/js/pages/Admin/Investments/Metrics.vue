<template>
    <AdminLayout title="Investment Metrics">
        <div class="space-y-6 p-6">
            <PageHeader class="mb-8">
                <PageTitle>Investment Metrics</PageTitle>
                <div class="flex items-center gap-4">
                    <Select v-model="selectedPeriod" class="w-40">
                        <option v-for="option in periodOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </Select>
                </div>
            </PageHeader>

            <div v-if="loading" class="flex justify-center py-12">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            </div>

            <div v-else-if="metrics.length" class="space-y-8">
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Total Investments</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">
                                {{ formatCurrency(metrics[metrics.length - 1]?.total_investments || 0) }}
                            </div>
                            <p class="text-xs text-muted-foreground">
                                {{ metrics[metrics.length - 1]?.trend_value > 0 ? '+' : '' }}{{ metrics[metrics.length - 1]?.trend_value }}% from previous period
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Active Investments</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">
                                {{ metrics[metrics.length - 1]?.active_investments || 0 }}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Average ROI</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">
                                {{ formatPercent(metrics[metrics.length - 1]?.average_roi || 0) }}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Success Rate</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">
                                {{ formatPercent(metrics[metrics.length - 1]?.success_rate || 0) }}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Investment Trends</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="h-[400px]">
                            <Line :data="chartData" :options="chartOptions" />
                        </div>
                    </CardContent>
                </Card>
            </div>
            <div v-else class="text-center py-12">
                <p class="text-gray-500">No metrics data available for the selected period</p>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ref, computed, watch } from 'vue';
import { PageHeader, PageTitle } from '@/components/ui/page-header';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select } from '@/components/ui/select';
import { Line } from 'vue-chartjs';
import {
    Chart,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title as ChartTitle,
    Tooltip as ChartTooltip,
    Legend,
    Filler
} from 'chart.js';
import { formatCurrency, formatPercent } from '@/utils/format';
import { router } from '@inertiajs/vue3';

// Register Chart.js components required by vue-chartjs
Chart.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    ChartTitle,
    ChartTooltip,
    Legend,
    Filler
);

const props = defineProps<{
    metrics: Array<{
        date: string;
        total_investments: number;
        average_roi: number;
        success_rate: number;
        active_investments: number;
        trend_value: number;
    }>;
}>();

const loading = ref(false);
const selectedPeriod = ref('month');

const periodOptions = [
    { label: 'Last Week', value: 'week' },
    { label: 'Last Month', value: 'month' },
    { label: 'Last Quarter', value: 'quarter' },
    { label: 'Last Year', value: 'year' }
];

watch(selectedPeriod, (newPeriod) => {
    loading.value = true;
    router.get(route('admin.investments.metrics'), 
        { period: newPeriod }, 
        { 
            preserveState: true,
            onSuccess: () => { loading.value = false; }
        }
    );
});

const chartData = computed(() => ({
    labels: props.metrics.map(m => new Date(m.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })),
    datasets: [
        {
            label: 'Total Investments',
            data: props.metrics.map(m => m.total_investments),
            borderColor: 'rgb(99, 102, 241)',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            tension: 0.4,
            fill: true
        },
        {
            label: 'Average ROI',
            data: props.metrics.map(m => m.average_roi),
            borderColor: 'rgb(34, 197, 94)',
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            tension: 0.4,
            fill: true,
            yAxisID: 'roi'
        }
    ]
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
        intersect: false,
        mode: 'index'
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                callback: (value: number) => {
                    if (typeof value === 'number') {
                        return `K${value.toLocaleString('en-ZM')}`;
                    }
                    return value;
                }
            }
        },
        roi: {
            position: 'right' as const,
            beginAtZero: true,
            ticks: {
                callback: (value: number) => formatPercent(value)
            }
        }
    },
    plugins: {
        tooltip: {
            callbacks: {
                label: (context: any) => {
                    let label = context.dataset.label || '';
                    if (label) {
                        label += ': ';
                    }
                    if (context.parsed.y !== null) {
                        label += context.dataset.yAxisID === 'roi' 
                            ? formatPercent(context.parsed.y)
                            : formatCurrency(context.parsed.y);
                    }
                    return label;
                }
            }
        }
    }
};
</script>