<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/layouts/EmployeePortalLayout.vue';
import {
    DocumentTextIcon,
    MagnifyingGlassIcon,
    CurrencyDollarIcon,
    CalendarIcon,
} from '@heroicons/vue/24/outline';
import debounce from 'lodash/debounce';

interface Receipt {
    id: number;
    receipt_number: string;
    amount: number;
    type: string;
    status: string;
    created_at: string;
    user?: { id: number; name: string; email: string };
}

interface Props {
    employee: { id: number; full_name: string };
    receipts: {
        data: Receipt[];
        links: any[];
        current_page: number;
        last_page: number;
        total: number;
    };
    filters: {
        search?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');

const applyFilters = debounce(() => {
    router.get(route('employee.portal.delegated.receipts.index'), {
        search: search.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300);

watch(search, () => {
    applyFilters();
});

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 2,
    }).format(amount);
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        completed: 'bg-green-100 text-green-800',
        pending: 'bg-amber-100 text-amber-800',
        failed: 'bg-red-100 text-red-800',
        cancelled: 'bg-gray-100 text-gray-800',
    };
    return colors[status] || colors.pending;
};

const getTypeColor = (type: string) => {
    const colors: Record<string, string> = {
        subscription: 'bg-blue-100 text-blue-800',
        payment: 'bg-green-100 text-green-800',
        withdrawal: 'bg-purple-100 text-purple-800',
        refund: 'bg-amber-100 text-amber-800',
    };
    return colors[type] || colors.payment;
};
</script>

<template>
    <EmployeePortalLayout>
        <Head title="Receipts - Delegated" />

        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <DocumentTextIcon class="h-6 w-6 text-purple-600" aria-hidden="true" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Receipts</h1>
                        <p class="text-sm text-gray-500">View payment receipts and transaction records</p>
                    </div>
                </div>
                <span class="px-3 py-1 text-xs font-medium bg-purple-100 text-purple-700 rounded-full">
                    Delegated Access
                </span>
            </div>

            <!-- Search -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="relative">
                    <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search by receipt number, user name, or email..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                    />
                </div>
            </div>

            <!-- Receipts Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Receipt
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    User
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Amount
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="receipt in receipts.data" :key="receipt.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <DocumentTextIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                                        <span class="font-mono text-sm text-gray-900">{{ receipt.receipt_number }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div v-if="receipt.user">
                                        <div class="text-sm font-medium text-gray-900">{{ receipt.user.name }}</div>
                                        <div class="text-sm text-gray-500">{{ receipt.user.email }}</div>
                                    </div>
                                    <span v-else class="text-sm text-gray-400">Unknown</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="[
                                        'px-2.5 py-0.5 rounded-full text-xs font-medium capitalize',
                                        getTypeColor(receipt.type)
                                    ]">
                                        {{ receipt.type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-1 text-sm font-medium text-gray-900">
                                        <CurrencyDollarIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                                        {{ formatCurrency(receipt.amount) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="[
                                        'px-2.5 py-0.5 rounded-full text-xs font-medium capitalize',
                                        getStatusColor(receipt.status)
                                    ]">
                                        {{ receipt.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex items-center gap-1">
                                        <CalendarIcon class="h-4 w-4" aria-hidden="true" />
                                        {{ formatDate(receipt.created_at) }}
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="receipts.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <DocumentTextIcon class="h-12 w-12 mx-auto mb-3 text-gray-300" aria-hidden="true" />
                                    <p>No receipts found</p>
                                    <p class="text-sm">Try adjusting your search</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="receipts.last_page > 1" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                    <p class="text-sm text-gray-500">
                        Showing page {{ receipts.current_page }} of {{ receipts.last_page }} ({{ receipts.total }} total)
                    </p>
                    <div class="flex gap-2">
                        <Link
                            v-for="link in receipts.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-1 text-sm rounded-lg',
                                link.active ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                                !link.url && 'opacity-50 cursor-not-allowed'
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
