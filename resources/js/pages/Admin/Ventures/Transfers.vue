<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { CheckCircleIcon, XCircleIcon, ArrowRightIcon, ClockIcon } from '@heroicons/vue/24/outline';

interface Transfer {
    id: number;
    shares: number;
    price_per_share: number | null;
    total_value: number | null;
    status: string;
    reason: string | null;
    admin_notes: string | null;
    created_at: string;
    venture: { id: number; title: string; slug: string };
    from_user: { id: number; name: string; email: string };
    to_user: { id: number; name: string; email: string };
    approver: { name: string } | null;
}

interface Props {
    venture?: { id: number; title: string };
    transfers: { data: Transfer[] };
}

const props = defineProps<Props>();

const formatCurrency = (amount: number | null) => {
    if (amount === null) return '-';
    return new Intl.NumberFormat('en-ZM', { style: 'currency', currency: 'ZMW', minimumFractionDigits: 0 }).format(amount);
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-800', approved: 'bg-green-100 text-green-800',
        completed: 'bg-blue-100 text-blue-800', rejected: 'bg-red-100 text-red-800', cancelled: 'bg-gray-100 text-gray-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const approveTransfer = (id: number) => {
    if (!confirm('Approve this share transfer? This will update the cap table.')) return;
    router.post(route('admin.ventures.transfers.approve', id));
};

const rejectTransfer = (id: number) => {
    const notes = prompt('Rejection reason (optional):');
    router.post(route('admin.ventures.transfers.reject', id), { admin_notes: notes || '' });
};
</script>

<template>
    <Head title="Share Transfers" />
    <AdminLayout>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link v-if="venture" :href="route('admin.ventures.show', venture.id)" class="text-sm text-blue-600 hover:text-blue-800">&larr; Back to {{ venture.title }}</Link>
                    <h1 class="text-2xl font-bold text-gray-900">Share Transfers</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ venture ? `Transfers for ${venture.title}` : 'All share transfer requests' }}</p>
                </div>

                <div v-if="!transfers.data.length" class="rounded-lg bg-white p-12 text-center shadow">
                    <ClockIcon class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">No transfers</h3>
                    <p class="mt-1 text-sm text-gray-500">No share transfer requests found.</p>
                </div>

                <div v-else class="space-y-4">
                    <div v-for="transfer in transfers.data" :key="transfer.id" class="rounded-lg bg-white p-6 shadow">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span :class="getStatusColor(transfer.status)" class="rounded-full px-2.5 py-0.5 text-xs font-medium capitalize">{{ transfer.status }}</span>
                                </div>
                                <div class="mt-2 flex items-center gap-2 text-sm">
                                    <span class="font-medium text-gray-900">{{ transfer.from_user.name }}</span>
                                    <ArrowRightIcon class="h-4 w-4 text-gray-400" />
                                    <span class="font-medium text-gray-900">{{ transfer.to_user.name }}</span>
                                </div>
                                <div class="mt-1 text-lg font-bold text-gray-900">{{ transfer.shares.toLocaleString() }} shares</div>
                                <div v-if="transfer.total_value" class="text-sm text-gray-600">Value: {{ formatCurrency(transfer.total_value) }}</div>
                                <div v-if="transfer.venture" class="text-sm text-gray-500">Venture: {{ transfer.venture.title }}</div>
                                <div v-if="transfer.reason" class="mt-2 text-sm text-gray-600"><span class="font-medium">Reason:</span> {{ transfer.reason }}</div>
                            </div>
                            <div v-if="transfer.status === 'pending'" class="ml-4 flex gap-2">
                                <button @click="approveTransfer(transfer.id)" class="rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow hover:bg-green-500">
                                    <CheckCircleIcon class="inline h-4 w-4" /> Approve
                                </button>
                                <button @click="rejectTransfer(transfer.id)" class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow hover:bg-red-500">
                                    <XCircleIcon class="inline h-4 w-4" /> Reject
                                </button>
                            </div>
                        </div>
                        <div v-if="transfer.approver" class="mt-3 border-t pt-3 text-xs text-gray-500">
                            Processed by {{ transfer.approver.name }} on {{ new Date(transfer.approved_at || transfer.created_at).toLocaleDateString() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
