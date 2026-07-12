<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface Shareholder {
    id: number;
    certificate_number: string;
    total_investment: number;
    shares_owned: number;
    equity_percentage: number;
    status: string;
    total_dividends_received: number;
    registration_date: string;
    user: { id: number; name: string; email: string };
    investment: { id: number };
}

defineProps<{
    venture: { id: number; title: string };
    shareholders: { data: Shareholder[] };
}>();

const formatCurrency = (amount: number) => new Intl.NumberFormat('en-ZM', { style: 'currency', currency: 'ZMW' }).format(amount);
const formatDate = (date: string) => new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
</script>

<template>
    <Head :title="`Shareholders - ${venture.title}`" />
    <AdminLayout>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">{{ venture.title }} - Shareholders</h1>
                </div>
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Member</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Certificate</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Investment</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Shares</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Equity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Dividends</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="sh in shareholders.data" :key="sh.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900">{{ sh.user.name }}</td>
                                <td class="px-6 py-4 font-mono text-sm text-gray-900">{{ sh.certificate_number }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ formatCurrency(sh.total_investment) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ sh.shares_owned.toLocaleString() }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ sh.equity_percentage.toFixed(2) }}%</td>
                                <td class="px-6 py-4 text-sm font-semibold text-green-600">{{ formatCurrency(sh.total_dividends_received) }}</td>
                                <td class="px-6 py-4"><span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">{{ sh.status }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-if="shareholders.data.length === 0" class="p-12 text-center text-gray-500">No shareholders registered yet.</div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
