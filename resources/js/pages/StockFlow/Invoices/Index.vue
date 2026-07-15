<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { useCurrency } from '@/composables/useCurrency';
import LoadingSkeleton from '@/components/StockFlow/LoadingSkeleton.vue';
import Pagination from '@/components/StockFlow/Pagination.vue';
import { PlusIcon } from '@heroicons/vue/24/outline';
import { ref } from 'vue';

interface InvoiceItem {
    id: number;
    item_name: string;
    quantity: number;
    unit_price: number;
    total: number;
}

interface Invoice {
    id: number;
    invoice_number: string;
    customer_name: string | null;
    invoice_date: string;
    due_date: string | null;
    status: string;
    total: number;
    amount_paid: number;
    balance_due: number;
    items: InvoiceItem[];
    created_at: string;
}

interface Props {
    invoices: {
        data: Invoice[];
        links: { url: string | null; label: string; active: boolean }[];
        meta: { current_page: number; last_page: number; total: number; from: number; to: number };
    };
    currentStatus: string | null;
}

const props = defineProps<Props>();

const { formatCurrency } = useCurrency();
const selectedStatus = ref(props.currentStatus || '');

const statusColors: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800',
    sent: 'bg-blue-100 text-blue-800',
    paid: 'bg-green-100 text-green-800',
    overdue: 'bg-red-100 text-red-800',
    cancelled: 'bg-amber-100 text-amber-800',
    partially_paid: 'bg-yellow-100 text-yellow-800',
};

const filterByStatus = () => {
    router.get(route('stockflow.sub.invoices.index'), { status: selectedStatus.value || undefined }, { preserveState: true });
};
</script>

<template>
    <StockFlowLayout title="Invoices">
        <Head title="Invoices - StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Invoices</h1>
                        <p class="mt-1 text-sm text-gray-500">Manage customer invoices and payments</p>
                    </div>
                    <div class="flex gap-3">
                        <select v-model="selectedStatus" @change="filterByStatus" class="rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">All Status</option>
                            <option value="draft">Draft</option>
                            <option value="sent">Sent</option>
                            <option value="paid">Paid</option>
                            <option value="overdue">Overdue</option>
                            <option value="partially_paid">Partially Paid</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <Link :href="route('stockflow.sub.invoices.create')" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                            <PlusIcon class="h-5 w-5" aria-hidden="true" />
                            New Invoice
                        </Link>
                    </div>
                </div>

                <LoadingSkeleton v-if="!invoices.data?.length" type="table" />
                <template v-else>
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Invoice #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Due</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Total</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Balance</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="inv in invoices.data" :key="inv.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <Link :href="route('stockflow.sub.invoices.show', inv.id)" class="font-medium text-emerald-600 hover:text-emerald-700">
                                            {{ inv.invoice_number }}
                                        </Link>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ inv.customer_name || '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ inv.invoice_date }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ inv.due_date || '-' }}</td>
                                    <td class="px-6 py-4">
                                        <span :class="[statusColors[inv.status] || 'bg-gray-100 text-gray-800', 'inline-flex rounded-full px-2 py-1 text-xs font-medium capitalize']">
                                            {{ inv.status.replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-semibold text-gray-900">{{ formatCurrency(inv.total) }}</td>
                                    <td class="px-6 py-4 text-right font-medium" :class="inv.balance_due > 0 ? 'text-red-600' : 'text-green-600'">
                                        {{ formatCurrency(inv.balance_due) }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <Link :href="route('stockflow.sub.invoices.show', inv.id)" class="text-sm text-emerald-600 hover:text-emerald-700">View</Link>
                                    </td>
                                </tr>
                                <tr v-if="!invoices.data?.length">
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">No invoices created yet</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination :links="invoices.links" :meta="invoices.meta" />
                </template>
            </div>
        </div>
    </StockFlowLayout>
</template>
