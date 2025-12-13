<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import POSLayout from '@/layouts/POSLayout.vue';
import { BanknotesIcon, EyeIcon, XCircleIcon } from '@heroicons/vue/24/outline';

interface SaleItem {
    id: number;
    product_name: string;
    quantity: number;
    unit_price: number;
    total: number;
}

interface Sale {
    id: number;
    sale_number: string;
    customer_name: string | null;
    payment_method: string;
    total_amount: number;
    status: string;
    created_at: string;
    items: SaleItem[];
}

interface Props {
    sales: {
        data: Sale[];
        links: any;
    };
    filters: Record<string, any>;
}

const props = withDefaults(defineProps<Props>(), {
    sales: () => ({ data: [], links: {} }),
    filters: () => ({}),
});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(amount);
};

const formatDateTime = (dateString: string) => {
    return new Date(dateString).toLocaleString('en-ZM', {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        completed: 'bg-green-100 text-green-700',
        voided: 'bg-red-100 text-red-700',
        pending: 'bg-amber-100 text-amber-700',
    };
    return colors[status] || 'bg-gray-100 text-gray-700';
};

const getPaymentMethodLabel = (method: string) => {
    const labels: Record<string, string> = {
        cash: 'Cash',
        mobile_money: 'Mobile Money',
        card: 'Card',
        credit: 'Credit',
        split: 'Split Payment',
    };
    return labels[method] || method;
};
</script>

<template>
    <POSLayout title="Sales">
        <Head title="POS Sales" />

        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Sales History</h1>
                        <p class="mt-1 text-sm text-gray-500">View and manage all sales transactions</p>
                    </div>
                    <Link
                        :href="route('pos.terminal')"
                        class="inline-flex items-center gap-2 rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700"
                    >
                        <BanknotesIcon class="h-5 w-5" aria-hidden="true" />
                        New Sale
                    </Link>
                </div>

                <!-- Sales List -->
                <div class="rounded-xl bg-white shadow-sm">
                    <div v-if="sales.data.length === 0" class="p-8 text-center">
                        <BanknotesIcon class="mx-auto h-12 w-12 text-gray-300" aria-hidden="true" />
                        <p class="mt-2 text-gray-500">No sales found</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                        Sale #
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                        Date/Time
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                        Customer
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                        Payment
                                    </th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">
                                        Amount
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase text-gray-500">
                                        Status
                                    </th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="sale in sales.data" :key="sale.id" class="hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-900">
                                        {{ sale.sale_number }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                                        {{ formatDateTime(sale.created_at) }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                                        {{ sale.customer_name || 'Walk-in' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                                        {{ getPaymentMethodLabel(sale.payment_method) }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-semibold text-gray-900">
                                        {{ formatCurrency(sale.total_amount) }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-center">
                                        <span
                                            :class="[
                                                'rounded-full px-2 py-1 text-xs font-medium capitalize',
                                                getStatusColor(sale.status),
                                            ]"
                                        >
                                            {{ sale.status }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button
                                                class="rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                                                title="View Details"
                                            >
                                                <EyeIcon class="h-5 w-5" aria-hidden="true" />
                                            </button>
                                            <button
                                                v-if="sale.status === 'completed'"
                                                class="rounded p-1 text-gray-400 hover:bg-red-100 hover:text-red-600"
                                                title="Void Sale"
                                            >
                                                <XCircleIcon class="h-5 w-5" aria-hidden="true" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </POSLayout>
</template>
