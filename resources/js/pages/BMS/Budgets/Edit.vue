<template>
    <CMSLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Budget</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Update budget details and items
                    </p>
                </div>
                <Link
                    :href="route('cms.budgets.index')"
                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300"
                >
                    Back to Budgets
                </Link>
            </div>

            <!-- Edit Form -->
            <div class="bg-white rounded-lg shadow p-6">
                <form @submit.prevent="submit">
                    <!-- Budget Details -->
                    <div class="space-y-4 mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Budget Details</h3>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Budget Name</label>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Period Type</label>
                                <select
                                    v-model="form.period_type"
                                    required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option value="monthly">Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select
                                    v-model="form.status"
                                    required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option value="draft">Draft</option>
                                    <option value="active">Active</option>
                                    <option value="completed">Completed</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                                <input
                                    v-model="form.start_date"
                                    type="date"
                                    required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                                <input
                                    v-model="form.end_date"
                                    type="date"
                                    required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea
                                v-model="form.notes"
                                rows="3"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            ></textarea>
                        </div>
                    </div>

                    <!-- Budget Items -->
                    <div class="space-y-4 mb-6 border-t border-gray-200 pt-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Budget Items</h3>
                            <button
                                type="button"
                                @click="addItem"
                                class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 shadow-sm"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Add Budget Item
                            </button>
                        </div>

                        <div v-for="(item, index) in form.items" :key="index" class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                                    <select
                                        v-model="item.item_type"
                                        @change="onTypeChange(item)"
                                        required
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        <option value="expense">Expense</option>
                                        <option value="revenue">Revenue</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                    <select
                                        v-model="item.category"
                                        required
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        <option value="">Select category...</option>
                                        <optgroup v-if="item.item_type === 'expense'" label="Platform Operational Expenses">
                                            <option value="Marketing">Marketing</option>
                                            <option value="Office Expenses">Office Expenses</option>
                                            <option value="Travel">Travel</option>
                                            <option value="Infrastructure">Infrastructure</option>
                                            <option value="Legal & Compliance">Legal & Compliance</option>
                                            <option value="Professional Services">Professional Services</option>
                                            <option value="Utilities">Utilities</option>
                                            <option value="General Expenses">General Expenses</option>
                                        </optgroup>
                                        <optgroup v-if="item.item_type === 'expense'" label="Member Payouts">
                                            <option value="Commissions">Commissions</option>
                                            <option value="Profit Sharing">Profit Sharing</option>
                                            <option value="LGR Awards">LGR Awards</option>
                                            <option value="Withdrawals">Withdrawals</option>
                                            <option value="Loan Disbursements">Loan Disbursements</option>
                                            <option value="Shop Credits">Shop Credits</option>
                                        </optgroup>
                                        <optgroup v-if="item.item_type === 'revenue'" label="Member Deposits">
                                            <option value="Wallet Top-ups">Wallet Top-ups</option>
                                        </optgroup>
                                        <optgroup v-if="item.item_type === 'revenue'" label="Product Sales">
                                            <option value="Starter Kits">Starter Kits</option>
                                            <option value="Starter Kit Upgrades">Starter Kit Upgrades</option>
                                            <option value="Starter Kit Gifts">Starter Kit Gifts</option>
                                            <option value="Shop Sales">Shop Sales</option>
                                            <option value="Marketplace Sales">Marketplace Sales</option>
                                            <option value="Learning Packs">Learning Packs</option>
                                        </optgroup>
                                        <optgroup v-if="item.item_type === 'revenue'" label="Service Payments">
                                            <option value="Subscriptions">Subscriptions</option>
                                            <option value="Workshops">Workshops</option>
                                            <option value="Coaching">Coaching</option>
                                            <option value="Services">Services</option>
                                            <option value="GrowBuilder">GrowBuilder</option>
                                        </optgroup>
                                        <optgroup v-if="item.item_type === 'revenue'" label="Other Revenue">
                                            <option value="Loan Repayments">Loan Repayments</option>
                                        </optgroup>
                                        <option value="__custom__">➕ Custom Category...</option>
                                    </select>
                                </div>

                                <!-- Custom Category Input (shown when Custom is selected) -->
                                <div v-if="item.category === '__custom__'">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Custom Category Name</label>
                                    <input
                                        v-model="item.custom_category"
                                        type="text"
                                        required
                                        placeholder="Enter custom category name..."
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        @blur="applyCustomCategory(item)"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount (K)</label>
                                    <input
                                        v-model.number="item.budgeted_amount"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        required
                                        placeholder="0.00"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                </div>

                                <div class="flex items-end">
                                    <button
                                        type="button"
                                        @click="removeItem(index)"
                                        class="w-full px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center justify-center gap-2"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Remove
                                    </button>
                                </div>
                            </div>

                            <div class="mt-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                                <input
                                    v-model="item.notes"
                                    type="text"
                                    placeholder="Add notes about this budget item..."
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>
                        </div>

                        <div v-if="form.items.length === 0" class="text-center py-8 text-gray-500">
                            No budget items yet. Click "Add Item" to get started.
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3">
                        <Link
                            :href="route('cms.budgets.index')"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Saving...' : 'Save Budget' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </CMSLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import CMSLayout from '@/layouts/CMSLayout.vue';

interface BudgetItem {
    category: string;
    item_type: 'expense' | 'revenue';
    budgeted_amount: number;
    notes?: string;
    custom_category?: string;
}

interface Budget {
    id: number;
    name: string;
    period_type: string;
    start_date: string;
    end_date: string;
    status: string;
    notes?: string;
    items: BudgetItem[];
}

interface Props {
    budget: Budget;
}

const props = defineProps<Props>();

const form = useForm({
    name: props.budget.name,
    period_type: props.budget.period_type,
    start_date: props.budget.start_date,
    end_date: props.budget.end_date,
    status: props.budget.status,
    notes: props.budget.notes || '',
    items: props.budget.items.map(item => ({
        category: item.category,
        item_type: item.item_type,
        budgeted_amount: item.budgeted_amount,
        notes: item.notes || '',
    })),
});

const addItem = () => {
    form.items.push({
        category: '',
        item_type: 'expense',
        budgeted_amount: 0,
        notes: '',
        custom_category: '',
    });
};

const removeItem = (index: number) => {
    form.items.splice(index, 1);
};

const onTypeChange = (item: any) => {
    // Clear category when type changes to force user to select appropriate category
    item.category = '';
    item.custom_category = '';
};

const applyCustomCategory = (item: any) => {
    // When user finishes typing custom category, apply it
    if (item.custom_category && item.custom_category.trim()) {
        item.category = item.custom_category.trim();
    }
};

const submit = () => {
    // Before submitting, ensure custom categories are applied
    form.items.forEach(item => {
        if (item.category === '__custom__' && item.custom_category) {
            item.category = item.custom_category.trim();
        }
    });
    
    form.put(route('cms.budgets.update', props.budget.id));
};
</script>
