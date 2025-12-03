<script setup lang="ts">
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

const form = useForm({
    code: '',
    name: '',
    type: 'asset',
    subtype: '',
    description: '',
    is_active: true,
});

const accountTypes = [
    { value: 'asset', label: 'Asset' },
    { value: 'liability', label: 'Liability' },
    { value: 'equity', label: 'Equity' },
    { value: 'revenue', label: 'Revenue' },
    { value: 'expense', label: 'Expense' },
];

const subtypes: Record<string, { value: string; label: string }[]> = {
    asset: [
        { value: 'current_asset', label: 'Current Asset' },
        { value: 'fixed_asset', label: 'Fixed Asset' },
        { value: 'bank', label: 'Bank' },
        { value: 'cash', label: 'Cash' },
        { value: 'accounts_receivable', label: 'Accounts Receivable' },
    ],
    liability: [
        { value: 'current_liability', label: 'Current Liability' },
        { value: 'long_term_liability', label: 'Long Term Liability' },
        { value: 'accounts_payable', label: 'Accounts Payable' },
    ],
    equity: [
        { value: 'retained_earnings', label: 'Retained Earnings' },
        { value: 'owner_equity', label: 'Owner Equity' },
    ],
    revenue: [
        { value: 'sales', label: 'Sales' },
        { value: 'other_income', label: 'Other Income' },
    ],
    expense: [
        { value: 'cost_of_goods', label: 'Cost of Goods Sold' },
        { value: 'operating_expense', label: 'Operating Expense' },
        { value: 'payroll', label: 'Payroll' },
    ],
};

const submit = () => {
    form.post(route('growfinance.accounts.store'));
};
</script>

<template>
    <GrowFinanceLayout>
        <Head title="Create Account" />

        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="flex items-center gap-4 mb-6">
                <Link
                    :href="route('growfinance.accounts.index')"
                    class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                >
                    <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
                </Link>
                <h1 class="text-2xl font-bold text-gray-900">Create Account</h1>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                            Account Code <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="code"
                            v-model="form.code"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono"
                            placeholder="e.g., 1000"
                            required
                        />
                        <p v-if="form.errors.code" class="mt-1 text-sm text-red-600">{{ form.errors.code }}</p>
                    </div>

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Account Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="e.g., Cash on Hand"
                            required
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
                            Account Type <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="type"
                            v-model="form.type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required
                        >
                            <option v-for="type in accountTypes" :key="type.value" :value="type.value">
                                {{ type.label }}
                            </option>
                        </select>
                        <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">{{ form.errors.type }}</p>
                    </div>

                    <div>
                        <label for="subtype" class="block text-sm font-medium text-gray-700 mb-1">
                            Subtype
                        </label>
                        <select
                            id="subtype"
                            v-model="form.subtype"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">Select subtype</option>
                            <option v-for="sub in subtypes[form.type]" :key="sub.value" :value="sub.value">
                                {{ sub.label }}
                            </option>
                        </select>
                        <p v-if="form.errors.subtype" class="mt-1 text-sm text-red-600">{{ form.errors.subtype }}</p>
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                        Description
                    </label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Optional description for this account"
                    ></textarea>
                    <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                </div>

                <div class="flex items-center gap-2">
                    <input
                        id="is_active"
                        v-model="form.is_active"
                        type="checkbox"
                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                    />
                    <label for="is_active" class="text-sm text-gray-700">Active account</label>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <Link
                        :href="route('growfinance.accounts.index')"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Creating...' : 'Create Account' }}
                    </button>
                </div>
            </form>
        </div>
    </GrowFinanceLayout>
</template>
