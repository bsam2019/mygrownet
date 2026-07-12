<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ref } from 'vue';

interface Dividend {
    id: number; dividend_period: string; amount: number; status: string;
    payment_date: string | null; paid_at: string | null; payment_reference: string | null;
    shareholder: { user: { name: string }; certificate_number: string; equity_percentage: number };
    processor: { name: string } | null;
}

const props = defineProps<{
    venture: { id: number; title: string };
    dividends: { data: Dividend[] };
}>();

const showDeclare = ref(false);
const form = useForm({ dividend_period: '', total_amount: 0, notes: '' });

const declare = () => {
    form.post(route('admin.ventures.dividends.declare', props.venture.id), {
        onSuccess: () => { showDeclare.value = false; form.reset(); }
    });
};
const formatCurrency = (amount: number) => new Intl.NumberFormat('en-ZM', { style: 'currency', currency: 'ZMW' }).format(amount);
const formatDate = (date: string | null) => date ? new Date(date).toLocaleDateString() : 'N/A';
const getStatusColor = (s: string) => ({ declared: 'bg-yellow-100 text-yellow-800', processing: 'bg-indigo-100 text-indigo-800', paid: 'bg-green-100 text-green-800', failed: 'bg-red-100 text-red-800' }[s] || 'bg-gray-100 text-gray-800');
</script>

<template>
    <Head :title="`Dividends - ${venture.title}`" />
    <AdminLayout>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">{{ venture.title }} - Dividends</h1>
                    <button @click="showDeclare = !showDeclare" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">{{ showDeclare ? 'Cancel' : 'Declare Dividend' }}</button>
                </div>
                <form v-if="showDeclare" @submit.prevent="declare" class="mb-6 rounded-lg bg-white p-6 shadow space-y-4">
                    <input v-model="form.dividend_period" placeholder="e.g. Q1 2025" class="w-full rounded-md border-gray-300" required />
                    <input v-model.number="form.total_amount" type="number" step="0.01" min="1" placeholder="Total amount" class="w-full rounded-md border-gray-300" required />
                    <textarea v-model="form.notes" placeholder="Notes" class="w-full rounded-md border-gray-300"></textarea>
                    <button type="submit" :disabled="form.processing" class="rounded-md bg-blue-600 px-4 py-2 text-sm text-white">{{ form.processing ? 'Declaring...' : 'Declare Dividend' }}</button>
                </form>
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50"><tr><th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Member</th><th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Period</th><th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Amount</th><th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th><th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Payment Ref</th><th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Date</th><th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Processed By</th></tr></thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="d in dividends.data" :key="d.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900">{{ d.shareholder.user.name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ d.dividend_period }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ formatCurrency(d.amount) }}</td>
                                <td class="px-6 py-4"><span :class="['inline-flex rounded-full px-2 py-1 text-xs font-medium', getStatusColor(d.status)]">{{ d.status }}</span></td>
                                <td class="px-6 py-4 font-mono text-xs text-gray-600">{{ d.payment_reference || 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ formatDate(d.paid_at || d.payment_date) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ d.processor?.name || 'N/A' }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-if="dividends.data.length === 0" class="p-12 text-center text-gray-500">No dividends declared yet.</div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
