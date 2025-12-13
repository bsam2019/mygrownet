<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <!-- Header -->
            <div class="flex items-center gap-3 mb-6">
                <button 
                    @click="router.visit(route('growfinance.quotations.index'))"
                    class="p-2 rounded-xl bg-white shadow-sm active:bg-gray-50"
                    aria-label="Go back"
                >
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                </button>
                <div class="flex-1">
                    <h1 class="text-xl font-bold text-gray-900">{{ quotation.quotation_number }}</h1>
                    <p class="text-gray-500 text-sm">{{ quotation.subject || 'Quotation Details' }}</p>
                </div>
                <span :class="['text-xs px-3 py-1 rounded-full font-medium', statusColors[quotation.status]]">
                    {{ statusLabels[quotation.status] }}
                </span>
            </div>

            <!-- Expiry Warning -->
            <div v-if="quotation.is_expired && quotation.status !== 'expired'" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl">
                <p class="text-sm text-red-700 font-medium">⚠️ This quotation has expired</p>
            </div>

            <!-- Customer Info -->
            <div class="bg-white rounded-2xl shadow-sm p-4 mb-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Customer</p>
                        <p class="font-medium text-gray-900">{{ quotation.customer?.name || 'Walk-in Customer' }}</p>
                        <p v-if="quotation.customer?.email" class="text-sm text-gray-500">{{ quotation.customer.email }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Quote Date</p>
                        <p class="font-medium text-gray-900">{{ quotation.quotation_date }}</p>
                        <p v-if="quotation.valid_until" class="text-xs text-gray-500">
                            Valid until: {{ quotation.valid_until }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Items -->
            <div class="bg-white rounded-2xl shadow-sm p-4 mb-4">
                <h3 class="font-semibold text-gray-900 mb-3">Items</h3>
                <div class="space-y-3">
                    <div v-for="item in quotation.items" :key="item.id" class="flex justify-between items-start py-2 border-b border-gray-100 last:border-0">
                        <div class="flex-1">
                            <p class="text-gray-900">{{ item.description }}</p>
                            <p class="text-sm text-gray-500">{{ item.quantity }} × {{ formatMoney(item.unit_price) }}</p>
                        </div>
                        <p class="font-medium text-gray-900">{{ formatMoney(item.line_total) }}</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <span class="font-semibold text-gray-900">Total</span>
                        <span class="text-xl font-bold text-emerald-600">{{ formatMoney(quotation.total_amount) }}</span>
                    </div>
                </div>
            </div>

            <!-- Notes & Terms -->
            <div v-if="quotation.notes || quotation.terms" class="bg-white rounded-2xl shadow-sm p-4 mb-4">
                <div v-if="quotation.notes" class="mb-4">
                    <p class="text-sm text-gray-500 mb-1">Notes</p>
                    <p class="text-gray-700">{{ quotation.notes }}</p>
                </div>
                <div v-if="quotation.terms">
                    <p class="text-sm text-gray-500 mb-1">Terms & Conditions</p>
                    <p class="text-gray-700">{{ quotation.terms }}</p>
                </div>
            </div>

            <!-- Converted Invoice Link -->
            <div v-if="quotation.converted_invoice_id" class="bg-indigo-50 rounded-2xl p-4 mb-4">
                <p class="text-sm text-indigo-700 mb-2">This quotation has been converted to an invoice</p>
                <button
                    @click="router.visit(route('growfinance.invoices.show', quotation.converted_invoice_id))"
                    class="text-indigo-600 font-medium text-sm"
                >
                    View Invoice →
                </button>
            </div>

            <!-- Actions -->
            <div class="space-y-3">
                <!-- Draft/Sent Actions -->
                <template v-if="['draft', 'sent'].includes(quotation.status)">
                    <button
                        @click="sendQuotation"
                        :disabled="isLoading"
                        class="w-full py-3 rounded-xl bg-blue-500 text-white font-medium active:scale-[0.98] transition-transform disabled:opacity-50"
                    >
                        {{ quotation.status === 'draft' ? 'Send to Customer' : 'Resend' }}
                    </button>
                    <button
                        @click="router.visit(route('growfinance.quotations.edit', quotation.id))"
                        class="w-full py-3 rounded-xl bg-white border border-gray-200 text-gray-700 font-medium active:scale-[0.98] transition-transform"
                    >
                        Edit Quotation
                    </button>
                </template>

                <!-- Sent Actions (Accept/Reject) -->
                <template v-if="quotation.status === 'sent'">
                    <div class="grid grid-cols-2 gap-3">
                        <button
                            @click="acceptQuotation"
                            :disabled="isLoading"
                            class="py-3 rounded-xl bg-emerald-500 text-white font-medium active:scale-[0.98] transition-transform disabled:opacity-50"
                        >
                            Accept
                        </button>
                        <button
                            @click="showRejectModal = true"
                            :disabled="isLoading"
                            class="py-3 rounded-xl bg-red-500 text-white font-medium active:scale-[0.98] transition-transform disabled:opacity-50"
                        >
                            Reject
                        </button>
                    </div>
                </template>

                <!-- Accepted Actions -->
                <template v-if="quotation.status === 'accepted' && !quotation.converted_invoice_id">
                    <button
                        @click="convertToInvoice"
                        :disabled="isLoading"
                        class="w-full py-4 rounded-2xl bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-semibold shadow-lg shadow-emerald-500/30 active:scale-[0.98] transition-transform disabled:opacity-50"
                    >
                        {{ isLoading ? 'Converting...' : 'Convert to Invoice' }}
                    </button>
                </template>

                <!-- Duplicate -->
                <button
                    @click="duplicateQuotation"
                    :disabled="isLoading"
                    class="w-full py-3 rounded-xl bg-white border border-gray-200 text-gray-700 font-medium active:scale-[0.98] transition-transform disabled:opacity-50"
                >
                    Duplicate Quotation
                </button>

                <!-- Delete (Draft only) -->
                <button
                    v-if="quotation.status === 'draft'"
                    @click="deleteQuotation"
                    :disabled="isLoading"
                    class="w-full py-3 rounded-xl text-red-500 font-medium active:scale-[0.98] transition-transform disabled:opacity-50"
                >
                    Delete Quotation
                </button>
            </div>

            <!-- Reject Modal -->
            <div v-if="showRejectModal" class="fixed inset-0 z-50 flex items-end justify-center bg-black/50" @click.self="showRejectModal = false">
                <div class="w-full max-w-lg bg-white rounded-t-3xl p-6 animate-slide-up">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Reject Quotation</h3>
                    <textarea
                        v-model="rejectReason"
                        rows="3"
                        placeholder="Reason for rejection (optional)"
                        class="w-full px-4 py-3 rounded-xl border-gray-200 focus:border-red-500 focus:ring-red-500 mb-4"
                    />
                    <div class="flex gap-3">
                        <button
                            @click="showRejectModal = false"
                            class="flex-1 py-3 rounded-xl bg-gray-100 text-gray-700 font-medium"
                        >
                            Cancel
                        </button>
                        <button
                            @click="rejectQuotation"
                            :disabled="isLoading"
                            class="flex-1 py-3 rounded-xl bg-red-500 text-white font-medium disabled:opacity-50"
                        >
                            Reject
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

interface QuotationItem {
    id: number;
    description: string;
    quantity: number;
    unit_price: number;
    line_total: number;
}

interface Quotation {
    id: number;
    quotation_number: string;
    quotation_date: string;
    valid_until: string | null;
    status: string;
    subject: string | null;
    total_amount: number;
    notes: string | null;
    terms: string | null;
    is_expired: boolean;
    converted_invoice_id: number | null;
    customer: { id: number; name: string; email: string | null } | null;
    items: QuotationItem[];
}

interface Props {
    quotation: Quotation;
}

const props = defineProps<Props>();

const isLoading = ref(false);
const showRejectModal = ref(false);
const rejectReason = ref('');

const statusLabels: Record<string, string> = {
    draft: 'Draft', sent: 'Sent', accepted: 'Accepted', rejected: 'Rejected', expired: 'Expired', converted: 'Converted',
};

const statusColors: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-600',
    sent: 'bg-blue-100 text-blue-600',
    accepted: 'bg-emerald-100 text-emerald-600',
    rejected: 'bg-red-100 text-red-600',
    expired: 'bg-amber-100 text-amber-600',
    converted: 'bg-indigo-100 text-indigo-600',
};

const formatMoney = (amount: number) => {
    return 'K' + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const sendQuotation = () => {
    isLoading.value = true;
    router.post(route('growfinance.quotations.send', props.quotation.id), {}, {
        onFinish: () => { isLoading.value = false; },
    });
};

const acceptQuotation = () => {
    isLoading.value = true;
    router.post(route('growfinance.quotations.accept', props.quotation.id), {}, {
        onFinish: () => { isLoading.value = false; },
    });
};

const rejectQuotation = () => {
    isLoading.value = true;
    router.post(route('growfinance.quotations.reject', props.quotation.id), { reason: rejectReason.value }, {
        onFinish: () => { 
            isLoading.value = false; 
            showRejectModal.value = false;
        },
    });
};

const convertToInvoice = () => {
    isLoading.value = true;
    router.post(route('growfinance.quotations.convert', props.quotation.id), {}, {
        onFinish: () => { isLoading.value = false; },
    });
};

const duplicateQuotation = () => {
    isLoading.value = true;
    router.post(route('growfinance.quotations.duplicate', props.quotation.id), {}, {
        onFinish: () => { isLoading.value = false; },
    });
};

const deleteQuotation = () => {
    if (confirm('Are you sure you want to delete this quotation?')) {
        isLoading.value = true;
        router.delete(route('growfinance.quotations.destroy', props.quotation.id), {
            onFinish: () => { isLoading.value = false; },
        });
    }
};
</script>

<style scoped>
@keyframes slide-up {
    from { transform: translateY(100%); }
    to { transform: translateY(0); }
}
.animate-slide-up { animation: slide-up 0.3s ease-out; }
</style>
