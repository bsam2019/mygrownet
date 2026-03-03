<template>
    <CMSLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Budgets</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Manage budgets and track spending against targets
                    </p>
                </div>
                <button
                    @click="openCreateModal"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                    Create Budget
                </button>
            </div>

            <!-- Budgets List -->
            <div v-if="budgets.data.length > 0" class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Budget Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Period
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Dates
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total Budget
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="budget in budgets.data" :key="budget.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ budget.name }}</div>
                                <div v-if="budget.notes" class="text-xs text-gray-500">{{ budget.notes }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                    {{ budget.period_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ formatDate(budget.start_date) }} - {{ formatDate(budget.end_date) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                                {{ formatCurrency(budget.total_budget) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span :class="getStatusClass(budget.status)">
                                    {{ budget.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <Link
                                        :href="route('cms.budgets.show', budget.id)"
                                        class="text-blue-600 hover:text-blue-900"
                                    >
                                        View
                                    </Link>
                                    <Link
                                        :href="route('cms.budgets.edit', budget.id)"
                                        class="text-indigo-600 hover:text-indigo-900"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="confirmDelete(budget)"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="budgets.links.length > 3" class="px-6 py-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing {{ budgets.from }} to {{ budgets.to }} of {{ budgets.total }} budgets
                        </div>
                        <div class="flex gap-2">
                            <Link
                                v-for="link in budgets.links"
                                :key="link.label"
                                :href="link.url"
                                :class="[
                                    'px-3 py-1 rounded',
                                    link.active
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white rounded-lg shadow p-12 text-center">
                <CalendarIcon class="h-16 w-16 text-gray-400 mx-auto mb-4" aria-hidden="true" />
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Budgets Yet</h3>
                <p class="text-gray-600 mb-6">Get started by creating your first budget</p>
                <button
                    @click="openCreateModal"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                    Create Budget
                </button>
            </div>
        </div>

        <!-- Create/Edit Modal (placeholder - implement as needed) -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4">
                <h2 class="text-xl font-bold mb-4">Create Budget</h2>
                <p class="text-gray-600 mb-4">Budget creation form coming soon...</p>
                <div class="flex justify-end gap-2">
                    <button
                        @click="showModal = false"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </CMSLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import CMSLayout from '@/layouts/CMSLayout.vue';
import { PlusIcon, CalendarIcon } from '@heroicons/vue/24/outline';

interface Budget {
    id: number;
    name: string;
    period_type: string;
    start_date: string;
    end_date: string;
    total_budget: number;
    status: string;
    notes?: string;
}

interface Props {
    budgets: {
        data: Budget[];
        links: any[];
        from: number;
        to: number;
        total: number;
    };
}

const props = defineProps<Props>();

const showModal = ref(false);

const openCreateModal = () => {
    showModal.value = true;
};

const confirmDelete = (budget: Budget) => {
    if (confirm(`Are you sure you want to delete "${budget.name}"?`)) {
        router.delete(route('cms.budgets.destroy', budget.id), {
            onSuccess: () => {
                // Success message handled by backend
            },
        });
    }
};

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
        month: 'short',
        day: 'numeric',
    });
};

const getStatusClass = (status: string): string => {
    const baseClasses = 'px-2 py-1 text-xs font-medium rounded-full';
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
</script>
