<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import StockAuditLayout from '@/layouts/StockAuditLayout.vue';
import { useCurrency } from '@/composables/useCurrency';
import { ref, computed } from 'vue';
import { PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';

interface Item {
    id: number;
    name: string;
    system_quantity: number;
    unit_price: number;
    unit: string;
}

interface Props {
    items: Item[];
}

defineProps<Props>();

const form = ref({
    sale_date: new Date().toISOString().slice(0, 10),
    payment_method: 'cash',
    amount_tendered: 0,
    notes: '',
});

interface SaleLineItem {
    sa_item_id: number | null;
    quantity: number;
    unit_price: number;
}

const lineItems = ref<SaleLineItem[]>([]);

const errors = ref<Record<string, string>>({});
const processing = ref(false);

const addLineItem = () => {
    lineItems.value.push({ sa_item_id: null, quantity: 1, unit_price: 0 });
};

const removeLineItem = (index: number) => {
    lineItems.value.splice(index, 1);
};

const totalAmount = computed(() => {
    return lineItems.value.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
});

const submit = () => {
    processing.value = true;
    router.post(route('stockflow.sub.sales.store'), {
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
    <StockAuditLayout title="New Sale">
        <Head title="New Sale - StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link :href="route('stockflow.sub.sales.index')" class="text-sm text-emerald-600 hover:text-emerald-700">&larr; Back to Sales</Link>
                    <h1 class="mt-2 text-2xl font-bold text-gray-900">New Sale</h1>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Sale Info -->
                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-gray-900">Sale Information</h2>
                        <div class="mt-4 grid gap-4 sm:grid-cols-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Sale Date *</label>
                                <input v-model="form.sale_date" type="date" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                                <p v-if="errors.sale_date" class="mt-1 text-sm text-red-600">{{ errors.sale_date }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Payment Method *</label>
                                <select v-model="form.payment_method" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                    <option value="cash">Cash</option>
                                    <option value="mobile_money">Mobile Money</option>
                                    <option value="card">Card</option>
                                    <option value="credit">Credit</option>
                                    <option value="transfer">Transfer</option>
                                </select>
                                <p v-if="errors.payment_method" class="mt-1 text-sm text-red-600">{{ errors.payment_method }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Amount Tendered *</label>
                                <input v-model.number="form.amount_tendered" type="number" step="0.01" min="0" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                                <p v-if="errors.amount_tendered" class="mt-1 text-sm text-red-600">{{ errors.amount_tendered }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Line Items -->
                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900">Items</h2>
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
                                            {{ item.name }} ({{ item.system_quantity }} {{ item.unit }} in stock)
                                        </option>
                                    </select>
                                </div>
                                <div class="w-24">
                                    <label class="block text-sm font-medium text-gray-700">Qty *</label>
                                    <input v-model.number="lineItem.quantity" type="number" step="0.01" min="0.01" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                                </div>
                                <div class="w-32">
                                    <label class="block text-sm font-medium text-gray-700">Unit Price *</label>
                                    <input v-model.number="lineItem.unit_price" type="number" step="0.01" min="0" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                                </div>
                                <div class="w-24 pt-6 text-right">
                                    <p class="text-sm font-semibold text-gray-900">{{ formatCurrency(lineItem.quantity * lineItem.unit_price) }}</p>
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

                    <!-- Notes -->
                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea v-model="form.notes" rows="2" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="submit" :disabled="processing" class="rounded-lg bg-emerald-600 px-6 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50">
                            {{ processing ? 'Recording Sale...' : 'Record Sale' }}
                        </button>
                        <Link :href="route('stockflow.sub.sales.index')" class="text-sm text-gray-600 hover:text-gray-800">Cancel</Link>
                    </div>
                </form>
            </div>
        </div>
    </StockAuditLayout>
</template>
