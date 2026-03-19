<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, watch } from 'vue';
import {
    PlusIcon,
    MagnifyingGlassIcon,
    FunnelIcon,
    DocumentTextIcon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

interface Invoice {
    id: number;
    invoice_number: string;
    client: {
        id: number;
        name: string;
        company_name: string | null;
    };
    invoice_date: string;
    due_date: string;
    total: number;
    total_paid: number;
    balance: number;
    payment_status: string;
    is_overdue: boolean;
    currency: string;
}

interface Client {
    id: number;
    client_name: string;
    company_name: string | null;
}

interface Props {
    invoices: {
        data: Invoice[];
        links: any;
        meta: any;
    };
    clients: Client[];
    filters: {
        client_id: number | null;
        payment_status: string;
        search: string | null;
    };
    stats: {
        total: number;
        unpaid: number;
        overdue: number;
        total_outstanding: number;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const clientId = ref(props.filters.client_id || null);
const paymentStatus = ref(props.filters.payment_status || 'all');
const showFilters = ref(false);

watch([search, clientId, paymentStatus], () => {
    router.get(route('growbuilder.invoices.index'), {
        search: search.value,
        client_id: clientId.value,
        payment_status: paymentStatus.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}, { debounce: 300 });

const getPaymentStatusClass = (status: string): string => {
    const classes = {
        draft: 'bg-gray-100 text-gray-800',
        sent: 'bg-blue-100 text-blue-800',
        paid: 'bg-green-100 text-green-800',
        partial: 'bg-amber-100 text-amber-800',
        overdue: 'bg-red-100 text-red-800',
    };
    return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <AppLayout>
        <Head title="Invoices - GrowBuilder" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Invoices</h1>
                            <p class="text-gray-600">Manage client invoices and payments</p>
                        </div>
                        <Link
                            :href="route('growbuilder.invoices.create')"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                        >
                            <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                            Create Invoice
                        </Link>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-white rounded-lg border border-gray-200 p-4">
                            <div class="text-sm font-medium text-gray-600">Total Invoices</div>
                            <div class="mt-2 text-2xl font-bold text-gray-900">{{ stats.total }}</div>
                        </div>
                        <div class="bg-white rounded-lg border border-gray-200 p-4">
                            <div class="text-sm font-medium text-gray-600">Unpaid</div>
                            <div class="mt-2 text-2xl font-bold text-amber-600">{{ stats.unpaid }}</div>
                        </div>
                        <div class="bg-white rounded-lg border border-gray-200 p-4">
                            <div class="text-sm font-medium text-gray-600">Overdue</div>
                            <div class="mt-2 text-2xl font-bold text-red-600">{{ stats.overdue }}</div>
                        </div>
                        <div class="bg-white rounded-lg border border-gray-200 p-4">
                            <div class="text-sm font-medium text-gray-600">Outstanding</div>
                            <div class="mt-2 text-2xl font-bold text-green-600">ZMW {{ stats.total_outstanding.toFixed(2) }}</div>
                        </div>
                    </div>

                    <!-- Search and Filters -->
                    <div class="bg-white rounded-lg border border-gray-200 p-4">
                        <div class="flex items-center gap-4">
                            <!-- Search -->
                            <div class="flex-1 relative">
                                <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Search by invoice number..."
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>

                            <!-- Filter Toggle -->
                            <button
                                @click="showFilters = !showFilters"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                            >
                                <FunnelIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                                Filters
                            </button>
                        </div>

                        <!-- Filter Options -->
                        <div v-if="showFilters" class="mt-4 pt-4 border-t border-gray-200 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="client_filter" class="block text-sm font-medium text-gray-700 mb-2">
                                    Client
                                </label>
                                <select
                                    id="client_filter"
                                    v-model="clientId"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option :value="null">All Clients</option>
                                    <option v-for="client in clients" :key="client.id" :value="client.id">
                                        {{ client.client_name }}{{ client.company_name ? ` (${client.company_name})` : '' }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label for="status_filter" class="block text-sm font-medium text-gray-700 mb-2">
                                    Payment Status
                                </label>
                                <select
                                    id="status_filter"
                                    v-model="paymentStatus"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="all">All Statuses</option>
                                    <option value="draft">Draft</option>
                                    <option value="sent">Sent</option>
                                    <option value="partial">Partially Paid</option>
                                    <option value="paid">Paid</option>
                                    <option value="overdue">Overdue</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoices Table -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div v-if="invoices.data.length > 0">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Invoice
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Client
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Due Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Amount
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Balance
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="invoice in invoices.data" :key="invoice.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <DocumentTextIcon class="h-5 w-5 text-gray-400 mr-2" aria-hidden="true" />
                                            <Link
                                                :href="route('growbuilder.invoices.show', invoice.id)"
                                                class="text-sm font-medium text-blue-600 hover:text-blue-700"
                                            >
                                                {{ invoice.invoice_number }}
                                            </Link>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ invoice.client.name }}</div>
                                        <div v-if="invoice.client.company_name" class="text-xs text-gray-500">
                                            {{ invoice.client.company_name }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ invoice.invoice_date }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ invoice.due_date }}</div>
                                        <div v-if="invoice.is_overdue" class="flex items-center gap-1 text-xs text-red-600 font-semibold">
                                            <ExclamationTriangleIcon class="h-3 w-3" aria-hidden="true" />
                                            Overdue
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        {{ invoice.currency }} {{ invoice.total.toFixed(2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold"
                                        :class="invoice.balance > 0 ? 'text-red-600' : 'text-green-600'">
                                        {{ invoice.currency }} {{ invoice.balance.toFixed(2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                              :class="getPaymentStatusClass(invoice.payment_status)">
                                            {{ invoice.payment_status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link
                                            :href="route('growbuilder.invoices.show', invoice.id)"
                                            class="text-blue-600 hover:text-blue-700"
                                        >
                                            View
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div v-if="invoices.links && invoices.meta" class="px-6 py-4 border-t border-gray-200">
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
                                            'px-3 py-2 text-sm rounded-lg',
                                            link.active
                                                ? 'bg-blue-600 text-white'
                                                : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50',
                                            !link.url && 'opacity-50 cursor-not-allowed'
                                        ]"
                                        v-html="link.label"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="p-12 text-center">
                        <DocumentTextIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No invoices found</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating a new invoice.</p>
                        <div class="mt-6">
                            <Link
                                :href="route('growbuilder.invoices.create')"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                            >
                                <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                                Create Invoice
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
