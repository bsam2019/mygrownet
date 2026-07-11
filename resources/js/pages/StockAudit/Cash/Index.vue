<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import StockAuditLayout from '@/layouts/StockAuditLayout.vue';
import { ref } from 'vue';
import { PlusIcon } from '@heroicons/vue/24/outline';

interface CashRegister {
    id: number;
    register_date: string;
    status: string;
    opening_balance: number;
    total_sales: number;
    total_expenses: number;
    total_banking: number;
    expected_closing: number;
    actual_closing: number | null;
    variance: number | null;
    opened_by: string;
    closed_by: string | null;
    created_at: string;
}

interface Props {
    registers: CashRegister[];
}

defineProps<Props>();

const showOpenForm = ref(false);
const openForm = ref({ register_date: new Date().toISOString().slice(0, 10), opening_balance: 0 });
const errors = ref<Record<string, string>>({});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', { style: 'currency', currency: 'ZMW', minimumFractionDigits: 2 }).format(amount);
};

const statusColors: Record<string, string> = {
    open: 'bg-green-100 text-green-800',
    closed: 'bg-gray-100 text-gray-800',
    verified: 'bg-blue-100 text-blue-800',
};

const openRegister = () => {
    router.post(route('stock-audit.cash.open'), openForm.value, {
        onSuccess: () => { showOpenForm.value = false; },
        onError: (err) => { errors.value = err; },
    });
};
</script>

<template>
    <StockAuditLayout title="Cash Register">
        <Head title="Cash Register - StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">Cash Register</h1>
                    <div class="flex gap-3">
                        <Link :href="route('stock-audit.cash.summary')" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Summary Report
                        </Link>
                        <button @click="showOpenForm = !showOpenForm" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                            <PlusIcon class="h-5 w-5" aria-hidden="true" />
                            Open Register
                        </button>
                    </div>
                </div>

                <!-- Open Register Form -->
                <div v-if="showOpenForm" class="mb-6 rounded-xl bg-white p-6 shadow-sm border border-emerald-200">
                    <h2 class="text-lg font-semibold text-gray-900">Open New Register</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date *</label>
                            <input v-model="openForm.register_date" type="date" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                            <p v-if="errors.register_date" class="mt-1 text-sm text-red-600">{{ errors.register_date }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Opening Balance *</label>
                            <input v-model.number="openForm.opening_balance" type="number" step="0.01" min="0" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                            <p v-if="errors.opening_balance" class="mt-1 text-sm text-red-600">{{ errors.opening_balance }}</p>
                        </div>
                    </div>
                    <div class="mt-4 flex gap-3">
                        <button @click="openRegister" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Open Register</button>
                        <button @click="showOpenForm = false" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Opening</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Sales</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Expenses</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Banking</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Expected</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="register in registers" :key="register.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900">{{ register.register_date }}</td>
                                <td class="px-6 py-4">
                                    <span :class="[statusColors[register.status] || 'bg-gray-100 text-gray-800', 'inline-flex rounded-full px-2 py-1 text-xs font-medium capitalize']">
                                        {{ register.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-gray-900">{{ formatCurrency(register.opening_balance) }}</td>
                                <td class="px-6 py-4 text-right text-sm text-emerald-600 font-medium">{{ formatCurrency(register.total_sales) }}</td>
                                <td class="px-6 py-4 text-right text-sm text-red-600">{{ formatCurrency(register.total_expenses) }}</td>
                                <td class="px-6 py-4 text-right text-sm text-blue-600">{{ formatCurrency(register.total_banking) }}</td>
                                <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">{{ formatCurrency(register.expected_closing) }}</td>
                                <td class="px-6 py-4 text-right">
                                    <Link :href="route('stock-audit.cash.show', register.id)" class="text-sm text-emerald-600 hover:text-emerald-700">View</Link>
                                </td>
                            </tr>
                            <tr v-if="registers.length === 0">
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">No cash registers yet</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </StockAuditLayout>
</template>
