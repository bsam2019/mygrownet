<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { 
    CheckCircleIcon, 
    XCircleIcon, 
    ClockIcon,
    DocumentTextIcon 
} from '@heroicons/vue/24/outline';
import { toast } from '@/utils/bizboost-toast';

interface Props {
    pendingApprovals: any[];
    allRequests: any;
}

const props = defineProps<Props>();

const showApproveConfirm = ref(false);
const showRejectModal = ref(false);
const pendingApprovalId = ref<number | null>(null);
const rejectReason = ref('');

const getStatusColor = (status: string) => {
    switch (status) {
        case 'pending': return 'bg-yellow-100 text-yellow-800';
        case 'approved': return 'bg-green-100 text-green-800';
        case 'rejected': return 'bg-red-100 text-red-800';
        case 'cancelled': return 'bg-gray-100 text-gray-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const getTypeLabel = (type: string) => {
    return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const viewDetails = (id: number) => {
    router.visit(route('cms.approvals.show', id));
};

const approve = (id: number) => {
    pendingApprovalId.value = id;
    showApproveConfirm.value = true;
};

const confirmApprove = () => {
    if (pendingApprovalId.value) {
        router.post(route('cms.approvals.approve', pendingApprovalId.value), {
            comments: null
        }, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Request approved', 'The approval request has been approved');
                showApproveConfirm.value = false;
            },
            onError: () => {
                toast.error('Approval failed', 'Could not approve request');
            }
        });
    }
};

const reject = (id: number) => {
    pendingApprovalId.value = id;
    rejectReason.value = '';
    showRejectModal.value = true;
};

const confirmReject = () => {
    if (pendingApprovalId.value && rejectReason.value.trim()) {
        router.post(route('cms.approvals.reject', pendingApprovalId.value), {
            reason: rejectReason.value
        }, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Request rejected', 'The approval request has been rejected');
                showRejectModal.value = false;
            },
            onError: () => {
                toast.error('Rejection failed', 'Could not reject request');
            }
        });
    } else {
        toast.warning('Reason required', 'Please provide a reason for rejection');
    }
};
</script>

<template>
    <Head title="Approvals" />

    <CMSLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Approval Requests</h1>
                <p class="mt-2 text-sm text-gray-600">
                    Review and manage approval requests
                </p>
            </div>

            <!-- Pending Approvals -->
            <div v-if="pendingApprovals.length > 0" class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Pending Your Approval</h2>
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <ul class="divide-y divide-gray-200">
                        <li v-for="approval in pendingApprovals" :key="approval.id" class="p-6 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <DocumentTextIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                                        <h3 class="text-lg font-medium text-gray-900">
                                            {{ getTypeLabel(approval.request_type) }}
                                        </h3>
                                        <span :class="[getStatusColor(approval.status), 'px-2 py-1 text-xs font-medium rounded-full']">
                                            {{ approval.status }}
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-600 space-y-1">
                                        <p>Amount: <span class="font-medium">K{{ Number(approval.amount).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</span></p>
                                        <p>Requested by: <span class="font-medium">{{ approval.requested_by?.user?.name }}</span></p>
                                        <p>Submitted: {{ new Date(approval.submitted_at).toLocaleDateString() }}</p>
                                        <p>Approval Level: {{ approval.approval_level }} of {{ approval.required_levels }}</p>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button
                                        @click="viewDetails(approval.id)"
                                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                                    >
                                        View Details
                                    </button>
                                    <button
                                        @click="approve(approval.id)"
                                        class="inline-flex items-center gap-2 px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-green-600 hover:bg-green-700"
                                    >
                                        <CheckCircleIcon class="h-5 w-5" aria-hidden="true" />
                                        Approve
                                    </button>
                                    <button
                                        @click="reject(approval.id)"
                                        class="inline-flex items-center gap-2 px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-red-700"
                                    >
                                        <XCircleIcon class="h-5 w-5" aria-hidden="true" />
                                        Reject
                                    </button>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <div v-else class="mb-8 bg-white shadow-sm rounded-lg p-8 text-center">
                <ClockIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                <h3 class="mt-2 text-sm font-medium text-gray-900">No pending approvals</h3>
                <p class="mt-1 text-sm text-gray-500">You don't have any approval requests waiting for your action.</p>
            </div>

            <!-- All Requests -->
            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-4">All Approval Requests</h2>
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested By</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="request in allRequests.data" :key="request.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ getTypeLabel(request.request_type) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    K{{ Number(request.amount).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ request.requested_by?.user?.name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="[getStatusColor(request.status), 'px-2 py-1 text-xs font-medium rounded-full']">
                                        {{ request.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ new Date(request.submitted_at).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button
                                        @click="viewDetails(request.id)"
                                        class="text-blue-600 hover:text-blue-900"
                                    >
                                        View
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </CMSLayout>

    <!-- Approve Confirmation Modal -->
    <div v-if="showApproveConfirm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="showApproveConfirm = false">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-xl">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Approve Request?</h3>
            <p class="text-sm text-gray-600 mb-6">This will approve the request and allow it to proceed.</p>
            <div class="flex gap-3 justify-end">
                <button @click="showApproveConfirm = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button @click="confirmApprove" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition">
                    Approve
                </button>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div v-if="showRejectModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="showRejectModal = false">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-xl">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Reject Request</h3>
            <p class="text-sm text-gray-600 mb-4">Please provide a reason for rejecting this request:</p>
            <textarea
                v-model="rejectReason"
                rows="4"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 mb-6"
                placeholder="Enter rejection reason..."
            ></textarea>
            <div class="flex gap-3 justify-end">
                <button @click="showRejectModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button @click="confirmReject" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                    Reject Request
                </button>
            </div>
        </div>
    </div>
</template>
