<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import StockAuditLayout from '@/layouts/StockAuditLayout.vue';
import { useCurrency } from '@/composables/useCurrency';
import { ref, computed } from 'vue';

interface InvoiceItem {
    id: number;
    item_name: string;
    quantity: number;
    unit_price: number;
    total: number;
}

interface Invoice {
    id: number;
    invoice_number: string;
    customer_name: string | null;
    customer_phone: string | null;
    customer_email: string | null;
    invoice_date: string;
    due_date: string | null;
    status: string;
    subtotal: number;
    discount: number;
    tax: number;
    total: number;
    amount_paid: number;
    balance_due: number;
    payment_terms: string | null;
    notes: string | null;
    created_by: number;
    sa_quotation_id: number | null;
    sa_sale_id: number | null;
    items: InvoiceItem[];
    created_at: string;
}

interface Props {
    invoice: Invoice;
}

const props = defineProps<Props>();

const { formatCurrency } = useCurrency();
const showPaymentForm = ref(false);
const paymentAmount = ref(0);

const statusColors: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800',
    sent: 'bg-blue-100 text-blue-800',
    paid: 'bg-green-100 text-green-800',
    overdue: 'bg-red-100 text-red-800',
    cancelled: 'bg-amber-100 text-amber-800',
    partially_paid: 'bg-yellow-100 text-yellow-800',
};

const isOpen = computed(() => ['draft', 'sent', 'partially_paid'].includes(props.invoice.status));

const recordPayment = () => {
    router.post(route('stockflow.sub.invoices.payment', props.invoice.id), { amount: paymentAmount.value }, {
        preserveScroll: true,
        onSuccess: () => { showPaymentForm.value = false; paymentAmount.value = 0; },
    });
};

const updateStatus = (status: string) => {
    router.post(route('stockflow.sub.invoices.status', props.invoice.id), { status }, {
        preserveScroll: true,
    });
};
</script>

<template>
    <StockAuditLayout :title="`Invoice ${invoice.invoice_number}`">
        <Head :title="`Invoice ${invoice.invoice_number} - StockFlow`" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <Link :href="route('stockflow.sub.invoices.index')" class="text-sm text-emerald-600 hover:text-emerald-700">&larr; Back to Invoices</Link>
                        <h1 class="mt-1 text-xl font-bold text-gray-900">{{ invoice.invoice_number }}</h1>
                    </div>
                    <div class="flex gap-2">
                        <button
                            v-if="invoice.status === 'draft'"
                            @click="updateStatus('sent')"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                        >
                            Mark Sent
                        </button>
                        <button
                            v-if="isOpen"
                            @click="showPaymentForm = !showPaymentForm"
                            class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
                        >
                            Record Payment
                        </button>
                        <button
                            v-if="isOpen"
                            @click="updateStatus('cancelled')"
                            class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50"
                        >
                            Cancel
                        </button>
                    </div>
                </div>

                <div v-if="showPaymentForm" class="mb-6 rounded-xl bg-emerald-50 p-6 shadow-sm border border-emerald-200">
                    <h3 class="text-lg font-semibold text-emerald-900">Record Payment</h3>
                    <div class="mt-3 flex items-end gap-4">
                        <div>
                            <label class="block text-sm font-medium text-emerald-700">Amount</label>
                            <input v-model.number="paymentAmount" type="number" step="0.01" min="0.01" class="mt-1 rounded-lg border-emerald-300 focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                        <button @click="recordPayment" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                            Submit Payment
                        </button>
                        <button @click="showPaymentForm = false" class="text-sm text-gray-600 hover:text-gray-800">Cancel</button>
                    </div>
                </div>

                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between border-b border-gray-200 pb-4">
                        <div>
                            <p class="text-sm text-gray-500">Date: {{ invoice.invoice_date }}</p>
                            <p v-if="invoice.due_date" class="text-sm text-gray-500">Due: {{ invoice.due_date }}</p>
                            <p v-if="invoice.payment_terms" class="text-sm text-gray-500">Terms: {{ invoice.payment_terms }}</p>
                        </div>
                        <span :class="[statusColors[invoice.status], 'rounded-full px-3 py-1 text-sm font-medium capitalize']">
                            {{ invoice.status.replace('_', ' ') }}
                        </span>
                    </div>

                    <div v-if="invoice.customer_name" class="mt-4">
                        <h3 class="text-sm font-medium text-gray-500">Customer</h3>
                        <p class="text-gray-900">{{ invoice.customer_name }}</p>
                        <p v-if="invoice.customer_phone" class="text-sm text-gray-600">{{ invoice.customer_phone }}</p>
                        <p v-if="invoice.customer_email" class="text-sm text-gray-600">{{ invoice.customer_email }}</p>
                    </div>

                    <table class="mt-4 w-full">
                        <thead>
                            <tr class="text-xs text-gray-500">
                                <th class="pb-2 text-left font-medium">Item</th>
                                <th class="pb-2 text-right font-medium">Qty</th>
                                <th class="pb-2 text-right font-medium">Price</th>
                                <th class="pb-2 text-right font-medium">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="item in invoice.items" :key="item.id">
                                <td class="py-2 text-sm text-gray-900">{{ item.item_name }}</td>
                                <td class="py-2 text-right text-sm text-gray-700">{{ item.quantity }}</td>
                                <td class="py-2 text-right text-sm text-gray-700">{{ formatCurrency(item.unit_price) }}</td>
                                <td class="py-2 text-right text-sm font-medium text-gray-900">{{ formatCurrency(item.total) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-4 border-t border-gray-200 pt-4">
                        <div class="ml-auto w-64 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Subtotal</span>
                                <span class="text-gray-900">{{ formatCurrency(invoice.subtotal) }}</span>
                            </div>
                            <div v-if="invoice.discount > 0" class="flex justify-between text-sm">
                                <span class="text-gray-500">Discount</span>
                                <span class="text-red-600">-{{ formatCurrency(invoice.discount) }}</span>
                            </div>
                            <div v-if="invoice.tax > 0" class="flex justify-between text-sm">
                                <span class="text-gray-500">Tax</span>
                                <span class="text-gray-900">{{ formatCurrency(invoice.tax) }}</span>
                            </div>
                            <div class="flex justify-between border-t border-gray-200 pt-2 text-lg font-bold">
                                <span class="text-gray-900">Total</span>
                                <span class="text-gray-900">{{ formatCurrency(invoice.total) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Paid</span>
                                <span class="text-green-600 font-medium">{{ formatCurrency(invoice.amount_paid) }}</span>
                            </div>
                            <div v-if="invoice.balance_due > 0" class="flex justify-between border-t border-gray-200 pt-2 text-base font-bold">
                                <span class="text-red-600">Balance Due</span>
                                <span class="text-red-600">{{ formatCurrency(invoice.balance_due) }}</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="invoice.notes" class="mt-4 border-t border-gray-200 pt-4">
                        <p class="text-xs font-medium text-gray-500">Notes</p>
                        <p class="mt-1 text-sm text-gray-700">{{ invoice.notes }}</p>
                    </div>
                </div>
            </div>
        </div>
    </StockAuditLayout>
</template>
