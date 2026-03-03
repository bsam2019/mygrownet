<template>
    <CMSLayout>
        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ budget.name }}</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ formatDate(budget.start_date) }} - {{ formatDate(budget.end_date) }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('cms.budgets.edit', budget.id)"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    >
                        Edit Budget
                    </Link>
                    <Link
                        :href="route('cms.budgets.index')"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300"
                    >
                        Back to Budgets
                    </Link>
                </div>
            </div>

            <!-- Budget Info -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <p class="text-sm text-gray-600">Period Type</p>
                        <p class="text-lg font-medium text-gray-900 capitalize">{{ budget.period_type }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Status</p>
                        <span :class="getStatusClass(budget.status)">
                            {{ budget.status }}
                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Budget</p>
                        <p class="text-lg font-medium text-gray-900">{{ formatCurrency(budget.total_budget) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Items</p>
                        <p class="text-lg font-medium text-gray-900">{{ budget.items.length }}</p>
                    </div>
                </div>
                <div v-if="budget.notes" class="mt-4 pt-4 border-t border-gray-200">
                    <p class="text-sm text-gray-600">Notes</p>
                    <p class="text-gray-900">{{ budget.notes }}</p>
                </div>
            </div>

            <!-- Budget vs Actual Comparison -->
            <div v-if="comparison" class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Budget vs Actual</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Category
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Budgeted
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actual
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Variance
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    % Used
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="item in budget.items" :key="item.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ item.category }}</div>
                                    <div v-if="item.notes" class="text-xs text-gray-500">{{ item.notes }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="[
                                        'px-2 py-1 text-xs font-medium rounded-full',
                                        item.item_type === 'expense' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'
                                    ]">
                                        {{ item.item_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                                    {{ formatCurrency(item.budgeted_amount) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">
                                    {{ formatCurrency(getActual(item.category)) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium" :class="getVarianceColor(item, getActual(item.category))">
                                    {{ formatCurrency(Math.abs(getActual(item.category) - item.budgeted_amount)) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                    <span :class="getPercentageColor(getPercentage(item.budgeted_amount, getActual(item.category)))">
                                        {{ getPercentage(item.budgeted_amount, getActual(item.category)) }}%
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Budget Items (if no comparison) -->
            <div v-else class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Budget Items</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Category
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Budgeted Amount
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="item in budget.items" :key="item.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ item.category }}</div>
                                    <div v-if="item.notes" class="text-xs text-gray-500">{{ item.notes }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="[
                                        'px-2 py-1 text-xs font-medium rounded-full',
                                        item.item_type === 'expense' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'
                                    ]">
                                        {{ item.item_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                                    {{ formatCurrency(item.budgeted_amount) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </CMSLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import CMSLayout from '@/layouts/CMSLayout.vue';

interface BudgetItem {
    id: number;
    category: string;
    item_type: 'expense' | 'revenue';
    budgeted_amount: number;
    notes?: string;
}

interface Budget {
    id: number;
    name: string;
    period_type: string;
    start_date: string;
    end_date: string;
    total_budget: number;
    status: string;
    notes?: string;
    items: BudgetItem[];
}

interface Props {
    budget: Budget;
    comparison?: any;
}

const props = defineProps<Props>();

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 2,
    }).format(amount);
};

const formatDate = (date: string): string => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const getStatusClass = (status: string): string => {
    const baseClasses = 'inline-block px-2 py-1 text-xs font-medium rounded-full';
    switch (status) {
        case 'active':
            return `${baseClasses} bg-green-100 text-green-800`;
        case 'draft':
            return `${baseClasses} bg-gray-100 text-gray-800`;
        case 'completed':
            return `${baseClasses} bg-blue-100 text-blue-800`;
        case 'archived':
            return `${baseClasses} bg-amber-100 text-amber-800`;
        default:
            return `${baseClasses} bg-gray-100 text-gray-800`;
    }
};

const getActual = (category: string): number => {
    if (!props.comparison) return 0;
    // This would come from the comparison data
    return 0;
};

const getPercentage = (budgeted: number, actual: number): number => {
    if (budgeted === 0) return 0;
    return Math.round((actual / budgeted) * 100);
};

const getPercentageColor = (percentage: number): string => {
    if (percentage >= 100) return 'text-red-600 font-bold';
    if (percentage >= 90) return 'text-amber-600 font-semibold';
    return 'text-green-600';
};

const getVarianceColor = (item: BudgetItem, actual: number): string => {
    const variance = actual - item.budgeted_amount;
    if (item.item_type === 'expense') {
        return variance >= 0 ? 'text-red-600' : 'text-green-600';
    } else {
        return variance >= 0 ? 'text-green-600' : 'text-red-600';
    }
};
</script>
