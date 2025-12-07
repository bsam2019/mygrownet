<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { Line, Bar, Doughnut } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js';
import {
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
    CalendarIcon,
    ChevronLeftIcon,
    ArrowsPointingOutIcon,
} from '@heroicons/vue/24/outline';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

interface DataPoint {
    label: string;
    value: number;
    date?: string;
    details?: Record<string, any>;
}

interface Props {
    title: string;
    type?: 'line' | 'bar' | 'doughnut';
    data: DataPoint[];
    color?: string;
    showTrend?: boolean;
    currency?: boolean;
    drillDownEnabled?: boolean;
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'line',
    color: 'violet',
    showTrend: true,
    currency: false,
    drillDownEnabled: true,
    loading: false,
});

const emit = defineEmits<{
    drillDown: [point: DataPoint];
    periodChange: [period: string];
}>();

const selectedPeriod = ref('7d');
const isDrilledDown = ref(false);
const drillDownData = ref<DataPoint | null>(null);
const isExpanded = ref(false);

const periods = [
    { value: '7d', label: '7 Days' },
    { value: '30d', label: '30 Days' },
    { value: '90d', label: '90 Days' },
    { value: '1y', label: '1 Year' },
];

const colorPalettes: Record<string, { primary: string; gradient: string[]; bg: string }> = {
    violet: { primary: '#7c3aed', gradient: ['rgba(124, 58, 237, 0.3)', 'rgba(124, 58, 237, 0)'], bg: 'rgba(124, 58, 237, 0.1)' },
    emerald: { primary: '#10b981', gradient: ['rgba(16, 185, 129, 0.3)', 'rgba(16, 185, 129, 0)'], bg: 'rgba(16, 185, 129, 0.1)' },
    blue: { primary: '#3b82f6', gradient: ['rgba(59, 130, 246, 0.3)', 'rgba(59, 130, 246, 0)'], bg: 'rgba(59, 130, 246, 0.1)' },
    amber: { primary: '#f59e0b', gradient: ['rgba(245, 158, 11, 0.3)', 'rgba(245, 158, 11, 0)'], bg: 'rgba(245, 158, 11, 0.1)' },
};

const palette = computed(() => colorPalettes[props.color] || colorPalettes.violet);

const total = computed(() => props.data.reduce((sum, d) => sum + d.value, 0));

const trend = computed(() => {
    if (props.data.length < 2) return 0;
    const mid = Math.floor(props.data.length / 2);
    const firstHalf = props.data.slice(0, mid).reduce((s, d) => s + d.value, 0);
    const secondHalf = props.data.slice(mid).reduce((s, d) => s + d.value, 0);
    if (firstHalf === 0) return secondHalf > 0 ? 100 : 0;
    return Math.round(((secondHalf - firstHalf) / firstHalf) * 100);
});

const chartData = computed(() => {
    if (props.type === 'doughnut') {
        return {
            labels: props.data.map(d => d.label),
            datasets: [{
                data: props.data.map(d => d.value),
                backgroundColor: [
                    palette.value.primary,
                    '#3b82f6',
                    '#10b981',
                    '#f59e0b',
                    '#ef4444',
                    '#8b5cf6',
                ],
                borderWidth: 0,
                hoverOffset: 8,
            }],
        };
    }

    return {
        labels: props.data.map(d => d.label),
        datasets: [{
            label: props.title,
            data: props.data.map(d => d.value),
            borderColor: palette.value.primary,
            backgroundColor: props.type === 'bar' 
                ? palette.value.bg 
                : (ctx: any) => {
                    const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, ctx.chart.height);
                    gradient.addColorStop(0, palette.value.gradient[0]);
                    gradient.addColorStop(1, palette.value.gradient[1]);
                    return gradient;
                },
            fill: props.type === 'line',
            tension: 0.4,
            pointRadius: 0,
            pointHoverRadius: 6,
            pointHoverBackgroundColor: palette.value.primary,
            pointHoverBorderColor: '#fff',
            pointHoverBorderWidth: 2,
            borderWidth: 2,
            borderRadius: props.type === 'bar' ? 6 : 0,
        }],
    };
});
</script>

<template>
    <div :class="[
        'rounded-2xl bg-white dark:bg-slate-800 shadow-sm ring-1 ring-slate-200 dark:ring-slate-700 overflow-hidden transition-all duration-300',
        isExpanded && 'fixed inset-4 z-50 shadow-2xl'
    ]">
        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-700">
            <div class="flex items-center gap-3">
                <button
                    v-if="isDrilledDown"
                    @click="isDrilledDown = false; drillDownData = null"
                    class="p-1 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors"
                    aria-label="Go back"
                >
                    <ChevronLeftIcon class="h-5 w-5" aria-hidden="true" />
                </button>
                <div>
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white">
                        {{ isDrilledDown && drillDownData ? drillDownData.label : title }}
                    </h3>
                    <p v-if="showTrend && !isDrilledDown" class="text-sm text-slate-500 flex items-center gap-1">
                        <span class="font-medium" :class="currency ? 'text-emerald-600' : ''">
                            {{ currency ? 'K' : '' }}{{ total.toLocaleString() }}
                        </span>
                        <span 
                            v-if="trend !== 0"
                            :class="trend > 0 ? 'text-emerald-600' : 'text-red-500'"
                            class="flex items-center text-xs"
                        >
                            <ArrowTrendingUpIcon v-if="trend > 0" class="h-3 w-3" aria-hidden="true" />
                            <ArrowTrendingDownIcon v-else class="h-3 w-3" aria-hidden="true" />
                            {{ Math.abs(trend) }}%
                        </span>
                    </p>
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                <!-- Period selector -->
                <div class="flex items-center gap-1 p-1 bg-slate-100 dark:bg-slate-700 rounded-lg">
                    <button
                        v-for="period in periods"
                        :key="period.value"
                        @click="selectedPeriod = period.value; emit('periodChange', period.value)"
                        :class="[
                            'px-2.5 py-1 text-xs font-medium rounded-md transition-all',
                            selectedPeriod === period.value
                                ? 'bg-white dark:bg-slate-600 text-slate-900 dark:text-white shadow-sm'
                                : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'
                        ]"
                    >
                        {{ period.label }}
                    </button>
                </div>
                
                <!-- Expand button -->
                <button
                    @click="isExpanded = !isExpanded"
                    class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors"
                    :aria-label="isExpanded ? 'Collapse chart' : 'Expand chart'"
                >
                    <ArrowsPointingOutIcon class="h-4 w-4" aria-hidden="true" />
                </button>
            </div>
        </div>

        <!-- Chart -->
        <div :class="['p-5', isExpanded ? 'h-[calc(100%-80px)]' : 'h-64']">
            <!-- Loading state -->
            <div v-if="loading" class="h-full flex items-center justify-center">
                <div class="animate-pulse flex flex-col items-center gap-2">
                    <div class="h-32 w-full bg-slate-100 rounded-lg"></div>
                    <div class="h-4 w-24 bg-slate-100 rounded"></div>
                </div>
            </div>

            <!-- Chart -->
            <template v-else>
                <Line
                    v-if="type === 'line'"
                    :data="chartData"
                    :options="{
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { intersect: false, mode: 'index' },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                titleColor: '#fff',
                                bodyColor: '#cbd5e1',
                                padding: 12,
                                cornerRadius: 8,
                                displayColors: false,
                                callbacks: {
                                    label: (ctx: any) => currency ? `K${ctx.raw.toLocaleString()}` : ctx.raw.toLocaleString(),
                                },
                            },
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: '#94a3b8', font: { size: 11 } },
                            },
                            y: {
                                grid: { color: '#f1f5f9' },
                                ticks: { 
                                    color: '#94a3b8', 
                                    font: { size: 11 },
                                    callback: (v: number) => currency ? `K${v}` : v,
                                },
                            },
                        },
                        onClick: (e: any, elements: any[]) => {
                            if (props.drillDownEnabled && elements.length > 0) {
                                const idx = elements[0].index;
                                const point = props.data[idx];
                                if (point.details) {
                                    isDrilledDown.value = true;
                                    drillDownData.value = point;
                                }
                                emit('drillDown', point);
                            }
                        },
                    }"
                />
                
                <Bar
                    v-else-if="type === 'bar'"
                    :data="chartData"
                    :options="{
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                padding: 12,
                                cornerRadius: 8,
                            },
                        },
                        scales: {
                            x: { grid: { display: false } },
                            y: { grid: { color: '#f1f5f9' } },
                        },
                    }"
                />
                
                <Doughnut
                    v-else-if="type === 'doughnut'"
                    :data="chartData"
                    :options="{
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: { usePointStyle: true, padding: 16 },
                            },
                        },
                    }"
                />
            </template>
        </div>

        <!-- Drill-down details -->
        <div v-if="isDrilledDown && drillDownData?.details" class="px-5 pb-5 border-t border-slate-100 dark:border-slate-700 pt-4">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div 
                    v-for="(value, key) in drillDownData.details" 
                    :key="key"
                    class="p-3 rounded-xl bg-slate-50 dark:bg-slate-700"
                >
                    <p class="text-xs text-slate-500 dark:text-slate-400 capitalize">{{ String(key).replace(/_/g, ' ') }}</p>
                    <p class="text-sm font-semibold text-slate-900 dark:text-white mt-1">
                        {{ typeof value === 'number' ? value.toLocaleString() : value }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Backdrop for expanded mode -->
    <div 
        v-if="isExpanded" 
        class="fixed inset-0 bg-slate-900/50 z-40"
        @click="isExpanded = false"
    ></div>
</template>
