<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { MagnifyingGlassIcon, PlusIcon, DocumentDuplicateIcon } from '@heroicons/vue/24/outline';
import CMSLayout from '@/Layouts/CMSLayout.vue';

defineOptions({
  layout: CMSLayout
})

interface Quotation {
    id: number;
    quotation_number: string;
    quotation_date: string;
    expiry_date: string | null;
    status: string;
    total_amount: number;
    customer: {
        id: number;
        name: string;
    };
    createdBy: {
        user: {
            name: string;
        };
    };
}

interface Props {
    quotations: {
        data: Quotation[];
        links: any[];
        meta: any;
    };
    summary: {
        total_quotations: number;
        draft_count: number;
        sent_count: number;
        accepted_count: number;
        total_value: number;
    };
    filters: {
        status: string;
        search: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search);
const selectedStatus = ref(props.filters.status);

const applyFilters = () => {
    router.get('/cms/quotations', {
        status: selectedStatus.value,
        search: search.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const formatCurrency = (amount: number) => {
    return `K${amount.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        draft: 'gray',
        sent: 'blue',
        accepted: 'green',
        rejected: 'red',
        expired: 'amber',
        converted: 'purple',
    };
    return colors[status] || 'gray';
};

const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        draft: 'Draft',
        sent: 'Sent',
        accepted: 'Accepted',
        rejected: 'Rejected',
        expired: 'Expired',
        converted: 'Converted',
    };
    return labels[status] || status;
};
</script>

<template>
    <Head title="Quotations - CMS" />

    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Quotations</h1>
                <p class="mt-1 text-sm text-gray-600">Create and manage customer quotations</p>
            </div>
            <Link
                :href="route('cms.quotations.create')"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
            >
                <PlusIcon class="h-5 w-5" aria-hidden="true" />
                Create Quotation
            </Link>
        </div>

        <!-- Summary Stats -->
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg bg-white p-4 shadow">
                <div class="text-sm font-medium text-gray-600">Total Quotations</div>
                <div class="mt-1 text-2xl font-bold text-gray-900">{{ summary.total_quotations }}</div>
            </div>
            <div class="rounded-lg bg-white p-4 shadow">
                <div class="text-sm font-medium text-gray-600">Total Value</div>
                <div class="mt-1 text-2xl font-bold text-gray-900">{{ formatCurrency(summary.total_value) }}</div>
            </div>
            <div class="rounded-lg bg-white p-4 shadow">
                <div class="text-sm font-medium text-gray-600">Sent</div>
                <div class="mt-1 text-2xl font-bold text-blue-600">{{ summary.sent_count }}</div>
            </div>
            <div class="rounded-lg bg-white p-4 shadow">
                <div class="text-sm font-medium text-gray-600">Accepted</div>
                <div class="mt-1 text-2xl font-bold text-green-600">{{ summary.accepted_count }}</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-6 rounded-lg bg-white p-4 shadow">
            <div class="flex flex-col gap-4 sm:flex-row">
                <div class="flex-1">
                    <div class="relative">
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" aria-hidden="true" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search quotations..."
                            class="w-full px-4 py-2.5 pl-10 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                </div>

                <div class="sm:w-48">
                    <select
                        v-model="selectedStatus"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        @change="applyFilters"
                    >
                        <option value="">All Statuses</option>
                        <option value="draft">Draft</option>
                        <option value="sent">Sent</option>
                        <option value="accepted">Accepted</option>
                        <option value="rejected">Rejected</option>
                        <option value="expired">Expired</option>
                        <option value="converted">Converted</option>
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

        <!-- Quotations Table -->
        <div class="overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Quotation #
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Customer
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Expiry
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Amount
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
                    <tr v-for="quotation in quotations.data" :key="quotation.id" class="hover:bg-gray-50">
                        <td class="whitespace-nowrap px-6 py-4">
                            <Link
                                :href="route('cms.quotations.show', quotation.id)"
                                class="font-medium text-blue-600 hover:text-blue-800"
                            >
                                {{ quotation.quotation_number }}
                            </Link>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                            {{ quotation.customer.name }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                            {{ formatDate(quotation.quotation_date) }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                            {{ quotation.expiry_date ? formatDate(quotation.expiry_date) : '-' }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                            {{ formatCurrency(quotation.total_amount) }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <span
                                :class="[
                                    'inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                                    getStatusColor(quotation.status) === 'gray' && 'bg-gray-100 text-gray-800',
                                    getStatusColor(quotation.status) === 'blue' && 'bg-blue-100 text-blue-800',
                                    getStatusColor(quotation.status) === 'green' && 'bg-green-100 text-green-800',
                                    getStatusColor(quotation.status) === 'red' && 'bg-red-100 text-red-800',
                                    getStatusColor(quotation.status) === 'amber' && 'bg-amber-100 text-amber-800',
                                    getStatusColor(quotation.status) === 'purple' && 'bg-purple-100 text-purple-800',
                                ]"
                            >
                                {{ getStatusLabel(quotation.status) }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                            <Link
                                :href="route('cms.quotations.show', quotation.id)"
                                class="text-blue-600 hover:text-blue-800"
                            >
                                View
                            </Link>
                        </td>
                    </tr>
                    <tr v-if="quotations.data.length === 0">
                        <td colspan="7" class="px-6 py-12 text-center">
                            <DocumentDuplicateIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                            <p class="mt-2 text-sm text-gray-600">No quotations found</p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="quotations.data.length > 0" class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing {{ quotations.meta.from }} to {{ quotations.meta.to }} of {{ quotations.meta.total }} results
                    </div>
                    <div class="flex gap-2">
                        <Link
                            v-for="link in quotations.links"
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
