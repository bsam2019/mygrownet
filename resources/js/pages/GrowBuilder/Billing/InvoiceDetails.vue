<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';
import {
    ArrowLeftIcon,
    PencilIcon,
    TrashIcon,
    PaperAirplaneIcon,
    CurrencyDollarIcon,
    DocumentTextIcon,
    ExclamationTriangleIcon,
    CheckCircleIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

interface InvoiceItem {
    id: number;
    description: string;
    quantity: number;
    amount: number;
    total: number;
    service: {
        id: number;
        name: string;
    } | null;
}

interface Payment {
    id: number;
    amount: number;
    payment_date: string;
    payment_method: string | null;
    reference: string | null;
    notes: string | null;
}

interface Invoice {
    id: number;
    invoice_number: string;
    client: {
        id: number;
        name: string;
        company_name: string | null;
        email: string | null;
        phone: string | null;
        address: string | null;
        city: string | null;
        country: string | null;
    };
    invoice_date: string;
    due_date: string;
    subtotal: number;
    tax: number;
    total: number;
    total_paid: number;
    balance: number;
    currency: string;
    payment_status: string;
    is_overdue: boolean;
    notes: string | null;
    items: InvoiceItem[];
    payments: Payment[];
}

interface Props {
    invoice: Invoice;
}

const props = defineProps<Props>();

const showPaymentModal = ref(false);
const paymentForm = useForm({
    amount: props.invoice.balance,
    payment_date: new Date().toISOString().split('T')[0],
    payment_method: 'bank_transfer',
    reference: '',
    notes: '',
});

const getPaymentStatusClass = (status: string): string => {
    const classes = {
        draft: 'bg-gray-100 text-gray-800',
        sent: 'bg-blue-100 text-blue-800',
        paid: 'bg-green-100 text-green-800',
        partial: 'bg-amber-100 text-amber-800',
        overdue: 'bg-red-100 text-red-800',
    };
    return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800';
};

const markAsSent = () => {
    if (confirm('Mark this invoice as sent to the client?')) {
        router.post(route('growbuilder.invoices.mark-as-sent', props.invoice.id), {}, {
            preserveScroll: true,
        });
    }
};

const recordPayment = () => {
    paymentForm.post(route('growbuilder.invoices.record-payment', props.invoice.id), {
        preserveScroll: true,
        onSuccess: () => {
            showPaymentModal.value = false;
            paymentForm.reset();
        },
    });
};

const deleteInvoice = () => {
    if (confirm(`Are you sure you want to delete invoice ${props.invoice.invoice_number}?`)) {
        router.delete(route('growbuilder.invoices.destroy', props.invoice.id));
    }
};

const openPaymentModal = () => {
    paymentForm.amount = props.invoice.balance;
    showPaymentModal.value = true;
};
</script>

<template>
    <AppLayout>
        <Head :title="`Invoice ${invoice.invoice_number}`" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('growbuilder.invoices.index')"
                        class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4"
                    >
                        <ArrowLeftIcon class="h-4 w-4 mr-2" aria-hidden="true" />
                        Back to Invoices
                    </Link>
                    
                    <div class="flex items-start justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ invoice.invoice_number }}</h1>
                            <p class="text-gray-600">Invoice for {{ invoice.client.name }}</p>
                            <div class="mt-2 flex items-center gap-3">
                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full"
                                      :class="getPaymentStatusClass(invoice.payment_status)">
                                    {{ invoice.payment_status }}
                                </span>
                                <span v-if="invoice.is_overdue" class="inline-flex items-center gap-1 px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                                    <ExclamationTriangleIcon class="h-4 w-4" aria-hidden="true" />
                                    Overdue
                                </span>
                            </div>
                        </div>
                        
                        <div class="flex gap-3">
                            <button
                                v-if="invoice.payment_status === 'draft'"
                                @click="markAsSent"
                                class="inline-flex items-center px-4 py-2 border border-blue-300 text-blue-700 rounded-lg hover:bg-blue-50"
                            >
                                <PaperAirplaneIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                                Mark as Sent
                            </button>
                            <button
                                v-if="invoice.balance > 0 && invoice.payment_status !== 'draft'"
                                @click="openPaymentModal"
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
                            >
                                <CurrencyDollarIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                                Record Payment
                            </button>
                            <Link
                                v-if="invoice.payment_status === 'draft'"
                                :href="route('growbuilder.invoices.edit', invoice.id)"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                            >
                                <PencilIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                                Edit
                            </Link>
                            <button
                                v-if="invoice.payment_status === 'draft'"
                                @click="deleteInvoice"
                                class="inline-flex items-center px-4 py-2 border border-red-300 text-red-700 rounded-lg hover:bg-red-50"
                            >
                                <TrashIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                                Delete
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Invoice Details -->
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">
                            <!-- Header Section -->
                            <div class="flex justify-between mb-8">
                                <div>
                                    <h2 class="text-2xl font-bold text-gray-900">INVOICE</h2>
                                    <p class="text-sm text-gray-600 mt-1">{{ invoice.invoice_number }}</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-gray-600">Invoice Date</div>
                                    <div class="font-semibold text-gray-900">{{ invoice.invoice_date }}</div>
                                    <div class="text-sm text-gray-600 mt-2">Due Date</div>
                                    <div class="font-semibold" :class="invoice.is_overdue ? 'text-red-600' : 'text-gray-900'">
                                        {{ invoice.due_date }}
                                    </div>
                                </div>
                            </div>

                            <!-- Client Information -->
                            <div class="mb-8 p-4 bg-gray-50 rounded-lg">
                                <div class="text-sm font-medium text-gray-600 mb-2">Bill To:</div>
                                <div class="font-semibold text-gray-900">{{ invoice.client.name }}</div>
                                <div v-if="invoice.client.company_name" class="text-gray-700">{{ invoice.client.company_name }}</div>
                                <div v-if="invoice.client.address" class="text-sm text-gray-600 mt-2">{{ invoice.client.address }}</div>
                                <div v-if="invoice.client.city || invoice.client.country" class="text-sm text-gray-600">
                                    {{ [invoice.client.city, invoice.client.country].filter(Boolean).join(', ') }}
                                </div>
                                <div v-if="invoice.client.email" class="text-sm text-gray-600 mt-2">{{ invoice.client.email }}</div>
                                <div v-if="invoice.client.phone" class="text-sm text-gray-600">{{ invoice.client.phone }}</div>
                            </div>

                            <!-- Invoice Items -->
                            <div class="mb-8">
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b-2 border-gray-300">
                                            <th class="text-left py-3 text-sm font-semibold text-gray-700">Description</th>
                                            <th class="text-center py-3 text-sm font-semibold text-gray-700">Qty</th>
                                            <th class="text-right py-3 text-sm font-semibold text-gray-700">Rate</th>
                                            <th class="text-right py-3 text-sm font-semibold text-gray-700">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in invoice.items" :key="item.id" class="border-b border-gray-200">
                                            <td class="py-4 text-sm text-gray-900">
                                                {{ item.description }}
                                                <span v-if="item.service" class="block text-xs text-gray-500 mt-1">
                                                    Service: {{ item.service.name }}
                                                </span>
                                            </td>
                                            <td class="py-4 text-center text-sm text-gray-900">{{ item.quantity }}</td>
                                            <td class="py-4 text-right text-sm text-gray-900">{{ invoice.currency }} {{ item.amount.toFixed(2) }}</td>
                                            <td class="py-4 text-right text-sm font-semibold text-gray-900">{{ invoice.currency }} {{ item.total.toFixed(2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Totals -->
                            <div class="flex justify-end">
                                <div class="w-64 space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Subtotal:</span>
                                        <span class="font-semibold text-gray-900">{{ invoice.currency }} {{ invoice.subtotal.toFixed(2) }}</span>
                                    </div>
                                    <div v-if="invoice.tax > 0" class="flex justify-between text-sm">
                                        <span class="text-gray-600">Tax:</span>
                                        <span class="font-semibold text-gray-900">{{ invoice.currency }} {{ invoice.tax.toFixed(2) }}</span>
                                    </div>
                                    <div class="flex justify-between text-lg font-bold border-t pt-2">
                                        <span>Total:</span>
                                        <span>{{ invoice.currency }} {{ invoice.total.toFixed(2) }}</span>
                                    </div>
                                    <div v-if="invoice.total_paid > 0" class="flex justify-between text-sm text-green-600">
                                        <span>Paid:</span>
                                        <span class="font-semibold">{{ invoice.currency }} {{ invoice.total_paid.toFixed(2) }}</span>
                                    </div>
                                    <div v-if="invoice.balance > 0" class="flex justify-between text-lg font-bold text-red-600 border-t pt-2">
                                        <span>Balance Due:</span>
                                        <span>{{ invoice.currency }} {{ invoice.balance.toFixed(2) }}</span>
                                    </div>
                                    <div v-else class="flex items-center justify-center gap-2 text-green-600 font-semibold border-t pt-2">
                                        <CheckCircleIcon class="h-5 w-5" aria-hidden="true" />
                                        <span>Paid in Full</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div v-if="invoice.notes" class="mt-8 p-4 bg-gray-50 rounded-lg">
                                <div class="text-sm font-medium text-gray-600 mb-2">Notes:</div>
                                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ invoice.notes }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Payment History -->
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <CurrencyDollarIcon class="h-5 w-5 text-green-600" aria-hidden="true" />
                                Payment History
                            </h2>
                            
                            <div v-if="invoice.payments && invoice.payments.length > 0" class="space-y-3">
                                <div
                                    v-for="payment in invoice.payments"
                                    :key="payment.id"
                                    class="p-3 border border-gray-200 rounded-lg"
                                >
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-semibold text-green-600">
                                            {{ invoice.currency }} {{ payment.amount.toFixed(2) }}
                                        </span>
                                        <span class="text-xs text-gray-500">{{ payment.payment_date }}</span>
                                    </div>
                                    <div v-if="payment.payment_method" class="text-xs text-gray-600 capitalize">
                                        {{ payment.payment_method.replace('_', ' ') }}
                                    </div>
                                    <div v-if="payment.reference" class="text-xs text-gray-500 mt-1">
                                        Ref: {{ payment.reference }}
                                    </div>
                                    <div v-if="payment.notes" class="text-xs text-gray-600 mt-2">
                                        {{ payment.notes }}
                                    </div>
                                </div>
                            </div>
                            
                            <div v-else class="text-center py-6">
                                <CurrencyDollarIcon class="mx-auto h-8 w-8 text-gray-400" aria-hidden="true" />
                                <p class="mt-2 text-sm text-gray-500">No payments recorded</p>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
                            <div class="space-y-2">
                                <Link
                                    :href="route('growbuilder.clients.show', invoice.client.id)"
                                    class="block w-full px-4 py-2 text-center border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                                >
                                    View Client
                                </Link>
                                <button
                                    class="block w-full px-4 py-2 text-center border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                                >
                                    Download PDF
                                </button>
                                <button
                                    class="block w-full px-4 py-2 text-center border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                                >
                                    Send Email
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Modal -->
        <div
            v-if="showPaymentModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @click.self="showPaymentModal = false"
        >
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Record Payment</h3>
                        <button
                            @click="showPaymentModal = false"
                            class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100"
                        >
                            <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                        </button>
                    </div>

                    <form @submit.prevent="recordPayment" class="space-y-4">
                        <div>
                            <label for="payment_amount" class="block text-sm font-medium text-gray-700 mb-2">
                                Amount ({{ invoice.currency }}) *
                            </label>
                            <input
                                id="payment_amount"
                                v-model.number="paymentForm.amount"
                                type="number"
                                step="0.01"
                                :max="invoice.balance"
                                min="0.01"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            />
                            <p class="mt-1 text-xs text-gray-500">
                                Balance due: {{ invoice.currency }} {{ invoice.balance.toFixed(2) }}
                            </p>
                            <div v-if="paymentForm.errors.amount" class="mt-1 text-sm text-red-600">
                                {{ paymentForm.errors.amount }}
                            </div>
                        </div>

                        <div>
                            <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Payment Date *
                            </label>
                            <input
                                id="payment_date"
                                v-model="paymentForm.payment_date"
                                type="date"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            />
                            <div v-if="paymentForm.errors.payment_date" class="mt-1 text-sm text-red-600">
                                {{ paymentForm.errors.payment_date }}
                            </div>
                        </div>

                        <div>
                            <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">
                                Payment Method
                            </label>
                            <select
                                id="payment_method"
                                v-model="paymentForm.payment_method"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="cash">Cash</option>
                                <option value="check">Check</option>
                                <option value="card">Card</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label for="payment_reference" class="block text-sm font-medium text-gray-700 mb-2">
                                Reference Number
                            </label>
                            <input
                                id="payment_reference"
                                v-model="paymentForm.reference"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Transaction ID, check number, etc."
                            />
                        </div>

                        <div>
                            <label for="payment_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Notes
                            </label>
                            <textarea
                                id="payment_notes"
                                v-model="paymentForm.notes"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Any additional notes..."
                            ></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t">
                            <button
                                type="button"
                                @click="showPaymentModal = false"
                                class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="paymentForm.processing"
                                class="px-6 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition"
                            >
                                <span v-if="paymentForm.processing">Recording...</span>
                                <span v-else>Record Payment</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
