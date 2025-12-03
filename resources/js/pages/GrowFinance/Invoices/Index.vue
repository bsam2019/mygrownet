<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Invoices</h1>
                    <p class="text-gray-500 text-sm">Manage customer invoices</p>
                </div>
                <button 
                    @click="router.visit(route('growfinance.invoices.create'))"
                    class="p-3 bg-emerald-500 text-white rounded-xl shadow-lg shadow-emerald-500/30 active:scale-95 transition-transform"
                    aria-label="Create invoice"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                </button>
            </div>

            <!-- Status Filter -->
            <div class="flex gap-2 overflow-x-auto pb-2 mb-4 -mx-4 px-4 scrollbar-hide">
                <button
                    @click="filterStatus(null)"
                    :class="[
                        'px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors',
                        !currentStatus ? 'bg-emerald-500 text-white' : 'bg-white text-gray-600'
                    ]"
                >
                    All
                </button>
                <button
                    v-for="status in statuses"
                    :key="status.value"
                    @click="filterStatus(status.value)"
                    :class="[
                        'px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors',
                        currentStatus === status.value ? 'bg-emerald-500 text-white' : 'bg-white text-gray-600'
                    ]"
                >
                    {{ status.label }}
                </button>
            </div>

            <!-- Invoices List -->
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <ul v-if="invoices.data.length > 0" class="divide-y divide-gray-100">
                    <li v-for="invoice in invoices.data" :key="invoice.id">
                        <button 
                            @click="router.visit(route('growfinance.invoices.show', invoice.id))"
                            class="w-full p-4 text-left active:bg-gray-50 transition-colors"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-900">{{ invoice.invoice_number }}</p>
                                    <p class="text-sm text-gray-500">{{ invoice.customer?.name || 'Walk-in' }}</p>
                                    <p class="text-xs text-gray-400">{{ invoice.invoice_date }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-900">{{ formatMoney(invoice.total_amount) }}</p>
                                    <span :class="['text-xs px-2 py-0.5 rounded-full', statusColors[invoice.status]]">
                                        {{ statusLabels[invoice.status] }}
                                    </span>
                                </div>
                            </div>
                        </button>
                    </li>
                </ul>
                <div v-else class="p-8 text-center">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
                        <DocumentTextIcon class="h-6 w-6 text-gray-400" aria-hidden="true" />
                    </div>
                    <p class="text-gray-500 text-sm">No invoices found</p>
                    <button 
                        @click="router.visit(route('growfinance.invoices.create'))"
                        class="text-emerald-600 text-sm font-medium mt-2"
                    >
                        Create your first invoice
                    </button>
                </div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { PlusIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';

interface Invoice {
    id: number;
    invoice_number: string;
    invoice_date: string;
    total_amount: number;
    status: string;
    customer: { id: number; name: string } | null;
}

interface Props {
    invoices: { data: Invoice[] };
    currentStatus: string | null;
}

defineProps<Props>();

const statuses = [
    { value: 'draft', label: 'Draft' },
    { value: 'sent', label: 'Sent' },
    { value: 'paid', label: 'Paid' },
    { value: 'partial', label: 'Partial' },
    { value: 'overdue', label: 'Overdue' },
];

const statusLabels: Record<string, string> = {
    draft: 'Draft', sent: 'Sent', paid: 'Paid', partial: 'Partial', overdue: 'Overdue', cancelled: 'Cancelled',
};

const statusColors: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-600',
    sent: 'bg-blue-100 text-blue-600',
    paid: 'bg-emerald-100 text-emerald-600',
    partial: 'bg-amber-100 text-amber-600',
    overdue: 'bg-red-100 text-red-600',
    cancelled: 'bg-gray-100 text-gray-400',
};

const formatMoney = (amount: number) => {
    return 'K' + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const filterStatus = (status: string | null) => {
    router.visit(route('growfinance.invoices.index', status ? { status } : {}));
};
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
