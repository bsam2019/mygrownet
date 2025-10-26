<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Purchase {
    id: number;
    invoice_number: string;
    user: User;
    amount: number;
    status: string;
    payment_method: string;
    payment_reference: string;
    purchased_at: string;
}

interface Pagination {
    data: Purchase[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{
    purchases: Pagination;
    filters: {
        status?: string;
        search?: string;
    };
}>();

const searchForm = useForm({
    search: props.filters.search || '',
    status: props.filters.status || '',
});

const selectedPurchase = ref<Purchase | null>(null);
const showStatusModal = ref(false);

const statusForm = useForm({
    status: '',
});

const search = () => {
    searchForm.get(route('admin.starter-kit.purchases'), {
        preserveState: true,
    });
};

const openStatusModal = (purchase: Purchase) => {
    selectedPurchase.value = purchase;
    statusForm.status = purchase.status;
    showStatusModal.value = true;
};

const updateStatus = () => {
    if (!selectedPurchase.value) return;
    
    statusForm.put(route('admin.starter-kit.purchases.update-status', selectedPurchase.value.id), {
        onSuccess: () => {
            showStatusModal.value = false;
            selectedPurchase.value = null;
        },
    });
};

const formatCurrency = (amount: number) => `K${amount.toLocaleString()}`;

const getStatusColor = (status: string) => {
    const colors = {
        completed: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        failed: 'bg-red-100 text-red-800',
        refunded: 'bg-gray-100 text-gray-800',
    };
    return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Starter Kit Purchases" />

    <AdminLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <Link :href="route('admin.starter-kit.dashboard')" class="text-blue-600 hover:text-blue-800 mb-2 inline-block">
                        ← Back to Dashboard
                    </Link>
                    <h1 class="text-3xl font-bold text-gray-900">Starter Kit Purchases</h1>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                            <div class="relative">
                                <input
                                    v-model="searchForm.search"
                                    type="text"
                                    placeholder="Search by name, email, or invoice..."
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    @keyup.enter="search"
                                />
                                <MagnifyingGlassIcon class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select
                                v-model="searchForm.status"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                @change="search"
                            >
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="completed">Completed</option>
                                <option value="failed">Failed</option>
                                <option value="refunded">Refunded</option>
                            </select>
                        </div>

                        <div class="flex items-end">
                            <button
                                @click="search"
                                class="w-full px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                            >
                                Apply Filters
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Purchases Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Member</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="purchase in purchases.data" :key="purchase.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ purchase.invoice_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ purchase.user.name }}</div>
                                        <div class="text-sm text-gray-500">{{ purchase.user.email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatCurrency(purchase.amount) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ purchase.payment_method }}</div>
                                        <div class="text-sm text-gray-500">{{ purchase.payment_reference }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getStatusColor(purchase.status)" class="px-2 py-1 text-xs font-medium rounded-full">
                                            {{ purchase.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ purchase.purchased_at }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <button
                                            @click="openStatusModal(purchase)"
                                            class="text-blue-600 hover:text-blue-800 font-medium"
                                        >
                                            Update Status
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing {{ (purchases.current_page - 1) * purchases.per_page + 1 }} to 
                            {{ Math.min(purchases.current_page * purchases.per_page, purchases.total) }} of 
                            {{ purchases.total }} results
                        </div>
                        <div class="flex gap-2">
                            <Link
                                v-if="purchases.current_page > 1"
                                :href="route('admin.starter-kit.purchases', { ...filters, page: purchases.current_page - 1 })"
                                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                            >
                                Previous
                            </Link>
                            <Link
                                v-if="purchases.current_page < purchases.last_page"
                                :href="route('admin.starter-kit.purchases', { ...filters, page: purchases.current_page + 1 })"
                                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                            >
                                Next
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Update Modal -->
        <div v-if="showStatusModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Update Purchase Status</h3>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select
                        v-model="statusForm.status"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="failed">Failed</option>
                        <option value="refunded">Refunded</option>
                    </select>
                </div>
                <div class="flex gap-3">
                    <button
                        @click="updateStatus"
                        :disabled="statusForm.processing"
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                    >
                        Update
                    </button>
                    <button
                        @click="showStatusModal = false"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
