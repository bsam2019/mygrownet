<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { computed, ref } from 'vue';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { PencilSquareIcon, TrashIcon, PlusIcon, ArrowTrendingUpIcon, ArrowTrendingDownIcon, MinusIcon } from '@heroicons/vue/24/outline';
import { toast } from '@/utils/bizboost-toast';
import LineChart from '@/components/BMS/Charts/LineChart.vue';

interface KpiValue {
    id: number;
    value: number;
    period_start: string;
    period_end: string;
    notes: string | null;
    recorded_by: { name: string } | null;
    created_at: string;
}

const props = defineProps<{
    kpi: {
        id: number;
        name: string;
        description: string | null;
        category: string;
        unit: string | null;
        frequency: string;
        target_min: number | null;
        target_max: number | null;
        direction: string;
        formula: string | null;
        owner: string | null;
        status: string;
    };
    values: KpiValue[];
}>();

const showValueForm = ref(false);
const valueForm = ref({ value: null as number | null, period_start: '', period_end: '', notes: '' });

function openValueForm() {
    valueForm.value = { value: null, period_start: '', period_end: '', notes: '' };
    showValueForm.value = true;
}

function submitValue() {
    if (valueForm.value.value === null) { toast.warning('Validation', 'Value is required'); return; }
    router.post(route('cms.kpis.values.store', props.kpi.id), valueForm.value, {
        preserveScroll: true,
        onSuccess: () => { showValueForm.value = false; toast.success('Recorded', 'Value recorded'); },
        onError: () => toast.error('Failed', 'Could not record value'),
    });
}

function confirmDeleteValue(valueId: number) {
    if (confirm('Delete this value?')) {
        router.delete(route('cms.kpis.values.delete', { id: props.kpi.id, valueId }), {
            preserveScroll: true,
            onSuccess: () => toast.success('Deleted', 'Value removed'),
        });
    }
}

function confirmDeleteKpi() {
    if (confirm(`Delete KPI "${props.kpi.name}"?`)) {
        router.delete(route('cms.kpis.delete', props.kpi.id), {
            onSuccess: () => toast.success('Deleted', 'KPI deleted'),
        });
    }
}

const categoryColors: Record<string, string> = {
    financial: 'bg-green-100 text-green-800',
    operational: 'bg-blue-100 text-blue-800',
    customer: 'bg-purple-100 text-purple-800',
    employee: 'bg-amber-100 text-amber-800',
    quality: 'bg-cyan-100 text-cyan-800',
};

function latestValue(): number | null {
    return props.values.length > 0 ? props.values[0].value : null;
}

function trendIcon() {
    if (props.values.length < 2) return MinusIcon;
    const first = props.values[0].value;
    const last = props.values[props.values.length - 1].value;
    const up = first > last;
    if (props.kpi.direction === 'up') return up ? ArrowTrendingUpIcon : ArrowTrendingDownIcon;
    if (props.kpi.direction === 'down') return up ? ArrowTrendingDownIcon : ArrowTrendingUpIcon;
    return MinusIcon;
}

function trendColor() {
    if (props.values.length < 2) return 'text-gray-400';
    const first = props.values[0].value;
    const last = props.values[props.values.length - 1].value;
    const improving = props.kpi.direction === 'up' ? first > last : first < last;
    if (improving) return 'text-green-500';
    return 'text-red-500';
}

function inTarget(value: number): boolean {
    const min = props.kpi.target_min;
    const max = props.kpi.target_max;
    if (min !== null && value < min) return false;
    if (max !== null && value > max) return false;
    return true;
}

const chartLabels = computed(() =>
    [...props.values].reverse().map(v => v.period_start?.slice(0, 7) || v.period_end?.slice(0, 7) || '')
);

const chartValues = computed(() =>
    [...props.values].reverse().map(v => v.value)
);
</script>

<template>
    <Head :title="kpi.name" />

    <CMSLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <Link :href="route('cms.kpis.index')" class="text-sm text-blue-600 hover:text-blue-800">&larr; Back to KPIs</Link>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('cms.kpis.edit', kpi.id)" class="px-3 py-2 text-sm font-medium text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 flex items-center gap-2">
                        <PencilSquareIcon class="h-4 w-4" /> Edit
                    </Link>
                    <button @click="confirmDeleteKpi" class="px-3 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 flex items-center gap-2">
                        <TrashIcon class="h-4 w-4" /> Delete
                    </button>
                </div>
            </div>

            <!-- KPI Header -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span :class="`px-2 py-0.5 text-xs font-medium rounded-full ${categoryColors[kpi.category] || 'bg-gray-100'}`">{{ kpi.category }}</span>
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-600">{{ kpi.frequency }}</span>
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-600">{{ kpi.direction === 'up' ? 'Higher is better' : kpi.direction === 'down' ? 'Lower is better' : 'Neutral' }}</span>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ kpi.name }}</h1>
                        <p v-if="kpi.description" class="text-sm text-gray-500 mt-1">{{ kpi.description }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-bold text-gray-900">
                            {{ latestValue() ?? '—' }}
                            <span v-if="kpi.unit" class="text-lg font-normal text-gray-500">{{ kpi.unit }}</span>
                        </p>
                        <component :is="trendIcon()" :class="`h-5 w-5 inline ${trendColor()}`" />
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-4 mt-6 text-sm">
                    <div v-if="kpi.target_min !== null">
                        <span class="text-gray-500">Target Min:</span>
                        <p class="font-medium text-gray-900">{{ kpi.target_min }} {{ kpi.unit }}</p>
                    </div>
                    <div v-if="kpi.target_max !== null">
                        <span class="text-gray-500">Target Max:</span>
                        <p class="font-medium text-gray-900">{{ kpi.target_max }} {{ kpi.unit }}</p>
                    </div>
                    <div v-if="kpi.formula">
                        <span class="text-gray-500">Formula:</span>
                        <p class="font-medium text-gray-900 text-xs">{{ kpi.formula }}</p>
                    </div>
                    <div v-if="kpi.owner">
                        <span class="text-gray-500">Owner:</span>
                        <p class="font-medium text-gray-900">{{ kpi.owner }}</p>
                    </div>
                </div>
            </div>

            <!-- Trend Chart -->
            <div v-if="values.length >= 2" class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Trend</h2>
                <LineChart
                    :labels="chartLabels"
                    :datasets="[{ label: kpi.name, data: chartValues, color: '#3b82f6', fill: true }]"
                    :height="200"
                    :show-legend="false"
                    :show-grid="true"
                />
            </div>

            <!-- Values -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Recorded Values
                        <span v-if="values.length" class="ml-2 text-sm font-normal text-gray-500">({{ values.length }})</span>
                    </h2>
                    <button @click="openValueForm" class="px-3 py-1.5 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 flex items-center gap-1.5">
                        <PlusIcon class="h-4 w-4" /> Record Value
                    </button>
                </div>

                <div v-if="values.length === 0" class="p-6 text-center text-sm text-gray-500">
                    No values recorded yet. Record the first measurement.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Value</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recorded By</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="v in values" :key="v.id" class="hover:bg-gray-50">
                                <td class="px-6 py-3 text-sm text-gray-900">{{ v.period_start }} — {{ v.period_end }}</td>
                                <td class="px-6 py-3 text-right">
                                    <span :class="`text-sm font-medium ${inTarget(v.value) ? 'text-green-600' : 'text-red-600'}`">
                                        {{ v.value }} {{ kpi.unit }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-500">{{ v.notes || '—' }}</td>
                                <td class="px-6 py-3 text-sm text-gray-500">{{ v.recorded_by?.name || '—' }}</td>
                                <td class="px-6 py-3 text-right">
                                    <button @click="confirmDeleteValue(v.id)" class="text-red-400 hover:text-red-600">
                                        <TrashIcon class="h-4 w-4" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Record Value Modal -->
            <div v-if="showValueForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="showValueForm = false">
                <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-xl">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Record Value</h3>
                    <form @submit.prevent="submitValue" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Value ({{ kpi.unit || 'number' }})</label>
                            <input v-model.number="valueForm.value" type="number" step="0.01" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Period Start</label>
                                <input v-model="valueForm.period_start" type="date" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Period End</label>
                                <input v-model="valueForm.period_end" type="date" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea v-model="valueForm.notes" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        </div>
                        <div class="flex gap-3 justify-end pt-4 border-t border-gray-100">
                            <button type="button" @click="showValueForm = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Cancel</button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">Record</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </CMSLayout>
</template>
