<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { ArrowLeftIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';

interface Product {
    id: number;
    name: string;
    price: number;
    sale_price?: number;
}

interface Customer {
    id: number;
    name: string;
    phone?: string;
}

interface Props {
    products: Product[];
    customers: Customer[];
}

const props = defineProps<Props>();

// Get today's date in YYYY-MM-DD format
const today = new Date().toISOString().split('T')[0];

const form = useForm({
    product_id: null as number | null,
    product_name: '',
    customer_id: null as number | null,
    quantity: 1,
    unit_price: 0,
    sale_date: today,
    payment_method: 'cash',
    notes: '',
});

// Custom product name for non-catalog items
const useCustomProduct = ref(false);

const onProductChange = () => {
    if (form.product_id) {
        const product = props.products.find(p => p.id === form.product_id);
        if (product) {
            form.product_name = product.name;
            form.unit_price = product.sale_price || product.price;
        }
        useCustomProduct.value = false;
    }
};

const toggleCustomProduct = () => {
    useCustomProduct.value = !useCustomProduct.value;
    if (useCustomProduct.value) {
        form.product_id = null;
        form.product_name = '';
        form.unit_price = 0;
    }
};

const total = computed(() => {
    return form.quantity * form.unit_price;
});

const canSubmit = computed(() => {
    return form.product_name.trim() !== '' && 
           form.quantity > 0 && 
           form.unit_price >= 0 &&
           form.sale_date !== '';
});

const submit = () => {
    if (!canSubmit.value) return;
    form.post('/bizboost/sales');
};
</script>

<template>
    <Head title="Record Sale - BizBoost" />
    <BizBoostLayout title="Record Sale">
        <div class="max-w-3xl">
            <Link
                href="/bizboost/sales"
                class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mb-6"
            >
                <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                Back to Sales
            </Link>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Product Selection -->
                <div class="rounded-xl bg-white dark:bg-slate-800 p-6 shadow-sm ring-1 ring-gray-200 dark:ring-slate-700">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Product</h2>
                        <button
                            type="button"
                            @click="toggleCustomProduct"
                            class="text-sm text-violet-600 hover:text-violet-700 dark:text-violet-400"
                        >
                            {{ useCustomProduct ? 'Select from catalog' : 'Enter custom product' }}
                        </button>
                    </div>

                    <div class="space-y-4">
                        <!-- Product from catalog -->
                        <div v-if="!useCustomProduct">
                            <label for="product" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select Product</label>
                            <select
                                id="product"
                                v-model="form.product_id"
                                @change="onProductChange"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-violet-500 focus:ring-violet-500"
                            >
                                <option :value="null">Select a product...</option>
                                <option v-for="product in products" :key="product.id" :value="product.id">
                                    {{ product.name }} - K{{ (product.sale_price || product.price).toLocaleString() }}
                                </option>
                            </select>
                            <p v-if="form.errors.product_id" class="mt-1 text-sm text-red-600">{{ form.errors.product_id }}</p>
                        </div>

                        <!-- Custom product name -->
                        <div v-else>
                            <label for="product_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Product Name</label>
                            <input
                                id="product_name"
                                v-model="form.product_name"
                                type="text"
                                placeholder="Enter product or service name"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-violet-500 focus:ring-violet-500"
                            />
                            <p v-if="form.errors.product_name" class="mt-1 text-sm text-red-600">{{ form.errors.product_name }}</p>
                        </div>

                        <!-- Quantity and Price -->
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div>
                                <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantity</label>
                                <input
                                    id="quantity"
                                    v-model.number="form.quantity"
                                    type="number"
                                    min="1"
                                    class="mt-1 block w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-violet-500 focus:ring-violet-500"
                                />
                                <p v-if="form.errors.quantity" class="mt-1 text-sm text-red-600">{{ form.errors.quantity }}</p>
                            </div>
                            <div>
                                <label for="unit_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unit Price (K)</label>
                                <input
                                    id="unit_price"
                                    v-model.number="form.unit_price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="mt-1 block w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-violet-500 focus:ring-violet-500"
                                />
                                <p v-if="form.errors.unit_price" class="mt-1 text-sm text-red-600">{{ form.errors.unit_price }}</p>
                            </div>
                            <div class="flex items-end">
                                <div class="w-full p-3 bg-violet-50 dark:bg-violet-900/30 rounded-lg text-center">
                                    <p class="text-xs text-violet-600 dark:text-violet-400">Total</p>
                                    <p class="text-lg font-bold text-violet-700 dark:text-violet-300">K{{ total.toLocaleString() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sale Details -->
                <div class="rounded-xl bg-white dark:bg-slate-800 p-6 shadow-sm ring-1 ring-gray-200 dark:ring-slate-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Sale Details</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="sale_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sale Date</label>
                            <input
                                id="sale_date"
                                v-model="form.sale_date"
                                type="date"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-violet-500 focus:ring-violet-500"
                            />
                            <p v-if="form.errors.sale_date" class="mt-1 text-sm text-red-600">{{ form.errors.sale_date }}</p>
                        </div>
                        <div>
                            <label for="payment" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Method</label>
                            <select
                                id="payment"
                                v-model="form.payment_method"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-violet-500 focus:ring-violet-500"
                            >
                                <option value="cash">Cash</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="card">Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label for="customer" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer (Optional)</label>
                            <select
                                id="customer"
                                v-model="form.customer_id"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-violet-500 focus:ring-violet-500"
                            >
                                <option :value="null">Walk-in Customer</option>
                                <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                                    {{ customer.name }}{{ customer.phone ? ` - ${customer.phone}` : '' }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="rounded-xl bg-white dark:bg-slate-800 p-6 shadow-sm ring-1 ring-gray-200 dark:ring-slate-700">
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes (Optional)</label>
                    <textarea
                        id="notes"
                        v-model="form.notes"
                        rows="2"
                        class="mt-1 block w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-violet-500 focus:ring-violet-500"
                        placeholder="Any additional notes about this sale..."
                    ></textarea>
                </div>

                <!-- Submit -->
                <div class="flex gap-3">
                    <button
                        type="submit"
                        :disabled="form.processing || !canSubmit"
                        class="flex-1 rounded-lg bg-violet-600 px-4 py-3 text-sm font-medium text-white hover:bg-violet-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        {{ form.processing ? 'Recording...' : `Record Sale - K${total.toLocaleString()}` }}
                    </button>
                    <Link
                        href="/bizboost/sales"
                        class="rounded-lg border border-gray-300 dark:border-slate-600 px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors"
                    >
                        Cancel
                    </Link>
                </div>
            </form>
        </div>
    </BizBoostLayout>
</template>
