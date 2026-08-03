<template>
    <AdminLayout title="Sponsorship - GrowStream Admin">
        <div class="mx-auto max-w-7xl">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-[var(--gs-text)]">Sponsorship Fund</h1>
                <p class="mt-2 text-[var(--gs-muted)]">Review and manage creator sponsorship proposals</p>
            </div>

            <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="gs-card p-6">
                    <p class="text-sm font-medium text-[var(--gs-muted)]">Total Approved</p>
                    <p class="mt-2 text-3xl font-bold text-[var(--gs-text)]">K{{ stats.total_approved.toLocaleString(undefined, { minimumFractionDigits: 2 }) }}</p>
                </div>
                <div class="gs-card p-6">
                    <p class="text-sm font-medium text-[var(--gs-muted)]">Total Disbursed</p>
                    <p class="mt-2 text-3xl font-bold text-[var(--gs-primary)]">K{{ stats.total_disbursed.toLocaleString(undefined, { minimumFractionDigits: 2 }) }}</p>
                </div>
            </div>

            <div
                v-if="grants.data.length === 0"
                class="gs-card flex flex-col items-center border-2 border-dashed border-[var(--gs-border)] py-16 text-center"
            >
                <p class="text-lg font-medium text-[var(--gs-text)]">No sponsorship proposals</p>
                <p class="mt-1 text-sm text-[var(--gs-muted)]">Creator submissions will appear here.</p>
            </div>

            <div v-else class="space-y-4">
                <div v-for="grant in grants.data" :key="grant.id" class="gs-card p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-[var(--gs-text)]">{{ grant.title }}</h3>
                            <p class="text-sm text-[var(--gs-muted)]">By {{ grant.creator?.user?.name || 'Unknown' }}</p>
                        </div>
                        <span
                            :class="statusBadge(grant.status)"
                            class="gs-chip"
                        >
                            {{ grant.status }}
                        </span>
                    </div>
                    <p class="mt-2 text-sm text-[var(--gs-muted)]">{{ grant.description }}</p>
                    <p class="mt-2 text-sm font-semibold text-[var(--gs-text)]">K{{ Number(grant.amount).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}</p>
                    <p v-if="grant.rejection_reason" class="mt-2 rounded bg-red-500/10 p-3 text-sm text-red-400">
                        Reason: {{ grant.rejection_reason }}
                    </p>

                    <div class="mt-4 flex items-center gap-2 border-t border-[var(--gs-border)] pt-4">
                        <template v-if="grant.status === 'submitted'">
                            <button @click="approve(grant.id)" class="gs-btn gs-btn-primary">
                                Approve
                            </button>
                            <form @submit.prevent="reject(grant.id)" class="flex flex-1 items-center gap-2">
                                <input
                                    v-model="reasons[grant.id]"
                                    type="text"
                                    placeholder="Rejection reason"
                                    class="gs-input flex-1"
                                    required
                                />
                                <button type="submit" class="gs-btn bg-red-500/15 text-red-400">
                                    Reject
                                </button>
                            </form>
                        </template>
                        <template v-else-if="grant.status === 'approved'">
                            <button @click="disburse(grant.id)" class="gs-btn gs-btn-accent">
                                Disburse Funds
                            </button>
                        </template>
                        <template v-else-if="grant.status === 'disbursed'">
                            <button @click="complete(grant.id)" class="gs-btn gs-btn-outline">
                                Mark Complete
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

interface Grant {
    id: number;
    title: string;
    description: string;
    amount: number;
    status: string;
    rejection_reason?: string | null;
    creator?: { user?: { name?: string } } | null;
}

interface Paginated<T> {
    data: T[];
    total: number;
}

interface Props {
    grants: Paginated<Grant>;
    stats: { total_approved: number; total_disbursed: number };
    filters: Record<string, any>;
}

defineProps<Props>();

const reasons = reactive<Record<number, string>>({});

const approve = (id: number) => router.post(route('growstream.admin.sponsorship.approve', id));
const disburse = (id: number) => router.post(route('growstream.admin.sponsorship.disburse', id));
const complete = (id: number) => router.post(route('growstream.admin.sponsorship.complete', id));

const reject = (id: number) => {
    router.post(route('growstream.admin.sponsorship.reject', id), { reason: reasons[id] });
};

const statusBadge = (status: string): string => {
    switch (status) {
        case 'submitted':
            return 'gs-chip-accent';
        case 'approved':
            return 'bg-blue-500/15 text-blue-400';
        case 'disbursed':
        case 'completed':
            return 'gs-chip-primary';
        case 'rejected':
            return 'bg-red-500/15 text-red-400';
        default:
            return 'gs-chip-primary';
    }
};
</script>
