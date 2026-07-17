<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { useNotifications } from '@/composables/useNotifications';
import { useStockflowRoute } from '@/composables/useStockflowRoute';

const props = defineProps<{ requisitions: any[] }>();
const { success } = useNotifications();
const { sf } = useStockflowRoute();

function approve(id: number) {
    router.post(sf('requisitions.approve', { id }), {}, { preserveState: true, onSuccess: () => success('Approved.') });
}
function reject(id: number) {
    router.post(sf('requisitions.reject', { id }), {}, { preserveState: true, onSuccess: () => success('Rejected.') });
}
function remove(id: number) {
    if (confirm('Delete?')) router.delete(sf('requisitions.destroy', { id }), { preserveState: true, onSuccess: () => success('Deleted.') });
}
const badgeClass = (s: string) => s === 'approved' ? 'bg-green-100 text-green-800' : s === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800';
</script>

<template>
    <Head title="Purchase Requisitions" />
    <StockFlowLayout title="Purchase Requisitions">
        <div class="max-w-5xl mx-auto py-6 px-4">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Purchase Requisitions</h1>
                <Link :href="sf('requisitions.index') + '?create=1'" class="px-4 py-2 bg-emerald-600 text-white rounded">New Requisition</Link>
            </div>
            <div class="bg-white rounded-lg shadow">
                <table class="w-full">
                    <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">#</th><th class="px-4 py-3 text-left">Requested</th><th class="px-4 py-3 text-left">Date Required</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3"></th></tr></thead>
                    <tbody>
                        <tr v-for="r in requisitions" :key="r.id" class="border-t">
                            <td class="px-4 py-3"><Link :href="sf('requisitions.show', { id: r.id })" class="text-blue-600 hover:underline">{{ r.requisition_number }}</Link></td>
                            <td class="px-4 py-3">{{ r.requested_by }}</td>
                            <td class="px-4 py-3">{{ r.date_required }}</td>
                            <td class="px-4 py-3"><span :class="badgeClass(r.status)" class="px-2 py-1 rounded text-xs font-medium capitalize">{{ r.status }}</span></td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <button v-if="r.status === 'pending'" @click="approve(r.id)" class="text-green-600 hover:underline">Approve</button>
                                <button v-if="r.status === 'pending'" @click="reject(r.id)" class="text-red-600 hover:underline">Reject</button>
                                <button @click="remove(r.id)" class="text-gray-600 hover:underline">Delete</button>
                            </td>
                        </tr>
                        <tr v-if="!requisitions.length"><td colspan="5" class="px-4 py-8 text-center text-gray-500">No requisitions.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </StockFlowLayout>
</template>
