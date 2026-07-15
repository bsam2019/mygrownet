<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import StockAuditLayout from '@/layouts/StockAuditLayout.vue';
import { useCurrency } from '@/composables/useCurrency';
import LoadingSkeleton from '@/components/StockAudit/LoadingSkeleton.vue';
import Pagination from '@/components/StockAudit/Pagination.vue';
import { PlusIcon } from '@heroicons/vue/24/outline';
import { ref } from 'vue';

interface QuotationItem {
    id: number;
    item_name: string;
    quantity: number;
    unit_price: number;
    total: number;
}

interface Quotation {
    id: number;
    quotation_number: string;
    customer_name: string | null;
    quotation_date: string;
    status: string;
    total: number;
    items: QuotationItem[];
    created_at: string;
}

interface Props {
    quotations: {
        data: Quotation[];
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
    accepted: 'bg-green-100 text-green-800',
    declined: 'bg-red-100 text-red-800',
    expired: 'bg-amber-100 text-amber-800',
    converted: 'bg-emerald-100 text-emerald-800',
};

const filterByStatus = () => {
    router.get(route('stock-audit.quotations.index'), { status: selectedStatus.value || undefined }, { preserveState: true });
};
</script>

<template>
    <StockAuditLayout title="Quotations">
        <Head title="Quotations - StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Quotations</h1>
                        <p class="mt-1 text-sm text-gray-500">Manage customer quotes and estimates</p>
                    </div>
                    <div class="flex gap-3">
                        <select v-model="selectedStatus" @change="filterByStatus" class="rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">All Status</option>
                            <option value="draft">Draft</option>
                            <option value="sent">Sent</option>
                            <option value="accepted">Accepted</option>
                            <option value="declined">Declined</option>
                            <option value="expired">Expired</option>
                            <option value="converted">Converted</option>
                        </select>
                        <Link :href="route('stock-audit.quotations.create')" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                            <PlusIcon class="h-5 w-5" aria-hidden="true" />
                            New Quotation
                        </Link>
                    </div>
                </div>

                <LoadingSkeleton v-if="!quotations.data?.length" type="table" />
                <template v-else>
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Quotation #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Total</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="q in quotations.data" :key="q.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <Link :href="route('stock-audit.quotations.show', q.id)" class="font-medium text-emerald-600 hover:text-emerald-700">
                                            {{ q.quotation_number }}
                                        </Link>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ q.customer_name || '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ q.quotation_date }}</td>
                                    <td class="px-6 py-4">
                                        <span :class="[statusColors[q.status] || 'bg-gray-100 text-gray-800', 'inline-flex rounded-full px-2 py-1 text-xs font-medium capitalize']">
                                            {{ q.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-semibold text-gray-900">{{ formatCurrency(q.total) }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <Link :href="route('stock-audit.quotations.show', q.id)" class="text-sm text-emerald-600 hover:text-emerald-700">View</Link>
                                    </td>
                                </tr>
                                <tr v-if="!quotations.data?.length">
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">No quotations created yet</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination :links="quotations.links" :meta="quotations.meta" />
                </template>
            </div>
        </div>
    </StockAuditLayout>
</template>
