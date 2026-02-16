<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { MagnifyingGlassIcon, PlusIcon, BanknotesIcon } from '@heroicons/vue/24/outline';
import CMSLayout from '@/Layouts/CMSLayout.vue';

defineOptions({
  layout: CMSLayout
})

interface Payment {
    id: number;
    amount: number;
    payment_method: string;
    reference_number: string | null;
    payment_date: string;
    unallocated_amount: number;
    customer: {
        id: number;
        name: string;
    };
    allocations: Array<{
        amount: number;
        invoice: {
            invoice_number: string;
        };
    }>;
}

interface Props {
    payments: {
        data: Payment[];
        links: any[];
        meta: any;
    };
    customers: Array<{ id: number; name: string }>;
    filters: {
        customer_id: number | null;
        search: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search);
const selectedCustomer = ref(props.filters.customer_id);

const applyFilters = () => {
    router.get('/cms/payments', {
        customer_id: selectedCustomer.value,
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

const getPaymentMethodLabel = (method: string) => {
    const labels: Record<string, string> = {
        cash: 'Cash',
        bank_transfer: 'Bank Transfer',
        mobile_money: 'Mobile Money',
        cheque: 'Cheque',
        card: 'Card',
    };
    return labels[method] || method;
};
</script>

<template>
    <Head title="Payments - CMS" />

    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Payments</h1>
                <p class="mt-1 text-sm text-gray-600">Record and manage customer payments</p>
            </div>
            <Link
                :href="route('cms.payments.create')"
                class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700"
            >
                <PlusIcon class="h-5 w-5" aria-hidden="true" />
                Record Payment
            </Link>
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
                            placeholder="Search payments..."
                            class="w-full px-4 py-2.5 pl-10 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                </div>

                <div class="sm:w-48">
                    <select
                        v-model="selectedCustomer"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        @change="applyFilters"
                    >
                        <option :value="null">All Customers</option>
                        <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                            {{ customer.name }}
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

        <!-- Payments Table -->
        <div class="overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Customer
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Method
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Reference
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Amount
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Allocated
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <tr v-for="payment in payments.data" :key="payment.id" class="hover:bg-gray-50">
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                            {{ formatDate(payment.payment_date) }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                            {{ payment.customer.name }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                            {{ getPaymentMethodLabel(payment.payment_method) }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                            {{ payment.reference_number || '-' }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                            {{ formatCurrency(payment.amount) }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                            {{ formatCurrency(payment.amount - payment.unallocated_amount) }}
                            <span v-if="payment.unallocated_amount > 0" class="ml-1 text-amber-600">
                                ({{ formatCurrency(payment.unallocated_amount) }} unallocated)
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                            <Link
                                :href="route('cms.payments.show', payment.id)"
                                class="text-blue-600 hover:text-blue-800"
                            >
                                View
                            </Link>
                        </td>
                    </tr>
                    <tr v-if="payments.data.length === 0">
                        <td colspan="7" class="px-6 py-12 text-center">
                            <BanknotesIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                            <p class="mt-2 text-sm text-gray-600">No payments found</p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="payments.data.length > 0" class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing {{ payments.meta.from }} to {{ payments.meta.to }} of {{ payments.meta.total }} results
                    </div>
                    <div class="flex gap-2">
                        <Link
                            v-for="link in payments.links"
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
