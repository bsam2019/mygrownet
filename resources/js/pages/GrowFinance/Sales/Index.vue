<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Sales</h1>
                    <p class="text-gray-500 text-sm">Record and track your income</p>
                </div>
            </div>

            <!-- Quick Add Sale Card -->
            <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-4 mb-6 shadow-lg">
                <h3 class="text-white font-semibold mb-4">Quick Sale</h3>
                <form @submit.prevent="submitQuickSale" class="space-y-3">
                    <input
                        v-model="quickSale.description"
                        type="text"
                        placeholder="What did you sell?"
                        class="w-full px-4 py-3 rounded-xl bg-white/20 text-white placeholder-white/60 border-0 focus:ring-2 focus:ring-white/50"
                        required
                    />
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-white/60">K</span>
                            <input
                                v-model="quickSale.amount"
                                type="number"
                                step="0.01"
                                min="0.01"
                                placeholder="0.00"
                                class="w-full pl-8 pr-4 py-3 rounded-xl bg-white/20 text-white placeholder-white/60 border-0 focus:ring-2 focus:ring-white/50"
                                required
                            />
                        </div>
                        <select
                            v-model="quickSale.payment_method"
                            class="px-4 py-3 rounded-xl bg-white/20 text-white border-0 focus:ring-2 focus:ring-white/50"
                        >
                            <option value="cash">Cash</option>
                            <option value="bank">Bank</option>
                            <option value="mobile_money">Mobile</option>
                        </select>
                    </div>
                    <select
                        v-model="quickSale.customer_id"
                        class="w-full px-4 py-3 rounded-xl bg-white/20 text-white border-0 focus:ring-2 focus:ring-white/50"
                    >
                        <option :value="null">Walk-in Customer</option>
                        <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="w-full py-3 rounded-xl bg-white text-emerald-600 font-semibold active:scale-[0.98] transition-transform disabled:opacity-50"
                    >
                        {{ isSubmitting ? 'Recording...' : 'Record Sale' }}
                    </button>
                </form>
            </div>

            <!-- Sales List -->
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">Recent Sales</h3>
                </div>
                <ul v-if="sales.data.length > 0" class="divide-y divide-gray-100">
                    <li v-for="sale in sales.data" :key="sale.id">
                        <button 
                            @click="router.visit(route('growfinance.invoices.show', sale.id))"
                            class="w-full p-4 text-left active:bg-gray-50 transition-colors"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                                        <BanknotesIcon class="h-5 w-5 text-emerald-600" aria-hidden="true" />
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ sale.customer?.name || 'Walk-in' }}</p>
                                        <p class="text-xs text-gray-500">{{ sale.invoice_number }} • {{ sale.invoice_date }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-emerald-600">{{ formatMoney(sale.total_amount) }}</p>
                                    <span :class="[
                                        'text-xs px-2 py-0.5 rounded-full',
                                        statusColors[sale.status]
                                    ]">
                                        {{ statusLabels[sale.status] }}
                                    </span>
                                </div>
                            </div>
                        </button>
                    </li>
                </ul>
                <div v-else class="p-8 text-center">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
                        <BanknotesIcon class="h-6 w-6 text-gray-400" aria-hidden="true" />
                    </div>
                    <p class="text-gray-500 text-sm">No sales recorded yet</p>
                    <p class="text-gray-400 text-xs mt-1">Use the form above to record your first sale</p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="sales.last_page > 1" class="flex justify-center gap-2 mt-4">
                <button
                    v-for="page in sales.last_page"
                    :key="page"
                    @click="router.visit(route('growfinance.sales.index', { page }))"
                    :class="[
                        'w-10 h-10 rounded-full font-medium transition-colors',
                        page === sales.current_page
                            ? 'bg-blue-600 text-white'
                            : 'bg-white text-gray-600 hover:bg-gray-100'
                    ]"
                >
                    {{ page }}
                </button>
            </div>
        </div>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { BanknotesIcon } from '@heroicons/vue/24/outline';

interface Sale {
    id: number;
    invoice_number: string;
    invoice_date: string;
    total_amount: number;
    status: string;
    customer: { id: number; name: string } | null;
}

interface Props {
    sales: {
        data: Sale[];
        current_page: number;
        last_page: number;
    };
    customers: Array<{ id: number; name: string }>;
}

defineProps<Props>();

const isSubmitting = ref(false);
const quickSale = reactive({
    description: '',
    amount: '',
    payment_method: 'cash',
    customer_id: null as number | null,
});

const statusLabels: Record<string, string> = {
    draft: 'Draft',
    sent: 'Sent',
    paid: 'Paid',
    partial: 'Partial',
    overdue: 'Overdue',
    cancelled: 'Cancelled',
};

const statusColors: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-600',
    sent: 'bg-blue-100 text-blue-600',
    paid: 'bg-emerald-100 text-emerald-600',
    partial: 'bg-amber-100 text-amber-600',
    overdue: 'bg-red-100 text-red-600',
    cancelled: 'bg-gray-100 text-gray-400',
};

const formatMoney = (amount: number) => {
    return 'K' + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const submitQuickSale = () => {
    isSubmitting.value = true;
    router.post(route('growfinance.sales.store'), quickSale, {
        onSuccess: () => {
            quickSale.description = '';
            quickSale.amount = '';
            quickSale.customer_id = null;
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};
</script>
