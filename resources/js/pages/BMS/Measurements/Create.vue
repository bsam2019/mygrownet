<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { route } from 'ziggy-js';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { PlusIcon, TrashIcon, ArrowLeftIcon, CalculatorIcon } from '@heroicons/vue/24/outline';

defineOptions({ layout: CMSLayout });

interface Customer { id: number; customer_number: string; name: string; phone: string; }
interface Worker { id: number; user: { name: string }; }
interface ItemType { value: string; label: string; }

const props = defineProps<{
    customers: Customer[];
    workers: Worker[];
    itemTypes: ItemType[];
}>();

const form = useForm({
    customer_id: null as number | null,
    project_name: '',
    location: '',
    measured_by: null as number | null,
    measurement_date: new Date().toISOString().split('T')[0],
    notes: '',
    items: [] as Array<{
        location_name: string;
        type: string;
        width_top: number | '';
        width_middle: number | '';
        width_bottom: number | '';
        height_left: number | '';
        height_right: number | '';
        quantity: number;
        notes: string;
    }>,
});

function addItem() {
    form.items.push({
        location_name: '',
        type: 'sliding_window',
        width_top: '',
        width_middle: '',
        width_bottom: '',
        height_left: '',
        height_right: '',
        quantity: 1,
        notes: '',
    });
}

function removeItem(i: number) {
    form.items.splice(i, 1);
}

// Live preview calculations
function calcItem(item: typeof form.items[0]) {
    const wTop = Number(item.width_top) || 0;
    const wMid = Number(item.width_middle) || 0;
    const wBot = Number(item.width_bottom) || 0;
    const hLeft = Number(item.height_left) || 0;
    const hRight = Number(item.height_right) || 0;

    const fw = Math.min(wTop, wMid, wBot);
    const fh = Math.min(hLeft, hRight);
    const area = (fw * fh) / 1_000_000;
    const totalArea = area * (Number(item.quantity) || 1);

    return { fw, fh, area, totalArea };
}

const grandTotal = computed(() =>
    form.items.reduce((s, item) => s + calcItem(item).totalArea, 0)
);

function submit() {
    form.post(route('cms.measurements.store'));
}
</script>

<template>
    <Head title="New Measurement - CMS" />

    <div class="max-w-4xl mx-auto">
        <!-- Back -->
        <Link :href="route('cms.measurements.index')" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4 transition">
            <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
            Back to Measurements
        </Link>

        <h1 class="text-2xl font-bold text-gray-900 mb-6">New Measurement</h1>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Basic Info -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-4">Project Details</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Customer -->
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Customer <span class="text-red-500">*</span></label>
                        <select v-model="form.customer_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                            <option :value="null">Select customer…</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }} ({{ c.customer_number }})</option>
                        </select>
                        <p v-if="form.errors.customer_id" class="mt-1 text-xs text-red-600">{{ form.errors.customer_id }}</p>
                    </div>

                    <!-- Project Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Project Name <span class="text-red-500">*</span></label>
                        <input v-model="form.project_name" type="text" placeholder="e.g. Kabulonga Residence" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                        <p v-if="form.errors.project_name" class="mt-1 text-xs text-red-600">{{ form.errors.project_name }}</p>
                    </div>

                    <!-- Location -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                        <input v-model="form.location" type="text" placeholder="e.g. Plot 123, Kabulonga" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>

                    <!-- Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Measurement Date <span class="text-red-500">*</span></label>
                        <input v-model="form.measurement_date" type="date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                    </div>

                    <!-- Measured By -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Measured By</label>
                        <select v-model="form.measured_by" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                            <option :value="null">Select staff…</option>
                            <option v-for="w in workers" :key="w.id" :value="w.id">{{ w.user?.name }}</option>
                        </select>
                    </div>

                    <!-- Notes -->
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea v-model="form.notes" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" placeholder="Any site notes…" />
                    </div>
                </div>
            </div>

            <!-- Measurement Items -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Measurement Items</h2>
                        <p class="text-xs text-gray-500 mt-0.5">All dimensions in millimetres (mm)</p>
                    </div>
                    <button type="button" @click="addItem" class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg text-sm font-medium hover:bg-blue-100 transition">
                        <PlusIcon class="h-4 w-4" aria-hidden="true" />
                        Add Item
                    </button>
                </div>

                <div v-if="form.items.length === 0" class="text-center py-8 text-sm text-gray-400 border-2 border-dashed border-gray-200 rounded-lg">
                    No items yet. Click "Add Item" to start measuring.
                </div>

                <div v-for="(item, i) in form.items" :key="i" class="mb-4 p-4 border border-gray-200 rounded-lg bg-gray-50">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-medium text-gray-700">Item {{ i + 1 }}</span>
                        <button type="button" @click="removeItem(i)" class="text-red-500 hover:text-red-700 transition" aria-label="Remove item">
                            <TrashIcon class="h-4 w-4" aria-hidden="true" />
                        </button>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <!-- Location Name -->
                        <div class="col-span-2 sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Location / Description <span class="text-red-500">*</span></label>
                            <input v-model="item.location_name" type="text" placeholder="e.g. Living Room Window" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 bg-white" />
                        </div>

                        <!-- Type -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Type <span class="text-red-500">*</span></label>
                            <select v-model="item.type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 bg-white">
                                <option v-for="t in itemTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                            </select>
                        </div>

                        <!-- Widths -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Width Top (mm) <span class="text-red-500">*</span></label>
                            <input v-model.number="item.width_top" type="number" min="1" step="0.5" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 bg-white" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Width Middle (mm) <span class="text-red-500">*</span></label>
                            <input v-model.number="item.width_middle" type="number" min="1" step="0.5" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 bg-white" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Width Bottom (mm) <span class="text-red-500">*</span></label>
                            <input v-model.number="item.width_bottom" type="number" min="1" step="0.5" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 bg-white" />
                        </div>

                        <!-- Heights -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Height Left (mm) <span class="text-red-500">*</span></label>
                            <input v-model.number="item.height_left" type="number" min="1" step="0.5" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 bg-white" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Height Right (mm) <span class="text-red-500">*</span></label>
                            <input v-model.number="item.height_right" type="number" min="1" step="0.5" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 bg-white" />
                        </div>

                        <!-- Quantity -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Quantity <span class="text-red-500">*</span></label>
                            <input v-model.number="item.quantity" type="number" min="1" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 bg-white" />
                        </div>
                    </div>

                    <!-- Live Calculation Preview -->
                    <div class="mt-3 p-3 bg-blue-50 rounded-lg grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                        <div>
                            <span class="text-gray-500">Final Width</span>
                            <p class="font-semibold text-gray-800">{{ calcItem(item).fw.toFixed(0) }} mm</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Final Height</span>
                            <p class="font-semibold text-gray-800">{{ calcItem(item).fh.toFixed(0) }} mm</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Area</span>
                            <p class="font-semibold text-gray-800">{{ calcItem(item).area.toFixed(4) }} m²</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Total Area (×{{ item.quantity || 1 }})</span>
                            <p class="font-semibold text-blue-700">{{ calcItem(item).totalArea.toFixed(4) }} m²</p>
                        </div>
                    </div>
                </div>

                <!-- Grand Total -->
                <div v-if="form.items.length > 0" class="mt-4 flex justify-end">
                    <div class="bg-gray-900 text-white rounded-lg px-5 py-3 text-sm">
                        <span class="text-gray-400 mr-3">Grand Total Area</span>
                        <span class="font-bold text-lg">{{ grandTotal.toFixed(4) }} m²</span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3">
                <Link :href="route('cms.measurements.index')" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition">Cancel</Link>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 transition"
                >
                    {{ form.processing ? 'Saving…' : 'Save Measurement' }}
                </button>
            </div>
        </form>
    </div>
</template>
