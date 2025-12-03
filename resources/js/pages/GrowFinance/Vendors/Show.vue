<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <!-- Header -->
            <div class="flex items-center gap-3 mb-6">
                <button 
                    @click="router.visit(route('growfinance.vendors.index'))"
                    class="p-2 rounded-xl bg-white shadow-sm active:bg-gray-50"
                    aria-label="Go back"
                >
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                </button>
                <div class="flex-1">
                    <h1 class="text-xl font-bold text-gray-900">{{ vendor.name }}</h1>
                    <p class="text-sm text-gray-500">Vendor Details</p>
                </div>
                <button 
                    @click="router.visit(route('growfinance.vendors.edit', vendor.id))"
                    class="p-2 rounded-xl bg-purple-50 text-purple-600"
                    aria-label="Edit vendor"
                >
                    <PencilIcon class="h-5 w-5" aria-hidden="true" />
                </button>
            </div>

            <!-- Vendor Info -->
            <div class="bg-white rounded-2xl shadow-sm p-4 mb-4">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-16 h-16 rounded-full bg-purple-100 flex items-center justify-center">
                        <BuildingStorefrontIcon class="h-8 w-8 text-purple-600" aria-hidden="true" />
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">{{ vendor.name }}</h2>
                        <span :class="['text-xs px-2 py-0.5 rounded-full', vendor.is_active ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-500']">
                            {{ vendor.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>

                <div class="space-y-3">
                    <div v-if="vendor.phone" class="flex items-center gap-3">
                        <PhoneIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                        <a :href="`tel:${vendor.phone}`" class="text-purple-600">{{ vendor.phone }}</a>
                    </div>
                    <div v-if="vendor.email" class="flex items-center gap-3">
                        <EnvelopeIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                        <a :href="`mailto:${vendor.email}`" class="text-purple-600">{{ vendor.email }}</a>
                    </div>
                    <div v-if="vendor.address" class="flex items-start gap-3">
                        <MapPinIcon class="h-5 w-5 text-gray-400 mt-0.5" aria-hidden="true" />
                        <span class="text-gray-600">{{ vendor.address }}</span>
                    </div>
                    <div v-if="vendor.payment_terms" class="flex items-center gap-3">
                        <ClockIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                        <span class="text-gray-600">{{ vendor.payment_terms }}</span>
                    </div>
                </div>
            </div>

            <!-- Outstanding Balance -->
            <div class="bg-white rounded-2xl shadow-sm p-4 mb-4">
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">You Owe</p>
                <p :class="['text-2xl font-bold', vendor.outstanding_balance > 0 ? 'text-red-600' : 'text-gray-900']">
                    {{ formatMoney(vendor.outstanding_balance) }}
                </p>
            </div>

            <!-- Recent Expenses -->
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">Recent Expenses</h3>
                </div>
                <ul v-if="vendor.expenses?.length > 0" class="divide-y divide-gray-100">
                    <li v-for="expense in vendor.expenses" :key="expense.id">
                        <button 
                            @click="router.visit(route('growfinance.expenses.edit', expense.id))"
                            class="w-full p-4 text-left active:bg-gray-50"
                        >
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="font-medium text-gray-900">{{ expense.description || expense.category }}</p>
                                    <p class="text-xs text-gray-500">{{ expense.expense_date }}</p>
                                </div>
                                <p class="font-semibold text-red-600">-{{ formatMoney(expense.amount) }}</p>
                            </div>
                        </button>
                    </li>
                </ul>
                <div v-else class="p-6 text-center text-gray-500 text-sm">No expenses yet</div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { ArrowLeftIcon, PencilIcon, PhoneIcon, EnvelopeIcon, MapPinIcon, ClockIcon, BuildingStorefrontIcon } from '@heroicons/vue/24/outline';

interface Props {
    vendor: {
        id: number;
        name: string;
        email: string | null;
        phone: string | null;
        address: string | null;
        payment_terms: string | null;
        outstanding_balance: number;
        is_active: boolean;
        expenses: Array<{ id: number; description: string; category: string; expense_date: string; amount: number }>;
    };
}

defineProps<Props>();

const formatMoney = (amount: number) => {
    return 'K' + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};
</script>
