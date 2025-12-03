<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Vendors</h1>
                    <p class="text-gray-500 text-sm">Manage your suppliers</p>
                </div>
                <button 
                    @click="router.visit(route('growfinance.vendors.create'))"
                    class="p-3 bg-purple-500 text-white rounded-xl shadow-lg shadow-purple-500/30 active:scale-95 transition-transform"
                    aria-label="Add vendor"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                </button>
            </div>

            <!-- Vendors List -->
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <ul v-if="vendors.data.length > 0" class="divide-y divide-gray-100">
                    <li v-for="vendor in vendors.data" :key="vendor.id">
                        <button 
                            @click="router.visit(route('growfinance.vendors.show', vendor.id))"
                            class="w-full p-4 text-left active:bg-gray-50 transition-colors"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                                        <BuildingStorefrontIcon class="h-5 w-5 text-purple-600" aria-hidden="true" />
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ vendor.name }}</p>
                                        <p class="text-xs text-gray-500">{{ vendor.phone || vendor.email || 'No contact' }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p v-if="vendor.outstanding_balance > 0" class="font-semibold text-red-600">
                                        {{ formatMoney(vendor.outstanding_balance) }}
                                    </p>
                                    <p class="text-xs text-gray-400">{{ vendor.expenses_count }} expenses</p>
                                </div>
                            </div>
                        </button>
                    </li>
                </ul>
                <div v-else class="p-8 text-center">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
                        <BuildingStorefrontIcon class="h-6 w-6 text-gray-400" aria-hidden="true" />
                    </div>
                    <p class="text-gray-500 text-sm">No vendors yet</p>
                    <button 
                        @click="router.visit(route('growfinance.vendors.create'))"
                        class="text-purple-600 text-sm font-medium mt-2"
                    >
                        Add your first vendor
                    </button>
                </div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { PlusIcon, BuildingStorefrontIcon } from '@heroicons/vue/24/outline';

interface Vendor {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    outstanding_balance: number;
    expenses_count: number;
}

interface Props {
    vendors: { data: Vendor[] };
}

defineProps<Props>();

const formatMoney = (amount: number) => {
    return 'K' + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};
</script>
