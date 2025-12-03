<script setup lang="ts">
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

interface Account {
    id: number;
    code: string;
    name: string;
}

interface Vendor {
    id: number;
    name: string;
}

interface Expense {
    id: number;
    account_id: number;
    vendor_id: number | null;
    expense_date: string;
    category: string;
    description: string;
    amount: number;
    payment_method: string;
    reference: string | null;
    notes: string | null;
}

const props = defineProps<{
    expense: Expense;
    accounts: Account[];
    vendors: Vendor[];
    categories: string[];
}>();

const form = useForm({
    account_id: props.expense.account_id,
    vendor_id: props.expense.vendor_id,
    expense_date: props.expense.expense_date.split('T')[0],
    category: props.expense.category,
    description: props.expense.description || '',
    amount: props.expense.amount,
    payment_method: props.expense.payment_method,
    reference: props.expense.reference || '',
    notes: props.expense.notes || '',
});

const paymentMethods = [
    { value: 'cash', label: 'Cash' },
    { value: 'bank', label: 'Bank Transfer' },
    { value: 'mobile_money', label: 'Mobile Money' },
    { value: 'credit', label: 'Credit' },
];

const submit = () => {
    form.put(route('growfinance.expenses.update', props.expense.id));
};
</script>

<template>
    <GrowFinanceLayout>
        <Head title="Edit Expense" />

        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="flex items-center gap-4 mb-6">
                <Link
                    :href="route('growfinance.expenses.index')"
                    class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                >
                    <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
                </Link>
                <h1 class="text-2xl font-bold text-gray-900">Edit Expense</h1>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label for="expense_date" class="block text-sm font-medium text-gray-700 mb-1">
                            Date <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="expense_date"
                            v-model="form.expense_date"
                            type="date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required
                        />
                        <p v-if="form.errors.expense_date" class="mt-1 text-sm text-red-600">{{ form.errors.expense_date }}</p>
                    </div>

                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-1">
                            Category
                        </label>
                        <select
                            id="category"
                            v-model="form.category"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">Select category</option>
                            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label for="account_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Expense Account <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="account_id"
                            v-model="form.account_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required
                        >
                            <option value="">Select account</option>
                            <option v-for="account in accounts" :key="account.id" :value="account.id">
                                {{ account.code }} - {{ account.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.account_id" class="mt-1 text-sm text-red-600">{{ form.errors.account_id }}</p>
                    </div>

                    <div>
                        <label for="vendor_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Vendor
                        </label>
                        <select
                            id="vendor_id"
                            v-model="form.vendor_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option :value="null">No vendor</option>
                            <option v-for="vendor in vendors" :key="vendor.id" :value="vendor.id">
                                {{ vendor.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                        Description
                    </label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="What was this expense for?"
                    ></textarea>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">
                            Amount (ZMW) <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="amount"
                            v-model.number="form.amount"
                            type="number"
                            step="0.01"
                            min="0.01"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required
                        />
                        <p v-if="form.errors.amount" class="mt-1 text-sm text-red-600">{{ form.errors.amount }}</p>
                    </div>

                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">
                            Payment Method <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="payment_method"
                            v-model="form.payment_method"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required
                        >
                            <option v-for="method in paymentMethods" :key="method.value" :value="method.value">
                                {{ method.label }}
                            </option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="reference" class="block text-sm font-medium text-gray-700 mb-1">
                        Reference Number
                    </label>
                    <input
                        id="reference"
                        v-model="form.reference"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Receipt or invoice number"
                    />
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                        Notes
                    </label>
                    <textarea
                        id="notes"
                        v-model="form.notes"
                        rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Additional notes"
                    ></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <Link
                        :href="route('growfinance.expenses.index')"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </GrowFinanceLayout>
</template>
