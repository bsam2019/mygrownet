<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StockAuditLayout from '@/layouts/StockAuditLayout.vue';

interface Audit {
    id: number;
    title: string;
    report_reference: string;
    audit_date: string;
    status: string;
    total_system_value: number;
    total_physical_value: number;
    total_variance: number;
    unaccounted_value: number;
    total_recorded_sales: number;
    created_at: string;
}

interface Props {
    audits: Audit[];
}

defineProps<Props>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', { style: 'currency', currency: 'ZMW', minimumFractionDigits: 2 }).format(amount);
};

const statusColors: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800',
    finalized: 'bg-green-100 text-green-800',
    archived: 'bg-blue-100 text-blue-800',
};
</script>

<template>
    <StockAuditLayout title="Audits">
        <Head title="Audits - StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Audits</h1>
                    <p class="mt-1 text-sm text-gray-500">Stock audits generated from physical counts</p>
                </div>

                <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Reference</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Variance</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Unaccounted</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="audit in audits" :key="audit.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-mono text-sm text-gray-900">{{ audit.report_reference }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ audit.title }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ audit.audit_date }}</td>
                                <td class="px-6 py-4">
                                    <span :class="[statusColors[audit.status] || 'bg-gray-100 text-gray-800', 'inline-flex rounded-full px-2 py-1 text-xs font-medium capitalize']">
                                        {{ audit.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium" :class="audit.total_variance < 0 ? 'text-red-600' : 'text-emerald-600'">
                                    {{ formatCurrency(audit.total_variance) }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm" :class="audit.unaccounted_value < 0 ? 'text-red-600 font-medium' : 'text-gray-700'">
                                    {{ formatCurrency(audit.unaccounted_value) }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Link :href="route('stock-audit.audits.show', audit.id)" class="text-sm text-emerald-600 hover:text-emerald-700">View</Link>
                                </td>
                            </tr>
                            <tr v-if="audits.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">No audits yet</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </StockAuditLayout>
</template>
