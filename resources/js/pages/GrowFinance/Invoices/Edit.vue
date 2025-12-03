<script setup lang="ts">
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ArrowLeftIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';

interface Customer {
    id: number;
    name: string;
}

interface InvoiceItem {
    id?: number;
    description: string;
    quantity: number;
    unit_price: number;
    amount: number;
}

interface Invoice {
    id: number;
    customer_id: number;
    invoice_date: string;
    due_date: string;
    notes: string | null;
    items: InvoiceItem[];
}

const props = defineProps<{
    invoice: Invoice;
    customers: Customer[];
}>();

const form = useForm({
    customer_id: props.invoice.customer_id,
    invoice_date: props.invoice.invoice_date.split('T')[0],
    due_date: props.invoice.due_date.split('T')[0],
    notes: props.invoice.notes || '',
    items: props.invoice.items.map(item => ({
        description: item.description,
        quantity: item.quantity,
        unit_price: item.unit_price,
        amount: item.amount,
    })),
});

const addItem = () => {
    form.items.push({
        description: '',
        quantity: 1,
        unit_price: 0,
        amount: 0,
    });
};

const removeItem = (index: number) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const updateItemAmount = (index: number) => {
    const item = form.items[index];
    item.amount = item.quantity * item.unit_price;
};

const subtotal = computed(() => {
    return form.items.reduce((sum, item) => sum + (item.amount || 0), 0);
});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 2,
    }).format(amount);
};

const submit = () => {
    form.put(route('growfinance.invoices.update', props.invoice.id));
};
</script>

<template>
    <GrowFinanceLayout>
        <Head title="Edit Invoice" />

        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex items-center gap-4 mb-6">
                <Link
                    :href="route('growfinance.invoices.index')"
                    class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                >
                    <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
                </Link>
                <h1 class="text-2xl font-bold text-gray-900">Edit Invoice</h1>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Invoice Details -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Invoice Details</h2>
                    
                    <div class="grid grid-cols-3 gap-6">
                        <div>
                            <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Customer <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="customer_id"
                                v-model="form.customer_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required
                            >
                                <option value="">Select customer</option>
                                <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                                    {{ customer.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.customer_id" class="mt-1 text-sm text-red-600">{{ form.errors.customer_id }}</p>
                        </div>

                        <div>
                            <label for="invoice_date" class="block text-sm font-medium text-gray-700 mb-1">
                                Invoice Date <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="invoice_date"
                                v-model="form.invoice_date"
                                type="date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required
                            />
                        </div>

                        <div>
                            <label for="due_date" class="block text-sm font-medium text-gray-700 mb-1">
                                Due Date <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="due_date"
                                v-model="form.due_date"
                                type="date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required
                            />
                        </div>
                    </div>
                </div>

                <!-- Line Items -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Line Items</h2>
                        <button
                            type="button"
                            @click="addItem"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg"
                        >
                            <PlusIcon class="h-4 w-4" aria-hidden="true" />
                            Add Item
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div
                            v-for="(item, index) in form.items"
                            :key="index"
                            class="grid grid-cols-12 gap-4 items-start"
                        >
                            <div class="col-span-5">
                                <label v-if="index === 0" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <input
                                    v-model="item.description"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Item description"
                                    required
                                />
                            </div>
                            <div class="col-span-2">
                                <label v-if="index === 0" class="block text-sm font-medium text-gray-700 mb-1">Qty</label>
                                <input
                                    v-model.number="item.quantity"
                                    type="number"
                                    min="1"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    @input="updateItemAmount(index)"
                                    required
                                />
                            </div>
                            <div class="col-span-2">
                                <label v-if="index === 0" class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                                <input
                                    v-model.number="item.unit_price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    @input="updateItemAmount(index)"
                                    required
                                />
                            </div>
                            <div class="col-span-2">
                                <label v-if="index === 0" class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                                <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-700">
                                    {{ formatCurrency(item.amount) }}
                                </div>
                            </div>
                            <div class="col-span-1 flex items-end">
                                <button
                                    v-if="form.items.length > 1"
                                    type="button"
                                    @click="removeItem(index)"
                                    class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg"
                                    :class="{ 'mt-6': index === 0 }"
                                >
                                    <TrashIcon class="h-5 w-5" aria-hidden="true" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <div class="flex justify-end">
                            <div class="w-64 space-y-2">
                                <div class="flex justify-between text-lg font-semibold">
                                    <span>Total</span>
                                    <span>{{ formatCurrency(subtotal) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                        Notes
                    </label>
                    <textarea
                        id="notes"
                        v-model="form.notes"
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Additional notes for the customer"
                    ></textarea>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3">
                    <Link
                        :href="route('growfinance.invoices.index')"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </GrowFinanceLayout>
</template>
