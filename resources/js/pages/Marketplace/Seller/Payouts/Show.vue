<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';
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
    <MarketplaceLayout>
        <div class="max-w-3xl mx-auto px-4 py-8">
            <!-- Header -->
            <div class="flex items-center gap-4 mb-6">
                <Link :href="route('marketplace.seller.payouts.index')" class="p-2 hover:bg-gray-100 rounded-lg">
                    <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Payout Details</h1>
                    <p class="text-gray-500">Reference: {{ payout.reference }}</p>
                </div>
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
                    <div class="flex-1">
                        <p :class="[
                            'text-lg font-semibold mb-1',
                            payout.status_color === 'green' ? 'text-green-900' :
                            payout.status_color === 'red' ? 'text-red-900' :
                            payout.status_color === 'blue' ? 'text-blue-900' :
                            payout.status_color === 'yellow' ? 'text-yellow-900' :
                            'text-gray-900'
                        ]">
                            {{ payout.status_label }}
                        </p>
                        <p :class="[
                            'text-sm',
                            payout.status_color === 'green' ? 'text-green-700' :
                            payout.status_color === 'red' ? 'text-red-700' :
                            payout.status_color === 'blue' ? 'text-blue-700' :
                            payout.status_color === 'yellow' ? 'text-yellow-700' :
                            'text-gray-700'
                        ]">
                            <span v-if="payout.status === 'pending'">Your payout request is being reviewed by our team.</span>
                            <span v-else-if="payout.status === 'approved'">Your payout has been approved and will be processed soon.</span>
                            <span v-else-if="payout.status === 'processing'">Your payout is being processed. Funds will arrive shortly.</span>
                            <span v-else-if="payout.status === 'completed'">Your payout has been completed successfully!</span>
                            <span v-else-if="payout.status === 'rejected'">Your payout request was rejected.</span>
                            <span v-else-if="payout.status === 'failed'">Payout processing failed. Amount has been refunded to your balance.</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Payout Information -->
            <div class="bg-white rounded-xl border divide-y">
                <!-- Amount Details -->
                <div class="p-6">
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
                            <span class="font-bold text-green-600 text-lg">{{ payout.formatted_net_amount }}</span>
                        </div>
                    </div>
                </div>

                <!-- Payment Details -->
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Details</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Method</span>
                            <span class="font-medium text-gray-900">{{ getMethodLabel(payout.payout_method) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Account Number</span>
                            <span class="font-medium text-gray-900">{{ payout.account_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Account Name</span>
                            <span class="font-medium text-gray-900">{{ payout.account_name }}</span>
                        </div>
                        <div v-if="payout.bank_name" class="flex justify-between">
                            <span class="text-gray-600">Bank</span>
                            <span class="font-medium text-gray-900">{{ payout.bank_name }}</span>
                        </div>
                        <div v-if="payout.transaction_reference" class="flex justify-between">
                            <span class="text-gray-600">Transaction Reference</span>
                            <span class="font-medium text-gray-900 font-mono text-sm">{{ payout.transaction_reference }}</span>
                        </div>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Timeline</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Requested</span>
                            <span class="font-medium text-gray-900">{{ new Date(payout.created_at).toLocaleString() }}</span>
                        </div>
                        <div v-if="payout.approved_at" class="flex justify-between">
                            <span class="text-gray-600">{{ payout.status === 'rejected' ? 'Rejected' : 'Approved' }}</span>
                            <span class="font-medium text-gray-900">{{ new Date(payout.approved_at).toLocaleString() }}</span>
                        </div>
                        <div v-if="payout.processed_at" class="flex justify-between">
                            <span class="text-gray-600">Processed</span>
                            <span class="font-medium text-gray-900">{{ new Date(payout.processed_at).toLocaleString() }}</span>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div v-if="payout.seller_notes || payout.admin_notes || payout.rejection_reason" class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Notes</h2>
                    <div class="space-y-4">
                        <div v-if="payout.seller_notes">
                            <p class="text-sm font-medium text-gray-700 mb-1">Your Notes</p>
                            <p class="text-sm text-gray-600">{{ payout.seller_notes }}</p>
                        </div>
                        <div v-if="payout.admin_notes">
                            <p class="text-sm font-medium text-gray-700 mb-1">Admin Notes</p>
                            <p class="text-sm text-gray-600">{{ payout.admin_notes }}</p>
                        </div>
                        <div v-if="payout.rejection_reason" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-sm font-medium text-red-900 mb-1">Rejection Reason</p>
                            <p class="text-sm text-red-700">{{ payout.rejection_reason }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Back Button -->
            <div class="mt-6">
                <Link 
                    :href="route('marketplace.seller.payouts.index')"
                    class="inline-flex items-center gap-2 px-6 py-2 border border-gray-300 text-gray-700 bg-white rounded-lg hover:bg-gray-50 font-medium"
                >
                    <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
                    Back to Payouts
                </Link>
            </div>
        </div>
    </MarketplaceLayout>
</template>
