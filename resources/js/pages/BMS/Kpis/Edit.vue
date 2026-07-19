<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ref } from 'vue';
import BMSLayout from '@/Layouts/BMSLayout.vue';
import { toast } from '@/utils/bizboost-toast';

const props = defineProps<{
    kpi: {
        id: number; name: string; description: string | null; category: string;
        unit: string | null; frequency: string; target_min: number | null;
        target_max: number | null; direction: string; formula: string | null;
        owner: string | null; status: string;
    };
}>();

const form = ref({
    name: props.kpi.name,
    description: props.kpi.description ?? '',
    category: props.kpi.category,
    unit: props.kpi.unit ?? '',
    frequency: props.kpi.frequency,
    target_min: props.kpi.target_min,
    target_max: props.kpi.target_max,
    direction: props.kpi.direction,
    formula: props.kpi.formula ?? '',
    owner: props.kpi.owner ?? '',
    status: props.kpi.status,
});

const submitting = ref(false);

function submit() {
    if (!form.value.name.trim()) { toast.warning('Validation', 'Name is required'); return; }
    submitting.value = true;
    router.put(route('bms.kpis.update', props.kpi.id), form.value, {
        onSuccess: () => toast.success('Updated', 'KPI updated'),
        onError: () => { toast.error('Failed', 'Could not update KPI'); submitting.value = false; },
        onFinish: () => { submitting.value = false; },
    });
}
</script>

<template>
    <Head :title="'Edit ' + form.name" />

    <BMSLayout>
        <div class="max-w-2xl mx-auto space-y-6">
            <div>
                <Link :href="route('bms.kpis.index')" class="text-sm text-blue-600 hover:text-blue-800">&larr; Back to KPIs</Link>
                <h1 class="text-2xl font-bold text-gray-900 mt-2">Edit KPI</h1>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-lg shadow p-6 space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select v-model="form.category" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="financial">Financial</option>
                            <option value="operational">Operational</option>
                            <option value="customer">Customer</option>
                            <option value="employee">Employee</option>
                            <option value="quality">Quality</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Frequency</label>
                        <select v-model="form.frequency" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input v-model="form.name" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea v-model="form.description" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                        <input v-model="form.unit" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Direction</label>
                        <select v-model="form.direction" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="up">Higher is better</option>
                            <option value="down">Lower is better</option>
                            <option value="neutral">Neutral</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Target Min</label>
                        <input v-model.number="form.target_min" type="number" step="0.01" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Target Max</label>
                        <input v-model.number="form.target_max" type="number" step="0.01" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Formula</label>
                        <input v-model="form.formula" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Owner</label>
                        <input v-model="form.owner" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                </div>

                <div class="flex gap-3 justify-end pt-4 border-t border-gray-100">
                    <Link :href="route('bms.kpis.index')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Cancel</Link>
                    <button type="submit" :disabled="submitting" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition disabled:opacity-50">
                        {{ submitting ? 'Saving...' : 'Update KPI' }}
                    </button>
                </div>
            </form>
        </div>
    </BMSLayout>
</template>
