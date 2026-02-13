<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { PlusIcon, TrashIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';
import CMSLayoutNew from '@/Layouts/CMSLayoutNew.vue';

defineOptions({
  layout: CMSLayoutNew
})

interface Customer {
    id: number;
    name: string;
    email: string;
    phone: string;
}

interface Props {
    customers: Customer[];
}

const props = defineProps<Props>();

const form = useForm({
    customer_id: null as number | null,
    quotation_date: new Date().toISOString().split('T')[0],
    expiry_date: '',
    items: [
        { description: '', quantity: 1, unit_price: 0, tax_rate: 0, discount_rate: 0, line_total: 0 },
    ],
    tax_amount: 0,
    discount_amount: 0,
    notes: '',
    terms: '',
});

const addItem = () => {
    form.items.push({ description: '', quantity: 1, unit_price: 0, tax_rate: 0, discount_rate: 0, line_total: 0 });
};

const removeItem = (index: number) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
        recalculateTotals();
    }
};

const calculateLineTotal = (item: any) => {
    const subtotal = item.quantity * item.unit_price;
    const discount = subtotal * (item.discount_rate / 100);
    const afterDiscount = subtotal - discount;
    const tax = afterDiscount * (item.tax_rate / 100);
    item.line_total = afterDiscount + tax;
    return item.line_total;
};

const recalculateTotals = () => {
    form.tax_amount = form.items.reduce((sum, item) => {
        const subtotal = item.quantity * item.unit_price;
        const discount = subtotal * (item.discount_rate / 100);
        const afterDiscount = subtotal - discount;
        return sum + (afterDiscount * (item.tax_rate / 100));
    }, 0);

    form.discount_amount = form.items.reduce((sum, item) => {
        const subtotal = item.quantity * item.unit_price;
        return sum + (subtotal * (item.discount_rate / 100));
    }, 0);
};

const calculateSubtotal = () => {
    return form.items.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
};

const calculateTotal = () => {
    return form.items.reduce((sum, item) => sum + item.line_total, 0);
};

const submit = () => {
    form.post(route('cms.quotations.store'));
};

const formatCurrency = (amount: number) => {
    return `K${amount.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
};
</script>

<template>
    <Head title="Create Quotation - CMS" />

    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="mb-6">
                <button
                    @click="$inertia.visit(route('cms.quotations.index'))"
                    class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-3 transition-colors"
                >
                    <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
                    Back to Quotations
                </button>
                <h1 class="text-2xl font-bold text-gray-900">Create Quotation</h1>
                <p class="mt-1 text-sm text-gray-500">Create a new quotation for a customer</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Customer & Dates -->
                <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Quotation Details</h2>
                    
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Customer <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.customer_id"
                                required
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors duration-200"
                            >
                                <option :value="null">Select customer...</option>
                                <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                                    {{ customer.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.customer_id" class="mt-1 text-sm text-red-600">{{ form.errors.customer_id }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Quotation Date <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.quotation_date"
                                type="date"
                                required
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors duration-200"
                            />
                            <p v-if="form.errors.quotation_date" class="mt-1 text-sm text-red-600">{{ form.errors.quotation_date }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                            <input
                                v-model="form.expiry_date"
                                type="date"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors duration-200"
                            />
                            <p v-if="form.errors.expiry_date" class="mt-1 text-sm text-red-600">{{ form.errors.expiry_date }}</p>
                        </div>
                    </div>
                </div>

                <!-- Quotation Items -->
                <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">Line Items</h2>
                        <button
                            type="button"
                            @click="addItem"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                        >
                            <PlusIcon class="h-4 w-4" aria-hidden="true" />
                            Add Item
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Description</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Qty</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Price</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Discount %</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Tax %</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Total</th>
                                    <th class="px-3 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="(item, index) in form.items" :key="index">
                                    <td class="px-3 py-2">
                                        <input
                                            v-model="item.description"
                                            type="text"
                                            placeholder="Description"
                                            required
                                            class="block w-full min-w-[200px] rounded border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500 transition-colors duration-200"
                                        />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input
                                            v-model.number="item.quantity"
                                            type="number"
                                            step="0.01"
                                            min="0.01"
                                            required
                                            @input="calculateLineTotal(item); recalculateTotals()"
                                            class="block w-20 rounded border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500 transition-colors duration-200"
                                        />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input
                                            v-model.number="item.unit_price"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            required
                                            @input="calculateLineTotal(item); recalculateTotals()"
                                            class="block w-24 rounded border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500 transition-colors duration-200"
                                        />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input
                                            v-model.number="item.discount_rate"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                            @input="calculateLineTotal(item); recalculateTotals()"
                                            class="block w-20 rounded border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500 transition-colors duration-200"
                                        />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input
                                            v-model.number="item.tax_rate"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                            @input="calculateLineTotal(item); recalculateTotals()"
                                            class="block w-20 rounded border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500 transition-colors duration-200"
                                        />
                                    </td>
                                    <td class="px-3 py-2 text-sm font-medium text-gray-900">
                                        {{ formatCurrency(item.line_total) }}
                                    </td>
                                    <td class="px-3 py-2">
                                        <button
                                            v-if="form.items.length > 1"
                                            type="button"
                                            @click="removeItem(index)"
                                            class="text-red-600 hover:text-red-800 transition-colors"
                                            aria-label="Remove item"
                                        >
                                            <TrashIcon class="h-5 w-5" aria-hidden="true" />
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals -->
                    <div class="mt-6 border-t pt-4">
                        <div class="flex justify-end">
                            <div class="w-64 space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Subtotal:</span>
                                    <span class="font-medium">{{ formatCurrency(calculateSubtotal()) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Discount:</span>
                                    <span class="font-medium text-red-600">-{{ formatCurrency(form.discount_amount) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Tax:</span>
                                    <span class="font-medium">{{ formatCurrency(form.tax_amount) }}</span>
                                </div>
                                <div class="flex justify-between border-t pt-2 text-lg font-bold">
                                    <span>Total:</span>
                                    <span>{{ formatCurrency(calculateTotal()) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes & Terms -->
                <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Additional Information</h2>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea
                                v-model="form.notes"
                                rows="4"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors duration-200"
                                placeholder="Internal notes..."
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Terms & Conditions</label>
                            <textarea
                                v-model="form.terms"
                                rows="4"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors duration-200"
                                placeholder="Payment terms, delivery conditions, etc..."
                            />
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3">
                    <button
                        type="button"
                        @click="$inertia.visit(route('cms.quotations.index'))"
                        class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <span v-if="form.processing">Creating...</span>
                        <span v-else>Create Quotation</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
