<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">Transaction Details</h2>
                    <button
                        @click="$emit('close')"
                        class="text-gray-400 hover:text-gray-600"
                        aria-label="Close modal"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Body -->
            <div class="p-6 space-y-6">
                <!-- Status Badge -->
                <div class="flex items-center justify-between">
                    <span
                        :class="[
                            'px-4 py-2 text-sm font-medium rounded-full',
                            getStatusClass(transaction.status),
                        ]"
                    >
                        {{ transaction.status.toUpperCase() }}
                    </span>
                    <span class="text-2xl font-bold text-gray-900">
                        {{ transaction.currency }} {{ formatAmount(transaction.amount) }}
                    </span>
                </div>

                <!-- Transaction Info -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Reference</label>
                        <p class="text-sm text-gray-900 font-mono">{{ transaction.transaction_reference }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">External Reference</label>
                        <p class="text-sm text-gray-900 font-mono">{{ transaction.external_reference || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Customer Name</label>
                        <p class="text-sm text-gray-900">{{ transaction.customer_name || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Phone Number</label>
                        <p class="text-sm text-gray-900">{{ transaction.phone_number }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                        <p class="text-sm text-gray-900">{{ transaction.customer_email || 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Created</label>
                        <p class="text-sm text-gray-900">{{ formatDate(transaction.created_at) }}</p>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Description</label>
                    <p class="text-sm text-gray-900">{{ transaction.description }}</p>
                </div>

                <!-- Metadata -->
                <div v-if="transaction.metadata">
                    <label class="block text-sm font-medium text-gray-500 mb-2">Metadata</label>
                    <pre class="bg-gray-50 rounded-lg p-4 text-xs overflow-x-auto">{{ JSON.stringify(transaction.metadata, null, 2) }}</pre>
                </div>

                <!-- Refund Info -->
                <div v-if="transaction.refund_reference" class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <h3 class="font-semibold text-purple-900 mb-2">Refund Information</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <label class="block text-purple-700 mb-1">Refund Reference</label>
                            <p class="text-purple-900 font-mono">{{ transaction.refund_reference }}</p>
                        </div>
                        <div>
                            <label class="block text-purple-700 mb-1">Refund Amount</label>
                            <p class="text-purple-900">{{ transaction.currency }} {{ formatAmount(transaction.refund_amount) }}</p>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-purple-700 mb-1">Reason</label>
                            <p class="text-purple-900">{{ transaction.refund_reason }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="border-t border-gray-200 px-6 py-4">
                <button
                    @click="$emit('close')"
                    class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
                >
                    Close
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
interface Props {
    transaction: any;
}

defineProps<Props>();
defineEmits(['close']);

function formatAmount(amount: number): string {
    return new Intl.NumberFormat('en-ZM', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
}

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('en-ZM', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function getStatusClass(status: string): string {
    const classes: Record<string, string> = {
        completed: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        processing: 'bg-blue-100 text-blue-800',
        failed: 'bg-red-100 text-red-800',
        cancelled: 'bg-gray-100 text-gray-800',
        refunded: 'bg-purple-100 text-purple-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
}
</script>
