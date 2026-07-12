<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { CurrencyDollarIcon, UsersIcon, BriefcaseIcon, BanknotesIcon } from '@heroicons/vue/24/outline';

interface TopVenture {
    id: number;
    title: string;
    total_raised: number;
    funding_target: number;
    investor_count: number;
    status: string;
    category: { name: string } | null;
}

interface Props {
    totalInvested: number;
    averageInvestment: number | null;
    totalShareholders: number;
    totalDividendsPaid: number;
    statusBreakdown: Record<string, number>;
    topVentures: TopVenture[];
}

const props = defineProps<Props>();

const formatCurrency = (amount: number) =>
    new Intl.NumberFormat('en-ZM', { style: 'currency', currency: 'ZMW', minimumFractionDigits: 0 }).format(amount);

const statusLabels: Record<string, string> = {
    draft: 'Draft', review: 'In Review', approved: 'Approved', funding: 'Funding',
    funded: 'Funded', active: 'Active', completed: 'Completed', cancelled: 'Cancelled',
};

const statusColors: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800', review: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-blue-100 text-blue-800', funding: 'bg-green-100 text-green-800',
    funded: 'bg-indigo-100 text-indigo-800', active: 'bg-emerald-100 text-emerald-800',
    completed: 'bg-teal-100 text-teal-800', cancelled: 'bg-red-100 text-red-800',
};

const totalVentures = Object.values(props.statusBreakdown).reduce((a, b) => a + b, 0);
</script>

<template>
    <Head title="Venture Analytics" />
    <AdminLayout>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h1 class="text-2xl font-bold text-gray-900">Venture Analytics</h1>
                <p class="mt-2 text-sm text-gray-600">Key metrics and performance data for all ventures</p>

                <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg bg-white p-6 shadow">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-blue-100 p-3"><CurrencyDollarIcon class="h-6 w-6 text-blue-600" /></div>
                            <div><div class="text-sm text-gray-600">Total Invested</div><div class="text-2xl font-bold text-gray-900">{{ formatCurrency(totalInvested) }}</div></div>
                        </div>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-green-100 p-3"><BriefcaseIcon class="h-6 w-6 text-green-600" /></div>
                            <div><div class="text-sm text-gray-600">Avg Investment</div><div class="text-2xl font-bold text-gray-900">{{ averageInvestment ? formatCurrency(averageInvestment) : '-' }}</div></div>
                        </div>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-indigo-100 p-3"><UsersIcon class="h-6 w-6 text-indigo-600" /></div>
                            <div><div class="text-sm text-gray-600">Shareholders</div><div class="text-2xl font-bold text-gray-900">{{ totalShareholders }}</div></div>
                        </div>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-emerald-100 p-3"><BanknotesIcon class="h-6 w-6 text-emerald-600" /></div>
                            <div><div class="text-sm text-gray-600">Dividends Paid</div><div class="text-2xl font-bold text-gray-900">{{ formatCurrency(totalDividendsPaid) }}</div></div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h3 class="text-lg font-semibold text-gray-900">Venture Status Breakdown</h3>
                        <div class="mt-4 space-y-3">
                            <div v-for="(count, status) in statusBreakdown" :key="status" class="flex items-center justify-between">
                                <span :class="['rounded-full px-2.5 py-0.5 text-xs font-medium', statusColors[status] || 'bg-gray-100 text-gray-800']">{{ statusLabels[status] || status }}</span>
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-32 overflow-hidden rounded-full bg-gray-200">
                                        <div class="h-full rounded-full bg-blue-600" :style="{ width: (count / totalVentures * 100) + '%' }"></div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">{{ count }}</span>
                                </div>
                            </div>
                            <div v-if="Object.keys(statusBreakdown).length === 0" class="text-sm text-gray-500">No ventures yet</div>
                        </div>
                    </div>

                    <div class="rounded-lg bg-white p-6 shadow">
                        <h3 class="text-lg font-semibold text-gray-900">Top Ventures by Funding</h3>
                        <div class="mt-4 space-y-4">
                            <div v-for="venture in topVentures" :key="venture.id" class="flex items-center justify-between border-b pb-3 last:border-0">
                                <div class="min-w-0 flex-1">
                                    <Link :href="route('admin.ventures.edit', venture.id)" class="text-sm font-medium text-blue-600 hover:text-blue-500">{{ venture.title }}</Link>
                                    <p class="text-xs text-gray-500">{{ venture.category?.name || 'Uncategorized' }} • {{ venture.investor_count }} investors</p>
                                </div>
                                <div class="ml-4 text-right">
                                    <div class="text-sm font-semibold text-gray-900">{{ formatCurrency(venture.total_raised) }}</div>
                                    <div class="text-xs text-gray-500">of {{ formatCurrency(venture.funding_target) }}</div>
                                </div>
                            </div>
                            <div v-if="topVentures.length === 0" class="text-sm text-gray-500">No funded ventures yet</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
