<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, inject } from 'vue';
import { MagnifyingGlassIcon, PlusIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';
import CMSLayout from '@/Layouts/CMSLayout.vue';

defineOptions({
  layout: CMSLayout
})

interface Invoice {
    id: number;
    invoice_number: string;
    invoice_date: string;
    due_date: string;
    status: string;
    total_amount: number;
    amount_paid: number;
    customer: {
        id: number;
        name: string;
    };
}

interface Props {
    invoices: {
        data: Invoice[];
        links: any[];
        meta: any;
    };
    summary: {
        total_invoices: number;
        draft_count: number;
        sent_count: number;
        partial_count: number;
        paid_count: number;
        total_value: number;
        total_paid: number;
        total_outstanding: number;
    };
    filters: {
        status: string;
        search: string;
    };
    statuses: Array<{ value: string; label: string; color: string }>;
}

const props = defineProps<Props>();

// Get slideOver from layout
const slideOver: any = inject('slideOver')

const search = ref(props.filters.search);
const selectedStatus = ref(props.filters.status);

const applyFilters = () => {
    router.get('/cms/invoices', {
        status: selectedStatus.value,
        search: search.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const getStatusColor = (status: string) => {
    const statusObj = props.statuses.find(s => s.value === status);
    return statusObj?.color || 'gray';
};

const formatCurrency = (amount: number) => {
    return `K${amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

<template>
    <Head title="Invoices - CMS" />

    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Invoices</h1>
                <p class="mt-1 text-sm text-gray-600">Manage customer invoices and billing</p>
            </div>
            <button
                @click="slideOver?.open('invoice')"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
            >
                <PlusIcon class="h-5 w-5" aria-hidden="true" />
                Create Invoice
            </button>
        </div>

        <!-- Summary Stats -->
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg bg-white p-4 shadow">
                <div class="text-sm font-medium text-gray-600">Total Invoices</div>
                <div class="mt-1 text-2xl font-bold text-gray-900">{{ summary.total_invoices }}</div>
            </div>
            <div class="rounded-lg bg-white p-4 shadow">
                <div class="text-sm font-medium text-gray-600">Total Value</div>
                <div class="mt-1 text-2xl font-bold text-gray-900">{{ formatCurrency(summary.total_value) }}</div>
            </div>
            <div class="rounded-lg bg-white p-4 shadow">
                <div class="text-sm font-medium text-gray-600">Total Paid</div>
                <div class="mt-1 text-2xl font-bold text-green-600">{{ formatCurrency(summary.total_paid) }}</div>
            </div>
            <div class="rounded-lg bg-white p-4 shadow">
                <div class="text-sm font-medium text-gray-600">Outstanding</div>
                <div class="mt-1 text-2xl font-bold text-amber-600">{{ formatCurrency(summary.total_outstanding) }}</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-6 rounded-lg bg-white p-4 shadow">
            <div class="flex flex-col gap-4 sm:flex-row">
                <!-- Search -->
                <div class="flex-1">
                    <div class="relative">
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" aria-hidden="true" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search invoices..."
                            class="w-full px-4 py-2.5 pl-10 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="sm:w-48">
                    <select
                        v-model="selectedStatus"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        @change="applyFilters"
                    >
                        <option value="all">All Statuses</option>
                        <option v-for="status in statuses" :key="status.value" :value="status.value">
                            {{ status.label }}
                        </option>
                    </select>
                </div>

                <button
                    @click="applyFilters"
                    class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200"
                >
                    Apply Filters
                </button>
            </div>
        </div>

        <!-- Invoices Table -->
        <div class="overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Invoice #
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Customer
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Due Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Amount
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Paid
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Status
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <tr v-for="invoice in invoices.data" :key="invoice.id" class="hover:bg-gray-50">
                        <td class="whitespace-nowrap px-6 py-4">
                            <Link
                                :href="route('cms.invoices.show', invoice.id)"
                                class="font-medium text-blue-600 hover:text-blue-800"
                            >
                                {{ invoice.invoice_number }}
                            </Link>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                            {{ invoice.customer.name }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                            {{ formatDate(invoice.invoice_date) }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                            {{ formatDate(invoice.due_date) }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                            {{ formatCurrency(invoice.total_amount) }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                            {{ formatCurrency(invoice.amount_paid) }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <span
                                :class="[
                                    'inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                                    getStatusColor(invoice.status) === 'gray' && 'bg-gray-100 text-gray-800',
                                    getStatusColor(invoice.status) === 'blue' && 'bg-blue-100 text-blue-800',
                                    getStatusColor(invoice.status) === 'amber' && 'bg-amber-100 text-amber-800',
                                    getStatusColor(invoice.status) === 'green' && 'bg-green-100 text-green-800',
                                    getStatusColor(invoice.status) === 'red' && 'bg-red-100 text-red-800',
                                ]"
                            >
                                {{ statuses.find(s => s.value === invoice.status)?.label }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                            <Link
                                :href="route('cms.invoices.show', invoice.id)"
                                class="text-blue-600 hover:text-blue-800"
                            >
                                View
                            </Link>
                        </td>
                    </tr>
                    <tr v-if="invoices.data.length === 0">
                        <td colspan="8" class="px-6 py-12 text-center">
                            <DocumentTextIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                            <p class="mt-2 text-sm text-gray-600">No invoices found</p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="invoices.data.length > 0" class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing {{ invoices.meta.from }} to {{ invoices.meta.to }} of {{ invoices.meta.total }} results
                    </div>
                    <div class="flex gap-2">
                        <Link
                            v-for="link in invoices.links"
                            :key="link.label"
                            :href="link.url"
                            :class="[
                                'rounded px-3 py-1 text-sm',
                                link.active
                                    ? 'bg-blue-600 text-white'
                                    : link.url
                                    ? 'bg-white text-gray-700 hover:bg-gray-50'
                                    : 'bg-gray-100 text-gray-400 cursor-not-allowed',
                            ]"
                            :disabled="!link.url"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
