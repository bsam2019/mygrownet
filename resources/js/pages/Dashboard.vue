<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import WorkspaceLayout from '@/layouts/WorkspaceLayout.vue';

const props = defineProps<{
    stats: {
        totalInvestment: number;
        activeReferrals: number;
        referralEarnings: number;
        returnRate: number;
        nextPayout: number;
        totalEarnings: number;
        pendingWithdrawals: number;
        recentTransactions: {
            id: number;
            type: string;
            amount: number;
            status: string;
            date: string;
        }[];
    };
}>();

const formatCurrency = (amount: number) => `K${amount.toLocaleString('en-ZM', { minimumFractionDigits: 2 })}`;

const statCards = [
    { label: 'Total Investment', value: formatCurrency(props.stats.totalInvestment), color: 'from-blue-500 to-blue-600' },
    { label: 'Active Referrals', value: props.stats.activeReferrals.toString(), color: 'from-emerald-500 to-emerald-600' },
    { label: 'Return Rate', value: `${props.stats.returnRate}%`, color: 'from-violet-500 to-violet-600' },
    { label: 'Next Payout', value: formatCurrency(props.stats.nextPayout), color: 'from-amber-500 to-amber-600' },
    { label: 'Total Earnings', value: formatCurrency(props.stats.totalEarnings), color: 'from-green-500 to-green-600' },
    { label: 'Pending Withdrawals', value: props.stats.pendingWithdrawals.toString(), color: 'from-rose-500 to-rose-600' },
];
</script>

<template>
    <WorkspaceLayout>
        <Head title="Dashboard" />
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Platform Dashboard</h1>
                <p class="text-gray-500 mt-1">Your overall platform statistics and recent activity</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
                <div
                    v-for="card in statCards"
                    :key="card.label"
                    class="bg-white rounded-xl border border-gray-200 shadow-sm p-5"
                >
                    <p class="text-sm text-gray-500 mb-1">{{ card.label }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ card.value }}</p>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">Recent Transactions</h3>
                </div>
                <div v-if="props.stats.recentTransactions.length > 0" class="divide-y divide-gray-100">
                    <div v-for="txn in props.stats.recentTransactions" :key="txn.id" class="px-5 py-3 flex items-center justify-between">
                        <div>
                            <span class="text-sm font-medium text-gray-900 capitalize">{{ txn.type }}</span>
                            <span class="text-xs text-gray-400 ml-2">{{ txn.date }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-semibold text-gray-900">{{ formatCurrency(txn.amount) }}</span>
                            <span :class="['text-xs font-medium px-2 py-0.5 rounded-full capitalize', txn.status === 'completed' ? 'bg-green-100 text-green-700' : txn.status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700']">{{ txn.status }}</span>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-12 text-sm text-gray-500">No recent transactions</div>
            </div>

            <div class="mt-6 text-center">
                <Link :href="route('workspace')" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">← Back to Workspace</Link>
            </div>
        </div>
    </WorkspaceLayout>
</template>
