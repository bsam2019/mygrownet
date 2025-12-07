<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import {
    PlusIcon,
    MagnifyingGlassIcon,
    PencilSquareIcon,
    TrashIcon,
    UserCircleIcon,
    PhoneIcon,
    EnvelopeIcon,
} from '@heroicons/vue/24/outline';

interface Customer {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    total_purchases: number;
    total_spent: number;
    last_purchase_at: string | null;
    tags: string[];
}

interface Props {
    customers: {
        data: Customer[];
        links: any;
        meta: any;
    };
    filters: {
        search?: string;
    };
}

const props = defineProps<Props>();
const search = ref(props.filters.search || '');

const applyFilters = () => {
    router.get('/bizboost/customers', {
        search: search.value || undefined,
    }, { preserveState: true });
};

const deleteCustomer = (id: number) => {
    if (confirm('Are you sure you want to delete this customer?')) {
        router.delete(`/bizboost/customers/${id}`);
    }
};
</script>

<template>
    <Head title="Customers - BizBoost" />
    <BizBoostLayout title="Customers">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Customers</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Manage your customer relationships</p>
                </div>
                <Link
                    href="/bizboost/customers/create"
                    class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                    Add Customer
                </Link>
            </div>

            <!-- Search -->
            <div class="relative max-w-md">
                <MagnifyingGlassIcon class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" aria-hidden="true" />
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search customers..."
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white pl-10 focus:border-violet-500 focus:ring-violet-500"
                    @keyup.enter="applyFilters"
                />
            </div>

            <!-- Customers List -->
            <div v-if="customers.data.length" class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Purchases</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total Spent</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="customer in customers.data" :key="customer.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-full bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center">
                                        <UserCircleIcon class="h-6 w-6 text-violet-600 dark:text-violet-400" aria-hidden="true" />
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ customer.name }}</p>
                                        <div v-if="customer.tags.length" class="flex gap-1 mt-1">
                                            <span
                                                v-for="tag in customer.tags.slice(0, 2)"
                                                :key="tag"
                                                class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded"
                                            >
                                                {{ tag }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    <div v-if="customer.email" class="flex items-center gap-1 text-sm text-gray-600 dark:text-gray-400">
                                        <EnvelopeIcon class="h-4 w-4" aria-hidden="true" />
                                        {{ customer.email }}
                                    </div>
                                    <div v-if="customer.phone" class="flex items-center gap-1 text-sm text-gray-600 dark:text-gray-400">
                                        <PhoneIcon class="h-4 w-4" aria-hidden="true" />
                                        {{ customer.phone }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ customer.total_purchases }}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                K{{ customer.total_spent.toLocaleString() }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <Link
                                        :href="`/bizboost/customers/${customer.id}/edit`"
                                        class="p-2 text-gray-400 hover:text-violet-600 dark:hover:text-violet-400"
                                        aria-label="Edit customer"
                                    >
                                        <PencilSquareIcon class="h-5 w-5" aria-hidden="true" />
                                    </Link>
                                    <button
                                        @click="deleteCustomer(customer.id)"
                                        class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400"
                                        aria-label="Delete customer"
                                    >
                                        <TrashIcon class="h-5 w-5" aria-hidden="true" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-else class="rounded-xl bg-white dark:bg-gray-800 p-12 text-center shadow-sm ring-1 ring-gray-200 dark:ring-gray-700">
                <UserCircleIcon class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" aria-hidden="true" />
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No customers yet</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Start building your customer base.</p>
                <Link
                    href="/bizboost/customers/create"
                    class="mt-4 inline-flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                    Add Customer
                </Link>
            </div>
        </div>
    </BizBoostLayout>
</template>
