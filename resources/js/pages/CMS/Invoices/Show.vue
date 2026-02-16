<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import CMSLayout from '@/Layouts/CMSLayout.vue';

defineOptions({
  layout: CMSLayout
})

interface Props {
    invoice: any;
    statuses: Array<{ value: string; label: string; color: string }>;
}

const props = defineProps<Props>();

const showCancelModal = ref(false);
const cancelReason = ref('');

const sendInvoice = () => {
    if (confirm('Mark this invoice as sent?')) {
        router.post(route('cms.invoices.send', props.invoice.id));
    }
};

const cancelInvoice = () => {
    if (cancelReason.value.trim()) {
        router.post(route('cms.invoices.cancel', props.invoice.id), {
            reason: cancelReason.value,
        });
        showCancelModal.value = false;
    }
};

const formatCurrency = (amount: number) => {
    return `K${amount.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
};

const getStatusColor = (status: string) => {
    const statusObj = props.statuses.find(s => s.value === status);
    return statusObj?.color || 'gray';
};
</script>

<template>
    <Head :title="`Invoice ${invoice.invoice_number} - CMS`" />

    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Invoice {{ invoice.invoice_number }}</h1>
                <p class="mt-1 text-sm text-gray-600">{{ invoice.customer.name }}</p>
            </div>
            <div class="flex gap-2">
                <a
                    :href="route('cms.invoices.pdf', invoice.id)"
                    target="_blank"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                >
                    Download PDF
                </a>
                <Link
                    v-if="invoice.status === 'draft'"
                    :href="route('cms.invoices.edit', invoice.id)"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                >
                    Edit
                </Link>
                <button
                    v-if="invoice.status === 'draft'"
                    @click="sendInvoice"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                >
                    Send Invoice
                </button>
                <Link
                    :href="route('cms.payments.create', { customer_id: invoice.customer_id })"
                    class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700"
                >
                    Record Payment
                </Link>
            </div>
        </div>

        <!-- Invoice Details -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Invoice Info -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <div class="text-sm font-medium text-gray-600">Invoice Date</div>
                            <div class="mt-1 text-gray-900">{{ formatDate(invoice.invoice_date) }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-600">Due Date</div>
                            <div class="mt-1 text-gray-900">{{ formatDate(invoice.due_date) }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-600">Status</div>
                            <span
                                :class="[
                                    'mt-1 inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                                    getStatusColor(invoice.status) === 'gray' && 'bg-gray-100 text-gray-800',
                                    getStatusColor(invoice.status) === 'blue' && 'bg-blue-100 text-blue-800',
                                    getStatusColor(invoice.status) === 'amber' && 'bg-amber-100 text-amber-800',
                                    getStatusColor(invoice.status) === 'green' && 'bg-green-100 text-green-800',
                                ]"
                            >
                                {{ statuses.find(s => s.value === invoice.status)?.label }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Items -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Items</h2>
                    <table class="min-w-full">
                        <thead class="border-b">
                            <tr>
                                <th class="pb-2 text-left text-sm font-medium text-gray-600">Description</th>
                                <th class="pb-2 text-right text-sm font-medium text-gray-600">Qty</th>
                                <th class="pb-2 text-right text-sm font-medium text-gray-600">Price</th>
                                <th class="pb-2 text-right text-sm font-medium text-gray-600">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="item in invoice.items" :key="item.id">
                                <td class="py-3 text-sm text-gray-900">{{ item.description }}</td>
                                <td class="py-3 text-right text-sm text-gray-600">{{ item.quantity }}</td>
                                <td class="py-3 text-right text-sm text-gray-600">{{ formatCurrency(item.unit_price) }}</td>
                                <td class="py-3 text-right text-sm font-medium text-gray-900">{{ formatCurrency(item.line_total) }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="border-t">
                            <tr>
                                <td colspan="3" class="pt-4 text-right text-sm font-medium text-gray-600">Subtotal:</td>
                                <td class="pt-4 text-right text-sm font-medium text-gray-900">{{ formatCurrency(invoice.subtotal) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="pt-2 text-right text-lg font-bold text-gray-900">Total:</td>
                                <td class="pt-2 text-right text-lg font-bold text-gray-900">{{ formatCurrency(invoice.total_amount) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="pt-2 text-right text-sm font-medium text-gray-600">Amount Paid:</td>
                                <td class="pt-2 text-right text-sm font-medium text-green-600">{{ formatCurrency(invoice.amount_paid) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="pt-2 text-right text-sm font-medium text-gray-600">Balance Due:</td>
                                <td class="pt-2 text-right text-sm font-medium text-amber-600">{{ formatCurrency(invoice.total_amount - invoice.amount_paid) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Notes -->
                <div v-if="invoice.notes" class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-2 text-lg font-semibold text-gray-900">Notes</h2>
                    <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ invoice.notes }}</p>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Customer Info -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Customer</h2>
                    <div class="space-y-3">
                        <div>
                            <div class="text-sm font-medium text-gray-600">Name</div>
                            <div class="mt-1 text-gray-900">{{ invoice.customer.name }}</div>
                        </div>
                        <div v-if="invoice.customer.email">
                            <div class="text-sm font-medium text-gray-600">Email</div>
                            <div class="mt-1 text-gray-900">{{ invoice.customer.email }}</div>
                        </div>
                        <div v-if="invoice.customer.phone">
                            <div class="text-sm font-medium text-gray-600">Phone</div>
                            <div class="mt-1 text-gray-900">{{ invoice.customer.phone }}</div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Actions</h2>
                    <div class="space-y-2">
                        <button
                            v-if="invoice.status !== 'cancelled'"
                            @click="showCancelModal = true"
                            class="w-full rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50"
                        >
                            Cancel Invoice
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cancel Modal -->
        <div v-if="showCancelModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="w-full max-w-md rounded-lg bg-white p-6">
                <h3 class="text-lg font-semibold text-gray-900">Cancel Invoice</h3>
                <p class="mt-2 text-sm text-gray-600">Please provide a reason for cancelling this invoice.</p>
                <textarea
                    v-model="cancelReason"
                    rows="3"
                    class="mt-4 block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Reason for cancellation..."
                />
                <div class="mt-6 flex justify-end gap-3">
                    <button
                        @click="showCancelModal = false"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                    >
                        Close
                    </button>
                    <button
                        @click="cancelInvoice"
                        :disabled="!cancelReason.trim()"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50"
                    >
                        Cancel Invoice
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
