<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PrimeEdgeAppLayout from '@/layouts/PrimeEdgeAppLayout.vue';

defineProps<{
    invoices: Array<{
        id: string;
        number: string;
        status: string;
        total: { amount: number; currency: string } | null;
        createdAt: string;
    }>;
}>();
</script>

<template>
    <PrimeEdgeAppLayout>
        <Head title="Invoices - PrimeEdge Advisory" />
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Invoices</h1>
            <p class="text-gray-600 mt-1">View and manage your invoices.</p>
        </div>
        <div v-if="invoices?.length" class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-6 py-3 font-medium text-gray-500">Invoice</th>
                        <th class="text-left px-6 py-3 font-medium text-gray-500">Amount</th>
                        <th class="text-left px-6 py-3 font-medium text-gray-500">Status</th>
                        <th class="text-left px-6 py-3 font-medium text-gray-500">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="inv in invoices" :key="inv.id" class="hover:bg-gray-50 cursor-pointer" @click="() => route('primeedge.invoices.show', inv.id)">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ inv.number }}</td>
                        <td class="px-6 py-4 text-gray-900">{{ inv.total ? inv.total.currency + ' ' + inv.total.amount.toLocaleString() : '—' }}</td>
                        <td class="px-6 py-4">
                            <span class="text-xs px-2 py-1 rounded-full font-medium" :class="inv.status === 'paid' ? 'bg-emerald-100 text-emerald-800' : inv.status === 'sent' ? 'bg-blue-100 text-blue-800' : inv.status === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600'">{{ inv.status }}</span>
                        </td>
                        <td class="px-6 py-4 text-gray-500">{{ inv.createdAt }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p v-else class="text-center text-gray-500 py-12">No invoices yet.</p>
    </PrimeEdgeAppLayout>
</template>
