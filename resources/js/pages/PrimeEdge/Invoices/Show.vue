<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PrimeEdgeAppLayout from '@/layouts/PrimeEdgeAppLayout.vue';

defineProps<{
    invoice: {
        id: string;
        number: string;
        status: string;
        total: { amount: number; currency: string } | null;
        notes: string | null;
        lineItems: Array<{
            description: string;
            unitPrice: { amount: number; currency: string };
            quantity: number;
            total: { amount: number; currency: string };
        }>;
        createdAt: string;
        sentAt: string | null;
        paidAt: string | null;
    };
}>();
</script>

<template>
    <PrimeEdgeAppLayout>
        <Head :title="'Invoice ' + invoice.number + ' - PrimeEdge Advisory'" />
        <div class="mb-6">
            <Link :href="route('primeedge.invoices.index')" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to Invoices</Link>
            <div class="flex items-center justify-between mt-1">
                <h1 class="text-2xl font-bold text-gray-900">Invoice {{ invoice.number }}</h1>
                <span class="text-sm px-3 py-1 rounded-full font-medium" :class="invoice.status === 'paid' ? 'bg-emerald-100 text-emerald-800' : invoice.status === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'">{{ invoice.status }}</span>
            </div>
        </div>
        <div class="max-w-3xl">
            <div class="bg-white rounded-xl p-8 border border-gray-100 shadow-sm">
                <div class="flex justify-between mb-8">
                    <div>
                        <h2 class="font-bold text-gray-900">PrimeEdge Advisory</h2>
                        <p class="text-sm text-gray-500">Lusaka, Zambia</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Issued: {{ invoice.createdAt }}</p>
                        <p v-if="invoice.sentAt" class="text-sm text-gray-500">Sent: {{ invoice.sentAt }}</p>
                    </div>
                </div>
                <table class="w-full text-sm mb-6">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 font-medium text-gray-500">Item</th>
                            <th class="text-right py-3 font-medium text-gray-500">Qty</th>
                            <th class="text-right py-3 font-medium text-gray-500">Rate</th>
                            <th class="text-right py-3 font-medium text-gray-500">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in invoice.lineItems" :key="item.description" class="border-b border-gray-100">
                            <td class="py-3 text-gray-900">{{ item.description }}</td>
                            <td class="py-3 text-right text-gray-600">{{ item.quantity }}</td>
                            <td class="py-3 text-right text-gray-600">{{ item.unitPrice.currency }} {{ item.unitPrice.amount.toLocaleString() }}</td>
                            <td class="py-3 text-right text-gray-900 font-medium">{{ item.total.currency }} {{ item.total.amount.toLocaleString() }}</td>
                        </tr>
                    </tbody>
                </table>
                <div class="text-right" v-if="invoice.total">
                    <p class="text-lg font-bold text-gray-900">{{ invoice.total.currency }} {{ invoice.total.amount.toLocaleString() }}</p>
                </div>
            </div>
        </div>
    </PrimeEdgeAppLayout>
</template>
