<template>
    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-medium">My Investments</h3>
                <Link :href="route('investments.create')" 
                      class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    New Investment
                </Link>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="investment in investments" :key="investment.id">
                            <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(investment.amount) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ investment.category.name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="{
                                    'px-2 py-1 text-xs rounded-full': true,
                                    'bg-yellow-100 text-yellow-800': investment.status === 'pending',
                                    'bg-green-100 text-green-800': investment.status === 'active',
                                    'bg-red-100 text-red-800': investment.status === 'rejected'
                                }">
                                    {{ investment.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ investment.created_at }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <Link :href="route('investments.show', investment.id)" 
                                      class="text-blue-600 hover:text-blue-900">
                                    View Details
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="investments.length === 0">
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                No investments found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { formatCurrency } from '@/utils/format';

defineProps({
    investments: {
        type: Array,
        required: true
    }
});
</script>