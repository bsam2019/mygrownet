<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { CheckCircleIcon, XCircleIcon, ClockIcon, EyeIcon } from 'lucide-vue-next';
import { ref } from 'vue';

interface Payment {
    id: number;
    user: {
        id: number;
        name: string;
        email: string;
        phone?: string;
    };
    amount: number;
    payment_method: string;
    payment_reference: string;
    phone_number: string;
    payment_type: string;
    status: 'pending' | 'verified' | 'rejected';
    notes?: string;
    admin_notes?: string;
    verified_by?: {
        id: number;
        name: string;
    };
    verified_at?: string;
    created_at: string;
}

interface Props {
    payments: {
        data: Payment[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    pendingCount: number;
    currentStatus?: string;
}

const props = defineProps<Props>();

const selectedPayment = ref<Payment | null>(null);
const showModal = ref(false);
const showDetailPanel = ref(false);
const actionType = ref<'verify' | 'reject' | 'reset'>('verify');
const adminNotes = ref('');
const rejectReason = ref('');
const resetReason = ref('');

const viewDetails = (payment: Payment) => {
    selectedPayment.value = payment;
    showDetailPanel.value = true;
};

const openResetModal = (payment: Payment) => {
    selectedPayment.value = payment;
    actionType.value = 'reset';
    resetReason.value = '';
    showModal.value = true;
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
    }).format(amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'verified': return 'bg-green-100 text-green-800';
        case 'pending': return 'bg-yellow-100 text-yellow-800';
        case 'rejected': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const getPaymentMethodLabel = (method: string) => {
    const labels: Record<string, string> = {
        mtn_momo: 'MTN MoMo',
        airtel_money: 'Airtel Money',
        bank_transfer: 'Bank Transfer',
        cash: 'Cash',
        other: 'Other',
    };
    return labels[method] || method;
};

const getPaymentTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
        wallet_topup: 'Wallet Top-Up',
        subscription: 'Subscription',
        workshop: 'Workshop',
        product: 'Product',
        learning_pack: 'Learning Pack',
        coaching: 'Coaching',
        upgrade: 'Upgrade',
        other: 'Other',
    };
    return labels[type] || type;
};

const filterByStatus = (status: string | null) => {
    router.get(route('admin.payments.index'), status ? { status } : {});
};

const openVerifyModal = (payment: Payment) => {
    selectedPayment.value = payment;
    actionType.value = 'verify';
    adminNotes.value = '';
    showModal.value = true;
};

const openRejectModal = (payment: Payment) => {
    selectedPayment.value = payment;
    actionType.value = 'reject';
    rejectReason.value = '';
    showModal.value = true;
};

const submitAction = () => {
    if (!selectedPayment.value) return;

    if (actionType.value === 'verify') {
        router.post(route('admin.payments.verify', selectedPayment.value.id), {
            admin_notes: adminNotes.value,
        }, {
            onSuccess: () => {
                showModal.value = false;
                selectedPayment.value = null;
            },
        });
    } else if (actionType.value === 'reject') {
        router.post(route('admin.payments.reject', selectedPayment.value.id), {
            reason: rejectReason.value,
        }, {
            onSuccess: () => {
                showModal.value = false;
                selectedPayment.value = null;
            },
        });
    } else if (actionType.value === 'reset') {
        router.post(route('admin.payments.reset', selectedPayment.value.id), {
            reason: resetReason.value,
        }, {
            onSuccess: () => {
                showModal.value = false;
                selectedPayment.value = null;
            },
        });
    }
};
</script>

<template>
    <AdminLayout>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">Payment Approvals</h1>
                    <p class="mt-2 text-sm text-gray-600">Review and approve member wallet top-ups</p>
                </div>

                <!-- Stats -->
                <div class="mb-6 grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
                        <div class="text-sm text-gray-600">Pending</div>
                        <div class="text-2xl font-bold text-gray-900">{{ pendingCount }}</div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="mb-6 flex gap-2">
                    <button
                        @click="filterByStatus(null)"
                        class="px-4 py-2 rounded-lg font-medium transition-colors"
                        :class="!currentStatus ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                    >
                        All
                    </button>
                    <button
                        @click="filterByStatus('pending')"
                        class="px-4 py-2 rounded-lg font-medium transition-colors"
                        :class="currentStatus === 'pending' ? 'bg-yellow-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                    >
                        Pending
                    </button>
                    <button
                        @click="filterByStatus('verified')"
                        class="px-4 py-2 rounded-lg font-medium transition-colors"
                        :class="currentStatus === 'verified' ? 'bg-green-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                    >
                        Verified
                    </button>
                    <button
                        @click="filterByStatus('rejected')"
                        class="px-4 py-2 rounded-lg font-medium transition-colors"
                        :class="currentStatus === 'rejected' ? 'bg-red-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                    >
                        Rejected
                    </button>
                </div>

                <!-- Payments List -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="divide-y divide-gray-200">
                        <div 
                            v-for="payment in payments.data" 
                            :key="payment.id" 
                            @click="viewDetails(payment)"
                            class="p-4 hover:bg-gray-50 cursor-pointer transition-colors"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3 flex-1 min-w-0">
                                    <component
                                        :is="payment.status === 'verified' ? CheckCircleIcon : payment.status === 'rejected' ? XCircleIcon : ClockIcon"
                                        class="h-5 w-5 flex-shrink-0"
                                        :class="{
                                            'text-green-600': payment.status === 'verified',
                                            'text-red-600': payment.status === 'rejected',
                                            'text-yellow-600': payment.status === 'pending'
                                        }"
                                    />
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-gray-900 truncate">{{ payment.user.name }}</p>
                                        <p class="text-sm text-gray-500 truncate">{{ payment.user.email }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 ml-4">
                                    <div class="text-right">
                                        <p class="font-bold text-gray-900">{{ formatCurrency(payment.amount) }}</p>
                                        <p class="text-xs text-gray-500">{{ getPaymentMethodLabel(payment.payment_method) }}</p>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full capitalize whitespace-nowrap" :class="getStatusColor(payment.status)">
                                        {{ payment.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Panel -->
        <div v-if="showDetailPanel && selectedPayment" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <!-- Header -->
                <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-900">Payment Details</h2>
                    <button @click="showDetailPanel = false" class="text-gray-400 hover:text-gray-600">
                        <XCircleIcon class="h-6 w-6" />
                    </button>
                </div>

                <!-- Content -->
                <div class="p-6 space-y-6">
                    <!-- Status -->
                    <div class="flex items-center gap-3">
                        <component
                            :is="selectedPayment.status === 'verified' ? CheckCircleIcon : selectedPayment.status === 'rejected' ? XCircleIcon : ClockIcon"
                            class="h-8 w-8"
                            :class="{
                                'text-green-600': selectedPayment.status === 'verified',
                                'text-red-600': selectedPayment.status === 'rejected',
                                'text-yellow-600': selectedPayment.status === 'pending'
                            }"
                        />
                        <div>
                            <p class="text-sm text-gray-500">Status</p>
                            <p class="text-lg font-semibold capitalize" :class="{
                                'text-green-600': selectedPayment.status === 'verified',
                                'text-red-600': selectedPayment.status === 'rejected',
                                'text-yellow-600': selectedPayment.status === 'pending'
                            }">{{ selectedPayment.status }}</p>
                        </div>
                    </div>

                    <!-- Member Info -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Member Information</h3>
                        <div class="space-y-1">
                            <p class="font-semibold text-gray-900">{{ selectedPayment.user.name }}</p>
                            <p class="text-sm text-gray-600">{{ selectedPayment.user.email }}</p>
                            <p v-if="selectedPayment.user.phone" class="text-sm text-gray-600">{{ selectedPayment.user.phone }}</p>
                        </div>
                    </div>

                    <!-- Payment Details -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Payment Details</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Amount</p>
                                <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(selectedPayment.amount) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Method</p>
                                <p class="font-semibold text-gray-900">{{ getPaymentMethodLabel(selectedPayment.payment_method) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Reference</p>
                                <p class="font-mono text-sm text-gray-900">{{ selectedPayment.payment_reference }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Phone Number</p>
                                <p class="text-gray-900">{{ selectedPayment.phone_number }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Payment Type</p>
                                <p class="text-gray-900">{{ getPaymentTypeLabel(selectedPayment.payment_type) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Submitted</p>
                                <p class="text-gray-900">{{ formatDate(selectedPayment.created_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div v-if="selectedPayment.notes" class="bg-blue-50 rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-blue-900 mb-2">Member Notes</h3>
                        <p class="text-sm text-blue-800">{{ selectedPayment.notes }}</p>
                    </div>

                    <!-- Admin Notes -->
                    <div v-if="selectedPayment.admin_notes" class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Admin Notes</h3>
                        <p class="text-sm text-gray-600">{{ selectedPayment.admin_notes }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 pt-4 border-t border-gray-200">
                        <div v-if="selectedPayment.status === 'pending'" class="flex gap-3 w-full">
                            <button
                                @click="openVerifyModal(selectedPayment); showDetailPanel = false"
                                class="flex-1 px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 flex items-center justify-center gap-2"
                            >
                                <CheckCircleIcon class="h-5 w-5" />
                                Verify
                            </button>
                            <button
                                @click="openRejectModal(selectedPayment); showDetailPanel = false"
                                class="flex-1 px-6 py-3 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 flex items-center justify-center gap-2"
                            >
                                <XCircleIcon class="h-5 w-5" />
                                Reject
                            </button>
                        </div>
                        <button
                            v-else
                            @click="openResetModal(selectedPayment); showDetailPanel = false"
                            class="w-full px-6 py-3 bg-yellow-600 text-white font-medium rounded-lg hover:bg-yellow-700 flex items-center justify-center gap-2"
                        >
                            <ClockIcon class="h-5 w-5" />
                            Reset to Pending
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold mb-4">
                    {{ actionType === 'verify' ? 'Verify Payment' : actionType === 'reject' ? 'Reject Payment' : 'Reset Payment' }}
                </h3>
                
                <div v-if="selectedPayment" class="mb-4 p-4 bg-gray-50 rounded">
                    <p class="text-sm"><strong>Member:</strong> {{ selectedPayment.user.name }}</p>
                    <p class="text-sm"><strong>Amount:</strong> {{ formatCurrency(selectedPayment.amount) }}</p>
                    <p class="text-sm"><strong>Reference:</strong> {{ selectedPayment.payment_reference }}</p>
                </div>

                <div v-if="actionType === 'verify'" class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Admin Notes (Optional)</label>
                    <textarea
                        v-model="adminNotes"
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                        placeholder="Add any notes about this verification..."
                    ></textarea>
                </div>

                <div v-else-if="actionType === 'reject'" class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason *</label>
                    <textarea
                        v-model="rejectReason"
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                        placeholder="Explain why this payment is being rejected..."
                        required
                    ></textarea>
                </div>

                <div v-else class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reset Reason *</label>
                    <textarea
                        v-model="resetReason"
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                        placeholder="Explain why this payment is being reset to pending..."
                        required
                    ></textarea>
                </div>

                <div class="flex gap-3">
                    <button
                        @click="showModal = false"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submitAction"
                        :disabled="(actionType === 'reject' && !rejectReason) || (actionType === 'reset' && !resetReason)"
                        class="flex-1 px-4 py-2 rounded-lg text-white"
                        :class="actionType === 'verify' ? 'bg-green-600 hover:bg-green-700' : actionType === 'reject' ? 'bg-red-600 hover:bg-red-700' : 'bg-yellow-600 hover:bg-yellow-700'"
                    >
                        {{ actionType === 'verify' ? 'Verify' : actionType === 'reject' ? 'Reject' : 'Reset' }}
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
