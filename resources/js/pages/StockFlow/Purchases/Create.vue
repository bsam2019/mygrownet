<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { useCurrency } from '@/composables/useCurrency';
import { useStockflowRoute } from '@/composables/useStockflowRoute';
import { ref, computed } from 'vue';
import { PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';

const { route } = useStockflowRoute();

interface Supplier {
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
    suppliers: Supplier[];
    items: Item[];
}

defineProps<Props>();

const form = ref({
    sa_supplier_id: '',
    order_date: new Date().toISOString().slice(0, 10),
    notes: '',
});

interface POLineItem {
    sa_item_id: number | null;
    quantity_ordered: number;
    unit_cost: number;
}

const lineItems = ref<POLineItem[]>([]);

const errors = ref<Record<string, string>>({});
const processing = ref(false);

const addLineItem = () => {
    lineItems.value.push({ sa_item_id: null, quantity_ordered: 1, unit_cost: 0 });
};

const removeLineItem = (index: number) => {
    lineItems.value.splice(index, 1);
};

const totalAmount = computed(() => {
    return lineItems.value.reduce((sum, item) => sum + (item.quantity_ordered * item.unit_cost), 0);
});

const submit = () => {
    processing.value = true;
    router.post(route('stockflow.sub.purchases.store'), {
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
    <StockFlowLayout title="New Purchase Order">
        <Head title="New Purchase Order - StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link :href="route('stockflow.sub.purchases.index')" class="text-sm text-emerald-600 hover:text-emerald-700">&larr; Back to Purchases</Link>
                    <h1 class="mt-2 text-2xl font-bold text-gray-900">New Purchase Order</h1>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-gray-900">Order Information</h2>
                        <div class="mt-4 grid gap-4 sm:grid-cols-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Order Date *</label>
                                <input v-model="form.order_date" type="date" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                                <p v-if="errors.order_date" class="mt-1 text-sm text-red-600">{{ errors.order_date }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Supplier</label>
                                <select v-model="form.sa_supplier_id" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                    <option value="">Select supplier (optional)</option>
                                    <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900">Order Items</h2>
                            <button type="button" @click="addLineItem" class="inline-flex items-center gap-1 text-sm font-medium text-emerald-600 hover:text-emerald-700">
                                <PlusIcon class="h-4 w-4" aria-hidden="true" />
                                Add Item
                            </button>
                        </div>

                        <div v-if="lineItems.length === 0" class="mt-4 py-8 text-center text-gray-500">
                            <p>No items added yet. Click "Add Item" to start.</p>
                        </div>

                        <div v-for="(lineItem, index) in lineItems" :key="index" class="mt-4 border-t border-gray-100 pt-4">
                            <div class="flex items-start gap-4">
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700">Item *</label>
                                    <select v-model="lineItem.sa_item_id" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                        <option :value="null">Select item</option>
                                        <option v-for="item in items" :key="item.id" :value="item.id">
                                            {{ item.name }} ({{ item.unit_price }} / {{ item.unit }})
                                        </option>
                                    </select>
                                </div>
                                <div class="w-24">
                                    <label class="block text-sm font-medium text-gray-700">Qty *</label>
                                    <input v-model.number="lineItem.quantity_ordered" type="number" step="0.01" min="0.01" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                                </div>
                                <div class="w-32">
                                    <label class="block text-sm font-medium text-gray-700">Unit Cost *</label>
                                    <input v-model.number="lineItem.unit_cost" type="number" step="0.01" min="0" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                                </div>
                                <div class="w-24 pt-6 text-right">
                                    <p class="text-sm font-semibold text-gray-900">{{ formatCurrency(lineItem.quantity_ordered * lineItem.unit_cost) }}</p>
                                </div>
                                <button type="button" @click="removeLineItem(index)" class="mt-6 p-1 text-gray-400 hover:text-red-600">
                                    <TrashIcon class="h-5 w-5" aria-hidden="true" />
                                </button>
                            </div>
                        </div>

                        <div v-if="lineItems.length > 0" class="mt-6 border-t border-gray-200 pt-4">
                            <div class="flex justify-end">
                                <div class="text-right">
                                    <p class="text-sm text-gray-500">Total</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(totalAmount) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea v-model="form.notes" rows="2" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="submit" :disabled="processing" class="rounded-lg bg-emerald-600 px-6 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50">
                            {{ processing ? 'Creating...' : 'Create Purchase Order' }}
                        </button>
                        <Link :href="route('stockflow.sub.purchases.index')" class="text-sm text-gray-600 hover:text-gray-800">Cancel</Link>
                    </div>
                </form>
            </div>
        </div>
    </StockFlowLayout>
</template>
