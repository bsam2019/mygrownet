<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Customers</h1>
                    <p class="text-gray-500 text-sm">Manage your customer list</p>
                </div>
                <button 
                    @click="router.visit(route('growfinance.customers.create'))"
                    class="p-3 bg-blue-500 text-white rounded-xl shadow-lg shadow-blue-500/30 active:scale-95 transition-transform"
                    aria-label="Add customer"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                </button>
            </div>

            <!-- Customers List -->
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <ul v-if="customers.data.length > 0" class="divide-y divide-gray-100">
                    <li v-for="customer in customers.data" :key="customer.id">
                        <button 
                            @click="router.visit(route('growfinance.customers.show', customer.id))"
                            class="w-full p-4 text-left active:bg-gray-50 transition-colors"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <span class="text-sm font-semibold text-blue-600">
                                            {{ getInitials(customer.name) }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ customer.name }}</p>
                                        <p class="text-xs text-gray-500">{{ customer.phone || customer.email || 'No contact' }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p v-if="customer.outstanding_balance > 0" class="font-semibold text-amber-600">
                                        {{ formatMoney(customer.outstanding_balance) }}
                                    </p>
                                    <p class="text-xs text-gray-400">{{ customer.invoices_count }} invoices</p>
                                </div>
                            </div>
                        </button>
                    </li>
                </ul>
                <div v-else class="p-8 text-center">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
                        <UsersIcon class="h-6 w-6 text-gray-400" aria-hidden="true" />
                    </div>
                    <p class="text-gray-500 text-sm">No customers yet</p>
                    <button 
                        @click="router.visit(route('growfinance.customers.create'))"
                        class="text-blue-600 text-sm font-medium mt-2"
                    >
                        Add your first customer
                    </button>
                </div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { PlusIcon, UsersIcon } from '@heroicons/vue/24/outline';

interface Customer {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    outstanding_balance: number;
    invoices_count: number;
}

interface Props {
    customers: { data: Customer[] };
}

defineProps<Props>();

const getInitials = (name: string) => {
    const parts = name.split(' ');
    return parts.length >= 2 ? (parts[0][0] + parts[1][0]).toUpperCase() : name.substring(0, 2).toUpperCase();
};

const formatMoney = (amount: number) => {
    return 'K' + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};
</script>
