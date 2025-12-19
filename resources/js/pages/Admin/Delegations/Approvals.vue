<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ref } from 'vue';
import { ClockIcon, CheckCircleIcon, XCircleIcon } from '@heroicons/vue/24/outline';

interface ApprovalRequest {
    id: number;
    employee: { id: number; first_name: string; last_name: string; full_name: string } | null;
    delegation: { permission_key: string } | null;
    reviewer: { id: number; name: string } | null;
    action_type: string;
    resource_type: string;
    resource_id: number;
    action_data: any;
    status: string;
    review_notes: string | null;
    reviewed_at: string | null;
    created_at: string;
}

interface Props {
    requests: { data: ApprovalRequest[]; links: any; meta: any };
    filters: { status?: string };
}

const props = defineProps<Props>();
const showModal = ref(false);
const selectedRequest = ref<ApprovalRequest | null>(null);
const modalAction = ref<'approve' | 'reject'>('approve');

const form = useForm({ notes: '' });

const openModal = (request: ApprovalRequest, action: 'approve' | 'reject') => {
    selectedRequest.value = request;
    modalAction.value = action;
    form.reset();
    showModal.value = true;
};

const submitAction = () => {
    if (!selectedRequest.value) return;
    const routeName = modalAction.value === 'approve' ? 'admin.delegations.approvals.approve' : 'admin.delegations.approvals.reject';
    const data = modalAction.value === 'approve' ? { notes: form.notes } : { reason: form.notes };
    
    form.transform(() => data).post(route(routeName, selectedRequest.value.id), {
        onSuccess: () => { showModal.value = false; selectedRequest.value = null; },
    });
};

const getStatusClass = (s: string) => ({
    'pending': 'bg-amber-100 text-amber-700',
    'approved': 'bg-green-100 text-green-700',
    'rejected': 'bg-red-100 text-red-700',
}[s] || 'bg-gray-100 text-gray-700');

const formatActionType = (type: string) => type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
</script>

<template>
    <Head title="Delegation Approvals" />
    <AdminLayout :breadcrumbs="[{ title: 'Delegations', href: route('admin.delegations.index') }, { title: 'Approvals' }]">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Approval Requests</h1>
                    <p class="text-gray-500">Review pending delegation action requests</p>
                </div>
            </div>

            <!-- Requests Table -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Requested</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="request in requests.data" :key="request.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-gray-900">{{ request.employee?.full_name || 'N/A' }}</p>
                                <p class="text-xs text-gray-500 font-mono">{{ request.delegation?.permission_key }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ formatActionType(request.action_type) }}</td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-600">{{ request.resource_type }} #{{ request.resource_id }}</p>
                                <p v-if="request.action_data?.amount" class="text-sm text-blue-600 font-medium">
                                    K{{ Number(request.action_data.amount).toLocaleString() }}
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="['px-2 py-1 text-xs font-medium rounded-full capitalize', getStatusClass(request.status)]">
                                    {{ request.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ new Date(request.created_at).toLocaleString() }}</td>
                            <td class="px-6 py-4 text-right">
                                <div v-if="request.status === 'pending'" class="flex gap-2 justify-end">
                                    <button @click="openModal(request, 'approve')" class="px-3 py-1 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700">
                                        Approve
                                    </button>
                                    <button @click="openModal(request, 'reject')" class="px-3 py-1 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700">
                                        Reject
                                    </button>
                                </div>
                                <div v-else class="text-sm text-gray-500">
                                    {{ request.reviewer?.name }} • {{ request.reviewed_at ? new Date(request.reviewed_at).toLocaleDateString() : '' }}
                                </div>
                            </td>
                        </tr>
                        <tr v-if="requests.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">No approval requests</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal -->
        <Teleport to="body">
            <div v-if="showModal && selectedRequest" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/50" @click="showModal = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">
                        {{ modalAction === 'approve' ? 'Approve' : 'Reject' }} Request
                    </h2>
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                        <p class="font-medium text-gray-900">{{ formatActionType(selectedRequest.action_type) }}</p>
                        <p class="text-sm text-gray-500">By {{ selectedRequest.employee?.full_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ modalAction === 'approve' ? 'Notes (optional)' : 'Reason for rejection' }}
                        </label>
                        <textarea v-model="form.notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2" :required="modalAction === 'reject'"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button @click="showModal = false" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">Cancel</button>
                        <button @click="submitAction" :disabled="form.processing || (modalAction === 'reject' && !form.notes)" :class="['px-4 py-2 rounded-lg text-white disabled:opacity-50', modalAction === 'approve' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700']">
                            {{ modalAction === 'approve' ? 'Approve' : 'Reject' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AdminLayout>
</template>
