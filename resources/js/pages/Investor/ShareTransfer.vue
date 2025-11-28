<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import InvestorLayout from '@/Layouts/InvestorLayout.vue';
import {
    ArrowsRightLeftIcon,
    DocumentTextIcon,
    ClockIcon,
    CheckCircleIcon,
    XCircleIcon,
    PlusIcon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

interface TransferRequest {
    id: number;
    shares_percentage: number;
    proposed_price: number;
    approved_price: number | null;
    transfer_type: string;
    status: string;
    reason_for_sale: string;
    submitted_at: string | null;
    reviewed_at: string | null;
    created_at: string;
}

interface Investor {
    id: number;
    name: string;
    email: string;
}

const props = defineProps<{
    investor: Investor;
    requests: TransferRequest[];
    currentShares: number | string | null;
    investorId: number;
    activePage?: string;
    unreadMessages?: number;
    unreadAnnouncements?: number;
}>();

// Safe accessor for currentShares - ensure it's always a number
const safeCurrentShares = computed((): number => {
    const value = props.currentShares;
    if (value === null || value === undefined) return 0;
    const num = typeof value === 'string' ? parseFloat(value) : Number(value);
    return isNaN(num) ? 0 : num;
});

const showNewRequestModal = ref(false);

const form = useForm({
    shares_percentage: '',
    proposed_price: '',
    transfer_type: 'buyback_request',
    reason_for_sale: '',
    proposed_buyer_name: '',
    proposed_buyer_email: '',
});

const submitForm = () => {
    form.post(route('investor.share-transfer.store'), {
        onSuccess: () => {
            showNewRequestModal.value = false;
            form.reset();
        },
    });
};

const submitForReview = (requestId: number) => {
    if (confirm('Submit this request for board review? You cannot edit it after submission.')) {
        useForm({}).post(route('investor.share-transfer.submit', requestId));
    }
};

const cancelRequest = (requestId: number) => {
    if (confirm('Are you sure you want to cancel this request?')) {
        useForm({}).post(route('investor.share-transfer.cancel', requestId));
    }
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'draft': return DocumentTextIcon;
        case 'submitted':
        case 'under_review': return ClockIcon;
        case 'board_approved':
        case 'completed': return CheckCircleIcon;
        case 'board_rejected':
        case 'cancelled': return XCircleIcon;
        default: return ClockIcon;
    }
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'draft': return 'text-gray-500 bg-gray-100';
        case 'submitted': return 'text-blue-600 bg-blue-100';
        case 'under_review': return 'text-yellow-600 bg-yellow-100';
        case 'board_approved': return 'text-green-600 bg-green-100';
        case 'board_rejected': return 'text-red-600 bg-red-100';
        case 'completed': return 'text-emerald-600 bg-emerald-100';
        case 'cancelled': return 'text-gray-500 bg-gray-100';
        default: return 'text-gray-500 bg-gray-100';
    }
};

const formatStatus = (status: string) => {
    return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', { style: 'currency', currency: 'ZMW' }).format(amount);
};
</script>

<template>
    <InvestorLayout 
        :investor="investor" 
        page-title="Share Transfer" 
        :active-page="activePage || 'share-transfer'"
        :unread-messages="unreadMessages"
        :unread-announcements="unreadAnnouncements"
    >
        <Head title="Share Transfer Requests" />

        <div class="max-w-6xl mx-auto px-4 py-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Share Transfer Requests</h1>
                    <p class="text-gray-600 mt-1">
                        Request to transfer your shares. All transfers require board approval.
                    </p>
                </div>
                <button
                    @click="showNewRequestModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                    New Request
                </button>
            </div>

            <!-- Current Holdings -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl p-6 text-white mb-8">
                <div class="flex items-center gap-3 mb-2">
                    <ArrowsRightLeftIcon class="h-6 w-6" aria-hidden="true" />
                    <span class="text-blue-100">Your Current Holdings</span>
                </div>
                <div class="text-3xl font-bold">{{ safeCurrentShares.toFixed(4) }}%</div>
                <p class="text-blue-100 text-sm mt-2">
                    Equity in MyGrowNet Ltd
                </p>
            </div>

            <!-- Important Notice -->
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-8">
                <div class="flex gap-3">
                    <ExclamationTriangleIcon class="h-5 w-5 text-amber-600 flex-shrink-0 mt-0.5" aria-hidden="true" />
                    <div>
                        <h3 class="font-medium text-amber-800">Important Information</h3>
                        <p class="text-sm text-amber-700 mt-1">
                            As a private limited company, all share transfers must be approved by the Board of Directors
                            in accordance with the Articles of Association. The company has the right of first refusal
                            on all share transfers.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Requests List -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Your Requests</h2>
                </div>

                <div v-if="requests.length === 0" class="p-12 text-center">
                    <DocumentTextIcon class="h-12 w-12 text-gray-300 mx-auto mb-4" aria-hidden="true" />
                    <p class="text-gray-500">No transfer requests yet</p>
                </div>

                <div v-else class="divide-y divide-gray-200">
                    <div v-for="request in requests" :key="request.id" class="p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span :class="[getStatusColor(request.status), 'px-3 py-1 rounded-full text-sm font-medium inline-flex items-center gap-1']">
                                        <component :is="getStatusIcon(request.status)" class="h-4 w-4" aria-hidden="true" />
                                        {{ formatStatus(request.status) }}
                                    </span>
                                    <span class="text-sm text-gray-500">
                                        {{ request.transfer_type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-3 gap-4 mt-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Shares</p>
                                        <p class="font-semibold">{{ request.shares_percentage.toFixed(4) }}%</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Proposed Price</p>
                                        <p class="font-semibold">{{ formatCurrency(request.proposed_price) }}</p>
                                    </div>
                                    <div v-if="request.approved_price">
                                        <p class="text-sm text-gray-500">Approved Price</p>
                                        <p class="font-semibold text-green-600">{{ formatCurrency(request.approved_price) }}</p>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600 mt-3">{{ request.reason_for_sale }}</p>
                            </div>
                            <div class="flex gap-2 ml-4">
                                <button
                                    v-if="request.status === 'draft'"
                                    @click="submitForReview(request.id)"
                                    class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700"
                                >
                                    Submit for Review
                                </button>
                                <button
                                    v-if="['draft', 'submitted'].includes(request.status)"
                                    @click="cancelRequest(request.id)"
                                    class="px-3 py-1.5 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50"
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Request Modal -->
        <Teleport to="body">
            <div v-if="showNewRequestModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="fixed inset-0 bg-black/50" @click="showNewRequestModal = false"></div>
                    <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6">
                        <h3 class="text-lg font-semibold mb-4">New Share Transfer Request</h3>
                        
                        <form @submit.prevent="submitForm" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Transfer Type</label>
                                <select v-model="form.transfer_type" class="w-full border-gray-300 rounded-lg">
                                    <option value="buyback_request">Request Company Buyback</option>
                                    <option value="internal">Transfer to Existing Shareholder</option>
                                    <option value="external">Transfer to External Party</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Shares to Transfer (%)
                                </label>
                                <input
                                    v-model="form.shares_percentage"
                                    type="number"
                                    step="0.0001"
                                    :max="safeCurrentShares"
                                    class="w-full border-gray-300 rounded-lg"
                                    placeholder="0.0000"
                                />
                                <p class="text-xs text-gray-500 mt-1">Maximum: {{ safeCurrentShares.toFixed(4) }}%</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Proposed Price (ZMW)
                                </label>
                                <input
                                    v-model="form.proposed_price"
                                    type="number"
                                    step="0.01"
                                    class="w-full border-gray-300 rounded-lg"
                                    placeholder="0.00"
                                />
                            </div>

                            <div v-if="form.transfer_type === 'external'">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Buyer Name</label>
                                <input
                                    v-model="form.proposed_buyer_name"
                                    type="text"
                                    class="w-full border-gray-300 rounded-lg"
                                />
                            </div>

                            <div v-if="form.transfer_type === 'external'">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Buyer Email</label>
                                <input
                                    v-model="form.proposed_buyer_email"
                                    type="email"
                                    class="w-full border-gray-300 rounded-lg"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Reason for Sale</label>
                                <textarea
                                    v-model="form.reason_for_sale"
                                    rows="3"
                                    class="w-full border-gray-300 rounded-lg"
                                    placeholder="Please explain why you wish to transfer your shares..."
                                ></textarea>
                            </div>

                            <div class="flex gap-3 pt-4">
                                <button
                                    type="button"
                                    @click="showNewRequestModal = false"
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                                >
                                    Create Draft
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Teleport>
    </InvestorLayout>
</template>
