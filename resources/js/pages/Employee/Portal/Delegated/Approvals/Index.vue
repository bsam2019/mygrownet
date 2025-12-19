<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/layouts/EmployeePortalLayout.vue';
import { ref } from 'vue';
import { ClockIcon, CheckCircleIcon, XCircleIcon, ShieldCheckIcon } from '@heroicons/vue/24/outline';

interface ApprovalRequest {
    id: number;
    employee: { id: number; first_name: string; last_name: string } | null;
    action_type: string;
    resource_type: string;
    resource_id: number;
    action_data: any;
    status: string;
    reviewer: { id: number; name: string } | null;
    reviewed_at: string | null;
    review_notes: string | null;
    created_at: string;
}

interface Props {
    employee: any;
    pendingApprovals: { data: ApprovalRequest[] };
    toReview: ApprovalRequest[];
}

const props = defineProps<Props>();
const showReviewModal = ref(false);
const selectedApproval = ref<ApprovalRequest | null>(null);

const form = useForm({ action: 'approve' as 'approve' | 'reject', notes: '' });

const openReview = (approval: ApprovalRequest) => {
    selectedApproval.value = approval;
    form.reset();
    showReviewModal.value = true;
};

const submitReview = () => {
    if (!selectedApproval.value) return;
    form.post(route('employee.portal.delegated.approvals.review', selectedApproval.value.id), {
        onSuccess: () => { showReviewModal.value = false; selectedApproval.value = null; },
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
    <Head title="Delegated - Approval Queue" />
    <EmployeePortalLayout>
        <template #header>Approval Queue</template>
        
        <div class="space-y-6">
            <!-- To Review Section (for managers) -->
            <div v-if="toReview.length > 0" class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="p-4 border-b border-gray-100 bg-amber-50">
                    <h2 class="font-semibold text-amber-800 flex items-center gap-2">
                        <ClockIcon class="h-5 w-5" />
                        Pending Your Review ({{ toReview.length }})
                    </h2>
                </div>
                <div class="divide-y divide-gray-100">
                    <div v-for="approval in toReview" :key="approval.id" class="p-4 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900">{{ formatActionType(approval.action_type) }}</p>
                                <p class="text-sm text-gray-500">
                                    Requested by {{ approval.employee?.first_name }} {{ approval.employee?.last_name }}
                                </p>
                                <p class="text-xs text-gray-400 mt-1">{{ new Date(approval.created_at).toLocaleString() }}</p>
                                <div v-if="approval.action_data" class="mt-2 text-sm">
                                    <span v-if="approval.action_data.amount" class="text-blue-600 font-medium">
                                        K{{ Number(approval.action_data.amount).toLocaleString() }}
                                    </span>
                                    <span v-if="approval.action_data.user_name" class="text-gray-500 ml-2">
                                        for {{ approval.action_data.user_name }}
                                    </span>
                                </div>
                            </div>
                            <button @click="openReview(approval)" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                                Review
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- My Requests Section -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="p-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                        <ShieldCheckIcon class="h-5 w-5 text-gray-400" />
                        My Approval Requests
                    </h2>
                </div>
                <div class="divide-y divide-gray-100">
                    <div v-for="approval in pendingApprovals.data" :key="approval.id" class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <p class="font-medium text-gray-900">{{ formatActionType(approval.action_type) }}</p>
                                    <span :class="['px-2 py-0.5 text-xs font-medium rounded-full capitalize', getStatusClass(approval.status)]">
                                        {{ approval.status }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 mt-1">
                                    {{ approval.resource_type.replace(/_/g, ' ') }} #{{ approval.resource_id }}
                                </p>
                                <p class="text-xs text-gray-400 mt-1">Submitted {{ new Date(approval.created_at).toLocaleString() }}</p>
                                <div v-if="approval.action_data" class="mt-2 text-sm">
                                    <span v-if="approval.action_data.amount" class="text-blue-600 font-medium">
                                        K{{ Number(approval.action_data.amount).toLocaleString() }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div v-if="approval.status === 'pending'" class="flex items-center gap-1 text-amber-600">
                                    <ClockIcon class="h-4 w-4" />
                                    <span class="text-sm">Awaiting approval</span>
                                </div>
                                <div v-else-if="approval.status === 'approved'" class="flex items-center gap-1 text-green-600">
                                    <CheckCircleIcon class="h-4 w-4" />
                                    <span class="text-sm">Approved</span>
                                </div>
                                <div v-else class="flex items-center gap-1 text-red-600">
                                    <XCircleIcon class="h-4 w-4" />
                                    <span class="text-sm">Rejected</span>
                                </div>
                                <p v-if="approval.reviewed_at" class="text-xs text-gray-400 mt-1">
                                    {{ new Date(approval.reviewed_at).toLocaleDateString() }}
                                </p>
                            </div>
                        </div>
                        <div v-if="approval.review_notes" class="mt-3 p-3 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-600"><strong>Notes:</strong> {{ approval.review_notes }}</p>
                        </div>
                    </div>
                    <div v-if="pendingApprovals.data.length === 0" class="p-8 text-center text-gray-500">
                        No approval requests
                    </div>
                </div>
            </div>
        </div>

        <!-- Review Modal -->
        <Teleport to="body">
            <div v-if="showReviewModal && selectedApproval" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/50" @click="showReviewModal = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Review Request</h2>
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                        <p class="font-medium text-gray-900">{{ formatActionType(selectedApproval.action_type) }}</p>
                        <p class="text-sm text-gray-500">
                            By {{ selectedApproval.employee?.first_name }} {{ selectedApproval.employee?.last_name }}
                        </p>
                        <div v-if="selectedApproval.action_data?.amount" class="mt-2">
                            <span class="text-blue-600 font-medium">K{{ Number(selectedApproval.action_data.amount).toLocaleString() }}</span>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="flex gap-3">
                            <button @click="form.action = 'approve'" :class="['flex-1 p-3 rounded-lg border-2 flex items-center justify-center gap-2', form.action === 'approve' ? 'border-green-500 bg-green-50' : 'border-gray-200']">
                                <CheckCircleIcon class="h-5 w-5 text-green-600" />Approve
                            </button>
                            <button @click="form.action = 'reject'" :class="['flex-1 p-3 rounded-lg border-2 flex items-center justify-center gap-2', form.action === 'reject' ? 'border-red-500 bg-red-50' : 'border-gray-200']">
                                <XCircleIcon class="h-5 w-5 text-red-600" />Reject
                            </button>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea v-model="form.notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2" :placeholder="form.action === 'reject' ? 'Reason for rejection...' : 'Optional notes...'"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button @click="showReviewModal = false" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">Cancel</button>
                        <button @click="submitReview" :disabled="form.processing" :class="['px-4 py-2 rounded-lg text-white', form.action === 'approve' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700']">
                            {{ form.action === 'approve' ? 'Approve' : 'Reject' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </EmployeePortalLayout>
</template>
