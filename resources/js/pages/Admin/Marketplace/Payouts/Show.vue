<script setup lang="ts">
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import MarketplaceAdminLayout from '@/layouts/MarketplaceAdminLayout.vue';
import { 
    ArrowLeftIcon,
    CheckCircleIcon,
    XCircleIcon,
    ClockIcon,
    ArrowPathIcon,
} from '@heroicons/vue/24/outline';

interface Payout {
    id: number;
    reference: string;
    amount: number;
    commission_deducted: number;
    net_amount: number;
    payout_method: string;
    account_number: string;
    account_name: string;
    bank_name?: string;
    status: string;
    status_label: string;
    status_color: string;
    formatted_amount: string;
    formatted_net_amount: string;
    seller_notes?: string;
    admin_notes?: string;
    rejection_reason?: string;
    transaction_reference?: string;
    created_at: string;
    approved_at?: string;
    processed_at?: string;
    seller: {
        id: number;
        business_name: string;
        trust_level: string;
        user: {
            name: string;
            email: string;
        };
    };
    approved_by?: {
        name: string;
    };
    processed_by?: {
        name: string;
    };
}

interface Props {
    payout: Payout;
}

const props = defineProps<Props>();

const showApproveModal = ref(false);
const showRejectModal = ref(false);
const showProcessModal = ref(false);
const showCompleteModal = ref(false);
const showFailModal = ref(false);

const approveForm = useForm({
    notes: '',
});

const rejectForm = useForm({
    reason: '',
});

const completeForm = useForm({
    transaction_reference: '',
    notes: '',
});

const failForm = useForm({
    reason: '',
});

const approve = () => {
    approveForm.post(route('admin.marketplace.payouts.approve', props.payout.id), {
        onSuccess: () => {
            showApproveModal.value = false;
            approveForm.reset();
        },
    });
};

const reject = () => {
    rejectForm.post(route('admin.marketplace.payouts.reject', props.payout.id), {
        onSuccess: () => {
            showRejectModal.value = false;
            rejectForm.reset();
        },
    });
};

const markAsProcessing = () => {
    useForm({}).post(route('admin.marketplace.payouts.process', props.payout.id), {
        onSuccess: () => {
            showProcessModal.value = false;
        },
    });
};

const complete = () => {
    completeForm.post(route('admin.marketplace.payouts.complete', props.payout.id), {
        onSuccess: () => {
            showCompleteModal.value = false;
            completeForm.reset();
        },
    });
};

const markAsFailed = () => {
    failForm.post(route('admin.marketplace.payouts.fail', props.payout.id), {
        onSuccess: () => {
            showFailModal.value = false;
            failForm.reset();
        },
    });
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'completed': return CheckCircleIcon;
        case 'rejected':
        case 'failed': return XCircleIcon;
        case 'processing': return ArrowPathIcon;
        default: return ClockIcon;
    }
};

const getMethodLabel = (method: string) => {
    const labels: Record<string, string> = {
        momo: 'MTN Mobile Money',
        airtel: 'Airtel Money',
        bank: 'Bank Transfer',
    };
    return labels[method] || method.toUpperCase();
};
</script>

<template>
    <MarketplaceAdminLayout>
        <div class="max-w-4xl mx-auto px-4 py-8">
            <!-- Header -->
            <div class="flex items-center gap-4 mb-6">
                <Link :href="route('admin.marketplace.payouts.index')" class="p-2 hover:bg-gray-100 rounded-lg">
                    <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
                </Link>
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-gray-900">Payout Details</h1>
                    <p class="text-gray-500">Reference: {{ payout.reference }}</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div v-if="payout.status === 'pending' || payout.status === 'approved' || payout.status === 'processing'" class="flex flex-wrap gap-3 mb-6">
                <button 
                    v-if="payout.status === 'pending'"
                    @click="showApproveModal = true"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium"
                >
                    Approve Payout
                </button>
                <button 
                    v-if="payout.status === 'pending' || payout.status === 'approved'"
                    @click="showRejectModal = true"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium"
                >
                    Reject Payout
                </button>
                <button 
                    v-if="payout.status === 'approved'"
                    @click="showProcessModal = true"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium"
                >
                    Mark as Processing
                </button>
                <button 
                    v-if="payout.status === 'processing'"
                    @click="showCompleteModal = true"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium"
                >
                    Mark as Completed
                </button>
                <button 
                    v-if="payout.status === 'processing'"
                    @click="showFailModal = true"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium"
                >
                    Mark as Failed
                </button>
            </div>

            <!-- Status Card -->
            <div :class="[
                'rounded-xl p-6 mb-6',
                payout.status_color === 'green' ? 'bg-green-50 border border-green-200' :
                payout.status_color === 'red' ? 'bg-red-50 border border-red-200' :
                payout.status_color === 'blue' ? 'bg-blue-50 border border-blue-200' :
                payout.status_color === 'yellow' ? 'bg-yellow-50 border border-yellow-200' :
                'bg-gray-50 border border-gray-200'
            ]">
                <div class="flex items-start gap-4">
                    <component 
                        :is="getStatusIcon(payout.status)" 
                        :class="[
                            'h-8 w-8 flex-shrink-0',
                            payout.status_color === 'green' ? 'text-green-600' :
                            payout.status_color === 'red' ? 'text-red-600' :
                            payout.status_color === 'blue' ? 'text-blue-600' :
                            payout.status_color === 'yellow' ? 'text-yellow-600' :
                            'text-gray-600'
                        ]"
                        aria-hidden="true"
                    />
                    <div>
                        <p :class="[
                            'text-lg font-semibold',
                            payout.status_color === 'green' ? 'text-green-900' :
                            payout.status_color === 'red' ? 'text-red-900' :
                            payout.status_color === 'blue' ? 'text-blue-900' :
                            payout.status_color === 'yellow' ? 'text-yellow-900' :
                            'text-gray-900'
                        ]">
                            {{ payout.status_label }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Seller Information -->
                <div class="bg-white rounded-xl border p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Seller Information</h2>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600">Business Name</p>
                            <p class="font-medium text-gray-900">{{ payout.seller.business_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Owner</p>
                            <p class="font-medium text-gray-900">{{ payout.seller.user.name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Email</p>
                            <p class="font-medium text-gray-900">{{ payout.seller.user.email }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Trust Level</p>
                            <span class="inline-block px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700">
                                {{ payout.seller.trust_level }}
                            </span>
                        </div>
                        <div class="pt-3 border-t">
                            <Link 
                                :href="route('admin.marketplace.sellers.show', payout.seller.id)"
                                class="text-sm text-orange-600 hover:text-orange-700 font-medium"
                            >
                                View Seller Profile →
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Amount Details -->
                <div class="bg-white rounded-xl border p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Amount Details</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Requested Amount</span>
                            <span class="font-medium text-gray-900">{{ payout.formatted_amount }}</span>
                        </div>
                        <div v-if="payout.commission_deducted > 0" class="flex justify-between">
                            <span class="text-gray-600">Commission</span>
                            <span class="font-medium text-red-600">-K{{ (payout.commission_deducted / 100).toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between pt-3 border-t">
                            <span class="font-semibold text-gray-900">Net Amount</span>
                            <span class="font-bold text-green-600 text-xl">{{ payout.formatted_net_amount }}</span>
                        </div>
                    </div>
                </div>

                <!-- Payment Details -->
                <div class="bg-white rounded-xl border p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Details</h2>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600">Method</p>
                            <p class="font-medium text-gray-900">{{ getMethodLabel(payout.payout_method) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Account Number</p>
                            <p class="font-medium text-gray-900 font-mono">{{ payout.account_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Account Name</p>
                            <p class="font-medium text-gray-900">{{ payout.account_name }}</p>
                        </div>
                        <div v-if="payout.bank_name">
                            <p class="text-sm text-gray-600">Bank</p>
                            <p class="font-medium text-gray-900">{{ payout.bank_name }}</p>
                        </div>
                        <div v-if="payout.transaction_reference">
                            <p class="text-sm text-gray-600">Transaction Reference</p>
                            <p class="font-medium text-gray-900 font-mono text-sm">{{ payout.transaction_reference }}</p>
                        </div>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="bg-white rounded-xl border p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Timeline</h2>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600">Requested</p>
                            <p class="font-medium text-gray-900">{{ new Date(payout.created_at).toLocaleString() }}</p>
                        </div>
                        <div v-if="payout.approved_at">
                            <p class="text-sm text-gray-600">{{ payout.status === 'rejected' ? 'Rejected' : 'Approved' }}</p>
                            <p class="font-medium text-gray-900">{{ new Date(payout.approved_at).toLocaleString() }}</p>
                            <p v-if="payout.approved_by" class="text-sm text-gray-500">by {{ payout.approved_by.name }}</p>
                        </div>
                        <div v-if="payout.processed_at">
                            <p class="text-sm text-gray-600">Processed</p>
                            <p class="font-medium text-gray-900">{{ new Date(payout.processed_at).toLocaleString() }}</p>
                            <p v-if="payout.processed_by" class="text-sm text-gray-500">by {{ payout.processed_by.name }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div v-if="payout.seller_notes || payout.admin_notes || payout.rejection_reason" class="bg-white rounded-xl border p-6 mt-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Notes</h2>
                <div class="space-y-4">
                    <div v-if="payout.seller_notes" class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm font-medium text-gray-700 mb-1">Seller Notes</p>
                        <p class="text-sm text-gray-600">{{ payout.seller_notes }}</p>
                    </div>
                    <div v-if="payout.admin_notes" class="p-4 bg-blue-50 rounded-lg">
                        <p class="text-sm font-medium text-blue-900 mb-1">Admin Notes</p>
                        <p class="text-sm text-blue-700">{{ payout.admin_notes }}</p>
                    </div>
                    <div v-if="payout.rejection_reason" class="p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm font-medium text-red-900 mb-1">Rejection Reason</p>
                        <p class="text-sm text-red-700">{{ payout.rejection_reason }}</p>
                    </div>
                </div>
            </div>

            <!-- Modals -->
            <!-- Approve Modal -->
            <div v-if="showApproveModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Approve Payout</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Approve this payout of {{ payout.formatted_net_amount }} to {{ payout.seller.business_name }}?
                    </p>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                        <textarea 
                            v-model="approveForm.notes"
                            rows="3"
                            class="w-full border-gray-300 rounded-lg"
                            placeholder="Add any notes..."
                        ></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button 
                            @click="showApproveModal = false"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button 
                            @click="approve"
                            :disabled="approveForm.processing"
                            class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50"
                        >
                            {{ approveForm.processing ? 'Approving...' : 'Approve' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Reject Modal -->
            <div v-if="showRejectModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Reject Payout</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Reject this payout? The amount will be refunded to the seller's balance.
                    </p>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reason *</label>
                        <textarea 
                            v-model="rejectForm.reason"
                            rows="3"
                            required
                            class="w-full border-gray-300 rounded-lg"
                            placeholder="Explain why this payout is being rejected..."
                        ></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button 
                            @click="showRejectModal = false"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button 
                            @click="reject"
                            :disabled="rejectForm.processing || !rejectForm.reason"
                            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50"
                        >
                            {{ rejectForm.processing ? 'Rejecting...' : 'Reject' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Process Modal -->
            <div v-if="showProcessModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Mark as Processing</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Mark this payout as processing? This indicates you've initiated the payment.
                    </p>
                    <div class="flex gap-3">
                        <button 
                            @click="showProcessModal = false"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button 
                            @click="markAsProcessing"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                        >
                            Confirm
                        </button>
                    </div>
                </div>
            </div>

            <!-- Complete Modal -->
            <div v-if="showCompleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Complete Payout</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Mark this payout as completed. Enter the transaction reference from the payment provider.
                    </p>
                    <div class="space-y-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Reference *</label>
                            <input 
                                v-model="completeForm.transaction_reference"
                                type="text"
                                required
                                class="w-full border-gray-300 rounded-lg"
                                placeholder="e.g. TXN123456789"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                            <textarea 
                                v-model="completeForm.notes"
                                rows="2"
                                class="w-full border-gray-300 rounded-lg"
                                placeholder="Any additional notes..."
                            ></textarea>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button 
                            @click="showCompleteModal = false"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button 
                            @click="complete"
                            :disabled="completeForm.processing || !completeForm.transaction_reference"
                            class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50"
                        >
                            {{ completeForm.processing ? 'Completing...' : 'Complete' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Fail Modal -->
            <div v-if="showFailModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Mark as Failed</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Mark this payout as failed? The amount will be refunded to the seller's balance.
                    </p>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Failure Reason *</label>
                        <textarea 
                            v-model="failForm.reason"
                            rows="3"
                            required
                            class="w-full border-gray-300 rounded-lg"
                            placeholder="Explain why the payout failed..."
                        ></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button 
                            @click="showFailModal = false"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button 
                            @click="markAsFailed"
                            :disabled="failForm.processing || !failForm.reason"
                            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50"
                        >
                            {{ failForm.processing ? 'Processing...' : 'Mark as Failed' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </MarketplaceAdminLayout>
</template>
