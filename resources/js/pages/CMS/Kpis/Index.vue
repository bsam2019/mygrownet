<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ref, onMounted } from 'vue';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { PlusIcon, ArrowTrendingUpIcon, ArrowTrendingDownIcon, MinusIcon } from '@heroicons/vue/24/outline';

interface KpiItem {
    id: number;
    name: string;
    description: string | null;
    category: string;
    unit: string | null;
    frequency: string;
    target_min: number | null;
    target_max: number | null;
    direction: string;
    owner: string | null;
    status: string;
    latest_value: number | null;
    latest_period: string | null;
    trend: string;
    status_color: string;
    values_count: number;
    sparkline: number[];
}

const props = defineProps<{
    kpis: KpiItem[];
}>();

const canvasRefs = ref<Map<number, HTMLCanvasElement>>(new Map());

function setCanvasRef(id: number, el: HTMLCanvasElement | null) {
    if (el) canvasRefs.value.set(id, el);
}

function drawSparkline(canvas: HTMLCanvasElement, data: number[], color: string) {
    if (data.length < 2) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;
    const w = canvas.width, h = canvas.height;
    ctx.clearRect(0, 0, w, h);

    const min = Math.min(...data), max = Math.max(...data);
    const range = max - min || 1;
    const pad = 2;
    const stepX = (w - pad * 2) / (data.length - 1);

    ctx.beginPath();
    ctx.strokeStyle = color;
    ctx.lineWidth = 1.5;
    ctx.lineJoin = 'round';

    data.forEach((v, i) => {
        const x = pad + i * stepX;
        const y = h - pad - ((v - min) / range) * (h - pad * 2);
        i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
    });
    ctx.stroke();
}

onMounted(() => {
    const colors: Record<string, string> = {
        improving: '#22c55e', declining: '#ef4444', insufficient_data: '#9ca3af', neutral: '#9ca3af',
    };
    setTimeout(() => {
        props.kpis.forEach(kpi => {
            const canvas = canvasRefs.value.get(kpi.id);
            if (canvas && kpi.sparkline.length >= 2) {
                drawSparkline(canvas, kpi.sparkline, colors[kpi.trend] || '#9ca3af');
            }
        });
    }, 50);
});

const categoryColors: Record<string, string> = {
    financial: 'bg-green-100 text-green-800',
    operational: 'bg-blue-100 text-blue-800',
    customer: 'bg-purple-100 text-purple-800',
    employee: 'bg-amber-100 text-amber-800',
    quality: 'bg-cyan-100 text-cyan-800',
};

function trendIcon(trend: string) {
    if (trend === 'improving') return ArrowTrendingUpIcon;
    if (trend === 'declining') return ArrowTrendingDownIcon;
    return MinusIcon;
}

function trendColor(trend: string) {
    if (trend === 'improving') return 'text-green-500';
    if (trend === 'declining') return 'text-red-500';
    return 'text-gray-400';
}
</script>

<template>
    <Head title="KPIs" />

    <CMSLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Key Performance Indicators</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Track and measure what matters across the company
                    </p>
                </div>
                <Link
                    :href="route('cms.kpis.create')"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2"
                >
                    <PlusIcon class="h-5 w-5" />
                    New KPI
                </Link>
            </div>

            <div v-if="kpis.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
                <p class="text-gray-500 text-sm">No KPIs yet.</p>
                <p class="text-gray-400 text-xs mt-1">Create your first KPI to start tracking performance.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                    v-for="kpi in kpis"
                    :key="kpi.id"
                    class="bg-white rounded-lg shadow p-5 hover:shadow-md transition border border-gray-100"
                >
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span :class="`px-2 py-0.5 text-xs font-medium rounded-full ${categoryColors[kpi.category] || 'bg-gray-100'}`">
                                    {{ kpi.category }}
                                </span>
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-600">
                                    {{ kpi.frequency }}
                                </span>
                            </div>
                            <Link :href="route('cms.kpis.show', kpi.id)" class="text-sm font-semibold text-gray-900 hover:text-blue-600">
                                {{ kpi.name }}
                            </Link>
                            <p v-if="kpi.description" class="text-xs text-gray-500 mt-0.5 truncate">{{ kpi.description }}</p>
                        </div>
                        <canvas v-if="kpi.sparkline?.length >= 2" :ref="(el: any) => setCanvasRef(kpi.id, el)" width="80" height="32" class="flex-shrink-0 ml-2" />
                        <component :is="trendIcon(kpi.trend)" v-else :class="`h-5 w-5 flex-shrink-0 ${trendColor(kpi.trend)}`" />
                    </div>

                    <div class="flex items-end justify-between">
                        <div>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ kpi.latest_value ?? '—' }}
                                <span v-if="kpi.unit" class="text-sm font-normal text-gray-500">{{ kpi.unit }}</span>
                            </p>
                            <p v-if="kpi.latest_period" class="text-xs text-gray-400">{{ kpi.latest_period }}</p>
                        </div>
                        <div class="text-right">
                            <p v-if="kpi.target_min !== null || kpi.target_max !== null" class="text-xs text-gray-500">
                                Target{{ kpi.target_min !== null ? ' K' + kpi.target_min : '' }}{{ kpi.target_max !== null ? ' — K' + kpi.target_max : '' }}
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ kpi.values_count }} records</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </CMSLayout>
</template>
