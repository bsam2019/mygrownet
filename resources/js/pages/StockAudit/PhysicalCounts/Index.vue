<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import StockAuditLayout from '@/layouts/StockAuditLayout.vue';
import LoadingSkeleton from '@/components/StockAudit/LoadingSkeleton.vue';
import Pagination from '@/components/StockAudit/Pagination.vue';
import { ref } from 'vue';
import { PlusIcon } from '@heroicons/vue/24/outline';

interface CountItem {
    id: number;
    sa_item_id: number;
    system_quantity: number;
    physical_quantity: number;
    variance: number;
}

interface PhysicalCount {
    id: number;
    title: string;
    count_date: string;
    status: string;
    notes: string | null;
    items: CountItem[];
    created_at: string;
}

interface Props {
    counts: {
        data: PhysicalCount[];
        links: { url: string | null; label: string; active: boolean }[];
        meta: { current_page: number; last_page: number; total: number; from: number; to: number };
    };
}

defineProps<Props>();

const showCreateForm = ref(false);
const createForm = ref({ title: '', count_date: new Date().toISOString().slice(0, 10), notes: '' });
const errors = ref<Record<string, string>>({});

const statusColors: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800',
    in_progress: 'bg-yellow-100 text-yellow-800',
    completed: 'bg-green-100 text-green-800',
};

const createCount = () => {
    router.post(route('stockflow.sub.physical-counts.store'), createForm.value, {
        onSuccess: () => { showCreateForm.value = false; },
        onError: (err) => { errors.value = err; },
    });
};
</script>

<template>
    <StockAuditLayout title="Physical Counts">
        <Head title="Physical Counts - StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">Physical Counts</h1>
                    <button @click="showCreateForm = !showCreateForm" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                        <PlusIcon class="h-5 w-5" aria-hidden="true" />
                        New Count
                    </button>
                </div>

                <div v-if="showCreateForm" class="mb-6 rounded-xl bg-white p-6 shadow-sm border border-emerald-200">
                    <h2 class="text-lg font-semibold text-gray-900">Start New Physical Count</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Title *</label>
                            <input v-model="createForm.title" type="text" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                            <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Count Date *</label>
                            <input v-model="createForm.count_date" type="date" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                            <p v-if="errors.count_date" class="mt-1 text-sm text-red-600">{{ errors.count_date }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Notes</label>
                            <input v-model="createForm.notes" type="text" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                    </div>
                    <div class="mt-4 flex gap-3">
                        <button @click="createCount" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Create Count</button>
                        <button @click="showCreateForm = false" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    </div>
                </div>

                <LoadingSkeleton v-if="!counts.data?.length" type="table" />
                <template v-else>
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Items</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="count in counts.data" :key="count.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ count.title }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ count.count_date }}</td>
                                    <td class="px-6 py-4">
                                        <span :class="[statusColors[count.status] || 'bg-gray-100 text-gray-800', 'inline-flex rounded-full px-2 py-1 text-xs font-medium capitalize']">
                                            {{ count.status.replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm text-gray-700">{{ count.items?.length || 0 }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <Link :href="route('stockflow.sub.physical-counts.show', count.id)" class="text-sm text-emerald-600 hover:text-emerald-700">View</Link>
                                    </td>
                                </tr>
                                <tr v-if="!counts.data?.length">
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">No physical counts yet</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination :links="counts.links" :meta="counts.meta" />
                </template>
            </div>
        </div>
    </StockAuditLayout>
</template>
