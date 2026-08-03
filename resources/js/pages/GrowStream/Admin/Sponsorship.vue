<template>
    <AppLayout title="Sponsorship - GrowStream Admin">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Sponsorship Fund</h1>
                <p class="mt-2 text-gray-600">Review and manage creator sponsorship proposals</p>
            </div>

            <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="rounded-lg bg-white p-6 shadow">
                    <p class="text-sm font-medium text-gray-600">Total Approved</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">K{{ stats.total_approved.toLocaleString(undefined, { minimumFractionDigits: 2 }) }}</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow">
                    <p class="text-sm font-medium text-gray-600">Total Disbursed</p>
                    <p class="mt-2 text-3xl font-bold text-green-600">K{{ stats.total_disbursed.toLocaleString(undefined, { minimumFractionDigits: 2 }) }}</p>
                </div>
            </div>

            <div v-if="grants.data.length === 0" class="rounded-lg border-2 border-dashed border-gray-300 py-16 text-center">
                <p class="text-lg font-medium text-gray-900">No sponsorship proposals</p>
                <p class="mt-1 text-sm text-gray-600">Creator submissions will appear here.</p>
            </div>

            <div v-else class="space-y-4">
                <div v-for="grant in grants.data" :key="grant.id" class="rounded-lg bg-white p-6 shadow">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ grant.title }}</h3>
                            <p class="text-sm text-gray-500">By {{ grant.creator?.user?.name || 'Unknown' }}</p>
                        </div>
                        <span
                            :class="{
                                'bg-amber-100 text-amber-800': grant.status === 'submitted',
                                'bg-blue-100 text-blue-800': grant.status === 'approved',
                                'bg-green-100 text-green-800': grant.status === 'disbursed' || grant.status === 'completed',
                                'bg-red-100 text-red-800': grant.status === 'rejected',
                            }"
                            class="rounded-full px-2 py-0.5 text-xs font-medium"
                        >
                            {{ grant.status }}
                        </span>
                    </div>
                    <p class="mt-2 text-sm text-gray-600">{{ grant.description }}</p>
                    <p class="mt-2 text-sm font-semibold text-gray-900">K{{ Number(grant.amount).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}</p>
                    <p v-if="grant.rejection_reason" class="mt-2 rounded bg-red-50 p-3 text-sm text-red-700">
                        Reason: {{ grant.rejection_reason }}
                    </p>

                    <div class="mt-4 flex items-center gap-2 border-t border-gray-100 pt-4">
                        <template v-if="grant.status === 'submitted'">
                            <button @click="approve(grant.id)" class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">
                                Approve
                            </button>
                            <form @submit.prevent="reject(grant.id)" class="flex flex-1 items-center gap-2">
                                <input
                                    v-model="reasons[grant.id]"
                                    type="text"
                                    placeholder="Rejection reason"
                                    class="flex-1 rounded-md border-gray-300 text-sm focus:border-red-500 focus:ring-red-500"
                                    required
                                />
                                <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                                    Reject
                                </button>
                            </form>
                        </template>
                        <template v-else-if="grant.status === 'approved'">
                            <button @click="disburse(grant.id)" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                Disburse Funds
                            </button>
                        </template>
                        <template v-else-if="grant.status === 'disbursed'">
                            <button @click="complete(grant.id)" class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-900">
                                Mark Complete
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

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
</script>
