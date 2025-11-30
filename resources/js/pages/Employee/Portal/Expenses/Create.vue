<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/Layouts/EmployeePortalLayout.vue';
import {
    ArrowLeftIcon,
    PaperClipIcon,
    XMarkIcon,
    CurrencyDollarIcon,
} from '@heroicons/vue/24/outline';

interface Props {
    categories: Record<string, string>;
}

const props = defineProps<Props>();

const form = useForm({
    title: '',
    description: '',
    category: '',
    amount: '',
    expense_date: new Date().toISOString().split('T')[0],
    receipts: [] as string[],
});

const submit = () => {
    form.post(route('employee.portal.expenses.store'));
};

const today = new Date().toISOString().split('T')[0];
</script>

<template>
    <Head title="Submit Expense" />

    <EmployeePortalLayout>
        <div class="max-w-2xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link :href="route('employee.portal.expenses.index')"
                    class="p-2 hover:bg-gray-100 rounded-lg">
                    <ArrowLeftIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Submit Expense</h1>
                    <p class="text-gray-500 mt-1">Create a new expense claim for reimbursement</p>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
                <!-- Category -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                        Category <span class="text-red-500">*</span>
                    </label>
                    <select id="category" v-model="form.category"
                        class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select a category</option>
                        <option v-for="(label, key) in categories" :key="key" :value="key">
                            {{ label }}
                        </option>
                    </select>
                    <p v-if="form.errors.category" class="mt-1 text-sm text-red-600">{{ form.errors.category }}</p>
                </div>

                <!-- Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="title" v-model="form.title"
                        placeholder="e.g., Client lunch meeting, Office supplies"
                        class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                    <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
                </div>

                <!-- Amount and Date -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                            Amount (ZMW) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <CurrencyDollarIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                            </div>
                            <input type="number" id="amount" v-model="form.amount"
                                step="0.01" min="0.01"
                                placeholder="0.00"
                                class="w-full pl-10 border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <p v-if="form.errors.amount" class="mt-1 text-sm text-red-600">{{ form.errors.amount }}</p>
                    </div>

                    <div>
                        <label for="expense_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Expense Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="expense_date" v-model="form.expense_date"
                            :max="today"
                            class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                        <p v-if="form.errors.expense_date" class="mt-1 text-sm text-red-600">{{ form.errors.expense_date }}</p>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description <span class="text-gray-400">(Optional)</span>
                    </label>
                    <textarea id="description" v-model="form.description" rows="3"
                        placeholder="Provide additional details about this expense..."
                        class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </textarea>
                </div>

                <!-- Receipt Upload Placeholder -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Receipts
                    </label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                        <PaperClipIcon class="h-8 w-8 mx-auto text-gray-400 mb-2" aria-hidden="true" />
                        <p class="text-sm text-gray-500">Drag and drop receipts here, or click to browse</p>
                        <p class="text-xs text-gray-400 mt-1">PNG, JPG, PDF up to 10MB</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <Link :href="route('employee.portal.expenses.index')"
                        class="px-4 py-2 text-gray-700 hover:text-gray-900">
                        Cancel
                    </Link>
                    <button type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                        {{ form.processing ? 'Creating...' : 'Create Expense' }}
                    </button>
                </div>
            </form>
        </div>
    </EmployeePortalLayout>
</template>
