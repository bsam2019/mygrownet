<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <!-- Header -->
            <div class="flex items-center gap-3 mb-6">
                <button 
                    @click="router.visit(route('growfinance.invoices.index'))"
                    class="p-2 rounded-xl bg-white shadow-sm active:bg-gray-50"
                    aria-label="Go back"
                >
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                </button>
                <div class="flex-1">
                    <h1 class="text-xl font-bold text-gray-900">{{ invoice.invoice_number }}</h1>
                    <span :class="['text-xs px-2 py-0.5 rounded-full', statusColors[invoice.status]]">
                        {{ statusLabels[invoice.status] }}
                    </span>
                </div>
            </div>

            <!-- Invoice Summary -->
            <div class="bg-white rounded-2xl shadow-sm p-4 mb-4">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-sm text-gray-500">Customer</p>
                        <p class="font-medium text-gray-900">{{ invoice.customer?.name || 'Walk-in Customer' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Date</p>
                        <p class="font-medium text-gray-900">{{ invoice.invoice_date }}</p>
                    </div>
                </div>
                
                <div v-if="invoice.due_date" class="flex justify-between items-center pt-3 border-t border-gray-100">
                    <span class="text-sm text-gray-500">Due Date</span>
                    <span :class="['font-medium', isOverdue ? 'text-red-600' : 'text-gray-900']">
                        {{ invoice.due_date }}
                    </span>
                </div>
            </div>

            <!-- Line Items -->
            <div class="bg-white rounded-2xl shadow-sm p-4 mb-4">
                <h3 class="font-semibold text-gray-900 mb-3">Items</h3>
                <div class="space-y-3">
                    <div v-for="item in invoice.items" :key="item.id" class="flex justify-between">
                        <div>
                            <p class="text-gray-900">{{ item.description }}</p>
                            <p class="text-xs text-gray-500">{{ item.quantity }} × {{ formatMoney(item.unit_price) }}</p>
                        </div>
                        <p class="font-medium text-gray-900">{{ formatMoney(item.line_total) }}</p>
                    </div>
                </div>
                
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <span class="font-semibold text-gray-900">Total</span>
                        <span class="text-xl font-bold text-gray-900">{{ formatMoney(invoice.total_amount) }}</span>
                    </div>
                    <div v-if="invoice.amount_paid > 0" class="flex justify-between items-center mt-2">
                        <span class="text-sm text-gray-500">Paid</span>
                        <span class="text-emerald-600 font-medium">-{{ formatMoney(invoice.amount_paid) }}</span>
                    </div>
                    <div v-if="balanceDue > 0" class="flex justify-between items-center mt-2 pt-2 border-t border-gray-100">
                        <span class="font-semibold text-gray-900">Balance Due</span>
                        <span class="text-xl font-bold text-red-600">{{ formatMoney(balanceDue) }}</span>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div v-if="invoice.notes" class="bg-white rounded-2xl shadow-sm p-4 mb-4">
                <h3 class="font-semibold text-gray-900 mb-2">Notes</h3>
                <p class="text-gray-600 text-sm">{{ invoice.notes }}</p>
            </div>

            <!-- Actions -->
            <div v-if="invoice.status !== 'paid' && invoice.status !== 'cancelled'" class="space-y-3">
                <button
                    @click="showPaymentModal = true"
                    class="w-full py-4 rounded-2xl bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-semibold shadow-lg shadow-emerald-500/30 active:scale-[0.98] transition-transform"
                >
                    Record Payment
                </button>
            </div>
        </div>

        <!-- Payment Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showPaymentModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm" @click="showPaymentModal = false" />
            </Transition>

            <Transition
                enter-active-class="transition-transform duration-300 ease-out"
                enter-from-class="translate-y-full"
                enter-to-class="translate-y-0"
                leave-active-class="transition-transform duration-200 ease-in"
                leave-from-class="translate-y-0"
                leave-to-class="translate-y-full"
            >
                <div v-if="showPaymentModal" class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md z-50 bg-white rounded-t-3xl shadow-2xl p-6">
                    <div class="flex justify-center mb-4">
                        <div class="w-10 h-1 bg-gray-300 rounded-full"></div>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Record Payment</h3>
                    <form @submit.prevent="submitPayment" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">K</span>
                                <input
                                    v-model="paymentForm.amount"
                                    type="number"
                                    step="0.01"
                                    :max="balanceDue"
                                    class="w-full pl-10 pr-4 py-3 rounded-xl border-gray-200"
                                    required
                                />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                            <select v-model="paymentForm.payment_method" class="w-full px-4 py-3 rounded-xl border-gray-200">
                                <option value="cash">Cash</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="w-full py-4 rounded-2xl bg-emerald-500 text-white font-semibold active:scale-[0.98] disabled:opacity-50"
                        >
                            {{ isSubmitting ? 'Processing...' : 'Confirm Payment' }}
                        </button>
                    </form>
                </div>
            </Transition>
        </Teleport>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

interface Props {
    invoice: {
        id: number;
        invoice_number: string;
        invoice_date: string;
        due_date: string | null;
        status: string;
        total_amount: number;
        amount_paid: number;
        notes: string | null;
        customer: { id: number; name: string } | null;
        items: Array<{ id: number; description: string; quantity: number; unit_price: number; line_total: number }>;
    };
}

const props = defineProps<Props>();

const showPaymentModal = ref(false);
const isSubmitting = ref(false);
const paymentForm = reactive({
    amount: props.invoice.total_amount - props.invoice.amount_paid,
    payment_method: 'cash',
});

const balanceDue = computed(() => props.invoice.total_amount - props.invoice.amount_paid);
const isOverdue = computed(() => {
    if (!props.invoice.due_date) return false;
    return new Date(props.invoice.due_date) < new Date() && props.invoice.status !== 'paid';
});

const statusLabels: Record<string, string> = {
    draft: 'Draft', sent: 'Sent', paid: 'Paid', partial: 'Partial', overdue: 'Overdue', cancelled: 'Cancelled',
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

const submitPayment = () => {
    isSubmitting.value = true;
    router.post(route('growfinance.invoices.payment', props.invoice.id), paymentForm, {
        onSuccess: () => { showPaymentModal.value = false; },
        onFinish: () => { isSubmitting.value = false; },
    });
};
</script>
