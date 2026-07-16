<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { useNotifications } from '@/composables/useNotifications';
import { useConfirmDialog } from '@/composables/useConfirmDialog';
import { useStockflowRoute } from '@/composables/useStockflowRoute';
import { ref } from 'vue';
import { PlusIcon, MagnifyingGlassIcon, TrashIcon, PencilIcon, PhoneIcon, EnvelopeIcon, MapPinIcon } from '@heroicons/vue/24/outline';
import { useCurrency } from '@/composables/useCurrency';

const { route } = useStockflowRoute();
const { success, error: notifyError } = useNotifications();
const confirm = useConfirmDialog();
const { formatCurrency } = useCurrency();

interface Customer {
    id: number;
    name: string;
    phone: string | null;
    email: string | null;
    address: string | null;
    city: string | null;
    country: string | null;
    credit_limit: number | null;
    payment_terms: string | null;
    notes: string | null;
}

interface Props { customers: Customer[]; }
const props = defineProps<Props>();

const search = ref('');

const deleteCustomer = async (customer: Customer) => {
    const ok = await confirm.show(`Delete "${customer.name}"? This cannot be undone.`, 'Delete Customer');
    if (ok) {
        router.delete(route('stockflow.sub.customers.destroy', customer.id), {
            onSuccess: () => success('Customer deleted'),
            onError: () => notifyError('Failed to delete customer'),
        });
    }
};
</script>

<template>
    <StockFlowLayout title="Customers">
        <Head title="Customers - StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">Customers</h1>
                    <div class="flex gap-3">
                        <Link :href="route('stockflow.sub.customers.create')" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                            <PlusIcon class="h-5 w-5" /> Add Customer
                        </Link>
                    </div>
                </div>

                <div class="mb-6 rounded-xl bg-white p-4 shadow-sm">
                    <div class="relative">
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                        <input v-model="search" @keydown.enter="router.get(route('stockflow.sub.customers.index'), { search: search }, { preserveState: true })" type="text" placeholder="Search by name, phone, or email..." class="w-full rounded-lg border-gray-300 pl-10 focus:border-emerald-500 focus:ring-emerald-500" />
                    </div>
                </div>

                <div v-if="customers.length" class="overflow-hidden rounded-xl bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Contact</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Location</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Credit Limit</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="customer in customers" :key="customer.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <Link :href="route('stockflow.sub.customers.show', customer.id)" class="font-medium text-emerald-600 hover:text-emerald-700">{{ customer.name }}</Link>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <div v-if="customer.phone">{{ customer.phone }}</div>
                                    <div v-if="customer.email" class="text-xs">{{ customer.email }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <span v-if="customer.city || customer.country">{{ [customer.city, customer.country].filter(Boolean).join(', ') }}</span>
                                    <span v-else>-</span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-gray-900">{{ customer.credit_limit ? formatCurrency(customer.credit_limit) : '-' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-1">
                                        <Link :href="route('stockflow.sub.customers.show', customer.id)" class="rounded p-1 text-gray-400 hover:bg-blue-100 hover:text-blue-600" title="View">
                                            <PencilIcon class="h-4 w-4" />
                                        </Link>
                                        <button @click="deleteCustomer(customer)" class="rounded p-1 text-gray-400 hover:bg-red-100 hover:text-red-600" title="Delete">
                                            <TrashIcon class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="text-center text-gray-500 py-12">No customers found. Add your first customer.</p>
            </div>
        </div>
    </StockFlowLayout>
</template>
