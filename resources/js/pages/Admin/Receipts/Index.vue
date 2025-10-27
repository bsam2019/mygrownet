<template>
    <AdminLayout>
        <div class="space-y-6">
            <!-- Header with Stats -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h1 class="text-2xl font-bold text-gray-900 mb-6">Receipt Management</h1>
                
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="text-sm text-blue-600 font-medium">Total Receipts</div>
                        <div class="text-2xl font-bold text-blue-900 mt-1">{{ stats.total_receipts }}</div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="text-sm text-green-600 font-medium">Total Amount</div>
                        <div class="text-2xl font-bold text-green-900 mt-1">K {{ formatAmount(stats.total_amount) }}</div>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-4">
                        <div class="text-sm text-purple-600 font-medium">Emailed</div>
                        <div class="text-2xl font-bold text-purple-900 mt-1">{{ stats.emailed_count }}</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="text-sm text-gray-600 font-medium">By Type</div>
                        <div class="text-xs text-gray-700 mt-1 space-y-1">
                            <div v-for="item in stats.by_type" :key="item.type">
                                {{ formatType(item.type) }}: {{ item.count }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input v-model="searchForm.search" 
                           @input="search"
                           type="text" 
                           placeholder="Search by receipt number..."
                           class="border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    
                    <select v-model="searchForm.type" 
                            @change="search"
                            class="border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Types</option>
                        <option value="payment">Registration</option>
                        <option value="starter_kit">Starter Kit</option>
                        <option value="wallet">Wallet</option>
                        <option value="purchase">Purchase</option>
                    </select>
                    
                    <button @click="clearFilters" 
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                        Clear Filters
                    </button>
                </div>
            </div>

            <!-- Receipts Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div v-if="receipts.data.length > 0" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Receipt #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Emailed</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="receipt in receipts.data" :key="receipt.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ receipt.receipt_number }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ receipt.user.name }}</div>
                                    <div class="text-xs text-gray-500">{{ receipt.user.email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getTypeBadgeClass(receipt.type)" 
                                          class="px-2 py-1 text-xs font-semibold rounded-full">
                                        {{ formatType(receipt.type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    K {{ formatAmount(receipt.amount) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatDate(receipt.created_at) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span v-if="receipt.emailed" class="text-green-600 text-sm">✓ Yes</span>
                                    <span v-else class="text-gray-400 text-sm">No</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                    <a :href="`/admin/receipts/${receipt.id}/download`" 
                                       class="text-blue-600 hover:text-blue-900">Download</a>
                                    <Link v-if="!receipt.emailed" 
                                          :href="`/admin/receipts/${receipt.id}/resend`" 
                                          method="post"
                                          as="button"
                                          class="text-green-600 hover:text-green-900">
                                        Resend
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <p class="text-gray-500">No receipts found</p>
                </div>

                <!-- Pagination -->
                <div v-if="receipts.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing {{ receipts.from }} to {{ receipts.to }} of {{ receipts.total }}
                        </div>
                        <div class="flex space-x-2">
                            <Link v-if="receipts.prev_page_url" 
                                  :href="receipts.prev_page_url"
                                  class="px-3 py-1 border rounded-md text-sm hover:bg-gray-50">
                                Previous
                            </Link>
                            <Link v-if="receipts.next_page_url" 
                                  :href="receipts.next_page_url"
                                  class="px-3 py-1 border rounded-md text-sm hover:bg-gray-50">
                                Next
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface Props {
    receipts: any;
    stats: any;
    filters: any;
}

const props = defineProps<Props>();

const searchForm = ref({
    search: props.filters.search || '',
    type: props.filters.type || '',
});

const search = () => {
    router.get('/admin/receipts', searchForm.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    searchForm.value = { search: '', type: '' };
    search();
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const formatAmount = (amount: number) => {
    return Number(amount).toFixed(2);
};

const formatType = (type: string) => {
    const types: Record<string, string> = {
        'payment': 'Registration',
        'starter_kit': 'Starter Kit',
        'wallet': 'Wallet',
        'purchase': 'Purchase'
    };
    return types[type] || type;
};

const getTypeBadgeClass = (type: string) => {
    const classes: Record<string, string> = {
        'payment': 'bg-blue-100 text-blue-800',
        'starter_kit': 'bg-green-100 text-green-800',
        'wallet': 'bg-purple-100 text-purple-800',
        'purchase': 'bg-yellow-100 text-yellow-800'
    };
    return classes[type] || 'bg-gray-100 text-gray-800';
};
</script>
