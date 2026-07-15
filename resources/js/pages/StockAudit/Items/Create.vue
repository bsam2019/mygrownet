<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import StockAuditLayout from '@/layouts/StockAuditLayout.vue';
import { ref } from 'vue';

interface Bin {
    id: number;
    name: string;
    label: string | null;
}

interface Props {
    bins: Bin[];
}

defineProps<Props>();

const form = ref({
    name: '',
    sku: '',
    description: '',
    unit_price: 0,
    unit: 'pcs',
    system_quantity: 0,
    sa_bin_id: '',
    category: '',
    is_expirable: false,
    expiry_date: '',
    notes: '',
});

const errors = ref<Record<string, string>>({});
const processing = ref(false);

const submit = () => {
    processing.value = true;
    router.post(route('stockflow.sub.items.store'), form.value, {
        onSuccess: () => { processing.value = false; },
        onError: (err) => { errors.value = err; processing.value = false; },
    });
};
</script>

<template>
    <StockAuditLayout title="Create Item">
        <Head title="Create Item - StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link :href="route('stockflow.sub.items.index')" class="text-sm text-emerald-600 hover:text-emerald-700">&larr; Back to Items</Link>
                    <h1 class="mt-2 text-2xl font-bold text-gray-900">Create New Item</h1>
                </div>

                <form @submit.prevent="submit" class="space-y-6 rounded-xl bg-white p-6 shadow-sm">
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name *</label>
                            <input v-model="form.name" type="text" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">SKU</label>
                            <input v-model="form.sku" type="text" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Category</label>
                            <input v-model="form.category" type="text" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Unit</label>
                            <select v-model="form.unit" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="pcs">Pieces (pcs)</option>
                                <option value="kg">Kilograms (kg)</option>
                                <option value="g">Grams (g)</option>
                                <option value="l">Liters (l)</option>
                                <option value="ml">Milliliters (ml)</option>
                                <option value="box">Box</option>
                                <option value="pack">Pack</option>
                                <option value="bottle">Bottle</option>
                                <option value="unit">Unit</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Unit Price *</label>
                            <input v-model.number="form.unit_price" type="number" step="0.01" min="0" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                            <p v-if="errors.unit_price" class="mt-1 text-sm text-red-600">{{ errors.unit_price }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">System Quantity *</label>
                            <input v-model.number="form.system_quantity" type="number" step="0.01" min="0" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                            <p v-if="errors.system_quantity" class="mt-1 text-sm text-red-600">{{ errors.system_quantity }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Bin</label>
                            <select v-model="form.sa_bin_id" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">No bin</option>
                                <option v-for="bin in bins" :key="bin.id" :value="bin.id">{{ bin.label || bin.name }}</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <label class="flex items-center gap-2">
                                <input v-model="form.is_expirable" type="checkbox" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" />
                                <span class="text-sm font-medium text-gray-700">Expirable item</span>
                            </label>
                        </div>
                    </div>

                    <div v-if="form.is_expirable">
                        <label class="block text-sm font-medium text-gray-700">Expiry Date</label>
                        <input v-model="form.expiry_date" type="date" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea v-model="form.description" rows="3" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea v-model="form.notes" rows="2" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="submit" :disabled="processing" class="rounded-lg bg-emerald-600 px-6 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50">
                            {{ processing ? 'Creating...' : 'Create Item' }}
                        </button>
                        <Link :href="route('stockflow.sub.items.index')" class="text-sm text-gray-600 hover:text-gray-800">Cancel</Link>
                    </div>
                </form>
            </div>
        </div>
    </StockAuditLayout>
</template>
