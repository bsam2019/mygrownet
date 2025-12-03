<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <!-- Header -->
            <div class="flex items-center gap-3 mb-6">
                <button 
                    @click="router.visit(route('growfinance.customers.index'))"
                    class="p-2 rounded-xl bg-white shadow-sm active:bg-gray-50"
                    aria-label="Go back"
                >
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                </button>
                <div class="flex-1">
                    <h1 class="text-xl font-bold text-gray-900">{{ customer.name }}</h1>
                    <p class="text-sm text-gray-500">Customer Details</p>
                </div>
                <button 
                    @click="router.visit(route('growfinance.customers.edit', customer.id))"
                    class="p-2 rounded-xl bg-blue-50 text-blue-600"
                    aria-label="Edit customer"
                >
                    <PencilIcon class="h-5 w-5" aria-hidden="true" />
                </button>
            </div>

            <!-- Customer Info -->
            <div class="bg-white rounded-2xl shadow-sm p-4 mb-4">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center">
                        <span class="text-xl font-bold text-blue-600">{{ getInitials(customer.name) }}</span>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">{{ customer.name }}</h2>
                        <span :class="['text-xs px-2 py-0.5 rounded-full', customer.is_active ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-500']">
                            {{ customer.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>

                <div class="space-y-3">
                    <div v-if="customer.phone" class="flex items-center gap-3">
                        <PhoneIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                        <a :href="`tel:${customer.phone}`" class="text-blue-600">{{ customer.phone }}</a>
                    </div>
                    <div v-if="customer.email" class="flex items-center gap-3">
                        <EnvelopeIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                        <a :href="`mailto:${customer.email}`" class="text-blue-600">{{ customer.email }}</a>
                    </div>
                    <div v-if="customer.address" class="flex items-start gap-3">
                        <MapPinIcon class="h-5 w-5 text-gray-400 mt-0.5" aria-hidden="true" />
                        <span class="text-gray-600">{{ customer.address }}</span>
                    </div>
                </div>
            </div>

            <!-- Financial Summary -->
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="bg-white rounded-2xl shadow-sm p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Outstanding</p>
                    <p :class="['text-xl font-bold', customer.outstanding_balance > 0 ? 'text-amber-600' : 'text-gray-900']">
                        {{ formatMoney(customer.outstanding_balance) }}
                    </p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Credit Limit</p>
                    <p class="text-xl font-bold text-gray-900">{{ formatMoney(customer.credit_limit) }}</p>
                </div>
            </div>

            <!-- Recent Invoices -->
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">Recent Invoices</h3>
                </div>
                <ul v-if="customer.invoices?.length > 0" class="divide-y divide-gray-100">
                    <li v-for="invoice in customer.invoices" :key="invoice.id">
                        <button 
                            @click="router.visit(route('growfinance.invoices.show', invoice.id))"
                            class="w-full p-4 text-left active:bg-gray-50"
                        >
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="font-medium text-gray-900">{{ invoice.invoice_number }}</p>
                                    <p class="text-xs text-gray-500">{{ invoice.invoice_date }}</p>
                                </div>
                                <p class="font-semibold text-gray-900">{{ formatMoney(invoice.total_amount) }}</p>
                            </div>
                        </button>
                    </li>
                </ul>
                <div v-else class="p-6 text-center text-gray-500 text-sm">No invoices yet</div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { ArrowLeftIcon, PencilIcon, PhoneIcon, EnvelopeIcon, MapPinIcon } from '@heroicons/vue/24/outline';

interface Props {
    customer: {
        id: number;
        name: string;
        email: string | null;
        phone: string | null;
        address: string | null;
        credit_limit: number;
        outstanding_balance: number;
        is_active: boolean;
        invoices: Array<{ id: number; invoice_number: string; invoice_date: string; total_amount: number }>;
    };
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
