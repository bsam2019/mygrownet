<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ArrowPathIcon, ArrowRightIcon, CheckCircleIcon, XCircleIcon, ClockIcon } from '@heroicons/vue/24/outline';

interface Transfer {
    id: number;
    shares: number;
    price_per_share: number | null;
    total_value: number | null;
    status: string;
    reason: string | null;
    created_at: string;
    completed_at: string | null;
    venture: { id: number; title: string; slug: string };
    from_user: { id: number; name: string; email: string };
    to_user: { id: number; name: string; email: string };
}

interface Props {
    transfers: { data: Transfer[] };
}

const props = defineProps<Props>();
const userId = (window as any).__user?.id;

const formatCurrency = (amount: number | null) => {
    if (amount === null) return '-';
    return new Intl.NumberFormat('en-ZM', { style: 'currency', currency: 'ZMW', minimumFractionDigits: 0 }).format(amount);
};

const getStatusIcon = (status: string) => {
    const icons: Record<string, any> = { pending: ClockIcon, approved: CheckCircleIcon, completed: CheckCircleIcon, rejected: XCircleIcon, cancelled: XCircleIcon };
    return icons[status] || ClockIcon;
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-800', approved: 'bg-green-100 text-green-800',
        completed: 'bg-blue-100 text-blue-800', rejected: 'bg-red-100 text-red-800', cancelled: 'bg-gray-100 text-gray-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Share Transfers" />
    <AppLayout>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Share Transfers</h1>
                    <p class="mt-1 text-sm text-gray-600">Track your share transfer requests</p>
                </div>

                <div v-if="!transfers.data.length" class="rounded-lg bg-white p-12 text-center shadow">
                    <ArrowPathIcon class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">No transfers</h3>
                    <p class="mt-1 text-sm text-gray-500">You haven't made or received any share transfer requests.</p>
                </div>

                <div v-else class="space-y-4">
                    <div v-for="transfer in transfers.data" :key="transfer.id" class="rounded-lg bg-white p-6 shadow">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <component :is="getStatusIcon(transfer.status)" class="h-8 w-8 text-gray-400" />
                                <div>
                                    <Link :href="route('mygrownet.ventures.show', transfer.venture.slug)" class="font-semibold text-gray-900 hover:text-blue-600">{{ transfer.venture.title }}</Link>
                                    <div class="mt-1 flex items-center gap-2 text-sm text-gray-600">
                                        <span>{{ transfer.from_user.name }}</span>
                                        <ArrowRightIcon class="h-4 w-4" />
                                        <span>{{ transfer.to_user.name }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold text-gray-900">{{ transfer.shares.toLocaleString() }} shares</div>
                                <div v-if="transfer.total_value" class="text-sm text-gray-600">{{ formatCurrency(transfer.total_value) }}</div>
                                <span :class="getStatusColor(transfer.status)" class="mt-1 inline-block rounded-full px-2.5 py-0.5 text-xs font-medium capitalize">{{ transfer.status }}</span>
                            </div>
                        </div>
                        <div v-if="transfer.reason" class="mt-3 border-t pt-3 text-sm text-gray-600">
                            <span class="font-medium">Reason:</span> {{ transfer.reason }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
