<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { route } from 'ziggy-js';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import {
    ArrowLeftIcon, PlusIcon, TrashIcon, PencilSquareIcon,
    CheckCircleIcon, DocumentTextIcon, ChartBarIcon,
} from '@heroicons/vue/24/outline';

defineOptions({ layout: CMSLayout });

interface Item {
    id: number;
    location_name: string;
    type: string;
    width_top: number; width_middle: number; width_bottom: number;
    height_left: number; height_right: number;
    final_width: number; final_height: number;
    area: number; quantity: number; total_area: number;
    notes: string | null;
}
interface Measurement {
    id: number;
    measurement_number: string;
    project_name: string;
    location: string | null;
    measurement_date: string;
    status: 'draft' | 'completed' | 'quoted';
    notes: string | null;
    customer: { id: number; name: string; phone: string };
    measured_by: { user: { name: string } } | null;
    created_by: { user: { name: string } } | null;
    items: Item[];
    quotations: { id: number; quotation_number: string; total_amount: number; status: string }[];
}
interface ProfitItem {
    location: string; type: string; area: number;
    selling_price: number; cost: number; profit: number;
    profit_percent: number; meets_minimum: boolean;
}
interface ProfitSummary {
    total_revenue: number; total_cost: number; total_profit: number;
    overall_profit_percent: number; meets_minimum: boolean;
    minimum_required: number; items: ProfitItem[];
}
interface ItemType { value: string; label: string; }

const props = defineProps<{
    measurement: Measurement;
    profitSummary: ProfitSummary;
    itemTypes: ItemType[];
}>();

const statusConfig: Record<string, { label: string; class: string }> = {
    draft:     { label: 'Draft',     class: 'bg-gray-100 text-gray-700' },
    completed: { label: 'Completed', class: 'bg-blue-100 text-blue-700' },
    quoted:    { label: 'Quoted',    class: 'bg-green-100 text-green-700' },
};

// Add item form
const showAddItem = ref(false);
const addItemForm = useForm({
    location_name: '',
    type: 'sliding_window',
    width_top: '' as number | '',
    width_middle: '' as number | '',
    width_bottom: '' as number | '',
    height_left: '' as number | '',
    height_right: '' as number | '',
    quantity: 1,
    notes: '',
});

function calcPreview() {
    const fw = Math.min(Number(addItemForm.width_top) || 0, Number(addItemForm.width_middle) || 0, Number(addItemForm.width_bottom) || 0);
    const fh = Math.min(Number(addItemForm.height_left) || 0, Number(addItemForm.height_right) || 0);
    const area = (fw * fh) / 1_000_000;
    return { fw, fh, area, totalArea: area * (addItemForm.quantity || 1) };
}

function submitAddItem() {
    addItemForm.post(route('cms.measurements.items.store', props.measurement.id), {
        onSuccess: () => { showAddItem.value = false; addItemForm.reset(); },
    });
}

function deleteItem(itemId: number) {
    if (!confirm('Remove this item?')) return;
    router.delete(route('cms.measurements.items.destroy', { id: props.measurement.id, itemId }));
}

function completeMeasurement() {
    if (!confirm('Mark this measurement as completed?')) return;
    router.post(route('cms.measurements.complete', props.measurement.id));
}

function generateQuotation() {
    if (!confirm('Generate a quotation from this measurement?')) return;
    router.post(route('cms.measurements.generate-quotation', props.measurement.id));
}

function deleteMeasurement() {
    if (!confirm(`Delete measurement ${props.measurement.measurement_number}? This cannot be undone.`)) return;
    router.delete(route('cms.measurements.destroy', props.measurement.id));
}

function fmt(date: string) {
    return new Date(date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

function fmtK(n: number) {
    return 'K' + n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function typeLabel(type: string) {
    return props.itemTypes.find(t => t.value === type)?.label ?? type;
}
</script>

<template>
    <Head :title="`${measurement.measurement_number} - Measurements`" />

    <div class="max-w-5xl mx-auto">
        <!-- Back -->
        <Link :href="route('cms.measurements.index')" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4 transition">
            <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
            Back to Measurements
        </Link>

        <!-- Header -->
        <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <h1 class="text-2xl font-bold text-gray-900">{{ measurement.project_name }}</h1>
                    <span class="px-2.5 py-1 rounded-full text-xs font-medium" :class="statusConfig[measurement.status]?.class">
                        {{ statusConfig[measurement.status]?.label }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 font-mono">{{ measurement.measurement_number }}</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <!-- Complete -->
                <button
                    v-if="measurement.status === 'draft'"
                    @click="completeMeasurement"
                    class="flex items-center gap-1.5 px-3 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition"
                >
                    <CheckCircleIcon class="h-4 w-4" aria-hidden="true" />
                    Mark Complete
                </button>

                <!-- Generate Quotation -->
                <button
                    v-if="measurement.status === 'completed'"
                    @click="generateQuotation"
                    class="flex items-center gap-1.5 px-3 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition"
                >
                    <DocumentTextIcon class="h-4 w-4" aria-hidden="true" />
                    Generate Quotation
                </button>

                <!-- Edit -->
                <Link
                    v-if="measurement.status !== 'quoted'"
                    :href="route('cms.measurements.edit', measurement.id)"
                    class="flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition"
                >
                    <PencilSquareIcon class="h-4 w-4" aria-hidden="true" />
                    Edit
                </Link>

                <!-- Delete -->
                <button
                    v-if="measurement.status === 'draft'"
                    @click="deleteMeasurement"
                    class="px-3 py-2 bg-white border border-red-200 text-red-600 rounded-lg text-sm font-medium hover:bg-red-50 transition"
                >
                    Delete
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Details + Items -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Project Info -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Project Information</h2>
                    <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                        <div>
                            <dt class="text-gray-500">Customer</dt>
                            <dd class="font-medium text-gray-900">{{ measurement.customer?.name }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Date</dt>
                            <dd class="font-medium text-gray-900">{{ fmt(measurement.measurement_date) }}</dd>
                        </div>
                        <div v-if="measurement.location">
                            <dt class="text-gray-500">Location</dt>
                            <dd class="font-medium text-gray-900">{{ measurement.location }}</dd>
                        </div>
                        <div v-if="measurement.measured_by">
                            <dt class="text-gray-500">Measured By</dt>
                            <dd class="font-medium text-gray-900">{{ measurement.measured_by.user?.name }}</dd>
                        </div>
                        <div v-if="measurement.notes" class="col-span-2">
                            <dt class="text-gray-500">Notes</dt>
                            <dd class="text-gray-700">{{ measurement.notes }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Items -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-semibold text-gray-900">Measurement Items</h2>
                        <button
                            v-if="measurement.status !== 'quoted'"
                            @click="showAddItem = !showAddItem"
                            class="flex items-center gap-1 text-sm text-blue-600 hover:text-blue-800 font-medium transition"
                        >
                            <PlusIcon class="h-4 w-4" aria-hidden="true" />
                            Add Item
                        </button>
                    </div>

                    <!-- Add Item Form -->
                    <div v-if="showAddItem" class="mb-4 p-4 bg-blue-50 rounded-lg border border-blue-100">
                        <h3 class="text-sm font-medium text-gray-800 mb-3">New Item</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Location / Description</label>
                                <input v-model="addItemForm.location_name" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                                <select v-model="addItemForm.type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500">
                                    <option v-for="t in itemTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                                </select>
                            </div>
                            <div><label class="block text-xs font-medium text-gray-600 mb-1">Width Top (mm)</label><input v-model.number="addItemForm.width_top" type="number" min="1" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500" /></div>
                            <div><label class="block text-xs font-medium text-gray-600 mb-1">Width Middle (mm)</label><input v-model.number="addItemForm.width_middle" type="number" min="1" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500" /></div>
                            <div><label class="block text-xs font-medium text-gray-600 mb-1">Width Bottom (mm)</label><input v-model.number="addItemForm.width_bottom" type="number" min="1" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500" /></div>
                            <div><label class="block text-xs font-medium text-gray-600 mb-1">Height Left (mm)</label><input v-model.number="addItemForm.height_left" type="number" min="1" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500" /></div>
                            <div><label class="block text-xs font-medium text-gray-600 mb-1">Height Right (mm)</label><input v-model.number="addItemForm.height_right" type="number" min="1" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500" /></div>
                            <div><label class="block text-xs font-medium text-gray-600 mb-1">Quantity</label><input v-model.number="addItemForm.quantity" type="number" min="1" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500" /></div>
                        </div>
                        <!-- Preview -->
                        <div class="mt-3 grid grid-cols-4 gap-2 text-xs bg-white rounded-lg p-3 border border-blue-100">
                            <div><span class="text-gray-500">Final W</span><p class="font-semibold">{{ calcPreview().fw.toFixed(0) }} mm</p></div>
                            <div><span class="text-gray-500">Final H</span><p class="font-semibold">{{ calcPreview().fh.toFixed(0) }} mm</p></div>
                            <div><span class="text-gray-500">Area</span><p class="font-semibold">{{ calcPreview().area.toFixed(4) }} m²</p></div>
                            <div><span class="text-gray-500">Total Area</span><p class="font-semibold text-blue-700">{{ calcPreview().totalArea.toFixed(4) }} m²</p></div>
                        </div>
                        <div class="mt-3 flex gap-2">
                            <button @click="submitAddItem" :disabled="addItemForm.processing" class="px-4 py-1.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 transition">Save Item</button>
                            <button @click="showAddItem = false" class="px-4 py-1.5 text-gray-600 text-sm hover:text-gray-800 transition">Cancel</button>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div v-if="measurement.items.length > 0" class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th class="text-left py-2 pr-3 text-xs font-medium text-gray-500">Location</th>
                                    <th class="text-left py-2 pr-3 text-xs font-medium text-gray-500">Type</th>
                                    <th class="text-right py-2 pr-3 text-xs font-medium text-gray-500">W×H (mm)</th>
                                    <th class="text-right py-2 pr-3 text-xs font-medium text-gray-500">Qty</th>
                                    <th class="text-right py-2 text-xs font-medium text-gray-500">Total Area</th>
                                    <th v-if="measurement.status !== 'quoted'" class="py-2 w-8"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="item in measurement.items" :key="item.id">
                                    <td class="py-2 pr-3 text-gray-900">{{ item.location_name }}</td>
                                    <td class="py-2 pr-3 text-gray-600">{{ typeLabel(item.type) }}</td>
                                    <td class="py-2 pr-3 text-right text-gray-600 font-mono text-xs">{{ Number(item.final_width).toFixed(0) }}×{{ Number(item.final_height).toFixed(0) }}</td>
                                    <td class="py-2 pr-3 text-right text-gray-600">{{ item.quantity }}</td>
                                    <td class="py-2 text-right font-medium text-gray-900">{{ Number(item.total_area).toFixed(4) }} m²</td>
                                    <td v-if="measurement.status !== 'quoted'" class="py-2 pl-2">
                                        <button @click="deleteItem(item.id)" class="text-red-400 hover:text-red-600 transition" aria-label="Remove item">
                                            <TrashIcon class="h-4 w-4" aria-hidden="true" />
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-gray-200">
                                    <td colspan="4" class="py-2 text-right text-sm font-semibold text-gray-700">Total Area</td>
                                    <td class="py-2 text-right font-bold text-gray-900">
                                        {{ measurement.items.reduce((s, i) => s + Number(i.total_area), 0).toFixed(4) }} m²
                                    </td>
                                    <td v-if="measurement.status !== 'quoted'"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div v-else class="text-center py-8 text-sm text-gray-400 border-2 border-dashed border-gray-200 rounded-lg">
                        No items yet.
                    </div>
                </div>
            </div>

            <!-- Right: Sidebar -->
            <div class="space-y-4">

                <!-- Linked Quotations -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Quotations</h2>
                    <div v-if="measurement.quotations?.length > 0" class="space-y-2">
                        <Link
                            v-for="q in measurement.quotations"
                            :key="q.id"
                            :href="route('cms.quotations.show', q.id)"
                            class="flex items-center justify-between p-2 rounded-lg bg-gray-50 hover:bg-gray-100 transition text-sm"
                        >
                            <span class="font-mono text-gray-700">{{ q.quotation_number }}</span>
                            <span class="font-medium text-gray-900">K{{ Number(q.total_amount).toLocaleString() }}</span>
                        </Link>
                    </div>
                    <p v-else class="text-xs text-gray-400">No quotations yet.</p>
                </div>

                <!-- Internal Profit Summary (staff only) -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <ChartBarIcon class="h-4 w-4 text-gray-500" aria-hidden="true" />
                        <h2 class="text-sm font-semibold text-gray-900">Profit Estimate</h2>
                        <span class="text-xs text-gray-400">(internal)</span>
                    </div>

                    <div v-if="measurement.items.length > 0" class="space-y-3">
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="bg-gray-50 rounded-lg p-2">
                                <p class="text-gray-500">Revenue</p>
                                <p class="font-bold text-gray-900">{{ fmtK(profitSummary.total_revenue) }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-2">
                                <p class="text-gray-500">Cost</p>
                                <p class="font-bold text-gray-900">{{ fmtK(profitSummary.total_cost) }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-2">
                                <p class="text-gray-500">Profit</p>
                                <p class="font-bold" :class="profitSummary.total_profit >= 0 ? 'text-green-700' : 'text-red-700'">
                                    {{ fmtK(profitSummary.total_profit) }}
                                </p>
                            </div>
                            <div class="rounded-lg p-2" :class="profitSummary.meets_minimum ? 'bg-green-50' : 'bg-red-50'">
                                <p class="text-gray-500">Margin</p>
                                <p class="font-bold" :class="profitSummary.meets_minimum ? 'text-green-700' : 'text-red-700'">
                                    {{ profitSummary.overall_profit_percent.toFixed(1) }}%
                                </p>
                            </div>
                        </div>
                        <p v-if="!profitSummary.meets_minimum" class="text-xs text-red-600 bg-red-50 rounded p-2">
                            ⚠ Below minimum {{ profitSummary.minimum_required }}% margin. Review pricing.
                        </p>
                        <p v-else class="text-xs text-green-700 bg-green-50 rounded p-2">
                            ✓ Meets minimum {{ profitSummary.minimum_required }}% margin.
                        </p>
                    </div>
                    <p v-else class="text-xs text-gray-400">Add items to see profit estimate.</p>
                </div>
            </div>
        </div>
    </div>
</template>
