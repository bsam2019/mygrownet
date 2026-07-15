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
    customer_name: '',
    customer_phone: '',
    customer_email: '',
    invoice_date: new Date().toISOString().slice(0, 10),
    due_date: '',
    discount: 0,
    tax: 0,
    payment_terms: '',
    notes: '',
});

interface LineItem {
    item_name: string;
    sa_item_id: number | null;
    quantity: number;
    unit_price: number;
}

const lineItems = ref<LineItem[]>([]);

const errors = ref<Record<string, string>>({});
const processing = ref(false);

const addLineItem = () => {
    lineItems.value.push({ item_name: '', sa_item_id: null, quantity: 1, unit_price: 0 });
};

const removeLineItem = (index: number) => {
    lineItems.value.splice(index, 1);
};

const selectItem = (index: number, item: Item | null) => {
    if (item) {
        lineItems.value[index].item_name = item.name;
        lineItems.value[index].unit_price = item.unit_price;
        lineItems.value[index].sa_item_id = item.id;
    }
};

const subtotal = computed(() => {
    return lineItems.value.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
});

const totalAmount = computed(() => {
    return subtotal.value + form.value.tax - form.value.discount;
});

const submit = () => {
    processing.value = true;
    router.post(route('stockflow.sub.invoices.store'), {
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
    <StockAuditLayout title="New Invoice">
        <Head title="New Invoice - StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link :href="route('stockflow.sub.invoices.index')" class="text-sm text-emerald-600 hover:text-emerald-700">&larr; Back to Invoices</Link>
                    <h1 class="mt-2 text-2xl font-bold text-gray-900">New Invoice</h1>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-gray-900">Customer Information</h2>
                        <div class="mt-4 grid gap-4 sm:grid-cols-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Customer Name</label>
                                <input v-model="form.customer_name" type="text" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Phone</label>
                                <input v-model="form.customer_phone" type="text" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input v-model="form.customer_email" type="email" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-gray-900">Invoice Details</h2>
                        <div class="mt-4 grid gap-4 sm:grid-cols-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Invoice Date *</label>
                                <input v-model="form.invoice_date" type="date" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                                <p v-if="errors.invoice_date" class="mt-1 text-sm text-red-600">{{ errors.invoice_date }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Due Date</label>
                                <input v-model="form.due_date" type="date" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Payment Terms</label>
                                <input v-model="form.payment_terms" type="text" placeholder="e.g. Net 30" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                            </div>
                        </div>
                    </div>

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
                                    <select v-model="lineItem.sa_item_id" @change="selectItem(index, items.find(i => i.id === lineItem.sa_item_id) || null)" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                        <option :value="null">Select or type item name</option>
                                        <option v-for="item in items" :key="item.id" :value="item.id">
                                            {{ item.name }}
                                        </option>
                                    </select>
                                </div>
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700">Item Name *</label>
                                    <input v-model="lineItem.item_name" type="text" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
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
                                <div class="w-64 space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500">Subtotal</span>
                                        <span class="text-gray-900">{{ formatCurrency(subtotal) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500">Discount</span>
                                        <input v-model.number="form.discount" type="number" step="0.01" min="0" class="w-28 rounded border-gray-300 text-right text-sm" />
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500">Tax</span>
                                        <input v-model.number="form.tax" type="number" step="0.01" min="0" class="w-28 rounded border-gray-300 text-right text-sm" />
                                    </div>
                                    <div class="flex justify-between border-t border-gray-200 pt-2 text-lg font-bold">
                                        <span class="text-gray-900">Total</span>
                                        <span class="text-gray-900">{{ formatCurrency(totalAmount) }}</span>
                                    </div>
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
                            {{ processing ? 'Creating Invoice...' : 'Create Invoice' }}
                        </button>
                        <Link :href="route('stockflow.sub.invoices.index')" class="text-sm text-gray-600 hover:text-gray-800">Cancel</Link>
                    </div>
                </form>
            </div>
        </div>
    </StockAuditLayout>
</template>
