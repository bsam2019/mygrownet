<template>
    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-900">My Investments</h2>
                <Link :href="route('investments.create')"
                      class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    New Investment
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expected Return</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="investment in investments.data" :key="investment.id">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ investment.id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(investment.amount) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="{
                                            'px-2 py-1 text-xs rounded-full': true,
                                            'bg-yellow-100 text-yellow-800': investment.status === 'pending',
                                            'bg-green-100 text-green-800': investment.status === 'active',
                                            'bg-red-100 text-red-800': investment.status === 'withdrawn'
                                        }">
                                            {{ investment.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(investment.expected_return) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ investment.created_at }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <Link :href="route('investments.show', investment.id)"
                                              class="text-blue-600 hover:text-blue-900 mr-3">View</Link>
                                        <button v-if="investment.status === 'pending'"
                                                @click="deleteInvestment(investment)"
                                                class="text-red-600 hover:text-red-900">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <Pagination :links="investments.links" class="mt-6" />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/InvestorLayout.vue';
import { Link } from '@inertiajs/vue3';
import Pagination from '@/components/Pagination.vue';
import { router } from '@inertiajs/vue3';
import { formatCurrency } from '@/utils/format';

const props = defineProps({
    investments: Object,
});

const deleteInvestment = (investment) => {
    if (confirm('Are you sure you want to delete this investment?')) {
        router.delete(route('investments.destroy', investment.id));
    }
};
</script>
