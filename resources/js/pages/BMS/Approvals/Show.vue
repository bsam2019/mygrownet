<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import BMSLayout from '@/Layouts/BMSLayout.vue';
import {
    CheckCircleIcon,
    XCircleIcon,
    ArrowLeftIcon,
    DocumentTextIcon,
    UserCircleIcon,
    CurrencyDollarIcon,
    CalendarIcon,
    ClockIcon,
} from '@heroicons/vue/24/outline';
import { toast } from '@/utils/bizboost-toast';

interface Step {
    id: number;
    step_level: number;
    approver_role: string;
    status: string;
    approved_at: string | null;
    comments: string | null;
    approver: {
        id: number;
        user: { name: string };
    } | null;
}

interface ApprovalRequest {
    id: number;
    request_type: string;
    amount: number;
    status: string;
    request_notes: string | null;
    submitted_at: string;
    approval_level: number;
    required_levels: number;
    requested_by: {
        id: number;
        user: { name: string; email: string };
    } | null;
    approvable: Record<string, any> | null;
    steps: Step[];
}

interface Props {
    approval: ApprovalRequest;
}

const props = defineProps<Props>();

const showRejectModal = ref(false);
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

const canAction = (step: Step) => {
    return step.status === 'pending'
        && props.approval.status === 'pending'
        && step.step_level === props.approval.approval_level;
};

const approveStep = (stepId: number) => {
    router.post(route('bms.approvals.approve', props.approval.id), {
        comments: null
    }, {
        preserveScroll: true,
        onSuccess: () => toast.success('Approved', 'Step approved successfully'),
        onError: () => toast.error('Failed', 'Could not approve step'),
    });
};

const openReject = () => {
    rejectReason.value = '';
    showRejectModal.value = true;
};

const confirmReject = () => {
    if (!rejectReason.value.trim()) {
        toast.warning('Reason required', 'Please provide a reason for rejection');
        return;
    }
    router.post(route('bms.approvals.reject', props.approval.id), {
        reason: rejectReason.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Rejected', 'Request has been rejected');
            showRejectModal.value = false;
        },
        onError: () => {
            toast.error('Failed', 'Could not reject request');
        }
    });
};

const goBack = () => {
    router.visit(route('bms.approvals.index'));
};
</script>

<template>
    <Head title="Approval Details" />

    <BMSLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <button @click="goBack" class="flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 mb-6">
                <ArrowLeftIcon class="h-4 w-4" />
                Back to Approvals
            </button>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">
                                {{ getTypeLabel(approval.request_type) }} Approval
                            </h1>
                            <p class="mt-1 text-sm text-gray-600">
                                Request #{{ approval.id }}
                            </p>
                        </div>
                        <span :class="[getStatusColor(approval.status), 'px-3 py-1.5 text-sm font-medium rounded-full']">
                            {{ approval.status.charAt(0).toUpperCase() + approval.status.slice(1) }}
                        </span>
                    </div>
                </div>

                <div class="px-6 py-5 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <DocumentTextIcon class="h-5 w-5 text-gray-400" />
                                <div>
                                    <p class="text-xs text-gray-500">Type</p>
                                    <p class="text-sm font-medium text-gray-900">{{ getTypeLabel(approval.request_type) }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <CurrencyDollarIcon class="h-5 w-5 text-gray-400" />
                                <div>
                                    <p class="text-xs text-gray-500">Amount</p>
                                    <p class="text-sm font-medium text-gray-900">K{{ Number(approval.amount).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <UserCircleIcon class="h-5 w-5 text-gray-400" />
                                <div>
                                    <p class="text-xs text-gray-500">Requested By</p>
                                    <p class="text-sm font-medium text-gray-900">{{ approval.requested_by?.user?.name || 'Unknown' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <CalendarIcon class="h-5 w-5 text-gray-400" />
                                <div>
                                    <p class="text-xs text-gray-500">Submitted</p>
                                    <p class="text-sm font-medium text-gray-900">{{ new Date(approval.submitted_at).toLocaleDateString() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="approval.request_notes" class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500 mb-1">Notes</p>
                        <p class="text-sm text-gray-900">{{ approval.request_notes }}</p>
                    </div>

                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Approval Progress</h2>
                        <p class="text-sm text-gray-600 mb-4">
                            Level {{ approval.approval_level }} of {{ approval.required_levels }}
                        </p>

                        <div class="space-y-4">
                            <div
                                v-for="step in approval.steps"
                                :key="step.id"
                                class="border rounded-lg p-4"
                                :class="{
                                    'border-green-300 bg-green-50': step.status === 'approved',
                                    'border-red-300 bg-red-50': step.status === 'rejected',
                                    'border-yellow-300 bg-yellow-50': step.status === 'pending' && step.step_level === approval.approval_level,
                                    'border-gray-200 bg-gray-50': step.status === 'pending' && step.step_level !== approval.approval_level,
                                }"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold"
                                            :class="{
                                                'bg-green-100 text-green-700': step.status === 'approved',
                                                'bg-red-100 text-red-700': step.status === 'rejected',
                                                'bg-yellow-100 text-yellow-700': step.status === 'pending',
                                                'bg-gray-100 text-gray-500': step.status === 'pending',
                                            }"
                                        >
                                            {{ step.step_level }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">
                                                Level {{ step.step_level }} - {{ step.approver_role.charAt(0).toUpperCase() + step.approver_role.slice(1) }}
                                            </p>
                                            <p v-if="step.approver?.user?.name" class="text-xs text-gray-500">
                                                Approved by: {{ step.approver.user.name }}
                                            </p>
                                            <p v-else class="text-xs text-gray-400">Awaiting approval</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full"
                                            :class="{
                                                'bg-green-100 text-green-800': step.status === 'approved',
                                                'bg-red-100 text-red-800': step.status === 'rejected',
                                                'bg-yellow-100 text-yellow-800': step.status === 'pending',
                                                'bg-gray-100 text-gray-800': step.status === 'pending',
                                            }"
                                        >
                                            {{ step.status }}
                                        </span>
                                        <div v-if="canAction(step)" class="flex gap-2 ml-2">
                                            <button
                                                @click="approveStep(step.id)"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-white bg-green-600 rounded-lg hover:bg-green-700"
                                            >
                                                <CheckCircleIcon class="h-4 w-4" />
                                                Approve
                                            </button>
                                            <button
                                                @click="openReject"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-white bg-red-600 rounded-lg hover:bg-red-700"
                                            >
                                                <XCircleIcon class="h-4 w-4" />
                                                Reject
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <p v-if="step.comments" class="mt-2 text-xs text-gray-600 ml-11">
                                    Comment: {{ step.comments }}
                                </p>
                                <p v-if="step.approved_at" class="mt-1 text-xs text-gray-400 ml-11">
                                    {{ new Date(step.approved_at).toLocaleString() }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BMSLayout>

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
