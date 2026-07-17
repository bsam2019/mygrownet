<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { useCurrency } from '@/composables/useCurrency';
import { useStockflowRoute } from '@/composables/useStockflowRoute';
import { ref, computed } from 'vue';
import { PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';

const { route } = useStockflowRoute();

interface Warehouse {
    id: number;
    name: string;
}

interface Item {
    id: number;
    name: string;
    system_quantity: number;
    unit_price: number;
    unit: string;
}

interface Props {
    warehouses: Warehouse[];
    items: Item[];
}

defineProps<Props>();

const form = ref({
    from_warehouse_id: '',
    to_warehouse_id: '',
    notes: '',
});

interface TransferLineItem {
    sa_item_id: number | null;
    quantity: number;
    unit_cost: number | null;
}

const lineItems = ref<TransferLineItem[]>([]);

const errors = ref<Record<string, string>>({});
const processing = ref(false);

const addLineItem = () => {
    lineItems.value.push({ sa_item_id: null, quantity: 1, unit_cost: null });
};

const removeLineItem = (index: number) => {
    lineItems.value.splice(index, 1);
};

const totalItems = computed(() => {
    return lineItems.value.reduce((sum, item) => sum + item.quantity, 0);
});

const submit = () => {
    processing.value = true;
    router.post(route('stockflow.sub.transfers.store'), {
        ...form.value,
        items: lineItems.value,
    }, {
        onSuccess: () => { processing.value = false; },
        onError: (err) => { errors.value = err; processing.value = false; },
    });
};

const { formatCurrency } = useCurrency();
</script>

<template>
    <StockFlowLayout title="New Transfer">
        <Head title="New Transfer - StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="mb-4">
                    <Link :href="route('stockflow.sub.transfers.index')" class="text-sm text-emerald-600 hover:text-emerald-700">&larr; Back to Transfers</Link>
                </div>

                <div class="mb-6 overflow-hidden rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-500 px-6 py-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/20">
                            <PlusIcon class="h-6 w-6 text-white" />
                        </div>
                        <div>
                            <h1 class="text-lg font-semibold text-white">New Warehouse Transfer</h1>
                            <p class="text-sm text-emerald-100">Transfer stock between warehouses</p>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 overflow-hidden">
                        <div class="border-b border-gray-100 px-6 py-4">
                            <h2 class="text-base font-semibold text-gray-900">Transfer Details</h2>
                        </div>
                        <div class="px-6 py-5">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">From Warehouse *</label>
                                    <select v-model="form.from_warehouse_id" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600">
                                        <option value="">Select source warehouse</option>
                                        <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                                    </select>
                                    <p v-if="errors.from_warehouse_id" class="mt-1 text-sm text-red-600">{{ errors.from_warehouse_id }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">To Warehouse *</label>
                                    <select v-model="form.to_warehouse_id" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600">
                                        <option value="">Select destination warehouse</option>
                                        <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                                    </select>
                                    <p v-if="errors.to_warehouse_id" class="mt-1 text-sm text-red-600">{{ errors.to_warehouse_id }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 overflow-hidden">
                        <div class="border-b border-gray-100 px-6 py-4">
                            <div class="flex items-center justify-between">
                                <h2 class="text-base font-semibold text-gray-900">Items to Transfer</h2>
                                <button type="button" @click="addLineItem" class="inline-flex items-center gap-1 text-sm font-medium text-emerald-600 hover:text-emerald-700">
                                    <PlusIcon class="h-4 w-4" aria-hidden="true" />
                                    Add Item
                                </button>
                            </div>
                        </div>
                        <div class="px-6 py-5">
                            <div v-if="lineItems.length === 0" class="py-8 text-center text-gray-500">
                                <p>No items added yet. Click "Add Item" to start.</p>
                            </div>

                            <div v-for="(lineItem, index) in lineItems" :key="index" class="border-t border-gray-100 pt-4">
                                <div class="mt-4 flex items-start gap-4">
                                    <div class="flex-1">
                                        <label class="block text-sm font-medium text-gray-700">Item *</label>
                                        <select v-model="lineItem.sa_item_id" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600">
                                            <option :value="null">Select item</option>
                                            <option v-for="item in items" :key="item.id" :value="item.id">
                                                {{ item.name }} ({{ item.system_quantity }} {{ item.unit }} in stock)
                                            </option>
                                        </select>
                                    </div>
                                    <div class="w-24">
                                        <label class="block text-sm font-medium text-gray-700">Qty *</label>
                                        <input v-model.number="lineItem.quantity" type="number" step="0.01" min="0.01" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600" />
                                    </div>
                                    <div class="w-32">
                                        <label class="block text-sm font-medium text-gray-700">Unit Cost</label>
                                        <input v-model.number="lineItem.unit_cost" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600" />
                                    </div>
                                    <button type="button" @click="removeLineItem(index)" class="mt-6 p-1 text-gray-400 hover:text-red-600">
                                        <TrashIcon class="h-5 w-5" aria-hidden="true" />
                                    </button>
                                </div>
                            </div>

                            <div v-if="lineItems.length > 0" class="mt-6 border-t border-gray-200 pt-4">
                                <div class="flex justify-end">
                                    <div class="text-right">
                                        <p class="text-sm text-gray-500">Total Items</p>
                                        <p class="text-2xl font-bold text-gray-900">{{ totalItems }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 overflow-hidden">
                        <div class="border-b border-gray-100 px-6 py-4">
                            <h2 class="text-base font-semibold text-gray-900">Notes</h2>
                        </div>
                        <div class="px-6 py-5">
                            <textarea v-model="form.notes" rows="2" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600"></textarea>
                        </div>
                    </div>

                    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 overflow-hidden">
                        <div class="flex items-center justify-end gap-3 px-6 py-4">
                            <Link :href="route('stockflow.sub.transfers.index')" class="text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">Cancel</Link>
                            <button type="submit" :disabled="processing" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                {{ processing ? 'Creating Transfer...' : 'Create Transfer' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </StockFlowLayout>
</template>
