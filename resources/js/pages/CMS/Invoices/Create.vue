<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
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
    outstanding_balance: number;
}

interface Props {
    customers: Customer[];
}

const props = defineProps<Props>();

const form = useForm({
    customer_id: null as number | null,
    due_date: '',
    notes: '',
    items: [
        { description: '', quantity: 1, unit_price: 0 },
    ],
});

const addItem = () => {
    form.items.push({ description: '', quantity: 1, unit_price: 0 });
};

const removeItem = (index: number) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const calculateLineTotal = (item: any) => {
    return item.quantity * item.unit_price;
};

const calculateSubtotal = () => {
    return form.items.reduce((sum, item) => sum + calculateLineTotal(item), 0);
};

const submit = () => {
    form.post(route('cms.invoices.store'));
};

const formatCurrency = (amount: number) => {
    return `K${amount.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
};
</script>

<template>
    <Head title="Create Invoice - CMS" />

    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <div class="max-w-5xl mx-auto">
            <!-- Header -->
            <div class="mb-6">
                <button
                    @click="$inertia.visit(route('cms.invoices.index'))"
                    class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-3 transition-colors"
                >
                    <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
                    Back to Invoices
                </button>
                <h1 class="text-2xl font-bold text-gray-900">Create Invoice</h1>
                <p class="mt-1 text-sm text-gray-500">Create a new invoice for a customer</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Customer Selection -->
                <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Customer Information</h2>
                    
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
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
                                    {{ customer.name }} ({{ formatCurrency(customer.outstanding_balance) }} outstanding)
                                </option>
                            </select>
                            <p v-if="form.errors.customer_id" class="mt-1 text-sm text-red-600">{{ form.errors.customer_id }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                            <input
                                v-model="form.due_date"
                                type="date"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors duration-200"
                            />
                            <p v-if="form.errors.due_date" class="mt-1 text-sm text-red-600">{{ form.errors.due_date }}</p>
                        </div>
                    </div>
                </div>

                <!-- Invoice Items -->
                <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">Invoice Items</h2>
                        <button
                            type="button"
                            @click="addItem"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                        >
                            <PlusIcon class="h-4 w-4" aria-hidden="true" />
                            Add Item
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div v-for="(item, index) in form.items" :key="index" class="flex gap-4">
                            <div class="flex-1">
                                <input
                                    v-model="item.description"
                                    type="text"
                                    placeholder="Description"
                                    required
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors duration-200"
                                />
                            </div>
                            <div class="w-24">
                                <input
                                    v-model.number="item.quantity"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    placeholder="Qty"
                                    required
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors duration-200"
                                />
                            </div>
                            <div class="w-32">
                                <input
                                    v-model.number="item.unit_price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="Price"
                                    required
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors duration-200"
                                />
                            </div>
                            <div class="w-32 flex items-center">
                                <span class="text-sm font-medium text-gray-900">
                                    {{ formatCurrency(calculateLineTotal(item)) }}
                                </span>
                            </div>
                            <button
                                v-if="form.items.length > 1"
                                type="button"
                                @click="removeItem(index)"
                                class="text-red-600 hover:text-red-800 transition-colors"
                                aria-label="Remove item"
                            >
                                <TrashIcon class="h-5 w-5" aria-hidden="true" />
                            </button>
                        </div>
                    </div>

                    <div class="mt-6 border-t pt-4">
                        <div class="flex justify-end">
                            <div class="w-64">
                                <div class="flex justify-between text-lg font-bold">
                                    <span>Total:</span>
                                    <span>{{ formatCurrency(calculateSubtotal()) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Additional Information</h2>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea
                            v-model="form.notes"
                            rows="3"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors duration-200"
                            placeholder="Add any notes or special instructions..."
                        />
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3">
                    <button
                        type="button"
                        @click="$inertia.visit(route('cms.invoices.index'))"
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
                        <span v-else>Create Invoice</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
