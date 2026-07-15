<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import StockAuditLayout from '@/layouts/StockAuditLayout.vue';
import { useCurrency } from '@/composables/useCurrency';
import { computed } from 'vue';

interface QuotationItem {
    id: number;
    item_name: string;
    quantity: number;
    unit_price: number;
    total: number;
}

interface Quotation {
    id: number;
    quotation_number: string;
    customer_name: string | null;
    customer_phone: string | null;
    customer_email: string | null;
    quotation_date: string;
    expiry_date: string | null;
    status: string;
    subtotal: number;
    discount: number;
    tax: number;
    total: number;
    notes: string | null;
    terms_conditions: string | null;
    created_by: number;
    converted_to_sale_id: number | null;
    items: QuotationItem[];
    created_at: string;
}

interface Props {
    quotation: Quotation;
}

const props = defineProps<Props>();

const { formatCurrency } = useCurrency();

const statusColors: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800',
    sent: 'bg-blue-100 text-blue-800',
    accepted: 'bg-green-100 text-green-800',
    declined: 'bg-red-100 text-red-800',
    expired: 'bg-amber-100 text-amber-800',
    converted: 'bg-emerald-100 text-emerald-800',
};

const statusActions: Record<string, { label: string; status: string }[]> = {
    draft: [
        { label: 'Mark Sent', status: 'sent' },
    ],
    sent: [
        { label: 'Mark Accepted', status: 'accepted' },
        { label: 'Mark Declined', status: 'declined' },
        { label: 'Mark Expired', status: 'expired' },
    ],
    accepted: [
        { label: 'Mark Converted', status: 'converted' },
    ],
    converted: [],
    declined: [],
    expired: [],
};

const canConvert = computed(() => props.quotation.status === 'accepted' || props.quotation.status === 'sent');

const updateStatus = (status: string) => {
    router.post(route('stock-audit.quotations.status', props.quotation.id), { status }, {
        preserveScroll: true,
    });
};
</script>

<template>
    <StockAuditLayout :title="`Quotation ${quotation.quotation_number}`">
        <Head :title="`Quotation ${quotation.quotation_number} - StockFlow`" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <Link :href="route('stock-audit.quotations.index')" class="text-sm text-emerald-600 hover:text-emerald-700">&larr; Back to Quotations</Link>
                        <h1 class="mt-1 text-xl font-bold text-gray-900">{{ quotation.quotation_number }}</h1>
                    </div>
                    <div class="flex gap-2">
                        <button
                            v-for="action in statusActions[quotation.status] || []"
                            :key="action.status"
                            @click="updateStatus(action.status)"
                            class="rounded-lg px-4 py-2 text-sm font-medium"
                            :class="action.status === 'declined' || action.status === 'expired'
                                ? 'border border-red-300 text-red-700 hover:bg-red-50'
                                : 'bg-emerald-600 text-white hover:bg-emerald-700'"
                        >
                            {{ action.label }}
                        </button>
                    </div>
                </div>

                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between border-b border-gray-200 pb-4">
                        <div>
                            <p class="text-sm text-gray-500">Date: {{ quotation.quotation_date }}</p>
                            <p v-if="quotation.expiry_date" class="text-sm text-gray-500">Expires: {{ quotation.expiry_date }}</p>
                        </div>
                        <span :class="[statusColors[quotation.status], 'rounded-full px-3 py-1 text-sm font-medium capitalize']">
                            {{ quotation.status }}
                        </span>
                    </div>

                    <div v-if="quotation.customer_name" class="mt-4">
                        <h3 class="text-sm font-medium text-gray-500">Customer</h3>
                        <p class="text-gray-900">{{ quotation.customer_name }}</p>
                        <p v-if="quotation.customer_phone" class="text-sm text-gray-600">{{ quotation.customer_phone }}</p>
                        <p v-if="quotation.customer_email" class="text-sm text-gray-600">{{ quotation.customer_email }}</p>
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
                            <tr v-for="item in quotation.items" :key="item.id">
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
                                <span class="text-gray-900">{{ formatCurrency(quotation.subtotal) }}</span>
                            </div>
                            <div v-if="quotation.discount > 0" class="flex justify-between text-sm">
                                <span class="text-gray-500">Discount</span>
                                <span class="text-red-600">-{{ formatCurrency(quotation.discount) }}</span>
                            </div>
                            <div v-if="quotation.tax > 0" class="flex justify-between text-sm">
                                <span class="text-gray-500">Tax</span>
                                <span class="text-gray-900">{{ formatCurrency(quotation.tax) }}</span>
                            </div>
                            <div class="flex justify-between border-t border-gray-200 pt-2 text-lg font-bold">
                                <span class="text-gray-900">Total</span>
                                <span class="text-gray-900">{{ formatCurrency(quotation.total) }}</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="quotation.terms_conditions" class="mt-4 border-t border-gray-200 pt-4">
                        <p class="text-xs font-medium text-gray-500">Terms & Conditions</p>
                        <p class="mt-1 text-sm text-gray-700 whitespace-pre-wrap">{{ quotation.terms_conditions }}</p>
                    </div>

                    <div v-if="quotation.notes" class="mt-4 border-t border-gray-200 pt-4">
                        <p class="text-xs font-medium text-gray-500">Notes</p>
                        <p class="mt-1 text-sm text-gray-700">{{ quotation.notes }}</p>
                    </div>
                </div>
            </div>
        </div>
    </StockAuditLayout>
</template>
