<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { useToast } from '@/composables/useToast';

const page = usePage();
const { toast } = useToast();

interface ApprovalLogEntry {
    step: number;
    approver_id: number;
    action: string;
    comment: string | null;
    timestamp: string;
}

interface PendingApproval {
    id: number;
    business_id: number;
    workflow_template_id: number;
    entity_type: string;
    entity_id: number;
    status: string;
    current_step: number;
    approval_log: ApprovalLogEntry[] | null;
    requested_by: number;
    requester_name: string;
    template_name: string;
    steps_config: { step_order: number; role: string; approver_id: number | null; action: string }[];
    created_at: string;
}

interface WorkflowTemplate {
    id: number;
    business_id: number;
    name: string;
    description: string | null;
    entity_type: string;
    steps: string;
    is_active: boolean;
    created_at: string;
}

const props = defineProps<{
    pendingApprovals: PendingApproval[];
    templates: WorkflowTemplate[];
}>();

const commentInputs = ref<Record<number, string>>({});
const showRejectModal = ref<number | null>(null);
const rejectComment = ref('');

const formatDate = (date: string) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-ZM', {
        year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit',
    });
};

const getStepLabel = (approval: PendingApproval) => {
    const step = approval.steps_config?.find(s => s.step_order === approval.current_step);
    if (step) return `${step.role}${step.approver_id ? ' (assigned)' : ''}`;
    return `Step ${approval.current_step + 1}`;
};

const handleApprove = (id: number) => {
    router.post(route('growfinance.workflow.approve', { instance: id }), {
        comment: commentInputs.value[id] || null,
    }, {
        preserveScroll: true,
        onSuccess: () => { toast.success('Approved successfully.'); delete commentInputs.value[id]; },
        onError: () => { toast.error('Failed to approve.'); },
    });
};

const openRejectModal = (id: number) => {
    showRejectModal.value = id;
    rejectComment.value = '';
};

const handleReject = () => {
    if (showRejectModal.value === null || !rejectComment.value.trim()) return;
    const id = showRejectModal.value;
    router.post(route('growfinance.workflow.reject', { instance: id }), {
        comment: rejectComment.value,
    }, {
        preserveScroll: true,
        onSuccess: () => { toast.info('Rejected.'); showRejectModal.value = null; },
        onError: () => { toast.error('Failed to reject.'); },
    });
};

const entityLabel = (type: string) => {
    const labels: Record<string, string> = { journal: 'Journal Entry', invoice: 'Invoice', expense: 'Expense' };
    return labels[type] || type;
};
</script>

<template>
    <GrowFinanceLayout>
        <div class="px-6 py-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Approvals</h1>
                    <p class="text-sm text-gray-500 mt-1">Review and approve pending workflow steps</p>
                </div>
                <Link :href="route('growfinance.workflow.templates')" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-emerald-700 bg-emerald-50 rounded-lg hover:bg-emerald-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                    Manage Templates
                </Link>
            </div>

            <!-- Pending Approvals -->
            <div class="space-y-4">
                <div v-if="props.pendingApprovals.length === 0" class="text-center py-12 bg-white rounded-xl border border-gray-200">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <h3 class="mt-3 text-sm font-medium text-gray-900">No pending approvals</h3>
                    <p class="mt-1 text-sm text-gray-500">All workflow steps have been processed.</p>
                </div>

                <div v-for="approval in props.pendingApprovals" :key="approval.id" class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
                    <div class="p-5">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>
                                    <span class="text-sm text-gray-500">{{ entityLabel(approval.entity_type) }} #{{ approval.entity_id }}</span>
                                    <span class="text-xs text-gray-400">{{ formatDate(approval.created_at) }}</span>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ approval.template_name }}</h3>
                                <div class="mt-2 flex items-center gap-4 text-sm text-gray-500">
                                    <span>Requested by: <strong>{{ approval.requester_name }}</strong></span>
                                    <span>Current step: <strong>{{ getStepLabel(approval) }}</strong></span>
                                    <span>Step {{ approval.current_step + 1 }} of {{ approval.steps_config?.length }}</span>
                                </div>

                                <!-- Approval log -->
                                <div v-if="approval.approval_log && approval.approval_log.length > 0" class="mt-3 space-y-1">
                                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">History</p>
                                    <div v-for="(log, i) in approval.approval_log" :key="i" class="flex items-center gap-2 text-xs text-gray-500">
                                        <span v-if="log.action === 'approve'" class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                        <span v-else class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                                        <span>Step {{ log.step + 1 }} — {{ log.action === 'approve' ? 'Approved' : 'Rejected' }} by {{ log.approver_id }}</span>
                                        <span v-if="log.comment">— "{{ log.comment }}"</span>
                                        <span class="text-gray-400">{{ formatDate(log.timestamp) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action buttons -->
                        <div class="mt-4 flex items-center gap-3 pt-3 border-t border-gray-100">
                            <input v-model="commentInputs[approval.id]" placeholder="Optional comment..." class="flex-1 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" />
                            <button @click="handleApprove(approval.id)" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                Approve
                            </button>
                            <button @click="openRejectModal(approval.id)" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-red-700 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                Reject
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div v-if="showRejectModal !== null" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="showRejectModal = null">
            <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Reject Step</h3>
                <p class="text-sm text-gray-500 mb-4">Provide a reason for rejection.</p>
                <textarea v-model="rejectComment" rows="3" placeholder="Reason for rejection..." class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent resize-none"></textarea>
                <div class="mt-4 flex justify-end gap-3">
                    <button @click="showRejectModal = null" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Cancel</button>
                    <button @click="handleReject" :disabled="!rejectComment.trim()" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50 transition-colors">Reject</button>
                </div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>
